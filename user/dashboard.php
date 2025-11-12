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
$SUPABASE_SERVICE_KEY = $_ENV['SUPABASE_SERVICE_KEY'] ?? '';
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
            padding: 5px 14px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .status-upcoming {
            background-color: #d9edf7;
            color: #31708f;
        }
        .status-in-progress-checkin {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-in-progress-occupied {
            background-color: #d4edda;
            color: #155724;
        }
        .status-in-progress-checkout {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-completed {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: flex-start;
            flex-wrap: wrap;
        }
        
        .btn-view {
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 5px;
            transition: all 0.2s;
        }
        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
        }
        
        .btn-delete {
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 5px;
            transition: all 0.2s;
        }
        .btn-delete:hover:not(.disabled):not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }
        .btn-delete.disabled,
        .btn-delete:disabled {
            opacity: 0.5;
            cursor: not-allowed !important;
            pointer-events: none;
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            color: #fff !important;
        }
        .btn-delete.disabled:hover,
        .btn-delete:disabled:hover {
            transform: none;
            box-shadow: none;
        }
        
        /* Booking Details Modal */
        .booking-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        
        .booking-modal-overlay.active {
            display: flex;
        }
        
        .booking-modal {
            background: #fff;
            border-radius: 10px;
            width: 90%;
            max-width: 750px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .booking-modal-header {
            background: #fff;
            color: #333;
            padding: 20px 30px;
            border-bottom: 1px solid #e5e5e5;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .booking-modal-header h3 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }
        
        .close-booking-modal {
            background: none;
            border: none;
            color: #999;
            font-size: 28px;
            cursor: pointer;
            line-height: 1;
            padding: 0;
            width: 30px;
            height: 30px;
            transition: color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 300;
        }
        
        .close-booking-modal:hover {
            color: #333;
        }
        
        .booking-modal-body {
            padding: 30px;
            overflow-y: auto;
            flex: 1;
        }
        
        .booking-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px 30px;
            margin-bottom: 25px;
        }
        
        .booking-detail-row {
            margin-bottom: 0;
        }
        
        .booking-detail-row.full-width {
            grid-column: 1 / -1;
        }
        
        .detail-label {
            font-size: 15px;
            color: #555;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }
        
        .detail-value {
            font-size: 16px;
            font-weight: 400;
            color: #333;
            padding: 0;
            background: transparent;
            border-radius: 0;
            border: none;
            display: block;
        }
        
        .detail-value .status-badge {
            display: inline-block;
        }
        
        .capacity-info {
            background: #f0f7ff;
            color: #1a5490;
            padding: 15px 20px;
            border-radius: 6px;
            border: 1px solid #cce5ff;
            margin-top: 25px;
            text-align: left;
            font-weight: 600;
            font-size: 15px;
        }
        
        .capacity-info i {
            margin-right: 8px;
            font-size: 16px;
        }
        
        .booking-modal-footer {
            padding: 20px 30px;
            border-top: 1px solid #e5e5e5;
            background: #fafafa;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
        
        .btn-modal-cancel {
            padding: 12px 28px;
            background: #6c757d;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .btn-modal-cancel:hover {
            background: #5a6268;
        }
        
        .btn-modal-close {
            padding: 12px 28px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .btn-modal-close:hover {
            background: #0056b3;
        }
        
        /* Create Booking Button */
        .create-booking-btn {
            background: #007bff;
            color: #fff;
            padding: 10px 24px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            position: relative;
        }
        
        .create-booking-btn i {
            font-size: 16px;
        }
        
        .create-booking-btn:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }
        
        .create-booking-btn:disabled {
            background: #6c757d !important;
            color: #fff !important;
            cursor: not-allowed !important;
            opacity: 0.6 !important;
            transform: none !important;
            box-shadow: none !important;
            border-color: #6c757d !important;
        }
        
        .create-booking-btn:disabled:hover {
            background: #6c757d !important;
            transform: none !important;
            box-shadow: none !important;
            border-color: #6c757d !important;
        }
        
        /* Tooltip for disabled Create Booking button */
        .create-booking-btn[disabled] {
            position: relative;
        }
        
        .create-booking-btn[disabled]:hover::after {
            content: "Penalties are currently unpaid";
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: #fff;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 13px;
            white-space: nowrap;
            margin-bottom: 8px;
            z-index: 10000;
            pointer-events: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .create-booking-btn[disabled]:hover::before {
            content: "";
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 6px solid transparent;
            border-top-color: #333;
            margin-bottom: 2px;
            z-index: 10001;
            pointer-events: none;
        }
        
        /* Create Booking Modal */
        .create-booking-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        
        .create-booking-modal-overlay.active {
            display: flex;
        }
        
        .create-booking-modal {
            background: #fff;
            border-radius: 10px;
            width: 90%;
            max-width: 550px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .create-booking-form-group {
            margin-bottom: 20px;
        }
        
        .create-booking-form-group:last-of-type {
            margin-bottom: 0;
        }
        
        .create-booking-form-group label {
            display: block;
            font-size: 15px;
            color: #555;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .create-booking-form-group label .required {
            color: #dc3545;
            margin-left: 2px;
        }
        
        .create-booking-form-group input,
        .create-booking-form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.2s;
            background: #fff;
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
        
        .create-booking-form-group input[type="date"] {
            min-width: 100%;
            font-family: inherit;
            color: #333;
        }
        
        .create-booking-form-group input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
        }
        
        .create-booking-form-group input:focus,
        .create-booking-form-group select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
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
            .booking-modal {
                width: 95%;
                max-width: none;
                margin: 10px;
            }
            
            .booking-modal-header {
                padding: 18px 20px;
            }
            
            .booking-modal-header h3 {
                font-size: 20px;
            }
            
            .booking-modal-body {
                padding: 20px;
            }
            
            .booking-modal-footer {
                padding: 15px 20px;
            }
            
            .btn-modal-close {
                width: 100%;
                padding: 12px;
            }
            
            .booking-details-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .booking-detail-row {
                margin-bottom: 0;
            }
            
            .detail-label {
                font-size: 14px;
            }
            
            .detail-value {
                font-size: 15px;
                padding: 0;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
            
            .btn-view, .btn-delete {
                width: 100%;
                font-size: 11px;
                padding: 5px 10px;
            }
            
            .create-booking-btn {
                width: 100%;
                justify-content: center;
                margin-top: 15px;
                font-size: 14px;
                padding: 10px 16px;
            }
            
            .create-booking-modal {
                width: 95%;
                margin: 10px;
            }
            
            .create-booking-form-group input,
            .create-booking-form-group select {
                font-size: 14px;
                padding: 10px 14px;
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
                        <a class="active-menu" href="dashboard.php"><i class="fa fa-calendar fa-fw"></i> Bookings</a>
                    </li>
                    <li>
                        <a href="penalties.php"><i class="fa fa-gavel fa-fw"></i> Penalties</a>
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
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #ddd; padding-bottom: 15px; margin-bottom: 25px; flex-wrap: wrap;">
                            <h1 style="margin: 0; font-size: 26px; font-weight: 400; color: #5a5a5a;">MY BOOKINGS</h1>
                            <button class="create-booking-btn" id="createBookingBtn" onclick="openCreateBookingModal()" disabled>
                                <i class="fa fa-plus-circle"></i> Create a booking
                            </button>
                        </div>
                    </div>
                </div>
                <!-- /. ROW  -->
                
                <div class="row">
                    <div class="col-md-12">
                        <!-- Bookings Table -->
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4>Booking History</h4>
                            </div>
                            <div class="panel-body">
                                <!-- Filter Controls -->
                                <div class="filter-controls">
                                    <div>
                                        <label for="filterStatus">Status:</label>
                                        <select id="filterStatus" class="form-control" style="display: inline-block; width: 200px;">
                                            <option value="all">All Statuses</option>
                                            <option value="upcoming">Upcoming</option>
                                            <option value="in-progress-checkin">In-Progress (Check-In)</option>
                                            <option value="in-progress-occupied">In Use</option>
                                            <option value="in-progress-checkout">In-Progress (Check-Out)</option>
                                            <option value="completed">Completed</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="filterPod">Pod:</label>
                                        <select id="filterPod" class="form-control" style="display: inline-block; width: 140px;">
                                            <option value="all">All Pods</option>
                                            <!-- Pod options will be populated dynamically from database -->
                                        </select>
                                    </div>
                                    <div>
                                        <label for="filterDate">Date:</label>
                                        <input type="date" id="filterDate" class="form-control" style="display: inline-block; width: 160px;" >
                                    </div>
                                    <button class="btn btn-sm btn-default" onclick="resetFilters()">
                                        <i class="fa fa-refresh"></i> Reset
                                    </button>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                        <thead>
                                            <tr>
                                                <th>Booking Date</th>
                                                <th>Pod No.</th>
                                                <th>Check-In Time</th>
                                                <th>Check-Out Time</th>
                                                <th>Duration</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="bookingsTableBody">
                                            <!-- User's Bookings -->
                                        </tbody>
                                        <tbody id="noResultsBody" style="display: none;">
                                            <tr>
                                                <td colspan="7" style="text-align: center; padding: 40px; color: #999; font-size: 16px;">
                                                    <i class="fa fa-search" style="font-size: 48px; display: block; margin-bottom: 15px; opacity: 0.3;"></i>
                                                    No bookings found with the current filters
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!--End Bookings Table -->
                    </div>
                </div>
                <!-- /. ROW  -->
            </div>
        </div>
        <!-- /. PAGE WRAPPER  -->
    </div>
    <!-- /. WRAPPER  -->
    
    <!-- Booking Details Modal -->
    <div class="booking-modal-overlay" id="bookingModal">
        <div class="booking-modal">
            <div class="booking-modal-header">
                <h3>Booking Details</h3>
                <button class="close-booking-modal" onclick="closeBookingModal()">&times;</button>
            </div>
            <div class="booking-modal-body" id="bookingDetailsContent">
                <!-- Booking details will be populated here -->
            </div>
            <div class="booking-modal-footer">
                <button class="btn-modal-close" onclick="closeBookingModal()">Close</button>
            </div>
        </div>
    </div>
    
    <!-- Create Booking Modal -->
    <div class="create-booking-modal-overlay" id="createBookingModal">
        <div class="create-booking-modal">
            <div class="booking-modal-header">
                <h3>Create a Booking</h3>
                <button class="close-booking-modal" onclick="closeCreateBookingModal()">&times;</button>
            </div>
            <div class="booking-modal-body">
                <form id="createBookingForm" onsubmit="return false;">
                    <div class="create-booking-form-group">
                        <label for="bookingDate">Booking Date <span class="required">*</span></label>
                        <input type="date" id="bookingDate" name="bookingDate" required min="" placeholder="Select date" style="color-scheme: light;" onchange="updateCheckInTimeOptions(); updateCheckOutOptions(); validateBookingTimes();">
                        <small style="color: #666; font-size: 13px; margin-top: 5px; display: block;">Format: DD-MM-YYYY</small>
                        <small id="bufferTimeWarning" style="display: none; color: #856404; margin-top: 5px; font-size: 13px; font-weight: 600;">
                            ⚠️ For same-day bookings, check-in must be at least 15 minutes from now.
                        </small>
                    </div>
                    
                    <div class="create-booking-form-group">
                        <label for="checkInTime">Check-In Time <span class="required">*</span></label>
                        <select id="checkInTime" required onchange="updateCheckOutOptions(); validateBookingTimes();">
                            <option value="">Select check-in time...</option>
                            <option value="08:00">08:00 AM</option>
                            <option value="08:15">08:15 AM</option>
                            <option value="08:30">08:30 AM</option>
                            <option value="08:45">08:45 AM</option>
                            <option value="09:00">09:00 AM</option>
                            <option value="09:15">09:15 AM</option>
                            <option value="09:30">09:30 AM</option>
                            <option value="09:45">09:45 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="10:15">10:15 AM</option>
                            <option value="10:30">10:30 AM</option>
                            <option value="10:45">10:45 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="11:15">11:15 AM</option>
                            <option value="11:30">11:30 AM</option>
                            <option value="11:45">11:45 AM</option>
                            <option value="12:00">12:00 PM</option>
                            <option value="12:15">12:15 PM</option>
                            <option value="12:30">12:30 PM</option>
                            <option value="12:45">12:45 PM</option>
                            <option value="13:00">01:00 PM</option>
                            <option value="13:15">01:15 PM</option>
                            <option value="13:30">01:30 PM</option>
                            <option value="13:45">01:45 PM</option>
                            <option value="14:00">02:00 PM</option>
                            <option value="14:15">02:15 PM</option>
                            <option value="14:30">02:30 PM</option>
                            <option value="14:45">02:45 PM</option>
                            <option value="15:00">03:00 PM</option>
                            <option value="15:15">03:15 PM</option>
                            <option value="15:30">03:30 PM</option>
                            <option value="15:45">03:45 PM</option>
                            <option value="16:00">04:00 PM</option>
                            <option value="16:15">04:15 PM</option>
                            <option value="16:30">04:30 PM</option>
                            <option value="16:45">04:45 PM</option>
                            <option value="17:00">05:00 PM</option>
                            <option value="17:15">05:15 PM</option>
                            <option value="17:30">05:30 PM</option>
                            <option value="17:45">05:45 PM</option>
                            <option value="18:00">06:00 PM</option>
                            <option value="18:15">06:15 PM</option>
                            <option value="18:30">06:30 PM</option>
                            <option value="18:45">06:45 PM</option>
                            <option value="19:00">07:00 PM</option>
                            <option value="19:15">07:15 PM</option>
                            <option value="19:30">07:30 PM</option>
                            <option value="19:45">07:45 PM</option>
                            <option value="20:00">08:00 PM</option>
                        </select>
                    </div>
                    
                    <div class="create-booking-form-group">
                        <label for="checkOutTime">Check-Out Time <span class="required">*</span></label>
                        <select id="checkOutTime" required disabled>
                            <option value="">Select check-out time...</option>
                        </select>
                        <small id="peakHourWarning" style="display: none; color: #dc3545; margin-top: 5px; font-size: 13px;">
                            ⚠️ Peak hours (8:00 AM - 5:00 PM): Maximum 1 hour booking
                        </small>
                    </div>
                    
                    <div class="create-booking-form-group">
                        <label for="numberOfPeople">Number of People <span class="required">*</span></label>
                        <input type="number" id="numberOfPeople" min="1" max="8" placeholder="Enter number of people (1-8)" required>
                    </div>
                    
                </form>
            </div>
            <div class="booking-modal-footer">
                <button class="btn-modal-cancel" onclick="closeCreateBookingModal()">Cancel</button>
                <button class="btn-modal-close" onclick="checkAvailability()">Create Booking</button>
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
        var supabase;
        var currentUser = null;
        var bookingsData = [];
        var podsData = [];
        var penaltyRates = {}; // Store penalty rates from database
        var refreshInterval = null;
        var hasPendingPenalties = true; // Default to true (disabled) until we check - prevents accidental clicks
        
        function convertTo12Hour(time24) {
            // time24 format: "HH:MM" (e.g., "08:30", "13:00", "17:45")
            var parts = time24.split(':');
            var hours = parseInt(parts[0]);
            var minutes = parts[1];
            
            var period = hours >= 12 ? 'PM' : 'AM';
            var hours12 = hours % 12 || 12; // Convert 0 to 12 for midnight, 13-23 to 1-11
            
            return hours12 + ':' + minutes + ' ' + period;
        }
        
        function formatDate(dateStr) {
            // dateStr format: "YYYY-MM-DD"
            var date = new Date(dateStr + 'T00:00:00');
            var options = { year: 'numeric', month: 'short', day: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        }
        
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
        
        // Get status text for display
        function getStatusText(status) {
            switch(status) {
                case 'upcoming':
                    return 'Upcoming';
                case 'in-progress-checkin':
                    return 'In-Progress (Check-In)';
                case 'in-progress-occupied':
                    return 'In Use';
                case 'in-progress-checkout':
                    return 'In-Progress (Check-Out)';
                case 'completed':
                    return 'Completed';
                default:
                    return 'Unknown';
            }
        }
        
        // Check if booking can be cancelled (not in progress stages)
        function canCancelBooking(status) {
            // Can only cancel if status is 'upcoming'
            // Cannot cancel if status is in-progress-checkin, in-progress-occupied, in-progress-checkout, or completed
            // Bookings that are in progress (stage 2 onwards) or completed cannot be cancelled
            return status === 'upcoming';
        }
        
        // Check if cancellation charge applies (less than 3 hours before booking)
        function cancellationChargeApplies(bookingDate, checkIn) {
            var now = new Date();
            var checkInDateTime = new Date(bookingDate + 'T' + checkIn + ':00');
            var timeDiff = checkInDateTime - now;
            var threeHoursInMs = 3 * 60 * 60 * 1000; // 3 hours in milliseconds
            
            // Charge applies if less than 3 hours before booking
            return timeDiff > 0 && timeDiff < threeHoursInMs;
        }
        
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
            
            // Ensure button is disabled immediately (button is already disabled in HTML)
            // This provides instant protection while we check for penalties
            const createBookingBtn = $('#createBookingBtn');
            if (createBookingBtn.length) {
                createBookingBtn.prop('disabled', true);
                const btnElement = createBookingBtn[0];
                if (btnElement) {
                    btnElement.style.setProperty('background-color', '#6c757d', 'important');
                    btnElement.style.setProperty('border-color', '#6c757d', 'important');
                    btnElement.style.setProperty('opacity', '0.6', 'important');
                    btnElement.style.setProperty('cursor', 'not-allowed', 'important');
                }
            }
            
            // Check for pending penalties (will enable button if no penalties found)
            await checkPendingPenalties();
            
            // Load penalty rates from database (for accurate penalty amounts)
            await loadPenaltyRates();
            
            // Load pods and bookings
            await loadPods();
            await loadBookings();
            
            // Populate pod filter dropdown
            populatePodFilter();
            
            // Initialize DataTable
            if (dataTable) {
                dataTable.fnDestroy();
                dataTable = null;
            }
            
            populateBookingsTable();
            
            dataTable = $('#dataTables-example').dataTable({
                "order": [[ 2, "asc" ]],
                "paging": false,
                "searching": false,
                "info": false
            });
            
            // Start refresh interval (10 seconds for real-time status updates)
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
            refreshInterval = setInterval(async function() {
                await checkPendingPenalties(); // Check penalties on each refresh
                await loadBookings(); // Reload bookings for real-time status updates
                if (dataTable) {
                    dataTable.fnDestroy();
                    dataTable = null;
                }
                populateBookingsTable();
                dataTable = $('#dataTables-example').dataTable({
                    "order": [[ 2, "asc" ]],
                    "paging": false,
                    "searching": false,
                    "info": false
                });
                applyFilters(); // Reapply filters after refresh
            }, 10000); // Update every 10 seconds for real-time status simulation
            
            // Attach filter event handlers
            $('#filterStatus').on('change', function() {
                applyFilters();
            });
            
            $('#filterPod').on('change', function() {
                applyFilters();
            });
            
            $('#filterDate').on('change', function() {
                applyFilters();
            });
        });
        
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
        
        // Populate pod filter dropdown
        function populatePodFilter() {
            var filterPod = $('#filterPod');
            // Clear existing options except "All Pods"
            filterPod.find('option:not([value="all"])').remove();
            
            // Add pods from database
            podsData.forEach(function(pod) {
                var podName = pod.name || 'Pod ' + pod.id;
                filterPod.append('<option value="' + pod.id + '">' + podName + '</option>');
            });
        }
        
        // Check for pending penalties
        async function checkPendingPenalties() {
            if (!currentUser || !supabase) {
                // Keep button disabled if not initialized
                hasPendingPenalties = true;
                updateCreateBookingButtonState();
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
                    // If table doesn't exist, assume no penalties and enable button
                    if (penaltiesError.message && (
                        penaltiesError.message.includes('does not exist') || 
                        penaltiesError.message.includes('relation') || 
                        penaltiesError.message.includes('42P01')
                    )) {
                        hasPendingPenalties = false;
                    } else {
                        console.error('Error checking pending penalties:', penaltiesError);
                        // On error, enable button (assume no penalties to avoid blocking users)
                        hasPendingPenalties = false;
                    }
                } else {
                    hasPendingPenalties = penalties && penalties.length > 0;
                }
                
                // Update Create Booking button state immediately
                updateCreateBookingButtonState();
            } catch (error) {
                console.error('Error in checkPendingPenalties:', error);
                // On error, enable button (assume no penalties to avoid blocking users)
                hasPendingPenalties = false;
                updateCreateBookingButtonState();
            }
        }
        
        // Update Create Booking button state based on pending penalties
        function updateCreateBookingButtonState() {
            const createBookingBtn = $('#createBookingBtn');
            if (!createBookingBtn.length) {
                return;
            }
            
            if (hasPendingPenalties) {
                // Keep button disabled and grayed out
                createBookingBtn.prop('disabled', true);
                createBookingBtn.attr('disabled', 'disabled');
                createBookingBtn.attr('title', 'Penalties are currently unpaid');
                // Force gray styles with inline styles
                const btnElement = createBookingBtn[0];
                if (btnElement) {
                    btnElement.style.setProperty('background-color', '#6c757d', 'important');
                    btnElement.style.setProperty('border-color', '#6c757d', 'important');
                    btnElement.style.setProperty('opacity', '0.6', 'important');
                    btnElement.style.setProperty('cursor', 'not-allowed', 'important');
                }
            } else {
                // Enable button and restore default styles
                createBookingBtn.prop('disabled', false);
                createBookingBtn.removeAttr('disabled');
                createBookingBtn.removeAttr('title');
                // Remove inline styles to restore default blue button
                const btnElement = createBookingBtn[0];
                if (btnElement) {
                    btnElement.style.removeProperty('background-color');
                    btnElement.style.removeProperty('border-color');
                    btnElement.style.removeProperty('opacity');
                    btnElement.style.removeProperty('cursor');
                }
            }
        }
        
        // Load pods from database
        async function loadPods() {
            try {
                const { data, error } = await supabase
                    .from('pods')
                    .select('id, name, capacity, status')
                    .order('created_at', { ascending: true });
                
                if (error) {
                    console.error('Error loading pods:', error);
                    return;
                }
                
                podsData = data || [];
            } catch (error) {
                console.error('Error in loadPods:', error);
            }
        }
        
        // Load bookings from database for current user
        async function loadBookings() {
            if (!currentUser || !supabase) {
                console.error('User not authenticated or Supabase not initialized');
                return;
            }
            
            try {
                // Load all bookings for the current user
                const { data: bookings, error: bookingsError } = await supabase
                    .from('bookings')
                    .select('id, pod_id, booking_date, check_in_time, check_out_time, number_of_people')
                    .eq('user_id', currentUser.id)
                    .order('booking_date', { ascending: false })
                    .order('check_in_time', { ascending: true });
                
                if (bookingsError) {
                    console.error('Error loading bookings:', bookingsError);
                    // Check if the error is because table doesn't exist
                    var errorMsg = bookingsError.message || bookingsError.toString() || '';
                    if (errorMsg.includes('does not exist') || errorMsg.includes('relation') || errorMsg.includes('42P01')) {
                        console.error('Bookings table does not exist. Please run create_bookings_table.sql in Supabase SQL Editor.');
                        // Don't show alert on page load, just log it - user will see alert when trying to create booking
                    } else {
                        console.error('Error loading bookings:', bookingsError);
                    }
                    bookingsData = [];
                    populateBookingsTable();
                    return;
                }
                
                if (!bookings || bookings.length === 0) {
                    bookingsData = [];
                    populateBookingsTable();
                    return;
                }
                
                // Map bookings with pod data
                bookingsData = bookings.map(booking => {
                    var pod = podsData.find(p => p.id === booking.pod_id);
                    var podName = pod ? (pod.name || 'Pod ' + pod.id) : 'Pod ' + booking.pod_id;
                    
                    // Handle time formats (could be "HH:MM:SS" or "HH:MM" or timestamp)
                    var checkInTime = '';
                    var checkOutTime = '';
                    
                    if (booking.check_in_time) {
                        if (typeof booking.check_in_time === 'string') {
                            // Extract HH:MM from time string
                            checkInTime = booking.check_in_time.substring(0, 5);
                        } else {
                            checkInTime = booking.check_in_time;
                        }
                    }
                    
                    if (booking.check_out_time) {
                        if (typeof booking.check_out_time === 'string') {
                            // Extract HH:MM from time string
                            checkOutTime = booking.check_out_time.substring(0, 5);
                        } else {
                            checkOutTime = booking.check_out_time;
                        }
                    }
                    
                    var duration = calculateDuration(checkInTime, checkOutTime);
                    
                    return {
                        id: booking.id,
                        date: booking.booking_date,
                        room: booking.pod_id,
                        roomName: podName,
                        checkIn: checkInTime,
                        checkOut: checkOutTime,
                        duration: duration,
                        occupants: booking.number_of_people || 1
                    };
                });
                
                // Sort bookings by date and check-in time
                bookingsData.sort(function(a, b) {
                    if (a.date !== b.date) {
                        return a.date.localeCompare(b.date);
                    }
                    return a.checkIn.localeCompare(b.checkIn);
                });
                
                // Populate table with loaded data
                populateBookingsTable();
            } catch (error) {
                console.error('Error in loadBookings:', error);
                bookingsData = [];
                populateBookingsTable();
            }
        }
        
        function getPodCapacity(podId) {
            var pod = podsData.find(p => p.id === podId);
            return pod ? (pod.capacity || 1) : 1;
        }
        
        function populateBookingsTable() {
            var tbody = $('#bookingsTableBody');
            tbody.empty();
            
            bookingsData.forEach(function(booking) {
                var status = getBookingStatus(booking.date, booking.checkIn, booking.checkOut);
                var statusClass = 'status-' + status;
                var statusText = '';
                
                // Format status text using helper function
                statusText = getStatusText(status);
                
                // Check if booking can be cancelled
                var canCancel = canCancelBooking(status);
                var cancelButtonHtml = '';
                
                if (canCancel) {
                    // Booking can be cancelled - show active cancel button
                    cancelButtonHtml = '<button class="btn btn-danger btn-sm btn-delete" onclick="cancelBooking(\'' + booking.id + '\')"><i class="fa fa-times"></i> Cancel</button>';
                } else {
                    // Booking is in progress - show disabled/grayed out button
                    cancelButtonHtml = '<button class="btn btn-danger btn-sm btn-delete disabled" disabled title="Cannot cancel booking that is in progress"><i class="fa fa-times"></i> Cancel</button>';
                }
                
                var row = '<tr data-status="' + status + '" data-date="' + booking.date + '" data-pod="' + booking.room + '" data-booking-id="' + booking.id + '">' +
                    '<td>' + formatDate(booking.date) + '</td>' +
                    '<td>' + booking.roomName + '</td>' +
                    '<td data-order="' + booking.checkIn + '">' + convertTo12Hour(booking.checkIn) + '</td>' +
                    '<td data-order="' + booking.checkOut + '">' + convertTo12Hour(booking.checkOut) + '</td>' +
                    '<td>' + booking.duration + '</td>' +
                    '<td><span class="status-badge ' + statusClass + '">' + statusText + '</span></td>' +
                    '<td>' +
                        '<div class="action-buttons">' +
                            '<button class="btn btn-info btn-sm btn-view" onclick="openBookingModal(\'' + booking.id + '\')"><i class="fa fa-eye"></i> View</button>' +
                            cancelButtonHtml +
                        '</div>' +
                    '</td>' +
                    '</tr>';
                
                tbody.append(row);
            });
        }
        
        function applyFilters() {
            if (!dataTable) {
                console.log('DataTable not initialized yet');
                return;
            }
            
            var statusFilter = $('#filterStatus').val();
            var podFilter = $('#filterPod').val();
            var dateFilter = $('#filterDate').val();
            
            console.log('FILTERING - Status:', statusFilter, '| Pod:', podFilter, '| Date:', dateFilter);
            
            // Show all rows first
            $('#bookingsTableBody tr').show();
            
            // Apply status filter
            if (statusFilter !== 'all') {
                $('#bookingsTableBody tr').each(function() {
                    var rowStatus = $(this).attr('data-status');
                    if (rowStatus !== statusFilter) {
                        $(this).hide();
                    }
                });
            }
            
            // Apply pod filter
            if (podFilter !== 'all') {
                $('#bookingsTableBody tr:visible').each(function() {
                    var rowPod = $(this).attr('data-pod');
                    if (String(rowPod) !== String(podFilter)) {
                        $(this).hide();
                    }
                });
            }
            
            // Apply date filter
            if (dateFilter !== '') {
                $('#bookingsTableBody tr:visible').each(function() {
                    var rowDate = $(this).attr('data-date');
                    if (rowDate !== dateFilter) {
                        $(this).hide();
                    }
                });
            }
            
            var visibleCount = $('#bookingsTableBody tr:visible').length;
            var hiddenCount = $('#bookingsTableBody tr:hidden').length;
            console.log('Results:', visibleCount, 'rows shown,', hiddenCount, 'rows hidden');
            
            // Show/hide "no results" message
            if (visibleCount === 0) {
                $('#noResultsBody').show();
            } else {
                $('#noResultsBody').hide();
            }
        }
        
        function resetFilters() {
            // Reset all filters
            $('#filterStatus').val('all');
            $('#filterPod').val('all');
            $('#filterDate').val('');
            
            // Show all rows and hide no results message
            $('#bookingsTableBody tr').show();
            $('#noResultsBody').hide();
            
            console.log('Filters reset - showing all rows');
        }
        
        // Modal functions
        function openBookingModal(bookingId) {
            var booking = bookingsData.find(b => b.id === bookingId);
            if (!booking) return;
            
            var status = getBookingStatus(booking.date, booking.checkIn, booking.checkOut);
            var statusText = '';
            var statusClass = '';
            
            // Format status text using helper function
            statusText = getStatusText(status);
            statusClass = 'status-' + status;
            
            // Map status to class (handle the occupied status)
            switch(status) {
                case 'upcoming':
                    statusClass = 'status-upcoming';
                    break;
                case 'in-progress-checkin':
                    statusClass = 'status-in-progress-checkin';
                    break;
                case 'in-progress-occupied':
                    statusClass = 'status-in-progress-occupied';
                    break;
                case 'in-progress-checkout':
                    statusClass = 'status-in-progress-checkout';
                    break;
                case 'completed':
                    statusClass = 'status-completed';
                    break;
            }
            
            var podCapacity = getPodCapacity(booking.room);
            
            var modalContent = `
                <div class="booking-details-grid">
                    <div class="booking-detail-row">
                        <label class="detail-label">Booking ID</label>
                        <div class="detail-value">${booking.id}</div>
                    </div>
                    
                    <div class="booking-detail-row">
                        <label class="detail-label">Booking Date</label>
                        <div class="detail-value">${formatDate(booking.date)}</div>
                    </div>
                    
                    <div class="booking-detail-row">
                        <label class="detail-label">Pod Number</label>
                        <div class="detail-value">${booking.roomName}</div>
                    </div>
                    
                    <div class="booking-detail-row">
                        <label class="detail-label">Time Slot</label>
                        <div class="detail-value">${convertTo12Hour(booking.checkIn)} - ${convertTo12Hour(booking.checkOut)}</div>
                    </div>
                    
                    <div class="booking-detail-row">
                        <label class="detail-label">Duration</label>
                        <div class="detail-value">${booking.duration}</div>
                    </div>
                    
                    <div class="booking-detail-row">
                        <label class="detail-label">Number of Occupants</label>
                        <div class="detail-value">${booking.occupants} ${booking.occupants === 1 ? 'Person' : 'People'}</div>
                    </div>
                    
                    <div class="booking-detail-row full-width">
                        <label class="detail-label">Status</label>
                        <div class="detail-value"><span class="status-badge ${statusClass}">${statusText}</span></div>
                    </div>
                </div>
                
                <div class="capacity-info">
                    <i class="fa fa-info-circle"></i>
                    ${booking.roomName} Maximum Capacity: ${podCapacity} People
                </div>
            `;
            
            $('#bookingDetailsContent').html(modalContent);
            $('#bookingModal').addClass('active');
            $('body').css('overflow', 'hidden');
        }
        
        function closeBookingModal() {
            $('#bookingModal').removeClass('active');
            $('body').css('overflow', '');
        }
        
        // Create Booking Modal functions
        function openCreateBookingModal() {
            // Check if user has pending penalties
            if (hasPendingPenalties) {
                alert('⚠️ Cannot Create Booking\n\nYou have unpaid penalties. Please pay your penalties before creating a new booking.\n\nYou can view and pay your penalties in the Penalties section.');
                return;
            }
            
            $('#createBookingModal').addClass('active');
            $('body').css('overflow', 'hidden');
            
            // Set minimum date to today (but don't auto-select it)
            var today = new Date();
            var year = today.getFullYear();
            var month = String(today.getMonth() + 1).padStart(2, '0');
            var day = String(today.getDate()).padStart(2, '0');
            var todayFormatted = year + '-' + month + '-' + day;
            $('#bookingDate').attr('min', todayFormatted);
            
            // Reset form fields (don't auto-select date - let user choose)
            $('#bookingDate').val('');
            $('#checkInTime').val('');
            $('#checkOutTime').prop('disabled', true).html('<option value="">Select check-out time...</option>');
            $('#numberOfPeople').val('');
            $('#peakHourWarning').hide();
            $('#bufferTimeWarning').hide();
            
            // Enable all check-in time options initially (will be updated when date is selected)
            $('#checkInTime option').each(function() {
                $(this).prop('disabled', false);
                $(this).css('color', '');
            });
        }
        
        // Update check-in time options based on selected date (disable past times for today)
        function updateCheckInTimeOptions() {
            var bookingDate = $('#bookingDate').val();
            if (!bookingDate) return;
            
            // Check if booking is for today
            var today = new Date();
            var todayStart = new Date(today);
            todayStart.setHours(0, 0, 0, 0);
            var selectedDate = new Date(bookingDate + 'T00:00:00');
            var isToday = selectedDate.getTime() === todayStart.getTime();
            
            if (isToday) {
                // For today's bookings, disable times that are less than 15 minutes from now
                var now = new Date();
                var currentHour = now.getHours();
                var currentMinute = now.getMinutes();
                var currentTimeInMinutes = currentHour * 60 + currentMinute;
                var bufferMinutes = 15;
                var minCheckInTimeInMinutes = currentTimeInMinutes + bufferMinutes;
                
                // Disable check-in time options that are too soon
                $('#checkInTime option').each(function() {
                    var optionValue = $(this).val();
                    if (optionValue && optionValue !== '') {
                        var timeParts = optionValue.split(':');
                        var optionHour = parseInt(timeParts[0]);
                        var optionMinute = parseInt(timeParts[1]);
                        var optionTimeInMinutes = optionHour * 60 + optionMinute;
                        
                        if (optionTimeInMinutes < minCheckInTimeInMinutes) {
                            $(this).prop('disabled', true);
                            $(this).css('color', '#999');
                        } else {
                            $(this).prop('disabled', false);
                            $(this).css('color', '');
                        }
                    }
                });
            } else {
                // For future dates, enable all options
                $('#checkInTime option').each(function() {
                    $(this).prop('disabled', false);
                    $(this).css('color', '');
                });
            }
        }
        
        function closeCreateBookingModal() {
            $('#createBookingModal').removeClass('active');
            $('body').css('overflow', '');
            resetCreateBookingForm();
        }
        
        function resetCreateBookingForm() {
            // Reset form but preserve the date (will be set to today by openCreateBookingModal)
            var currentDate = $('#bookingDate').val();
            $('#checkInTime').val('');
            $('#checkOutTime').prop('disabled', true).html('<option value="">Select check-out time...</option>');
            $('#numberOfPeople').val('');
            $('#peakHourWarning').hide();
            $('#bufferTimeWarning').hide();
            
            // Restore the date
            if (currentDate) {
                $('#bookingDate').val(currentDate);
            }
        }
        
        // Validate booking times and show buffer warning if needed
        function validateBookingTimes() {
            var bookingDate = $('#bookingDate').val();
            var checkInTime = $('#checkInTime').val();
            
            if (!bookingDate || !checkInTime) {
                $('#bufferTimeWarning').hide();
                return;
            }
            
            // Check if booking is for today
            var today = new Date();
            var todayStart = new Date(today);
            todayStart.setHours(0, 0, 0, 0);
            var selectedDate = new Date(bookingDate + 'T00:00:00');
            var isToday = selectedDate.getTime() === todayStart.getTime();
            
            if (isToday) {
                var now = new Date();
                var currentHour = now.getHours();
                var currentMinute = now.getMinutes();
                var currentTimeInMinutes = currentHour * 60 + currentMinute;
                
                // Parse check-in time
                var checkInParts = checkInTime.split(':');
                var checkInHour = parseInt(checkInParts[0]);
                var checkInMinute = parseInt(checkInParts[1]);
                var checkInTimeInMinutes = checkInHour * 60 + checkInMinute;
                
                // Calculate minimum allowed check-in time (current time + 15 minutes buffer)
                var bufferMinutes = 15;
                var minCheckInTimeInMinutes = currentTimeInMinutes + bufferMinutes;
                
                if (checkInTimeInMinutes < minCheckInTimeInMinutes) {
                    $('#bufferTimeWarning').show();
                } else {
                    $('#bufferTimeWarning').hide();
                }
            } else {
                $('#bufferTimeWarning').hide();
            }
        }
        
        // Convert 24-hour time to 12-hour format for display
        function convertTo12Hour(time24) {
            var parts = time24.split(':');
            var hour = parseInt(parts[0]);
            var minute = parts[1];
            var period = hour >= 12 ? 'PM' : 'AM';
            
            if (hour > 12) {
                hour -= 12;
            } else if (hour === 0) {
                hour = 12;
            }
            
            return hour.toString().padStart(2, '0') + ':' + minute + ' ' + period;
        }
        
        // Update check-out time options based on check-in time
        function updateCheckOutOptions() {
            var checkInTime = $('#checkInTime').val();
            var bookingDate = $('#bookingDate').val();
            
            if (!checkInTime) {
                $('#checkOutTime').prop('disabled', true).html('<option value="">Select check-out time...</option>');
                $('#peakHourWarning').hide();
                return;
            }
            
            // Parse check-in time
            var checkInParts = checkInTime.split(':');
            var checkInHour = parseInt(checkInParts[0]);
            var checkInMinute = parseInt(checkInParts[1]);
            
            // Determine if we're in peak hours (8:00 AM - 5:00 PM)
            var isPeakHours = checkInHour >= 8 && checkInHour < 17;
            
            // Calculate maximum allowed check-out time
            var maxCheckOutHour, maxCheckOutMinute;
            
            if (isPeakHours) {
                // Peak hours: maximum 1 hour
                maxCheckOutHour = checkInHour + 1;
                maxCheckOutMinute = checkInMinute;
                $('#peakHourWarning').show();
            } else {
                // Off-peak: can book until 8:00 PM
                maxCheckOutHour = 20;
                maxCheckOutMinute = 0;
                $('#peakHourWarning').hide();
            }
            
            // Check if booking is for today - apply buffer time restriction for check-out
            var today = new Date();
            var todayStart = new Date(today);
            todayStart.setHours(0, 0, 0, 0);
            var selectedDate = bookingDate ? new Date(bookingDate + 'T00:00:00') : null;
            var isToday = selectedDate && selectedDate.getTime() === todayStart.getTime();
            
            // If booking is for today, ensure check-out is not in the past
            if (isToday) {
                var now = new Date();
                var currentHour = now.getHours();
                var currentMinute = now.getMinutes();
                var currentTimeInMinutes = currentHour * 60 + currentMinute;
                var minCheckOutTimeInMinutes = currentTimeInMinutes + 15; // 15 min buffer
                var minCheckOutHour = Math.floor(minCheckOutTimeInMinutes / 60);
                var minCheckOutMinute = minCheckOutTimeInMinutes % 60;
                
                // Adjust max check-out if needed (but don't go below check-in time)
                var checkOutTimeInMinutes = maxCheckOutHour * 60 + maxCheckOutMinute;
                var checkInTimeInMinutes = checkInHour * 60 + checkInMinute;
                if (checkOutTimeInMinutes < minCheckOutTimeInMinutes && minCheckOutTimeInMinutes > checkInTimeInMinutes) {
                    maxCheckOutHour = minCheckOutHour;
                    maxCheckOutMinute = minCheckOutMinute;
                }
            }
            
            // Generate check-out options
            var checkOutOptions = '<option value="">Select check-out time...</option>';
            var allTimes = [
                '08:00', '08:15', '08:30', '08:45',
                '09:00', '09:15', '09:30', '09:45',
                '10:00', '10:15', '10:30', '10:45',
                '11:00', '11:15', '11:30', '11:45',
                '12:00', '12:15', '12:30', '12:45',
                '13:00', '13:15', '13:30', '13:45',
                '14:00', '14:15', '14:30', '14:45',
                '15:00', '15:15', '15:30', '15:45',
                '16:00', '16:15', '16:30', '16:45',
                '17:00', '17:15', '17:30', '17:45',
                '18:00', '18:15', '18:30', '18:45',
                '19:00', '19:15', '19:30', '19:45',
                '20:00'
            ];
            
            var checkInTimeInMinutes = checkInHour * 60 + checkInMinute;
            var maxCheckOutInMinutes = maxCheckOutHour * 60 + maxCheckOutMinute;
            
            allTimes.forEach(function(time) {
                var timeParts = time.split(':');
                var hour = parseInt(timeParts[0]);
                var minute = parseInt(timeParts[1]);
                var timeInMinutes = hour * 60 + minute;
                
                // Check if this time is after check-in and within allowed range
                if (timeInMinutes > checkInTimeInMinutes && timeInMinutes <= maxCheckOutInMinutes) {
                    checkOutOptions += '<option value="' + time + '">' + convertTo12Hour(time) + '</option>';
                }
            });
            
            $('#checkOutTime').prop('disabled', false).html(checkOutOptions);
        }
        
        // Check pod availability and create booking
        async function checkAvailability() {
            if (!currentUser || !supabase) {
                alert('Please wait for the page to load completely.');
                return;
            }
            
            var bookingDate = $('#bookingDate').val();
            var checkIn = $('#checkInTime').val();
            var checkOut = $('#checkOutTime').val();
            var numberOfPeople = parseInt($('#numberOfPeople').val());
            
            // Validate inputs
            if (!bookingDate || !checkIn || !checkOut || !numberOfPeople) {
                alert('Please fill in all required fields.');
                return;
            }
            
            // Validate booking date is not in the past
            var today = new Date();
            var todayStart = new Date(today);
            todayStart.setHours(0, 0, 0, 0); // Reset time to start of day
            var selectedDate = new Date(bookingDate + 'T00:00:00');
            
            if (selectedDate < todayStart) {
                alert('⚠️ Invalid Date\n\nYou cannot make a booking for a past date.\n\nPlease select today or a future date.');
                return;
            }
            
            // Validate buffer time for today's bookings (15 minutes minimum)
            var isToday = selectedDate.getTime() === todayStart.getTime();
            if (isToday) {
                var now = new Date();
                var currentHour = now.getHours();
                var currentMinute = now.getMinutes();
                var currentTimeInMinutes = currentHour * 60 + currentMinute;
                
                // Parse check-in time
                var checkInParts = checkIn.split(':');
                var checkInHour = parseInt(checkInParts[0]);
                var checkInMinute = parseInt(checkInParts[1]);
                var checkInTimeInMinutes = checkInHour * 60 + checkInMinute;
                
                // Calculate minimum allowed check-in time (current time + 15 minutes buffer)
                var bufferMinutes = 15;
                var minCheckInTimeInMinutes = currentTimeInMinutes + bufferMinutes;
                
                if (checkInTimeInMinutes < minCheckInTimeInMinutes) {
                    // Calculate the minimum allowed time
                    var minHour = Math.floor(minCheckInTimeInMinutes / 60);
                    var minMinute = minCheckInTimeInMinutes % 60;
                    var minTime = String(minHour).padStart(2, '0') + ':' + String(minMinute).padStart(2, '0');
                    
                    alert('⚠️ Buffer Time Required\n\nFor bookings on the same day, you must book at least 15 minutes in advance.\n\nCurrent time: ' + convertTo12Hour(String(currentHour).padStart(2, '0') + ':' + String(currentMinute).padStart(2, '0')) + '\nMinimum check-in time: ' + convertTo12Hour(minTime) + '\n\nPlease select a check-in time that is at least 15 minutes from now.');
                    return;
                }
            }
            
            if (numberOfPeople < 1 || numberOfPeople > 8) {
                alert('Number of people must be between 1 and 8.');
                return;
            }
            
            // Validate check-out is after check-in
            if (checkOut <= checkIn) {
                alert('Check-out time must be after check-in time.');
                return;
            }
            
            try {
                // Check for overlapping bookings for the current user
                const { data: userBookings, error: userBookingsError } = await supabase
                    .from('bookings')
                    .select('id, pod_id, booking_date, check_in_time, check_out_time')
                    .eq('user_id', currentUser.id)
                    .eq('booking_date', bookingDate);
                
                if (userBookingsError) {
                    console.error('Error checking user bookings:', userBookingsError);
                    // Check if the error is because table doesn't exist
                    var errorMsg = userBookingsError.message || userBookingsError.toString() || '';
                    if (errorMsg.includes('does not exist') || errorMsg.includes('relation') || errorMsg.includes('42P01')) {
                        alert('⚠️ Database Setup Required\n\nThe bookings table has not been created yet.\n\nPlease run the SQL script in create_bookings_table.sql in your Supabase SQL Editor to create the table.\n\nSee DATABASE_SETUP.md for detailed instructions.');
                    } else {
                        alert('Error checking existing bookings: ' + (userBookingsError.message || userBookingsError));
                    }
                return;
            }
            
            // Check for duplicate booking (exact same time slot)
                var duplicateBooking = userBookings.find(function(b) {
                    var bCheckIn = b.check_in_time.substring(0, 5);
                    var bCheckOut = b.check_out_time.substring(0, 5);
                    return bCheckIn === checkIn && bCheckOut === checkOut;
            });
            
            if (duplicateBooking) {
                    alert('⚠️ Duplicate Booking\n\nYou already have a booking for this exact time slot.\n\nBooking ID: ' + duplicateBooking.id + '\nDate: ' + formatDate(bookingDate) + '\nTime: ' + convertTo12Hour(checkIn) + ' - ' + convertTo12Hour(checkOut));
                return;
            }
            
            // Check for overlapping bookings (user cannot be in two pods at the same time)
                var overlappingBooking = userBookings.find(function(b) {
                    var bCheckIn = b.check_in_time.substring(0, 5);
                    var bCheckOut = b.check_out_time.substring(0, 5);
                // Overlap occurs if: new checkIn < existing checkOut AND new checkOut > existing checkIn
                    return (checkIn < bCheckOut && checkOut > bCheckIn);
            });
            
            if (overlappingBooking) {
                    var bCheckIn = overlappingBooking.check_in_time.substring(0, 5);
                    var bCheckOut = overlappingBooking.check_out_time.substring(0, 5);
                    alert('⚠️ Overlapping Booking Detected\n\nYou cannot create overlapping bookings.\n\nYou already have a booking:\n\nBooking ID: ' + overlappingBooking.id + '\nPod: Pod ' + overlappingBooking.pod_id + '\nTime: ' + convertTo12Hour(bCheckIn) + ' - ' + convertTo12Hour(bCheckOut) + '\n\nPlease select a different time slot.');
                return;
            }
            
                // Get all bookings for the selected date to check pod availability
                const { data: allBookings, error: allBookingsError } = await supabase
                    .from('bookings')
                    .select('pod_id, check_in_time, check_out_time')
                    .eq('booking_date', bookingDate);
                
                if (allBookingsError) {
                    console.error('Error checking all bookings:', allBookingsError);
                    // Check if the error is because table doesn't exist
                    var errorMsg = allBookingsError.message || allBookingsError.toString() || '';
                    if (errorMsg.includes('does not exist') || errorMsg.includes('relation') || errorMsg.includes('42P01')) {
                        alert('⚠️ Database Setup Required\n\nThe bookings table has not been created yet.\n\nPlease run the SQL script in create_bookings_table.sql in your Supabase SQL Editor to create the table.\n\nSee DATABASE_SETUP.md for detailed instructions.');
            } else {
                        alert('Error checking pod availability: ' + (allBookingsError.message || allBookingsError));
                    }
                    return;
            }
            
                // Find available pods that can fit the number of people
                // This checks ALL pods and finds the best available match
            var availablePods = [];
                var unavailablePods = []; // Track why pods are unavailable for better feedback
                
                console.log('Checking pod availability for ' + numberOfPeople + ' people...');
                console.log('Total pods in system: ' + podsData.length);
                
                // First, find all pods that can fit the number of people and check availability
                podsData.forEach(function(pod) {
                    var podName = pod.name || 'Pod ' + pod.id;
                    
                    // Skip suspended pods
                    if (pod.status === 'suspended') {
                        unavailablePods.push({
                            id: pod.id,
                            name: podName,
                            capacity: pod.capacity,
                            reason: 'Pod is currently suspended'
                        });
                        console.log(podName + ' - REJECTED: Suspended');
                        return;
                    }
                    
                    // Check if pod capacity can fit the number of people
                    if (numberOfPeople > pod.capacity) {
                        unavailablePods.push({
                            id: pod.id,
                            name: podName,
                            capacity: pod.capacity,
                            reason: 'Capacity (' + pod.capacity + ') is less than required (' + numberOfPeople + ')'
                        });
                        console.log(podName + ' - REJECTED: Capacity (' + pod.capacity + ') too small for ' + numberOfPeople + ' people');
                        return;
                    }
                    
                    // Check if pod is available at this time (no overlapping bookings)
                    var conflictingBooking = null;
                    var isAvailable = !allBookings.some(function(b) {
                        if (b.pod_id !== pod.id) {
                        return false;
                    }
                    
                        var bCheckIn = b.check_in_time.substring(0, 5);
                        var bCheckOut = b.check_out_time.substring(0, 5);
                        
                        // Check if times overlap
                        var overlaps = (checkIn < bCheckOut && checkOut > bCheckIn);
                        if (overlaps) {
                            conflictingBooking = {
                                checkIn: bCheckIn,
                                checkOut: bCheckOut
                            };
                        }
                        return overlaps;
                    });
                    
                    if (!isAvailable) {
                        unavailablePods.push({
                            id: pod.id,
                            name: podName,
                            capacity: pod.capacity,
                            reason: 'Already booked from ' + conflictingBooking.checkIn + ' to ' + conflictingBooking.checkOut
                        });
                        console.log(podName + ' - REJECTED: Booking conflict (already booked ' + conflictingBooking.checkIn + ' - ' + conflictingBooking.checkOut + ')');
                        return;
                    }
                    
                    // Pod is available!
                    var podInfo = {
                        id: pod.id,
                        name: podName,
                        capacity: pod.capacity,
                        capacityDifference: Math.abs(pod.capacity - numberOfPeople)
                    };
                    
                    availablePods.push(podInfo);
                    console.log(podName + ' - AVAILABLE: Capacity ' + pod.capacity + ', Difference from required: ' + podInfo.capacityDifference);
                });
                
                console.log('Available pods found: ' + availablePods.length);
                console.log('Unavailable pods: ' + unavailablePods.length);
                
            if (availablePods.length === 0) {
                    // Provide detailed feedback about why no pods are available
                    var message = '⚠️ No Pods Available\n\nSorry, there are no pods available at these times.\n\n';
                    
                    // Check if any pods were rejected due to capacity
                    var capacityRejected = unavailablePods.filter(p => p.reason.includes('Capacity'));
                    if (capacityRejected.length > 0 && capacityRejected.length === unavailablePods.length) {
                        message += 'All pods have insufficient capacity for ' + numberOfPeople + ' people.\n\n';
                        message += 'Please reduce the number of people or select a different time slot.';
                    } else {
                        // Some pods exist but are all booked
                        var bookedPods = unavailablePods.filter(p => p.reason.includes('already booked'));
                        if (bookedPods.length > 0) {
                            message += 'The following pods are already booked during this time:\n';
                            bookedPods.forEach(function(pod, index) {
                                if (index < 3) { // Show first 3
                                    message += '• ' + pod.name + ' (' + pod.reason + ')\n';
                                }
                            });
                            if (bookedPods.length > 3) {
                                message += '• ... and ' + (bookedPods.length - 3) + ' more\n';
                            }
                            message += '\nPlease select a different time slot or date.';
                        } else {
                            message += 'Please select a different time slot or date.';
                        }
                    }
                    
                    alert(message);
                return;
            }
            
                // Sort available pods by capacity difference (closest to number of people first)
                // This ensures we get the best fit, and if the preferred pod is taken, we get the next best
                availablePods.sort(function(a, b) {
                    // Primary sort: capacity difference (closest first)
                    if (a.capacityDifference !== b.capacityDifference) {
                        return a.capacityDifference - b.capacityDifference;
                    }
                    // Secondary sort: if same difference, prefer smaller capacity (more efficient use of space)
                    return a.capacity - b.capacity;
                });
                
            var assignedPod = availablePods[0];
                var wasPreferredPod = assignedPod.capacityDifference === 0; // Exact match
                var alternativeMessage = '';
                
                // If we have multiple available pods, check if the assigned one was not the first choice
                if (availablePods.length > 1 && !wasPreferredPod) {
                    // Check if there was a pod with exact capacity match that was unavailable
                    var exactMatchUnavailable = unavailablePods.find(p => 
                        p.capacity === numberOfPeople && p.reason.includes('already booked')
                    );
                    
                    if (exactMatchUnavailable) {
                        alternativeMessage = '\n\nNote: ' + exactMatchUnavailable.name + ' (perfect capacity match) was already booked, so ' + assignedPod.name + ' was assigned instead.';
                    } else {
                        alternativeMessage = '\n\n' + assignedPod.name + ' was selected as the best available option based on capacity.';
                    }
                } else if (wasPreferredPod) {
                    alternativeMessage = '\n\nPerfect capacity match!';
                }
                
                console.log('Selected pod: ' + assignedPod.name + ' (Capacity: ' + assignedPod.capacity + ', Difference: ' + assignedPod.capacityDifference + ')');
                if (availablePods.length > 1) {
                    console.log('Other available pods considered:');
                    availablePods.slice(1, Math.min(4, availablePods.length)).forEach(function(pod) {
                        console.log('  - ' + pod.name + ' (Capacity: ' + pod.capacity + ', Difference: ' + pod.capacityDifference + ')');
                    });
                }
            
            // Calculate duration
            var duration = calculateDuration(checkIn, checkOut);
            
                // Insert booking into database
                const { data: newBooking, error: insertError } = await supabase
                    .from('bookings')
                    .insert([
                        {
                            user_id: currentUser.id,
                            pod_id: assignedPod.id,
                            booking_date: bookingDate,
                            check_in_time: checkIn + ':00',
                            check_out_time: checkOut + ':00',
                            number_of_people: numberOfPeople
                        }
                    ])
                    .select()
                    .single();
                
                if (insertError) {
                    console.error('Error creating booking:', insertError);
                    // Check if the error is because table doesn't exist
                    var errorMsg = insertError.message || insertError.toString() || '';
                    if (errorMsg.includes('does not exist') || errorMsg.includes('relation') || errorMsg.includes('42P01')) {
                        alert('⚠️ Database Setup Required\n\nThe bookings table has not been created yet.\n\nPlease run the SQL script in create_bookings_table.sql in your Supabase SQL Editor to create the table.\n\nSee DATABASE_SETUP.md for detailed instructions.');
                    } else {
                        alert('Error creating booking: ' + (insertError.message || insertError));
                    }
                    return;
                }
                
                // Send email notification
                try {
                    await sendBookingConfirmationEmail(newBooking, assignedPod, numberOfPeople);
                } catch (emailError) {
                    console.error('Error sending email:', emailError);
                    // Don't fail the booking if email fails, just log it
                }
                
                // Reload bookings
                await loadBookings();
            
            // Refresh the table
            if (dataTable) {
                dataTable.fnDestroy();
                dataTable = null;
            }
            
            populateBookingsTable();
            
            dataTable = $('#dataTables-example').dataTable({
                "order": [[ 2, "asc" ]],
                "paging": false,
                "searching": false,
                "info": false
            });
            
            // Close modal
            closeCreateBookingModal();
            
                // Show success message with pod assignment details
                var successMessage = '✓ Booking Created Successfully!\n\n';
                successMessage += 'Booking ID: ' + newBooking.id + '\n';
                successMessage += 'Date: ' + formatDate(bookingDate) + '\n';
                successMessage += 'Pod: ' + assignedPod.name + ' (Capacity: ' + assignedPod.capacity + ' people)\n';
                successMessage += 'Time: ' + convertTo12Hour(checkIn) + ' - ' + convertTo12Hour(checkOut) + '\n';
                successMessage += 'Occupants: ' + numberOfPeople + ' people';
                
                if (alternativeMessage) {
                    successMessage += alternativeMessage;
                }
                
                successMessage += '\n\nA confirmation email has been sent to your email address.';
                
                alert(successMessage);
            
            console.log('New booking created:', newBooking);
            } catch (error) {
                console.error('Error in checkAvailability:', error);
                alert('An error occurred while creating the booking. Please try again.');
            }
        }
        
        // Send booking confirmation email
        async function sendBookingConfirmationEmail(booking, pod, numberOfPeople) {
            try {
                // Call PHP endpoint to send email
                const response = await fetch('send_booking_email.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        booking_id: booking.id,
                        user_email: currentUser.email,
                        user_name: currentUser.user_metadata?.username || currentUser.email,
                        pod_name: pod.name,
                        booking_date: booking.booking_date,
                        check_in_time: booking.check_in_time.substring(0, 5),
                        check_out_time: booking.check_out_time.substring(0, 5),
                        number_of_people: numberOfPeople
                    })
                });
                
                const result = await response.json();
                if (!response.ok || result.error) {
                    throw new Error(result.error || 'Failed to send email');
                }
                
                return result;
            } catch (error) {
                console.error('Error sending email:', error);
                throw error;
            }
        }
        
        function calculateDuration(checkIn, checkOut) {
            if (!checkIn || !checkOut) return 'N/A';
            
            try {
                var inParts = checkIn.split(':');
                var outParts = checkOut.split(':');
                var inMinutes = parseInt(inParts[0]) * 60 + parseInt(inParts[1]);
                var outMinutes = parseInt(outParts[0]) * 60 + parseInt(outParts[1]);
                var diffMinutes = outMinutes - inMinutes;
                
                if (diffMinutes < 0) return 'N/A';
                
                var hours = Math.floor(diffMinutes / 60);
                var minutes = diffMinutes % 60;
                
                if (hours === 0) {
                    return minutes + ' min';
                } else if (minutes === 0) {
                    return hours + (hours === 1 ? ' hour' : ' hours');
            } else {
                    return hours + (hours === 1 ? ' hour' : ' hours') + ' ' + minutes + ' min';
                }
            } catch (e) {
                return 'N/A';
            }
        }
        
        // Cancel booking function (renamed from deleteBooking)
        async function cancelBooking(bookingId) {
            if (!supabase || !currentUser) {
                alert('Please wait for the page to load completely.');
                return;
            }
            
            var booking = bookingsData.find(b => b.id === bookingId);
            if (!booking) {
                alert('Booking not found.');
                return;
            }
            
            // Check booking status
            var status = getBookingStatus(booking.date, booking.checkIn, booking.checkOut);
            
            // Check if booking can be cancelled (not in progress)
            if (!canCancelBooking(status)) {
                alert('⚠️ Cannot Cancel Booking\n\nThis booking is currently in progress and cannot be cancelled.\n\nStatus: ' + getStatusText(status));
                return;
            }
            
            var now = new Date();
            var checkInDateTime = new Date(booking.date + 'T' + booking.checkIn + ':00');
            
            // Calculate time difference in milliseconds
            var timeDiff = checkInDateTime - now;
            var threeHoursInMs = 3 * 60 * 60 * 1000; // 3 hours in milliseconds
            
            // Check if cancellation charge applies (less than 3 hours before booking)
            var chargeApplies = cancellationChargeApplies(booking.date, booking.checkIn);
            
            // Debug logging
            console.log('Cancellation check:');
            console.log('  Booking date:', booking.date);
            console.log('  Check-in time:', booking.checkIn);
            console.log('  Current time:', new Date().toISOString());
            console.log('  Charge applies:', chargeApplies);
            console.log('  Time until booking (ms):', checkInDateTime - now);
            console.log('  Time until booking (hours):', (checkInDateTime - now) / (60 * 60 * 1000));
            
            var confirmMessage = '';
            var successMessage = '';
            
            if (chargeApplies) {
                // Less than 3 hours before booking - cancellation charge applies
                var hoursUntilBooking = Math.floor(timeDiff / (60 * 60 * 1000));
                var minutesUntilBooking = Math.floor((timeDiff % (60 * 60 * 1000)) / (60 * 1000));
                var timeUntilBooking = '';
                if (hoursUntilBooking > 0) {
                    timeUntilBooking = hoursUntilBooking + ' hour' + (hoursUntilBooking > 1 ? 's' : '') + ' and ' + minutesUntilBooking + ' minute' + (minutesUntilBooking !== 1 ? 's' : '');
            } else {
                    timeUntilBooking = minutesUntilBooking + ' minute' + (minutesUntilBooking !== 1 ? 's' : '');
                }
                
                confirmMessage = '⚠️ Cancel Booking?\n\nYou are cancelling this booking less than 3 hours before it starts.\n\nTime until booking: ' + timeUntilBooking + '\n\n⚠️ Cancellation charges will apply.\n\nDo you want to proceed?';
                successMessage = '✓ Booking Cancelled\n\nYour booking has been cancelled.\n\n⚠️ Cancellation charges apply as the booking was cancelled less than 3 hours before the scheduled time.';
            } else {
                // More than 3 hours before booking - no charge
                var hoursUntilBooking = Math.floor(timeDiff / (60 * 60 * 1000));
                var minutesUntilBooking = Math.floor((timeDiff % (60 * 60 * 1000)) / (60 * 1000));
                var timeUntilBooking = '';
                if (hoursUntilBooking > 0) {
                    timeUntilBooking = hoursUntilBooking + ' hour' + (hoursUntilBooking > 1 ? 's' : '') + ' and ' + minutesUntilBooking + ' minute' + (minutesUntilBooking !== 1 ? 's' : '');
                } else {
                    timeUntilBooking = minutesUntilBooking + ' minute' + (minutesUntilBooking !== 1 ? 's' : '');
                }
                
                confirmMessage = 'Cancel Booking?\n\nAre you sure you want to cancel this booking?\n\nBooking ID: ' + bookingId + '\nTime until booking: ' + timeUntilBooking + '\n\nNo cancellation charges will apply.';
                successMessage = '✓ Booking Cancelled\n\nYour booking has been successfully cancelled.\n\nNo cancellation charges apply.';
            }
            
            // Show confirmation dialog
            if (confirm(confirmMessage)) {
                try {
                    // If cancellation charge applies, create a penalty record BEFORE deleting the booking
                    if (chargeApplies) {
                        console.log('=== CREATING PENALTY ===');
                        console.log('Charge applies: TRUE - proceeding with penalty creation');
                        try {
                            // Get pod ID from booking (booking.room is the pod_id)
                            var podId = booking.room;
                            
                            if (!podId) {
                                console.error('ERROR: Pod ID is missing from booking:', booking);
                                alert('⚠️ Error: Could not determine pod for penalty. Booking cancelled but penalty not created.');
                            } else {
                                // Get penalty amount from database (current rate)
                                // The backend will use the database rate anyway, but we fetch it here for display
                                var penaltyAmount = penaltyRates['Late Cancellation'] || 10.00; // Fallback to 10.00 if not loaded
                                
                                // Get current date and time for violation
                                var now = new Date();
                                var violationDate = now.toISOString().split('T')[0]; // YYYY-MM-DD
                                var violationTime = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0') + ':00'; // HH:MM:SS
                                
                                // Create penalty via PHP endpoint (uses service key)
                                // Note: The backend (create_penalty.php) will ALWAYS use the current rate from the database
                                // This ensures new penalties use the latest rate, while old penalties retain their original rate
                                const penaltyData = {
                                    user_id: currentUser.id,
                                    booking_id: bookingId, // Store the booking ID before it's deleted
                                    pod_id: podId,
                                    violation_type: 'Late Cancellation',
                                    penalty_amount: penaltyAmount, // This will be overridden by backend with database rate
                                    violation_date: violationDate,
                                    violation_time: violationTime
                                };
                                
                                // Log the penalty data for debugging
                                console.log('Penalty data to send:', JSON.stringify(penaltyData, null, 2));
                                console.log('User ID:', currentUser.id);
                                console.log('Booking ID:', bookingId);
                                console.log('Pod ID:', podId);
                                console.log('Penalty rates loaded:', penaltyRates);
                                console.log('Calling create_penalty.php...');
                                
                                const penaltyResponse = await fetch('create_penalty.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify(penaltyData)
                                });
                                
                                console.log('Penalty API response status:', penaltyResponse.status, penaltyResponse.statusText);
                                
                                // Get response text first (can only read once)
                                const responseText = await penaltyResponse.text();
                                console.log('Penalty API response text:', responseText);
                                
                                if (!penaltyResponse.ok) {
                                    console.error('ERROR: Failed to create penalty - HTTP Status:', penaltyResponse.status);
                                    console.error('Error response:', responseText);
                                    try {
                                        const penaltyResult = JSON.parse(responseText);
                                        const errorMsg = penaltyResult.error || penaltyResult.details || 'Unknown error';
                                        console.error('Error details:', penaltyResult);
                                        
                                        // Check if it's a schema error
                                        if (errorMsg.includes('booking_id') || errorMsg.includes('schema') || errorMsg.includes('column missing')) {
                                            alert('⚠️ Database Schema Error\n\n' + 
                                                'The penalties table is missing the booking_id column.\n\n' +
                                                'Please contact your administrator to run:\n' +
                                                'add_booking_id_to_penalties.sql\n\n' +
                                                'Booking has been cancelled, but penalty could not be recorded.');
                                        } else {
                                            alert('⚠️ Warning: Booking cancelled but penalty could not be created: ' + errorMsg);
                                        }
                                    } catch (e) {
                                        console.error('Could not parse error response as JSON:', responseText);
                                        alert('⚠️ Warning: Booking cancelled but penalty could not be created. Please check server logs.');
                                    }
                                    // Don't fail the cancellation if penalty creation fails, just log it
                                    console.warn('Booking will be cancelled but penalty could not be created. Please contact support.');
                                    // Continue with cancellation anyway
                                } else {
                                    try {
                                        const penaltyResult = JSON.parse(responseText);
                                        if (penaltyResult.error) {
                                            console.error('ERROR: Penalty creation returned error:', penaltyResult.error);
                                            console.error('Penalty response data:', penaltyResult);
                                            
                                            // Check if it's a schema error even in "success" response
                                            if (penaltyResult.error.includes('booking_id') || penaltyResult.error.includes('schema')) {
                                                alert('⚠️ Database Schema Error\n\n' + 
                                                    'The penalties table is missing the booking_id column.\n\n' +
                                                    'Please contact your administrator to run:\n' +
                                                    'add_booking_id_to_penalties.sql\n\n' +
                                                    'Booking has been cancelled, but penalty could not be recorded.');
                                            } else {
                                                alert('⚠️ Warning: Booking cancelled but penalty could not be created: ' + penaltyResult.error);
                                            }
                                        } else if (penaltyResult.success && penaltyResult.penalty) {
                                            console.log('SUCCESS: Penalty created successfully');
                                            console.log('Penalty ID:', penaltyResult.penalty.id);
                                            console.log('Penalty booking_id:', penaltyResult.penalty.booking_id);
                                            console.log('Penalty amount:', penaltyResult.penalty.penalty_amount);
                                            console.log('Penalty rate_id:', penaltyResult.penalty.rate_id);
                                            // Update success message to mention penalty
                                            successMessage += '\n\n⚠️ A penalty of $' + (penaltyResult.penalty.penalty_amount || penaltyAmount).toFixed(2) + ' has been added to your account. Please check your penalties page to view and pay it.';
                                        } else {
                                            console.warn('WARNING: Unexpected penalty response format:', penaltyResult);
                                            console.log('Response:', responseText);
                                        }
                                    } catch (e) {
                                        console.error('ERROR: Could not parse penalty response as JSON:', e);
                                        console.error('Response text:', responseText);
                                        alert('⚠️ Warning: Booking cancelled but could not verify penalty creation. Please check your penalties page.');
                                    }
                                }
                            }
                        } catch (penaltyError) {
                            console.error('ERROR: Exception creating penalty:', penaltyError);
                            console.error('Penalty error stack:', penaltyError.stack);
                            // Don't fail the cancellation if penalty creation fails, just log it
                            console.warn('Booking will be cancelled but penalty could not be created. Please contact support.');
                            alert('⚠️ Warning: Booking cancelled but penalty could not be created. Please contact support.');
                            // Continue with cancellation anyway
                        }
                    } else {
                        console.log('No penalty needed - cancellation charge does not apply');
                    }
                    
                    // Delete booking from database
                    const { error: deleteError } = await supabase
                        .from('bookings')
                        .delete()
                        .eq('id', bookingId)
                        .eq('user_id', currentUser.id); // Ensure user can only cancel their own bookings
                    
                    if (deleteError) {
                        console.error('Error cancelling booking:', deleteError);
                        // Check if the error is because table doesn't exist
                        var errorMsg = deleteError.message || deleteError.toString() || '';
                        if (errorMsg.includes('does not exist') || errorMsg.includes('relation') || errorMsg.includes('42P01')) {
                            alert('⚠️ Database Setup Required\n\nThe bookings table has not been created yet.\n\nPlease run the SQL script in create_bookings_table.sql in your Supabase SQL Editor to create the table.\n\nSee DATABASE_SETUP.md for detailed instructions.');
                        } else {
                            alert('Error cancelling booking: ' + (deleteError.message || deleteError));
                        }
                        return;
                    }
                    
                    // Reload bookings
                    await loadBookings();
                    
                    // Destroy DataTable first
                    if (dataTable) {
                        dataTable.fnDestroy();
                        dataTable = null;
                    }
                    
                    // Refresh the table with new data
                    populateBookingsTable();
                    
                    // Reinitialize DataTable
                    dataTable = $('#dataTables-example').dataTable({
                        "order": [[ 2, "asc" ]],
                        "paging": false,
                        "searching": false,
                        "info": false
                    });
                    
                    // Get current filter values
                    var statusFilter = $('#filterStatus').val();
                    var podFilter = $('#filterPod').val();
                    var dateFilter = $('#filterDate').val();
                    
                    // Only reapply filters if any filter is active
                    if (statusFilter !== 'all' || podFilter !== 'all' || dateFilter !== '') {
                        applyFilters();
                    }
                    
                    // Show success message
                    alert(successMessage);
                    
                    console.log('Booking ' + bookingId + ' cancelled successfully.');
                } catch (error) {
                    console.error('Error in cancelBooking:', error);
                    alert('An error occurred while cancelling the booking. Please try again.');
                }
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
            
            // Set minimum date to today to disable past dates in calendar picker
            var today = new Date();
            var year = today.getFullYear();
            var month = String(today.getMonth() + 1).padStart(2, '0');
            var day = String(today.getDate()).padStart(2, '0');
            var todayFormatted = year + '-' + month + '-' + day;
            $('#bookingDate').attr('min', todayFormatted);
            
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
            
            // Close booking modal when clicking outside
            $(document).on('click', '#bookingModal', function(e) {
                if (e.target.id === 'bookingModal') {
                    closeBookingModal();
                }
            });
            
            // Close booking modal with Escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('#bookingModal').hasClass('active')) {
                    closeBookingModal();
                }
                if (e.key === 'Escape' && $('#createBookingModal').hasClass('active')) {
                    closeCreateBookingModal();
                }
            });
            
            // Close create booking modal when clicking outside
            $(document).on('click', '#createBookingModal', function(e) {
                if (e.target.id === 'createBookingModal') {
                    closeCreateBookingModal();
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