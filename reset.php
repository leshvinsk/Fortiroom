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
    <link rel="stylesheet" href="assets/assets_customer/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    <script>
        window.__SUPABASE__ = {
            url: "<?php echo htmlspecialchars($SUPABASE_URL, ENT_QUOTES, 'UTF-8'); ?>",
            anonKey: "<?php echo htmlspecialchars($SUPABASE_ANON_KEY, ENT_QUOTES, 'UTF-8'); ?>"
        };
    </script>
    <style>
        /* Minimal, reuse your existing styles if preferred */
        .reset-container { max-width: 420px; margin: 8vh auto; background: rgba(255,255,255,0.9); padding: 24px; border-radius: 12px; }
        .reset-container h2 { margin: 0 0 12px 0; }
        .reset-container .input-wrap { margin: 12px 0; }
        .reset-container input { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #ddd; }
        .reset-container button { margin-top: 12px; width: 100%; padding: 10px 12px; border: 0; border-radius: 8px; background: #111827; color: #fff; cursor: pointer; }
        .reset-container .note { color: #6b7280; font-size: 12px; margin-top: 8px; }
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
                        <h6>Let's Regain Your Access</h6>
                    </div>
                    <div class="actual-form">
                        <div class="input-wrap">
                            <input type="password" class="input-field" id="new_password" minlength="8" required />
                            <label>New Password</label>
                            <span class="password-strength-text" id="strength_text_new"></span>
                            <i class="fas fa-eye-slash toggle-password" data-target="new_password"></i>
                        </div>
                        <div class="input-wrap">
                            <input type="password" class="input-field" id="confirm_password" minlength="8" required />
                            <label>Confirm Password</label>
                            <i class="fas fa-eye-slash toggle-password" data-target="confirm_password"></i>
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

            document.querySelectorAll('.toggle-password').forEach(icon => {
                icon.addEventListener('click', function() {
                    const target = document.getElementById(this.getAttribute('data-target'));
                    if (!target) return;
                    const isHidden = target.type === 'password';
                    target.type = isHidden ? 'text' : 'password';
                    this.classList.toggle('fa-eye', isHidden);
                    this.classList.toggle('fa-eye-slash', !isHidden);
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
