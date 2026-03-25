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
                <button class="comment-btn" onclick="toggleComments(\'' . $post['id'] . '\')">💬 Comment</button>
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
            <div class="card shadow-sm border-0" style="cursor:pointer;" onclick="document.querySelector(\'#postTextarea\').focus()">
                <div class="card-body p-3">
                    <div class="user-avatar-small">' . $user_avatar . '</div>
                    <textarea id="postTextarea" placeholder="What\'s on your mind, ' . $user_name . '? " class="form-control border-0" rows="1" style="resize:none; background:transparent;"></textarea>
                </div>
            </div>
            <form method="POST" action="post_handler.php" enctype="multipart/form-data" class="mt-2 d-none" id="postForm">
                <textarea name="content" required maxlength="1000"></textarea>
                <input type="file" name="image" accept="image/*">
                <button type="submit">Post</button>
            </form>
        </div>
        <script>
        document.getElementById("postTextarea").addEventListener("input", function() {
            document.getElementById("postForm").querySelector("textarea").value = this.value;
            this.style.height = "auto";
            this.style.height = this.scrollHeight + "px";
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
  if (box) box.style.display = box.style.display === "none" ? "block" : "none";
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

