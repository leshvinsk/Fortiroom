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
    <title>FORTIROOM | Intelligent Space Access Platform</title>
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

            input::-webkit-outer-spin-button,
            input::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }

            input[type=number] {
                -moz-appearance: textfield;
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
                @apply absolute top-0 left-0 h-full w-[60%] grid transition-all duration-700 ease-in-out;
                grid-template-columns: 1fr;
                grid-template-rows: 1fr;
            }

            form {
                @apply w-full max-w-[320px] h-full mx-auto flex flex-col justify-start transition-opacity duration-[10ms] delay-[350ms];
                grid-column: 1 / 2;
                grid-row: 1 / 2;
                padding: 0;
                padding-top: 0.5rem;
                gap: 0.7rem;
            }

            form.sign-up-form {
                @apply opacity-0 pointer-events-none justify-start;
                padding-top: 0.2rem;
                gap: 0.4rem;
            }

            form.sign-up-form .logo {
                margin-bottom: 0.3rem;
            }

            form.sign-up-form .heading {
                margin-bottom: 0.5rem;
            }

            form.sign-up-form .file-upload-wrap {
                margin-bottom: 0.5rem;
            }

            form.sign-up-form .text {
                margin-bottom: 0.3rem;
                margin-top: 0.3rem;
            }

            .auth-consent-text,
            .register-consent-text {
                font-size: 0.82rem;
                line-height: 1.45;
                color: #555;
            }

            form.sign-up-form .sign-btn-main {
                margin-top: 0.2rem;
                margin-bottom: 0.3rem;
            }

            form.forgot-password-form {
                @apply opacity-0 pointer-events-none;
            }

            form.sign-in-form,
            form.forgot-password-form {
                @apply justify-center;
            }

            .logo {
                @apply flex items-center;
            }

            .heading h2 {
                @apply text-[1.9rem] font-semibold text-black;
                margin-bottom: 0.2rem;
                margin-top: 0;
            }

            .data {
                @apply underline text-[0.9rem] font-medium;
                color: #2a5646;
                transition: 0.3s;
            }

            .data:hover,
            .text .data:hover,
            .mobile-account-switch-link:hover {
                color: #6B9E78;
            }

            .input-wrap {
                @apply relative h-[35px];
                margin-bottom: 1.3rem;
            }

            .file-upload-wrap {
                @apply relative text-center;
                margin-bottom: 0.8rem;
            }

            .file-input-hidden {
                @apply absolute opacity-0 overflow-hidden;
                width: 0.1px;
                height: 0.1px;
                z-index: -1;
            }

            .file-upload-label {
                @apply inline-flex items-center justify-center rounded-md font-medium outline-none transition-all duration-300 ease-in-out;
                gap: 0.3rem;
                padding: 0.35rem 0.85rem;
                background-color: #f5f5f5;
                border: 2px dashed #6B9E78;
                color: #151111;
                font-size: 0.76rem;
                font-family: "Poppins", sans-serif;
            }

            .file-upload-text {
                @apply inline-flex flex-col items-start;
                line-height: 1.1;
            }

            .file-upload-subtext {
                @apply self-center font-normal;
                margin-top: 0.08rem;
                font-size: 0.62rem;
                opacity: 0.85;
            }

            .file-upload-label:hover {
                background-color: #6B9E78;
                color: #fff;
                border-style: solid;
            }

            .file-upload-label i {
                font-size: 0.82rem;
            }

            .file-name {
                @apply block font-medium;
                margin-top: 0.4rem;
                margin-bottom: 0.7rem;
                min-height: 0.8rem;
                font-size: 0.75rem;
                color: #2a5646;
            }

            .file-clear-btn {
                @apply inline-flex items-center justify-center w-9 h-9 rounded-md text-white transition-all duration-300 ease-in-out;
                background-color: #dc3545;
                font-size: 0.9rem;
            }

            .file-clear-btn:hover {
                background-color: #c82333;
                transform: scale(1.05);
            }

            .file-clear-btn i {
                font-size: 0.85rem;
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

            .pwd-input {
                padding-right: 42px !important;
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

            .input-wrap .password-strength-text.weak {
                color: #d9534f;
            }

            .input-wrap .password-strength-text.fair {
                color: #f0ad4e;
            }

            .input-wrap .password-strength-text.good {
                color: #5bc0de;
            }

            .input-wrap .password-strength-text.strong {
                color: #2a5646;
            }

            .username-check-indicator {
                @apply absolute top-1/2 hidden items-center justify-center bg-white pointer-events-none;
                right: 0;
                transform: translateY(-50%);
                min-width: 1.2rem;
                height: 1.2rem;
                font-size: 0.95rem;
                font-weight: 700;
                line-height: 1;
                padding-left: 0.35rem;
                color: #6c757d;
                z-index: 100;
            }

            .username-check-indicator.is-checking,
            .username-check-indicator.is-available,
            .username-check-indicator.is-taken {
                @apply inline-flex;
            }

            .username-check-indicator.is-available {
                color: #28a745;
            }

            .username-check-indicator.is-taken {
                color: #dc3545;
            }

            .username-check-indicator.is-checking::before {
                content: "...";
            }

            .username-check-indicator.is-available::before {
                content: "\2713";
            }

            .username-check-indicator.is-taken::before {
                content: "\2715";
            }

            .password-strength-container {
                margin-top: 0.5rem;
                margin-bottom: 0.8rem;
            }

            .password-strength-bar {
                @apply w-full overflow-hidden;
                height: 6px;
                background-color: #e0e0e0;
                border-radius: 3px;
                position: relative;
            }

            .password-strength-fill {
                @apply h-full;
                width: 0%;
                transition: width 0.3s ease, background-color 0.3s ease;
                border-radius: 3px;
            }

            .password-strength-fill.weak { width: 25%; background-color: #d9534f; }
            .password-strength-fill.fair { width: 50%; background-color: #f0ad4e; }
            .password-strength-fill.good { width: 75%; background-color: #5bc0de; }
            .password-strength-fill.strong { width: 100%; background-color: #2a5646; }

            .password-strength-container .password-strength-text {
                @apply static bg-transparent p-0 text-right font-medium uppercase;
                font-size: 0.7rem;
                margin-top: 0.3rem;
                transition: color 0.3s ease;
            }

            .password-strength-container .password-strength-text.weak { color: #d9534f; }
            .password-strength-container .password-strength-text.fair { color: #f0ad4e; }
            .password-strength-container .password-strength-text.good { color: #5bc0de; }
            .password-strength-container .password-strength-text.strong { color: #2a5646; }

            .input-field {
                @apply absolute w-full h-full bg-transparent border-0 outline-none text-[0.95rem];
                border-bottom: 1px solid #000000;
                padding: 0;
                color: #151111;
            }

            .input-wrap .input-field[type="password"]#password,
            .input-wrap .input-field[type="text"]#password {
                padding-right: 50px;
            }

            .input-wrap .input-field[type="password"]#password_register,
            .input-wrap .input-field[type="text"]#password_register {
                padding-right: 90px;
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

            .sign-btn,
            .sign-btn-main {
                @apply inline-flex items-center justify-center w-full text-white border-0 cursor-pointer rounded-[0.8rem] text-[1rem] transition-colors duration-300;
                gap: 0.5rem;
                height: 40px;
            }

            .sign-btn {
                background-color: #151111;
                margin-bottom: 0.3rem;
            }

            .sign-btn-main {
                background-color: #2a5646;
                margin-top: 0.5rem;
                margin-bottom: 0.5rem;
            }

            .sign-btn:hover,
            .sign-btn-main:hover {
                background-color: #6B9E78;
            }

            .text {
                @apply text-center text-[0.95rem] text-black;
                margin-top: 0.5rem;
                margin-bottom: 0.5rem;
            }

            .text a {
                @apply transition-colors duration-300;
                color: #000000;
            }

            .text .data {
                color: #2a5646;
                font-weight: 600;
            }

            main.sign-up-mode form.sign-in-form,
            main.forgot-password-mode form.sign-in-form {
                @apply opacity-0 pointer-events-none;
            }

            main.sign-up-mode form.sign-up-form,
            main.forgot-password-mode form.forgot-password-form {
                @apply opacity-100 pointer-events-auto;
            }

            main.sign-up-mode .forms-wrap {
                left: 40%;
            }

            main.sign-up-mode .carousel {
                left: 0%;
            }

            .carousel {
                @apply absolute top-0 left-[60%] h-full w-[40%] bg-white overflow-hidden flex items-center justify-center transition-all duration-700 ease-in-out;
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
                margin-bottom: 0;
            }

            .image {
                @apply w-full;
                grid-column: 1/2;
                grid-row: 1/2;
                opacity: 0;
                transition: opacity 0.3s, transform 0.5s;
            }

            .img-1 { transform: translate(0, -50px); }
            .image.show {
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
                @apply flex flex-col items-center justify-center text-center px-4;
            }

            .carousel-content {
                @apply w-full text-center;
                margin-top: 0;
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

            .mobile-account-switch {
                display: none !important;
                margin: -0.15rem 0 0.2rem;
                font-size: 0.78rem;
                font-weight: 400;
                color: #555;
                text-align: left;
            }

            .mobile-account-switch-link {
                @apply inline border-0 bg-transparent p-0 m-0 underline font-semibold transition-colors duration-300;
                color: #2a5646;
                font: inherit;
            }

            .mobile-account-switch-link.toggle-carousel {
                padding-left: 0;
                padding-right: 0;
            }

            .toggle-carousel {
                padding-right: 40px;
                padding-left: 40px;
            }

            .register-carousel,
            .forgot-carousel {
                display: none;
            }

            main.sign-up-mode .login-carousel,
            main.forgot-password-mode .login-carousel {
                display: none;
            }

            main.sign-up-mode .register-carousel,
            main.forgot-password-mode .forgot-carousel {
                display: block;
                animation: fadeIn 0.5s ease;
            }
        }

        @keyframes arrow-bounce-right {
            0%, 100% { transform: translateY(-50%); }
            50% { transform: translate(5px, -50%); }
        }

        @keyframes arrow-bounce-left {
            0%, 100% { transform: translateY(-50%); }
            50% { transform: translate(-5px, -50%); }
        }

        .register-btn::after {
            content: "→";
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            transition: all 0.3s ease;
            animation: arrow-bounce-right 1.5s infinite;
        }

        .register-btn:hover::after {
            right: 15px;
        }

        .login-btn::after {
            content: "←";
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            transition: all 0.3s ease;
            animation: arrow-bounce-left 1.5s infinite;
        }

        .login-btn:hover::after {
            left: 15px;
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
                transition: transform 0.8s ease-in-out, opacity 0.45s linear;
            }

            .mobile-account-switch {
                display: block !important;
            }

            .heading {
                margin: 0.2rem 0 0.95rem;
            }

            .actual-form {
                display: flex;
                flex-direction: column;
                gap: 0.15rem;
            }

            .input-wrap {
                margin-bottom: 1rem;
            }

            .text {
                margin-top: 0.35rem;
                margin-bottom: 0.35rem;
            }

            .sign-btn-main {
                margin-top: 0.25rem;
                margin-bottom: 0.1rem;
            }

            form.sign-up-form {
                gap: 0.3rem;
                transform: translateX(100%);
            }

            form.forgot-password-form {
                transform: translateX(100%);
            }

            form.sign-up-form .file-upload-wrap {
                margin-top: 0.35rem;
                margin-bottom: 0.35rem;
            }

            form.sign-up-form .text {
                margin-top: 0.2rem;
                margin-bottom: 0.2rem;
            }

            main.sign-up-mode form.sign-in-form,
            main.forgot-password-mode form.sign-in-form {
                transform: translateX(-100%);
            }

            main.sign-up-mode form.sign-up-form,
            main.forgot-password-mode form.forgot-password-form {
                transform: translateX(0%);
            }

            .carousel,
            .images-wrapper {
                display: none;
            }

            .text-slider {
                width: 100%;
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

            .mobile-account-switch {
                width: 100%;
                text-align: left;
            }

            .heading {
                margin: 0.05rem 0 0.8rem;
            }

            .input-wrap {
                margin-bottom: 0.85rem;
            }

            .sign-btn,
            .sign-btn-main {
                height: 38px;
            }
        }
    </style>
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
                        <p class="mobile-account-switch">Don't have an account? <button type="button" class="mobile-account-switch-link toggle-carousel">Create Here</button></p>
                    </div>
                    <div class="actual-form">
                        <div class="input-wrap">
                            <input type="email" class="input-field" name="email" required />
                            <label>Email</label>
                        </div>
                        <div class="input-wrap">
                            <input type="password" class="input-field pwd-input" name="password" id="password" required />
                            <label>Password</label>
                            <button type="button" class="toggle-password" data-target="password" aria-label="Press and hold to view password" title="Press and hold to view">
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
                        <h2>Let's Get Started</h2>
                        <p class="mobile-account-switch">Have an account? <button type="button" class="mobile-account-switch-link toggle-carousel">Login</button></p>
                    </div>
                    <div class="actual-form">
                        <div class="file-upload-wrap">
                            <input type="file" name="photo" id="photo_upload" class="file-input-hidden" accept="image/jpeg,image/jpg,image/png,image/gif" required/>
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" class="file-upload-label" id="file_upload_btn">
                                    <i class="fas fa-camera"></i>
                                    <span class="file-upload-text">Upload Your Photo<span class="file-upload-subtext">(Max. 10MB)</span></span>
                                </button>
                                <button type="button" class="file-clear-btn hidden" id="file_clear_btn" title="Clear selected photo">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <span class="file-name" id="file_name"></span>
                        </div>
                        <div class="input-wrap">
                            <input type="text" name="username" class="input-field" id="username_register" required/>
                            <label>Username</label>
                            <span class="username-check-indicator" id="username_check_text" aria-live="polite"></span>
                        </div>
                        <div class="input-wrap">
                            <input type="email" name="email" class="input-field" required />
                            <label>Email</label>
                        </div>
                        <div class="input-wrap">
                            <input type="password" name="password" class="input-field pwd-input" minlength="8" id="password_register" required/>
                            <label>Password</label>
                            <span class="password-strength-text" id="strength_text_register"></span>
                            <button type="button" class="toggle-password" data-target="password_register" aria-label="Press and hold to view password" title="Press and hold to view">
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
                        <button type="submit" name="register_submit" class="sign-btn" id="register_submit_btn"><i class="fas fa-user-plus"></i> Register</button>
                        <p class="text register-consent-text">
                            By registering, you agree to the Terms of Service and Privacy Policy of FORTIROOM.
                        </p>
                    </div>
                    <button type="button" name="Go To Home" onclick="window.location.href = 'index.php';" class="sign-btn-main"><i class="fas fa-home"></i> Go to Home</button>
                </form>

                <form autocomplete="off" id="forgot" method="POST" action="login.php" class="forgot-password-form">
                    <div class="logo"></div>
                    <div class="heading">
                        <h2>Oops! Forgot Your Password?</h2>
                        <p class="mobile-account-switch">Changed Your Mind? <button type="button" class="mobile-account-switch-link" onclick="window.location.href='login.php';">Login Here</button></p>
                    </div>
                    <div class="actual-form">
                        <div class="input-wrap">
                            <input type="email" class="input-field" name="email" required/>
                            <label>Email</label>
                        </div>
                        <button type="submit" name="reset_submit" class="sign-btn"><i class="fas fa-paper-plane"></i> Send Reset Link</button>
                        <p class="text auth-consent-text">
                            By resetting, you agree to the Terms of Service and Privacy Policy of FORTIROOM.
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
                fileClearBtn.classList.remove('hidden');
                fileClearBtn.classList.add('inline-flex');
            } else {
                fileClearBtn.classList.add('hidden');
                fileClearBtn.classList.remove('inline-flex');
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
    let usernameCheckRequestId = 0;

    function setUsernameCheckState(state) {
        if (!usernameCheckText) return;

        usernameCheckText.className = 'username-check-indicator';
        usernameCheckText.removeAttribute('title');

        if (!state) {
            usernameCheckText.textContent = '';
            usernameCheckText.style.display = 'none';
            return;
        }

        usernameCheckText.style.display = 'inline-flex';

        if (state === 'checking') {
            usernameCheckText.classList.add('is-checking');
            usernameCheckText.textContent = '...';
            usernameCheckText.title = 'Checking username availability';
            return;
        }

        if (state === 'available') {
            usernameCheckText.classList.add('is-available');
            usernameCheckText.textContent = '';
            usernameCheckText.title = 'Username available';
            return;
        }

        if (state === 'taken') {
            usernameCheckText.classList.add('is-taken');
            usernameCheckText.textContent = '';
            usernameCheckText.title = 'Username already taken';
            return;
        }
    }
    
    if (usernameRegister && usernameCheckText) {
        usernameRegister.addEventListener('input', function() {
            const username = this.value.trim();
            const requestId = ++usernameCheckRequestId;
            
            // Clear previous timeout
            if (usernameCheckTimeout) {
                clearTimeout(usernameCheckTimeout);
            }
            
            // Hide indicator if empty
            if (!username) {
                setUsernameCheckState(null);
                return;
            }
            
            // Debounce: wait 500ms after user stops typing
            usernameCheckTimeout = setTimeout(async () => {
                try {
                    if (requestId !== usernameCheckRequestId || !usernameRegister.value.trim()) {
                        setUsernameCheckState(null);
                        return;
                    }

                    setUsernameCheckState('checking');
                    
                    const checkResponse = await fetch('check_username.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ username: username })
                    });
                    
                    const checkResult = await checkResponse.json();

                    if (requestId !== usernameCheckRequestId || !usernameRegister.value.trim()) {
                        setUsernameCheckState(null);
                        return;
                    }

                    setUsernameCheckState(checkResult.available ? 'available' : 'taken');
                    return;
                } catch (checkError) {
                    console.error('Username check error:', checkError);
                    setUsernameCheckState(null);
                    return;
                }
            }, 500);
        });
        
        // Clear indicator on blur if empty
        usernameRegister.addEventListener('blur', function() {
            if (!this.value.trim()) {
                setUsernameCheckState(null);
                return;
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
                    setUsernameCheckState('taken');
                } else {
                    setUsernameCheckState('available');
                }
                
                if (!checkResult.available) {
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
