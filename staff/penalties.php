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
        
        /* Filter Controls */
        .filter-controls {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        .filter-controls label {
            font-weight: 600;
            margin-right: 5px;
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
        .status-paid {
            background-color: #dff0d8;
            color: #3c763d;
        }
        
        /* Manage Penalty Button */
        .manage-penalty-btn {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }
        
        .manage-penalty-btn:hover {
            background: #0056b3;
            color: #fff;
        }
        
        .manage-penalty-btn i {
            font-size: 16px;
        }
        
        .panel-heading h4 {
            overflow: hidden;
        }
        
        /* Modal Overlay */
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
        
        /* Penalty Form Modal */
        .penalty-form-modal {
            background: #fff;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
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
        
        .penalty-form-header {
            background: #fff;
            color: #333;
            padding: 24px 30px;
            border-radius: 12px 12px 0 0;
            position: relative;
            border-bottom: 1px solid #e9ecef;
        }
        
        .penalty-form-header h3 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            color: #1a1a1a;
        }
        
        .close-modal {
            position: absolute;
            top: 20px;
            right: 24px;
            background: none;
            border: none;
            color: #6c757d;
            font-size: 28px;
            cursor: pointer;
            line-height: 1;
            padding: 0;
            width: 32px;
            height: 32px;
            transition: color 0.2s;
        }
        
        .close-modal:hover {
            color: #343a40;
        }
        
        .penalty-form-body {
            padding: 30px;
        }
        
        .form-group-penalty {
            margin-bottom: 24px;
        }
        
        .form-group-penalty label {
            display: block;
            font-weight: 500;
            margin-bottom: 10px;
            color: #333;
            font-size: 15px;
        }
        
        .form-group-penalty label .required {
            color: #dc3545;
            margin-left: 2px;
        }
        
        .form-group-penalty input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
            background: #fff;
        }
        
        .form-group-penalty input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
        }
        
        .form-group-penalty input::placeholder {
            color: #adb5bd;
            font-weight: 400;
        }
        
        .input-group-penalty {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-group-penalty .dollar-sign {
            position: absolute;
            left: 16px;
            color: #6c757d;
            font-size: 16px;
            font-weight: 600;
            pointer-events: none;
            z-index: 1;
        }
        
        .input-group-penalty input {
            padding-left: 35px;
        }
        
        .penalty-form-footer {
            padding: 24px 30px;
            border-top: 1px solid #e9ecef;
            text-align: right;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background: #f8f9fa;
            border-radius: 0 0 12px 12px;
        }
        
        .btn-set-penalty {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-set-penalty:hover {
            background: #0056b3;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
        }
        
        .btn-cancel {
            background: #6c757d;
            color: #fff;
            border: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-cancel:hover {
            background: #5a6268;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
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
        @media (max-width: 768px) {
            .col-md-12 > div[style*="display: flex"] {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 15px;
            }
            
            .manage-penalty-btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 991px) {
            .panel-heading h4 {
                text-align: center;
            }
            
            .penalty-form-modal {
                width: 95%;
                margin: 0 10px;
            }
            
            .penalty-form-header {
                padding: 20px 24px;
            }
            
            .penalty-form-header h3 {
                font-size: 20px;
            }
            
            .penalty-form-body {
                padding: 24px;
            }
            
            .penalty-form-footer {
                padding: 20px 24px;
                flex-direction: column-reverse;
            }
            
            .btn-set-penalty, .btn-cancel {
                width: 100%;
                padding: 12px 24px;
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
        
        /* Global fix for all date inputs */
        input[type="date"] {
            min-width: 150px !important;
            font-family: inherit;
            color: #333 !important;
            letter-spacing: normal !important;
            word-spacing: normal !important;
        }
        
        input[type="date"]::-webkit-datetime-edit {
            padding: 0;
            display: inline-flex !important;
        }
        
        input[type="date"]::-webkit-datetime-edit-fields-wrapper {
            padding: 0;
            display: inline-flex !important;
        }
        
        input[type="date"]::-webkit-datetime-edit-text {
            padding: 0 0.3em;
            display: inline !important;
        }
        
        input[type="date"]::-webkit-datetime-edit-month-field {
            padding: 0 0.2em;
            min-width: 2ch !important;
            display: inline !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        input[type="date"]::-webkit-datetime-edit-day-field {
            padding: 0 0.2em;
            min-width: 2ch !important;
            display: inline !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        input[type="date"]::-webkit-datetime-edit-year-field {
            padding: 0 0.2em;
            min-width: 4ch !important;
            display: inline !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
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
                        <a  href="dashboard.php"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
                    </li>
                    <li>
                        <a class="active-menu" href="penalties.php"><i class="fa fa-exclamation-triangle fa-fw"></i> Penalties</a>
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
                        <div style="border-bottom: 3px solid #ddd; padding-bottom: 15px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
                            <h1 style="margin: 0; font-size: 26px; font-weight: 400; color: #5a5a5a;">PENALTIES MANAGEMENT</h1>
                            <button class="btn btn-primary btn-sm manage-penalty-btn" onclick="openPenaltyModal()" style="margin-top: 0;">
                                <i class="fa fa-plus-circle"></i> Manage Penalty Rates
                            </button>
                        </div>
                    </div>
                </div>
                <!-- /. ROW  -->
                
                <div class="row">
                    <div class="col-md-12">
                        <!-- Penalties Table -->
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4>Penalty Records</h4>
                            </div>
                            <div class="panel-body">
                                <!-- Filter Controls -->
                                <div class="filter-controls">
                                    <div>
                                        <label for="filterStatus">Status:</label>
                                        <select id="filterStatus" class="form-control" style="display: inline-block; width: 140px;">
                                            <option value="all">All Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="paid">Paid</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="filterViolation">Violation Type:</label>
                                        <select id="filterViolation" class="form-control" style="display: inline-block; width: 160px;">
                                            <option value="all">All Types</option>
                                            <option value="No Show">No Show</option>
                                            <option value="Late Checkout">Late Checkout</option>
                                            <option value="Late Cancellation">Late Cancellation</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="filterDate">Date:</label>
                                        <input type="date" id="filterDate" class="form-control" style="display: inline-block; width: 160px;">
                                    </div>
                                    <div>
                                        <label for="searchUsername">Search Username:</label>
                                        <input type="text" id="searchUsername" class="form-control" placeholder="Enter username..." style="display: inline-block; width: 200px;">
                                    </div>
                                    <button class="btn btn-sm btn-default" onclick="resetFilters()">
                                        <i class="fa fa-refresh"></i> Reset Filters
                                    </button>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Pods No.</th>
                                                <th>Violation Type</th>
                                                <th>Cancellation Date & Time</th>
                                                <th>Penalty Amount</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="penaltiesTableBody">
                                            <!-- Penalty Records -->
                                        </tbody>
                                        <tbody id="noResultsBody" style="display: none;">
                                            <tr>
                                                <td colspan="6" style="text-align: center; padding: 40px; color: #999; font-size: 16px;">
                                                    <i class="fa fa-search" style="font-size: 48px; display: block; margin-bottom: 15px; opacity: 0.3;"></i>
                                                    No penalties found with the current filters
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!--End Penalties Table -->
                    </div>
                </div>
                <!-- /. ROW  -->
            </div>
        </div>
        <!-- /. PAGE WRAPPER  -->
    </div>
    <!-- /. WRAPPER  -->
    
    <!-- Penalty Rate Management Modal -->
    <div id="penaltyModal" class="modal-overlay">
        <div class="penalty-form-modal">
            <div class="penalty-form-header">
                <h3>Manage Penalty Rates</h3>
                <button class="close-modal" onclick="closePenaltyModal()">&times;</button>
            </div>
            <div class="penalty-form-body">
                <div class="form-group-penalty">
                    <label for="lateCancellationRate">Late Cancellation Penalty <span class="required">*</span></label>
                    <div class="input-group-penalty">
                        <span class="dollar-sign">$</span>
                        <input type="number" id="lateCancellationRate" class="form-control" placeholder="10.00" step="0.01" min="0">
                    </div>
                </div>
                <div class="form-group-penalty">
                    <label for="noShowRate">No Show Penalty <span class="required">*</span></label>
                    <div class="input-group-penalty">
                        <span class="dollar-sign">$</span>
                        <input type="number" id="noShowRate" class="form-control" placeholder="25.00" step="0.01" min="0">
                    </div>
                </div>
                <div class="form-group-penalty">
                    <label for="lateCheckoutRate">Late Checkout Penalty <span class="required">*</span></label>
                    <div class="input-group-penalty">
                        <span class="dollar-sign">$</span>
                        <input type="number" id="lateCheckoutRate" class="form-control" placeholder="15.00" step="0.01" min="0">
                    </div>
                </div>
            </div>
            <div class="penalty-form-footer">
                <button class="btn-cancel" onclick="closePenaltyModal()">Cancel</button>
                <button class="btn-set-penalty" onclick="setPenaltyRates()">Set Rates</button>
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
        // Global variables
        var supabase = null;
        var currentUser = null;
        var dataTable = null;
        var penaltiesData = [];
        var podsData = [];
        var penaltyRates = {}; // Store penalty rates: { 'Late Cancellation': 10.00, 'No Show': 25.00, 'Late Checkout': 15.00 }
        
        // Initialize Supabase and load data
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
            
            // Load pods first (for lookups)
            await loadPods();
            
            // Load penalty rates from database
            await loadPenaltyRates();
            
            // Load penalties from database (which will also load users from Auth API)
            // This will call populatePenaltiesTable() which will initialize DataTable
            await loadPenalties();
            
            // Attach filter event handlers
            $('#filterStatus').on('change', function() {
                applyFilters();
            });
            
            $('#filterDate').on('change', function() {
                applyFilters();
            });
            
            // Violation type filter
            $('#filterViolation').on('change', function() {
                applyFilters();
            });
            
            // Search input - filter as user types
            $('#searchUsername').on('keyup', function() {
                applyFilters();
            });
        });
        
        // Load pods from database
        async function loadPods() {
            try {
                const { data, error } = await supabase
                    .from('pods')
                    .select('id, name')
                    .order('created_at', { ascending: true });
                
                if (error) {
                    console.error('Error loading pods:', error);
                    podsData = [];
                    return;
                }
                
                podsData = data || [];
            } catch (error) {
                console.error('Error in loadPods:', error);
                podsData = [];
            }
        }
        
        // Load penalty rates from database
        async function loadPenaltyRates() {
            try {
                const { data, error } = await supabase
                    .from('penalty_rates')
                    .select('violation_type, penalty_amount')
                    .order('violation_type', { ascending: true });
                
                if (error) {
                    console.error('Error loading penalty rates:', error);
                    // Set default rates if database query fails
                    penaltyRates = {
                        'Late Cancellation': 10.00,
                        'No Show': 25.00,
                        'Late Checkout': 15.00
                    };
                    return;
                }
                
                // Convert array to object for easy lookup
                penaltyRates = {};
                if (data && data.length > 0) {
                    data.forEach(function(rate) {
                        penaltyRates[rate.violation_type] = parseFloat(rate.penalty_amount) || 0;
                    });
                    console.log('Loaded penalty rates from database:', penaltyRates);
                } else {
                    // Set default rates if no rates found
                    console.log('No penalty rates found in database, using defaults');
                    penaltyRates = {
                        'Late Cancellation': 10.00,
                        'No Show': 25.00,
                        'Late Checkout': 15.00
                    };
                }
            } catch (error) {
                console.error('Error in loadPenaltyRates:', error);
                // Set default rates on error
                penaltyRates = {
                    'Late Cancellation': 10.00,
                    'No Show': 25.00,
                    'Late Checkout': 15.00
                };
            }
        }
        
        // Load penalties from database
        async function loadPenalties() {
            try {
                // Load all penalties (staff can see all penalties)
                const { data: penalties, error: penaltiesError } = await supabase
                    .from('penalties')
                    .select('id, user_id, pod_id, booking_id, violation_type, penalty_amount, status, violation_date, violation_time, receipt_number, paid_at, created_at, updated_at')
                    .order('violation_date', { ascending: false })
                    .order('violation_time', { ascending: false });
                
                if (penaltiesError) {
                    console.error('Error loading penalties:', penaltiesError);
                    console.error('Error details:', JSON.stringify(penaltiesError, null, 2));
                    
                    // Check if the error is because table doesn't exist
                    var errorMsg = penaltiesError.message || penaltiesError.toString() || '';
                    if (errorMsg.includes('does not exist') || errorMsg.includes('relation') || errorMsg.includes('42P01')) {
                        console.error('Penalties table does not exist. Please run create_penalties_table.sql in Supabase SQL Editor.');
                        penaltiesData = [];
                        populatePenaltiesTable();
                        return;
                    } else if (errorMsg.includes('permission') || errorMsg.includes('policy') || errorMsg.includes('RLS') || errorMsg.includes('row-level security')) {
                        console.error('RLS Policy Error: Staff/Admin may not have permission to view all penalties.');
                        console.error('Please run create_penalties_rls_policies.sql in Supabase SQL Editor to grant staff/admin access.');
                        alert('Permission Error: Cannot load penalties. Please ensure RLS policies are configured for staff/admin users.');
                        penaltiesData = [];
                        populatePenaltiesTable();
                        return;
                    } else {
                        console.error('Error loading penalties:', penaltiesError);
                        alert('Error loading penalties: ' + (penaltiesError.message || 'Unknown error'));
                    }
                    penaltiesData = [];
                    populatePenaltiesTable();
                    return;
                }
                
                console.log('Loaded penalties from database:', penalties ? penalties.length : 0, 'penalties');
                console.log('Raw penalties response:', penalties);
                
                if (!penalties || penalties.length === 0) {
                    console.log('No penalties found in database');
                    console.log('This could mean:');
                    console.log('1. No penalties exist in the database');
                    console.log('2. RLS policies are blocking access (check if user has admin/staff role)');
                    console.log('3. User metadata role:', currentUser?.user_metadata?.role);
                    penaltiesData = [];
                    populatePenaltiesTable();
                    return;
                }
                
                // Log first penalty for debugging
                if (penalties.length > 0) {
                    console.log('First penalty sample:', JSON.stringify(penalties[0], null, 2));
                }
                
                // Create pods map for quick lookup
                var podsMap = {};
                podsData.forEach(function(pod) {
                    podsMap[pod.id] = pod;
                });
                
                // Extract unique user IDs from penalties
                var userIds = [...new Set(penalties.map(p => p.user_id).filter(id => id))];
                var usersMap = {};
                
                // Load users from Supabase Auth via PHP endpoint
                if (userIds.length > 0) {
                    console.log('Loading users from Auth API for user_ids:', userIds);
                    try {
                        const response = await fetch('get_users_info.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ user_ids: userIds })
                        });
                        
                        if (!response.ok) {
                            throw new Error('Failed to fetch users: ' + response.statusText);
                        }
                        
                        const data = await response.json();
                        
                        if (data.error) {
                            console.error('Error loading users:', data.error);
                            console.warn('Using user_id as fallback for usernames');
                        } else if (data.errors && data.errors.length > 0) {
                            console.error('Errors loading users:', data.errors);
                            console.warn('Some users may not be loaded. Using fallback for missing users.');
                        }
                        
                        if (data.users && Object.keys(data.users).length > 0) {
                            console.log('Loaded users from Auth API:', Object.keys(data.users).length);
                            
                            // data.users is a map of user_id => { id, username, email }
                            Object.keys(data.users).forEach(userId => {
                                const user = data.users[userId];
                                // Use username directly from the response
                                usersMap[userId] = user.username || (user.email ? user.email.split('@')[0] : 'User ' + userId.substring(0, 8));
                            });
                            console.log('Users map created with', Object.keys(usersMap).length, 'users');
                        } else {
                            console.warn('No users returned from Auth API');
                        }
                    } catch (error) {
                        console.error('Error fetching users from Auth API:', error);
                        console.warn('Using user_id as fallback for usernames');
                    }
                } else {
                    console.log('No user IDs to load');
                }
                
                // Map penalties with user and pod data
                penaltiesData = penalties.map(function(penalty) {
                    // Get username from usersMap, fallback to user ID if not found
                    var username = 'Unknown';
                    if (penalty.user_id) {
                        if (usersMap[penalty.user_id]) {
                            username = usersMap[penalty.user_id];
                        } else {
                            // Fallback: use first 8 characters of user_id
                            username = 'User ' + penalty.user_id.substring(0, 8);
                            console.log('No username found for user_id:', penalty.user_id, '- using fallback:', username);
                        }
                    }
                    
                    // Get pod information
                    var pod = penalty.pod_id ? podsMap[penalty.pod_id] : null;
                    var podName = pod ? (pod.name || 'Pod ' + pod.id.substring(0, 8)) : 'N/A';
                    var podId = penalty.pod_id || 'N/A';
                    
                    // Format date and time
                    var dateStr = penalty.violation_date || '';
                    var timeStr = penalty.violation_time || '';
                    var dateTimeStr = '';
                    
                    if (dateStr) {
                        var date = new Date(dateStr + (timeStr ? 'T' + timeStr : ''));
                        if (!isNaN(date.getTime())) {
                            var monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                                            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                            var formattedDate = date.getDate() + ' ' + monthNames[date.getMonth()] + ' ' + date.getFullYear();
                            
                            if (timeStr) {
                                var hours = date.getHours();
                                var minutes = date.getMinutes();
                                var period = hours >= 12 ? 'PM' : 'AM';
                                var hours12 = hours % 12 || 12;
                                var formattedTime = hours12 + ':' + (minutes < 10 ? '0' : '') + minutes + ' ' + period;
                                dateTimeStr = formattedDate + ' ' + formattedTime;
                            } else {
                                dateTimeStr = formattedDate;
                            }
                        } else {
                            dateTimeStr = dateStr + (timeStr ? ' ' + timeStr : '');
                        }
                    }
                    
                    // Format penalty amount
                    var amountStr = '$' + parseFloat(penalty.penalty_amount || 0).toFixed(2);
                    
                    // Format paid_at date if available
                    var paidAtStr = '';
                    if (penalty.paid_at) {
                        var paidDate = new Date(penalty.paid_at);
                        if (!isNaN(paidDate.getTime())) {
                            var monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                                            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                            paidAtStr = paidDate.getDate() + ' ' + monthNames[paidDate.getMonth()] + ' ' + paidDate.getFullYear();
                            var hours = paidDate.getHours();
                            var minutes = paidDate.getMinutes();
                            var period = hours >= 12 ? 'PM' : 'AM';
                            var hours12 = hours % 12 || 12;
                            paidAtStr += ' ' + hours12 + ':' + (minutes < 10 ? '0' : '') + minutes + ' ' + period;
                        }
                    }
                    
                    return {
                        id: penalty.id,
                        username: username,
                        room: podId,
                        roomName: podName,
                        violationType: penalty.violation_type || '',
                        date: dateStr,
                        dateTime: dateTimeStr,
                        amount: amountStr,
                        status: penalty.status || 'pending',
                        userId: penalty.user_id,
                        podId: penalty.pod_id,
                        bookingId: penalty.booking_id,
                        receiptNumber: penalty.receipt_number || '',
                        paidAt: paidAtStr,
                        createdAt: penalty.created_at || '',
                        updatedAt: penalty.updated_at || ''
                    };
                });
                
                // Sort penalties: pending first, then by date (newest first)
                penaltiesData.sort(function(a, b) {
                    if (a.status === 'pending' && b.status !== 'pending') {
                        return -1;
                    }
                    if (a.status !== 'pending' && b.status === 'pending') {
                        return 1;
                    }
                    // If same status, sort by date (newest first)
                    return b.date.localeCompare(a.date);
                });
                
                console.log('Processed', penaltiesData.length, 'penalties');
                console.log('Sample processed penalty:', penaltiesData.length > 0 ? JSON.stringify(penaltiesData[0], null, 2) : 'none');
                
                // Populate table with loaded data
                populatePenaltiesTable();
                
                // Ensure all rows are visible after loading
                setTimeout(function() {
                    $('#penaltiesTableBody tr').show();
                    $('#noResultsBody').hide();
                    console.log('Ensured all', $('#penaltiesTableBody tr').length, 'penalty rows are visible');
                }, 200);
            } catch (error) {
                console.error('Error in loadPenalties:', error);
                penaltiesData = [];
                populatePenaltiesTable();
            }
        }
        
        // Initialize DataTable
        function initializeDataTable() {
            // Destroy existing DataTable if it exists
            if (dataTable) {
                try {
                    dataTable.fnDestroy();
                    dataTable = null;
                } catch (e) {
                    console.log('DataTable was not initialized, skipping destroy');
                }
            }
            
            // Check if table has data rows (not just "no results" message)
            var tbody = $('#penaltiesTableBody');
            var rows = tbody.find('tr');
            var hasDataRows = rows.length > 0 && !rows.first().find('td[colspan]').length;
            
            if (!hasDataRows) {
                console.log('No data rows to initialize DataTable with');
                return;
            }
            
            // Ensure all rows are visible before initializing DataTable
            rows.show();
            
            // Initialize DataTable with a small delay to ensure DOM is ready
            setTimeout(function() {
                try {
                    // Make sure all rows are still visible
                    $('#penaltiesTableBody tr').show();
                    
                    dataTable = $('#dataTables-example').dataTable({
                        "order": [[ 3, "desc" ]],  // Sort by date & time (column index 3) - newest first
                        "paging": false,  // Disable pagination - show all records
                        "searching": false,  // Disable search box (we use custom filters)
                        "info": false,  // Hide "Showing X to Y of Z entries" text
                        "autoWidth": false,
                        "columnDefs": [
                            { "orderable": true, "targets": [0, 1, 2, 3, 4, 5] }
                        ]
                    });
                    
                    // After initialization, ensure all rows are visible
                    $('#penaltiesTableBody tr').show();
                    $('#noResultsBody').hide();
                    
                    console.log('DataTable initialized successfully with', rows.length, 'rows');
                } catch (e) {
                    console.error('Error initializing DataTable:', e);
                }
            }, 150);
        }
        
        function populatePenaltiesTable() {
            var tbody = $('#penaltiesTableBody');
            tbody.empty();
            
            if (penaltiesData.length === 0) {
                // Show "no results" message if no penalties
                $('#noResultsBody').show();
                // Destroy DataTable if it exists
                if (dataTable) {
                    try {
                        dataTable.fnDestroy();
                        dataTable = null;
                    } catch (e) {
                        console.log('Error destroying DataTable:', e);
                    }
                }
                return;
            }
            
            $('#noResultsBody').hide();
            
            penaltiesData.forEach(function(penalty) {
                var statusClass = 'status-' + penalty.status;
                var statusText = penalty.status.charAt(0).toUpperCase() + penalty.status.slice(1);
                
                // Escape HTML to prevent XSS
                var escapeHtml = function(text) {
                    var map = {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;'
                    };
                    return (text || '').toString().replace(/[&<>"']/g, function(m) { return map[m]; });
                };
                
                var row = '<tr data-status="' + escapeHtml(penalty.status) + '" data-date="' + escapeHtml(penalty.date) + 
                    '" data-violation="' + escapeHtml(penalty.violationType) + '" data-username="' + escapeHtml(penalty.username.toLowerCase()) + '">' +
                    '<td>' + escapeHtml(penalty.username) + '</td>' +
                    '<td>' + escapeHtml(penalty.roomName) + '</td>' +
                    '<td>' + escapeHtml(penalty.violationType) + '</td>' +
                    '<td>' + escapeHtml(penalty.dateTime) + '</td>' +
                    '<td>' + escapeHtml(penalty.amount) + '</td>' +
                    '<td><span class="status-badge ' + statusClass + '">' + escapeHtml(statusText) + '</span></td>' +
                    '</tr>';
                
                tbody.append(row);
            });
            
            console.log('Populated table with', penaltiesData.length, 'penalty records');
            
            // Reinitialize DataTable after populating
            initializeDataTable();
        }
        
        function applyFilters() {
            var statusFilter = $('#filterStatus').val();
            var dateFilter = $('#filterDate').val();
            var violationFilter = $('#filterViolation').val();
            var searchText = $('#searchUsername').val().toLowerCase().trim();
            
            console.log('FILTERING - Status:', statusFilter, '| Date:', dateFilter, '| Violation:', violationFilter, '| Search:', searchText);
            
            // If DataTable is initialized, destroy it temporarily to apply manual filters
            var wasDataTableActive = false;
            if (dataTable) {
                try {
                    dataTable.fnDestroy();
                    wasDataTableActive = true;
                    dataTable = null;
                } catch (e) {
                    console.log('Error destroying DataTable for filtering:', e);
                }
            }
            
            // Show all rows first
            $('#penaltiesTableBody tr').show();
            
            var totalRows = $('#penaltiesTableBody tr').length;
            console.log('Total rows before filtering:', totalRows);
            
            // Apply status filter
            if (statusFilter !== 'all') {
                $('#penaltiesTableBody tr').each(function() {
                    var rowStatus = $(this).attr('data-status');
                    if (rowStatus !== statusFilter) {
                        $(this).hide();
                    }
                });
            }
            
            // Apply date filter
            if (dateFilter !== '') {
                $('#penaltiesTableBody tr:visible').each(function() {
                    var rowDate = $(this).attr('data-date');
                    if (rowDate !== dateFilter) {
                        $(this).hide();
                    }
                });
            }
            
            // Apply violation type filter
            if (violationFilter !== 'all') {
                $('#penaltiesTableBody tr:visible').each(function() {
                    var rowViolation = $(this).attr('data-violation');
                    if (rowViolation !== violationFilter) {
                        $(this).hide();
                    }
                });
            }
            
            // Apply username search filter
            if (searchText !== '') {
                $('#penaltiesTableBody tr:visible').each(function() {
                    var username = $(this).find('td:first').text().toLowerCase();
                    if (username.indexOf(searchText) === -1) {
                        $(this).hide();
                    }
                });
            }
            
            var visibleCount = $('#penaltiesTableBody tr:visible').length;
            var hiddenCount = $('#penaltiesTableBody tr:hidden').length;
            console.log('Results:', visibleCount, 'rows shown,', hiddenCount, 'rows hidden');
            
            // Show/hide "no results" message
            if (visibleCount === 0 && totalRows > 0) {
                $('#noResultsBody').show();
            } else {
                $('#noResultsBody').hide();
            }
            
            // Reinitialize DataTable if it was active and we have visible rows
            if (wasDataTableActive && visibleCount > 0) {
                setTimeout(function() {
                    initializeDataTable();
                }, 100);
            }
        }
        
        function resetFilters() {
            // Reset dropdowns and search
            $('#filterStatus').val('all');
            $('#filterDate').val('');
            $('#filterViolation').val('all');
            $('#searchUsername').val('');
            
            // Destroy DataTable if it exists
            if (dataTable) {
                try {
                    dataTable.fnDestroy();
                    dataTable = null;
                } catch (e) {
                    console.log('Error destroying DataTable on reset:', e);
                }
            }
            
            // Show all rows and hide no results message
            $('#penaltiesTableBody tr').show();
            $('#noResultsBody').hide();
            
            var visibleCount = $('#penaltiesTableBody tr:visible').length;
            console.log('Filters reset - showing all', visibleCount, 'rows');
            
            // Reinitialize DataTable after reset
            if (visibleCount > 0) {
                setTimeout(function() {
                    initializeDataTable();
                }, 100);
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
            
            // Request notification permission on page load
            if ("Notification" in window && Notification.permission === "default") {
                Notification.requestPermission();
            }
            
            // Note: DataTable will be initialized after penalties are loaded in loadPenalties()
            
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
        
        // Penalty Rate Management Functions
        async function openPenaltyModal() {
            // Load current penalty rates from database
            await loadPenaltyRates();
            
            // Populate form fields with current rates
            $('#lateCancellationRate').val(penaltyRates['Late Cancellation'] || '');
            $('#noShowRate').val(penaltyRates['No Show'] || '');
            $('#lateCheckoutRate').val(penaltyRates['Late Checkout'] || '');
            
            $('#penaltyModal').addClass('active');
            // Prevent body scroll when modal is open
            $('body').css('overflow', 'hidden');
        }
        
        function closePenaltyModal() {
            $('#penaltyModal').removeClass('active');
            // Don't reset form fields - keep them for next time
            // Restore body scroll
            $('body').css('overflow', '');
        }
        
        async function setPenaltyRates() {
            var lateCancellation = $('#lateCancellationRate').val().trim();
            var noShow = $('#noShowRate').val().trim();
            var lateCheckout = $('#lateCheckoutRate').val().trim();
            
            // Check if at least one field is filled
            if (!lateCancellation && !noShow && !lateCheckout) {
                alert('Please enter at least one penalty rate to update.');
                return;
            }
            
            // Validate that all filled rates are valid numbers
            var ratesToUpdate = [];
            
            if (lateCancellation) {
                var lateCancellationNum = parseFloat(lateCancellation);
                if (isNaN(lateCancellationNum) || lateCancellationNum < 0) {
                    alert('Invalid penalty amount for Late Cancellation. Please enter a valid number >= 0.');
                    return;
                }
                ratesToUpdate.push({
                    violation_type: 'Late Cancellation',
                    penalty_amount: lateCancellationNum
                });
            }
            
            if (noShow) {
                var noShowNum = parseFloat(noShow);
                if (isNaN(noShowNum) || noShowNum < 0) {
                    alert('Invalid penalty amount for No Show. Please enter a valid number >= 0.');
                    return;
                }
                ratesToUpdate.push({
                    violation_type: 'No Show',
                    penalty_amount: noShowNum
                });
            }
            
            if (lateCheckout) {
                var lateCheckoutNum = parseFloat(lateCheckout);
                if (isNaN(lateCheckoutNum) || lateCheckoutNum < 0) {
                    alert('Invalid penalty amount for Late Checkout. Please enter a valid number >= 0.');
                    return;
                }
                ratesToUpdate.push({
                    violation_type: 'Late Checkout',
                    penalty_amount: lateCheckoutNum
                });
            }
            
            if (ratesToUpdate.length === 0) {
                alert('Please enter at least one valid penalty rate to update.');
                return;
            }
            
            // Show loading indicator
            var setButton = $('.btn-set-penalty');
            var originalText = setButton.text();
            setButton.prop('disabled', true).text('Saving...');
            
            try {
                // Send update request to PHP endpoint
                const response = await fetch('update_penalty_rates.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        rates: ratesToUpdate,
                        user_id: currentUser ? currentUser.id : null
                    })
                });
                
                const result = await response.json();
                
                if (!response.ok || !result.success) {
                    throw new Error(result.error || 'Failed to update penalty rates');
                }
                
                // Reload penalty rates from database to get the latest values
                await loadPenaltyRates();
                
                console.log('Penalty rates updated successfully:', result);
                console.log('Updated penalty rates:', penaltyRates);
                
                // Close the modal
                closePenaltyModal();
                
                // Show success notification
                showPenaltyRateUpdateNotification();
                
            } catch (error) {
                console.error('Error updating penalty rates:', error);
                alert('Error updating penalty rates: ' + (error.message || 'Unknown error') + '\n\nPlease try again.');
            } finally {
                // Restore button state
                setButton.prop('disabled', false).text(originalText);
            }
        }
        
        function showPenaltyRateUpdateNotification() {
            
            // Show browser notification
            if ("Notification" in window) {
                console.log('Notification permission:', Notification.permission);
                
                if (Notification.permission === "granted") {
                    // Show the notification
                    try {
                        var notification = new Notification("Penalty Rates Updated!", {
                            body: "All users will be informed about the new charges. Only new penalties will apply the updated rates.",
                            icon: "../images/FYP_Logo_small.png",
                            requireInteraction: false
                        });
                        
                        console.log('Notification created successfully');
                        
                        // Auto-close notification after 5 seconds
                        setTimeout(function() {
                            notification.close();
                        }, 5000);
                    } catch (e) {
                        console.error('Error creating notification:', e);
                        // Fallback to alert
                        alert('Penalty Rates Updated!\n\nAll users will be informed about the new charges. Only new penalties will apply the updated rates.');
                    }
                } else if (Notification.permission !== "denied") {
                    // Request permission
                    console.log('Requesting notification permission...');
                    Notification.requestPermission().then(function(permission) {
                        console.log('Permission response:', permission);
                        if (permission === "granted") {
                            try {
                                var notification = new Notification("Penalty Rates Updated!", {
                                    body: "All users will be informed about the new charges. Only new penalties will apply the updated rates.",
                                    icon: "../images/FYP_Logo_small.png",
                                    requireInteraction: false
                                });
                                
                                setTimeout(function() {
                                    notification.close();
                                }, 5000);
                            } catch (e) {
                                console.error('Error creating notification:', e);
                                alert('Penalty Rates Updated!\n\nAll users will be informed about the new charges. Only new penalties will apply the updated rates.');
                            }
                        } else {
                            // Permission denied, use alert as fallback
                            alert('Penalty Rates Updated!\n\nAll users will be informed about the new charges. Only new penalties will apply the updated rates.');
                        }
                    });
                } else {
                    // Permission denied, use alert as fallback
                    console.log('Notification permission denied, using alert');
                    alert('Penalty Rates Updated!\n\nAll users will be informed about the new charges. Only new penalties will apply the updated rates.');
                }
            } else {
                // Browser doesn't support notifications
                console.log('Browser does not support notifications');
                alert('Penalty Rates Updated!\n\nAll users will be informed about the new charges. Only new penalties will apply the updated rates.');
            }
        }
        
        // Close modal when clicking outside of it
        $(document).on('click', '#penaltyModal', function(e) {
            if (e.target.id === 'penaltyModal') {
                closePenaltyModal();
            }
        });
        
        // Close modal with Escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#penaltyModal').hasClass('active')) {
                closePenaltyModal();
            }
        });
    </script>
    <!-- Custom Js -->
    <script src="assets/js/custom-scripts.js"></script>
</body>
</html>