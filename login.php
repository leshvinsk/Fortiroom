<?php
// Minimal .env loader (no external deps). Loads KEY=VALUE pairs into $_ENV.
$envPath = __DIR__ . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(ltrim($line), '#') === 0) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            // Remove optional surrounding quotes
            $len = strlen($value);
            if ($len >= 2 && (($value[0] === '"' && $value[$len - 1] === '"') || ($value[0] === "'" && $value[$len - 1] === "'"))) {
                $value = substr($value, 1, $len - 2);
            }            
            $_ENV[$key] = $value;
        }
    }
}
$SUPABASE_URL = $_ENV['SUPABASE_URL'] ?? '';
$SUPABASE_ANON_KEY = $_ENV['SUPABASE_ANON_KEY'] ?? '';

// Optional: auto-provision admin account on server-side (never expose service key client-side)
$SUPABASE_SERVICE_KEY = $_ENV['SUPABASE_SERVICE_KEY'] ?? '';
$ADMIN_EMAIL = $_ENV['ADMIN_EMAIL'] ?? '';
$ADMIN_PASSWORD = $_ENV['ADMIN_PASSWORD'] ?? '';

if ($SUPABASE_URL && $SUPABASE_SERVICE_KEY && $ADMIN_EMAIL && $ADMIN_PASSWORD && function_exists('curl_init')) {
    // Robust create-or-exist: try creating every time; if already exists, Supabase returns 422/409 which we ignore.
    $createUrl = rtrim($SUPABASE_URL, '/') . '/auth/v1/admin/users';
    $payload = json_encode([
        'email' => $ADMIN_EMAIL,
        'password' => $ADMIN_PASSWORD,
        'email_confirm' => true,
        'user_metadata' => ['role' => 'admin']
    ]);
    $ch2 = curl_init($createUrl);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_POST, true);
    curl_setopt($ch2, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch2, CURLOPT_HTTPHEADER, [
        'apikey: ' . $SUPABASE_SERVICE_KEY,
        'Authorization: Bearer ' . $SUPABASE_SERVICE_KEY,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch2, CURLOPT_TIMEOUT, 8);
    $resp2 = curl_exec($ch2);
    $code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    curl_close($ch2);
    // Treat 200-299 as created, and 409/422 as "already exists"
    if (!($code2 >= 200 && $code2 < 300) && !in_array($code2, [409, 422], true)) {
        error_log('Supabase admin ensure failed (HTTP ' . $code2 . '). Response: ' . substr((string)$resp2, 0, 500));
    }
}
?>
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
    <!-- Supabase JS v2 -->
    <script defer src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    <script>
        // Injected from server-side env. The anon key is safe to expose client-side.
        window.__SUPABASE__ = {
            url: "<?php echo htmlspecialchars($SUPABASE_URL, ENT_QUOTES, 'UTF-8'); ?>",
            anonKey: "<?php echo htmlspecialchars($SUPABASE_ANON_KEY, ENT_QUOTES, 'UTF-8'); ?>"
        };
    </script>
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
                            <input type="email" class="input-field" name="email" required />
                            <label>Email</label>
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
                            <input type="file" name="photo" id="photo_upload" class="file-input-hidden" accept="image/jpeg,image/jpg,image/png,image/gif" required/>
                            <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                                <button type="button" class="file-upload-label" id="file_upload_btn">
                                    <i class="fas fa-camera"></i>
                                    <span class="file-upload-text">Upload Your Photo</span>
                                </button>
                                <button type="button" class="file-clear-btn" id="file_clear_btn" style="display: none;" title="Clear selected photo">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <span class="file-name" id="file_name"></span>
                            <p style="margin-top: 8px; font-size: 11px; color: #6c757d; text-align: center;">
                                Max size: 10MB 
                            </p>
                        </div>
                        <div class="input-wrap">
                            <input type="text" name="username" class="input-field" id="username_register" required/>
                            <label>Username</label>
                            <span class="username-check-text" id="username_check_text" style="display: none; font-size: 11px; position: absolute; bottom: -18px; left: 0; color: #6c757d;"></span>
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
// Check for confirmation tokens IMMEDIATELY (before DOMContentLoaded)
// This runs synchronously to catch tokens before Supabase auto-processes them
(function() {
    const hash = window.location.hash.startsWith('#') ? window.location.hash.slice(1) : window.location.hash;
    const hashParams = new URLSearchParams(hash);
    const queryParams = new URLSearchParams(window.location.search);
    
    const hasConfirmationToken = hashParams.get('type') === 'signup' || 
                                hashParams.get('access_token') || 
                                hashParams.get('token_hash') ||
                                queryParams.get('token') ||
                                queryParams.get('code');
    
    // Store flag for later use
    window.__HANDLE_CONFIRMATION__ = hasConfirmationToken;
})();

