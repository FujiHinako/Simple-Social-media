<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProfileApp - Connect with Professionals</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script>
      function toggleMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        menu.classList.toggle('open');
      }
      
      document.addEventListener('click', function(e) {
        const hamburger = e.target.closest('.hamburger');
        const menu = document.getElementById('mobileMenu');
        if (!hamburger && menu.classList.contains('open')) {
          toggleMobileMenu();
        }
      });
    </script>
</head>
<body>
    <nav class="navbar">
        <a href="homepage.php" class="navbar-brand">
            <img src="https://t3.ftcdn.net/jpg/03/46/83/96/360_F_346839683_6nAPzbhpSkIpb8pmAwufkC7c5eD7wYws.jpg" alt="Logo">
            Profile<span>App</span>
        </a>
        <div class="navbar-search">
            <input type="search" placeholder="Search profiles...">
        </div>
        <div class="navbar-nav">
            <a href="homepage.php" class="nav-link" title="Home"><i class="fas fa-home"></i></a>
            <a href="profile.php" class="nav-link" title="Profile"><i class="fas fa-user"></i></a>
            <div class="user-avatar" title="David">DP</div>
            <div class="hamburger" onclick="toggleMobileMenu()" title="Menu"><i class="fas fa-bars"></i></div>
        </div>
        <div class="mobile-menu" id="mobileMenu">
            <a href="homepage.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
            <a href="profile.php" class="nav-link"><i class="fas fa-user"></i> Profile</a>
            <div class="user-avatar" onclick="toggleMobileMenu()">DP</div>
        </div>
    </nav>

    <main class="main-content">
        <div class="split-container">
            <!-- LEFT: HERO + FEED -->
            <section class="hero-column">
                <h1>Welcome to ProfileApp</h1>
                <p class="subtitle">Connect with professionals and grow your network</p>
                <button class="btn-signup-main">Join Now</button>
                
                <h3>Recent Activity</h3>
                <div class="card-deck">
                    <a href="profile.php" class="stretched-link">
                        <div class="card">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=320&h=160&fit=crop" alt="Profile preview">
                            <div class="card-body">
                                <h3 class="card-sub">Sarah Johnson</h3>
                                <p class="time-card">Updated profile • 2h ago</p>
                            </div>
                        </div>
                    </a>
                </div>
            </section>

            <!-- RIGHT: SIGNUP FORM -->
            <aside class="form-column">
                <div class="signup-card">
                    <h2>Create Account</h2>
                    <form>
                        <label>Full Name</label>
                        <input type="text" placeholder="Your name">
                        
                        <label>Email</label>
                        <input type="email" placeholder="your@email.com">
                        
                        <label>Password</label>
                        <input type="password" placeholder="Create password">
                        
                        <button type="submit" class="btn-signup-main">Sign Up</button>
                        <button type="button" class="btn-social">Sign up with Google</button>
                    </form>
                </div>
            </aside>
        </div>
    </main>
</body>
