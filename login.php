<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FORTIROOM - A Smart Space Management System</title>
    <link rel="icon" href="images/FYP_Logo_small.png" type="image/icon type">
    <link rel="stylesheet" href="assets/assets_customer/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css">
</head>
<body>
<video autoplay loop muted plays-inline class="back-video">
    <source src="assets/assets_customer/img/login-bg-video.mp4" type="video/mp4">
</video>
<main>
    <div class="box">
        <div class="inner-box">
            <div class="forms-wrap">
                <form autocomplete="off" id="login" method="POST" action="login.php" class="sign-in-form">
                    <div class="logo"></div>
                    <div class="heading">
                        <h2>Welcome Back</h2>
                        <h6>Let's Get You Connected</h6>
                    </div>
                    <div class="actual-form">
                        <div class="input-wrap">
                            <input type="text" class="input-field" name="username" required />
                            <label>Username</label>
                        </div>
                        <div class="input-wrap">
                            <input type="password" class="input-field" name="password" id="password" required />
                            <label>Password</label>
                            <i class="fas fa-eye-slash toggle-password" data-target="password"></i>
                        </div>
                        <button type="submit" name="login_submit" class="sign-btn"><i class="fas fa-sign-in-alt"></i> Log In</button>
                        <p class="text">
                            Forgot Your Account Details?
                            <a class="data">Recover Here</a>
                        </p>
                    </div>
                    <button type="button" name="Go To Home" onclick="window.location.href = 'index.php';" class="sign-btn-main"><i class="fas fa-home"></i> Go To Home</button>
                </form>

                <form autocomplete="off" id="register" method="POST" action="login.php" class="sign-up-form">
                    <div class="logo"></div>
                    <div class="heading">
                        <h2>Get Started</h2>
                        <h6>Let's Give You Access</h6>
                    </div>
                    <div class="actual-form">
                        <div class="file-upload-wrap">
                            <input type="file" name="photo" id="photo_upload" class="file-input-hidden" accept="image/*" required/>
                            <button type="button" class="file-upload-label" id="file_upload_btn">
                                <i class="fas fa-camera"></i>
                                <span class="file-upload-text">Upload Your Photo</span>
                            </button>
                            <span class="file-name" id="file_name"></span>
                        </div>
                        <div class="input-wrap">
                            <input type="text" name="username" class="input-field" required/>
                            <label>Username</label>
                        </div>
                        <div class="input-wrap">
                            <input type="email" name="email" class="input-field" required />
                            <label>Email</label>
                        </div>
                        <div class="input-wrap">
                            <input type="password" name="password" class="input-field" minlength="8" id="password_register" required/>
                            <label>Password</label>
                            <span class="password-strength-text" id="strength_text_register"></span>
                            <i class="fas fa-eye-slash toggle-password" data-target="password_register"></i>
                        </div>
                        <button type="submit" name="register_submit" class="sign-btn" id="register_submit_btn"><i class="fas fa-user-plus"></i> Register</button>
                        <p class="text">
                            By registering, I agree to 
                            Terms of Services and
                            Privacy Policy of FORTIROOM
                        </p>
                    </div>
                    <button type="button" name="Go To Home" onclick="window.location.href = 'index.php';" class="sign-btn-main"><i class="fas fa-home"></i> Go to Home</button>
                </form>

                <form autocomplete="off" id="forgot" method="POST" action="login.php" class="forgot-password-form">
                    <div class="logo"></div>
                    <div class="heading">
                        <h2>Oops! Forgot Your Password?</h2>
                        <h6>Let's Regain Your Access</h6>
                    </div>
                    <div class="actual-form">
                        <div class="input-wrap">
                            <input type="email" class="input-field" name="email" required/>
                            <label>Email</label>
                        </div>
                        <button type="submit" name="reset_submit" class="sign-btn"><i class="fas fa-paper-plane"></i> Send Reset Link</button>
                        <p class="text">
                            By resetting, I agree to 
                            Terms of Services and
                            Privacy Policy of FORTIROOM
                        </p>
                    </div>
                    <button type="button" name="Go To Home" onclick="window.location.href = 'index.php';" class="sign-btn-main"><i class="fas fa-home"></i> Go To Home</button>
                </form>
            </div>
            <div class="carousel">
                <div class="carousel-container">
                    <div class="images-wrapper">
                        <img src="assets/img/FYP_logo.png" class="image img-1 show logo-centered"/>
                    </div>
                    <div class="text-slider">
                        <!-- Login carousel content (shown when on login form) -->
                        <div class="carousel-content login-carousel">
                            <h3>Not Registered Yet?</h3>
                            <p>Create an Account to Feel A Difference</p>
                            <button type="button" class="carousel-btn toggle-carousel register-btn"><i class="fas fa-user-plus"></i> Register Now</button>
                        </div>
                        <!-- Register carousel content (shown when on register form) -->
                        <div class="carousel-content register-carousel">
                            <h3>Have an Account?</h3>
                            <p>Login to Get Connected</p>
                            <button type="button" class="carousel-btn toggle-carousel login-btn"><i class="fas fa-sign-in-alt"></i> Login Now</button>
                        </div>
                        <!-- Forgot password carousel content -->
                        <div class="carousel-content forgot-carousel">
                            <h3>Changed Your Mind?</h3>
                            <p>Return to Where You Started</p>
                            <button type="button" class="carousel-btn" onclick="window.location.href = 'login.php';"><i class="fas fa-arrow-left"></i> Back to Login</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="assets/assets_customer/app.js"></script>
