<?php
session_start();

$data_dir = 'data/';
$posts_file = $data_dir . 'posts.json';
$users_file = $data_dir . 'users.json';

$posts = [];
if (file_exists($posts_file)) {
    $posts = json_decode(file_get_contents($posts_file), true) ?: [];
}
$posts = array_reverse($posts);

$users = [];
if (file_exists($users_file)) {
    $users = json_decode(file_get_contents($users_file), true) ?: [];
}

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

$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? 'You';
$user_avatar = strtoupper(substr($user_name, 0, 2));

$post_form = '';
if ($is_logged_in) {
    $post_form = '
        <div class="post-form-container">
            <form method="POST" action="post_handler.php">
                <div class="post-form-header">
                    <div class="user-avatar-small" title="' . htmlspecialchars($user_name) . '">' . $user_avatar . '</div>
                    <textarea name="content" placeholder="What\'s on your mind, ' . htmlspecialchars($user_name) . '?" maxlength="500" required></textarea>
                </div>
                <button type="submit" class="post-btn">Post</button>
            </form>
        </div>';
}

$users_list = '';
foreach ($users as $user) {
    $avatar_url = $user['profile_photo'] ?? '';
    $avatar_fallback = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
    $user_card = '
        <a href="profile.php?uid=' . $user['id'] . '" class="user-card">
            <img src="' . $avatar_url . '" alt="' . htmlspecialchars($user['first_name']) . '" class="user-card-avatar" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\';">
            <div class="avatar-fallback" style="display:none;">' . $avatar_fallback . '</div>
            <h3>' . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . '</h3>
            <p class="user-card-bio">' . htmlspecialchars(substr($user['bio'] ?? 'No bio', 0, 100)) . '...</p>
            <span class="user-card-link">View Profile</span>
        </a>';
    $users_list .= $user_card;
}

$auth_section = $is_logged_in ? '
    <button onclick="logout()" class="auth-btn btn-logout" id="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
    <div class="user-avatar" id="user-avatar" title="' . htmlspecialchars($user_name) . '">' . $user_avatar . '</div>' : '
    <a href="login.php" class="auth-btn btn-login" id="login-btn"><i class="fas fa-sign-in-alt"></i> Login</a>
    <a href="login.php#signup-form" class="auth-btn btn-signup" id="signup-btn">Sign Up</a>';

$mobile_auth = $is_logged_in ? '
    <div class="user-avatar" onclick="toggleMobileMenu()">' . $user_avatar . '</div>' : '
    <a href="login.php" class="auth-btn btn-login"><i class="fas fa-sign-in-alt"></ Asc i> Login</a>
    <a href="login.php#signup-form" class="auth-btn btn-signup">Sign Up</a>';

$head_script = '<script>
function logout() {
  fetch("logout.php", {method: "POST"}).then(() => {
    localStorage.setItem("loggedIn", "false");
    location.reload();
  });
}
function likePost(postId) {
  fetch("post_handler.php?like=" + postId).then(() => location.reload());
}
function toggleComments(postId) {
  const box = document.getElementById("comments-" + postId);
  if (box) box.style.display = box.style.display === "none" ? "block" : "none";
}
function toggleAuth() {
  localStorage.setItem("loggedIn", "false");
  location.reload();
}
function updateAuth() {
  const loggedIn = localStorage.getItem("loggedIn") !== "false";
  const loginBtn = document.getElementById("login-btn");
  const signupBtn = document.getElementById("signup-btn");
  const logoutBtn = document.getElementById("logout-btn");
  const userAvatar = document.getElementById("user-avatar");
  if (loggedIn && logoutBtn && userAvatar) {
    if (loginBtn) loginBtn.style.display = "none";
    if (signupBtn) signupBtn.style.display = "none";
    logoutBtn.style.display = "inline-flex";
    userAvatar.style.display = "flex";
  } else if (loginBtn && signupBtn) {
    loginBtn.style.display = "inline-flex";
    signupBtn.style.display = "inline-flex";
    if (logoutBtn) logoutBtn.style.display = "none";
    if (userAvatar) userAvatar.style.display = "none";
  }
}
window.addEventListener("load", updateAuth);
</script>';

$page_script = '';

$replacements = [
    '{PAGE_TITLE}' => 'ProfileApp - Connect with Professionals',
    '{PAGE_HEAD}' => $head_script,
    '{MAIN_CONTENT}' => str_replace([
        '{USERS_LIST}',
        '{POST_FORM}',
        '{POSTS_LIST}'
    ], [
        $users_list,
        $post_form,
        $post_html
    ], file_get_contents(__DIR__ . '/templates/home.html')),
    '{AUTH_SECTION}' => $auth_section,
    '{MOBILE_AUTH}' => $mobile_auth,
    '{PROFILE_LINK}' => '',
    '{PAGE_SCRIPT}' => $page_script
];

$layout = file_get_contents(__DIR__ . '/templates/layout.html');
foreach ($replacements as $key => $value) {
    $layout = str_replace($key, $value, $layout);
}

echo $layout;
?>

