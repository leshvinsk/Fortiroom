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
                        <a href="dashboard.php"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
                    </li>
                    <li>
                        <a href="penalties.php"><i class="fa fa-exclamation-triangle fa-fw"></i> Penalties</a>
                    </li>
                    <li>
                        <a href="pods.php"><i class="fa fa-building fa-fw"></i> Pods Management </a>
                    </li>
                    <li>
                        <a href="users.php"><i class="fa fa-users fa-fw"></i> User Management </a>
                    </li>
                    <li>
                        <a href="analytics.php"><i class="fa fa-bar-chart-o fa-fw"></i> Analytics</a>
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
                            <!-- Username Field -->
                            <div class="profile-field">
                                <div class="field-icon">
                                    <i class="fa fa-user"></i>
                                </div>
                                <div class="field-content">
                                    <label>Username</label>
                                    <div class="field-value" id="username">admin123</div>
                                </div>
                            </div>
                            
                            <!-- Email Field -->
                            <div class="profile-field">
                                <div class="field-icon">
                                    <i class="fa fa-envelope"></i>
                                </div>
                                <div class="field-content">
                                    <label>Email Address</label>
                                    <div class="field-value" id="email">admin@fortiroom.com</div>
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
                            
                            <!-- Phone Number Field -->
                            <div class="profile-field">
                                <div class="field-icon">
                                    <i class="fa fa-phone"></i>
                                </div>
                                <div class="field-content">
                                    <label>Phone Number</label>
                                    <div class="field-value" id="phone">+60 12-345 6789</div>
                                </div>
                                <div class="field-action">
                                    <button class="btn btn-primary btn-sm update-btn" onclick="openUpdateModal('phone')">
                                        <i class="fa fa-edit"></i> Update
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!--End Profile Fields Container -->
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
                    <!-- Email Update Form -->
                    <div id="emailForm" class="update-form" style="display: none;">
                        <div class="form-group-update">
                            <label for="newEmail">New Email Address <span class="required">*</span></label>
                            <input type="email" id="newEmail" placeholder="e.g., admin@fortiroom.com" required>
                        </div>
                        <div class="form-group-update">
                            <label for="confirmEmail">Confirm Email Address <span class="required">*</span></label>
                            <input type="email" id="confirmEmail" placeholder="Re-enter email address" required>
                        </div>
                    </div>
                    
                    <!-- Password Update Form -->
                    <div id="passwordForm" class="update-form" style="display: none;">
                        <div class="form-group-update">
                            <label for="currentPassword">Current Password <span class="required">*</span></label>
                            <input type="password" id="currentPassword" placeholder="Enter your current password" required>
                        </div>
                        <div class="form-group-update">
                            <label for="newPassword">New Password <span class="required">*</span></label>
                            <input type="password" id="newPassword" placeholder="Enter new password (min. 8 characters)" required>
                        </div>
                        <div class="form-group-update">
                            <label for="confirmPassword">Confirm New Password <span class="required">*</span></label>
                            <input type="password" id="confirmPassword" placeholder="Re-enter new password" required>
                        </div>
                    </div>
                    
                    <!-- Phone Number Update Form -->
                    <div id="phoneForm" class="update-form" style="display: none;">
                        <div class="form-group-update">
                            <label for="newPhone">New Phone Number <span class="required">*</span></label>
                            <input type="tel" id="newPhone" placeholder="+60 12-345 6789" required>
                        </div>
                        <div class="form-group-update">
                            <label for="confirmPhone">Confirm Phone Number <span class="required">*</span></label>
                            <input type="tel" id="confirmPhone" placeholder="Re-enter phone number" required>
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
        
        // Profile data
        var profileData = {
            username: 'admin123',
            email: 'admin@fortiroom.com',
            phone: '+60 12-345 6789'
        };
        
        function openUpdateModal(field) {
            currentUpdateField = field;
            
            // Hide all forms
            $('.update-form').hide();
            
            // Show the appropriate form and update modal title
            if (field === 'email') {
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
        }
        
        function saveUpdate() {
            if (!currentUpdateField) return;
            
            if (currentUpdateField === 'email') {
                var newEmail = $('#newEmail').val();
                var confirmEmail = $('#confirmEmail').val();
                
                if (!newEmail || !confirmEmail) {
                    alert('Please fill in all fields.');
                    return;
                }
                
                if (newEmail !== confirmEmail) {
                    alert('Email addresses do not match. Please try again.');
                    return;
                }
                
                // Validate email format
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(newEmail)) {
                    alert('Please enter a valid email address.');
                    return;
                }
                
                // Update email in profile
                profileData.email = newEmail;
                $('#email').text(newEmail);
                
                closeUpdateModal();
                alert('Email address updated successfully!');
                
            } else if (currentUpdateField === 'password') {
                var currentPassword = $('#currentPassword').val();
                var newPassword = $('#newPassword').val();
                var confirmPassword = $('#confirmPassword').val();
                
                if (!currentPassword || !newPassword || !confirmPassword) {
                    alert('Please fill in all fields.');
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
                
                closeUpdateModal();
                alert('Password updated successfully!');
                
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
                
                // Update phone in profile
                profileData.phone = newPhone;
                $('#phone').text(newPhone);
                
                closeUpdateModal();
                alert('Phone number updated successfully!');
            }
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
        });
    </script>
    <!-- Custom Js -->
    <script src="assets/js/custom-scripts.js"></script>
</body>
</html>