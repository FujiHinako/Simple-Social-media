<?php
session_start();

$user_id = $_GET['uid'] ?? $_GET['user'] ?? null;
if (!isset($_SESSION['user_id']) && !$user_id) {
    header('Location: login.php?from=profile');
    exit;
}
if (!$user_id) {
    header('Location: homepage.php');
    exit;
}

$data_dir = __DIR__ . '/data/';
$users_file = $data_dir . 'users.json';
$posts_file = $data_dir . 'posts.json';

$users = [];
if (file_exists($users_file)) {
    $json = file_get_contents($users_file);
    if ($json !== false) {
        $users = json_decode($json, true) ?: [];
    }
}
$user = null;
foreach ($users as $u) {
    if ($u['id'] === $user_id) {
        $user = $u;
        break;
    }
}

if (!$user) {
    http_response_code(404);
    die('<h1>User not found</h1>');
}

$posts = [];
if (file_exists($posts_file)) {
    $json = file_get_contents($posts_file);
    if ($json !== false) {
        $posts = json_decode($json, true) ?: [];
    }
}
$user_posts = array_filter($posts, fn($p) => $p['user_id'] === $user_id);
$user_posts = array_reverse($user_posts);

$is_owner = isset($_SESSION['user_id']) && $_SESSION['user_id'] === $user_id;

$user_full_name = htmlspecialchars($user['first_name'] . ($user['middle_name'] ? ' ' . $user['middle_name'] : '') . ' ' . $user['last_name']);
$user_email = htmlspecialchars($user['email']);
$user_bio = htmlspecialchars($user['bio'] ?? 'No biography available.');
$user_avatar = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));

$edit_button = $is_owner ? '<a href="edit_profile.php" class="btn-edit">Edit Profile</a>' : '';

$user_posts_list = '';
if (empty($user_posts)) {
    $user_posts_list = '<p>No posts yet. ' . ($is_owner ? '<a href="homepage.php">Create some posts!</a>' : '') . '</p>';
} else {
    foreach ($user_posts as $post) {
$image_html = !empty($post['image']) ? '<img src="' . htmlspecialchars($post['image']) . '" alt="Post image" class="img-fluid rounded mt-3 post-image" style="max-height:400px; object-fit:cover;">' : '';

$user_post_avatar = strtoupper(substr($post['user_name'], 0, 2));

$user_posts_list .= '
        <article class="post-card">
            <div class="post-header">
                <a href="profile.php?uid=' . $post['user_id'] . '" class="post-user">
                    <div class="post-avatar">' . $user_post_avatar . '</div>
                    <div>
                        <div class="post-author">' . htmlspecialchars($post['user_name']) . '</div>
                        <div class="post-time">' . date('M j, Y', strtotime($post['created_at'])) . '</div>
                    </div>
                </a>
            </div>
            <div class="post-content">' . nl2br(htmlspecialchars($post['content'])) . '</div>
            ' . $image_html . '
            <div class="post-actions">
                <button class="like-btn" onclick="likePost(\'' . $post['id'] . '\')">👍 ' . ($post['likes'] ?? 0) . '</button>
                <button class="comment-btn" onclick="showComments(\'' . $post['id'] . '\')">💬 Comment</button>
            </div>
            <div id="comments-' . $post['id'] . '" style="display:none;" class="border-top pt-3">
                <form method="POST" action="../post_handler.php" class="d-flex gap-2">
                    <input type="hidden" name="post_id" value="' . $post['id'] . '">
                    <input type="text" name="comment" class="form-control form-control-sm" placeholder="Write a comment..." required maxlength="500">
                    <button type="submit" class="btn btn-primary btn-sm">Post</button>
                </form>
            </div>
        </article>'; 

    }
}

$user_name = $_SESSION['user_name'] ?? 'Guest';
$user_session_avatar = strtoupper(substr($user_name, 0, 1));

$auth_section = isset($_SESSION['user_id']) ? '
    <div class="user-avatar" id="user-avatar" title="' . htmlspecialchars($user_name) . '" onclick="toggleProfileDropdown()">' . $user_session_avatar . '</div>' : '

    <a href="login.php" class="auth-btn btn-login btn-account"><i class="fas fa-user-circle"></i> Sign In / Up</a>';

$mobile_auth = isset($_SESSION['user_id']) ? '
    <button onclick="logout()" class="auth-btn btn-logout">Logout</button>' : '';

$page_title = $user_full_name;
$profile_link = '?uid=' . $user_id;
$page_script = '
<script>
function logout() {
  fetch("logout.php", {method: "POST"}).then(() => location.reload());
}

