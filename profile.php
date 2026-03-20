<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="homepage.php" class="navbar-brand">
            <img src="https://t3.ftcdn.net/jpg/03/46/83/96/360_F_346839683_6nAPzbhpSkIpb8pmAwufkC7c5eD7wYws.jpg" alt="Logo" width="32" height="32">
            Profile<span>App</span>
        </a>
        <div class="navbar-search">
            <input type="search" placeholder="Search profiles, companies...">
        </div>
        <div class="navbar-nav">
            <a href="homepage.php" class="nav-link" title="Home"><i class="fas fa-home"></i></a>
            <a href="profile.php" class="nav-link" title="Profile"><i class="fas fa-user"></i></a>
            <div class="user-avatar" title="David P">DP</div>
            <div class="hamburger" onclick="toggleMobileMenu()" title="Menu"><i class="fas fa-bars"></i></div>
        </div>
    </nav>
    <div style="height: 60px;"></div>
    <main class="main-container">
        <h1>Your Profile</h1>
        <p>Profile page content goes here. Navbar matches homepage.</p>
    </main>
</body>
</html>
