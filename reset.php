<?php
// Reuse the same simple .env loader logic as login.php
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Password - FORTIROOM</title>
    <link rel="icon" href="images/FYP_Logo_small.png" type="image/icon type">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif']
                    }
                }
            }
        };
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    <script>
        window.__SUPABASE__ = {
            url: "<?php echo htmlspecialchars($SUPABASE_URL, ENT_QUOTES, 'UTF-8'); ?>",
            anonKey: "<?php echo htmlspecialchars($SUPABASE_ANON_KEY, ENT_QUOTES, 'UTF-8'); ?>"
        };
    </script>
    <style type="text/tailwindcss">
        @layer base {
            *,
            *::before,
            *::after {
                @apply box-border m-0 p-0;
            }

            body,
            input {
                @apply font-sans;
            }

            body {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }

            ::-webkit-scrollbar {
                display: none;
            }
        }

        @layer components {
            .back-video {
                @apply fixed inset-0 w-screen h-screen pointer-events-none;
                object-fit: cover;
                object-position: center;
                filter: brightness(0.4);
            }

            main {
                @apply w-full min-h-screen overflow-hidden p-8 flex items-center justify-center;
            }

            .box {
                @apply relative w-full max-w-[900px] h-[580px] bg-white rounded-[2.5rem];
                opacity: 0.87;
                box-shadow: 0 60px 40px -30px rgba(0, 0, 0, 0.27);
            }

            .inner-box {
                @apply absolute top-1/2 left-1/2;
                width: calc(100% - 3.6rem);
                height: calc(100% - 3.6rem);
                transform: translate(-50%, -50%);
            }

            .forms-wrap {
                @apply absolute top-0 left-0 h-full w-[60%] grid;
                grid-template-columns: 1fr;
                grid-template-rows: 1fr;
            }

            form {
                @apply w-full max-w-[320px] h-full mx-auto flex flex-col justify-center;
                grid-column: 1 / 2;
                grid-row: 1 / 2;
                padding: 0;
                padding-top: 0.5rem;
                gap: 0.7rem;
            }

            .logo {
                @apply flex items-center;
            }

            .heading {
                margin: 2rem 0;
            }

            .heading h2 {
                @apply text-[1.9rem] font-semibold text-black;
                margin-bottom: 0.2rem;
                margin-top: 0;
            }

            .heading p {
                @apply text-[0.85rem] font-normal;
                color: #555;
            }

            .actual-form {
                @apply flex flex-col;
                gap: 0.15rem;
            }

            .input-wrap {
                @apply relative h-[35px];
                margin-bottom: 1.3rem;
            }

            .input-field {
                @apply absolute w-full h-full bg-transparent border-0 outline-none text-[0.95rem];
                border-bottom: 1px solid #000000;
                padding: 0;
                color: #151111;
            }

            .pwd-input {
                padding-right: 42px !important;
            }

            label {
                @apply absolute left-0 top-1/2 text-[0.95rem] text-black pointer-events-none transition-all duration-300;
                transform: translateY(-50%);
            }

            .input-field.active {
                border-bottom-color: #6B9E78;
            }

            .input-field.active + label {
                font-size: 0.75rem;
                top: -2px;
            }

            .input-wrap .toggle-password {
                @apply absolute inline-flex items-center justify-center cursor-pointer p-0 border-0 bg-transparent transition-colors duration-200;
                right: 14px;
                bottom: 13px;
                width: 22px;
                height: 22px;
                color: #9ca3af;
                z-index: 10;
            }

            .input-wrap .toggle-password svg {
                width: 18px;
                height: 18px;
                stroke: currentColor;
                fill: none;
                stroke-linecap: round;
                stroke-linejoin: round;
            }

            .input-wrap .toggle-password .icon-visible {
                display: none;
            }

            .input-wrap .toggle-password .icon-hidden {
                display: block;
            }

            .input-wrap .toggle-password.is-revealing .icon-visible {
                display: block;
            }

            .input-wrap .toggle-password.is-revealing .icon-hidden {
                display: none;
            }

            .input-wrap .toggle-password:hover {
                color: #6b7280;
            }

            .input-wrap .password-strength-text {
                @apply absolute top-1/2 pointer-events-none uppercase bg-white rounded-[3px];
                right: 42px;
                transform: translateY(-50%);
                font-size: 0.7rem;
                font-weight: 600;
                transition: color 0.3s ease;
                z-index: 100;
                letter-spacing: 0.5px;
                padding: 2px 6px;
            }

            .input-wrap .password-strength-text.weak { color: #d9534f; }
            .input-wrap .password-strength-text.fair { color: #f0ad4e; }
            .input-wrap .password-strength-text.good { color: #5bc0de; }
            .input-wrap .password-strength-text.strong { color: #2a5646; }

            .sign-btn {
                @apply inline-flex items-center justify-center w-full text-white border-0 cursor-pointer rounded-[0.8rem] text-[1rem] transition-colors duration-300;
                gap: 0.5rem;
                height: 40px;
                background-color: #151111;
                margin-bottom: 0.3rem;
            }

            .sign-btn:hover {
                background-color: #6B9E78;
            }

            .carousel {
                @apply absolute top-0 left-[60%] h-full w-[40%] bg-white overflow-hidden flex items-center justify-center;
                opacity: 1;
                border-radius: 2rem;
                padding-top: 2rem;
                padding-bottom: 2rem;
            }

            .carousel-container {
                @apply flex flex-col items-center justify-center w-full h-full;
                padding-top: 70px;
            }

            .images-wrapper {
                @apply flex items-center justify-center w-full;
            }

            .image {
                @apply w-full;
                opacity: 1;
                transform: none;
            }

            .logo-centered {
                padding-top: 0 !important;
                max-width: 80% !important;
                margin: 0 auto 10px auto !important;
                display: block !important;
            }

            .text-slider {
                @apply flex flex-col items-center justify-center text-center px-4 w-full;
            }

            .carousel-content {
                @apply w-full text-center;
                margin-left: auto;
                margin-right: auto;
            }

            .carousel-content h3 {
                @apply text-2xl font-semibold;
                margin-bottom: 0.5rem;
                color: #2a5646;
            }

            .carousel-content p {
                font-size: 0.9rem;
                margin-bottom: 1.5rem;
                color: #555;
            }

            .carousel-btn {
                @apply inline-flex items-center text-white font-medium rounded-md transition-all duration-300 relative border-0 cursor-pointer;
                gap: 0.5rem;
                padding: 10px 25px;
                background-color: #2a5646;
                font-size: 0.9rem;
            }

            .carousel-btn:hover {
                background-color: #6B9E78;
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            }

            @media (max-width: 850px) {
                .box {
                    height: auto;
                    max-width: 500px;
                    overflow: hidden;
                }

                .inner-box {
                    position: static;
                    transform: none;
                    width: revert;
                    height: revert;
                    padding: 1.2rem 1.5rem 1.25rem;
                }

                .forms-wrap {
                    position: revert;
                    width: 100%;
                    height: auto;
                }

                form {
                    max-width: revert;
                    padding: 0.55rem 1.75rem 0.95rem;
                }

                .heading {
                    margin: 0.2rem 0 0.95rem;
                }

                .input-wrap {
                    margin-bottom: 1rem;
                }

                .carousel,
                .images-wrapper {
                    display: none;
                }
            }

            @media (max-width: 530px) {
                main {
                    padding: 1rem;
                }

                .box {
                    border-radius: 2rem;
                }

                .inner-box {
                    padding: 0.8rem 1rem 0.9rem;
                }

                form {
                    padding: 0.15rem 1.2rem 0.65rem;
                }

                .heading {
                    margin: 0.05rem 0 0.8rem;
                }

                .input-wrap {
                    margin-bottom: 0.85rem;
                }

                .sign-btn {
                    height: 38px;
                }
            }
        }
    </style>
<?php /* Ensure there is no extra whitespace before closing head or body to preserve layout */ ?>
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
                        <p>Set a strong new password to secure your account.</p>
                    </div>
                    <div class="actual-form">
                        <div class="input-wrap">
                            <input type="password" class="input-field pwd-input" id="new_password" minlength="8" required />
                            <label>New Password</label>
                            <span class="password-strength-text" id="strength_text_new"></span>
                            <button type="button" class="toggle-password" data-target="new_password" aria-label="Press and hold to view password" title="Press and hold to view">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon-hidden" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M4 12c1.8-3.1 4.5-4.8 8-4.8s6.2 1.7 8 4.8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" />
                                    <circle cx="12" cy="14.2" r="3.2" stroke="currentColor" stroke-width="2.2" />
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="icon-visible" aria-hidden="true">
                                    <path d="M4 12c1.8-3.1 4.5-4.8 8-4.8s6.2 1.7 8 4.8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" />
                                    <circle cx="12" cy="14.2" r="3.2" stroke="currentColor" stroke-width="2.2" />
                                </svg>
                            </button>
                        </div>
                        <div class="input-wrap">
                            <input type="password" class="input-field pwd-input" id="confirm_password" minlength="8" required />
                            <label>Confirm Password</label>
                            <button type="button" class="toggle-password" data-target="confirm_password" aria-label="Press and hold to view password" title="Press and hold to view">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon-hidden" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M4 12c1.8-3.1 4.5-4.8 8-4.8s6.2 1.7 8 4.8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" />
                                    <circle cx="12" cy="14.2" r="3.2" stroke="currentColor" stroke-width="2.2" />
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="icon-visible" aria-hidden="true">
                                    <path d="M4 12c1.8-3.1 4.5-4.8 8-4.8s6.2 1.7 8 4.8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" />
                                    <circle cx="12" cy="14.2" r="3.2" stroke="currentColor" stroke-width="2.2" />
                                </svg>
                            </button>
                        </div>
                        <button type="button" id="submit_reset" class="sign-btn"><i class="fas fa-key"></i> Update Password</button>
                    </div>
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

    <script>
    (function () {
        const onReady = async function() {
            const { createClient } = window.supabase || {};
            const submitBtn = document.getElementById('submit_reset');
            if (!createClient) {
                alert('Failed to load Supabase.');
                return;
            }
            const supabase = createClient(window.__SUPABASE__.url, window.__SUPABASE__.anonKey);

            // Floating labels & password toggle (match login.php behaviour)
            document.querySelectorAll('.input-field').forEach(field => {
                if (field.value) field.classList.add('active');
                field.addEventListener('focus', () => field.classList.add('active'));
                field.addEventListener('blur', () => {
                    if (!field.value) field.classList.remove('active');
                });
            });

            const toggleIcons = document.querySelectorAll('.toggle-password');

            function showPasswordForHold(btn) {
                const targetId = btn.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                if (!passwordInput) return;
                passwordInput.type = 'text';
                btn.classList.add('is-revealing');
            }

            function hidePasswordForHold(btn) {
                const targetId = btn.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                if (!passwordInput) return;
                passwordInput.type = 'password';
                btn.classList.remove('is-revealing');
            }

            toggleIcons.forEach((icon) => {
                icon.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    showPasswordForHold(this);
                });

                icon.addEventListener('touchstart', function(e) {
                    e.preventDefault();
                    showPasswordForHold(this);
                }, { passive: false });

                ['mouseup', 'mouseleave', 'touchend', 'touchcancel'].forEach((eventName) => {
                    icon.addEventListener(eventName, function() {
                        hidePasswordForHold(this);
                    });
                });

                icon.addEventListener('keydown', function(e) {
                    if (e.key === ' ' || e.key === 'Enter') {
                        e.preventDefault();
                        showPasswordForHold(this);
                    }
                });

                icon.addEventListener('keyup', function(e) {
                    if (e.key === ' ' || e.key === 'Enter') {
                        hidePasswordForHold(this);
                    }
                });
            });

            const strengthText = document.getElementById('strength_text_new');
            const applyStrength = (password) => {
                if (!strengthText) return;
                let strength = 0;
                if (password.length >= 8) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) strength++;
                strengthText.classList.remove('weak', 'fair', 'good', 'strong');
                if (strength === 0) {
                    strengthText.textContent = '';
                } else if (strength === 1) {
                    strengthText.classList.add('weak');
                    strengthText.textContent = 'Weak';
                } else if (strength === 2) {
                    strengthText.classList.add('fair');
                    strengthText.textContent = 'Fair';
                } else if (strength === 3) {
                    strengthText.classList.add('good');
                    strengthText.textContent = 'Good';
                } else if (strength === 4) {
                    strengthText.classList.add('strong');
                    strengthText.textContent = 'Strong';
                }
            };

            const newPasswordInput = document.getElementById('new_password');
            if (newPasswordInput) {
                newPasswordInput.addEventListener('input', () => applyStrength(newPasswordInput.value));
            }

            // Parse access_token / refresh_token from hash if present
            function parseHashParams() {
                const hash = location.hash.startsWith('#') ? location.hash.slice(1) : location.hash;
                const params = new URLSearchParams(hash);
                const access_token = params.get('access_token');
                const refresh_token = params.get('refresh_token');
                const type = params.get('type');
                const token_hash = params.get('token_hash');
                const error_description = params.get('error_description');
                return { access_token, refresh_token, type, token_hash, error_description };
            }
            function parseQueryParams() {
                const params = new URLSearchParams(location.search);
                return {
                    code: params.get('code'),
                    token_hash: params.get('token_hash'),
                    type: params.get('type'),
                    error_description: params.get('error_description')
                };
            }

            // Helper: wait briefly for session from recovery link; optionally seed from hash
            async function waitForRecoverySession(attempts = 10) {
                for (let i = 0; i < attempts; i++) {
                    // If we don't yet have a session, but hash has tokens, seed the session
                    const { data: check } = await supabase.auth.getSession();
                    if (!check || !check.session) {
                        const hashParams = parseHashParams();
                        const queryParams = parseQueryParams();
                        try {
                            if (queryParams.code) {
                                // PKCE-style exchange (rare for recovery but safe to support)
                                await supabase.auth.exchangeCodeForSession(window.location.href);
                            } else if (hashParams.token_hash && (hashParams.type === 'recovery' || !hashParams.type)) {
                                // Newer recovery links provide token_hash
                                await supabase.auth.verifyOtp({ type: 'recovery', token_hash: hashParams.token_hash });
                            } else if (hashParams.access_token && hashParams.refresh_token) {
                                await supabase.auth.setSession({ access_token: hashParams.access_token, refresh_token: hashParams.refresh_token });
                            }
                        } catch (e) {
                            // ignore and retry
                        }
                    }
                    const { data } = await supabase.auth.getSession();
                    if (data && data.session) return data.session;
                    await new Promise(r => setTimeout(r, 200));
                }
                return null;
            }

            // If opened directly without the recovery link, bounce immediately (no waiting)
            const hasRecoveryHash = location.hash.includes('type=recovery') || location.hash.includes('access_token') || location.hash.includes('token_hash') || location.search.includes('code=');
            if (!hasRecoveryHash) {
                window.location.href = 'login.php';
                return;
            }
            // Otherwise, try to establish the session quickly
            let session = await waitForRecoverySession();
            if (!session) {
                // Soft hint; no alert loop if session arrives shortly after
                console.info('Preparing your reset session... if this persists, click the email link again.');
            }

            // Web Notification helper
            async function notify(title, options = {}) {
                try {
                    if (!('Notification' in window)) return false;
                    if (Notification.permission === 'granted') {
                        new Notification(title, options);
                        return true;
                    }
                    if (Notification.permission !== 'denied') {
                        const perm = await Notification.requestPermission();
                        if (perm === 'granted') {
                            new Notification(title, options);
                            return true;
                        }
                    }
                } catch (_) {}
                return false;
            }

            // Handle submit
            submitBtn.addEventListener('click', async () => {
                const newPassword = document.getElementById('new_password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                if (!newPassword || newPassword.length < 8) { alert('Password must be at least 8 characters.'); return; }
                if (newPassword !== confirmPassword) { alert('Passwords do not match.'); return; }

                // Re-check or wait for session just before updating
                let current = await waitForRecoverySession(10);
                if (!current) {
                    // Final attempt: explicitly seed session based on current URL format then retry once
                    try {
                        const hashParams = parseHashParams();
                        const queryParams = parseQueryParams();
                        if (queryParams.code) {
                            await supabase.auth.exchangeCodeForSession(window.location.href);
                        } else if (hashParams.token_hash && (hashParams.type === 'recovery' || !hashParams.type)) {
                            await supabase.auth.verifyOtp({ type: 'recovery', token_hash: hashParams.token_hash });
                        } else if (hashParams.access_token && hashParams.refresh_token) {
                            await supabase.auth.setSession({ access_token: hashParams.access_token, refresh_token: hashParams.refresh_token });
                        }
                        const check = await supabase.auth.getSession();
                        current = check.data.session;
                    } catch (e) {
                        // ignore error; will show message below
                    }
                    if (!current) { alert('Invalid access. Please use the forgot password link.'); return; }
                }

                // Updating
                const { error } = await supabase.auth.updateUser({ password: newPassword });
                if (error) {
                    alert(error.message || 'Failed to update password.');
                    return;
                }
                // Ensure user is signed out so they must log in again with the new password
                try { await supabase.auth.signOut(); } catch (_) {}
                alert('Password updated! Redirecting to login...');
                // Chrome/Browser notification (allowed on localhost)
                await notify('Password updated', {
                    body: 'Redirecting to login…',
                    icon: 'images/FYP_Logo_small.png',
                    silent: true
                });
                // Redirect to login to prompt credentials again
                setTimeout(() => { window.location.href = 'login.php'; }, 800);
            });
        };
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', onReady);
        } else {
            onReady();
        }
    })();
    </script>
</body>
</html>
