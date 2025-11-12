<?php
// Minimal .env loader (no external deps). Loads KEY=VALUE pairs into $_ENV.
$envPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '.env';
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
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FORTIROOM - A Smart Space Management System</title>
    <link rel="icon" href="../images/FYP_Logo_small.png" type="image/icon type">
    <!-- Bootstrap Styles-->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- FontAwesome Styles-->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- Custom Styles-->
    <link href="assets/css/custom-styles.css" rel="stylesheet" />
    <!-- Google Fonts-->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
    <!-- TABLE STYLES-->
    <link href="assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <!-- Supabase JS v2 -->
    <script defer src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    <script>
        // Injected from server-side env. The anon key is safe to expose client-side.
        window.__SUPABASE__ = {
            url: "<?php echo htmlspecialchars($SUPABASE_URL, ENT_QUOTES, 'UTF-8'); ?>",
            anonKey: "<?php echo htmlspecialchars($SUPABASE_ANON_KEY, ENT_QUOTES, 'UTF-8'); ?>"
        };
    </script>
    <style>
        body::-webkit-scrollbar {
            display: none;
        }
        
        /* Hide scrollbar for Chrome, Safari and Opera */
        ::-webkit-scrollbar {
            width: 0px;
            background: transparent;
        }
        
        /* Top Navbar Right Links */
        .navbar-top-links {
            margin-right: 0;
        }
        .navbar-top-links li {
            display: inline-block;
        }
        .navbar-top-links li a {
            padding: 15px 15px;
            min-height: 50px;
            color: #fff !important;
            font-weight: 600;
        }
        .navbar-top-links li a i {
            margin-right: 5px;
        }
        .navbar-top-links li a:hover,
        .navbar-top-links li a:focus,
        .navbar-top-links li a:active {
            background-color: inherit !important;
            color: #fff !important;
            text-decoration: none !important;
            cursor: default !important;
            box-shadow: none !important;
            opacity: 1 !important;
            transform: none !important;
        }
        .navbar-top-links li a.active-menu:hover,
        .navbar-top-links li a.active-menu:focus,
        .navbar-top-links li a.active-menu:active {
            background-color: #3F729B !important;
        }
        
        /* Profile Field Styles */
        .profile-fields-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .profile-field {
            display: flex;
            align-items: center;
            padding: 30px;
            border-bottom: 1px solid #e9ecef;
            transition: background-color 0.2s;
        }
        
        .profile-field:last-child {
            border-bottom: none;
        }
        
        .profile-field:hover {
            background-color: #f8f9fa;
        }
        
        .field-icon {
            width: 60px;
            height: 60px;
            background: #3F729B;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 25px;
            flex-shrink: 0;
        }
        
        .field-icon i {
            color: #fff;
            font-size: 24px;
        }
        
        .field-content {
            flex: 1;
        }
        
        .field-content label {
            display: block;
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }
        
        .field-value {
            font-size: 16px;
            color: #333;
            font-weight: 500;
        }
        
        .field-action {
            margin-left: 15px;
        }
        
        .update-btn {
            padding: 8px 20px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            transition: all 0.3s;
            white-space: nowrap;
        }
        
        .update-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }
        
        .update-btn i {
            margin-right: 5px;
        }
        
        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(2px);
        }
        
        .modal-overlay.active {
            display: flex;
        }
        
        .update-modal {
            background: #fff;
            border-radius: 12px;
            width: 90%;
            max-width: 550px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            animation: slideDown 0.3s ease;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .update-modal-header {
            background: #fff;
            color: #1a1a1a;
            padding: 24px 30px;
            border-radius: 12px 12px 0 0;
            position: relative;
            border-bottom: 1px solid #e9ecef;
        }
        
        .update-modal-header h3 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
            color: #1a1a1a;
        }
        
        .close-modal {
            position: absolute;
            top: 20px;
            right: 24px;
            background: transparent;
            border: none;
            color: #999;
            font-size: 32px;
            cursor: pointer;
            line-height: 1;
            padding: 0;
            width: 36px;
            height: 36px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 300;
        }
        
        .close-modal:hover {
            color: #333;
        }
        
        .update-modal-body {
            padding: 30px;
        }
        
        .update-form .form-group-update {
            margin-bottom: 24px;
        }
        
        .update-form .form-group-update:last-child {
            margin-bottom: 0;
        }
        
        .update-form label {
            display: block;
            font-weight: 500;
            margin-bottom: 10px;
            color: #333;
            font-size: 15px;
        }
        
        .update-form label .required {
            color: #dc3545;
            margin-left: 2px;
        }
        
        .update-form input {
            width: 100%;
            padding: 12px 16px;
            padding-right: 45px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
            background: #fff;
        }
        
        .update-form input:focus {
            outline: none;
            border-color: #3F729B;
            box-shadow: 0 0 0 0.2rem rgba(63, 114, 155, 0.15);
        }
        
        .update-form input::placeholder {
            color: #adb5bd;
            font-weight: 400;
        }
        
        .form-group-update {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 16px;
            bottom: 12px;
            cursor: pointer;
            color: #6c757d;
            font-size: 16px;
            transition: color 0.2s;
            z-index: 10;
        }
        
        .toggle-password:hover {
            color: #3F729B;
        }
        
        .update-modal-footer {
            padding: 20px 30px;
            border-top: 1px solid #e9ecef;
            text-align: right;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background: #f8f9fa;
            border-radius: 0 0 12px 12px;
        }
        
        .btn-cancel {
            background: #6c757d;
            color: #fff;
            border: none;
            padding: 12px 28px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-cancel:hover {
            background: #5a6268;
        }
        
        .btn-save {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 12px 28px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-save:hover {
            background: #0056b3;
        }
        
        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        /* Fixed Header and Sidebar */
        .navbar.navbar-default.top-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            margin-bottom: 0;
        }
        
        .navbar-default.navbar-side {
            position: fixed;
            top: 60px; /* Height of top navbar */
            left: 0;
            bottom: 0;
            z-index: 999;
            overflow-y: auto;
            width: 260px;
        }
        
        #page-wrapper {
            margin-left: 260px;
            margin-top: 60px; /* Height of top navbar */
            min-height: 100vh;
            position: relative;
            padding-bottom: 50px;
        }
        
        #wrapper {
            width: 100%;
            overflow-x: hidden;
            min-height: 100vh;
        }
        
        body {
            overflow-x: hidden;
            overflow-y: auto !important;
            min-height: 100vh;
        }
        
        html {
            overflow-y: auto !important;
        }
        
        #page-inner {
            min-height: auto;
            padding-bottom: 30px;
        }
        
        /* Profile Photo Styles */
        .profile-photo-field {
            display: flex;
            align-items: center;
            padding: 40px 30px;
            border-bottom: 1px solid #e9ecef;
            background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
        }
        
        .photo-preview-container {
            position: relative;
            margin-right: 30px;
        }
        
        .profile-photo-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #3F729B;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .photo-upload-icon {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 35px;
            height: 35px;
            background: #3F729B;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #fff;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .photo-upload-icon:hover {
            background: #2d5a7a;
            transform: scale(1.1);
        }
        
        .photo-upload-icon i {
            color: #fff;
            font-size: 14px;
        }
        
        .photo-info {
            flex: 1;
        }
        
        .photo-info h4 {
            margin: 0 0 8px 0;
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        
        .photo-info p {
            margin: 0;
            font-size: 13px;
            color: #6c757d;
            line-height: 1.6;
        }
        
        /* Photo Upload Form */
        #photoForm {
            text-align: center;
        }
        
        .photo-upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            background: #f8f9fa;
            margin-bottom: 20px;
        }
        
        .photo-upload-area:hover {
            border-color: #3F729B;
            background: #fff;
        }
        
        .photo-upload-area.drag-over {
            border-color: #3F729B;
            background: #e7f3ff;
        }
        
        .upload-preview-container {
            margin-bottom: 20px;
            display: none;
        }
        
        .upload-preview {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto;
            border: 4px solid #3F729B;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .upload-icon {
            width: 80px;
            height: 80px;
            background: #3F729B;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .upload-icon i {
            color: #fff;
            font-size: 36px;
        }
        
        .upload-text h4 {
            margin: 0 0 10px 0;
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        
        .upload-text p {
            margin: 0;
            font-size: 13px;
            color: #6c757d;
        }
        
        .file-input-hidden {
            display: none;
        }
        
        /* Mobile and Tablet Responsive */
        @media (max-width: 991px) {
            .profile-fields-container {
                border-radius: 0;
            }
            
            .profile-field {
                flex-direction: column;
                align-items: flex-start;
                padding: 20px 15px;
            }
            
            .field-icon {
                margin-bottom: 15px;
            }
            
            .field-action {
                margin-left: 0;
                margin-top: 15px;
                width: 100%;
            }
            
            .update-btn {
                width: 100%;
                justify-content: center;
                display: flex;
                align-items: center;
            }
            
            .profile-photo-field {
                flex-direction: column;
                text-align: center;
                padding: 30px 20px;
            }
            
            .photo-preview-container {
                margin-right: 0;
                margin-bottom: 20px;
            }
            
            .photo-info {
                width: 100%;
            }
            
            .photo-info h4 {
                font-size: 16px;
            }
            
            .photo-info p {
                font-size: 12px;
            }
            
            .update-modal {
                width: 95%;
                max-width: none;
                margin: 10px;
            }
            
            .update-modal-header {
                padding: 20px;
            }
            
            .update-modal-header h3 {
                font-size: 18px;
                padding-right: 40px;
            }
            
            .update-modal-body {
                padding: 20px;
            }
            
            .update-modal-footer {
                flex-direction: column-reverse;
                padding: 20px;
            }
            
            .btn-cancel, .btn-save {
                width: 100%;
                font-size: 14px;
            }
            
            .navbar.navbar-default.top-navbar {
                background-color: #fff !important;
                border-bottom: 1px solid #ddd !important;
            }
            
            .navbar-header {
                background-color: #fff !important;
                width: 100% !important;
                position: relative !important;
            }
            
            .navbar-toggle {
                display: block !important;
                position: absolute !important;
                right: 15px !important;
                top: 8px !important;
                float: none !important;
                margin: 0 !important;
                background-color: #fff !important;
                border: 1px solid #888 !important;
                padding: 6px 10px !important;
            }
            
            .navbar-toggle .icon-bar {
                background-color: #333 !important;
            }
            
            .navbar-brand {
                float: none !important;
                display: inline-block !important;
                padding: 10px 15px !important;
            }
            
            /* Force sidebar to start hidden - CSS takes precedence */
            .navbar-default.navbar-side {
                left: -260px !important;
                transition: left 0.3s ease;
                z-index: 999;
                background-color: #1a2942 !important;
                transform: translateX(0) !important;
            }
            
            /* Only show when explicitly opened */
            .navbar-default.navbar-side.in {
                left: 0 !important;
                transform: translateX(0) !important;
            }
            
            #page-wrapper {
                margin-left: 0 !important;
                margin-top: 60px;
                width: 100% !important;
                background-color: #f5f5f5 !important;
            }
            
            .navbar-top-links {
                display: none !important;
            }
            
            .sidebar-collapse {
                padding-top: 0;
            }
            
            .sidebar-collapse .nav > li > a {
                padding: 15px 15px 15px 25px;
            }
            
            .mobile-only {
                display: block !important;
                border-top: 1px solid #2C5F7C;
            }
            
            .mobile-only:first-of-type {
                margin-top: 10px;
            }
            
            #wrapper {
                overflow-x: hidden !important;
                background-color: #f5f5f5 !important;
            }
            
            body {
                background-color: #f5f5f5 !important;
            }
            
            /* Danger Zone Mobile Styles */
            .btn-danger {
                width: 100%;
                text-align: center;
            }
        }
        
        /* Wrapper for disabled button tooltip */
        .btn-danger-wrapper {
            position: relative;
            display: inline-block;
        }
        
        /* Disabled Account Deletion Button Styles */
        .btn-danger:disabled,
        .btn-danger[disabled] {
            opacity: 0.6 !important;
            cursor: not-allowed !important;
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            pointer-events: auto !important;
        }
        
        .btn-danger:disabled:hover,
        .btn-danger[disabled]:hover {
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            opacity: 0.6 !important;
        }
        
        /* Class-based tooltip for better browser support - only show when has-disabled class */
        .btn-danger-wrapper.has-disabled:hover::after {
            content: "Penalties are currently unpaid" !important;
            position: absolute !important;
            bottom: calc(100% + 10px) !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            background: #333 !important;
            color: #fff !important;
            padding: 8px 12px !important;
            border-radius: 4px !important;
            font-size: 13px !important;
            white-space: nowrap !important;
            z-index: 10000 !important;
            pointer-events: none !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2) !important;
        }
        
        .btn-danger-wrapper.has-disabled:hover::before {
            content: "" !important;
            position: absolute !important;
            bottom: calc(100% + 4px) !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            border: 6px solid transparent !important;
            border-top-color: #333 !important;
            z-index: 10001 !important;
            pointer-events: none !important;
        }
        
        /* Fallback: Direct button hover (some browsers allow hover on disabled buttons) */
        .btn-danger:disabled:hover::after,
        .btn-danger[disabled]:hover::after {
            content: "Penalties are currently unpaid" !important;
            position: absolute !important;
            bottom: calc(100% + 10px) !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            background: #333 !important;
            color: #fff !important;
            padding: 8px 12px !important;
            border-radius: 4px !important;
            font-size: 13px !important;
            white-space: nowrap !important;
            z-index: 10000 !important;
            pointer-events: none !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2) !important;
        }
        
        .btn-danger:disabled:hover::before,
        .btn-danger[disabled]:hover::before {
            content: "" !important;
            position: absolute !important;
            bottom: calc(100% + 4px) !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            border: 6px solid transparent !important;
            border-top-color: #333 !important;
            z-index: 10001 !important;
            pointer-events: none !important;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <nav class="navbar navbar-default top-navbar" role="navigation">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".sidebar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="dashboard.php"><img src="../images/header_logo.png" width="150"></a>
            </div>
            
            <ul class="nav navbar-top-links navbar-right">
                <li>
                    <a class="active-menu" href="profile.php"><i class="fa fa-user-circle"></i> Profile</a>
                </li>
                <li>
                    <a href="logout.php"><i class="fa fa-sign-out"></i> Log Out</a>
                </li>
            </ul>
        </nav>
        <!--/. NAV TOP  -->
        <nav class="navbar-default navbar-side" role="navigation">
            <div class="sidebar-collapse">
                <ul class="nav" id="main-menu">
                    <li>
                        <a href="dashboard.php"><i class="fa fa-calendar fa-fw"></i> Bookings</a>
                    </li>
                    <li>
                        <a href="penalties.php"><i class="fa fa-gavel fa-fw"></i> Penalties</a>
                    </li>
                    <li class="mobile-only" style="display: none;">
                        <a class="active-menu" href="profile.php"><i class="fa fa-user-circle fa-fw"></i> Profile</a>
                    </li>
                    <li class="mobile-only" style="display: none;">
                        <a href="logout.php"><i class="fa fa-sign-out fa-fw"></i> Log Out</a>
                    </li>
                </ul>
            </div>
        </nav>
        <!-- /. NAV SIDE  -->
        <div id="page-wrapper">
            <div id="page-inner">
                <div class="row">
                    <div class="col-md-12">
                        <div style="border-bottom: 3px solid #ddd; padding-bottom: 15px; margin-bottom: 25px;">
                            <h1 style="margin: 0; font-size: 26px; font-weight: 400; color: #5a5a5a;">MY PROFILE</h1>
                        </div>
                    </div>
                </div>
                <!-- /. ROW  -->
                
                <div class="row">
                    <div class="col-md-12">
                        <!-- Profile Fields Container -->
                        <div class="profile-fields-container">
                            <!-- Profile Photo Field -->
                            <div class="profile-photo-field">
                                <div class="photo-preview-container">
                                    <img src="" 
                                         alt="Profile Photo" 
                                         class="profile-photo-preview" 
                                         id="profilePhotoPreview">
                                    <div class="photo-upload-icon" onclick="openUpdateModal('photo')">
                                        <i class="fa fa-camera"></i>
                                    </div>
                                </div>
                                <div class="photo-info">
                                    <h4>Profile Photo</h4>
                                    <p>Click the camera icon to upload or update your profile photo.<br>
                                    Max size: 10MB.</p>
                                </div>
                            </div>
                            
                            <!-- Username Field -->
                            <div class="profile-field">
                                <div class="field-icon">
                                    <i class="fa fa-user"></i>
                                </div>
                                <div class="field-content">
                                    <label>Username</label>
                                    <div class="field-value" id="username">Loading...</div>
                                </div>
                            </div>
                            
                            <!-- Email Field -->
                            <div class="profile-field">
                                <div class="field-icon">
                                    <i class="fa fa-envelope"></i>
                                </div>
                                <div class="field-content">
                                    <label>Email Address</label>
                                    <div class="field-value" id="email">Loading...</div>
                                </div>
                                <div class="field-action">
                                    <button class="btn btn-primary btn-sm update-btn" onclick="openUpdateModal('email')">
                                        <i class="fa fa-edit"></i> Update
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Password Field -->
                            <div class="profile-field">
                                <div class="field-icon">
                                    <i class="fa fa-lock"></i>
                                </div>
                                <div class="field-content">
                                    <label>Password</label>
                                    <div class="field-value">••••••••••</div>
                                </div>
                                <div class="field-action">
                                    <button class="btn btn-primary btn-sm update-btn" onclick="openUpdateModal('password')">
                                        <i class="fa fa-edit"></i> Update
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!--End Profile Fields Container -->
                        
                        <!-- Account Deletion Section -->
                        <div style="margin-top: 40px; padding: 25px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-left: 4px solid #dc3545;">
                            <h4 style="margin: 0 0 10px 0; color: #dc3545; font-weight: 600;">
                                <i class="fa fa-exclamation-triangle"></i> Danger Zone
                            </h4>
                            <p style="margin: 0 0 15px 0; color: #666; font-size: 14px;">
                                Once you delete your account, there is no going back. Please be certain.
                            </p>
                            <div class="btn-danger-wrapper" style="display: inline-block; position: relative;">
                                <button class="btn btn-danger" onclick="openDeleteModal()" style="padding: 10px 24px; font-weight: 600; position: relative;">
                                    <i class="fa fa-trash"></i> Request Account Deletion
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /. ROW  -->
            </div>
        </div>
        <!-- /. PAGE WRAPPER  -->
    </div>
    <!-- /. WRAPPER  -->
    
    <!-- Update Profile Modal -->
    <div id="updateModal" class="modal-overlay">
        <div class="update-modal">
            <div class="update-modal-header">
                <h3 id="modalTitle">Update Information</h3>
                <button class="close-modal" onclick="closeUpdateModal()">&times;</button>
            </div>
            <div class="update-modal-body">
                <form id="updateForm">
                    <!-- Photo Update Form -->
                    <div id="photoForm" class="update-form" style="display: none;">
                        <div class="upload-preview-container" id="uploadPreviewContainer">
                            <img src="" alt="Upload Preview" class="upload-preview" id="uploadPreview">
                        </div>
                        <div class="photo-upload-area" id="photoUploadArea">
                            <div class="upload-icon">
                                <i class="fa fa-cloud-upload"></i>
                            </div>
                            <div class="upload-text">
                                <h4>Click to upload or drag and drop</h4>
                                <p>JPG, JPEG, PNG or GIF (Max size: 10MB)</p>
                            </div>
                        </div>
                        <input type="file" id="photoFileInput" class="file-input-hidden" accept="image/jpeg,image/jpg,image/png,image/gif">
                    </div>
                    
                    <!-- Email Update Form -->
                    <div id="emailForm" class="update-form" style="display: none;">
                        <div class="form-group-update">
                            <label for="newEmail">New Email Address <span class="required">*</span></label>
                            <input type="email" id="newEmail" placeholder="e.g., marcus.chen@fortiroom.com" required>
                        </div>
                        <div class="form-group-update" id="emailCodeSection" style="display: none;">
                            <label for="emailCode">Verification Code <span class="required">*</span></label>
                            <input type="text" id="emailCode" placeholder="Enter the 6-digit code sent to your new email" maxlength="6" pattern="[0-9]{6}" style="text-align: center; font-size: 18px; letter-spacing: 4px; font-weight: 600;">
                            <p style="margin-top: 8px; font-size: 13px; color: #6c757d;">
                                <i class="fa fa-info-circle"></i> Check your new email inbox (and spam folder) for the verification code. Enter the 6-digit code you received.
                            </p>

                        </div>
                        <div class="form-group-update">
                            <button type="button" id="requestCodeBtn" class="btn btn-primary" style="width: 100%; padding: 10px; margin-top: 5px;" onclick="requestEmailCode()">
                                <i class="fa fa-paper-plane"></i> Request Code
                            </button>
                            <p style="margin-top: 10px; font-size: 12px; color: #6c757d; text-align: center;">
                                A verification code will be sent to the new email address
                            </p>
                        </div>
                    </div>
                    
                    <!-- Password Update Form -->
                    <div id="passwordForm" class="update-form" style="display: none;">
                        <div class="form-group-update">
                            <label for="currentPassword">Current Password <span class="required">*</span></label>
                            <input type="password" id="currentPassword" placeholder="Enter your current password" required>
                            <i class="fa fa-eye-slash toggle-password" data-target="currentPassword"></i>
                        </div>
                        <div class="form-group-update">
                            <label for="newPassword">New Password <span class="required">*</span></label>
                            <input type="password" id="newPassword" placeholder="Enter new password (min. 8 characters)" required>
                            <i class="fa fa-eye-slash toggle-password" data-target="newPassword"></i>
                        </div>
                        <div class="form-group-update">
                            <label for="confirmPassword">Confirm New Password <span class="required">*</span></label>
                            <input type="password" id="confirmPassword" placeholder="Re-enter new password" required>
                            <i class="fa fa-eye-slash toggle-password" data-target="confirmPassword"></i>
                        </div>
                    </div>
                    
                </form>
            </div>
            <div class="update-modal-footer">
                <button class="btn-cancel" onclick="closeUpdateModal()">Cancel</button>
                <button class="btn-save" onclick="saveUpdate()">Update</button>
            </div>
        </div>
    </div>
    
    <!-- Account Deletion Modal -->
    <div id="deleteModal" class="modal-overlay">
        <div class="update-modal">
            <div class="update-modal-header" style="border-bottom: 2px solid #dc3545;">
                <h3 id="deleteModalTitle" style="color: #dc3545;">
                    <i class="fa fa-exclamation-triangle"></i> Confirm Account Deletion
                </h3>
                <button class="close-modal" onclick="closeDeleteModal()">&times;</button>
            </div>
            <div class="update-modal-body">
                <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 15px; margin-bottom: 20px;">
                    <p style="margin: 0; color: #856404; font-size: 14px;">
                        <i class="fa fa-warning"></i> <strong>Warning:</strong> This action cannot be undone. Your account deletion request will be submitted for processing.
                    </p>
                </div>
                <p style="color: #333; font-size: 15px; line-height: 1.6; margin: 0;">
                    Are you sure you want to request account deletion? This will permanently delete all your data including bookings, history, and profile information.
                </p>
            </div>
            <div class="update-modal-footer">
                <button class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button class="btn-save" onclick="confirmDeletion()" style="background: #dc3545;">
                    <i class="fa fa-trash"></i> Confirm Deletion
                </button>
            </div>
        </div>
    </div>
    
    <!-- JS Scripts-->
    <!-- jQuery Js -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <!-- Bootstrap Js -->
    <script src="assets/js/bootstrap.min.js"></script>
    <!-- Metis Menu Js -->
    <script src="assets/js/jquery.metisMenu.js"></script>
    <!-- DATA TABLE SCRIPTS -->
    <script src="assets/js/dataTables/jquery.dataTables.js"></script>
    <script src="assets/js/dataTables/dataTables.bootstrap.js"></script>
    <script>
        var currentUpdateField = null;
        var selectedPhotoFile = null;
        var supabase = null;
        var currentUser = null;
        var hasPendingPenalties = false;
        var profileData = {
            username: '',
            email: '',
            photo: ''
        };
        
        // Initialize Supabase and load user data
        document.addEventListener('DOMContentLoaded', async function() {
            // Initialize Supabase
            const { createClient } = window.supabase || {};
            if (!createClient) {
                console.error('Supabase library failed to load.');
                return;
            }
            supabase = createClient(window.__SUPABASE__.url, window.__SUPABASE__.anonKey);
            
            // Check if user is logged in
            const { data: sessionData, error: sessionError } = await supabase.auth.getSession();
            if (sessionError || !sessionData?.session) {
                // Redirect to login if not authenticated
                window.location.href = '../login.php';
                return;
            }
            
            currentUser = sessionData.session.user;
            
            // Check for pending penalties
            await checkPendingPenalties();
            
            await loadUserProfile();
        });
        
        // Check for pending penalties
        async function checkPendingPenalties() {
            if (!currentUser || !supabase) {
                return;
            }
            
            try {
                const { data: penalties, error: penaltiesError } = await supabase
                    .from('penalties')
                    .select('id, status')
                    .eq('user_id', currentUser.id)
                    .eq('status', 'pending')
                    .limit(1);
                
                if (penaltiesError) {
                    // If table doesn't exist, assume no penalties
                    if (penaltiesError.message && (
                        penaltiesError.message.includes('does not exist') || 
                        penaltiesError.message.includes('relation') || 
                        penaltiesError.message.includes('42P01')
                    )) {
                        hasPendingPenalties = false;
                    } else {
                        console.error('Error checking pending penalties:', penaltiesError);
                        hasPendingPenalties = false;
                    }
                } else {
                    hasPendingPenalties = penalties && penalties.length > 0;
                }
                
                // Update Account Deletion button state
                updateAccountDeletionButtonState();
            } catch (error) {
                console.error('Error in checkPendingPenalties:', error);
                hasPendingPenalties = false;
                updateAccountDeletionButtonState();
            }
        }
        
        // Update Account Deletion button state based on pending penalties
        function updateAccountDeletionButtonState() {
            const deleteBtn = $('.btn-danger');
            if (!deleteBtn.length) {
                return;
            }
            
            if (hasPendingPenalties) {
                // Set disabled attribute
                deleteBtn.prop('disabled', true);
                deleteBtn.attr('disabled', 'disabled');
                
                // Force styles using inline styles with !important
                const btnElement = deleteBtn[0];
                if (btnElement) {
                    btnElement.style.setProperty('opacity', '0.6', 'important');
                    btnElement.style.setProperty('cursor', 'not-allowed', 'important');
                    btnElement.style.setProperty('background-color', '#6c757d', 'important');
                    btnElement.style.setProperty('border-color', '#6c757d', 'important');
                    btnElement.style.setProperty('position', 'relative', 'important');
                }
                
                // Add class to wrapper for tooltip
                let wrapper = deleteBtn.closest('.btn-danger-wrapper');
                if (!wrapper.length) {
                    // Wrap button if wrapper doesn't exist
                    deleteBtn.wrap('<div class="btn-danger-wrapper" style="display: inline-block; position: relative;"></div>');
                    wrapper = deleteBtn.closest('.btn-danger-wrapper');
                }
                if (wrapper.length) {
                    wrapper.addClass('has-disabled');
                }
            } else {
                // Remove disabled attribute
                deleteBtn.prop('disabled', false);
                deleteBtn.removeAttr('disabled');
                
                // Remove inline styles
                const btnElement = deleteBtn[0];
                if (btnElement) {
                    btnElement.style.removeProperty('opacity');
                    btnElement.style.removeProperty('cursor');
                    btnElement.style.removeProperty('background-color');
                    btnElement.style.removeProperty('border-color');
                    btnElement.style.removeProperty('position');
                }
                
                // Remove class from wrapper
                const wrapper = deleteBtn.closest('.btn-danger-wrapper');
                if (wrapper.length) {
                    wrapper.removeClass('has-disabled');
                }
            }
        }
        
        // Load user profile data from Supabase
        async function loadUserProfile() {
            if (!currentUser) return;
            
            // Get username from user_metadata
            const username = currentUser.user_metadata?.username || currentUser.email?.split('@')[0] || 'User';
            const email = currentUser.email || 'No email';
            const avatarUrl = currentUser.user_metadata?.avatar_url || null;
            
            // Update UI
            document.getElementById('username').textContent = username;
            document.getElementById('email').textContent = email;
            
            // Update profile photo
            const photoPreview = document.getElementById('profilePhotoPreview');
            if (avatarUrl) {
                photoPreview.src = avatarUrl;
            } else {
                // Generate avatar from username
                const encodedName = encodeURIComponent(username);
                photoPreview.src = `https://ui-avatars.com/api/?name=${encodedName}&size=120&background=3F729B&color=fff&font-size=0.4&bold=true`;
            }
            
            // Store in profileData for compatibility
            profileData = {
                username: username,
                email: email,
                photo: avatarUrl || photoPreview.src
            };
        }
        
        function openUpdateModal(field) {
            currentUpdateField = field;
            
            // Hide all forms
            $('.update-form').hide();
            
            // Show the appropriate form and update modal title
            if (field === 'photo') {
                $('#photoForm').show();
                $('#modalTitle').text('Update Profile Photo');
                selectedPhotoFile = null;
                $('#uploadPreviewContainer').hide();
            } else if (field === 'email') {
                $('#emailForm').show();
                $('#modalTitle').text('Update Email Address');
            } else if (field === 'password') {
                $('#passwordForm').show();
                $('#modalTitle').text('Update Password');
            } else if (field === 'phone') {
                $('#phoneForm').show();
                $('#modalTitle').text('Update Phone Number');
            }
            
            // Show modal
            $('#updateModal').addClass('active');
            $('body').css('overflow', 'hidden');
        }
        
        function closeUpdateModal() {
            $('#updateModal').removeClass('active');
            $('body').css('overflow', '');
            
            // Reset form
            $('#updateForm')[0].reset();
            currentUpdateField = null;
            
            // Reset photo upload state
            selectedPhotoFile = null;
            $('#uploadPreviewContainer').hide();
            $('#photoFileInput').val('');
            
            // Reset email verification state
            $('#emailCodeSection').hide();
            $('#emailCode').val('');
            
            // Clear countdown if active
            if (countdownInterval) {
                clearInterval(countdownInterval);
                countdownInterval = null;
            }
            
            // Reset button
            $('#requestCodeBtn').prop('disabled', false);
            $('#requestCodeBtn').css({
                'background-color': '',
                'cursor': '',
                'opacity': ''
            });
            $('#requestCodeBtn').html('<i class="fa fa-paper-plane"></i> Request Code');
            lastCodeRequestTime = 0;
        }
        
        // Track when code was last requested to prevent rate limiting
        var lastCodeRequestTime = 0;
        var CODE_REQUEST_COOLDOWN = 60000; // 60 seconds (1 minute)
        var countdownInterval = null;
        
        // Helper function to handle logout after email update for security
        async function logoutAfterEmailUpdate() {
            // Update UI first
            closeUpdateModal();
            
            // Show security notification
            alert('Email address updated successfully!\n\nFor security purposes, the system will log you out. Please log in again with your new email address.');
            
            // Log out from Supabase
            try {
                await supabase.auth.signOut();
                console.log('User logged out after email update');
            } catch (logoutError) {
                console.error('Logout error:', logoutError);
            }
            
            // Redirect to login page
            setTimeout(() => {
                window.location.href = '../login.php';
            }, 500);
        }
        
        // Helper function to handle logout after password update for security
        async function logoutAfterPasswordUpdate() {
            // Update UI first
            closeUpdateModal();
            
            // Show security notification
            alert('Password updated successfully!\n\nFor security purposes, the system will log you out. Please log in again with your new password.');
            
            // Log out from Supabase
            try {
                await supabase.auth.signOut();
                console.log('User logged out after password update');
            } catch (logoutError) {
                console.error('Logout error:', logoutError);
            }
            
            // Redirect to login page
            setTimeout(() => {
                window.location.href = '../login.php';
            }, 500);
        }
        
        // Start countdown timer on the button
        function startCountdown() {
            var remainingSeconds = CODE_REQUEST_COOLDOWN / 1000;
            var btn = $('#requestCodeBtn');
            
            // Clear any existing interval
            if (countdownInterval) {
                clearInterval(countdownInterval);
            }
            
            // Disable button and style it as grayed out
            btn.prop('disabled', true);
            btn.css({
                'background-color': '#6c757d',
                'cursor': 'not-allowed',
                'opacity': '0.6'
            });
            
            // Update button text with countdown
            function updateCountdown() {
                if (remainingSeconds > 0) {
                    btn.html('<i class="fa fa-clock-o"></i> Request Code (' + remainingSeconds + 's)');
                    remainingSeconds--;
                } else {
                    // Countdown finished, enable button
                    clearInterval(countdownInterval);
                    btn.prop('disabled', false);
                    btn.css({
                        'background-color': '',
                        'cursor': '',
                        'opacity': ''
                    });
                    btn.html('<i class="fa fa-paper-plane"></i> Request Code');
                    countdownInterval = null;
                }
            }
            
            // Update immediately
            updateCountdown();
            
            // Update every second
            countdownInterval = setInterval(updateCountdown, 1000);
        }
        
        // Request verification code for email change
        async function requestEmailCode() {
            if (!supabase || !currentUser) {
                alert('Please wait for the page to load completely.');
                return;
            }
            
            var newEmail = $('#newEmail').val().trim().toLowerCase();
            
            if (!newEmail) {
                alert('Please enter a new email address first.');
                return;
            }
            
            // Validate email format
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(newEmail)) {
                alert('Please enter a valid email address.');
                return;
            }
            
            // Check if it's the same as current email
            if (newEmail === currentUser.email) {
                alert('The new email address must be different from your current email.');
                return;
            }
            
            // Check rate limit - prevent requesting too soon
            var now = Date.now();
            var timeSinceLastRequest = now - lastCodeRequestTime;
            
            if (lastCodeRequestTime > 0 && timeSinceLastRequest < CODE_REQUEST_COOLDOWN) {
                var remainingSeconds = Math.ceil((CODE_REQUEST_COOLDOWN - timeSinceLastRequest) / 1000);
                alert('Please wait ' + remainingSeconds + ' more seconds before requesting another code.');
                return;
            }
            
            // Disable button and show loading state
            $('#requestCodeBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
            
            try {
                console.log('Requesting email change for:', newEmail);
                
                const { data: updateData, error: updateError } = await supabase.auth.updateUser({
                    email: newEmail,
                    options: {
                        emailRedirectTo: window.location.origin + window.location.pathname
                    }
                });
                
                console.log('Update user response:', { data: updateData, error: updateError });
                
                if (updateError) {
                    console.error('Email update error details:', {
                        message: updateError.message,
                        status: updateError.status,
                        error: updateError
                    });
                    
                    // Check if the error is because email change requires confirmation
                    // In this case, Supabase should have sent an email anyway
                    if (updateError.message && (
                        updateError.message.includes('email_change') || 
                        updateError.message.includes('confirmation') || 
                        updateError.message.includes('sent') ||
                        updateError.message.includes('verify') ||
                        updateError.message.includes('check your email')
                    ) && !updateError.message.includes('rate limit') && !updateError.message.includes('40')) {
                        // Email was likely sent, show code input
                        console.log('Email was sent despite error, showing code input');
                        lastCodeRequestTime = Date.now();
                        startCountdown();
                        $('#emailCodeSection').slideDown();
                        alert('Verification code has been sent to ' + newEmail + '. Please check your inbox (and spam folder) and enter the code below.');
                        return;
                    }
                    
                    // Check for rate limit errors
                    if (updateError.message && (updateError.message.includes('rate limit') || 
                        updateError.message.includes('40') || 
                        updateError.message.includes('security purposes'))) {
                        var waitTime = 45; // seconds
                        throw new Error('Please wait ' + waitTime + ' seconds before requesting another code. This is a security measure.');
                    }
                    
                    // If it's a different error, show it
                    throw updateError;
                }
                
                // Record the time when code was requested
                lastCodeRequestTime = Date.now();
                
                // Start countdown timer
                startCountdown();
                
                const { data: sessionData } = await supabase.auth.getSession();
                console.log('Current session after update:', sessionData);
                
                if (sessionData?.session?.user?.email === newEmail) {
                    // Email was updated immediately (if email confirmation is disabled)
                    console.log('Email updated immediately');
                    $('#email').text(newEmail);
                    profileData.email = newEmail;
                    currentUser = sessionData.session.user;
                    await logoutAfterEmailUpdate();
                    return;
                }
                
                // Email change requires confirmation - show code input
                console.log('Email change requires confirmation, showing code input');
                $('#emailCodeSection').slideDown();
                alert('Verification code has been sent to ' + newEmail + '. Please check your inbox (and spam folder) and enter the code below.');
                
            } catch (error) {
                console.error('Request code error:', error);
                
                // Only reset button if countdown hasn't started (i.e., request failed before sending)
                // If countdown is active, keep it running
                if (!countdownInterval) {
                    $('#requestCodeBtn').prop('disabled', false);
                    $('#requestCodeBtn').css({
                        'background-color': '',
                        'cursor': '',
                        'opacity': ''
                    });
                    $('#requestCodeBtn').html('<i class="fa fa-paper-plane"></i> Request Code');
                }
                
                // Provide helpful error messages
                let errorMessage = 'Failed to send verification code. ';
                
                if (error.message) {
                    if (error.message.includes('rate limit') || error.message.includes('too many')) {
                        errorMessage = 'Too many requests. Please wait a few minutes before requesting another code.';
                    } else if (error.message.includes('already') || error.message.includes('in use')) {
                        errorMessage = 'This email address is already in use. Please use a different email.';
                    } else if (error.message.includes('invalid')) {
                        errorMessage = 'Invalid email address. Please check and try again.';
                    } else {
                        errorMessage += error.message;
                    }
                } else {
                    errorMessage += 'Please try again.';
                }
                
                alert(errorMessage);
            }
        }
        
        async function saveUpdate() {
            if (!currentUpdateField || !supabase || !currentUser) return;
            
            if (currentUpdateField === 'photo') {
                if (!selectedPhotoFile) {
                    alert('Please select a photo to upload.');
                    return;
                }
                
                try {
                    // Upload photo to Supabase Storage via server endpoint
                    const fd = new FormData();
                    fd.append('file', selectedPhotoFile);
                    fd.append('username', currentUser.user_metadata?.username || currentUser.email?.split('@')[0] || 'user');
                    
                    const res = await fetch('../upload_avatar.php', { method: 'POST', body: fd });
                    const json = await res.json().catch(() => ({}));
                    
                    if (!res.ok || json.error) {
                        throw new Error(json.error || 'Avatar upload failed.');
                    }
                    
                    const avatarUrl = json.publicUrl || null;
                    
                    // Update user metadata with new avatar URL
                    const { error: updateError } = await supabase.auth.updateUser({
                        data: { 
                            ...currentUser.user_metadata,
                            avatar_url: avatarUrl 
                        }
                    });
                    
                    if (updateError) {
                        throw updateError;
                    }
                    
                    // Update UI
                    $('#profilePhotoPreview').attr('src', avatarUrl);
                    profileData.photo = avatarUrl;
                    
                    // Reload user data to get updated metadata
                    const { data: sessionData } = await supabase.auth.getSession();
                    if (sessionData?.session) {
                        currentUser = sessionData.session.user;
                    }
                    
                    closeUpdateModal();
                    alert('Profile photo updated successfully!');
                } catch (error) {
                    console.error('Photo update error:', error);
                    alert(error.message || 'Failed to update profile photo. Please try again.');
                }
                
            } else if (currentUpdateField === 'email') {
                var newEmail = $('#newEmail').val().trim().toLowerCase();
                var emailCode = $('#emailCode').val().trim();
                
                if (!newEmail) {
                    alert('Please enter a new email address.');
                    return;
                }
                
                // Validate email format
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(newEmail)) {
                    alert('Please enter a valid email address.');
                    return;
                }
                
                // Check if code section is visible (code was requested)
                if (!$('#emailCodeSection').is(':visible')) {
                    alert('Please request a verification code first.');
                    return;
                }
                
                if (!emailCode || emailCode.length !== 6) {
                    alert('Please enter the 6-digit verification code.');
                    return;
                }
                
                try {
                    // Verify the OTP code for email change
                    // IMPORTANT: verifyOtp with type 'email_change' will automatically update the email
                    // We should NOT call updateUser again as it causes rate limiting issues
                    console.log('Verifying OTP code for email change:', { email: newEmail, codeLength: emailCode.length });
                    
                    const { data: verifyData, error: verifyError } = await supabase.auth.verifyOtp({
                        email: newEmail,
                        token: emailCode,
                        type: 'email_change'
                    });
                    
                    if (verifyError) {
                        console.error('OTP verification error:', verifyError);
                        
                        // Handle specific error cases
                        if (verifyError.message) {
                            // Rate limit error
                            if (verifyError.message.includes('40') || verifyError.message.includes('rate limit') || 
                                verifyError.message.includes('security purposes')) {
                                throw new Error('Please wait a moment before verifying. For security, there is a 60-second cooldown between email change requests. Please try again in a few seconds.');
                            }
                            
                            // Expired token
                            if (verifyError.message.includes('expired') || verifyError.message.includes('Expired')) {
                                throw new Error('The verification code has expired. Please request a new code.');
                            }
                            
                            // Invalid token
                            if (verifyError.message.includes('Invalid') || verifyError.message.includes('invalid') || 
                                verifyError.message.includes('token')) {
                                throw new Error('Invalid verification code. Please check the code and try again.');
                            }
                        }
                        
                        throw verifyError;
                    }
                    
                    console.log('OTP verification successful:', verifyData);
                    
                    // After successful OTP verification with type 'email_change', Supabase should automatically update the email
                    // First, update the session if provided in the response
                    if (verifyData?.session) {
                        await supabase.auth.setSession(verifyData.session);
                        console.log('Session updated from verifyOtp response');
                    }
                    
                    // Check if the user data in the response has the updated email
                    let emailUpdated = false;
                    if (verifyData?.user?.email === newEmail) {
                        console.log('Email found in verifyOtp response user data');
                        emailUpdated = true;
                        currentUser = verifyData.user;
                    } else if (verifyData?.session?.user?.email === newEmail) {
                        console.log('Email found in verifyOtp response session data');
                        emailUpdated = true;
                        currentUser = verifyData.session.user;
                    }
                    
                    // Refresh the session to get the latest data
                    console.log('Refreshing session to get latest user data...');
                    const { data: refreshData, error: refreshError } = await supabase.auth.refreshSession();
                    
                    if (!refreshError && refreshData?.session) {
                        console.log('Session refreshed successfully');
                        if (refreshData.session.user?.email === newEmail) {
                            console.log('Email found in refreshed session');
                            emailUpdated = true;
                            currentUser = refreshData.session.user;
                        }
                    }
                    
                    // Get the latest user data directly
                    console.log('Getting latest user data...');
                    const { data: userData, error: userError } = await supabase.auth.getUser();
                    
                    if (!userError && userData?.user) {
                        console.log('Current user data:', userData.user);
                        if (userData.user.email === newEmail) {
                            console.log('Email successfully updated to:', newEmail);
                            emailUpdated = true;
                            currentUser = userData.user;
                        } else {
                            console.warn('Email not yet updated. Current:', userData.user.email, 'Expected:', newEmail);
                        }
                    }
                    
                    // Also check session data
                    const { data: sessionData } = await supabase.auth.getSession();
                    if (sessionData?.session?.user?.email === newEmail) {
                        console.log('Email found in session data');
                        emailUpdated = true;
                        currentUser = sessionData.session.user;
                    }
                    
                    if (emailUpdated) {
                        // Email was updated successfully
                        $('#email').text(newEmail);
                        profileData.email = newEmail;
                        await logoutAfterEmailUpdate();
                        return;
                    }
                    
                    // If email wasn't updated automatically, we need to apply it manually
                    // This can happen if Supabase requires explicit confirmation
                    console.log('Email not automatically updated, attempting manual update...');
                    
                    // Wait a moment for Supabase to process
                    await new Promise(resolve => setTimeout(resolve, 500));
                    
                    // Try to update the email explicitly
                    // Since OTP is verified, this should work without sending another email
                    const { data: updateData, error: updateError } = await supabase.auth.updateUser({
                        email: newEmail
                    });
                    
                    if (updateError) {
                        console.error('Manual update error:', updateError);
                        // Check if it's because email is already the same/updated
                        if (updateError.message && (
                            updateError.message.includes('already') ||
                            updateError.message.includes('same') ||
                            updateError.message.includes('unchanged') ||
                            updateError.message.includes('identical')
                        )) {
                            // Email is already updated, just refresh
                            console.log('Email already updated according to error message');
                            $('#email').text(newEmail);
                            profileData.email = newEmail;
                            await logoutAfterEmailUpdate();
                            return;
                        }
                        
                        // If it's a rate limit, the email might still be updated
                        if (updateError.message && updateError.message.includes('rate limit')) {
                            console.log('Rate limit hit, but checking if email was updated...');
                            const { data: checkData } = await supabase.auth.getUser();
                            if (checkData?.user?.email === newEmail) {
                                $('#email').text(newEmail);
                                profileData.email = newEmail;
                                currentUser = checkData.user;
                                await logoutAfterEmailUpdate();
                                return;
                            }
                        }
                        
                        throw new Error('Failed to update email: ' + updateError.message);
                    }
                    
                    // Final check after manual update
                    const { data: finalUserData } = await supabase.auth.getUser();
                    if (finalUserData?.user?.email === newEmail) {
                        console.log('Email updated after manual update');
                        currentUser = finalUserData.user;
                        $('#email').text(newEmail);
                        profileData.email = newEmail;
                        await logoutAfterEmailUpdate();
                        return;
                    }
                    
                    // If we get here, update optimistically and logout
                    console.log('Updating UI optimistically and logging out');
                    $('#email').text(newEmail);
                    profileData.email = newEmail;
                    await logoutAfterEmailUpdate();
                    
                } catch (error) {
                    console.error('Email update error:', error);
                    
                    let errorMessage = 'Failed to update email. ';
                    
                    if (error.message) {
                        if (error.message.includes('40') || error.message.includes('rate limit') || 
                            error.message.includes('security purposes')) {
                            errorMessage = 'Please wait a moment before verifying. For security, there is a 60-second cooldown between email change requests.';
                        } else if (error.message.includes('expired') || error.message.includes('Expired')) {
                            errorMessage = 'The verification code has expired. Please request a new code.';
                        } else if (error.message.includes('Invalid') || error.message.includes('invalid')) {
                            errorMessage = 'Invalid verification code. Please check the code and try again.';
                        } else {
                            errorMessage += error.message;
                        }
                    } else {
                        errorMessage += 'Please try again.';
                    }
                    
                    alert(errorMessage);
                }
                
            } else if (currentUpdateField === 'password') {
                var currentPassword = $('#currentPassword').val();
                var newPassword = $('#newPassword').val();
                var confirmPassword = $('#confirmPassword').val();
                
                if (!currentPassword || !newPassword || !confirmPassword) {
                    alert('Please fill in all fields.');
                    return;
                }
                
                // Check if new password is the same as current password
                if (currentPassword === newPassword) {
                    alert('New password must be different from your current password.');
                    return;
                }
                
                if (newPassword !== confirmPassword) {
                    alert('New passwords do not match. Please try again.');
                    return;
                }
                
                if (newPassword.length < 8) {
                    alert('Password must be at least 8 characters long.');
                    return;
                }
                
                // Validate password strength
                if (!/[A-Z]/.test(newPassword) || !/[0-9]/.test(newPassword) || !/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(newPassword)) {
                    alert('Password must contain at least 1 uppercase letter, 1 number, and 1 symbol.');
                    return;
                }
                
                try {
                    // Show loading state
                    var saveBtn = $('.btn-save');
                    var originalBtnText = saveBtn.html();
                    saveBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');
                    
                    // First, verify the current password by attempting to sign in
                    console.log('Verifying current password...');
                    const { data: verifyData, error: verifyError } = await supabase.auth.signInWithPassword({
                        email: currentUser.email,
                        password: currentPassword
                    });
                    
                    if (verifyError) {
                        console.error('Current password verification failed:', verifyError);
                        saveBtn.prop('disabled', false).html(originalBtnText);
                        
                        if (verifyError.message && (
                            verifyError.message.includes('Invalid') || 
                            verifyError.message.includes('invalid') ||
                            verifyError.message.includes('credentials') ||
                            verifyError.message.includes('password')
                        )) {
                            throw new Error('Current password is incorrect. Please try again.');
                        }
                        throw verifyError;
                    }
                    
                    console.log('Current password verified successfully');
                    
                    // Now update to the new password
                    console.log('Updating password...');
                    const { data: updateData, error: updateError } = await supabase.auth.updateUser({
                        password: newPassword
                    });
                    
                    if (updateError) {
                        console.error('Password update error:', updateError);
                        saveBtn.prop('disabled', false).html(originalBtnText);
                        
                        if (updateError.message && updateError.message.includes('same')) {
                            throw new Error('New password must be different from your current password.');
                        }
                        throw updateError;
                    }
                    
                    console.log('Password updated successfully');
                    
                    // Reset button
                    saveBtn.prop('disabled', false).html(originalBtnText);
                    
                    // Log out for security purposes
                    await logoutAfterPasswordUpdate();
                    
                } catch (error) {
                    console.error('Password update error:', error);
                    
                    // Reset button
                    var saveBtn = $('.btn-save');
                    if (saveBtn.prop('disabled')) {
                        saveBtn.prop('disabled', false).html('Update');
                    }
                    
                    let errorMessage = 'Failed to update password. ';
                    if (error.message) {
                        if (error.message.includes('incorrect') || error.message.includes('Invalid')) {
                            errorMessage = error.message;
                        } else if (error.message.includes('same')) {
                            errorMessage = 'New password must be different from your current password.';
                        } else {
                            errorMessage += error.message;
                        }
                    } else {
                        errorMessage += 'Please try again.';
                    }
                    alert(errorMessage);
                }
                
            } else if (currentUpdateField === 'phone') {
                var newPhone = $('#newPhone').val();
                var confirmPhone = $('#confirmPhone').val();
                
                if (!newPhone || !confirmPhone) {
                    alert('Please fill in all fields.');
                    return;
                }
                
                if (newPhone !== confirmPhone) {
                    alert('Phone numbers do not match. Please try again.');
                    return;
                }
                
                try {
                    // Update phone in user metadata
                    const { error: updateError } = await supabase.auth.updateUser({
                        data: { 
                            ...currentUser.user_metadata,
                            phone: newPhone 
                        }
                    });
                    
                    if (updateError) {
                        throw updateError;
                    }
                    
                    // Update UI
                    profileData.phone = newPhone;
                    if ($('#phone').length) {
                        $('#phone').text(newPhone);
                    }
                    
                    // Reload user data
                    const { data: sessionData } = await supabase.auth.getSession();
                    if (sessionData?.session) {
                        currentUser = sessionData.session.user;
                    }
                    
                    closeUpdateModal();
                    alert('Phone number updated successfully!');
                } catch (error) {
                    console.error('Phone update error:', error);
                    alert(error.message || 'Failed to update phone number. Please try again.');
                }
            }
        }
        
        // Handle photo file selection
        function handlePhotoSelect(file) {
            // Validate file type
            var validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                alert('Please select a valid image file (JPG, JPEG, PNG, or GIF).');
                return;
            }
            
            // Validate file size (10MB max)
            var maxSize = 10 * 1024 * 1024; // 10MB in bytes
            if (file.size > maxSize) {
                alert('File size must not exceed 10MB.');
                return;
            }
            
            selectedPhotoFile = file;
            
            // Show preview
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#uploadPreview').attr('src', e.target.result);
                $('#uploadPreviewContainer').show();
            };
            reader.readAsDataURL(file);
        }
        
        // Force reset sidebar state on page load and browser back/forward
        function resetSidebar() {
            $('.navbar-side').removeClass('in');
            $('.navbar-side').css('left', '');  // Clear inline styles
            $('.navbar-side').attr('style', '');  // Remove all inline styles
            $('#sidebar-overlay').remove();
            $('body').css('overflow', '');  // Reset body overflow
        }
        
        // Close sidebar before leaving page
        $(window).on('beforeunload unload pagehide', function() {
            resetSidebar();
        });
        
        $(document).ready(function () {
            // Reset sidebar state on page load - with delay to ensure DOM is ready
            setTimeout(function() {
                resetSidebar();
            }, 100);
            
            // Mobile menu toggle
            $('.navbar-toggle').on('click', function() {
                $('.navbar-side').toggleClass('in');
                
                // Add/remove overlay
                if ($('.navbar-side').hasClass('in')) {
                    if (!$('#sidebar-overlay').length) {
                        $('body').append('<div id="sidebar-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 998;"></div>');
                    }
                } else {
                    $('#sidebar-overlay').remove();
                }
            });
            
            // Close sidebar when clicking overlay
            $(document).on('click', '#sidebar-overlay', function() {
                $('.navbar-side').removeClass('in');
                $(this).remove();
            });
            
            // Close sidebar when clicking a link on mobile/tablet
            $('.navbar-side a').on('click', function() {
                if ($(window).width() <= 991) {
                    $('.navbar-side').removeClass('in');
                    $('#sidebar-overlay').remove();
                }
            });
            
            // Photo upload area click
            $('#photoUploadArea').on('click', function() {
                $('#photoFileInput').click();
            });
            
            // Photo file input change
            $('#photoFileInput').on('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    handlePhotoSelect(e.target.files[0]);
                }
            });
            
            // Email code input - only allow numbers
            $('#emailCode').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
            
            // Toggle password visibility
            $(document).on('click', '.toggle-password', function() {
                var targetId = $(this).attr('data-target');
                var passwordInput = $('#' + targetId);
                
                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    $(this).removeClass('fa-eye-slash').addClass('fa-eye');
                } else {
                    passwordInput.attr('type', 'password');
                    $(this).removeClass('fa-eye').addClass('fa-eye-slash');
                }
            });
            
            // Drag and drop functionality
            $('#photoUploadArea').on('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('drag-over');
            });
            
            $('#photoUploadArea').on('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over');
            });
            
            $('#photoUploadArea').on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over');
                
                if (e.originalEvent.dataTransfer.files && e.originalEvent.dataTransfer.files[0]) {
                    handlePhotoSelect(e.originalEvent.dataTransfer.files[0]);
                }
            });
        });
        
        // Handle browser back/forward button - reset sidebar
        window.addEventListener('pageshow', function(event) {
            // Always reset on pageshow, whether from cache or not
            setTimeout(function() {
                resetSidebar();
            }, 50);
        });
        
        // Additional reset on window load
        window.addEventListener('load', function() {
            setTimeout(function() {
                resetSidebar();
            }, 100);
        });
        
        // Close modal when clicking outside of it
        $(document).on('click', '#updateModal', function(e) {
            if (e.target.id === 'updateModal') {
                closeUpdateModal();
            }
        });
        
        // Close modal with Escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#updateModal').hasClass('active')) {
                closeUpdateModal();
            }
            if (e.key === 'Escape' && $('#deleteModal').hasClass('active')) {
                closeDeleteModal();
            }
        });
        
        // Account Deletion Functions
        function openDeleteModal() {
            // Check if user has pending penalties
            if (hasPendingPenalties) {
                alert('⚠️ Cannot Request Account Deletion\n\nYou have unpaid penalties. Please pay your penalties before requesting account deletion.\n\nYou can view and pay your penalties in the Penalties section.');
                return;
            }
            
            $('#deleteModal').addClass('active');
            $('body').css('overflow', 'hidden');
        }
        
        function closeDeleteModal() {
            $('#deleteModal').removeClass('active');
            $('body').css('overflow', '');
        }
        
        async function confirmDeletion() {
            if (!supabase || !currentUser) {
                alert('Please wait for the page to load completely.');
                return;
            }
            
            // Check if user has pending penalties (double check)
            if (hasPendingPenalties) {
                alert('⚠️ Cannot Request Account Deletion\n\nYou have unpaid penalties. Please pay your penalties before requesting account deletion.');
                closeDeleteModal();
                return;
            }
            
            try {
                // Create deletion request via API
                const response = await fetch('create_deletion_request.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        user_id: currentUser.id
                    })
                });
                
                const data = await response.json();
                
                if (!response.ok || data.error) {
                    throw new Error(data.error || 'Failed to create deletion request');
                }
                
                // Close the deletion modal
                closeDeleteModal();
                
                // Show success message
                alert('Account deletion request submitted successfully!\n\nYour request has been received and will be processed by an administrator. You will now be logged out.');
                
                // Log out from Supabase
                try {
                    await supabase.auth.signOut();
                    console.log('User logged out after deletion request');
                } catch (logoutError) {
                    console.error('Logout error:', logoutError);
                }
                
                // Redirect to logout page after a short delay
                setTimeout(function() {
                    window.location.href = 'logout.php';
                }, 500);
                
            } catch (error) {
                console.error('Error creating deletion request:', error);
                alert('Failed to submit deletion request: ' + (error.message || error));
                closeDeleteModal();
            }
        }
        
        // Close deletion modal when clicking outside of it
        $(document).on('click', '#deleteModal', function(e) {
            if (e.target.id === 'deleteModal') {
                closeDeleteModal();
            }
        });
    </script>
    <!-- Custom Js -->
    <script src="assets/js/custom-scripts.js"></script>
</body>
</html>