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
        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
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
        }
        
        .create-booking-btn i {
            font-size: 16px;
        }
        
        .create-booking-btn:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
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
                            <button class="create-booking-btn" onclick="openCreateBookingModal()">
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
                                            <option value="in-progress-occupied">In-Progress (Occupied)</option>
                                            <option value="in-progress-checkout">In-Progress (Check-Out)</option>
                                            <option value="completed">Completed</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="filterPod">Pod:</label>
                                        <select id="filterPod" class="form-control" style="display: inline-block; width: 140px;">
                                            <option value="all">All Pods</option>
                                            <option value="1">Pod 1</option>
                                            <option value="2">Pod 2</option>
                                            <option value="3">Pod 3</option>
                                            <option value="4">Pod 4</option>
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
                        <input type="date" id="bookingDate" name="bookingDate" required min="" placeholder="Select date" style="color-scheme: light;">
                        <small style="color: #666; font-size: 13px; margin-top: 5px; display: block;">Format: DD-MM-YYYY</small>
                    </div>
                    
                    <div class="create-booking-form-group">
                        <label for="checkInTime">Check-In Time <span class="required">*</span></label>
                        <select id="checkInTime" required onchange="updateCheckOutOptions()">
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
        var currentUser = 'marcus_chen87';
        
        // Pod capacity information
        // Pod 1-3: Max 6 people
        // Pod 4: Max 8 people
        function getPodCapacity(podNumber) {
            return podNumber === 4 ? 8 : 6;
        }
        
        // User's booking data - 3 bookings with different statuses (arranged by earliest check-in time first)
        // Peak Hours (8:00 AM - 5:00 PM): Max 1 hour booking
        // Off-Peak Hours (Before 8:00 AM or After 5:00 PM): Can book longer
        var bookingsData = [
            { id: 'BK-001', date: '2025-10-24', room: 3, checkIn: '09:00', checkOut: '10:00', duration: '1 hour', occupants: 2 },   // Completed - Peak hours, max 1 hour
            { id: 'BK-002', date: '2025-10-24', room: 1, checkIn: '14:00', checkOut: '15:00', duration: '1 hour', occupants: 3 },  // In-Progress (Occupied) - Peak hours, max 1 hour
            { id: 'BK-003', date: '2025-10-24', room: 2, checkIn: '18:00', checkOut: '20:00', duration: '2 hours', occupants: 4 }  // Upcoming - Off-peak, can be 2 hours
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
                // Between check-in and check-out window (occupied)
                return 'in-progress-occupied';
            } else if (now >= checkOutStart && now < checkOutDateTime) {
                // Within check-out window (15 min before to check-out time)
                return 'in-progress-checkout';
            } else {
                // After check-out time
                return 'completed';
            }
        }
        
        function populateBookingsTable() {
            var tbody = $('#bookingsTableBody');
            tbody.empty();
            
            bookingsData.forEach(function(booking) {
                var status = getBookingStatus(booking.date, booking.checkIn, booking.checkOut);
                var statusClass = 'status-' + status;
                var statusText = '';
                
                // Format status text
                switch(status) {
                    case 'upcoming':
                        statusText = 'Upcoming';
                        break;
                    case 'in-progress-checkin':
                        statusText = 'In-Progress (Check-In)';
                        break;
                    case 'in-progress-occupied':
                        statusText = 'In-Progress (Occupied)';
                        break;
                    case 'in-progress-checkout':
                        statusText = 'In-Progress (Check-Out)';
                        break;
                    case 'completed':
                        statusText = 'Completed';
                        break;
                }
                
                var row = '<tr data-status="' + status + '" data-date="' + booking.date + '" data-pod="' + booking.room + '" data-booking-id="' + booking.id + '">' +
                    '<td>' + formatDate(booking.date) + '</td>' +
                    '<td>Pod ' + booking.room + '</td>' +
                    '<td data-order="' + booking.checkIn + '">' + convertTo12Hour(booking.checkIn) + '</td>' +
                    '<td data-order="' + booking.checkOut + '">' + convertTo12Hour(booking.checkOut) + '</td>' +
                    '<td>' + booking.duration + '</td>' +
                    '<td><span class="status-badge ' + statusClass + '">' + statusText + '</span></td>' +
                    '<td>' +
                        '<div class="action-buttons">' +
                            '<button class="btn btn-info btn-sm btn-view" onclick="openBookingModal(\'' + booking.id + '\')"><i class="fa fa-eye"></i> View</button>' +
                            '<button class="btn btn-danger btn-sm btn-delete" onclick="deleteBooking(\'' + booking.id + '\')"><i class="fa fa-trash"></i> Delete</button>' +
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
            
            // Format status text
            switch(status) {
                case 'upcoming':
                    statusText = 'Upcoming';
                    statusClass = 'status-upcoming';
                    break;
                case 'in-progress-checkin':
                    statusText = 'In-Progress (Check-In)';
                    statusClass = 'status-in-progress-checkin';
                    break;
                case 'in-progress-occupied':
                    statusText = 'In-Progress (Occupied)';
                    statusClass = 'status-in-progress-occupied';
                    break;
                case 'in-progress-checkout':
                    statusText = 'In-Progress (Check-Out)';
                    statusClass = 'status-in-progress-checkout';
                    break;
                case 'completed':
                    statusText = 'Completed';
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
                        <div class="detail-value">Pod ${booking.room}</div>
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
                    Pod ${booking.room} Maximum Capacity: ${podCapacity} People
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
            $('#createBookingModal').addClass('active');
            $('body').css('overflow', 'hidden');
            
            // Set minimum date to today
            var today = new Date();
            var year = today.getFullYear();
            var month = String(today.getMonth() + 1).padStart(2, '0');
            var day = String(today.getDate()).padStart(2, '0');
            var todayFormatted = year + '-' + month + '-' + day;
            $('#bookingDate').attr('min', todayFormatted);
            
            // Reset form
            resetCreateBookingForm();
        }
        
        function closeCreateBookingModal() {
            $('#createBookingModal').removeClass('active');
            $('body').css('overflow', '');
            resetCreateBookingForm();
        }
        
        function resetCreateBookingForm() {
            $('#createBookingForm')[0].reset();
            $('#checkOutTime').prop('disabled', true).html('<option value="">Select check-out time...</option>');
            $('#peakHourWarning').hide();
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
            
            allTimes.forEach(function(time) {
                var timeParts = time.split(':');
                var hour = parseInt(timeParts[0]);
                var minute = parseInt(timeParts[1]);
                
                // Check if this time is after check-in and within allowed range
                var timeInMinutes = hour * 60 + minute;
                var checkInInMinutes = checkInHour * 60 + checkInMinute;
                var maxCheckOutInMinutes = maxCheckOutHour * 60 + maxCheckOutMinute;
                
                if (timeInMinutes > checkInInMinutes && timeInMinutes <= maxCheckOutInMinutes) {
                    checkOutOptions += '<option value="' + time + '">' + convertTo12Hour(time) + '</option>';
                }
            });
            
            $('#checkOutTime').prop('disabled', false).html(checkOutOptions);
        }
        
        // Check pod availability
        function checkAvailability() {
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
            today.setHours(0, 0, 0, 0); // Reset time to start of day
            var selectedDate = new Date(bookingDate + 'T00:00:00');
            
            if (selectedDate < today) {
                alert('⚠️ Invalid Date\n\nYou cannot make a booking for a past date.\n\nPlease select today or a future date.');
                return;
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
            
            // Check for duplicate booking (exact same time slot)
            var duplicateBooking = bookingsData.find(function(b) {
                return b.date === bookingDate && b.checkIn === checkIn && b.checkOut === checkOut;
            });
            
            if (duplicateBooking) {
                alert('⚠️ Duplicate Booking\n\nYou already have a booking for this exact time slot.\n\nBooking ID: ' + duplicateBooking.id + '\nDate: ' + formatDate(duplicateBooking.date) + '\nTime: ' + convertTo12Hour(checkIn) + ' - ' + convertTo12Hour(checkOut));
                return;
            }
            
            // Check for overlapping bookings (user cannot be in two pods at the same time)
            var overlappingBooking = bookingsData.find(function(b) {
                if (b.date !== bookingDate) {
                    return false;
                }
                
                // Check if the new booking times overlap with any existing booking
                // Overlap occurs if: new checkIn < existing checkOut AND new checkOut > existing checkIn
                return (checkIn < b.checkOut && checkOut > b.checkIn);
            });
            
            if (overlappingBooking) {
                alert('⚠️ Overlapping Booking Detected\n\nYou cannot create overlapping bookings.\n\nYou already have a booking:\n\nBooking ID: ' + overlappingBooking.id + '\nPod: Pod ' + overlappingBooking.room + '\nTime: ' + convertTo12Hour(overlappingBooking.checkIn) + ' - ' + convertTo12Hour(overlappingBooking.checkOut) + '\n\nPlease select a different time slot.');
                return;
            }
            
            // Determine which pods to check based on occupancy
            var podsToCheck = [];
            if (numberOfPeople <= 6) {
                // Prioritize Pods 1-3
                podsToCheck = [1, 2, 3];
            } else {
                // Only Pod 4 for 7-8 people
                podsToCheck = [4];
            }
            
            // Find available pods
            var availablePods = [];
            podsToCheck.forEach(function(podNum) {
                // Check if pod is available at this time
                var isAvailable = !bookingsData.some(function(b) {
                    if (b.room !== podNum || b.date !== bookingDate) {
                        return false;
                    }
                    
                    // Check if times overlap
                    var existingCheckIn = b.checkIn;
                    var existingCheckOut = b.checkOut;
                    
                    return (checkIn < existingCheckOut && checkOut > existingCheckIn);
                });
                
                if (isAvailable) {
                    var capacity = getPodCapacity(podNum);
                    if (numberOfPeople <= capacity) {
                        availablePods.push(podNum);
                    }
                }
            });
            
            // Show results
            if (availablePods.length === 0) {
                alert('⚠️ No Pods Available\n\nSorry, there are no pods available at these times.\n\nPlease select a different time slot or date.');
                return;
            }
            
            // Automatically assign the first available pod
            var assignedPod = availablePods[0];
            
            // Calculate duration
            var duration = calculateDuration(checkIn, checkOut);
            
            // Generate new booking ID
            var maxId = 0;
            if (bookingsData.length > 0) {
                maxId = Math.max(...bookingsData.map(b => parseInt(b.id.split('-')[1])));
            }
            var newBookingId = 'BK-' + String(maxId + 1).padStart(3, '0');
            
            // Create new booking
            var newBooking = {
                id: newBookingId,
                date: bookingDate,
                room: assignedPod,
                checkIn: checkIn,
                checkOut: checkOut,
                duration: duration,
                occupants: numberOfPeople
            };
            
            // Add to bookings array
            bookingsData.push(newBooking);
            
            // Sort bookings by date and check-in time
            bookingsData.sort(function(a, b) {
                if (a.date !== b.date) {
                    return a.date.localeCompare(b.date);
                }
                return a.checkIn.localeCompare(b.checkIn);
            });
            
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
            
            // Show success message
            alert('✓ Booking Created Successfully!\n\nBooking ID: ' + newBookingId + '\nDate: ' + formatDate(bookingDate) + '\nPod: Pod ' + assignedPod + '\nTime: ' + convertTo12Hour(checkIn) + ' - ' + convertTo12Hour(checkOut) + '\nOccupants: ' + numberOfPeople + ' people');
            
            console.log('New booking created:', newBooking);
        }
        
        function calculateDuration(checkIn, checkOut) {
            var start = parseInt(checkIn.split(':')[0]);
            var end = parseInt(checkOut.split(':')[0]);
            var hours = end - start;
            
            if (hours === 1) {
                return '1 hour';
            } else if (hours === 1.5) {
                return '1.5 hours';
            } else {
                return hours + ' hours';
            }
        }
        
        // Delete booking function
        function deleteBooking(bookingId) {
            var booking = bookingsData.find(b => b.id === bookingId);
            if (!booking) return;
            
            var now = new Date();
            var checkInDateTime = new Date(booking.date + 'T' + booking.checkIn + ':00');
            
            // Calculate time difference in milliseconds
            var timeDiff = checkInDateTime - now;
            var oneHourInMs = 60 * 60 * 1000; // 1 hour in milliseconds
            
            // Check if deletion is within 1 hour before booking starts
            var isWithinOneHour = timeDiff > 0 && timeDiff <= oneHourInMs;
            
            var confirmMessage = '';
            var successMessage = '';
            
            if (isWithinOneHour) {
                confirmMessage = '⚠️ Delete Booking?\n\nYou are deleting this booking less than 1 hour before it starts.\n\nCancellation charges will apply.\n\nDo you want to proceed?';
                successMessage = '✓ Booking Deleted\n\nYour booking has been cancelled.\nCancellation charges apply.';
            } else {
                confirmMessage = 'Delete Booking?\n\nAre you sure you want to delete this booking?\n\nBooking ID: ' + bookingId;
                successMessage = '✓ Booking Deleted\n\nYour booking has been successfully cancelled.';
            }
            
            // Show confirmation dialog
            if (confirm(confirmMessage)) {
                // Remove booking from array
                var index = bookingsData.findIndex(b => b.id === bookingId);
                if (index !== -1) {
                    bookingsData.splice(index, 1);
                    
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
                    
                    console.log('Booking ' + bookingId + ' deleted. Remaining bookings:', bookingsData.length);
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
            
            // Populate table with user's bookings
            populateBookingsTable();
            
            // Initialize DataTable - no pagination, search, or info
            dataTable = $('#dataTables-example').dataTable({
                "order": [[ 2, "asc" ]],  // Sort by check-in time (column index 2) - earliest first
                "paging": false,  // Disable pagination - show all records
                "searching": false,  // Disable search box
                "info": false  // Hide "Showing X to Y of Z entries" text
            });
            
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
            
            // Update status every 30 seconds
            setInterval(function() {
                populateBookingsTable();
                dataTable.fnDestroy();
                dataTable = $('#dataTables-example').dataTable({
                    "order": [[ 2, "asc" ]],  // Sort by check-in time - earliest first
                    "paging": false,
                    "searching": false,
                    "info": false
                });
                applyFilters(); // Reapply filters after refresh
            }, 30000); // Update every 30 seconds
            
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