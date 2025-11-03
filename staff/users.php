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
        
        /* Status Badges */
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-termination_sent {
            background-color: #fcf8e3;
            color: #8a6d3b;
        }
        .status-termination_progress {
            background-color: #f2dede;
            color: #a94442;
        }
        .status-termination_declined {
            background-color: #d9edf7;
            color: #31708f;
        }
        
        /* Review Modal */
        .review-modal-overlay {
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
        
        .review-modal-overlay.active {
            display: flex;
        }
        
        .review-modal {
            background: #fff;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            animation: slideDown 0.3s ease;
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
        
        .review-modal-header {
            background: #fff;
            color: #333;
            padding: 24px 30px;
            border-radius: 12px 12px 0 0;
            position: relative;
            border-bottom: 1px solid #e9ecef;
        }
        
        .review-modal-header h3 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
            color: #1a1a1a;
        }
        
        .review-modal-body {
            padding: 30px;
        }
        
        .review-modal-body p {
            margin: 0 0 20px 0;
            font-size: 15px;
            line-height: 1.6;
            color: #555;
        }
        
        .review-modal-body .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .review-modal-body .user-info strong {
            color: #333;
        }
        
        .review-modal-body .email-notice {
            font-size: 13px;
            color: #666;
            font-style: italic;
            margin-top: 15px;
        }
        
        .review-modal-footer {
            padding: 20px 30px;
            border-top: 1px solid #e9ecef;
            text-align: right;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background: #f8f9fa;
            border-radius: 0 0 12px 12px;
        }
        
        .btn-accept {
            background: #28a745;
            color: #fff;
            border: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-accept:hover {
            background: #218838;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
        }
        
        .btn-decline {
            background: #dc3545;
            color: #fff;
            border: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-decline:hover {
            background: #c82333;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
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
            .panel-heading h4 {
                text-align: center;
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
                    <a href="profile.php"><i class="fa fa-user-circle"></i> Profile</a>
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
                        <a class="active-menu" href="users.php"><i class="fa fa-users fa-fw"></i> User Management </a>
                    </li>
                    <li>
                        <a href="analytics.php"><i class="fa fa-bar-chart-o fa-fw"></i> Analytics</a>
                    </li>
                    <li class="mobile-only" style="display: none;">
                        <a href="profile.php"><i class="fa fa-user-circle fa-fw"></i> Profile</a>
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
                            <h1 style="margin: 0; font-size: 26px; font-weight: 400; color: #5a5a5a;">USER MANAGEMENT</h1>
                        </div>
                    </div>
                </div>
                <!-- /. ROW  -->
                
                <div class="row">
                    <div class="col-md-12">
                        <!-- Users Table -->
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4>User Records</h4>
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="usersTableBody">
                                            <!-- User Records -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!--End Users Table -->
                    </div>
                </div>
                <!-- /. ROW  -->
            </div>
        </div>
        <!-- /. PAGE WRAPPER  -->
    </div>
    <!-- /. WRAPPER  -->
    
    <!-- Review Request Modal -->
    <div id="reviewModal" class="review-modal-overlay">
        <div class="review-modal">
            <div class="review-modal-header">
                <h3>Review Account Termination Request</h3>
            </div>
            <div class="review-modal-body">
                <p>Do you accept the request for Account Termination?</p>
                <div class="user-info">
                    <strong>Username:</strong> <span id="modalUsername"></span><br>
                    <strong>Email:</strong> <span id="modalEmail"></span>
                </div>
                <p class="email-notice">An email notification will be sent to the user regarding your decision.</p>
            </div>
            <div class="review-modal-footer">
                <button class="btn-decline" onclick="declineTermination()">No - Decline Request</button>
                <button class="btn-accept" onclick="acceptTermination()">Yes - Accept Request</button>
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
        var dataTable;
        
        // Sample user data
        var usersData = [
            { username: 'michaelbrown', email: 'michael.brown@helplive.edu.my', fullName: 'Michael Brown', status: 'termination_sent' },
            { username: 'johnsmith', email: 'john.smith@helplive.edu.my', fullName: 'John Smith', status: 'termination_progress' },
            { username: 'sarahdavis', email: 'sarah.davis@helplive.edu.my', fullName: 'Sarah Davis', status: 'termination_progress' }
        ];
        
        var currentReviewUser = null;
        
        function populateUsersTable() {
            var tbody = $('#usersTableBody');
            tbody.empty();
            
            usersData.forEach(function(user) {
                var statusClass = 'status-' + user.status;
                var statusText = '';
                if (user.status === 'termination_sent') {
                    statusText = 'Account Termination Request Sent';
                } else if (user.status === 'termination_progress') {
                    statusText = 'Account Termination in Progress';
                } else if (user.status === 'termination_declined') {
                    statusText = 'Account Termination Request Declined';
                }
                
                var row = '<tr data-username="' + user.username + '">' +
                    '<td>' + user.username + '</td>' +
                    '<td>' + user.email + '</td>' +
                    '<td class="status-cell"><span class="status-badge ' + statusClass + '">' + statusText + '</span></td>' +
                    '<td>' +
                    '<button class="btn btn-sm btn-primary" onclick="reviewRequest(\'' + user.username + '\')"><i class="fa fa-search"></i> Review Request</button>' +
                    '</td>' +
                    '</tr>';
                
                tbody.append(row);
            });
        }
        
        function reviewRequest(username) {
            // Find user data
            var user = usersData.find(function(u) { return u.username === username; });
            if (!user) return;
            
            // Store current user being reviewed
            currentReviewUser = username;
            
            // Populate modal with user info
            $('#modalUsername').text(user.username);
            $('#modalEmail').text(user.email);
            
            // Show modal
            $('#reviewModal').addClass('active');
            $('body').css('overflow', 'hidden');
        }
        
        function closeReviewModal() {
            $('#reviewModal').removeClass('active');
            $('body').css('overflow', '');
            currentReviewUser = null;
        }
        
        function acceptTermination() {
            if (!currentReviewUser) return;
            
            // Find user and update status
            var user = usersData.find(function(u) { return u.username === currentReviewUser; });
            if (user) {
                user.status = 'termination_progress';
                
                // Update the table row
                var row = $('tr[data-username="' + currentReviewUser + '"]');
                row.find('.status-cell').html('<span class="status-badge status-termination_progress">Account Termination in Progress</span>');
            }
            
            // Close modal
            closeReviewModal();
            
            // Show confirmation
            alert('Request accepted! The user status has been updated to "Account Termination in Progress".\n\nAn email notification has been sent to the user.');
        }
        
        function declineTermination() {
            if (!currentReviewUser) return;
            
            // Find user and update status
            var user = usersData.find(function(u) { return u.username === currentReviewUser; });
            if (user) {
                user.status = 'termination_declined';
                
                // Update the table row
                var row = $('tr[data-username="' + currentReviewUser + '"]');
                row.find('.status-cell').html('<span class="status-badge status-termination_declined">Account Termination Request Declined</span>');
            }
            
            // Close modal
            closeReviewModal();
            
            // Show confirmation
            alert('Request declined. The user status has been updated to "Account Termination Request Declined".\n\nAn email notification has been sent to the user.');
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
            
            // Populate table with users
            populateUsersTable();
            
            // Initialize DataTable
            dataTable = $('#dataTables-example').dataTable({
                "order": [[ 2, "desc" ]],  // Sort by status (column index 2) - Request Sent first
                "pageLength": 10
            });
            
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
        $(document).on('click', '#reviewModal', function(e) {
            if (e.target.id === 'reviewModal') {
                closeReviewModal();
            }
        });
        
        // Close modal with Escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#reviewModal').hasClass('active')) {
                closeReviewModal();
            }
        });
    </script>
    <!-- Custom Js -->
    <script src="assets/js/custom-scripts.js"></script>
</body>
</html>