function likePost(postId) {
  fetch("post_handler.php?like=" + postId).then(() => location.reload());
}

function showComments(postId) {
  const commentBox = document.getElementById("comments-" + postId);
  if (commentBox) commentBox.style.display = commentBox.style.display === "none" ? "block" : "none";
}

// ✅ CLEAN DROPDOWN TOGGLE
function toggleProfileDropdown() {
  const dropdown = document.getElementById("profileDropdown");
  if (!dropdown) return;

  dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}

// ✅ CLICK OUTSIDE TO CLOSE
document.addEventListener("click", function (e) {
  const dropdown = document.getElementById("profileDropdown");
  const avatar = document.getElementById("user-avatar");

  if (!dropdown || !avatar) return;

  if (!avatar.contains(e.target) && !dropdown.contains(e.target)) {
    dropdown.style.display = "none";
  }
});
</script>';

$gallery_html = '';
foreach ($user['gallery'] ?? [] as $photo) {
    $gallery_html .= '<div class="gallery-item"><img src="' . htmlspecialchars($photo) . '" alt="Gallery Photo" loading="lazy"></div>';
}

$cover_photo = $user['cover_photo'] ?? 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwMCIgaGVpZ2h0PSIzMDAiIHZpZXdCb3g9IjAgMCAxMjAwIDMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTIwMCIgaGVpZ2h0PSIzMDAiIGZpbGw9IiNlMWU4ZWQiLz48dGV4dCB4PSI2MDAiIHk9IjE2MCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzk5OSI+PGVtc3RydW0+PC90ZXh0Pjwvc3ZnPg==';
$profile_photo = $user['profile_photo'] ?? 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgdmlld0JveD0iMCAwIDEyMCAxMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iNjAiIGN5PSI2MCIgcj0iNjAiIGZpbGw9IiNkZGQiLz48L3N2Zz4=';

$friends_list = '';
foreach (array_slice($users, 0, 8) as $friend) {
    $avatar_url = $friend['profile_photo'] ?? '';
    $avatar_fallback = strtoupper(substr($friend['first_name'], 0, 1) . substr($friend['last_name'], 0, 1));
    $friends_list .= '
    <a href="profile.php?uid=' . $friend['id'] . '" class="user-card">
        <img src="' . $avatar_url . '" alt="' . htmlspecialchars($friend['first_name']) . '" class="user-card-avatar"
            onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\';">
        <div class="avatar-fallback" style="display:none;">' . $avatar_fallback . '</div>
        <div class="user-card-info">
            <h3>' . htmlspecialchars($friend['first_name'] . ' ' . $friend['last_name']) . '</h3>
        </div>
    </a>';
}

$sidebar_friends = $friends_list;

$photos_thumb = '';
foreach (($user['gallery'] ?? []) as $photo) {
    $photos_thumb .= '<div class="col-4"><img src="' . htmlspecialchars($photo) . '" class="w-100" style="height:60px; object-fit:cover; border-radius:8px;" loading="lazy"></div>';
}

$main_content = str_replace([
    '{COVER_PHOTO}',
    '{PROFILE_PHOTO}',
    '{USER_FULL_NAME}',
    '{USER_BIO}',
    '{USER_EMAIL}',
    '{EDIT_BUTTON}',
    '{GALLERY_HTML}',
    '{USER_POSTS_LIST}',
    '{FRIENDS_LIST}',
    '{SIDEBAR_FRIENDS}',
    '{PHOTOS_THUMB}'
], [
    $cover_photo,
    $profile_photo,
    $user_full_name,
    $user_bio,
    $user_email,
    $edit_button,
    $gallery_html,
    $user_posts_list,
    $friends_list,
    $sidebar_friends,
    $photos_thumb
], file_get_contents(__DIR__ . '/templates/profile.html'));


$replacements = [
    '{PAGE_TITLE}' => $page_title,
    '{PAGE_HEAD}' => '',
    '{MAIN_CONTENT}' => $main_content,
    '{AUTH_SECTION}' => $auth_section,
    '{MOBILE_AUTH}' => $mobile_auth,
    '{PROFILE_LINK}' => isset($_SESSION['user_id']) ? '?uid=' . $_SESSION['user_id'] : '',
    '{PAGE_SCRIPT}' => $page_script
];

$layout = file_get_contents(__DIR__ . '/templates/layout.html');
foreach ($replacements as $key => $value) {
    $layout = str_replace($key, $value, $layout);
}

echo $layout;
?>