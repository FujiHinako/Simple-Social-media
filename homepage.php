<?php
session_start();

$data_dir = 'data/';
$posts_file = $data_dir . 'posts.json';
$users_file = $data_dir . 'users.json';

/* Load Posts */
$posts = [];
if (file_exists($posts_file)) {
    $posts = json_decode(file_get_contents($posts_file), true) ?: [];
}
$posts = array_reverse($posts);

/* Load Users */
$users = [];
if (file_exists($users_file)) {
    $users = json_decode(file_get_contents($users_file), true) ?: [];
}

/* Session */
$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? (int)$_SESSION['user_id'] : null;
$user_name = $_SESSION['user_name'] ?? 'You';
$user_avatar = strtoupper(substr($user_name, 0, 2));

/* Post HTML */
$post_html = '';
foreach ($posts as $post) {
    $comments_html = '';
    foreach (($post['comments'] ?? []) as $comment) {
        $comments_html .= '
        <div class="comment-item mb-2 p-2 bg-light rounded">
            <small class="fw-bold text-primary">' . htmlspecialchars($comment['user']) . '</small>
            <div>' . nl2br(htmlspecialchars($comment['text'])) . '</div>
        </div>';
    }
    
    $post_html .= '
        <article class="post-card">
            <div class="post-header">
                <a href="profile.php?uid=' . $post['user_id'] . '" class="post-user">
                    <div class="post-avatar">' . strtoupper(substr($post['user_name'], 0, 2)) . '</div>
                    <div>
                        <div class="post-author">' . htmlspecialchars($post['user_name']) . '</div>
                        <div class="post-time">' . date('M j', strtotime($post['created_at'])) . '</div>
                    </div>
                </a>
            </div>

            <div class="post-content">' . nl2br(htmlspecialchars($post['content'])) . '</div>
            ' . (!empty($post['image']) ? '<div class="post-image"><img src="' . htmlspecialchars($post['image']) . '" alt="Post image" class="img-fluid rounded"></div>' : '') . '

            <div class="post-actions">
                <button class="like-btn" data-post-id="' . $post['id'] . '" onclick="likePost(\'' . $post['id'] . '\')">👍 Like (' . ($post['likes'] ?? 0) . ')</button>
                <button class="comment-btn" data-post-id="' . $post['id'] . '" onclick="toggleComments(\'' . $post['id'] . '\')">💬 Comment (' . count($post['comments'] ?? []) . ')</button>
            </div>
            <div id="comments-' . $post['id'] . '" class="comments-section" style="display:none;">
                <div class="comments-list p-3 border-top">' . $comments_html . '</div>
                <form class="comment-form p-3 border-top" onsubmit="submitComment(event, \'' . $post['id'] . '\')">
                    <div class="input-group">
                        <input type="text" class="form-control" name="comment" placeholder="Write a comment..." maxlength="500" required>
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                    <input type="hidden" name="post_id" value="' . $post['id'] . '">
                </form>
            </div>
        </article>';
}

if (empty($posts)) {
    $post_html = '<p>No posts yet. <a href="#post-form">Create the first post!</a></p>';
}

/* Users List - Contacts Style */
$users_list = '';
foreach ($users as $user) {
    if ($user['id'] == $user_id) continue; // Skip current user
    $profile_pic = !empty($user['profile_photo']) ? $user['profile_photo'] : '';
    $avatar = $profile_pic ? '<img src="' . htmlspecialchars($profile_pic) . '" alt="Profile" class="contact-avatar-img">' : strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
    
    $users_list .= '
        <a href="profile.php?uid=' . $user['id'] . '" class="contact-item">
            <div class="contact-avatar">' . $avatar . '</div>
            <div class="contact-info">
                <div class="contact-name">' . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . '</div>
            </div>
            <div class="contact-status">
                <div class="status-dot"></div>
            </div>
        </a>';
}

