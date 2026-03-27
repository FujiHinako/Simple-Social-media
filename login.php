<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ProfileApp</title>
    <!-- Bootstrap + MDB -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/mdb-ui-kit@7.0.0/dist/css/mdb.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mdb-ui-kit@7.0.0/dist/js/mdb.umd.min.js"></script>
</head>
<body class="bg-light">
    <?php
      $error = $_GET['error'] ?? '';
      $email = htmlspecialchars($_GET['email'] ?? '', ENT_QUOTES, 'UTF-8');
    ?>
    <!-- Main container -->
    <div class="container py-5">
        <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <!-- Card -->
                <div class="card shadow-5-strong style="border-radius: 15px;">
                    <!-- Header -->
                    <div class="card-header p-4 bg-gradient text-blue rounded-top">
                        <h3 class="mb-0"><i class="fas fa-user-circle me-2"></i>ProfileApp</h3>
                    </div>
                    
                    <!-- Login form -->
                    <div class="card-body p-5">
                      
                        
                        <!-- Login tab content -->
                        <form id="loginForm" method="POST" action="login_handler.php" class="needs-validation" novalidate>
                            <input type="hidden" name="action" value="login">
                            <?php if($error === 'invalid'): ?>
                              <div class="alert alert-danger">Invalid email or password.</div>
                            <?php endif; ?>
                            <div class="form-outline mb-4">
                                <input type="email" id="loginEmail" class="form-control form-control-lg <?php echo ($error === 'invalid' ? 'is-invalid' : ''); ?>" name="email" required value="<?php echo $email; ?>" />
                                <label class="form-label" for="loginEmail">Email address</label>
                            </div>

                            <div class="form-outline mb-4">
                                <input type="password" id="loginPassword" class="form-control form-control-lg <?php echo ($error === 'invalid' ? 'is-invalid' : ''); ?>" name="password" required />
                                <label class="form-label" for="loginPassword">Password</label>
                            </div>

                            <div class="d-flex justify-content-around align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="remember" />
                                    <label class="form-check-label" for="remember">
                                        Remember me
                                    </label>
                                </div>
                                <a href="#!" class="text-body">Forgot password?</a>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg btn-block w-100 gradient-custom mb-3">
                                Sign in
                            </button>

                            <p class="text-center text-muted mt-3 mb-0">
                                Don't have account? 
                                <a href="#signup" class="fw-bold text-body switch-tab">Sign up here</a>
                            </p>
                        </form>

                        <!-- Signup tab content -->
                        <form id="signupForm" method="POST" action="login_handler.php" class="needs-validation" novalidate style="display: none;">
                            <input type="hidden" name="action" value="signup">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="form-outline">
                                        <input type="text" id="firstName" class="form-control form-control-lg" name="first_name" required />
                                        <label class="form-label" for="firstName">First name</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-outline">
                                        <input type="text" id="lastName" class="form-control form-control-lg" name="last_name" required />
                                        <label class="form-label" for="lastName">Last name</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-outline mb-4">
                                <input type="email" id="signupEmail" class="form-control form-control-lg" name="email" required />
                                <label class="form-label" for="signupEmail">Email</label>
                            </div>
                            <div class="form-outline mb-4">
                                <input type="password" id="signupPassword" class="form-control form-control-lg" name="password" minlength="6" required />
                                <label class="form-label" for="signupPassword">Password (min 6 chars)</label>
                            </div>
                            <div class="form-outline mb-4">
                                <input type="password" id="signupConfirm" class="form-control form-control-lg" name="confirm_password" required />
                                <label class="form-label" for="signupConfirm">Confirm Password</label>
                            </div>
                            <div class="form-check d-flex justify-content-center mb-4">
                                <input class="form-check-input me-2" type="checkbox" value="" id="agree" required />
                                <label class="form-check-label" for="agree">
                                    I agree all statements in <a href="#!" class="text-body"><u>Terms of service</u></a>
                                </label>
                            </div>
                            <div id="passwordMatch" class="alert alert-danger" style="display: none;">
                                Passwords don't match!
                            </div>
                            <button type="submit" class="btn btn-success btn-lg btn-block w-100 gradient-custom">
                                Register
                            </button>
                            <p class="text-center text-muted mt-3 mb-0">
                                Have already account? 
                                <a href="#login" class="fw-bold text-body switch-tab">Sign in here</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab switching
        document.querySelectorAll('.switch-tab').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const target = link.getAttribute('href');
                if (target === '#signup') {
                    document.getElementById('loginForm').style.display = 'none';
                    document.getElementById('signupForm').style.display = 'block';
                } else {
                    document.getElementById('signupForm').style.display = 'none';
                    document.getElementById('loginForm').style.display = 'block';
                }
            });
        });

        // Password validation
        document.getElementById('signupConfirm').addEventListener('input', function() {
            const pass1 = document.getElementById('signupPassword').value;
            const pass2 = this.value;
            const alert = document.getElementById('passwordMatch');
            if (pass1 !== pass2) {
                alert.style.display = 'block';
            } else {
                alert.style.display = 'none';
            }
        });

        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>
</body>
</html>

