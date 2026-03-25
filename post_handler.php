<?php
session_start();

$data_dir = __DIR__ . '/data/';
$posts_file = $data_dir . 'posts.json';
$users_file = $data_dir . 'users.json';

/* ---------- LIKE ---------- */
if (isset($_GET['like'])) {
    $posts = file_exists($posts_file) ? json_decode(file_get_contents($posts_file), true) : [];
    $posts = $posts ?: [];

    foreach ($posts as &$p) {
        if ($p['id'] === $_GET['like']) {
            $p['likes'] = ($p['likes'] ?? 0) + 1;
            break;
        }
    }

    file_put_contents($posts_file, json_encode($posts, JSON_PRETTY_PRINT));
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

/* ---------- COMMENT ---------- */
if (isset($_POST['comment'], $_POST['post_id'])) {
    $posts = file_exists($posts_file) ? json_decode(file_get_contents($posts_file), true) : [];
    $posts = $posts ?: [];

foreach ($posts as &$p) {
        if ($p['id'] === $_POST['post_id']) {
            $p['comments'][] = [
                'user' => $_SESSION['user_name'] ?? 'Anonymous',
                'text' => htmlspecialchars(trim($_POST['comment']))
            ];
            break;  // Optional: limit to first match
        }
    }

    file_put_contents($posts_file, json_encode($posts, JSON_PRETTY_PRINT));
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

/* ---------- CREATE POST ---------- */
if (!isset($_SESSION['user_id'])) {
    header('Location: login_page.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $posts = file_exists($posts_file) ? json_decode(file_get_contents($posts_file), true) : [];
    $users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];

    $posts = $posts ?: [];
    $users = $users ?: [];

    $content = trim($_POST['content']);

    $user = null;
    foreach ($users as $u) {
        if ($u['id'] === $_SESSION['user_id']) {
            $user = $u;
            break;
        }
    }

    if (!$user) {
        header('Location: homepage.php');
        exit;
    }

    /* IMAGE */
    $image_path = '';
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir);

        $image_path = $target_dir . time() . '_' . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);
    }

    if (!empty($content)) {
        $posts[] = [
            'id' => uniqid(),
            'user_id' => $_SESSION['user_id'],
            'user_name' => $user['first_name'] . ' ' . $user['last_name'],
            'content' => substr($content, 0, 500),
            'created_at' => date('Y-m-d H:i:s'),
            'likes' => 0,
            'comments' => [],
            'image' => $image_path
        ];

        file_put_contents($posts_file, json_encode($posts, JSON_PRETTY_PRINT));
    }

    header('Location: homepage.php');
    exit;
}
?>