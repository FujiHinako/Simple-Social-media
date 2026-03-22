<?php
// Redirect to handler for GET requests, show login page
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get error from query param
    $error = '';
    if (isset($_GET['error'])) {
        switch ($_GET['error']) {
            case 'invalid': $error = 'Invalid email or password'; break;
            case 'empty': $error = 'Please fill all required fields'; break;
            case 'pass_short': $error = 'Password must be at least 6 characters'; break;
            case 'email_exists': $error = 'Email already exists'; break;
        }
    }
    
    $error_html = $error ? '<div class="error-msg">' . htmlspecialchars($error) . '</div>' : '';
    
    $is_logged_in = isset($_SESSION['user_id']);
    $user_name = $_SESSION['user_name'] ?? '';
    $user_avatar = $user_name ? strtoupper(substr($user_name, 0, 2)) : 'DP';
    
    $auth_section = $is_logged_in ? '
        <button onclick="logout()" class="auth-btn btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</button>
        <div class="user-avatar" title="' . htmlspecialchars($user_name) . '">' . $user_avatar . '</div>' : '
        <a href="login.php" class="auth-btn btn-login"><i class="fas fa-sign-in-alt"></i> Login</a>';

    $page_title = 'Login - ProfileApp';
    $main_content = str_replace('{ERROR_MSG}', $error_html, file_get_contents(__DIR__ . '/templates/login.html'));
    
    $replacements = [
        '{PAGE_TITLE}' => $page_title,
        '{PAGE_HEAD}' => '',
        '{MAIN_CONTENT}' => $main_content,
        '{AUTH_SECTION}' => $auth_section,
        '{MOBILE_AUTH}' => $is_logged_in ? '<div class="user-avatar">' . $user_avatar . '</div>' : '<a href="login.php" class="auth-btn btn-login"><i class="fas fa-sign-in-alt"></i></a>',
        '{PROFILE_LINK' => '',
        '{PAGE_SCRIPT}' => ''
    ];
    
    $layout = file_get_contents(__DIR__ . '/templates/layout.html');
    foreach ($replacements as $key => $value) {
        $layout = str_replace($key, $value, $layout);
    }
    
    echo $layout;
    exit;
}

// POST handled by login_handler.php
header('Location: login_handler.php' . $_SERVER['REQUEST_URI']);
?>

