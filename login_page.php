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

    <input type="email" name="email" required>
    <input type="password" name="password" required>

    <button type="submit">Login</button>
</form>