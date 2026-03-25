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

            <div class="post-actions">
                <button class="like-btn" onclick="likePost(\'' . $post['id'] . '\')">👍 Like (' . ($post['likes'] ?? 0) . ')</button>
                <button class="comment-btn" onclick="toggleComments(\'' . $post['id'] . '\')" data-post-id="' . $post['id'] . '">💬 Comment (' . strlen($comments_html) . ')</button>
            </div>
            <div id="comments-' . $post['id'] . '" class="comments-section" style="display:none;">
                <div class="comments-list p-3 border-top">' . $comments_html . '</div>
                <form class="comment-form p-3 border-top" onsubmit="submitComment(event)" data-post-id="' . $post['id'] . '">
                    <div class="input-group">
                        <input type="text" class="form-control" name="comment" data-username="' . htmlspecialchars($_SESSION['user_name'] ?? 'Anonymous') . '" placeholder="Write a comment..." maxlength="500" required>
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

/* Users List */
$users_list = '';
foreach ($users as $user) {
    $users_list .= '
        <a href="profile.php?uid=' . $user['id'] . '" class="user-card">
            <div class="user-card-avatar">' . strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) . '</div>
            <div>
                <h3>' . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . '</h3>
                <p>' . htmlspecialchars($user['bio'] ?? 'No bio') . '</p>
            </div>
        </a>';
}

/* Auth */
if ($is_logged_in) {
    $auth_section = '
    <button onclick="logout()" class="auth-btn btn-logout">
        Logout
    </button>
    <div class="user-avatar" onclick="toggleProfileDropdown()">' . $user_avatar . '</div>
    <div id="profileDropdown" style="display:none; position:absolute; top:60px; right:1rem; background:white; box-shadow:0 8px 25px rgba(0,0,0,0.15); border-radius:12px;">
        <a href="profile.php?uid=' . $_SESSION['user_id'] . '" style="display:block; padding:12px 20px; color:#333; text-decoration:none;">My Profile</a>
        <button onclick="logout()" style="border:none; background:none; width:100%; text-align:left; padding:12px 20px; color:#e74c3c;">Logout</button>
    </div>';
} else {
    $auth_section = '
    <a href="login.php" class="auth-btn btn-login">
        Sign In
    </a>';
}

/* Head Script */
$head_script = '<script>
function logout() { fetch("logout.php", {method: "POST"}).then(() => location.reload()); }
function likePost(postId) { fetch("post_handler.php?like=" + postId).then(() => location.reload()); }
function toggleProfileDropdown() { 
  const dropdown = document.getElementById("profileDropdown"); 
  dropdown.style.display = dropdown.style.display === "block" ? "none" : "block"; 
}
document.addEventListener("click", (e) => { 
  const dropdown = document.getElementById("profileDropdown"); 
  if (dropdown.style.display === "block" && !e.target.closest(".user-avatar")) dropdown.style.display = "none"; 
});
</script>';

/* Page replacements */
$replacements = [
    '{PAGE_TITLE}' => 'ProfileApp - Home',
    '{PAGE_HEAD}' => $head_script,
    '{MAIN_CONTENT}' => str_replace(
        ['{USERS_LIST}', '{POST_FORM}', '{POSTS_LIST}'],
        [$users_list, $post_form, $post_html],
        file_get_contents(__DIR__ . '/templates/home.html')
    ),
    '{PROFILE_LINK}' => $is_logged_in ? '?uid=' . $_SESSION['user_id'] : '',
    '{AUTH_SECTION}' => $auth_section,
    '{PAGE_SCRIPT}' => ''
];

echo str_replace(array_keys($replacements), array_values($replacements), file_get_contents(__DIR__ . '/templates/layout.html'));
?>

