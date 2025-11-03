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
    <!-- Morris Charts CSS -->
    <link href="assets/js/morris/morris-0.4.3.min.css" rel="stylesheet" />
    <!-- html2pdf library for PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body::-webkit-scrollbar {
            display: none;
        }
        
        /* Dashboard Cards */
        .dashboard-cards {
            margin-bottom: 30px;
        }
        .stat-card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border-left: 4px solid;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .stat-card.occupancy {
            border-left-color: #5cb85c;
        }
        .stat-card.bookings {
            border-left-color: #5bc0de;
        }
        .stat-card.penalties {
            border-left-color: #f0ad4e;
        }
        .stat-card.room-status {
            border-left-color: #d9534f;
        }
        .stat-card h3 {
            margin: 0;
            font-size: 16px;
            color: #666;
            font-weight: 600;
        }
        .stat-card .stat-value {
            font-size: 36px;
            font-weight: bold;
            margin: 10px 0;
            color: #333;
        }
        .stat-card .stat-icon {
            float: right;
            font-size: 48px;
            opacity: 0.3;
        }
        .stat-card .stat-label {
            font-size: 14px;
            color: #999;
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
        .status-upcoming {
            background-color: #d9edf7;
            color: #31708f;
        }
        .status-ongoing {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .status-completed {
            background-color: #f5f5f5;
            color: #777;
        }
        
        /* Label Badges */
        .label {
            display: inline-block;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 600;
            line-height: 1;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 3px;
        }
        .label-info {
            background-color:rgb(13, 98, 124);
        }
        .label-warning {
            background-color: #f0ad4e;
        }
        .label-success {
            background-color: #5cb85c;
        }
        .label-danger {
            background-color: #d9534f;
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
            min-height: calc(100vh - 60px);
            position: relative;
        }
        
        #wrapper {
            width: 100%;
            overflow-x: hidden;
        }
        
        body {
            overflow-x: hidden;
        }
        
        /* Mobile and Tablet Responsive */
        /* Print Button Hover Effect */
        .btn-primary:hover {
            background-color: #0056b3 !important;
            box-shadow: 0 4px 8px rgba(0,123,255,0.4) !important;
            transform: translateY(-2px);
        }
        
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
                        <a class="active-menu" href="analytics.php"><i class="fa fa-bar-chart-o fa-fw"></i> Analytics</a>
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
                            <h1 style="margin: 0; font-size: 26px; font-weight: 400; color: #5a5a5a;">SYSTEM ANALYTICS</h1>
                            <div>
                                <button class="btn btn-primary" onclick="printAnalytics()" style="background-color: #007bff; border: none; border-radius: 5px; padding: 10px 20px; font-size: 14px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,123,255,0.3); transition: all 0.3s ease;">
                                    <i class="fa fa-print" style="margin-right: 8px;"></i> Print
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /. ROW  -->
                
                <!-- Filter Controls -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class="filter-controls">
                                    <div>
                                        <label for="filterByType">Filter By Type:</label>
                                        <select id="filterByType" class="form-control" style="display: inline-block; width: 160px;">
                                            <option value="" selected>Please Select</option>
                                            <option value="both">Both</option>
                                            <option value="bookings">Bookings Only</option>
                                            <option value="penalties">Penalties Only</option>
                                        </select>
                        </div>
                                    <div>
                                        <label for="filterByParameter">Filter By Parameter:</label>
                                        <select id="filterByParameter" class="form-control" style="display: inline-block; width: 160px;">
                                            <option value="" selected>Please Select</option>
                                            <option value="date">Date Range</option>
                                            <option value="month">Month</option>
                                            <option value="quarterly">Quarterly</option>
                                        </select>
                    </div>
                                    <div id="dynamicFilterContainer">
                                        <!-- Dynamic filter will be inserted here -->
                        </div>
                                    <button class="btn btn-sm btn-primary" onclick="applyAnalyticsFilters()">
                                        <i class="fa fa-filter"></i> Apply Filters
                                    </button>
                                    <button class="btn btn-sm btn-default" onclick="resetAnalyticsFilters()">
                                        <i class="fa fa-refresh"></i> Reset
                                    </button>
                    </div>
                        </div>
                    </div>
                        </div>
                    </div>
                <!-- /. Filter Controls -->
                
                <!-- Analytics Chart -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4><i class="fa fa-bar-chart-o"></i> Bookings Made vs Penalties Issued</h4>
                            </div>
                            <div class="panel-body">
                                <div id="analytics-chart" style="height: 400px; display: none;"></div>
                                <div id="chart-legend" style="display: none; text-align: center; margin-top: 20px; padding: 15px; background-color: #f9f9f9; border-top: 1px solid #e3e3e3;">
                                    <div style="display: inline-block;">
                                        <span id="legend-bookings" style="display: inline-flex; align-items: center; margin-right: 25px;">
                                            <span style="display: inline-block; width: 20px; height: 20px; background-color: #5cb85c; margin-right: 8px; border-radius: 3px;"></span>
                                            <span style="font-weight: 600; color: #333;">Bookings Made</span>
                                        </span>
                                        <span id="legend-penalties" style="display: inline-flex; align-items: center;">
                                            <span style="display: inline-block; width: 20px; height: 20px; background-color: #d9534f; margin-right: 8px; border-radius: 3px;"></span>
                                            <span style="font-weight: 600; color: #333;">Penalties Issued</span>
                                        </span>
                                    </div>
                                    </div>
                                <div id="no-chart-data" style="display: none; text-align: center; padding: 100px 20px;">
                                    <i class="fa fa-bar-chart-o" style="font-size: 72px; color: #ddd; margin-bottom: 20px;"></i>
                                    <h3 style="color: #999; font-weight: 400;">No Data Found</h3>
                                    <p style="color: #bbb;">There are no bookings or penalties recorded for the selected period.</p>
                                    </div>
                                <div id="select-prompt-chart" style="text-align: center; padding: 100px 20px;">
                                    <i class="fa fa-filter" style="font-size: 72px; color: #ddd; margin-bottom: 20px;"></i>
                                    <h3 style="color: #999; font-weight: 400;">Please Select Filters</h3>
                                    <p style="color: #bbb;">Select type of data and parameter above, then click "Apply Filters" to view analytics.</p>
                                    </div>
                                </div>
                                    </div>
                                    </div>
                                </div>
                <!-- /. Analytics Chart -->
                
                <div class="row">
                    <div class="col-md-12">
                        <!-- Combined Data Table -->
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4>Bookings and Penalties Records</h4>
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Date</th>
                                                <th>Details</th>
                                            </tr>
                                        </thead>
                                        <tbody id="recordsTableBody">
                                            <!-- Combined records will be populated here -->
                                        </tbody>
                                        <tbody id="noResultsBody" style="display: none;">
                                            <tr>
                                                <td colspan="3" style="text-align: center; padding: 40px; color: #999; font-size: 16px;">
                                                    <i class="fa fa-inbox" style="font-size: 48px; display: block; margin-bottom: 15px; opacity: 0.3;"></i>
                                                    <strong>No Data Found</strong><br>
                                                    There are no records for the selected period.
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tbody id="selectPromptBody">
                                            <tr>
                                                <td colspan="3" style="text-align: center; padding: 40px; color: #999; font-size: 16px;">
                                                    <i class="fa fa-filter" style="font-size: 48px; display: block; margin-bottom: 15px; opacity: 0.3;"></i>
                                                    <strong>Please Select Filters</strong><br>
                                                    Select type of data and parameter above, then click "Apply Filters" to view records.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!--End Combined Table -->
                    </div>
                </div>
                <!-- /. ROW  -->
            </div>
        </div>
        <!-- /. PAGE WRAPPER  -->
    </div>
    <!-- /. WRAPPER  -->
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
    <!-- Morris Charts -->
    <script src="assets/js/morris/raphael-2.1.0.min.js"></script>
    <script src="assets/js/morris/morris.js"></script>
    <script>
        var dataTable;
        var morrisChart;
        
        // Combined records data (bookings and penalties) - Spread across 2024-2025
        // Note: Penalties can only exist with bookings (a user must book first to get a penalty)
        var allRecords = [
            // === 2024 DATA (Fluctuating amounts per month) ===
            
            // January 2024 - 8 bookings, 2 penalties
            { type: 'booking', username: 'johnsmith', date: '2024-01-05', details: '08:30 - 09:30 (1 hour)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'emmajohnson', date: '2024-01-08', details: '10:15 - 11:15 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'michaelbrown', date: '2024-01-12', details: '13:15 - 14:15 (1 hour)', pod: '2', status: 'completed' },
            { type: 'penalty', username: 'michaelbrown', date: '2024-01-12', details: 'Late cancellation', pod: '2', status: 'paid' },
            { type: 'booking', username: 'sarahdavis', date: '2024-01-15', details: '09:15 - 10:15 (1 hour)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'jameswalker', date: '2024-01-18', details: '14:00 - 15:30 (1.5 hours)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'mariagarcia', date: '2024-01-22', details: '10:30 - 11:30 (1 hour)', pod: '3', status: 'completed' },
            { type: 'penalty', username: 'mariagarcia', date: '2024-01-22', details: 'Overstayed booking', pod: '3', status: 'paid' },
            { type: 'booking', username: 'danielharris', date: '2024-01-25', details: '16:00 - 17:00 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'lisaanderson', date: '2024-01-28', details: '17:00 - 20:00 (3 hours)', pod: '3', status: 'completed' },
            
            // February 2024 - 5 bookings, 1 penalty
            { type: 'booking', username: 'williammoore', date: '2024-02-03', details: '11:15 - 12:15 (1 hour)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'kevinmartin', date: '2024-02-10', details: '09:00 - 10:00 (1 hour)', pod: '3', status: 'completed' },
            { type: 'booking', username: 'patriciawhite', date: '2024-02-14', details: '15:00 - 16:30 (1.5 hours)', pod: '4', status: 'completed' },
            { type: 'penalty', username: 'patriciawhite', date: '2024-02-14', details: 'No-show', pod: '4', status: 'paid' },
            { type: 'booking', username: 'robertlee', date: '2024-02-20', details: '08:00 - 09:00 (1 hour)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'lindagreen', date: '2024-02-25', details: '12:00 - 13:00 (1 hour)', pod: '2', status: 'completed' },
            
            // March 2024 - 12 bookings, 3 penalties
            { type: 'booking', username: 'charlesking', date: '2024-03-02', details: '14:30 - 16:00 (1.5 hours)', pod: '3', status: 'completed' },
            { type: 'booking', username: 'barbarahill', date: '2024-03-05', details: '10:00 - 11:30 (1.5 hours)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'josephwright', date: '2024-03-08', details: '16:00 - 17:00 (1 hour)', pod: '4', status: 'completed' },
            { type: 'penalty', username: 'josephwright', date: '2024-03-08', details: 'Late cancellation', pod: '4', status: 'paid' },
            { type: 'booking', username: 'nancyscott', date: '2024-03-11', details: '09:30 - 10:30 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'thomasmoore', date: '2024-03-14', details: '13:00 - 15:00 (2 hours)', pod: '3', status: 'completed' },
            { type: 'booking', username: 'susanharris', date: '2024-03-17', details: '11:00 - 12:00 (1 hour)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'davidclark', date: '2024-03-20', details: '08:30 - 09:30 (1 hour)', pod: '4', status: 'completed' },
            { type: 'penalty', username: 'davidclark', date: '2024-03-20', details: 'Overstayed booking', pod: '4', status: 'pending' },
            { type: 'booking', username: 'jenniferlopez', date: '2024-03-23', details: '15:00 - 16:00 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'christophermartinez', date: '2024-03-26', details: '10:00 - 11:30 (1.5 hours)', pod: '3', status: 'completed' },
            { type: 'booking', username: 'matthewgarcia', date: '2024-03-28', details: '14:00 - 15:00 (1 hour)', pod: '1', status: 'completed' },
            { type: 'penalty', username: 'matthewgarcia', date: '2024-03-28', details: 'No-show', pod: '1', status: 'paid' },
            { type: 'booking', username: 'elizabethrodriguez', date: '2024-03-30', details: '09:00 - 10:00 (1 hour)', pod: '4', status: 'completed' },
            
            // April 2024 - 7 bookings, 2 penalties
            { type: 'booking', username: 'anthonywilson', date: '2024-04-03', details: '08:00 - 09:30 (1.5 hours)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'dorothyanderson', date: '2024-04-08', details: '11:00 - 12:00 (1 hour)', pod: '2', status: 'completed' },
            { type: 'penalty', username: 'dorothyanderson', date: '2024-04-08', details: 'Late cancellation', pod: '2', status: 'paid' },
            { type: 'booking', username: 'paulthomas', date: '2024-04-12', details: '14:00 - 15:30 (1.5 hours)', pod: '3', status: 'completed' },
            { type: 'booking', username: 'helentaylor', date: '2024-04-16', details: '10:00 - 11:00 (1 hour)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'markjackson', date: '2024-04-20', details: '09:00 - 10:30 (1.5 hours)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'bettywhite', date: '2024-04-24', details: '13:00 - 14:00 (1 hour)', pod: '2', status: 'completed' },
            { type: 'penalty', username: 'bettywhite', date: '2024-04-24', details: 'Overstayed booking', pod: '2', status: 'pending' },
            { type: 'booking', username: 'stevenharris', date: '2024-04-28', details: '15:30 - 17:00 (1.5 hours)', pod: '3', status: 'completed' },
            
            // May 2024 - 10 bookings, 1 penalty
            { type: 'booking', username: 'ruthmartin', date: '2024-05-02', details: '08:30 - 09:30 (1 hour)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'edwardthompson', date: '2024-05-05', details: '11:30 - 13:00 (1.5 hours)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'karenmoore', date: '2024-05-08', details: '14:00 - 15:00 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'brianwalker', date: '2024-05-11', details: '09:00 - 10:00 (1 hour)', pod: '3', status: 'completed' },
            { type: 'booking', username: 'carolallen', date: '2024-05-14', details: '16:00 - 17:30 (1.5 hours)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'ronaldyoung', date: '2024-05-17', details: '10:00 - 11:30 (1.5 hours)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'sandrahernandez', date: '2024-05-20', details: '13:30 - 14:30 (1 hour)', pod: '2', status: 'completed' },
            { type: 'penalty', username: 'sandrahernandez', date: '2024-05-20', details: 'No-show', pod: '2', status: 'paid' },
            { type: 'booking', username: 'kennethjones', date: '2024-05-23', details: '08:00 - 09:00 (1 hour)', pod: '3', status: 'completed' },
            { type: 'booking', username: 'donnamiller', date: '2024-05-26', details: '15:00 - 16:30 (1.5 hours)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'kevindavis', date: '2024-05-29', details: '11:00 - 12:30 (1.5 hours)', pod: '1', status: 'completed' },
            
            // June 2024 - 6 bookings, 3 penalties
            { type: 'booking', username: 'laurawilson', date: '2024-06-04', details: '09:30 - 10:30 (1 hour)', pod: '2', status: 'completed' },
            { type: 'penalty', username: 'laurawilson', date: '2024-06-04', details: 'Late cancellation', pod: '2', status: 'paid' },
            { type: 'booking', username: 'jasonbrown', date: '2024-06-10', details: '14:00 - 15:00 (1 hour)', pod: '3', status: 'completed' },
            { type: 'penalty', username: 'jasonbrown', date: '2024-06-10', details: 'Overstayed booking', pod: '3', status: 'pending' },
            { type: 'booking', username: 'sarahlee', date: '2024-06-15', details: '10:00 - 11:00 (1 hour)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'jeffreymartin', date: '2024-06-20', details: '12:00 - 13:30 (1.5 hours)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'michellegarcia', date: '2024-06-25', details: '08:30 - 09:30 (1 hour)', pod: '2', status: 'completed' },
            { type: 'penalty', username: 'michellegarcia', date: '2024-06-25', details: 'No-show', pod: '2', status: 'paid' },
            { type: 'booking', username: 'ryanrodriguez', date: '2024-06-28', details: '15:00 - 16:00 (1 hour)', pod: '3', status: 'completed' },
            
            // July 2024 - 9 bookings, 2 penalties
            { type: 'booking', username: 'ashleymartinez', date: '2024-07-02', details: '11:00 - 12:30 (1.5 hours)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'timothyhernandez', date: '2024-07-06', details: '08:00 - 09:00 (1 hour)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'rachellopez', date: '2024-07-10', details: '13:00 - 14:30 (1.5 hours)', pod: '2', status: 'completed' },
            { type: 'penalty', username: 'rachellopez', date: '2024-07-10', details: 'Late cancellation', pod: '2', status: 'paid' },
            { type: 'booking', username: 'adamgonzalez', date: '2024-07-14', details: '10:00 - 11:00 (1 hour)', pod: '3', status: 'completed' },
            { type: 'booking', username: 'melissaperez', date: '2024-07-18', details: '15:00 - 16:00 (1 hour)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'jacoblewis', date: '2024-07-22', details: '09:00 - 10:30 (1.5 hours)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'amberrobinson', date: '2024-07-25', details: '12:00 - 13:00 (1 hour)', pod: '2', status: 'completed' },
            { type: 'penalty', username: 'amberrobinson', date: '2024-07-25', details: 'Overstayed booking', pod: '2', status: 'pending' },
            { type: 'booking', username: 'ethanhall', date: '2024-07-28', details: '14:00 - 15:30 (1.5 hours)', pod: '3', status: 'completed' },
            { type: 'booking', username: 'rebeccaturner', date: '2024-07-31', details: '10:30 - 11:30 (1 hour)', pod: '4', status: 'completed' },
            
            // August 2024 - 11 bookings, 4 penalties  
            { type: 'booking', username: 'nathanyoung', date: '2024-08-03', details: '08:00 - 09:30 (1.5 hours)', pod: '1', status: 'completed' },
            { type: 'penalty', username: 'nathanyoung', date: '2024-08-03', details: 'No-show', pod: '1', status: 'paid' },
            { type: 'booking', username: 'nicolescott', date: '2024-08-06', details: '11:00 - 12:00 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'jacksongreen', date: '2024-08-09', details: '14:00 - 15:00 (1 hour)', pod: '3', status: 'completed' },
            { type: 'penalty', username: 'jacksongreen', date: '2024-08-09', details: 'Late cancellation', pod: '3', status: 'paid' },
            { type: 'booking', username: 'hannahbaker', date: '2024-08-12', details: '10:00 - 11:30 (1.5 hours)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'isaacnelson', date: '2024-08-15', details: '15:00 - 16:00 (1 hour)', pod: '1', status: 'completed' },
            { type: 'penalty', username: 'isaacnelson', date: '2024-08-15', details: 'Overstayed booking', pod: '1', status: 'pending' },
            { type: 'booking', username: 'gracecarter', date: '2024-08-18', details: '09:00 - 10:00 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'lucasmitchell', date: '2024-08-21', details: '12:30 - 14:00 (1.5 hours)', pod: '3', status: 'completed' },
            { type: 'booking', username: 'avaroberts', date: '2024-08-24', details: '08:30 - 09:30 (1 hour)', pod: '4', status: 'completed' },
            { type: 'penalty', username: 'avaroberts', date: '2024-08-24', details: 'No-show', pod: '4', status: 'paid' },
            { type: 'booking', username: 'henryedwards', date: '2024-08-27', details: '13:00 - 14:30 (1.5 hours)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'sophiacollins', date: '2024-08-30', details: '11:00 - 12:00 (1 hour)', pod: '2', status: 'completed' },
            
            // September 2024 - 8 bookings, 1 penalty
            { type: 'booking', username: 'owenmorris', date: '2024-09-02', details: '14:00 - 15:30 (1.5 hours)', pod: '3', status: 'completed' },
            { type: 'booking', username: 'emilyphillips', date: '2024-09-06', details: '09:30 - 10:30 (1 hour)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'noahbell', date: '2024-09-10', details: '12:00 - 13:00 (1 hour)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'oliviacook', date: '2024-09-14', details: '15:00 - 16:30 (1.5 hours)', pod: '2', status: 'completed' },
            { type: 'penalty', username: 'oliviacook', date: '2024-09-14', details: 'Late cancellation', pod: '2', status: 'paid' },
            { type: 'booking', username: 'liamward', date: '2024-09-18', details: '10:00 - 11:00 (1 hour)', pod: '3', status: 'completed' },
            { type: 'booking', username: 'isabellahughes', date: '2024-09-22', details: '08:00 - 09:30 (1.5 hours)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'masoncooper', date: '2024-09-26', details: '13:30 - 14:30 (1 hour)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'charlotterivera', date: '2024-09-29', details: '11:30 - 12:30 (1 hour)', pod: '2', status: 'completed' },
            
            // October 2024 - 6 bookings, 2 penalties
            { type: 'booking', username: 'elijahgray', date: '2024-10-04', details: '09:00 - 10:00 (1 hour)', pod: '3', status: 'completed' },
            { type: 'penalty', username: 'elijahgray', date: '2024-10-04', details: 'Overstayed booking', pod: '3', status: 'pending' },
            { type: 'booking', username: 'ameliajames', date: '2024-10-09', details: '14:00 - 15:30 (1.5 hours)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'jamesdiaz', date: '2024-10-14', details: '10:30 - 11:30 (1 hour)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'miabutler', date: '2024-10-19', details: '13:00 - 14:00 (1 hour)', pod: '2', status: 'completed' },
            { type: 'penalty', username: 'miabutler', date: '2024-10-19', details: 'No-show', pod: '2', status: 'paid' },
            { type: 'booking', username: 'benjaminlong', date: '2024-10-24', details: '08:30 - 09:30 (1 hour)', pod: '3', status: 'completed' },
            { type: 'booking', username: 'harperwood', date: '2024-10-29', details: '15:00 - 16:00 (1 hour)', pod: '4', status: 'completed' },
            
            // November 2024 - 7 bookings, 1 penalty
            { type: 'booking', username: 'alexanderfoster', date: '2024-11-03', details: '11:00 - 12:30 (1.5 hours)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'evalynbarnes', date: '2024-11-07', details: '09:00 - 10:00 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'sebastianross', date: '2024-11-11', details: '14:00 - 15:00 (1 hour)', pod: '3', status: 'completed' },
            { type: 'penalty', username: 'sebastianross', date: '2024-11-11', details: 'Late cancellation', pod: '3', status: 'paid' },
            { type: 'booking', username: 'abigailpowell', date: '2024-11-15', details: '12:00 - 13:30 (1.5 hours)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'danieljenkins', date: '2024-11-19', details: '10:00 - 11:00 (1 hour)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'elizarussell', date: '2024-11-23', details: '15:30 - 16:30 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'matthewgriffin', date: '2024-11-27', details: '08:00 - 09:30 (1.5 hours)', pod: '3', status: 'completed' },
            
            // December 2024 - 5 bookings, 2 penalties
            { type: 'booking', username: 'averydiaz', date: '2024-12-02', details: '13:00 - 14:00 (1 hour)', pod: '4', status: 'completed' },
            { type: 'penalty', username: 'averydiaz', date: '2024-12-02', details: 'Overstayed booking', pod: '4', status: 'pending' },
            { type: 'booking', username: 'cartersanders', date: '2024-12-08', details: '10:30 - 11:30 (1 hour)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'scarletthayescompleted', date: '2024-12-14', details: '14:30 - 15:30 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'leoreynolds', date: '2024-12-20', details: '09:00 - 10:30 (1.5 hours)', pod: '3', status: 'completed' },
            { type: 'penalty', username: 'leoreynolds', date: '2024-12-20', details: 'No-show', pod: '3', status: 'paid' },
            { type: 'booking', username: 'ariapryce', date: '2024-12-26', details: '12:00 - 13:00 (1 hour)', pod: '4', status: 'completed' },
            
            // === 2025 DATA (Fluctuating amounts per month) ===
            
            // January 2025 - 9 bookings, 2 penalties
            { type: 'booking', username: 'gabrielburns', date: '2025-01-04', details: '08:00 - 09:30 (1.5 hours)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'penelope howard', date: '2025-01-08', details: '11:00 - 12:00 (1 hour)', pod: '2', status: 'completed' },
            { type: 'penalty', username: 'penelopehoward', date: '2025-01-08', details: 'Late cancellation', pod: '2', status: 'paid' },
            { type: 'booking', username: 'julianhughes', date: '2025-01-12', details: '14:00 - 15:30 (1.5 hours)', pod: '3', status: 'completed' },
            { type: 'booking', username: 'rileyflores', date: '2025-01-16', details: '10:00 - 11:00 (1 hour)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'wyattgomez', date: '2025-01-20', details: '09:00 - 10:30 (1.5 hours)', pod: '1', status: 'completed' },
            { type: 'penalty', username: 'wyattgomez', date: '2025-01-20', details: 'Overstayed booking', pod: '1', status: 'pending' },
            { type: 'booking', username: 'laylamurray', date: '2025-01-24', details: '13:00 - 14:00 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'lincolnfreeman', date: '2025-01-27', details: '15:30 - 17:00 (1.5 hours)', pod: '3', status: 'completed' },
            { type: 'booking', username: 'zoeywestcompleted', date: '2025-01-30', details: '08:30 - 09:30 (1 hour)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'jacksonwells', date: '2025-01-31', details: '12:00 - 13:00 (1 hour)', pod: '1', status: 'completed' },
            
            // February 2025 - 6 bookings, 1 penalty
            { type: 'booking', username: 'aurorarodriguez', date: '2025-02-05', details: '11:30 - 13:00 (1.5 hours)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'graysonstewart', date: '2025-02-10', details: '14:00 - 15:00 (1 hour)', pod: '3', status: 'completed' },
            { type: 'penalty', username: 'graysonstewart', date: '2025-02-10', details: 'No-show', pod: '3', status: 'paid' },
            { type: 'booking', username: 'novabennett', date: '2025-02-15', details: '09:00 - 10:00 (1 hour)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'eastoncoleman', date: '2025-02-20', details: '16:00 - 17:30 (1.5 hours)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'lunaperez', date: '2025-02-24', details: '10:00 - 11:30 (1.5 hours)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'carsonreed', date: '2025-02-28', details: '13:30 - 14:30 (1 hour)', pod: '3', status: 'completed' },
            
            // March 2025 - 10 bookings, 3 penalties
            { type: 'booking', username: 'violettbailey', date: '2025-03-03', details: '08:00 - 09:00 (1 hour)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'hudsoncox', date: '2025-03-06', details: '15:00 - 16:30 (1.5 hours)', pod: '1', status: 'completed' },
            { type: 'penalty', username: 'hudsoncox', date: '2025-03-06', details: 'Late cancellation', pod: '1', status: 'paid' },
            { type: 'booking', username: 'hazelrichardson', date: '2025-03-09', details: '11:00 - 12:30 (1.5 hours)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'luckashoward', date: '2025-03-12', details: '09:30 - 10:30 (1 hour)', pod: '3', status: 'completed' },
            { type: 'penalty', username: 'luckashoward', date: '2025-03-12', details: 'Overstayed booking', pod: '3', status: 'pending' },
            { type: 'booking', username: 'stellaward', date: '2025-03-15', details: '14:00 - 15:00 (1 hour)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'mavericsanders', date: '2025-03-18', details: '10:00 - 11:00 (1 hour)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'evelynprice', date: '2025-03-21', details: '12:00 - 13:30 (1.5 hours)', pod: '2', status: 'completed' },
            { type: 'penalty', username: 'evelynprice', date: '2025-03-21', details: 'No-show', pod: '2', status: 'paid' },
            { type: 'booking', username: 'coltonbennet', date: '2025-03-24', details: '08:30 - 09:30 (1 hour)', pod: '3', status: 'completed' },
            { type: 'booking', username: 'audreymorgan', date: '2025-03-27', details: '15:00 - 16:00 (1 hour)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'hunterbell', date: '2025-03-30', details: '11:30 - 12:30 (1 hour)', pod: '1', status: 'completed' },
            
            // April 2025 - 8 bookings, 2 penalties
            { type: 'booking', username: 'chloeward', date: '2025-04-03', details: '13:00 - 14:30 (1.5 hours)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'asherrivera', date: '2025-04-07', details: '09:00 - 10:00 (1 hour)', pod: '3', status: 'completed' },
            { type: 'penalty', username: 'asherrivera', date: '2025-04-07', details: 'Late cancellation', pod: '3', status: 'paid' },
            { type: 'booking', username: 'eleanor kelly', date: '2025-04-11', details: '14:30 - 15:30 (1 hour)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'jamieson cooper', date: '2025-04-15', details: '10:30 - 11:30 (1 hour)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'sophieraymond', date: '2025-04-19', details: '12:00 - 13:00 (1 hour)', pod: '2', status: 'completed' },
            { type: 'penalty', username: 'sophieraymond', date: '2025-04-19', details: 'Overstayed booking', pod: '2', status: 'pending' },
            { type: 'booking', username: 'axelgraves', date: '2025-04-23', details: '08:00 - 09:30 (1.5 hours)', pod: '3', status: 'completed' },
            { type: 'booking', username: 'brookejimenez', date: '2025-04-27', details: '15:00 - 16:30 (1.5 hours)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'ryderfoster', date: '2025-04-30', details: '11:00 - 12:00 (1 hour)', pod: '1', status: 'completed' },
            
            // May 2025 - 12 bookings, 2 penalties
            { type: 'booking', username: 'madisongray', date: '2025-05-02', details: '09:30 - 10:30 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'romanbutler', date: '2025-05-05', details: '13:00 - 14:00 (1 hour)', pod: '3', status: 'completed' },
            { type: 'booking', username: 'gracestone', date: '2025-05-08', details: '10:00 - 11:30 (1.5 hours)', pod: '4', status: 'completed' },
            { type: 'penalty', username: 'gracestone', date: '2025-05-08', details: 'No-show', pod: '4', status: 'paid' },
            { type: 'booking', username: 'declanlong', date: '2025-05-11', details: '14:00 - 15:30 (1.5 hours)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'scarlettbyrd', date: '2025-05-14', details: '08:30 - 09:30 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'kaiwood', date: '2025-05-17', details: '12:00 - 13:00 (1 hour)', pod: '3', status: 'completed' },
            { type: 'booking', username: 'bellacastro', date: '2025-05-20', details: '15:00 - 16:00 (1 hour)', pod: '4', status: 'completed' },
            { type: 'penalty', username: 'bellacastro', date: '2025-05-20', details: 'Late cancellation', pod: '4', status: 'paid' },
            { type: 'booking', username: 'landonhicks', date: '2025-05-23', details: '10:30 - 11:30 (1 hour)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'lilyhawkins', date: '2025-05-26', details: '13:30 - 14:30 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'westonknight', date: '2025-05-29', details: '09:00 - 10:00 (1 hour)', pod: '3', status: 'completed' },
            { type: 'booking', username: 'addisontate', date: '2025-05-31', details: '14:30 - 15:30 (1 hour)', pod: '4', status: 'completed' },
            
            // June 2025 - 7 bookings, 1 penalty
            { type: 'booking', username: 'josiahgray', date: '2025-06-04', details: '11:00 - 12:30 (1.5 hours)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'natalieortiz', date: '2025-06-09', details: '08:00 - 09:00 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'theoandrade', date: '2025-06-14', details: '14:00 - 15:00 (1 hour)', pod: '3', status: 'completed' },
            { type: 'penalty', username: 'theoandrade', date: '2025-06-14', details: 'Overstayed booking', pod: '3', status: 'pending' },
            { type: 'booking', username: 'hannahvargas', date: '2025-06-19', details: '10:00 - 11:00 (1 hour)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'oliverjuarez', date: '2025-06-23', details: '12:30 - 13:30 (1 hour)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'emmavasquez', date: '2025-06-26', details: '15:30 - 16:30 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'kingsley barrett', date: '2025-06-29', details: '09:30 - 10:30 (1 hour)', pod: '3', status: 'completed' },
            
            // July 2025 - 11 bookings, 3 penalties
            { type: 'booking', username: 'valentinablake', date: '2025-07-02', details: '13:00 - 14:30 (1.5 hours)', pod: '4', status: 'completed' },
            { type: 'penalty', username: 'valentinablake', date: '2025-07-02', details: 'No-show', pod: '4', status: 'paid' },
            { type: 'booking', username: 'kingstonmendez', date: '2025-07-05', details: '10:00 - 11:30 (1.5 hours)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'alicecruz', date: '2025-07-08', details: '14:30 - 15:30 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'leongreene', date: '2025-07-11', details: '08:30 - 09:30 (1 hour)', pod: '3', status: 'completed' },
            { type: 'penalty', username: 'leongreene', date: '2025-07-11', details: 'Late cancellation', pod: '3', status: 'paid' },
            { type: 'booking', username: 'claireburton', date: '2025-07-14', details: '12:00 - 13:00 (1 hour)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'adrianmaldonado', date: '2025-07-17', details: '15:00 - 16:30 (1.5 hours)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'stellagriffin', date: '2025-07-20', details: '09:00 - 10:00 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'rowanreyes', date: '2025-07-23', details: '13:30 - 14:30 (1 hour)', pod: '3', status: 'completed' },
            { type: 'penalty', username: 'rowanreyes', date: '2025-07-23', details: 'Overstayed booking', pod: '3', status: 'pending' },
            { type: 'booking', username: 'savannachambers', date: '2025-07-26', details: '11:00 - 12:00 (1 hour)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'everettbryant', date: '2025-07-29', details: '14:00 - 15:30 (1.5 hours)', pod: '1', status: 'completed' },
            
            // August 2025 - 9 bookings, 2 penalties
            { type: 'booking', username: 'ariamcdonald', date: '2025-08-01', details: '10:30 - 11:30 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'paxtoncrawford', date: '2025-08-05', details: '13:00 - 14:00 (1 hour)', pod: '3', status: 'completed' },
            { type: 'penalty', username: 'paxtoncrawford', date: '2025-08-05', details: 'No-show', pod: '3', status: 'paid' },
            { type: 'booking', username: 'skylarcarr', date: '2025-08-09', details: '08:00 - 09:30 (1.5 hours)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'dominiclewis', date: '2025-08-13', details: '15:00 - 16:00 (1 hour)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'alayahparrish', date: '2025-08-17', details: '11:30 - 12:30 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'jonathanporter', date: '2025-08-21', details: '14:00 - 15:30 (1.5 hours)', pod: '3', status: 'completed' },
            { type: 'penalty', username: 'jonathanporter', date: '2025-08-21', details: 'Late cancellation', pod: '3', status: 'paid' },
            { type: 'booking', username: 'brooklynhale', date: '2025-08-25', details: '09:00 - 10:00 (1 hour)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'charleshess', date: '2025-08-29', details: '12:00 - 13:30 (1.5 hours)', pod: '1', status: 'completed' },
            
            // September 2025 - 10 bookings, 3 penalties
            { type: 'booking', username: 'kennedydawson', date: '2025-09-02', details: '10:00 - 11:00 (1 hour)', pod: '2', status: 'completed' },
            { type: 'penalty', username: 'kennedydawson', date: '2025-09-02', details: 'Overstayed booking', pod: '2', status: 'pending' },
            { type: 'booking', username: 'christianpage', date: '2025-09-06', details: '14:30 - 15:30 (1 hour)', pod: '3', status: 'completed' },
            { type: 'booking', username: 'auroravelasquez', date: '2025-09-10', details: '08:30 - 09:30 (1 hour)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'brycemills', date: '2025-09-14', details: '13:00 - 14:30 (1.5 hours)', pod: '1', status: 'completed' },
            { type: 'penalty', username: 'brycemills', date: '2025-09-14', details: 'No-show', pod: '1', status: 'paid' },
            { type: 'booking', username: 'camiladunn', date: '2025-09-18', details: '11:00 - 12:00 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'gavinwebb', date: '2025-09-22', details: '15:00 - 16:30 (1.5 hours)', pod: '3', status: 'completed' },
            { type: 'penalty', username: 'gavinwebb', date: '2025-09-22', details: 'Late cancellation', pod: '3', status: 'paid' },
            { type: 'booking', username: 'genesislane', date: '2025-09-26', details: '09:30 - 10:30 (1 hour)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'tristanbowman', date: '2025-09-29', details: '12:30 - 13:30 (1 hour)', pod: '1', status: 'completed' },
            
            // October 2025 - 8 bookings, 1 penalty
            { type: 'booking', username: 'serenityfields', date: '2025-10-03', details: '14:00 - 15:00 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'collinmcdaniel', date: '2025-10-07', details: '10:00 - 11:30 (1.5 hours)', pod: '3', status: 'completed' },
            { type: 'booking', username: 'josephinestout', date: '2025-10-11', details: '08:00 - 09:00 (1 hour)', pod: '4', status: 'completed' },
            { type: 'penalty', username: 'josephinestout', date: '2025-10-11', details: 'Overstayed booking', pod: '4', status: 'pending' },
            { type: 'booking', username: 'brooksmeyers', date: '2025-10-15', details: '13:00 - 14:30 (1.5 hours)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'delaneyhorton', date: '2025-10-19', details: '11:30 - 12:30 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'damianvaughn', date: '2025-10-23', details: '15:00 - 16:00 (1 hour)', pod: '3', status: 'completed' },
            { type: 'booking', username: 'finleyryan', date: '2025-10-27', details: '09:00 - 10:00 (1 hour)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'presleygiles', date: '2025-10-31', details: '12:00 - 13:00 (1 hour)', pod: '1', status: 'completed' },
            
            // November 2025 - 6 bookings, 2 penalties
            { type: 'booking', username: 'elliotwoods', date: '2025-11-04', details: '14:30 - 15:30 (1 hour)', pod: '2', status: 'completed' },
            { type: 'penalty', username: 'elliotwoods', date: '2025-11-04', details: 'No-show', pod: '2', status: 'paid' },
            { type: 'booking', username: 'mariahfuentes', date: '2025-11-09', details: '10:30 - 11:30 (1 hour)', pod: '3', status: 'completed' },
            { type: 'booking', username: 'beckettbradford', date: '2025-11-14', details: '08:30 - 09:30 (1 hour)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'genevievecolon', date: '2025-11-19', details: '13:00 - 14:30 (1.5 hours)', pod: '1', status: 'completed' },
            { type: 'penalty', username: 'genevievecolon', date: '2025-11-19', details: 'Late cancellation', pod: '1', status: 'paid' },
            { type: 'booking', username: 'kashvega', date: '2025-11-24', details: '11:00 - 12:00 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'madisonochoa', date: '2025-11-29', details: '15:00 - 16:30 (1.5 hours)', pod: '3', status: 'completed' },
            
            // December 2025 - 7 bookings, 1 penalty
            { type: 'booking', username: 'silasgrant', date: '2025-12-03', details: '09:00 - 10:00 (1 hour)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'lucyhaas', date: '2025-12-08', details: '12:00 - 13:30 (1.5 hours)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'emmettdecker', date: '2025-12-13', details: '14:00 - 15:00 (1 hour)', pod: '2', status: 'completed' },
            { type: 'booking', username: 'adelyngilbert', date: '2025-12-17', details: '10:00 - 11:00 (1 hour)', pod: '3', status: 'completed' },
            { type: 'penalty', username: 'adelyngilbert', date: '2025-12-17', details: 'Overstayed booking', pod: '3', status: 'pending' },
            { type: 'booking', username: 'zanetaylor', date: '2025-12-21', details: '08:00 - 09:30 (1.5 hours)', pod: '4', status: 'completed' },
            { type: 'booking', username: 'ivyleach', date: '2025-12-26', details: '13:30 - 14:30 (1 hour)', pod: '1', status: 'completed' },
            { type: 'booking', username: 'wadeterry', date: '2025-12-30', details: '11:30 - 12:30 (1 hour)', pod: '2', status: 'completed' }
        ];
        
        // Function to calculate analytics data from actual records
        function calculateAnalyticsFromRecords() {
            var analytics = {
                daily: [
                    { period: 'Mon', bookings: 0, penalties: 0 },
                    { period: 'Tue', bookings: 0, penalties: 0 },
                    { period: 'Wed', bookings: 0, penalties: 0 },
                    { period: 'Thu', bookings: 0, penalties: 0 },
                    { period: 'Fri', bookings: 0, penalties: 0 },
                    { period: 'Sat', bookings: 0, penalties: 0 },
                    { period: 'Sun', bookings: 0, penalties: 0 }
                ],
                monthly: {},
                quarterly: []
            };
            
            // Initialize monthly data for available years
            var years = [2024, 2025, 2026];
            years.forEach(function(year) {
                analytics.monthly[year] = {};
                for (var i = 0; i < 12; i++) {
                    analytics.monthly[year][i] = { bookings: 0, penalties: 0 };
                }
            });
            
            // Count records by month and year
            allRecords.forEach(function(record) {
                var date = new Date(record.date);
                var year = date.getFullYear();
                var month = date.getMonth();
                
                if (analytics.monthly[year]) {
                    if (record.type === 'booking') {
                        analytics.monthly[year][month].bookings++;
                    } else if (record.type === 'penalty') {
                        analytics.monthly[year][month].penalties++;
                    }
                }
            });
            
            // Calculate quarterly data
            var quarterlyData = {};
            allRecords.forEach(function(record) {
                var date = new Date(record.date);
                var year = date.getFullYear();
                var month = date.getMonth();
                var quarter = Math.floor(month / 3) + 1;
                var key = 'Q' + quarter + ' ' + year;
                
                if (!quarterlyData[key]) {
                    quarterlyData[key] = { period: key, bookings: 0, penalties: 0 };
                }
                
                if (record.type === 'booking') {
                    quarterlyData[key].bookings++;
                } else if (record.type === 'penalty') {
                    quarterlyData[key].penalties++;
                }
            });
            
            // Convert quarterly object to array and sort
            analytics.quarterly = Object.values(quarterlyData).sort(function(a, b) {
                return a.period.localeCompare(b.period);
            });
            
            return analytics;
        }
        
        // Calculate analytics data from actual records
        var masterAnalyticsData = calculateAnalyticsFromRecords();
        
        // Current analytics data for display
        var analyticsData = masterAnalyticsData.daily;
        
        // Mock booking data for today - 30 bookings across all time slots
        // No overlapping bookings per pod - each pod can only have one booking at a time
        var bookingsData = [
            // Pod 1 bookings
            { username: 'johnsmith', room: 1, checkIn: '08:30', checkOut: '09:30', duration: '1 hour' },
            { username: 'jameswalker', room: 1, checkIn: '10:00', checkOut: '11:00', duration: '1 hour' },
            { username: 'williammoore', room: 1, checkIn: '11:15', checkOut: '12:15', duration: '1 hour' },
            { username: 'markwright', room: 1, checkIn: '13:00', checkOut: '14:00', duration: '1 hour' },
            { username: 'nancyking', room: 1, checkIn: '14:30', checkOut: '15:30', duration: '1 hour' },
            { username: 'helenrobinson', room: 1, checkIn: '16:00', checkOut: '17:00', duration: '1 hour' },
            { username: 'jessicamartinez', room: 1, checkIn: '17:30', checkOut: '19:30', duration: '2 hours' },
            
            // Pod 2 bookings
            { username: 'danielharris', room: 2, checkIn: '08:45', checkOut: '09:45', duration: '1 hour' },
            { username: 'emmajohnson', room: 2, checkIn: '10:15', checkOut: '11:15', duration: '1 hour' },
            { username: 'jenniferwilson', room: 2, checkIn: '11:45', checkOut: '12:45', duration: '1 hour' },
            { username: 'michaelbrown', room: 2, checkIn: '13:15', checkOut: '14:15', duration: '1 hour' },
            { username: 'thomasanderson', room: 2, checkIn: '14:45', checkOut: '15:45', duration: '1 hour' },
            { username: 'jasonclark', room: 2, checkIn: '16:15', checkOut: '17:15', duration: '1 hour' },
            { username: 'lindagreen', room: 2, checkIn: '17:45', checkOut: '19:15', duration: '1.5 hours' },
            { username: 'stevenhill', room: 2, checkIn: '19:30', checkOut: '20:00', duration: '30 min' },
            
            // Pod 3 bookings
            { username: 'kevinmartin', room: 3, checkIn: '09:00', checkOut: '10:00', duration: '1 hour' },
            { username: 'mariagarcia', room: 3, checkIn: '10:30', checkOut: '11:30', duration: '1 hour' },
            { username: 'davidwilson', room: 3, checkIn: '12:00', checkOut: '13:00', duration: '1 hour' },
            { username: 'barbaralee', room: 3, checkIn: '13:30', checkOut: '14:30', duration: '1 hour' },
            { username: 'christopherlee', room: 3, checkIn: '15:00', checkOut: '16:00', duration: '1 hour' },
            { username: 'lisaanderson', room: 3, checkIn: '17:00', checkOut: '20:00', duration: '3 hours' },
            
            // Pod 4 bookings
            { username: 'sarahdavis', room: 4, checkIn: '09:15', checkOut: '10:15', duration: '1 hour' },
            { username: 'robertbrown', room: 4, checkIn: '10:45', checkOut: '11:45', duration: '1 hour' },
            { username: 'susantaylor', room: 4, checkIn: '12:15', checkOut: '13:15', duration: '1 hour' },
            { username: 'amandawhite', room: 4, checkIn: '14:00', checkOut: '15:00', duration: '1 hour' },
            { username: 'dorothymartin', room: 4, checkIn: '15:30', checkOut: '16:30', duration: '1 hour' },
            { username: 'patriciamoore', room: 4, checkIn: '17:00', checkOut: '18:30', duration: '1.5 hours' },
            { username: 'brianlopez', room: 4, checkIn: '19:00', checkOut: '20:00', duration: '1 hour' }
        ];
        
        function convertTo12Hour(time24) {
            // time24 format: "HH:MM" (e.g., "08:30", "13:00", "17:45")
            var parts = time24.split(':');
            var hours = parseInt(parts[0]);
            var minutes = parts[1];
            
            var period = hours >= 12 ? 'PM' : 'AM';
            var hours12 = hours % 12 || 12; // Convert 0 to 12 for midnight, 13-23 to 1-11
            
            return hours12 + ':' + minutes + ' ' + period;
        }
        
        function getBookingStatus(checkIn, checkOut) {
            var now = new Date();
            var today = now.toISOString().split('T')[0];
            
            var checkInDateTime = new Date(today + ' ' + checkIn);
            var checkOutDateTime = new Date(today + ' ' + checkOut);
            
            if (now < checkInDateTime) {
                return 'upcoming';
            } else if (now >= checkInDateTime && now <= checkOutDateTime) {
                return 'ongoing';
            } else {
                return 'completed';
            }
        }
        
        function initializeChart(data, hasData, typeFilter) {
            // If no data, show message instead of chart
            if (hasData === false) {
                $('#analytics-chart').hide();
                $('#chart-legend').hide();
                $('#no-chart-data').show();
                return;
            }
            
            // Show chart and hide no data message
            $('#analytics-chart').show();
            $('#no-chart-data').hide();
            
            // Destroy existing chart if it exists
            if (morrisChart) {
                $('#analytics-chart').empty();
            }
            
            // Determine which data to show based on type filter
            var ykeys, labels, barColors;
            
            if (typeFilter === 'bookings') {
                ykeys = ['bookings'];
                labels = ['Bookings Made'];
                barColors = ['#5cb85c'];
                // Show only bookings in legend
                $('#chart-legend').show();
                $('#legend-bookings').show();
                $('#legend-penalties').hide();
            } else if (typeFilter === 'penalties') {
                ykeys = ['penalties'];
                labels = ['Penalties Issued'];
                barColors = ['#d9534f'];
                // Show only penalties in legend
                $('#chart-legend').show();
                $('#legend-bookings').hide();
                $('#legend-penalties').show();
            } else {
                // Both
                ykeys = ['bookings', 'penalties'];
                labels = ['Bookings Made', 'Penalties Issued'];
                barColors = ['#5cb85c', '#d9534f'];
                // Show both in legend
                $('#chart-legend').show();
                $('#legend-bookings').show();
                $('#legend-penalties').show();
            }
            
            morrisChart = Morris.Bar({
                element: 'analytics-chart',
                data: data || analyticsData,
                xkey: 'period',
                ykeys: ykeys,
                labels: labels,
                barColors: barColors,
                hideHover: 'auto',
                resize: true,
                gridTextSize: 12,
                barSizeRatio: 0.5,
                xLabelAngle: 0,
                parseTime: false,  // Treat x-axis as categories, not dates
                xLabels: null,  // Show all x-axis labels
                axes: true,
                grid: true
            });
            
            // Add data labels on top of bars after chart is rendered
            // Use longer timeout and store data for label function
            window.currentChartData = data || analyticsData;
            window.currentTypeFilter = typeFilter;
            
            setTimeout(function() {
                addMissingMonthLabels();
            }, 100);
            
            setTimeout(function() {
                addDataLabels();
            }, 150);
        }
        
        function addMissingMonthLabels() {
            var chartData = window.currentChartData;
            if (!chartData || chartData.length === 0) {
                return;
            }
            
            var svg = $('#analytics-chart svg');
            if (!svg.length) {
                return;
            }
            
            var svgElement = svg[0];
            var chartHeight = parseFloat(svg.attr('height'));
            var chartWidth = parseFloat(svg.attr('width'));
            
            // REMOVE ALL existing x-axis labels from Morris.js
            svg.find('text').each(function() {
                var yPos = parseFloat($(this).attr('y'));
                if (yPos > chartHeight - 50) {
                    $(this).remove();
                }
            });
            
            // Find all bars and group by X position
            var barGroups = [];
            var allBars = [];
            
            svg.find('rect').each(function() {
                var fill = $(this).attr('fill');
                var height = parseFloat($(this).attr('height'));
                
                if (height > 5) {
                    var isGreen = fill === '#5cb85c' || fill === 'rgb(92, 184, 92)' || fill.toLowerCase() === '#5cb85c';
                    var isRed = fill === '#d9534f' || fill === 'rgb(217, 83, 79)' || fill.toLowerCase() === '#d9534f';
                    
                    if (isGreen || isRed) {
                        var x = parseFloat($(this).attr('x'));
                        var width = parseFloat($(this).attr('width'));
                        allBars.push({
                            x: x,
                            width: width,
                            centerX: x + (width / 2)
                        });
                    }
                }
            });
            
            // Sort bars by X position
            allBars.sort(function(a, b) { return a.centerX - b.centerX; });
            
            // Calculate dynamic tolerance based on chart width and number of data points
            // For quarterly (4 points), bars are more spread out than monthly (12 points)
            var avgBarSpacing = chartWidth / (chartData.length * 2);
            var groupTolerance = avgBarSpacing * 0.8; // 80% of average spacing
            
            // Group bars that are close together (same period)
            var currentGroup = [];
            for (var i = 0; i < allBars.length; i++) {
                if (currentGroup.length === 0) {
                    currentGroup.push(allBars[i]);
                } else {
                    var lastBar = currentGroup[currentGroup.length - 1];
                    if (Math.abs(allBars[i].centerX - lastBar.centerX) < groupTolerance) {
                        currentGroup.push(allBars[i]);
                    } else {
                        barGroups.push(currentGroup);
                        currentGroup = [allBars[i]];
                    }
                }
            }
            if (currentGroup.length > 0) {
                barGroups.push(currentGroup);
            }
            
            // Fixed Y position for ALL labels
            var fixedYPos = chartHeight - 10;
            
            // Create labels at the center of each bar group
            for (var i = 0; i < Math.min(barGroups.length, chartData.length); i++) {
                var group = barGroups[i];
                var period = chartData[i].period;
                
                // Calculate the center of all bars in this group
                var sumX = 0;
                for (var j = 0; j < group.length; j++) {
                    sumX += group[j].centerX;
                }
                var groupCenterX = sumX / group.length;
                
                var text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                text.setAttribute('x', groupCenterX);
                text.setAttribute('y', fixedYPos);
                text.setAttribute('text-anchor', 'middle');
                text.setAttribute('font-family', 'sans-serif');
                text.setAttribute('font-size', '12px');
                text.setAttribute('fill', '#888');
                text.textContent = period;
                
                svgElement.appendChild(text);
            }
        }
        
        function addDataLabels() {
            // Get the SVG element
            var svg = $('#analytics-chart svg');
            if (!svg.length) {
                return;
            }
            
            var svgElement = svg[0];
            var chartData = window.currentChartData;
            var typeFilter = window.currentTypeFilter;
            
            if (!chartData || chartData.length === 0) {
                return;
            }
            
            // Get chart dimensions
            var chartHeight = parseFloat(svg.attr('height'));
            var chartBottom = chartHeight - 30;
            
            // Get X positions from the month labels (they're positioned at bar centers!)
            var monthLabelPositions = [];
            svg.find('text').each(function() {
                var yPos = parseFloat($(this).attr('y'));
                var xPos = parseFloat($(this).attr('x'));
                var text = $(this).text();
                
                if (yPos > chartHeight - 50) {
                    for (var i = 0; i < chartData.length; i++) {
                        if (chartData[i].period === text) {
                            monthLabelPositions[i] = xPos;
                            break;
                        }
                    }
                }
            });
            
            // Find ALL bars with their Y positions
            var greenBars = [];
            var redBars = [];
            
            svg.find('rect').each(function() {
                var fill = $(this).attr('fill');
                var height = parseFloat($(this).attr('height'));
                
                if (height > 5) {
                    var x = parseFloat($(this).attr('x'));
                    var y = parseFloat($(this).attr('y'));
                    var width = parseFloat($(this).attr('width'));
                    var centerX = x + (width / 2);
                    
                    if (fill === '#5cb85c' || fill === 'rgb(92, 184, 92)' || fill.toLowerCase() === '#5cb85c') {
                        greenBars.push({ centerX: centerX, y: y });
                    } else if (fill === '#d9534f' || fill === 'rgb(217, 83, 79)' || fill.toLowerCase() === '#d9534f') {
                        redBars.push({ centerX: centerX, y: y });
                    }
                }
            });
            
            greenBars.sort(function(a, b) { return a.centerX - b.centerX; });
            redBars.sort(function(a, b) { return a.centerX - b.centerX; });
            
            // Add labels at month label positions
            var greenIndex = 0;
            var redIndex = 0;
            
            for (var i = 0; i < chartData.length; i++) {
                var dataPoint = chartData[i];
                var labelX = monthLabelPositions[i];
                
                if (!labelX) continue;
                
                // Add booking label
                if (typeFilter === 'both' || typeFilter === 'bookings') {
                    var bookingValue = dataPoint.bookings || 0;
                    var greenBar = (bookingValue > 0 && greenIndex < greenBars.length) ? greenBars[greenIndex++] : null;
                    
                    var textX, textY;
                    if (greenBar) {
                        // Use actual bar center X for non-zero values
                        textX = greenBar.centerX;
                        textY = greenBar.y - 5;
                    } else {
                        // Zero value - position relative to label
                        textX = labelX - (typeFilter === 'both' ? 20 : 0);
                        textY = chartBottom - 5;
                    }
                    
                    var text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                    text.setAttribute('x', textX);
                    text.setAttribute('y', textY);
                    text.setAttribute('text-anchor', 'middle');
                    text.setAttribute('font-family', 'Arial, sans-serif');
                    text.setAttribute('font-size', '11px');
                    text.setAttribute('font-weight', 'bold');
                    text.setAttribute('fill', '#333');
                    text.textContent = bookingValue;
                    
                    svgElement.appendChild(text);
                }
                
                // Add penalty label
                if (typeFilter === 'both' || typeFilter === 'penalties') {
                    var penaltyValue = dataPoint.penalties || 0;
                    var redBar = (penaltyValue > 0 && redIndex < redBars.length) ? redBars[redIndex++] : null;
                    
                    var textX, textY;
                    if (redBar) {
                        // Use actual bar center X for non-zero values
                        textX = redBar.centerX;
                        textY = redBar.y - 5;
                    } else {
                        // Zero value - position relative to label
                        textX = labelX + (typeFilter === 'both' ? 20 : 0);
                        textY = chartBottom - 5;
                    }
                    
                    var text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                    text.setAttribute('x', textX);
                    text.setAttribute('y', textY);
                    text.setAttribute('text-anchor', 'middle');
                    text.setAttribute('font-family', 'Arial, sans-serif');
                    text.setAttribute('font-size', '11px');
                    text.setAttribute('font-weight', 'bold');
                    text.setAttribute('fill', '#333');
                    text.textContent = penaltyValue;
                    
                    svgElement.appendChild(text);
                }
            }
        }
        
        function updateDynamicFilter() {
            var parameter = $('#filterByParameter').val();
            var container = $('#dynamicFilterContainer');
            container.empty();
            
            var filterHTML = '';
            
            if (parameter === 'date') {
                filterHTML = '<div>' +
                    '<label for="startDate">From:</label> ' +
                    '<input type="date" id="startDate" class="form-control" style="display: inline-block; width: 150px;"> ' +
                    '<label for="endDate">To:</label> ' +
                    '<input type="date" id="endDate" class="form-control" style="display: inline-block; width: 150px;">' +
                    '</div>';
            } else if (parameter === 'month') {
                filterHTML = '<div>' +
                    '<label for="yearSelect">Select Year:</label> ' +
                                    '<select id="yearSelect" class="form-control" style="display: inline-block; width: 160px;">' +
                                    '<option value="2024" selected>2024</option>' +
                                    '<option value="2025">2025</option>' +
                                    '<option value="2026">2026</option>' +
                                    '</select>' +
                                    '</div>';
            } else if (parameter === 'quarterly') {
                filterHTML = '<div>' +
                    '<label for="yearSelectQuarterly">Select Year:</label> ' +
                    '<select id="yearSelectQuarterly" class="form-control" style="display: inline-block; width: 160px;">' +
                    '<option value="2024" selected>2024</option>' +
                    '<option value="2025">2025</option>' +
                    '<option value="2026">2026</option>' +
                    '</select>' +
                    '</div>';
            }
            
            container.html(filterHTML);
            
            // Add date validation for date range filter
            if (parameter === 'date') {
                // When start date changes, update the minimum allowed end date
                $('#startDate').on('change', function() {
                    var startDate = $(this).val();
                    if (startDate) {
                        $('#endDate').attr('min', startDate);
                        
                        // If end date is already set and is earlier than start date, clear it
                        var endDate = $('#endDate').val();
                        if (endDate && endDate < startDate) {
                            $('#endDate').val('');
                            alert('The "To" date cannot be earlier than the "From" date. Please select a valid date range.');
                        }
                    }
                });
                
                // When end date changes, update the maximum allowed start date
                $('#endDate').on('change', function() {
                    var endDate = $(this).val();
                    if (endDate) {
                        $('#startDate').attr('max', endDate);
                        
                        // If start date is already set and is later than end date, clear end date
                        var startDate = $('#startDate').val();
                        if (startDate && startDate > endDate) {
                            $(this).val('');
                            alert('The "To" date cannot be earlier than the "From" date. Please select a valid date range.');
                        }
                    }
                });
            }
        }
        
        function applyAnalyticsFilters() {
            var typeFilter = $('#filterByType').val();
            var parameter = $('#filterByParameter').val();
            
            // Check if filters are selected
            if (!typeFilter || !parameter) {
                alert('Please select both Type and Parameter before applying filters.');
                return;
            }
            
            // Hide the select prompt
            $('#select-prompt-chart').hide();
            $('#selectPromptBody').hide();
            
            // Populate the table with all records first
            populateRecordsTable();
            
            // Get data based on parameter
            var chartData = [];
            var hasData = true; // Default to true
            
            if (parameter === 'date') {
                var startDate = $('#startDate').val();
                var endDate = $('#endDate').val();
                
                if (startDate && endDate) {
                    var start = new Date(startDate);
                    var end = new Date(endDate);
                    
                    // Filter records within the date range
                    var filteredRecords = allRecords.filter(function(record) {
                        var recordDate = new Date(record.date);
                        return recordDate >= start && recordDate <= end;
                    });
                    
                    // Group by date and count
                    var dateGroups = {};
                    filteredRecords.forEach(function(record) {
                        var dateKey = record.date;
                        if (!dateGroups[dateKey]) {
                            dateGroups[dateKey] = { period: dateKey, bookings: 0, penalties: 0 };
                        }
                        if (record.type === 'booking') {
                            dateGroups[dateKey].bookings++;
                        } else if (record.type === 'penalty') {
                            dateGroups[dateKey].penalties++;
                        }
                    });
                    
                    // Convert to array and sort by date
                    chartData = Object.values(dateGroups).sort(function(a, b) {
                        return new Date(a.period) - new Date(b.period);
                    });
                    
                    // Format dates for display
                    chartData = chartData.map(function(item) {
                        var date = new Date(item.period);
                        var monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                                        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        var formatted = date.getDate() + '-' + monthNames[date.getMonth()];
                        return {
                            period: formatted,
                            bookings: item.bookings,
                            penalties: item.penalties
                        };
                    });
                    
                    // Filter chartData based on typeFilter to only show relevant dates
                    // This prevents showing dates with 0 values when filtering by specific type
                    chartData = chartData.filter(function(item) {
                        if (typeFilter === 'bookings') {
                            return item.bookings > 0;
                        } else if (typeFilter === 'penalties') {
                            return item.penalties > 0;
                        } else {
                            // 'both' - show all dates with any data
                            return item.bookings > 0 || item.penalties > 0;
                        }
                    });
                    
                    // Check if there's data
                    hasData = chartData.length > 0 && chartData.some(function(item) {
                        return item.bookings > 0 || item.penalties > 0;
                    });
                } else {
                    // No dates selected, show daily default
                    chartData = masterAnalyticsData.daily;
                }
            } else if (parameter === 'month') {
                var selectedYear = $('#yearSelect').val() || '2024';
                var monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                                'July', 'August', 'September', 'October', 'November', 'December'];
                
                // Get all 12 months for the selected year
                chartData = [];
                hasData = false;
                
                for (var i = 0; i < 12; i++) {
                    var monthData = masterAnalyticsData.monthly[selectedYear][i];
                    chartData.push({
                        period: monthNames[i],
                        bookings: monthData.bookings,
                        penalties: monthData.penalties
                    });
                    
                    // Check if this year has any data
                    if (monthData.bookings > 0 || monthData.penalties > 0) {
                        hasData = true;
                    }
                }
                
            } else if (parameter === 'quarterly') {
                var selectedYear = $('#yearSelectQuarterly').val() || '2024';
                
                // Filter quarterly data for the selected year
                chartData = masterAnalyticsData.quarterly.filter(function(item) {
                    return item.period.includes(selectedYear);
                });
                
                // Check if there's any data for this year
                hasData = false;
                for (var i = 0; i < chartData.length; i++) {
                    if (chartData[i].bookings > 0 || chartData[i].penalties > 0) {
                        hasData = true;
                        break;
                    }
                }
                
                // If no data for this year, create empty quarters
                if (chartData.length === 0) {
                    chartData = [
                        { period: 'Q1 ' + selectedYear, bookings: 0, penalties: 0 },
                        { period: 'Q2 ' + selectedYear, bookings: 0, penalties: 0 },
                        { period: 'Q3 ' + selectedYear, bookings: 0, penalties: 0 },
                        { period: 'Q4 ' + selectedYear, bookings: 0, penalties: 0 }
                    ];
                }
            }
            
            // Apply type filter to chart data
            var filteredChartData = chartData.map(function(item) {
                var newItem = { period: item.period };
                
                if (typeFilter === 'both') {
                    newItem.bookings = item.bookings;
                    newItem.penalties = item.penalties;
                } else if (typeFilter === 'bookings') {
                    newItem.bookings = item.bookings;
                    newItem.penalties = 0;
                } else if (typeFilter === 'penalties') {
                    newItem.bookings = 0;
                    newItem.penalties = item.penalties;
                }
                
                return newItem;
            });
            
            // Update chart - pass hasData flag for month, quarterly, and date parameters
            var showNoData = ((parameter === 'month' || parameter === 'quarterly' || parameter === 'date') && !hasData);
            initializeChart(filteredChartData, showNoData ? false : true, typeFilter);
            
            // Filter table data based on type and time period
            filterTableByType(typeFilter, showNoData, parameter);
        }
        
        function filterTableByType(typeFilter, forceNoData, parameter) {
            // If forced to show no data
            if (forceNoData) {
                $('#recordsTableBody tr').hide();
                $('#noResultsBody').show();
                return;
            }
            
            $('#recordsTableBody tr').show();
            
            // Filter by type (bookings/penalties)
            if (typeFilter === 'bookings') {
                $('#recordsTableBody tr').each(function() {
                    if ($(this).attr('data-type') === 'penalty') {
                        $(this).hide();
                    }
                });
            } else if (typeFilter === 'penalties') {
                $('#recordsTableBody tr').each(function() {
                    if ($(this).attr('data-type') === 'booking') {
                        $(this).hide();
                    }
                });
            }
            
            // Filter by time period
            if (parameter === 'month') {
                var selectedYear = $('#yearSelect').val() || '2024';
                $('#recordsTableBody tr:visible').each(function() {
                    var recordDate = $(this).attr('data-date');
                    var recordYear = recordDate.split('-')[0];
                    if (recordYear !== selectedYear) {
                        $(this).hide();
                    }
                });
            } else if (parameter === 'quarterly') {
                var selectedYearQuarterly = $('#yearSelectQuarterly').val() || '2024';
                $('#recordsTableBody tr:visible').each(function() {
                    var recordDate = $(this).attr('data-date');
                    var recordYear = recordDate.split('-')[0];
                    if (recordYear !== selectedYearQuarterly) {
                        $(this).hide();
                    }
                });
            } else if (parameter === 'date') {
                // Filter by date range
                var startDateFilter = $('#startDate').val();
                var endDateFilter = $('#endDate').val();
                
                if (startDateFilter && endDateFilter) {
                    var startFilter = new Date(startDateFilter);
                    var endFilter = new Date(endDateFilter);
                    
                    $('#recordsTableBody tr:visible').each(function() {
                        var recordDate = new Date($(this).attr('data-date'));
                        if (recordDate < startFilter || recordDate > endFilter) {
                            $(this).hide();
                        }
                    });
                }
            }
            
            // Check if any rows are visible
            var visibleCount = $('#recordsTableBody tr:visible').length;
            if (visibleCount === 0) {
                $('#noResultsBody').show();
            } else {
                $('#noResultsBody').hide();
            }
        }
        
        function resetAnalyticsFilters() {
            $('#filterByType').val('');
            $('#filterByParameter').val('');
            updateDynamicFilter();
            
            // Hide chart, legend and table data
            $('#analytics-chart').hide();
            $('#chart-legend').hide();
            $('#no-chart-data').hide();
            $('#select-prompt-chart').show();
            
            // Hide table data and show prompt
            $('#recordsTableBody').empty();
            $('#noResultsBody').hide();
            $('#selectPromptBody').show();
        }
        
        function populateRecordsTable() {
            var tbody = $('#recordsTableBody');
            tbody.empty();
            
            // Sort records by date (newest first)
            var sortedRecords = allRecords.slice().sort(function(a, b) {
                return new Date(b.date) - new Date(a.date);
            });
            
            sortedRecords.forEach(function(record) {
                var typeClass = record.type === 'booking' ? 'label-info' : 'label-warning';
                var typeText = record.type === 'booking' ? 'Booking' : 'Penalty';
                
                var row = '<tr data-type="' + record.type + '" data-date="' + record.date + '" data-username="' + record.username + '">' +
                    '<td><span class="label ' + typeClass + '">' + typeText + '</span></td>' +
                    '<td>' + formatDate(record.date) + '</td>' +
                    '<td>' + record.details + '</td>' +
                    '</tr>';
                
                tbody.append(row);
            });
        }
        
        function formatDate(dateStr) {
            var date = new Date(dateStr);
            var options = { year: 'numeric', month: 'short', day: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        }
        
        function applyTableFilters() {
            if (!dataTable) {
                console.log('DataTable not initialized yet');
                return;
            }
            
            var typeFilter = $('#filterType').val();
            var dateFilter = $('#filterDate').val();
            var searchText = $('#searchUser').val().toLowerCase().trim();
            
            console.log('FILTERING - Type:', typeFilter, '| Date:', dateFilter, '| Search:', searchText);
            
            // Show all rows first
            $('#recordsTableBody tr').show();
            
            // Apply type filter
            if (typeFilter !== 'all') {
                $('#recordsTableBody tr').each(function() {
                    var rowType = $(this).attr('data-type');
                    if (rowType !== typeFilter) {
                        $(this).hide();
                    }
                });
            }
            
            // Apply date filter
            if (dateFilter !== 'all') {
                var today = new Date();
                today.setHours(0, 0, 0, 0);
                
                $('#recordsTableBody tr:visible').each(function() {
                    var rowDate = new Date($(this).attr('data-date'));
                    rowDate.setHours(0, 0, 0, 0);
                    var showRow = false;
                    
                    if (dateFilter === 'today') {
                        showRow = rowDate.getTime() === today.getTime();
                    } else if (dateFilter === 'week') {
                        var weekAgo = new Date(today);
                        weekAgo.setDate(today.getDate() - 7);
                        showRow = rowDate >= weekAgo && rowDate <= today;
                    } else if (dateFilter === 'month') {
                        var monthAgo = new Date(today);
                        monthAgo.setMonth(today.getMonth() - 1);
                        showRow = rowDate >= monthAgo && rowDate <= today;
                    }
                    
                    if (!showRow) {
                        $(this).hide();
                    }
                });
            }
            
            // Apply username search filter
            if (searchText !== '') {
                $('#recordsTableBody tr:visible').each(function() {
                    var username = $(this).attr('data-username').toLowerCase();
                    if (username.indexOf(searchText) === -1) {
                        $(this).hide();
                    }
                });
            }
            
            var visibleCount = $('#recordsTableBody tr:visible').length;
            var hiddenCount = $('#recordsTableBody tr:hidden').length;
            console.log('Results:', visibleCount, 'rows shown,', hiddenCount, 'rows hidden');
            
            // Show/hide "no results" message
            if (visibleCount === 0) {
                $('#noResultsBody').show();
            } else {
                $('#noResultsBody').hide();
            }
        }
        
        function resetTableFilters() {
            // Reset all filters
            $('#filterType').val('all');
            $('#filterDate').val('all');
            $('#searchUser').val('');
            
            // Show all rows and hide no results message
            $('#recordsTableBody tr').show();
            $('#noResultsBody').hide();
            
            console.log('Filters reset - showing all rows');
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
            
            // Initialize dynamic filter (will be empty initially)
            updateDynamicFilter();
            
            // Don't initialize chart or table on page load - show prompts instead
            // Show the select prompt messages
            $('#select-prompt-chart').show();
            $('#selectPromptBody').show();
            
            // Initialize DataTable - no pagination, search, or info
            dataTable = $('#dataTables-example').dataTable({
                "order": [[ 1, "desc" ]],  // Sort by date (column index 1) - newest first
                "paging": false,  // Disable pagination - show all records
                "searching": false,  // Disable search box
                "info": false  // Hide "Showing X to Y of Z entries" text
            });
            
            // Handle parameter change to update dynamic filter
            $('#filterByParameter').on('change', function() {
                updateDynamicFilter();
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
        
        // Print Analytics Function
        function printAnalytics() {
            // Create a new window for printing
            var printWindow = window.open('', '_blank');
            
            // Get the current filter info
            var typeFilter = $('#filterByType option:selected').text();
            var typeFilterValue = $('#filterByType').val();
            var paramFilter = $('#filterByParameter option:selected').text();
            var dateInfo = '';
            
            if ($('#filterByParameter').val() === 'date') {
                var startDate = $('#startDate').val();
                var endDate = $('#endDate').val();
                if (startDate && endDate) {
                    dateInfo = ' (' + startDate + ' to ' + endDate + ')';
                }
            } else if ($('#filterByParameter').val() === 'month') {
                var year = $('#yearSelect').val();
                if (year) {
                    dateInfo = ' (Year: ' + year + ')';
                }
            } else if ($('#filterByParameter').val() === 'quarterly') {
                var year = $('#yearSelectQuarterly').val();
                if (year) {
                    dateInfo = ' (Year: ' + year + ')';
                }
            }
            
            // Get the chart SVG
            var chartSVG = $('#analytics-chart').html();
            
            // Build legend HTML with inline styles for printing
            var legendHTML = '';
            if ($('#chart-legend').is(':visible')) {
                legendHTML = '<div style="text-align: center; margin-top: 20px; padding: 15px; background-color: #f9f9f9 !important; border-top: 1px solid #e3e3e3; -webkit-print-color-adjust: exact; print-color-adjust: exact;"><div style="display: inline-block;">';
                
                if (typeFilterValue === 'both' || typeFilterValue === 'bookings') {
                    legendHTML += '<span style="display: inline-flex; align-items: center; margin-right: 25px;">';
                    legendHTML += '<span style="display: inline-block; width: 20px; height: 20px; background-color: #5cb85c !important; margin-right: 8px; border-radius: 3px; -webkit-print-color-adjust: exact; print-color-adjust: exact;"></span>';
                    legendHTML += '<span style="font-weight: 600; color: #333;">Bookings Made</span>';
                    legendHTML += '</span>';
                }
                
                if (typeFilterValue === 'both' || typeFilterValue === 'penalties') {
                    legendHTML += '<span style="display: inline-flex; align-items: center;">';
                    legendHTML += '<span style="display: inline-block; width: 20px; height: 20px; background-color: #d9534f !important; margin-right: 8px; border-radius: 3px; -webkit-print-color-adjust: exact; print-color-adjust: exact;"></span>';
                    legendHTML += '<span style="font-weight: 600; color: #333;">Penalties Issued</span>';
                    legendHTML += '</span>';
                }
                
                legendHTML += '</div></div>';
            }
            
            // Get the table HTML and fix label colors with inline styles
            var tableHTML = $('#dataTables-example').parent().html();
            // Replace class-based labels with inline-styled spans
            tableHTML = tableHTML.replace(/class="label label-info"/g, 'style="display: inline-block; padding: 4px 10px; font-size: 12px; font-weight: 600; color: #fff; background-color: #5bc0de !important; border-radius: 3px; -webkit-print-color-adjust: exact; print-color-adjust: exact;"');
            tableHTML = tableHTML.replace(/class="label label-danger"/g, 'style="display: inline-block; padding: 4px 10px; font-size: 12px; font-weight: 600; color: #fff; background-color: #d9534f !important; border-radius: 3px; -webkit-print-color-adjust: exact; print-color-adjust: exact;"');
            tableHTML = tableHTML.replace(/class="label label-success"/g, 'style="display: inline-block; padding: 4px 10px; font-size: 12px; font-weight: 600; color: #fff; background-color: #5cb85c !important; border-radius: 3px; -webkit-print-color-adjust: exact; print-color-adjust: exact;"');
            tableHTML = tableHTML.replace(/class="label label-warning"/g, 'style="display: inline-block; padding: 4px 10px; font-size: 12px; font-weight: 600; color: #fff; background-color: #f0ad4e !important; border-radius: 3px; -webkit-print-color-adjust: exact; print-color-adjust: exact;"');
            
            // Build the print content
            var printContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>System Analytics - Print</title>
                    <link href="assets/css/bootstrap.css" rel="stylesheet" />
                    <style>
                        /* Force all colors to print - apply globally */
                        * { 
                            -webkit-print-color-adjust: exact !important; 
                            print-color-adjust: exact !important; 
                            color-adjust: exact !important; 
                        }
                        
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        h1 { color: #5a5a5a; font-size: 24px; margin-bottom: 10px; }
                        .filter-info { margin-bottom: 20px; padding: 10px; background: #f5f5f5; border-radius: 5px; }
                        .chart-section { margin-bottom: 30px; page-break-inside: avoid; }
                        .table-section { page-break-inside: avoid; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f0f0f0; }
                        
                        @media print {
                            body { margin: 0; padding: 15px; }
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <h1>SYSTEM ANALYTICS</h1>
                    <div class="filter-info">
                        <strong>Filter Type:</strong> ${typeFilter}<br>
                        <strong>Filter Parameter:</strong> ${paramFilter}${dateInfo}
                    </div>
                    <div class="chart-section">
                        <h3>Bookings Made vs Penalties Issued</h3>
                        <div>${chartSVG || 'No chart data available'}</div>
                        <div>${legendHTML || ''}</div>
                    </div>
                    <div class="table-section">
                        <h3>Records Table</h3>
                        ${tableHTML || 'No table data available'}
                    </div>
                </body>
                </html>
            `;
            
            printWindow.document.write(printContent);
            printWindow.document.close();
            
            // Wait for content to load, then print
            printWindow.onload = function() {
                setTimeout(function() {
                    printWindow.print();
                }, 500);
            };
        }
        
        // Save as PDF Function
        function saveAsPDF() {
            // Get the current filter info (same as print function)
            var typeFilter = $('#filterByType option:selected').text();
            var typeFilterValue = $('#filterByType').val();
            var paramFilter = $('#filterByParameter option:selected').text();
            var dateInfo = '';
            
            if ($('#filterByParameter').val() === 'date') {
                var startDate = $('#startDate').val();
                var endDate = $('#endDate').val();
                if (startDate && endDate) {
                    dateInfo = ' (' + startDate + ' to ' + endDate + ')';
                }
            } else if ($('#filterByParameter').val() === 'month') {
                var year = $('#yearSelect').val();
                if (year) {
                    dateInfo = ' (Year: ' + year + ')';
                }
            } else if ($('#filterByParameter').val() === 'quarterly') {
                var year = $('#yearSelectQuarterly').val();
                if (year) {
                    dateInfo = ' (Year: ' + year + ')';
                }
            }
            
            // Get the chart SVG (same as print)
            var chartSVG = $('#analytics-chart').html();
            
            // Build legend HTML with inline styles for PDF (same as print)
            var legendHTML = '';
            if ($('#chart-legend').is(':visible')) {
                legendHTML = '<div style="text-align: center; margin-top: 20px; padding: 15px; background-color: #f9f9f9 !important; border-top: 1px solid #e3e3e3; -webkit-print-color-adjust: exact; print-color-adjust: exact;"><div style="display: inline-block;">';
                
                if (typeFilterValue === 'both' || typeFilterValue === 'bookings') {
                    legendHTML += '<span style="display: inline-flex; align-items: center; margin-right: 25px;">';
                    legendHTML += '<span style="display: inline-block; width: 20px; height: 20px; background-color: #5cb85c !important; margin-right: 8px; border-radius: 3px; -webkit-print-color-adjust: exact; print-color-adjust: exact;"></span>';
                    legendHTML += '<span style="font-weight: 600; color: #333;">Bookings Made</span>';
                    legendHTML += '</span>';
                }
                
                if (typeFilterValue === 'both' || typeFilterValue === 'penalties') {
                    legendHTML += '<span style="display: inline-flex; align-items: center;">';
                    legendHTML += '<span style="display: inline-block; width: 20px; height: 20px; background-color: #d9534f !important; margin-right: 8px; border-radius: 3px; -webkit-print-color-adjust: exact; print-color-adjust: exact;"></span>';
                    legendHTML += '<span style="font-weight: 600; color: #333;">Penalties Issued</span>';
                    legendHTML += '</span>';
                }
                
                legendHTML += '</div></div>';
            }
            
            // Get the table HTML and fix label colors with inline styles (same as print)
            var tableHTML = $('#dataTables-example').parent().html();
            tableHTML = tableHTML.replace(/class="label label-info"/g, 'style="display: inline-block; padding: 4px 10px; font-size: 12px; font-weight: 600; color: #fff; background-color: #5bc0de !important; border-radius: 3px; -webkit-print-color-adjust: exact; print-color-adjust: exact;"');
            tableHTML = tableHTML.replace(/class="label label-danger"/g, 'style="display: inline-block; padding: 4px 10px; font-size: 12px; font-weight: 600; color: #fff; background-color: #d9534f !important; border-radius: 3px; -webkit-print-color-adjust: exact; print-color-adjust: exact;"');
            tableHTML = tableHTML.replace(/class="label label-success"/g, 'style="display: inline-block; padding: 4px 10px; font-size: 12px; font-weight: 600; color: #fff; background-color: #5cb85c !important; border-radius: 3px; -webkit-print-color-adjust: exact; print-color-adjust: exact;"');
            tableHTML = tableHTML.replace(/class="label label-warning"/g, 'style="display: inline-block; padding: 4px 10px; font-size: 12px; font-weight: 600; color: #fff; background-color: #f0ad4e !important; border-radius: 3px; -webkit-print-color-adjust: exact; print-color-adjust: exact;"');
            
            // Create the exact same HTML content as print
            var pdfContentHTML = `
                <div style="font-family: Arial, sans-serif; padding: 20px; background: white;">
                    <h1 style="color: #5a5a5a; font-size: 24px; margin-bottom: 10px;">SYSTEM ANALYTICS</h1>
                    <div style="margin-bottom: 20px; padding: 10px; background: #f5f5f5; border-radius: 5px;">
                        <strong>Filter Type:</strong> ${typeFilter}<br>
                        <strong>Filter Parameter:</strong> ${paramFilter}${dateInfo}
                    </div>
                    <div style="margin-bottom: 30px;">
                        <h3>Bookings Made vs Penalties Issued</h3>
                        <div>${chartSVG || 'No chart data available'}</div>
                        ${legendHTML}
                    </div>
                    <div>
                        <h3 style="margin-top: 30px;">Records Table</h3>
                        ${tableHTML || 'No table data available'}
                    </div>
                </div>
            `;
            
            // Create temporary element
            var pdfElement = document.createElement('div');
            pdfElement.innerHTML = pdfContentHTML;
            pdfElement.style.position = 'absolute';
            pdfElement.style.left = '-9999px';
            pdfElement.style.width = '210mm';
            document.body.appendChild(pdfElement);
            
            // Configure PDF options
            var opt = {
                margin: 10,
                filename: 'System_Analytics_' + new Date().toISOString().split('T')[0] + '.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { 
                    scale: 2, 
                    useCORS: true,
                    backgroundColor: '#ffffff'
                },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            
            // Generate PDF
            html2pdf().set(opt).from(pdfElement).save().then(function() {
                // Remove temporary element
                document.body.removeChild(pdfElement);
            }).catch(function(error) {
                console.error('PDF generation error:', error);
                document.body.removeChild(pdfElement);
                alert('Error generating PDF. Please try again.');
            });
        }
    </script>
    <!-- Custom Js -->
    <script src="assets/js/custom-scripts.js"></script>
</body>
</html>