<script>
// Hardcoded credentials
const CREDENTIALS = {
    admin: {
        username: 'admin',
        password: 'admin123',
        redirect: 'staff/dashboard.php'
    },
    user: {
        username: 'marcus_chen87',
        password: 'User1234!',
        redirect: 'user/dashboard.php'
    }
};

// Password toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    // File upload handler
    const photoUpload = document.getElementById('photo_upload');
    const fileUploadBtn = document.getElementById('file_upload_btn');
    const fileName = document.getElementById('file_name');
    
    if (fileUploadBtn && photoUpload) {
        fileUploadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            photoUpload.click();
        });
    }
    
    if (photoUpload && fileName) {
        photoUpload.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                fileName.textContent = '✓ ' + this.files[0].name;
            } else {
                fileName.textContent = '';
            }
        });
    }
    
    const toggleIcons = document.querySelectorAll('.toggle-password');
    
    toggleIcons.forEach(icon => {
        icon.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            }
        });
    });
    
    // Login form validation
    const loginForm = document.getElementById('login');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = this.querySelector('input[name="username"]').value;
            const password = this.querySelector('input[name="password"]').value;
            
            // Check credentials
            if (username === CREDENTIALS.admin.username && password === CREDENTIALS.admin.password) {
                window.location.href = CREDENTIALS.admin.redirect;
            } else if (username === CREDENTIALS.user.username && password === CREDENTIALS.user.password) {
                window.location.href = CREDENTIALS.user.redirect;
            } else {
                alert('Invalid credentials. Please try again.');
            }
            
            return false;
        });
    }
    
    // Password validation for registration
    const passwordRegister = document.getElementById('password_register');
    if (passwordRegister) {
        const strengthContainer = document.getElementById('strength_container_register');
        
        passwordRegister.addEventListener('focus', function() {
            if (this.value !== '') {
                strengthContainer.style.display = 'block';
            }
        });
        
        passwordRegister.addEventListener('input', function() {
            updatePasswordStrength(this.value, 'register');
        });
    }
    
    // Registration form validation
    const registerForm = document.getElementById('register');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('password_register').value;
            const username = this.querySelector('input[name="username"]').value;
            const photo = this.querySelector('input[name="photo"]');
            const email = this.querySelector('input[name="email"]').value;
            
            if (!photo.files || photo.files.length === 0) {
                alert('Please upload your photo.');
                return false;
            }
            
            if (!isPasswordValid(password)) {
                alert('Password must be at least 8 characters and contain at least 1 uppercase letter, 1 number, and 1 symbol.');
                return false;
            }
            
            // Simulate successful registration
            alert('Registration successful!\n\nWelcome, ' + username + '!\nYou can now login with your credentials.');
            
            // Redirect to login form
            window.location.href = 'login.php';
            
            return false;
        });
    }
    
    // Forgot password form validation
    const forgotForm = document.getElementById('forgot');
    if (forgotForm) {
        forgotForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = this.querySelector('input[name="email"]').value;
            
            // Simulate email validation (check if email exists)
            // In a real app, this would check against a database
            const validEmails = ['marcus_chen87@helplive.edu.my'];
            
            if (validEmails.includes(email.toLowerCase())) {
                // Email found - show success message and redirect to reset page
                alert('Email found! Redirecting you to reset your password...');
                
                // Store email in sessionStorage for the reset page
                sessionStorage.setItem('resetEmail', email);
                
                // Redirect to reset.php
                window.location.href = 'reset.php';
            } else {
                // Email not found
                alert('Email not found. Please check your email address and try again.');
            }
            
            return false;
        });
    }
});

function updatePasswordStrength(password, suffix) {
    let strength = 0;
    const textEl = document.getElementById('strength_text_' + suffix);
    
    // Calculate strength based on requirements
    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) strength++;
    
    // Remove all strength classes
    textEl.classList.remove('weak', 'fair', 'good', 'strong');
    
    // Apply appropriate strength class and text
    if (strength === 1) {
        textEl.classList.add('weak');
        textEl.textContent = 'Weak';
    } else if (strength === 2) {
        textEl.classList.add('fair');
        textEl.textContent = 'Fair';
    } else if (strength === 3) {
        textEl.classList.add('good');
        textEl.textContent = 'Good';
    } else if (strength === 4) {
        textEl.classList.add('strong');
        textEl.textContent = 'Strong';
    } else {
        textEl.textContent = '';
    }
}

function isPasswordValid(password) {
    return password.length >= 8 &&
           /[A-Z]/.test(password) &&
           /[0-9]/.test(password) &&
           /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
}

// Prevent right-click on video
document.addEventListener('DOMContentLoaded', function() {
    const video = document.querySelector("video");
    if (video) {
        video.addEventListener("contextmenu", function(e) {
            e.preventDefault();
            return false;
        });
    }
});
</script>
</body>
</html>

