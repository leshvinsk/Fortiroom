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
                <form autocomplete="off" id="resetForm" method="POST" class="sign-in-form" novalidate>
                    <div class="logo"></div>
                    <div class="heading">
                    <h2>Reset Your Password</h2>
                    <h6>Let's Regain Your Access</h6>
                    </div>
                    <div class="actual-form">
                        <div class="input-wrap">
                            <input type="password" class="input-field" name="password" id="new_password" />
                            <label>New Password</label>
                            <span class="password-strength-text" id="strength_text_new"></span>
                            <i class="fas fa-eye-slash toggle-password" data-target="new_password"></i>
                        </div>
                        <div class="input-wrap">
                            <input type="password" class="input-field" name="password_confirmation" id="password_confirmation" />
                            <label>Confirm Password</label>
                            <i class="fas fa-eye-slash toggle-password" data-target="password_confirmation"></i>
                        </div>
                        <button type="submit" name="reset_password_submit" class="sign-btn" id="reset_submit_btn"><i class="fas fa-key"></i> Reset Password</button>
                    </div>
                    <p class="text">
                        By resetting, I agree to Terms of Services and Privacy Policy of FORTIROOM
                    </p>
                    <button type="button" name="Go To Home" onclick="window.location.href = 'index.php';" class="sign-btn-main"><i class="fas fa-home"></i> Go To Home</button>
                </form>
            </div>
            <div class="carousel">
                <div class="carousel-container">
                    <div class="images-wrapper">
                        <img src="assets/img/FYP_Logo.png" class="image img-1 show logo-centered"/>
                    </div>
                    <div class="text-slider">
                        <div class="carousel-content">
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
    // Password toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
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
        
        // Check if user came from forgot password page
        const resetEmail = sessionStorage.getItem('resetEmail');
        if (!resetEmail) {
            // If no email in session, redirect to login
            alert('Invalid access. Please use the forgot password link.');
            window.location.href = 'login.php';
            return;
        }
        
        // Password validation for new password
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('password_confirmation');
        
        if (newPassword) {
            newPassword.addEventListener('input', function() {
                updatePasswordStrength(this.value, 'new');
            });
        }
        
        // Reset form validation
        const resetForm = document.getElementById('resetForm');
        if (resetForm) {
            resetForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const password = document.getElementById('new_password').value.trim();
                const confirmPass = document.getElementById('password_confirmation').value.trim();
                
                // Check if passwords are empty
                if (!password || !confirmPass) {
                    alert('Please fill in both password fields.');
                    return false;
                }
                
                // Check password length
                if (password.length < 8) {
                    alert('Password must be at least 8 characters long.');
                    return false;
                }
                
                // Check for uppercase letter
                if (!/[A-Z]/.test(password)) {
                    alert('Password must contain at least 1 uppercase letter.');
                    return false;
                }
                
                // Check for number
                if (!/[0-9]/.test(password)) {
                    alert('Password must contain at least 1 number.');
                    return false;
                }
                
                // Check for special character
                if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
                    alert('Password must contain at least 1 special character (!@#$%^&* etc).');
                    return false;
                }
                
                // Check if passwords match
                if (password !== confirmPass) {
                    alert('Passwords do not match.');
                    return false;
                }
                
                // Password reset successful
                alert('Password reset successful!\n\nYou can now login with your new password.');
                
                // Clear the session storage
                sessionStorage.removeItem('resetEmail');
                
                // Redirect to login page
                window.location.href = 'login.php';
                
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

    // JavaScript for preventing right-click on video
    document.addEventListener('contextmenu', function(event) {
        event.preventDefault();
    });
    
    // JavaScript to handle toggle link
    const toggleElement = document.querySelector('.toggle');
    if (toggleElement) {
        toggleElement.addEventListener('click', function(event) {
            event.preventDefault();
            window.location.href = 'login.php';
        });
    }
</script>
</body>
</html>
