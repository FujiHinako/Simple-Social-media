<?php
session_start();

$data_dir = 'data/';
$posts_file = $data_dir . 'posts.json';
$users_file = $data_dir . 'users.json';

/* ---------- LOAD POSTS ---------- */
$posts = [];
if (file_exists($posts_file)) {
    $posts = json_decode(file_get_contents($posts_file), true) ?: [];
}
$posts = array_reverse($posts);

/* ---------- LOAD USERS ---------- */
$users = [];
if (file_exists($users_file)) {
    $users = json_decode(file_get_contents($users_file), true) ?: [];
}

/* ---------- POSTS HTML ---------- */
$post_html = '';
foreach ($posts as $post) {
    $user_link = 'profile.php?uid=' . $post['user_id'];

    $post_html .= '
        <article class="post-card">
            <div class="post-header">
                <a href="' . $user_link . '" class="post-user">
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
                <button class="comment-btn" onclick="toggleComments(\'' . $post['id'] . '\')" data-post-id="' . $post['id'] . '">💬 Comment (' . count($post['comments'] ?? []) . ')</button>
            </div>
            <div id="comments-' . $post['id'] . '" class="comments-section" style="display:none;">
                <div class="comments-list p-3 border-top">';
                    foreach (($post['comments'] ?? []) as $comment) {
                        $post_html .= '
                    <div class="comment-item mb-2 p-2 bg-light rounded">
                        <small class="fw-bold text-primary">' . htmlspecialchars($comment['user']) . '</small>
                        <div>' . nl2br(htmlspecialchars($comment['text'])) . '</div>
                    </div>';
                    }
                    $post_html .= '
                </div>
                <form class="comment-form p-3 border-top" onsubmit="submitComment(event)" data-post-id="' . $post['id'] . '">
                    <div class="input-group">
                <input type="text" class="form-control" name="comment" data-username="' . addslashes($_SESSION['user_name'] ?? 'Anonymous') . '" placeholder="Write a comment..." maxlength="500" required>
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

/* ---------- SESSION ---------- */
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? 'You';
$user_avatar = strtoupper(substr($user_name, 0, 2));

/* ---------- POST FORM ---------- */
$post_form = '';
if ($is_logged_in) {
    $post_form = '
        <div class="post-composer mb-4">
            <div class="card shadow-sm border-0" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#postModal">
                <div class="card-body p-3">
                    <div class="user-avatar-small">' . $user_avatar . '</div>
                    <textarea placeholder="What\'s on your mind, ' . $user_name . '? " readonly class="form-control border-0" rows="1" style="resize:none; background:transparent;"></textarea>
                </div>
            </div>
        </div>
        <!-- Post Modal -->
        <div class="modal fade" id="postModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-0 pb-0">
                        <div class="user-avatar-large me-3">' . $user_avatar . '</div>
                        <div>
                            <h5 class="mb-1">' . htmlspecialchars($user_name) . '</h5>
                            <small class="text-muted">Anyone can reply</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="post_handler.php" enctype="multipart/form-data" id="postForm" onsubmit="location.reload();">
                        <div class="modal-body p-0">
                            <div class="p-4">
                                <textarea id="postTextarea" name="content" class="form-control border-0" rows="3" placeholder="What\'s on your mind, ' . $user_name . '? " maxlength="1000" style="resize:none; font-size:1.1rem;"></textarea>
                            </div>
                            <div class="border-top position-relative">
                                <img id="postImagePreview" class="img-fluid rounded" style="max-height:300px; display:none; width:100%;">
                                <label for="postImage" class="position-absolute top-2 end-2 btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-image me-1"></i> Photo
                                </label>
                                <input type="file" id="postImage" name="image" class="d-none" accept="image/*">
                            </div>
                        </div>
                        <div class="modal-footer border-0 bg-light">
                            <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4">Post</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
        const postModal = new bootstrap.Modal(document.getElementById(\'postModal\'));
        const textarea = document.getElementById(\'postTextarea\');
        const imageInput = document.getElementById(\'postImage\');
        const imagePreview = document.getElementById(\'postImagePreview\');
        
        // Auto-resize textarea
        textarea.addEventListener(\'input\', function() {
            this.style.height = \'auto\';
            this.style.height = this.scrollHeight + \'px\';
        });
        
        // Image preview
        imageInput.addEventListener(\'change\', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = \'block\';
                };
                reader.readAsDataURL(file);
            }
        });
        </script>';
}

/* ---------- USERS LIST ---------- */
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

/* ---------- AUTH ---------- */
if ($is_logged_in) {
    $auth_section = '
    <button onclick="logout()" class="auth-btn btn-logout">
        Logout
    </button>
    <div class="user-avatar" onclick="toggleProfileDropdown()">' . $user_avatar . '</div>
    <div id="profileDropdown" style="display:none; position:absolute; top:60px; right:1rem; background:white; box-shadow:0 8px 25px rgba(0,0,0,0.15); border-radius:12px;">
        <a href="profile.php{PROFILE_LINK}" style="display:block; padding:12px 20px; color:#333; text-decoration:none;">My Profile</a>
        <button onclick="logout()" style="border:none; background:none; width:100%; text-align:left; padding:12px 20px; color:#e74c3c;">Logout</button>
    </div>';
} else {
    $auth_section = '
    <a href="login.php" class="auth-btn btn-login">
        Sign In
    </a>';
}

/* ---------- JS ---------- */
$head_script = '<script>
function logout() {
  fetch("logout.php", {method: "POST"}).then(() => location.reload());
}
function likePost(postId) {
  fetch("post_handler.php?like=" + postId).then(() => location.reload());
}
function toggleComments(postId) {
  const box = document.getElementById("comments-" + postId);
  const btn = event.target.closest(\'.comment-btn\');
  if (box) {
    const isHidden = box.style.display === "none";
    box.style.display = isHidden ? "block" : "none";
    if (isHidden && btn) btn.innerHTML = "💬 Comment (" + box.querySelectorAll(\'.comment-item\').length + ")";
  }
}
function submitComment(event, postId) {
  event.preventDefault();
  const form = event.target;
  const formData = new FormData(form);
  fetch("post_handler.php", {
    method: "POST",
    body: formData
  }).then(response => response.json())
    .then(data => {
      if (data.success) {
        const commentsList = document.querySelector("#comments-" + postId + " .comments-list");
            
        const userName = "' . htmlspecialchars($_SESSION['user_name'] ?? 'Anonymous', ENT_QUOTES) . '";
        const newComment = `
          <div class="comment-item mb-2 p-2 bg-light rounded">
            <small class="fw-bold text-primary">${userName}</small> 
            <div>${commentInput.value}</div>
          </div>`;
        commentsList.insertAdjacentHTML("beforeend", newComment);
        commentInput.value = "";
        // Update count
        const commentBtn = document.querySelector("button[onclick*=\\'"+postId+"']").closest(\'.comment-btn\');
        if (commentBtn) commentBtn.innerHTML = "💬 Comment (" + commentsList.querySelectorAll(\'.comment-item\').length + ")";
      }
    }).catch(err => console.error("Comment failed", err));
}
function toggleProfileDropdown() {
  const dropdown = document.getElementById("profileDropdown");
  dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}
document.addEventListener("click", (e) => {
  const dropdown = document.getElementById("profileDropdown");
  if (dropdown.style.display === "block" && !e.target.closest(".user-avatar")) dropdown.style.display = "none";
});
</script>';

$page_script = '';

/* ---------- TEMPLATE ---------- */
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
    '{PAGE_SCRIPT}' => $page_script
];

$layout = file_get_contents(__DIR__ . '/templates/layout.html');

foreach ($replacements as $key => $value) {
    $layout = str_replace($key, $value, $layout);
}

echo $layout;
?>

