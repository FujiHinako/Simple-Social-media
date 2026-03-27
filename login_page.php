<?php
$error = $_GET['error'] ?? '';
$email = htmlspecialchars($_GET['email'] ?? '', ENT_QUOTES, 'UTF-8');
?>

<style>
.login-error { background: #ffe8e8; color: #b00; border: 1px solid #f5a5a5; padding: 10px 12px; border-radius: 8px; margin-bottom: 12px; }
.input-error { border: 1px solid #e52f2f; box-shadow: 0 0 0 1px rgba(229,47,47,.4); }
</style>

<form method="POST" action="login_handler.php">
    <input type="hidden" name="action" value="signup">

    <input type="text" name="first_name" placeholder="First name" required>
    <input type="text" name="last_name" placeholder="Last name" required>
    <input type="email" name="email" required>
    <input type="password" name="password" required>

    <button type="submit">Sign Up</button>
</form>

<hr>

<form method="POST" action="login_handler.php">
    <input type="hidden" name="action" value="login">

    <?php if ($error === 'invalid'): ?>
      <div class="login-error">Invalid email or password. Please try again.</div>
    <?php endif; ?>

    <input type="email" name="email" value="<?php echo $email; ?>" placeholder="Email" required <?php echo $error === 'invalid' ? 'class="input-error"' : ''; ?> >
    <input type="password" name="password" placeholder="Password" required <?php echo $error === 'invalid' ? 'class="input-error"' : ''; ?> >

    <button type="submit">Login</button>
</form>