// Password toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Supabase
    const { createClient } = window.supabase || {};
    if (!createClient) {
        console.error('Supabase library failed to load.');
    }
    const supabase = createClient(window.__SUPABASE__.url, window.__SUPABASE__.anonKey);

    // Handle logout query param before any session redirect checks
    (async () => {
        try {
            const params = new URLSearchParams(window.location.search);
            if (params.get('logout') === '1') {
                await supabase.auth.signOut();
                // Clean the URL (remove ?logout=1) without reloading
                if (window.history && window.history.replaceState) {
                    const url = window.location.pathname + window.location.hash;
                    window.history.replaceState({}, document.title, url);
                }
            }
        } catch (e) {
            // ignore
        }
    })();

    // Handle email confirmation link - confirm email but don't auto-login
    // Use auth state listener to catch when Supabase auto-creates a session
    let confirmationHandled = false;
    
    if (window.__HANDLE_CONFIRMATION__) {
        // Set up auth state listener to catch auto-created sessions
        supabase.auth.onAuthStateChange(async (event, session) => {
            if (confirmationHandled) return;
            
            // If a session was created and we have confirmation tokens, sign out immediately
            if (event === 'SIGNED_IN' && session && window.__HANDLE_CONFIRMATION__) {
                confirmationHandled = true;
                
                // Sign out immediately
                await supabase.auth.signOut();
                
                // Clean the URL
                if (window.history && window.history.replaceState) {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
                
                // Show success message
                alert('Email confirmed successfully! Please log in with your credentials.');
                
                // Clear the flag
                window.__HANDLE_CONFIRMATION__ = false;
            }
        });
        
        // Also check immediately in case session already exists
        (async () => {
            try {
                // Wait a moment for Supabase to process hash tokens
                await new Promise(resolve => setTimeout(resolve, 100));
                
                const { data: sessionData } = await supabase.auth.getSession();
                
                if (sessionData?.session && window.__HANDLE_CONFIRMATION__ && !confirmationHandled) {
                    confirmationHandled = true;
                    
                    // Sign out immediately
                    await supabase.auth.signOut();
                    
                    // Clean the URL
                    if (window.history && window.history.replaceState) {
                        window.history.replaceState({}, document.title, window.location.pathname);
                    }
                    
                    // Show success message
                    alert('Email confirmed successfully! Please log in with your credentials.');
                    
                    // Clear the flag
                    window.__HANDLE_CONFIRMATION__ = false;
                } else if (!sessionData?.session && window.__HANDLE_CONFIRMATION__ && !confirmationHandled) {
                    // No session yet, but we have confirmation tokens - process them manually
                    const hash = window.location.hash.startsWith('#') ? window.location.hash.slice(1) : window.location.hash;
                    const hashParams = new URLSearchParams(hash);
                    const queryParams = new URLSearchParams(window.location.search);
                    
                    try {
                        if (queryParams.get('code')) {
                            // PKCE flow
                            await supabase.auth.exchangeCodeForSession(window.location.href);
                        } else if (hashParams.get('token_hash') && hashParams.get('type') === 'signup') {
                            // OTP verification for signup
                            await supabase.auth.verifyOtp({ 
                                type: 'signup', 
                                token_hash: hashParams.get('token_hash') 
                            });
                        } else if (hashParams.get('access_token') && hashParams.get('refresh_token')) {
                            // Direct token in hash
                            await supabase.auth.setSession({ 
                                access_token: hashParams.get('access_token'), 
                                refresh_token: hashParams.get('refresh_token') 
                            });
                        }
                        
                        // Wait a moment for session to be created
                        await new Promise(resolve => setTimeout(resolve, 100));
                        
                        // Immediately sign out after confirming email
                        await supabase.auth.signOut();
                        
                        // Clean the URL
                        if (window.history && window.history.replaceState) {
                            window.history.replaceState({}, document.title, window.location.pathname);
                        }
                        
                        // Show success message
                        alert('Email confirmed successfully! Please log in with your credentials.');
                        
                        confirmationHandled = true;
                        window.__HANDLE_CONFIRMATION__ = false;
                    } catch (confirmationError) {
                        console.error('Confirmation error:', confirmationError);
                        // Still clean URL even if there's an error
                        if (window.history && window.history.replaceState) {
                            window.history.replaceState({}, document.title, window.location.pathname);
                        }
                        confirmationHandled = true;
                        window.__HANDLE_CONFIRMATION__ = false;
                    }
                }
            } catch (e) {
                console.error('Confirmation handling error:', e);
                confirmationHandled = true;
                window.__HANDLE_CONFIRMATION__ = false;
            }
        })();
    }

    // If already logged in, redirect away from login/register immediately
    // (This won't run if we just handled a confirmation link above)
    (async () => {
        try {
            // Wait longer to ensure confirmation handling completes and sign out happens
            await new Promise(resolve => setTimeout(resolve, 500));
            
            // Double-check for confirmation tokens in URL (safety check)
            const hash = window.location.hash.startsWith('#') ? window.location.hash.slice(1) : window.location.hash;
            const hashParams = new URLSearchParams(hash);
            const queryParams = new URLSearchParams(window.location.search);
            const stillHasConfirmationToken = hashParams.get('type') === 'signup' || 
                                            hashParams.get('access_token') || 
                                            hashParams.get('token_hash') ||
                                            queryParams.get('token') ||
                                            queryParams.get('code');
            
            // Only redirect if we're not handling a confirmation
            if (window.__HANDLE_CONFIRMATION__ || confirmationHandled || stillHasConfirmationToken) {
                return; // Still processing confirmation or just finished
            }
            
            const { data } = await supabase.auth.getSession();
            const user = data?.session?.user;
            if (user) {
                const role = user.user_metadata?.role || 'user';
                if (role === 'admin') {
                    window.location.href = 'staff/dashboard.php';
                } else {
                    window.location.href = 'user/dashboard.php';
                }
                return;
            }
        } catch (e) {
            // ignore
        }
    })();

    // File upload handler
    const photoUpload = document.getElementById('photo_upload');
    const fileUploadBtn = document.getElementById('file_upload_btn');
    const fileClearBtn = document.getElementById('file_clear_btn');
    const fileName = document.getElementById('file_name');
    
    // Function to update clear button visibility
    function updateClearButtonVisibility() {
        if (fileClearBtn) {
            if (photoUpload && photoUpload.files && photoUpload.files.length > 0) {
                fileClearBtn.style.display = 'inline-flex';
            } else {
                fileClearBtn.style.display = 'none';
            }
        }
    }
    
    if (fileUploadBtn && photoUpload) {
        fileUploadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            photoUpload.click();
        });
    }
    
    // Clear button handler
    if (fileClearBtn && photoUpload && fileName) {
        fileClearBtn.addEventListener('click', function(e) {
            e.preventDefault();
            photoUpload.value = '';
            fileName.textContent = '';
            updateClearButtonVisibility();
        });
    }
    
    if (photoUpload && fileName) {
        photoUpload.addEventListener('change', async function() {
            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                
                // Validate file
                const validation = await validateImageFile(file);
                if (!validation.valid) {
                    alert(validation.message);
                    this.value = ''; // Clear the file input
                    fileName.textContent = '';
                    updateClearButtonVisibility();
                    return;
                }
                
                fileName.textContent = '✓ ' + file.name;
                updateClearButtonVisibility();
            } else {
                fileName.textContent = '';
                updateClearButtonVisibility();
            }
        });
    }
    
    // Validate image file: max 10MB
    async function validateImageFile(file) {
        if (!file) {
            return { valid: false, message: 'Please select a file.' };
        }
        
        // Check file type by MIME type
        const allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!file.type || !allowedMimeTypes.includes(file.type.toLowerCase())) {
            return { valid: false, message: 'Please select a valid image file (JPEG, JPG, PNG, or GIF only).' };
        }
        
        // Also check file extension as a backup
        const fileName = file.name.toLowerCase();
        const allowedExtensions = ['.jpg', '.jpeg', '.png', '.gif'];
        const hasValidExtension = allowedExtensions.some(ext => fileName.endsWith(ext));
        if (!hasValidExtension) {
            return { valid: false, message: 'Please select a valid image file (JPEG, JPG, PNG, or GIF only).' };
        }
        
        // Check file size (10MB = 10 * 1024 * 1024 bytes)
        const maxSize = 10 * 1024 * 1024; // 10MB
        if (file.size > maxSize) {
            const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
            return { valid: false, message: `File size (${fileSizeMB}MB) exceeds the maximum allowed size of 10MB.` };
        }
        
        return { valid: true };
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
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const password = this.querySelector('input[name="password"]').value;
            const email = this.querySelector('input[name="email"]').value.trim().toLowerCase();
            if (!email) {
                alert('Please enter your email.');
                return false;
            }
            
            const { data, error } = await supabase.auth.signInWithPassword({ email, password });
            
            if (!error && data?.session?.user) {
                // Check if user has an in-progress deletion request and cancel it
                try {
                    const checkResponse = await fetch('user/check_deletion_request.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            user_id: data.session.user.id
                        })
                    });
                    
                    const checkData = await checkResponse.json();
                    if (checkData.cancelled) {
                        // Show message that deletion was cancelled
                        console.log('Account deletion request cancelled:', checkData.message);
                        // Don't show alert here to avoid disrupting login flow
                        // The message will be shown after successful login redirect
                    }
                } catch (checkError) {
                    console.error('Error checking deletion request:', checkError);
                    // Don't fail login if deletion check fails
                }
            }
            if (error) {
                // Check for specific error types
                if (error.message.includes('Invalid login credentials') || error.message.includes('Email not confirmed')) {
                    alert('Invalid email or password. If you just registered, please check your email to confirm your account first.');
                } else {
                    alert(error.message || 'Login failed. Please try again.');
                }
                console.error('Login error:', error);
                return false;
            }
            
            if (!data || !data.user) {
                alert('Login failed. No user data returned.');
                return false;
            }
            
            const user = data.user;
            const role = user?.user_metadata?.role || 'user';
            if (role === 'admin') {
                window.location.href = 'staff/dashboard.php';
            } else {
                window.location.href = 'user/dashboard.php';
            }
            return true;
        });
    }
    
    // Username uniqueness check for registration
    const usernameRegister = document.getElementById('username_register');
    const usernameCheckText = document.getElementById('username_check_text');
    let usernameCheckTimeout = null;
    
    // Make usernameCheckText accessible globally for form submission
    window.usernameCheckText = usernameCheckText;
    
    if (usernameRegister && usernameCheckText) {
        usernameRegister.addEventListener('input', function() {
            const username = this.value.trim();
            
            // Clear previous timeout
            if (usernameCheckTimeout) {
                clearTimeout(usernameCheckTimeout);
            }
            
            // Hide check text if empty
            if (!username) {
                usernameCheckText.style.display = 'none';
                return;
            }
            
            // Debounce: wait 500ms after user stops typing
            usernameCheckTimeout = setTimeout(async () => {
                try {
                    usernameCheckText.style.display = 'block';
                    usernameCheckText.textContent = 'Checking availability...';
                    usernameCheckText.style.color = '#6c757d';
                    
                    const checkResponse = await fetch('check_username.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ username: username })
                    });
                    
                    const checkResult = await checkResponse.json();
                    
                    if (checkResult.available) {
                        usernameCheckText.textContent = '✓ Username available';
                        usernameCheckText.style.color = '#28a745';
                    } else {
                        usernameCheckText.textContent = '✗ Username already taken';
                        usernameCheckText.style.color = '#dc3545';
                    }
                } catch (checkError) {
                    console.error('Username check error:', checkError);
                    usernameCheckText.style.display = 'none';
                }
            }, 500);
        });
        
        // Clear check text on blur if empty
        usernameRegister.addEventListener('blur', function() {
            if (!this.value.trim()) {
                usernameCheckText.style.display = 'none';
            }
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
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Derive base path dynamically, e.g. "/Fortiroom/"
            const currentPath = window.location.pathname; // e.g. /Fortiroom/login.php
            const basePath = currentPath.substring(0, currentPath.lastIndexOf('/') + 1);
            
            const password = document.getElementById('password_register').value;
            const photo = this.querySelector('input[name="photo"]');
            const username = this.querySelector('input[name="username"]').value.trim();
            const email = this.querySelector('input[name="email"]').value.trim().toLowerCase();
            
            if (!photo.files || photo.files.length === 0) {
                alert('Please upload your photo.');
                return false;
            }
            
            // Validate image file before upload
            const photoFile = photo.files[0];
            const photoValidation = await validateImageFile(photoFile);
            if (!photoValidation.valid) {
                alert(photoValidation.message);
                return false;
            }
            
            if (!isPasswordValid(password)) {
                alert('Password must be at least 8 characters and contain at least 1 uppercase letter, 1 number, and 1 symbol.');
                return false;
            }
            if (!username) {
                alert('Please enter a username.');
                return false;
            }
            
            // Check email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address.');
                return false;
            }
            
            // Check if email already exists in Supabase
            try {
                const emailCheckResponse = await fetch('check_email.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ email: email })
                });
                
                if (!emailCheckResponse.ok) {
                    throw new Error('Server error during email check');
                }
                
                const emailCheckResult = await emailCheckResponse.json();
                
                if (!emailCheckResult.available) {
                    alert('This email address is already registered. Please use a different email or try logging in instead.');
                    return false;
                }
            } catch (emailCheckError) {
                console.error('Email check error:', emailCheckError);
                alert('Unable to verify email availability. Please try again.');
                return false;
            }
            
            // Check username uniqueness before proceeding (final check on submit)
            try {
                const checkResponse = await fetch('check_username.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ username: username })
                });
                
                if (!checkResponse.ok) {
                    throw new Error('Server error during username check');
                }
                
                const checkResult = await checkResponse.json();
                
                if (!checkResult.available) {
                    // Update the UI feedback
                    const checkTextEl = document.getElementById('username_check_text');
                    if (checkTextEl) {
                        checkTextEl.style.display = 'block';
                        checkTextEl.textContent = '✗ Username already taken';
                        checkTextEl.style.color = '#dc3545';
                    }
                    alert('This username is already taken. Please choose a different username.');
                    return false;
                }
            } catch (checkError) {
                console.error('Username check error:', checkError);
                alert('Unable to verify username availability. Please try again.');
                return false;
            }

            // Re-validate file one more time before upload (safety check)
            const finalPhotoFile = photo.files[0];
            const finalValidation = await validateImageFile(finalPhotoFile);
            if (!finalValidation.valid) {
                alert(finalValidation.message);
                photo.value = ''; // Clear the file input
                fileName.textContent = '';
                updateClearButtonVisibility();
                return false;
            }
            
            // Upload avatar to Supabase Storage via secure server endpoint
            let avatarUrl = null;
            try {
                const fd = new FormData();
                fd.append('file', photo.files[0]);
                fd.append('username', username);
                const res = await fetch('upload_avatar.php', { method: 'POST', body: fd });
                const json = await res.json().catch(() => ({}));
                if (!res.ok || json.error) {
                    throw new Error(json.error || 'Avatar upload failed.');
                }
                avatarUrl = json.publicUrl || null;
            } catch (err) {
                alert(err.message || 'Avatar upload failed.');
                return false;
            }
            
            // Create account with Supabase Auth. Supabase securely hashes passwords.
            const { data, error } = await supabase.auth.signUp({
                email,
                password,
                options: {
                    data: { role: 'user', username, avatar_url: avatarUrl },
                    emailRedirectTo: window.location.origin + basePath + 'login.php'
                }
            });
            
            if (error) {
                // Check for specific error types related to duplicate emails
                if (error.message && (
                    error.message.includes('already registered') || 
                    error.message.includes('already exists') ||
                    error.message.includes('User already registered') ||
                    error.message.includes('email address is already registered') ||
                    error.message.includes('User with this email address already exists') ||
                    error.status === 422 ||
                    error.code === 'signup_disabled'
                )) {
                    alert('This email address is already registered. Please use a different email or try logging in instead.');
                } else {
                    alert(error.message || 'Registration failed. Please try again.');
                }
                console.error('Registration error:', error);
                return false;
            }
            
            // Verify that user was created
            if (!data || !data.user) {
                alert('Registration may have failed. Please check if this email is already registered, or try again.');
                return false;
            }
            
            alert('Registration submitted! Please check your email to confirm your account, then you can log in with your email and password.');
            window.location.href = 'login.php';
            return true;
        });
    }
    
    // Forgot password form validation
    const forgotForm = document.getElementById('forgot');
    if (forgotForm) {
        forgotForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Derive base path dynamically, e.g. "/Fortiroom/"
            const currentPath = window.location.pathname; // e.g. /Fortiroom/login.php
            const basePath = currentPath.substring(0, currentPath.lastIndexOf('/') + 1);
            
            const email = this.querySelector('input[name="email"]').value.trim().toLowerCase();
            
            const { error } = await supabase.auth.resetPasswordForEmail(email, {
                redirectTo: window.location.origin + basePath + 'reset.php'
            });
            if (error) {
                alert(error.message || 'Unable to send reset email.');
                return false;
            }
            alert('Password reset link has been sent.');
            return true;
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

