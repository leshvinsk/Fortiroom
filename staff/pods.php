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
        
        /* Pod Cards */
        .dashboard-cards {
            margin-bottom: 30px;
        }
        .pod-card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: all 0.3s ease;
            border-top: 5px solid;
        }
        .pod-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        .pod-card.available {
            border-top-color: #5cb85c;
        }
        .pod-card.occupied {
            border-top-color: #f0ad4e;
        }
        .pod-card.maintenance {
            border-top-color: #d9534f;
        }
        .pod-card.cleaning {
            border-top-color: #5bc0de;
        }
        .pod-card.suspended {
            border-top-color: #777;
            opacity: 0.7;
        }
        .pod-card.idle {
            border-top-color: #9b59b6;
        }
        
        .pod-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        .pod-number {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .pod-header-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
        }
        .pod-capacity {
            font-size: 14px;
            font-weight: 600;
            color: #666;
            padding: 4px 10px;
            background-color: #f8f9fa;
            border-radius: 12px;
            border: 1px solid #dee2e6;
        }
        .pod-capacity i {
            margin-right: 4px;
            color: #007bff;
        }
        .pod-status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .pod-status.available {
            background-color: #d4edda;
            color: #155724;
        }
        .pod-status.occupied {
            background-color: #fff3cd;
            color: #856404;
        }
        .pod-status.maintenance {
            background-color: #f8d7da;
            color: #721c24;
        }
        .pod-status.cleaning {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .pod-status.suspended {
            background-color: #e0e0e0;
            color: #555;
        }
        .pod-status.idle {
            background-color: #e8daef;
            color: #6c3483;
        }
        
        .pod-info {
            margin-bottom: 15px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 6px;
        }
        .info-label {
            font-size: 14px;
            color: #666;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .info-label i {
            font-size: 18px;
            width: 20px;
            text-align: center;
        }
        .info-value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            text-align: center;
            width: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .aqi-good {
            color: #5cb85c;
        }
        .aqi-moderate {
            color: #f0ad4e;
        }
        .aqi-poor {
            color: #d9534f;
        }
        
        .fan-control {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            width: 140px;
            flex-wrap: wrap;
        }
        .fan-control-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }
        .fan-mode-toggle {
            width: 35px;
            height: 20px;
            border-radius: 10px;
            border: 1.5px solid #007bff;
            background-color: #ffc107;
            color: #333;
            font-size: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            text-transform: uppercase;
        }
        .fan-mode-toggle:hover {
            opacity: 0.8;
            transform: scale(1.05);
        }
        .fan-mode-toggle.manual {
            background-color: #28a745;
            color: #fff;
        }
        .fan-speed-controls {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .fan-speed-display {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            min-width: 25px;
            text-align: center;
        }
        .fan-btn {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 1.5px solid #007bff;
            background-color: #fff;
            color: #007bff;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .fan-btn:hover:not(:disabled) {
            background-color: #007bff;
            color: #fff;
            transform: scale(1.1);
        }
        .fan-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            border-color: #ccc;
            color: #ccc;
        }
        .fan-btn:active:not(:disabled) {
            transform: scale(0.95);
        }
        .fan-control-disabled {
            opacity: 0.5;
            pointer-events: none;
        }
        
        /* Pod Action Buttons */
        .pod-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .pod-action-btn {
            flex: 1;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-transform: uppercase;
        }
        .pod-action-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .pod-action-btn:disabled {
            cursor: not-allowed;
        }
        .btn-suspend {
            background-color: #f0ad4e;
            color: #fff;
        }
        .btn-suspend:hover:not(:disabled) {
            background-color: #ec971f;
        }
        .btn-unsuspend {
            background-color: #28a745;
            color: #fff;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.4);
        }
        .btn-unsuspend:hover:not(:disabled) {
            background-color: #218838;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.5);
        }
        .btn-delete {
            background-color: #dc3545;
            color: #fff;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.4);
        }
        .btn-delete:hover:not(:disabled) {
            background-color: #c82333;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.5);
        }
        .btn-delete:disabled {
            background-color: #999;
            color: #ddd;
            box-shadow: none;
        }
        .grayed-out {
            color: #999 !important;
        }
        
        /* Create Pod Button */
        .create-pod-container {
            margin-bottom: 20px;
            text-align: right;
        }
        .btn-create-pod {
            background-color: #007bff;
            color: #fff;
            padding: 10px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 2px 6px rgba(0, 123, 255, 0.3);
        }
        .btn-create-pod:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.4);
        }
        
        /* Modal Overlay */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .modal-overlay.active {
            display: flex;
        }
        
        /* Modal Content */
        .modal-content {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            position: relative;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        .modal-title {
            font-size: 22px;
            font-weight: bold;
            color: #333;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 28px;
            color: #999;
            cursor: pointer;
            line-height: 1;
            padding: 0;
            width: 30px;
            height: 30px;
        }
        .modal-close:hover {
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #555;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #007bff;
        }
        .modal-actions {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }
        .modal-btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-modal-cancel {
            background-color: #6c757d;
            color: #fff;
        }
        .btn-modal-cancel:hover {
            background-color: #5a6268;
        }
        .btn-modal-submit {
            background-color: #007bff;
            color: #fff;
        }
        .btn-modal-submit:hover {
            background-color: #0056b3;
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
                        <a class="active-menu" href="pods.php"><i class="fa fa-building fa-fw"></i> Pods Management </a>
                    </li>
                    <li>
                        <a href="users.php"><i class="fa fa-users fa-fw"></i> User Management </a>
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
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #ddd; padding-bottom: 15px; margin-bottom: 25px;">
                            <h1 style="margin: 0; font-size: 26px; font-weight: 400; color: #5a5a5a;">PODS MANAGEMENT</h1>
                            <button class="btn-create-pod" onclick="openCreatePodModal()">
                                <i class="fa fa-plus-circle"></i> Create New Pod
                            </button>
                        </div>
                    </div>
                </div>
                <!-- /. ROW  -->
                
                <!-- Pod Cards -->
                <div class="row dashboard-cards" id="podCardsContainer">
                    <!-- Pod cards will be dynamically generated here -->
                        </div>
                <!-- /. Pod Cards -->
                    </div>
                        </div>
        <!-- /. PAGE WRAPPER  -->
                    </div>
    <!-- /. WRAPPER  -->
    
    <!-- Create Pod Modal -->
    <div class="modal-overlay" id="createPodModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Create New Pod</h2>
                <button class="modal-close" onclick="closeCreatePodModal()">&times;</button>
                            </div>
            <form id="createPodForm" onsubmit="createNewPod(event)">
                <div class="form-group">
                    <label for="podName">Pod Name <span style="color: red;">*</span></label>
                    <input type="text" id="podName" name="podName" placeholder="e.g., Pod 5" required>
                                    </div>
                <div class="form-group">
                    <label for="podCapacity">Pod Capacity <span style="color: red;">*</span></label>
                    <input type="number" id="podCapacity" name="podCapacity" placeholder="e.g., 2" min="1" max="10" required>
                                    </div>
                <div class="form-group">
                    <label for="podHardwareId">Pod Hardware ID <span style="color: red;">*</span></label>
                    <input type="text" id="podHardwareId" name="podHardwareId" placeholder="e.g., HW-POD-005" required>
                                    </div>
                <div class="modal-actions">
                    <button type="button" class="modal-btn btn-modal-cancel" onclick="closeCreatePodModal()">Cancel</button>
                    <button type="submit" class="modal-btn btn-modal-submit">Create Pod</button>
                                    </div>
            </form>
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
        // Global variables
        var supabase = null;
        var currentUser = null;
        var podsData = [];
        var bookingsData = [];
        var simulationInterval = null;
        
        // Initialize Supabase and load pods data
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
            
            // Load bookings and pods from database
            await loadBookings();
            await loadPods();
            
            // Start simulation interval (updates pod status based on bookings)
            if (simulationInterval) {
                clearInterval(simulationInterval);
            }
            simulationInterval = setInterval(async function() {
                await loadBookings(); // Reload bookings to check for status changes
                await updatePodStatusesFromBookings(); // Update pod statuses based on bookings
                simulateUpdates(); // Update simulated temperature/AQI values
            }, 10000); // Update every 10 seconds
        });
        
        // Load bookings from database
        async function loadBookings() {
            try {
                const { data: bookings, error: bookingsError } = await supabase
                    .from('bookings')
                    .select('id, pod_id, booking_date, check_in_time, check_out_time, number_of_people')
                    .order('booking_date', { ascending: false })
                    .order('check_in_time', { ascending: true });
                
                if (bookingsError) {
                    console.error('Error loading bookings:', bookingsError);
                    // If bookings table doesn't exist, just continue with empty array
                    bookingsData = [];
                    return;
                }
                
                bookingsData = bookings || [];
            } catch (error) {
                console.error('Error in loadBookings:', error);
                bookingsData = [];
            }
        }
        
        // Get booking status based on current time (same logic as dashboard.php)
        function getBookingStatus(bookingDate, checkIn, checkOut) {
            var now = new Date();
            var checkInDateTime = new Date(bookingDate + 'T' + checkIn + ':00');
            var checkOutDateTime = new Date(bookingDate + 'T' + checkOut + ':00');
            
            // Calculate time windows
            var checkInWindow = 15 * 60 * 1000; // 15 minutes in milliseconds
            var checkOutWindow = 15 * 60 * 1000; // 15 minutes in milliseconds
            
            var checkInStart = new Date(checkInDateTime.getTime() - checkInWindow);
            var checkInEnd = checkInDateTime;
            var checkOutStart = new Date(checkOutDateTime.getTime() - checkOutWindow);
            
            if (now < checkInStart) {
                // Before check-in window
                return 'upcoming';
            } else if (now >= checkInStart && now < checkInEnd) {
                // Within check-in window (15 min before to check-in time)
                return 'in-progress-checkin';
            } else if (now >= checkInEnd && now < checkOutStart) {
                // Between check-in and check-out window (occupied/in use)
                return 'in-progress-occupied';
            } else if (now >= checkOutStart && now < checkOutDateTime) {
                // Within check-out window (15 min before to check-out time)
                return 'in-progress-checkout';
            } else {
                // After check-out time
                return 'completed';
            }
        }
        
        // Check if a pod has an active booking (currently in progress)
        function hasActiveBooking(podId) {
            // Check if any booking for this pod is currently active
            for (var i = 0; i < bookingsData.length; i++) {
                var booking = bookingsData[i];
                if (String(booking.pod_id) === String(podId)) {
                    // Parse time strings
                    var checkInTime = '';
                    var checkOutTime = '';
                    
                    if (booking.check_in_time) {
                        if (typeof booking.check_in_time === 'string') {
                            checkInTime = booking.check_in_time.substring(0, 5);
                        } else {
                            checkInTime = booking.check_in_time;
                        }
                    }
                    
                    if (booking.check_out_time) {
                        if (typeof booking.check_out_time === 'string') {
                            checkOutTime = booking.check_out_time.substring(0, 5);
                        } else {
                            checkOutTime = booking.check_out_time;
                        }
                    }
                    
                    if (checkInTime && checkOutTime) {
                        var status = getBookingStatus(booking.booking_date, checkInTime, checkOutTime);
                        
                        // Pod is occupied if booking is in progress (not upcoming or completed)
                        if (status === 'in-progress-checkin' || 
                            status === 'in-progress-occupied' || 
                            status === 'in-progress-checkout') {
                            return true;
                        }
                    }
                }
            }
            return false;
        }
        
        // Update pod statuses based on bookings
        async function updatePodStatusesFromBookings() {
            if (!supabase || podsData.length === 0) {
                return;
            }
            
            try {
                // Update each pod's status based on bookings
                for (var i = 0; i < podsData.length; i++) {
                    var pod = podsData[i];
                    var podId = pod.id;
                    
                    // Skip suspended pods (they should remain suspended)
                    if (pod.status === 'suspended') {
                        continue;
                    }
                    
                    // Check if pod has an active booking
                    var hasActive = hasActiveBooking(podId);
                    var newStatus = hasActive ? 'occupied' : 'idle';
                    
                    // Only update if status has changed
                    if (pod.status !== newStatus) {
                        // Update in database
                        const { error: updateError } = await supabase
                            .from('pods')
                            .update({ status: newStatus })
                            .eq('id', podId);
                        
                        if (updateError) {
                            console.error('Error updating pod status for pod ' + podId + ':', updateError);
                        } else {
                            // Update local data
                            pod.status = newStatus;
                            console.log('Pod ' + pod.name + ' status updated to: ' + newStatus);
                        }
                    }
                }
                
                // Re-render pods with updated statuses
                renderPodCards();
            } catch (error) {
                console.error('Error in updatePodStatusesFromBookings:', error);
            }
        }
        
        // Calculate target temperature based on fan speed
        // Correlation: Lower fan speed = Higher temperature
        // Fan speed 0: 28-30°C, Fan speed 3: 22-24°C, Fan speed 5: 18-20°C
        function calculateTargetTemperature(fanSpeed) {
            if (fanSpeed === null || fanSpeed === undefined) return null;
            // Base temperature: 30°C at fan speed 0, decreases by 2°C per level
            // Add small random variation for realism (±1°C)
            var baseTemp = 30 - (fanSpeed * 2);
            return baseTemp + (Math.random() * 2 - 1); // Range: baseTemp ± 1°C
        }
        
        // Load pods from database
        async function loadPods() {
            try {
                // Select only columns that exist in database (temperature, fan_speed, aqi removed)
                const { data, error } = await supabase
                    .from('pods')
                    .select('id, name, capacity, hardware_id, status, saved_state, created_at, updated_at')
                    .order('created_at', { ascending: true });
                
                if (error) {
                    console.error('Error loading pods:', error);
                    alert('Failed to load pods from database: ' + error.message);
                    return;
                }
                
                // Convert database records to podsData format
                podsData = (data || []).map(pod => {
                    var isSuspended = pod.status === 'suspended';
                    // Generate simulated values for display (temperature, AQI, fan speed columns removed from database)
                    // These values are simulated in the UI only - IoT devices will provide real values later
                    var simulatedFanSpeed = isSuspended ? null : 3; // Default fan speed is 3
                    var simulatedFanMode = isSuspended ? null : 'M'; // Default mode is Auto
                    // Calculate temperature based on fan speed (lower fan speed = higher temperature)
                    // Fan speed 3 (default): 22-24°C range
                    var simulatedTemp = isSuspended ? null : calculateTargetTemperature(simulatedFanSpeed);
                    var simulatedAqi = isSuspended ? null : (30 + Math.floor(Math.random() * 20));
                    
                    // Determine initial status: if not suspended, check if pod has active booking
                    var initialStatus = pod.status || 'idle';
                    if (!isSuspended && bookingsData.length > 0) {
                        var hasActive = hasActiveBooking(pod.id);
                        initialStatus = hasActive ? 'occupied' : 'idle';
                    }
                    
                    return {
                        id: pod.id,
                        name: pod.name || `Pod ${pod.id}`,
                        capacity: pod.capacity || 1,
                        hardwareId: pod.hardware_id || '',
                        status: initialStatus, // Will be updated based on bookings
                        // Simulated values (not stored in database - columns removed)
                        temperature: simulatedTemp,
                        fanSpeed: simulatedFanSpeed,
                        fanMode: simulatedFanMode, // 'M' for Auto, 'A' for Manual
                        aqi: simulatedAqi,
                        suspended: isSuspended,
                        savedState: pod.saved_state ? (typeof pod.saved_state === 'string' ? JSON.parse(pod.saved_state) : pod.saved_state) : null
                    };
                });
                
                // Update pod statuses based on bookings after loading
                await updatePodStatusesFromBookings();
                
                // Render pods
                renderPodCards();
            } catch (error) {
                console.error('Error in loadPods:', error);
                alert('Failed to load pods: ' + error.message);
            }
        }
        
        // Modal functions
        function openCreatePodModal() {
            $('#createPodModal').addClass('active');
        }
        
        function closeCreatePodModal() {
            $('#createPodModal').removeClass('active');
            $('#createPodForm')[0].reset();
        }
        
        // Create new pod
        async function createNewPod(event) {
            event.preventDefault();
            
            if (!supabase) {
                alert('Database connection not available. Please refresh the page.');
                return;
            }
            
            var podName = $('#podName').val().trim();
            var podCapacity = parseInt($('#podCapacity').val());
            var podHardwareId = $('#podHardwareId').val().trim();
            
            // Validate inputs
            if (!podName || !podCapacity || !podHardwareId) {
                alert('Please fill in all required fields.');
                return;
            }
            
            // Insert into database (temperature, fan_speed, aqi columns removed - will be provided by IoT devices)
            try {
                const { data: newPod, error: insertError } = await supabase
                    .from('pods')
                    .insert([
                        {
                            name: podName,
                            capacity: podCapacity,
                            hardware_id: podHardwareId,
                            status: 'idle',
                            saved_state: null
                        }
                    ])
                    .select()
                    .single();
                
                if (insertError) {
                    console.error('Error creating pod:', insertError);
                    alert('Failed to create pod: ' + insertError.message);
                    return;
                }
                
                // Close modal and reset form
                closeCreatePodModal();
                
                // Reload pods from database
                await loadPods();
                
                // Show success message
                alert('✓ Pod created successfully!\n\nPod Name: ' + podName + '\nCapacity: ' + podCapacity + ' person(s)\nHardware ID: ' + podHardwareId + '\nStatus: IDLE\n\nThe pod is now ready for operation.');
                
                console.log('New pod created:', newPod);
            } catch (error) {
                console.error('Error in createNewPod:', error);
                alert('Failed to create pod: ' + error.message);
            }
        }
        
        // Close modal when clicking outside
        $(document).on('click', function(event) {
            if ($(event.target).is('#createPodModal')) {
                closeCreatePodModal();
            }
        });
        
        // Get AQI class based on value
        function getAQIClass(aqi) {
            if (aqi === null || aqi === undefined) return '';
            if (aqi <= 50) return 'aqi-good';
            if (aqi <= 100) return 'aqi-moderate';
            return 'aqi-poor';
        }
        
        // Get AQI label based on value
        function getAQILabel(aqi) {
            if (aqi === null || aqi === undefined) return '';
            if (aqi <= 50) return 'Good';
            if (aqi <= 100) return 'Moderate';
            return 'Poor';
        }
        
        // Toggle fan mode between Auto (M) and Manual (A)
        function toggleFanMode(podId) {
            podId = String(podId);
            var pod = podsData.find(p => String(p.id) === podId);
            if (pod && !pod.suspended) {
                // Toggle between 'M' (Auto) and 'A' (Manual)
                pod.fanMode = pod.fanMode === 'M' ? 'A' : 'M';
                updatePodCard(podId);
                console.log('Fan mode changed to ' + pod.fanMode + ' for pod ' + podId);
            }
        }
        
        // Change fan speed (simulated only - real values will come from IoT devices)
        // Only works in Manual mode
        function changeFanSpeed(podId, change) {
            // Convert podId to string if needed (for UUID comparison)
            podId = String(podId);
            var pod = podsData.find(p => String(p.id) === podId);
            if (pod && !pod.suspended && pod.fanMode === 'A') {
                var currentSpeed = pod.fanSpeed || 3;
                var newSpeed = currentSpeed + change;
                if (newSpeed >= 0 && newSpeed <= 5) {
                    var oldSpeed = pod.fanSpeed;
                    // Update local data only (not stored in database - IoT devices will provide real values)
                    pod.fanSpeed = newSpeed;
                    
                    // Gradually adjust temperature based on fan speed change
                    // Lower fan speed = Higher temperature (correlation)
                    var targetTemp = calculateTargetTemperature(newSpeed);
                    var currentTemp = pod.temperature || calculateTargetTemperature(3);
                    var tempDifference = targetTemp - currentTemp;
                    
                    // Gradual temperature adjustment (60% towards target for realistic gradual change)
                    // This makes the temperature change smoothly over time
                    pod.temperature = currentTemp + (tempDifference * 0.6);
                    
                    updatePodCard(podId);
                    console.log('Fan speed changed from ' + oldSpeed + ' to ' + newSpeed + ' (simulated - not stored in database)');
                    console.log('Temperature adjusting to ' + pod.temperature.toFixed(1) + '°C');
                }
            }
        }
        
        // Suspend pod
        async function suspendPod(podId) {
            if (!supabase) {
                alert('Database connection not available. Please refresh the page.');
                return;
            }
            
            // Convert podId to string if needed (for UUID comparison)
            podId = String(podId);
            var pod = podsData.find(p => String(p.id) === podId);
            var podName = pod ? (pod.name || 'Pod ' + podId) : 'Pod ' + podId;
            
            if (pod && !pod.suspended) {
                // Show confirmation popup
                if (confirm('Suspend ' + podName + '?\n\n⚠️ Warning:\n• All bookings for this pod will be removed\n• No penalties will be applied\n• Email notifications will be sent to all affected users\n• Pod systems will be shut down for maintenance\n\nDo you want to proceed?')) {
                    try {
                        // First, delete all bookings for this pod (no penalties applied)
                        var deletedCount = 0;
                        const { data: deletedBookings, error: deleteError } = await supabase
                            .from('bookings')
                            .delete()
                            .eq('pod_id', podId)
                            .select();
                        
                        if (deleteError) {
                            console.error('Error deleting bookings for pod:', deleteError);
                            // Continue with suspension even if booking deletion fails
                            // (bookings might not exist or table might not be accessible)
                            console.warn('Warning: Could not delete bookings for pod. Continuing with suspension.');
                        } else {
                            deletedCount = deletedBookings ? deletedBookings.length : 0;
                            console.log('Deleted ' + deletedCount + ' booking(s) for pod ' + podId + ' (no penalties applied)');
                        }
                        
                        // Save current state (status only - temperature/AQI/fan speed columns removed from database)
                        var savedState = {
                            status: pod.status
                        };
                        
                        // Update pod status to suspended in database
                        const { error: updateError } = await supabase
                            .from('pods')
                            .update({
                                status: 'suspended',
                                saved_state: JSON.stringify(savedState)
                            })
                            .eq('id', podId);
                        
                        if (updateError) {
                            console.error('Error suspending pod:', updateError);
                            alert('Failed to suspend pod: ' + updateError.message);
                            return;
                        }
                        
                        // Reload bookings and pods from database
                        await loadBookings();
                        await loadPods();
                        
                        console.log('Pod ' + podId + ' suspended - all bookings removed (no penalties)');
                        
                        // Show success message
                        var deletedCountMsg = deletedCount > 0 
                            ? deletedCount + ' booking(s) removed. ' 
                            : '';
                        alert('✓ ' + podName + ' has been suspended.\n\n' + deletedCountMsg + 'No penalties were applied.\nEmail notifications have been sent to affected users.');
                    } catch (error) {
                        console.error('Error in suspendPod:', error);
                        alert('Failed to suspend pod: ' + error.message);
                    }
                }
            }
        }
        
        // Unsuspend pod
        async function unsuspendPod(podId) {
            if (!supabase) {
                alert('Database connection not available. Please refresh the page.');
                return;
            }
            
            // Convert podId to string if needed (for UUID comparison)
            podId = String(podId);
            var pod = podsData.find(p => String(p.id) === podId);
            var podName = pod ? (pod.name || 'Pod ' + podId) : 'Pod ' + podId;
            
            if (pod && pod.suspended) {
                // Show notification popup
                alert('Operate ' + podName + '?\n\n📧 Email notifications will be sent to all users to acknowledge the pod\'s new status.\n\nThe pod will be set to IDLE status and users can make bookings again.');
                
                try {
                    // Reload bookings to check current state (though bookings should have been deleted on suspend)
                    await loadBookings();
                    
                    // Set status to idle (since all bookings were deleted on suspend, there won't be any active bookings)
                    // Even if there are any bookings (edge case), we set to idle as the pod is being reactivated
                    var newStatus = 'idle';
                    
                    // Update pod status to idle in database
                    const { error: updateError } = await supabase
                        .from('pods')
                        .update({
                            status: newStatus,
                            saved_state: null
                        })
                        .eq('id', podId);
                    
                    if (updateError) {
                        console.error('Error unsuspending pod:', updateError);
                        alert('Failed to unsuspend pod: ' + updateError.message);
                        return;
                    }
                    
                    // Reload pods from database
                    await loadPods();
                    
                    console.log('Pod ' + podId + ' unsuspended - status set to ' + newStatus);
                    
                    // Show success message
                    setTimeout(function() {
                        alert('✓ ' + podName + ' is now operational!\n\nStatus: ' + newStatus.toUpperCase() + '\nUsers can now make bookings for this pod.\nEmail notifications have been sent to users.');
                    }, 100);
                } catch (error) {
                    console.error('Error in unsuspendPod:', error);
                    alert('Failed to unsuspend pod: ' + error.message);
                }
            }
        }
        
        // Delete pod
        async function deletePod(podId) {
            if (!supabase) {
                alert('Database connection not available. Please refresh the page.');
                return;
            }
            
            // Convert podId to string if needed (for UUID comparison)
            podId = String(podId);
            var pod = podsData.find(p => String(p.id) === podId);
            var podName = pod ? (pod.name || 'Pod ' + podId) : 'Pod ' + podId;
            
            if (pod && pod.suspended) {
                // Show confirmation dialog
                if (confirm('⚠️ DELETE ' + podName.toUpperCase() + '?\n\n🚨 WARNING: This action is PERMANENT and CANNOT be undone!\n\n• ' + podName + ' will be completely removed from the system\n• All historical data will be lost\n• This pod cannot be reused\n\nAre you absolutely sure you want to DELETE ' + podName + '?')) {
                    // Double confirmation
                    if (confirm('FINAL CONFIRMATION\n\nClick OK to permanently delete ' + podName + '\nClick Cancel to keep the pod')) {
                        try {
                            // Delete from database
                            const { error: deleteError } = await supabase
                                .from('pods')
                                .delete()
                                .eq('id', podId);
                            
                            if (deleteError) {
                                console.error('Error deleting pod:', deleteError);
                                alert('Failed to delete pod: ' + deleteError.message);
                                return;
                            }
                            
                            // Reload pods from database
                            await loadPods();
                            
                            console.log('Pod ' + podId + ' deleted');
                            
                            // Show success message
                            alert('✓ ' + podName + ' has been permanently deleted from the system.');
                        } catch (error) {
                            console.error('Error in deletePod:', error);
                            alert('Failed to delete pod: ' + error.message);
                        }
                    }
                }
            }
        }
        
        // Update a single pod card (temperature, AQI, status display)
        function updatePodCard(podId) {
            // Convert podId to string for comparison
            podId = String(podId);
            var pod = podsData.find(p => String(p.id) === podId);
            if (!pod) return;
            
            var podCard = $('#pod-' + pod.id);
            if (podCard.length === 0) return; // Card doesn't exist yet
            
            // Update temperature display
            var tempElement = podCard.find('.info-row').first().find('.info-value');
            if (tempElement.length) {
                if (pod.suspended || pod.temperature === null) {
                    tempElement.html('<span class="grayed-out">NULL</span>');
                } else {
                    tempElement.html(pod.temperature.toFixed(1) + '°C');
                }
            }
            
            // Update AQI display
            var aqiElement = podCard.find('.info-row').last().find('.info-value');
            if (aqiElement.length) {
                if (pod.suspended || pod.aqi === null) {
                    aqiElement.html('<span class="grayed-out">NULL</span>');
                } else {
                    var aqiClass = getAQIClass(pod.aqi);
                    var aqiLabel = getAQILabel(pod.aqi);
                    aqiElement.html(`${pod.aqi} <small>(${aqiLabel})</small>`);
                    aqiElement.removeClass('aqi-good aqi-moderate aqi-poor').addClass(aqiClass);
                }
            }
            
            // Update status display
            var statusElement = podCard.find('.pod-status');
            if (statusElement.length) {
                statusElement.text(pod.status);
                statusElement.removeClass('available occupied maintenance cleaning suspended idle').addClass(pod.status);
            }
            
            // Update fan speed display
            $('#pod-' + pod.id + '-fanspeed').text(pod.fanSpeed || 3);
            
            // Update fan mode button
            var modeBtn = $('#pod-' + pod.id + '-fan-mode');
            if (modeBtn.length) {
                modeBtn.text(pod.fanMode || 'M');
                modeBtn.toggleClass('manual', pod.fanMode === 'A');
                modeBtn.attr('title', pod.fanMode === 'A' ? 'Manual Mode - Click to switch to Auto' : 'Auto Mode - Click to switch to Manual');
            }
            
            // Update fan speed controls based on mode and suspended status
            var isManualMode = pod.fanMode === 'A';
            var isDisabled = pod.suspended || !isManualMode;
            var speedControls = $('#pod-' + pod.id + '-btn-minus').parent('.fan-speed-controls');
            if (speedControls.length) {
                // Disable controls when not in manual mode or when suspended
                speedControls.toggleClass('fan-control-disabled', !isManualMode || pod.suspended);
            }
            
            $('#pod-' + pod.id + '-btn-minus').prop('disabled', isDisabled || (pod.fanSpeed || 3) === 0);
            $('#pod-' + pod.id + '-btn-plus').prop('disabled', isDisabled || (pod.fanSpeed || 3) === 5);
        }
        
        // Generate pod card HTML
        function generatePodCard(pod) {
            var aqiClass = (pod.suspended || pod.aqi === null) ? '' : getAQIClass(pod.aqi);
            var aqiLabel = (pod.suspended || pod.aqi === null) ? '' : getAQILabel(pod.aqi);
            var grayedClass = (pod.suspended || pod.temperature === null) ? 'grayed-out' : '';
            
            // Temperature display
            var tempDisplay = (pod.suspended || pod.temperature === null) ? '<span class="grayed-out">NULL</span>' : `${pod.temperature.toFixed(1)}°C`;
            
            // Fan speed display with M/A toggle
            var fanSpeedHTML = '';
            // Escape pod.id for use in HTML attributes (UUIDs may contain dashes)
            var podIdEscaped = String(pod.id).replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '\\"');
            var isManualMode = pod.fanMode === 'A';
            var isDisabled = pod.suspended || !isManualMode;
            
            if (pod.suspended) {
                fanSpeedHTML = '<div class="info-value grayed-out">NULL</div>';
            } else {
                fanSpeedHTML = `
                    <div class="fan-control-wrapper">
                        <button class="fan-mode-toggle ${isManualMode ? 'manual' : ''}" 
                                id="pod-${pod.id}-fan-mode"
                                onclick="toggleFanMode('${podIdEscaped}')"
                                title="${isManualMode ? 'Manual Mode - Click to switch to Auto' : 'Auto Mode - Click to switch to Manual'}">
                            ${pod.fanMode || 'M'}
                        </button>
                        <div class="fan-speed-controls ${!isManualMode ? 'fan-control-disabled' : ''}">
                            <button class="fan-btn" id="pod-${pod.id}-btn-minus" 
                                    onclick="changeFanSpeed('${podIdEscaped}', -1)"
                                    ${isDisabled || pod.fanSpeed === 0 ? 'disabled' : ''}
                                    title="${!isManualMode ? 'Switch to Manual mode to adjust' : 'Decrease fan speed'}">
                                −
                            </button>
                            <span class="fan-speed-display" id="pod-${pod.id}-fanspeed">${pod.fanSpeed || 3}</span>
                            <button class="fan-btn" id="pod-${pod.id}-btn-plus" 
                                    onclick="changeFanSpeed('${podIdEscaped}', 1)"
                                    ${isDisabled || pod.fanSpeed === 5 ? 'disabled' : ''}
                                    title="${!isManualMode ? 'Switch to Manual mode to adjust' : 'Increase fan speed'}">
                                +
                            </button>
                        </div>
                    </div>
                `;
            }
            
            // AQI display
            var aqiDisplay = (pod.suspended || pod.aqi === null) ? '<span class="grayed-out">NULL</span>' : `${pod.aqi} <small>(${aqiLabel})</small>`;
            
            // Action buttons
            var actionButtons = '';
            if (pod.suspended) {
                actionButtons = `
                    <div class="pod-actions">
                        <button class="pod-action-btn btn-unsuspend" onclick="unsuspendPod('${podIdEscaped}')">
                            <i class="fa fa-play"></i> Operate
                        </button>
                        <button class="pod-action-btn btn-delete" onclick="deletePod('${podIdEscaped}')">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </div>
                `;
            } else {
                actionButtons = `
                    <div class="pod-actions">
                        <button class="pod-action-btn btn-suspend" onclick="suspendPod('${podIdEscaped}')">
                            <i class="fa fa-pause"></i> Suspend
                        </button>
                        <button class="pod-action-btn btn-delete" onclick="deletePod('${podIdEscaped}')" disabled>
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </div>
                `;
            }
            
            // Pod name display (use custom name if exists, otherwise "Pod X")
            var podDisplayName = pod.name || `Pod ${pod.id}`;
            
            return `
                <div class="col-md-6 col-lg-3 col-sm-6">
                    <div class="pod-card ${pod.status}" id="pod-${pod.id}">
                        <div class="pod-header">
                            <div class="pod-number">
                                <i class="fa fa-building"></i> ${podDisplayName}
                            </div>
                            <div class="pod-header-right">
                                <div class="pod-capacity">
                                    <i class="fa fa-users"></i> ${pod.capacity || 1} ${(pod.capacity || 1) === 1 ? 'person' : 'people'}
                                </div>
                                <div class="pod-status ${pod.status}">${pod.status}</div>
                            </div>
                        </div>
                        
                        <div class="pod-info">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fa fa-thermometer-half"></i>
                                    Temperature
                                </div>
                                <div class="info-value ${grayedClass}">${tempDisplay}</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fa fa-circle-o-notch"></i>
                                    Fan Speed
                                </div>
                                ${fanSpeedHTML}
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fa fa-leaf"></i>
                                    AQI Index
                                </div>
                                <div class="info-value ${aqiClass} ${grayedClass}">
                                    ${aqiDisplay}
                                </div>
                            </div>
                        </div>
                        
                        ${actionButtons}
                    </div>
                </div>
            `;
        }
        
        // Render all pod cards
        function renderPodCards() {
            var container = $('#podCardsContainer');
            container.empty();
            
            podsData.forEach(function(pod) {
                container.append(generatePodCard(pod));
            });
        }
        
        // Simulate real-time updates (temperature and AQI fluctuations)
        // These values are simulated in the UI only (columns removed from database)
        // Real values will come from IoT devices and can be stored when columns are added back
        function simulateUpdates() {
            if (podsData.length === 0) {
                return;
            }
            
            // Update each pod's simulated values (UI only, not stored in database)
            for (var i = 0; i < podsData.length; i++) {
                var pod = podsData[i];
                
                // Skip suspended pods
                if (!pod.suspended && pod.temperature !== null) {
                    // Calculate target temperature based on fan speed (correlation: lower fan speed = higher temp)
                    var targetTemp = calculateTargetTemperature(pod.fanSpeed || 3);
                    
                    if (targetTemp !== null) {
                        var currentTemp = pod.temperature;
                        var tempDifference = targetTemp - currentTemp;
                        
                        // Gradual temperature adjustment towards target (5% per update for smooth transition)
                        // This makes the temperature change gradually and realistically
                        var adjustment = tempDifference * 0.05;
                        var newTemp = currentTemp + adjustment;
                        
                        // Add small random fluctuations for realism (±0.1°C)
                        newTemp += (Math.random() - 0.5) * 0.2;
                        newTemp = Math.round(newTemp * 10) / 10;
                        
                        // Keep temperature in reasonable range based on fan speed
                        // Fan speed 0: 28-30°C, Fan speed 5: 18-20°C
                        var minTemp = 18 + ((5 - (pod.fanSpeed || 3)) * 2);
                        var maxTemp = 30 - ((pod.fanSpeed || 3) * 2);
                        newTemp = Math.max(minTemp, Math.min(maxTemp, newTemp));
                        
                        pod.temperature = newTemp;
                    }
                    
                    // Randomly fluctuate AQI slightly (±2)
                    var newAqi = (pod.aqi || 30) + Math.floor((Math.random() - 0.5) * 4);
                    newAqi = Math.max(20, Math.min(150, newAqi)); // Keep in range 20-150
                    
                    // Update local data only (not stored in database)
                    pod.aqi = newAqi;
                }
            }
            
            // Update individual pod cards with new temperature/AQI values (status updates handled separately)
            for (var i = 0; i < podsData.length; i++) {
                var pod = podsData[i];
                updatePodCard(pod.id);
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
    </script>
    <!-- Custom Js -->
    <script src="assets/js/custom-scripts.js"></script>
</body>
</html>