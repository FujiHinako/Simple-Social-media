<?php
session_start();

$user_id = $_GET['uid'] ?? $_GET['user'] ?? null;
if ($user_id !== null) {
    $user_id = (int)$user_id; // Ensure user_id is integer for proper comparison
}
if (!isset($_SESSION['user_id']) && !$user_id) {
    header('Location: login.php?from=profile');
    exit;
}
if (!$user_id) {
    header('Location: homepage.php');
    exit;
}

// Files
$data_dir = __DIR__ . '/data/';
$users_file = $data_dir . 'users.json';
$posts_file = $data_dir . 'posts.json';

// Load Users
$users = [];
if (file_exists($users_file)) {
    $json = file_get_contents($users_file);
    $users = $json ? json_decode($json, true) : [];
}

// Find user
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

// Load posts
$posts = [];
if (file_exists($posts_file)) {
    $json = file_get_contents($posts_file);
    $posts = $json ? json_decode($json, true) : [];
}
$user_posts = array_reverse(array_filter($posts, fn($p) => $p['user_id'] === $user_id));

$is_owner = isset($_SESSION['user_id']) && $_SESSION['user_id'] === $user_id;

// User Info
$user_full_name = htmlspecialchars($user['first_name'] . ($user['middle_name'] ? ' ' . $user['middle_name'] : '') . ' ' . $user['last_name']);
$user_email = htmlspecialchars($user['email']);
$user_bio = htmlspecialchars($user['bio'] ?? 'No biography available.');
$edit_button = $is_owner ? '<a href="edit_profile.php" class="btn-edit">Edit Profile</a>' : '';

// User Posts HTML
$user_posts_list = '';
if (empty($user_posts)) {
    $user_posts_list = '<p>No posts yet. ' . ($is_owner ? '<a href="homepage.php">Create some posts!</a>' : '') . '</p>';
} else {
    foreach ($user_posts as $post) {
        $image_html = !empty($post['image']) ? '<img src="' . htmlspecialchars($post['image']) . '" alt="Post image" class="img-fluid rounded mt-3 post-image" style="max-height:400px; object-fit:cover;">' : '';
        $user_post_avatar = strtoupper(substr($post['user_name'], 0, 2));

        // Build comments list HTML
        $comments_html = '';
        foreach (($post['comments'] ?? []) as $comment) {
            $comments_html .= '
        <div class="comment-item mb-2 p-2 bg-light rounded">
            <small class="fw-bold text-primary">' . htmlspecialchars($comment['user']) . '</small>
            <div>' . nl2br(htmlspecialchars($comment['text'])) . '</div>
        </div>';
        }

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
}

// User session info for navbar
$user_name = $_SESSION['user_name'] ?? 'Guest';
$user_session_avatar = strtoupper(substr($user_name, 0, 1));

$auth_section = isset($_SESSION['user_id']) ? '
<div class="user-avatar" id="user-avatar" title="' . htmlspecialchars($user_name) . '" onclick="toggleProfileDropdown()">' . $user_session_avatar . '</div>
<div id="profileDropdown" style="display:none; position:absolute; top:60px; right:1rem; background:white; box-shadow:0 8px 25px rgba(0,0,0,0.15); border-radius:12px; z-index: 1000;">
    <a href="profile.php?uid=' . $_SESSION['user_id'] . '&tab=posts" style="display:block; padding:12px 20px; color:#333; text-decoration:none;">View Profile</a>
    <button onclick="logout()" style="border:none; background:none; width:100%; text-align:left; padding:12px 20px; color:#e74c3c; cursor:pointer;">Logout</button>
</div>' : '
<a href="login.php" class="auth-btn btn-login btn-account"><i class="fas fa-user-circle"></i> Sign In / Up</a>';

$mobile_auth = isset($_SESSION['user_id']) ? '<button onclick="logout()" class="auth-btn btn-logout">Logout</button>' : '';

// Profile Media
$default_cover = 'https://via.placeholder.com/1200x400?text=Cover+Photo';
$default_profile = 'https://via.placeholder.com/200?text=Profile+Photo';

function resolveImagePath($path, $fallback) {
    if (empty($path)) {
        return $fallback;
    }

    $path = str_replace('\\', '/', trim($path));

    // Full URL path
    if (filter_var($path, FILTER_VALIDATE_URL)) {
        return $path;
    }

    // Local path in project; verify file exists on disk
    $localFile = __DIR__ . '/' . ltrim($path, '/');
    if (file_exists($localFile)) {
        return $path;
    }

    // fallback to placeholder
    return $fallback;
}

$cover_photo = resolveImagePath($user['cover_photo'] ?? '', $default_cover);
$profile_photo = resolveImagePath($user['profile_photo'] ?? '', $default_profile);

// Gallery HTML
$gallery_html = '';
foreach ($user['gallery'] ?? [] as $photo) {
    $gallery_html .= '<div class="gallery-item"><img src="' . htmlspecialchars($photo) . '" alt="Gallery Photo" loading="lazy"></div>';
}

// Image Posts HTML - Posts with images from the posts tab
$image_posts_html = '';
$image_posts = array_filter($user_posts, fn($p) => !empty($p['image']));
if (!empty($image_posts)) {
    foreach ($image_posts as $post) {
        $image_posts_html .= '<div class="gallery-item"><img src="' . htmlspecialchars($post['image']) . '" alt="Post from ' . htmlspecialchars($post['user_name']) . '" loading="lazy" title="' . htmlspecialchars($post['content']) . '" class="post-image-thumb"></div>';
    }
}

// Combined Photos HTML (gallery + image posts)
$combined_photos_html = $gallery_html . $image_posts_html;

// Friends
$friends_list = '';
foreach (array_slice($users, 0, 8) as $friend) {
    $avatar_url = $friend['profile_photo'] ?? '';
    $avatar_fallback = strtoupper(substr($friend['first_name'], 0, 1) . substr($friend['last_name'], 0, 1));
    $friends_list .= '
    <a href="profile.php?uid=' . $friend['id'] . '" class="user-card">
        <img src="' . $avatar_url . '" alt="' . htmlspecialchars($friend['first_name']) . '" class="user-card-avatar" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\';">
        <div class="avatar-fallback" style="display:none;">' . $avatar_fallback . '</div>
        <div class="user-card-info"><h3>' . htmlspecialchars($friend['first_name'] . ' ' . $friend['last_name']) . '</h3></div>
    </a>';
}

// MAIN CONTENT
$main_content = '
<div class="profile-page">
    <div class="profile-cover" style="background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url(' . $cover_photo . ');"></div>
    <div class="profile-header-overlay">
        <img src="' . $profile_photo . '" alt="Profile Photo" class="profile-photo">
        <div class="profile-info">
            <h1>' . $user_full_name . '</h1>
            <p class="profile-bio">' . $user_bio . '</p>
            <p class="profile-email">' . $user_email . '</p>
            ' . $edit_button . '
        </div>
    </div>

    <div class="profile-tabs">
        <div class="profile-tab active" data-tab="posts" onclick="showTab(\'posts\')">Posts</div>
        <div class="profile-tab" data-tab="photos" onclick="showTab(\'photos\')">Photos</div>
        <div class="profile-tab" data-tab="friends" onclick="showTab(\'friends\')">Friends</div>
    </div>

    <div class="tab-content-wrapper">
        <div class="tab-content active" id="posts">' . $user_posts_list . '</div>
        <div class="tab-content" id="photos"><div class="gallery-grid">' . $gallery_html . '</div></div>
        <div class="tab-content" id="friends"><div class="users-grid">' . $friends_list . '</div></div>
    </div>
</div>';

// SCRIPT
$page_script = <<<'EOD'
<script>
function logout() {
  fetch("logout.php", {method:"POST"}).then(()=>location.reload());
}

function likePost(postId) {
  fetch("post_handler.php?like=" + postId)
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        const btn = document.querySelector('.like-btn[data-post-id="' + postId + '"]');
        if (btn) {
          const match = btn.textContent.match(/\((\d+)\)/);
          const currentLikes = match ? parseInt(match[1]) : 0;
          btn.textContent = btn.textContent.replace(/\(\d+\)/, "(" + (currentLikes + 1) + ")");
        }
      }
    })
    .catch(function(error) { console.error(error); });
}