/* Auth */
if ($is_logged_in) {
    $auth_section = '
    <div class="user-avatar" onclick="toggleProfileDropdown()">' . '<img src="{PROFILE_PHOTO}" alt="Profile" class="profile-photo" onerror="this.src=\'https://via.placeholder.com/200?text=Profile\';">' . '</div>
    <div id="profileDropdown" style="display:none; position:absolute; top:60px; right:1rem; background:white; box-shadow:0 8px 25px rgba(0,0,0,0.15); border-radius:12px;">
        <a href="profile.php?uid=' . $_SESSION['user_id'] . '" style="display:block; padding:12px 20px; color:#333; text-decoration:none;">View Profile</a>
        <button onclick="logout()" style="border:none; background:none; width:100%; text-align:left; padding:12px 20px; color:#e74c3c;">Logout</button>
    </div>';
} else {
    $auth_section = '
    <a href="login.php" class="auth-btn btn-login">
        Sign In
    </a>';
}

/* Post Form - INLINE WORKING */
$post_form = '';
if ($is_logged_in) {
    $profile_link = 'profile.php?uid=' . $_SESSION['user_id'];
    $post_form = '
        <div class="post-composer-single mb-4 card shadow-sm border-0 p-3" onclick="openPostModal()" style="cursor:pointer;">
            <div class="d-flex align-items-center gap-3">
                <div class="user-avatar-small">' . $user_avatar . '</div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <a href="' . $profile_link . '" class="fw-semibold text-decoration-none">' . htmlspecialchars($user_name) . '</a>
                    </div>
                    <div class="text-muted small">What\'s on your mind, ' . htmlspecialchars($user_name) . '?</div>
                </div>
            </div>
        </div>';
}

/* Head Script - MINIMAL */
$head_script = '<script>
function logout() { fetch("logout.php", {method: "POST"}).then(() => location.reload()); }
function likePost(postId) {
  fetch("post_handler.php?like=" + postId)
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        const btn = document.querySelector(\'.like-btn[data-post-id="\' + postId + \'\"]\');
        if (btn) {
          const match = btn.textContent.match(/\\((\\d+)\\)/);
          const currentLikes = match ? parseInt(match[1]) : 0;
          btn.textContent = btn.textContent.replace(/\\(\\d+\\)/, "(" + (currentLikes + 1) + ")");
        }
      }
    })
    .catch(function(error) { console.error(error); });
}
function toggleProfileDropdown() { 
  const dropdown = document.getElementById("profileDropdown"); 
  dropdown.style.display = dropdown.style.display === "block" ? "none" : "block"; 
}
function toggleProfileNavDropdown(event) {
  event.preventDefault();
  const dropdown = document.getElementById("profile-nav-dropdown");
  dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}
document.addEventListener("click", (e) => { 
  const dropdown = document.getElementById("profileDropdown"); 
  if (dropdown.style.display === "block" && !e.target.closest(".user-avatar")) dropdown.style.display = "none";
  
  const navDropdown = document.getElementById("profile-nav-dropdown");
  const navLink = document.querySelector(".nav-link[onclick*=\"toggleProfileNavDropdown\"]");
  if (navDropdown && navDropdown.style.display === "block" && navLink && !navLink.contains(e.target) && !navDropdown.contains(e.target)) {
    navDropdown.style.display = "none";
  }
});
</script>';

/* Template */
$content = str_replace(
    ['{USERS_LIST}', '{POST_FORM}', '{POSTS_LIST}', '{POST_MODAL}'],
    [$users_list, $post_form, $post_html, file_get_contents(__DIR__ . '/templates/post-create-modal.html')],
    file_get_contents(__DIR__ . '/templates/home-fixed.html')
);

$replacements = [
    '{PAGE_TITLE}' => 'Home',
    '{PAGE_HEAD}' => $head_script,
    '{MAIN_CONTENT}' => $content,
    '{PROFILE_LINK}' => $is_logged_in ? '?uid=' . $_SESSION['user_id'] . '&tab=posts' : '?tab=posts',
    '{AUTH_SECTION}' => $auth_section,
    '{PAGE_SCRIPT}' => ''
];

echo str_replace(array_keys($replacements), array_values($replacements), file_get_contents(__DIR__ . '/templates/layout.html'));
?>

