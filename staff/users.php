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
        
        /* Status Badges */
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending {
            background-color: #fcf8e3;
            color: #8a6d3b;
        }
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        .status-declined {
            background-color: #d9edf7;
            color: #31708f;
        }
        .status-in_progress {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-cancelled {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .status-completed {
            background-color: #f8d7da;
            color: #721c24;
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
                                                <th>Requested Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="usersTableBody">
                                            <!-- Deletion Request Records -->
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
                <h3>Review Account Deletion Request</h3>
            </div>
            <div class="review-modal-body">
                <p>Do you accept the request for Account Deletion?</p>
                <div class="user-info">
                    <strong>Username:</strong> <span id="modalUsername"></span><br>
                    <strong>Email:</strong> <span id="modalEmail"></span><br>
                    <strong>Requested Date:</strong> <span id="modalRequestedDate"></span><br>
                    <strong>Request ID:</strong> <span id="modalRequestId"></span>
                </div>
                <p class="email-notice" style="margin-top: 15px;">
                    <strong>Note:</strong> If approved, the account deletion process will start immediately and take 30 days to complete. 
                    If the user logs in during this period, the deletion will be cancelled automatically. 
                    Bookings and penalties records will be retained for analytics purposes.
                </p>
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
        var supabase = null;
        var currentUser = null;
        var deletionRequestsData = []; // Always keep as array
        var usersMap = {}; // Map of user_id to user info (username, email)
        var currentReviewRequest = null;
        
        // Ensure deletionRequestsData is always an array (defensive programming)
        if (!Array.isArray(deletionRequestsData)) {
            deletionRequestsData = [];
        }
        
        // Initialize Supabase and load deletion requests
        document.addEventListener('DOMContentLoaded', async function() {
            // Initialize Supabase
            const { createClient } = window.supabase || {};
            if (!createClient) {
                console.error('Supabase library failed to load.');
                alert('Failed to load database connection. Please refresh the page.');
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
            
            // Load deletion requests and user info
            await loadDeletionRequests();
        });
        
        // Load deletion requests from database via PHP endpoint (uses service key, bypasses RLS)
        async function loadDeletionRequests() {
            try {
                // Load deletion requests via PHP endpoint (uses service key, bypasses RLS)
                const response = await fetch('get_deletion_requests.php', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Failed to fetch deletion requests. Status:', response.status, 'Response:', errorText);
                    throw new Error('Failed to fetch deletion requests: ' + response.statusText);
                }
                
                const data = await response.json();
                console.log('Deletion requests API response:', data);
                
                // Ensure requests is an array - defensive validation
                if (!data) {
                    console.error('No data received from API');
                    deletionRequestsData = [];
                } else if (data.error) {
                    console.error('API returned error:', data.error);
                    // Check if it's a table doesn't exist error
                    if (data.message && (data.message.includes('does not exist') || data.message.includes('Table does not exist'))) {
                        alert('⚠️ Database Setup Required\n\nThe account_deletion_requests table has not been created yet.\n\nPlease run create_account_deletion_requests_table.sql in your Supabase SQL Editor.');
                        deletionRequestsData = [];
                    } else {
                        // For other errors, show error but don't throw (allow page to load with empty data)
                        console.error('Error loading deletion requests:', data.error);
                        deletionRequestsData = [];
                    }
                } else if (!data.hasOwnProperty('requests')) {
                    console.error('Unexpected response structure - missing "requests" property:', data);
                    deletionRequestsData = [];
                } else if (!Array.isArray(data.requests)) {
                    console.error('Requests is not an array. Type:', typeof data.requests, 'Value:', data.requests);
                    // Force to array - handle null/undefined/object cases
                    if (data.requests === null || data.requests === undefined) {
                        deletionRequestsData = [];
                    } else if (Array.isArray(data.requests)) {
                        deletionRequestsData = data.requests;
                    } else {
                        // Not an array, force to empty array
                        deletionRequestsData = [];
                    }
                } else {
                    // Valid array
                    deletionRequestsData = data.requests;
                }
                
                // Final safety check - ensure it's definitely an array
                if (!Array.isArray(deletionRequestsData)) {
                    console.error('deletionRequestsData is still not an array after validation. Forcing to empty array.');
                    deletionRequestsData = [];
                }
                
                console.log('Loaded deletion requests:', deletionRequestsData.length, 'requests');
                console.log('Deletion requests data type:', typeof deletionRequestsData, 'Is array:', Array.isArray(deletionRequestsData));
                
                // Get unique user IDs (only if we have valid array data)
                var userIds = [];
                if (Array.isArray(deletionRequestsData) && deletionRequestsData.length > 0) {
                    try {
                        userIds = [...new Set(deletionRequestsData.map(function(r) {
                            return r && r.user_id ? r.user_id : null;
                        }).filter(function(id) {
                            return id !== null && id !== undefined;
                        }))];
                    } catch (mapError) {
                        console.error('Error mapping user IDs:', mapError);
                        console.error('deletionRequestsData:', deletionRequestsData);
                        userIds = [];
                    }
                }
                
                console.log('User IDs to fetch:', userIds.length, 'user IDs');
                
                // Load user info for all user IDs
                if (userIds.length > 0) {
                    await loadUsersInfo(userIds);
                }
                
                // Populate table
                populateUsersTable();
                
                // Initialize or refresh DataTable
                initializeDataTable();
                
            } catch (error) {
                console.error('Error in loadDeletionRequests:', error);
                console.error('Error stack:', error.stack);
                alert('Failed to load deletion requests: ' + error.message);
                deletionRequestsData = [];
                populateUsersTable();
                initializeDataTable();
            }
        }
        
        // Load user info from Supabase Auth via PHP endpoint
        async function loadUsersInfo(userIds) {
            try {
                const response = await fetch('get_users_info.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        user_ids: userIds
                    })
                });
                
                if (!response.ok) {
                    throw new Error('Failed to fetch user info: ' + response.statusText);
                }
                
                const data = await response.json();
                if (data.error) {
                    throw new Error(data.error);
                }
                
                usersMap = data.users || {};
                console.log('Loaded user info for', Object.keys(usersMap).length, 'users');
            } catch (error) {
                console.error('Error loading user info:', error);
                // Continue even if user info fails to load
            }
        }
        
        // Populate users table with deletion requests
        function populateUsersTable() {
            var tbody = $('#usersTableBody');
            tbody.empty();
            
            // Safety check - ensure deletionRequestsData is an array
            if (!Array.isArray(deletionRequestsData)) {
                console.error('populateUsersTable: deletionRequestsData is not an array:', typeof deletionRequestsData);
                deletionRequestsData = [];
            }
            
            if (deletionRequestsData.length === 0) {
                tbody.append('<tr><td colspan="5" style="text-align: center; padding: 20px;">No account deletion requests found.</td></tr>');
                return;
            }
            
            try {
                deletionRequestsData.forEach(function(request) {
                var userInfo = usersMap[request.user_id] || {};
                var username = userInfo.username || 'User ' + (request.user_id ? request.user_id.substring(0, 8) : 'Unknown');
                var email = userInfo.email || 'N/A';
                
                // Format requested date
                var requestedDate = 'N/A';
                if (request.requested_at) {
                    var date = new Date(request.requested_at);
                    requestedDate = date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }
                
                // Get status text and class
                var statusClass = 'status-' + request.status;
                var statusText = '';
                switch (request.status) {
                    case 'pending':
                        statusText = 'Pending Review';
                        break;
                    case 'approved':
                        statusText = 'Approved';
                        break;
                    case 'declined':
                        statusText = 'Declined';
                        break;
                    case 'in_progress':
                        statusText = 'Deletion in Progress';
                        break;
                    case 'cancelled':
                        statusText = 'Cancelled';
                        break;
                    case 'completed':
                        statusText = 'Completed';
                        break;
                    default:
                        statusText = request.status;
                }
                
                // Only show review button for pending requests
                var actionButton = '';
                if (request.status === 'pending') {
                    actionButton = '<button class="btn btn-sm btn-primary" onclick="reviewRequest(\'' + request.id + '\')"><i class="fa fa-search"></i> Review Request</button>';
                } else {
                    actionButton = '<span style="color: #999; font-style: italic;">No action available</span>';
                }
                
                var row = '<tr data-request-id="' + request.id + '">' +
                    '<td>' + escapeHtml(username) + '</td>' +
                    '<td>' + escapeHtml(email) + '</td>' +
                    '<td>' + requestedDate + '</td>' +
                    '<td class="status-cell"><span class="status-badge ' + statusClass + '">' + statusText + '</span></td>' +
                    '<td>' + actionButton + '</td>' +
                    '</tr>';
                
                tbody.append(row);
                });
            } catch (forEachError) {
                console.error('Error in populateUsersTable forEach:', forEachError);
                console.error('deletionRequestsData:', deletionRequestsData);
                tbody.append('<tr><td colspan="5" style="text-align: center; padding: 20px; color: red;">Error loading deletion requests. Please check console.</td></tr>');
            }
        }
        
        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            if (!text) return '';
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
        
        // Review deletion request
        function reviewRequest(requestId) {
            // Find request data
            var request = deletionRequestsData.find(function(r) { return r.id === requestId; });
            if (!request) {
                alert('Request not found.');
                return;
            }
            
            // Get user info
            var userInfo = usersMap[request.user_id] || {};
            var username = userInfo.username || 'User ' + (request.user_id ? request.user_id.substring(0, 8) : 'Unknown');
            var email = userInfo.email || 'N/A';
            
            // Format requested date
            var requestedDate = 'N/A';
            if (request.requested_at) {
                var date = new Date(request.requested_at);
                requestedDate = date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
            
            // Store current request being reviewed
            currentReviewRequest = requestId;
            
            // Populate modal with request info
            $('#modalUsername').text(username);
            $('#modalEmail').text(email);
            $('#modalRequestedDate').text(requestedDate);
            $('#modalRequestId').text(requestId);
            
            // Show modal
            $('#reviewModal').addClass('active');
            $('body').css('overflow', 'hidden');
        }
        
        function closeReviewModal() {
            $('#reviewModal').removeClass('active');
            $('body').css('overflow', '');
            currentReviewRequest = null;
        }
        
        // Accept deletion request
        async function acceptTermination() {
            if (!currentReviewRequest || !currentUser) {
                alert('No request selected or user not logged in.');
                return;
            }
            
            try {
                // Update request status to approved (which will set it to in_progress)
                const response = await fetch('update_deletion_request.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        request_id: currentReviewRequest,
                        status: 'approved',
                        reviewed_by: currentUser.id,
                        admin_notes: 'Request approved by administrator'
                    })
                });
                
                const data = await response.json();
                
                if (!response.ok || data.error) {
                    throw new Error(data.error || 'Failed to update deletion request');
                }
                
                // Close modal
                closeReviewModal();
                
                // Show confirmation
                alert('Request accepted! The account deletion process has started and will take 30 days to complete.\n\nIf the user logs in during this period, the deletion will be cancelled automatically.\n\nAn email notification has been sent to the user.');
                
                // Reload the page after alert is dismissed
                window.location.reload();
                
            } catch (error) {
                console.error('Error accepting termination:', error);
                alert('Failed to accept request: ' + (error.message || error));
            }
        }
        
        // Decline deletion request
        async function declineTermination() {
            if (!currentReviewRequest || !currentUser) {
                alert('No request selected or user not logged in.');
                return;
            }
            
            try {
                // Update request status to declined
                const response = await fetch('update_deletion_request.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        request_id: currentReviewRequest,
                        status: 'declined',
                        reviewed_by: currentUser.id,
                        admin_notes: 'Request declined by administrator'
                    })
                });
                
                const data = await response.json();
                
                if (!response.ok || data.error) {
                    throw new Error(data.error || 'Failed to update deletion request');
                }
                
                // Close modal
                closeReviewModal();
                
                // Show confirmation
                alert('Request declined. The user status has been updated to "Declined".\n\nAn email notification has been sent to the user.');
                
                // Reload the page after alert is dismissed
                window.location.reload();
                
            } catch (error) {
                console.error('Error declining termination:', error);
                alert('Failed to decline request: ' + (error.message || error));
            }
        }
        
        // Initialize DataTable
        function initializeDataTable() {
            // Destroy existing DataTable if it exists
            if (dataTable) {
                try {
                    dataTable.fnDestroy();
                } catch (e) {
                    console.warn('Error destroying DataTable:', e);
                }
                dataTable = null;
            }
            
            // Wait a bit for DOM to update
            setTimeout(function() {
                if ($('#dataTables-example').length) {
                    var rowCount = $('#dataTables-example tbody tr').length;
                    // Only initialize if there are rows (not just the "No requests" message)
                    if (rowCount > 0 && !$('#dataTables-example tbody tr td[colspan]').length) {
                        try {
                            dataTable = $('#dataTables-example').dataTable({
                                "order": [[ 2, "desc" ]], // Sort by requested date (newest first)
                                "pageLength": 10,
                                "columnDefs": [
                                    { "orderable": false, "targets": 4 } // Disable sorting on Actions column
                                ]
                            });
                        } catch (e) {
                            console.error('Error initializing DataTable:', e);
                        }
                    }
                }
            }, 100);
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