function toggleProfileDropdown() {
  const dropdown = document.getElementById("profileDropdown");
  if(dropdown) dropdown.style.display = dropdown.style.display==="block"?"none":"block";
}

document.addEventListener("click",function(e){
  const dropdown=document.getElementById("profileDropdown");
  const avatar=document.getElementById("user-avatar");
  if(!dropdown||!avatar) return;
  if(!avatar.contains(e.target)&&!dropdown.contains(e.target)) dropdown.style.display="none";
});

function showTab(tabId){
  document.querySelectorAll(".tab-content").forEach(t=>t.classList.remove("active"));
  document.querySelectorAll(".profile-tab").forEach(t=>t.classList.remove("active"));
  const content = document.getElementById(tabId + '-tab');
  const tab = document.querySelector(".profile-tab[data-tab='" + tabId + "']");
  if(content) content.classList.add("active");
  if(tab) tab.classList.add("active");
}

function bindProfileTabEvents(){
  const tabs = document.querySelectorAll('.profile-tab');
  tabs.forEach(tab=>{
    tab.addEventListener('click', function(e){
      e.preventDefault();
      const id = this.getAttribute('data-tab');
      if(id) showTab(id);
    });
  });
}

function getInitialTab() {
  const urlParams = new URLSearchParams(window.location.search);
  const requested = urlParams.get('tab');
  const validTabs = ['posts', 'photos', 'friends'];
  if (requested && validTabs.includes(requested)) {
    return requested;
  }
  return 'posts';
}

if(document.readyState === 'loading'){
  document.addEventListener('DOMContentLoaded', function(){
    bindProfileTabEvents();
    showTab(getInitialTab());
  });
}else{
  bindProfileTabEvents();
  showTab(getInitialTab());
}
</script>
EOD;

// PAGE TITLE
$page_title = $user_full_name;

// LAYOUT
$layout = file_get_contents(__DIR__ . '/templates/layout.html');
$profile_content = str_replace(
  ['{COVER_PHOTO}', '{PROFILE_PHOTO}', '{USER_FULL_NAME}', '{USER_BIO}', '{USER_EMAIL}', '{EDIT_BUTTON}', '{USER_POSTS_LIST}', '{GALLERY_HTML}', '{FRIENDS_LIST}'],
  [$cover_photo, $profile_photo, $user_full_name, $user_bio, $user_email, $edit_button, $user_posts_list, $combined_photos_html, $friends_list],
  file_get_contents(__DIR__ . '/templates/profile-fixed.html')
);

$main_content = $profile_content;
$profileLink = isset($_SESSION['user_id']) ? '?uid=' . $_SESSION['user_id'] . '&tab=posts' : '?tab=posts';
$replacements = [
    '{PAGE_TITLE}' => $page_title,
    '{PAGE_HEAD}' => '',
    '{MAIN_CONTENT}' => $main_content,
    '{AUTH_SECTION}' => $auth_section,
    '{MOBILE_AUTH}' => $mobile_auth,
    '{PROFILE_LINK}' => $profileLink,
    '{PAGE_SCRIPT}' => $page_script
];

foreach($replacements as $key=>$value){
    $layout = str_replace($key,$value,$layout);
}

echo $layout;
?>