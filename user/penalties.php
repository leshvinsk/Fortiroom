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
        .status-waived {
            background-color: #d9edf7;
            color: #31708f;
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
        
        /* Action Buttons */
        .btn-pay-now {
            background: #28a745;
            color: #fff;
            border: none;
            padding: 6px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }
        
        .btn-pay-now:hover {
            background: #218838;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(40, 167, 69, 0.3);
        }
        
        .btn-view-receipt {
            background: #6c757d;
            color: #fff;
            border: none;
            padding: 6px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }
        
        .btn-view-receipt:hover {
            background: #5a6268;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(108, 117, 125, 0.3);
        }
        
        /* Receipt Modal */
        .receipt-modal-overlay {
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
        
        /* Hide any date inputs inside receipt */
        .receipt-container input[type="date"],
        .receipt-modal-overlay input[type="date"] {
            display: none !important;
            visibility: hidden !important;
            width: 0 !important;
            height: 0 !important;
            position: absolute !important;
            left: -9999px !important;
        }
        
        /* Override any date input styles for receipt values */
        .receipt-value,
        .receipt-value * {
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
        }
        
        .receipt-modal-overlay.active {
            display: flex;
        }
        
        .receipt-container {
            background: #fff;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            animation: slideDown 0.3s ease;
            max-height: 90vh;
            overflow-y: auto;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        .receipt-header {
            background: #fff;
            padding: 20px 30px 10px;
            border-radius: 12px 12px 0 0;
            position: relative;
            text-align: center;
        }
        
        .receipt-header .close-receipt {
            position: absolute;
            top: 15px;
            right: 20px;
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
        
        .receipt-header .close-receipt:hover {
            color: #343a40;
        }
        
        .receipt-header h2 {
            margin: 0 0 5px 0;
            font-size: 24px;
            font-weight: 600;
            color: #1a1a1a;
        }
        
        .receipt-header p {
            margin: 0;
            color: #6c757d;
            font-size: 14px;
        }
        
        .receipt-body {
            padding: 20px 30px;
            border-top: 2px dashed #dee2e6;
            border-bottom: 2px dashed #dee2e6;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        .receipt-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f1f3f5;
        }
        
        .receipt-row:last-child {
            border-bottom: none;
        }
        
        /* Ensure no inputs appear in receipt rows */
        .receipt-row input,
        .receipt-row input[type="date"],
        .receipt-row input[type="text"] {
            display: none !important;
            visibility: hidden !important;
            position: absolute !important;
            left: -9999px !important;
        }
        
        .receipt-label {
            font-weight: 500;
            color: #495057;
            font-size: 14px;
        }
        
        .receipt-value {
            font-weight: 600;
            color: #212529;
            font-size: 14px;
            text-align: right;
        }
        
        .receipt-value::before,
        .receipt-value::after {
            content: none !important;
        }
        
        .receipt-total {
            background: #f8f9fa;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        .receipt-total .label {
            font-size: 16px;
            font-weight: 600;
            color: #212529;
        }
        
        .receipt-total .amount {
            font-size: 24px;
            font-weight: 700;
            color: #28a745;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        .receipt-footer {
            padding: 20px 30px;
            text-align: center;
        }
        
        .receipt-footer .btn-print {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 12px 32px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .receipt-footer .btn-print:hover {
            background: #0056b3;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
        }
        
        .receipt-footer .receipt-note {
            margin-top: 15px;
            font-size: 12px;
            color: #6c757d;
            line-height: 1.5;
        }
        
        .receipt-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        
        .receipt-badge.paid {
            background-color: #d4edda;
            color: #155724;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        .receipt-badge.waived {
            background-color: #d1ecf1;
            color: #0c5460;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        /* Payment Processing Overlay */
        .payment-processing-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }
        
        .payment-processing-overlay.active {
            display: flex;
        }
        
        .payment-processing-box {
            background: #fff;
            border-radius: 12px;
            padding: 40px 50px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .payment-processing-box .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .payment-processing-box h3 {
            margin: 0 0 10px 0;
            font-size: 20px;
            font-weight: 600;
            color: #1a1a1a;
        }
        
        .payment-processing-box p {
            margin: 0;
            color: #6c757d;
            font-size: 14px;
        }
        
        /* Print Styles */
        @media print {
            @page {
                size: A4;
                margin: 0.5cm;
            }
            
            html, body {
                height: auto !important;
                overflow: visible !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            /* Force all colors to print */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            /* Hide everything */
            body > * {
                display: none !important;
            }
            
            /* Show only the receipt modal */
            .receipt-modal-overlay {
                display: block !important;
                visibility: visible !important;
                background: transparent !important;
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                backdrop-filter: none !important;
            }
            
            .receipt-container {
                visibility: visible !important;
                position: relative !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                max-height: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                padding: 10px !important;
                page-break-after: avoid !important;
            }
            
            .receipt-container * {
                visibility: visible !important;
            }
            
            /* Ensure badge colors print */
            .receipt-badge,
            .receipt-badge.paid,
            .receipt-badge.waived {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            .receipt-header .close-receipt,
            .receipt-footer .btn-print {
                display: none !important;
            }
            
            .receipt-header,
            .receipt-body,
            .receipt-total,
            .receipt-footer {
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
            }
            
            /* Hide any date inputs in print */
            input[type="date"] {
                display: none !important;
                visibility: hidden !important;
            }
            
            /* Ensure no blank pages */
            #wrapper,
            #page-wrapper,
            .navbar,
            .navbar-side,
            .payment-processing-overlay {
                display: none !important;
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
                        <a href="dashboard.php"><i class="fa fa-calendar fa-fw"></i> Bookings</a>
                    </li>
                    <li>
                        <a class="active-menu" href="penalties.php"><i class="fa fa-gavel fa-fw"></i> Penalties</a>
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
                            <h1 style="margin: 0; font-size: 26px; font-weight: 400; color: #5a5a5a;">MY PENALTIES</h1>
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
                                            <option value="waived">Waived</option>
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
                                    <button class="btn btn-sm btn-default" onclick="resetFilters()">
                                        <i class="fa fa-refresh"></i> Reset Filters
                                    </button>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                        <thead>
                                            <tr>
                                                <th>Pods No.</th>
                                                <th>Violation Type</th>
                                                <th>Date & Time</th>
                                                <th>Penalty Amount</th>
                                                <th>Status</th>
                                                <th>Action</th>
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
    
    <!-- Payment Processing Overlay -->
    <div id="paymentProcessing" class="payment-processing-overlay">
        <div class="payment-processing-box">
            <div class="spinner"></div>
            <h3>Processing Payment</h3>
            <p>Please wait while we process your payment...</p>
        </div>
    </div>
    
    <!-- Receipt Modal -->
    <div id="receiptModal" class="receipt-modal-overlay">
        <div class="receipt-container">
            <div class="receipt-header">
                <button class="close-receipt" onclick="closeReceipt()">&times;</button>
                <h2>Payment Receipt</h2>
                <p>FORTIROOM - Smart Space Management</p>
            </div>
            <div class="receipt-body">
                <div class="receipt-row">
                    <span class="receipt-label">Receipt No:</span>
                    <span class="receipt-value" id="receiptNo">-</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Date & Time:</span>
                    <span class="receipt-value" id="receiptDateTime">-</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Pod Number:</span>
                    <span class="receipt-value" id="receiptPod">-</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Violation Type:</span>
                    <span class="receipt-value" id="receiptViolation">-</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Payment Status:</span>
                    <span class="receipt-value" id="receiptStatus"></span>
                </div>
            </div>
            <div class="receipt-total">
                <span class="label">Total Amount:</span>
                <span class="amount" id="receiptAmount">$0.00</span>
            </div>
            <div class="receipt-footer">
                <button class="btn-print" onclick="printReceipt()">
                    <i class="fa fa-print"></i> Print Receipt
                </button>
                <p class="receipt-note">
                    Thank you for your payment. This is an official receipt from FORTIROOM.<br>
                    For any queries, please contact our support team.
                </p>
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
        
        // Penalty data - 3 records (user's penalties only)
        // Ordered by: 1) Status (pending first), 2) Date (earliest to latest)
        var penaltiesData = [
            { room: 1, violationType: 'Late Checkout', date: '2025-10-20', dateTime: '2025-10-20 11:45 AM', amount: '$15.00', status: 'pending', statusOrder: 1 },
            { room: 2, violationType: 'Late Cancellation', date: '2025-10-18', dateTime: '2025-10-18 05:15 PM', amount: '$10.00', status: 'paid', statusOrder: 2 },
            { room: 3, violationType: 'No Show', date: '2025-10-22', dateTime: '2025-10-22 02:30 PM', amount: '$25.00', status: 'paid', statusOrder: 2 }
        ];
        
        function populatePenaltiesTable() {
            var tbody = $('#penaltiesTableBody');
            tbody.empty();
            
            penaltiesData.forEach(function(penalty) {
                var statusClass = 'status-' + penalty.status;
                var statusText = penalty.status.charAt(0).toUpperCase() + penalty.status.slice(1);
                
                // Determine action button based on status
                var actionButton = '';
                if (penalty.status === 'pending') {
                    actionButton = '<button class="btn-pay-now" onclick="payNow(' + penalty.room + ', \'' + penalty.violationType + '\')"><i class="fa fa-credit-card"></i> Pay Now</button>';
                } else if (penalty.status === 'paid' || penalty.status === 'waived') {
                    actionButton = '<button class="btn-view-receipt" onclick="viewReceipt(' + penalty.room + ', \'' + penalty.violationType + '\')"><i class="fa fa-file-text-o"></i> View Receipt</button>';
                }
                
                var row = '<tr data-status="' + penalty.status + '" data-date="' + penalty.date + 
                    '" data-violation="' + penalty.violationType + '" data-status-order="' + penalty.statusOrder + '">' +
                    '<td>' + penalty.room + '</td>' +
                    '<td>' + penalty.violationType + '</td>' +
                    '<td>' + penalty.dateTime + '</td>' +
                    '<td>' + penalty.amount + '</td>' +
                    '<td><span class="status-badge ' + statusClass + '">' + statusText + '</span></td>' +
                    '<td>' + actionButton + '</td>' +
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
            var dateFilter = $('#filterDate').val();
            var violationFilter = $('#filterViolation').val();
            
            console.log('FILTERING - Status:', statusFilter, '| Date:', dateFilter, '| Violation:', violationFilter);
            
            // Show all rows first
            $('#penaltiesTableBody tr').show();
            
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
            
            var visibleCount = $('#penaltiesTableBody tr:visible').length;
            var hiddenCount = $('#penaltiesTableBody tr:hidden').length;
            console.log('Results:', visibleCount, 'rows shown,', hiddenCount, 'rows hidden');
            
            // Show/hide "no results" message
            if (visibleCount === 0) {
                $('#noResultsBody').show();
            } else {
                $('#noResultsBody').hide();
            }
        }
        
        function resetFilters() {
            // Reset dropdowns
            $('#filterStatus').val('all');
            $('#filterDate').val('');
            $('#filterViolation').val('all');
            
            // Show all rows and hide no results message
            $('#penaltiesTableBody tr').show();
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
            
            // Request notification permission on page load
            if ("Notification" in window && Notification.permission === "default") {
                Notification.requestPermission();
            }
            
            // Populate table with penalties
            populatePenaltiesTable();
            
            // Initialize DataTable - no pagination, search, or info
            dataTable = $('#dataTables-example').dataTable({
                "order": [],  // No default sorting - maintain manual order (pending first, then by date)
                "paging": false,  // Disable pagination - show all records
                "searching": false,  // Disable search box
                "info": false  // Hide "Showing X to Y of Z entries" text
            });
            
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
        function openPenaltyModal() {
            $('#penaltyModal').addClass('active');
            // Prevent body scroll when modal is open
            $('body').css('overflow', 'hidden');
        }
        
        function closePenaltyModal() {
            $('#penaltyModal').removeClass('active');
            // Reset form fields
            $('#lateCancellationRate').val('');
            $('#noShowRate').val('');
            $('#lateCheckoutRate').val('');
            // Restore body scroll
            $('body').css('overflow', '');
        }
        
        function setPenaltyRates() {
            var lateCancellation = $('#lateCancellationRate').val();
            var noShow = $('#noShowRate').val();
            var lateCheckout = $('#lateCheckoutRate').val();
            
            // Check if at least one field is filled
            if (!lateCancellation && !noShow && !lateCheckout) {
                alert('Please enter at least one penalty rate to update.');
                return;
            }
            
            // Close the modal
            closePenaltyModal();
            
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
            
            // Here you would typically make an AJAX call to save the rates to the database
            console.log('Penalty rates updated:', {
                lateCancellation: lateCancellation || 'No change',
                noShow: noShow || 'No change',
                lateCheckout: lateCheckout || 'No change'
            });
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
        
        // Action Button Functions
        function payNow(room, violationType) {
            // Show payment processing message
            if (confirm('Payment processing for:\nPod: ' + room + '\nViolation: ' + violationType + '\n\nRedirecting to payment gateway...\n\nClick OK to proceed with payment.')) {
                // Show loading overlay
                $('#paymentProcessing').addClass('active');
                $('body').css('overflow', 'hidden');
                
                // Simulate payment processing delay
                setTimeout(function() {
                    // Update the penalty status in the data
                    var penalty = penaltiesData.find(function(p) {
                        return p.room === room && p.violationType === violationType;
                    });
                    
                    if (penalty) {
                        // Update status to paid
                        penalty.status = 'paid';
                        penalty.statusOrder = 2;
                        
                        // Re-populate the table with updated data
                        populatePenaltiesTable();
                        
                        // Hide loading overlay
                        $('#paymentProcessing').removeClass('active');
                        $('body').css('overflow', '');
                        
                        // Show success message
                        alert('Payment Successful!\n\nPenalty has been paid.\nYou can now view your receipt.');
                        
                        // Here you would typically make an AJAX call to update the database
                        // $.ajax({ url: 'update_penalty.php', method: 'POST', data: { room: room, type: violationType, status: 'paid' } });
                    } else {
                        // Hide loading overlay if penalty not found
                        $('#paymentProcessing').removeClass('active');
                        $('body').css('overflow', '');
                        alert('Error: Penalty not found.');
                    }
                }, 2000); // 2 second delay to simulate payment processing
            }
        }
        
        function viewReceipt(room, violationType) {
            // Find the penalty data for this receipt
            var penalty = penaltiesData.find(function(p) {
                return p.room === room && p.violationType === violationType;
            });
            
            if (!penalty) {
                alert('Receipt not found');
                return;
            }
            
            // Clear all receipt fields first - use multiple methods to ensure clean slate
            $('#receiptNo').html('').text('');
            $('#receiptDateTime').html('').text('');
            $('#receiptPod').html('').text('');
            $('#receiptViolation').html('').text('');
            $('#receiptStatus').html('').text('').empty();
            $('#receiptAmount').html('').text('');
            
            // Remove any child elements that might have been added
            $('#receiptStatus').children().remove();
            
            // Generate a receipt number (in real app, this would come from database)
            var receiptNumber = 'RCP-' + penalty.date.replace(/-/g, '') + '-' + room;
            
            // Populate receipt data
            $('#receiptNo').text(receiptNumber);
            $('#receiptDateTime').text(penalty.dateTime);
            $('#receiptPod').text('Pod ' + penalty.room);
            $('#receiptViolation').text(penalty.violationType);
            
            // Set status with badge - clear first, then set
            var statusBadgeClass = penalty.status === 'paid' ? 'paid' : 'waived';
            var statusText = penalty.status.charAt(0).toUpperCase() + penalty.status.slice(1);
            var statusBadge = '<span class="receipt-badge ' + statusBadgeClass + '">' + statusText + '</span>';
            $('#receiptStatus').html(statusBadge);
            
            $('#receiptAmount').text(penalty.amount);
            
            // Show the receipt modal
            $('#receiptModal').addClass('active');
            $('body').css('overflow', 'hidden');
            
            // Additional cleanup: Remove any input elements that might have been injected
            setTimeout(function() {
                $('#receiptModal input').remove();
                $('#receiptModal input[type="date"]').remove();
                $('.receipt-container input').remove();
            }, 50);
        }
        
        function closeReceipt() {
            $('#receiptModal').removeClass('active');
            $('body').css('overflow', '');
        }
        
        function printReceipt() {
            window.print();
        }
        
        // Close receipt when clicking outside of it
        $(document).on('click', '#receiptModal', function(e) {
            if (e.target.id === 'receiptModal') {
                closeReceipt();
            }
        });
        
        // Close receipt with Escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#receiptModal').hasClass('active')) {
                closeReceipt();
            }
        });
    </script>
    <!-- Custom Js -->
    <script src="assets/js/custom-scripts.js"></script>
</body>
</html>