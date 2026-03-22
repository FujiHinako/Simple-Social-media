<?php
session_start();

$data_dir = __DIR__ . '/data/';
$users_file = $data_dir . 'users.json';

$users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];
$users = $users ?: [];

if ($_POST['action'] === 'login') {

    foreach ($users as $user) {
        if ($user['email'] === $_POST['email'] &&
            password_verify($_POST['password'], $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];

            header('Location: homepage.php');
            exit;
        }
    }

    echo "Invalid login";

} elseif ($_POST['action'] === 'signup') {

    $new_user = [
        'id' => uniqid(),
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'password' => password_hash($_POST['password'], PASSWORD_DEFAULT)
    ];

    $users[] = $new_user;
    file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));

    $_SESSION['user_id'] = $new_user['id'];
    $_SESSION['user_name'] = $new_user['first_name'] . ' ' . $new_user['last_name'];

    header('Location: homepage.php');
    exit;
}
?>