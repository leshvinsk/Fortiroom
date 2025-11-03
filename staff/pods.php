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
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        .pod-number {
            font-size: 24px;
            font-weight: bold;
            color: #333;
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
        // Mock data for 4 pods
        var podsData = [
            {
                id: 1,
                status: 'occupied',
                temperature: 23.5,
                fanSpeed: 4,
                aqi: 45,
                suspended: false,
                savedState: null
            },
            {
                id: 2,
                status: 'occupied',
                temperature: 24.0,
                fanSpeed: 3,
                aqi: 36,
                suspended: false,
                savedState: null
            },
            {
                id: 3,
                status: 'occupied',
                temperature: 22.8,
                fanSpeed: 5,
                aqi: 47,
                suspended: false,
                savedState: null
            },
            {
                id: 4,
                status: 'occupied',
                temperature: 25.2,
                fanSpeed: 1,
                aqi: 39,
                suspended: false,
                savedState: null
            }
        ];
        
        // Modal functions
        function openCreatePodModal() {
            $('#createPodModal').addClass('active');
        }
        
        function closeCreatePodModal() {
            $('#createPodModal').removeClass('active');
            $('#createPodForm')[0].reset();
        }
        
        // Create new pod
        function createNewPod(event) {
            event.preventDefault();
            
            var podName = $('#podName').val().trim();
            var podCapacity = parseInt($('#podCapacity').val());
            var podHardwareId = $('#podHardwareId').val().trim();
            
            // Validate inputs
            if (!podName || !podCapacity || !podHardwareId) {
                alert('Please fill in all required fields.');
                return;
            }
            
            // Generate new pod ID
            var maxId = Math.max(...podsData.map(p => p.id));
            var newPodId = maxId + 1;
            
            // Create new pod object
            var newPod = {
                id: newPodId,
                name: podName,
                capacity: podCapacity,
                hardwareId: podHardwareId,
                status: 'idle',
                temperature: 22.0 + Math.random() * 3, // Random initial temp between 22-25°C
                fanSpeed: 0, // Start with fan off
                aqi: 30 + Math.floor(Math.random() * 20), // Random AQI between 30-50
                suspended: false,
                savedState: null
            };
            
            // Add to pods array
            podsData.push(newPod);
            
            // Close modal and reset form
            closeCreatePodModal();
            
            // Re-render cards
            renderPodCards();
            
            // Show success message
            alert('✓ Pod created successfully!\n\nPod Name: ' + podName + '\nCapacity: ' + podCapacity + ' person(s)\nHardware ID: ' + podHardwareId + '\nStatus: IDLE\n\nThe pod is now ready for operation.');
            
            console.log('New pod created:', newPod);
        }
        
        // Close modal when clicking outside
        $(document).on('click', function(event) {
            if ($(event.target).is('#createPodModal')) {
                closeCreatePodModal();
            }
        });
        
        // Get AQI class based on value
        function getAQIClass(aqi) {
            if (aqi <= 50) return 'aqi-good';
            if (aqi <= 100) return 'aqi-moderate';
            return 'aqi-poor';
        }
        
        // Get AQI label based on value
        function getAQILabel(aqi) {
            if (aqi <= 50) return 'Good';
            if (aqi <= 100) return 'Moderate';
            return 'Poor';
        }
        
        // Change fan speed
        function changeFanSpeed(podId, change) {
            var pod = podsData.find(p => p.id === podId);
            if (pod && !pod.suspended) {
                var newSpeed = pod.fanSpeed + change;
                if (newSpeed >= 0 && newSpeed <= 5) {
                    pod.fanSpeed = newSpeed;
                    updatePodCard(podId);
                }
            }
        }
        
        // Suspend pod
        function suspendPod(podId) {
            var pod = podsData.find(p => p.id === podId);
            if (pod && !pod.suspended) {
                // Show confirmation popup
                if (confirm('Suspend Pod ' + podId + '?\n\n⚠️ Warning:\n• All bookings for this pod will be removed\n• Email notifications will be sent to all affected users\n• Pod systems will be shut down for maintenance\n\nDo you want to proceed?')) {
                    // Save current state
                    pod.savedState = {
                        status: pod.status,
                        temperature: pod.temperature,
                        fanSpeed: pod.fanSpeed,
                        aqi: pod.aqi
                    };
                    
                    // Suspend the pod
                    pod.suspended = true;
                    pod.status = 'suspended';
                    pod.temperature = null;
                    pod.fanSpeed = null;
                    pod.aqi = null;
                    
                    renderPodCards();
                    console.log('Pod ' + podId + ' suspended');
                    
                    // Show success message
                    alert('✓ Pod ' + podId + ' has been suspended.\n\nAll bookings have been removed and email notifications with vouchers have been sent to users.');
                }
            }
        }
        
        // Unsuspend pod
        function unsuspendPod(podId) {
            var pod = podsData.find(p => p.id === podId);
            if (pod && pod.suspended && pod.savedState) {
                // Show notification popup
                alert('Operate Pod ' + podId + '\n\n📧 Email notifications will be sent to all users to acknowledge the pod\'s new status.\n\nThe pod will be set to IDLE and ready for operation.');
                
                // Restore saved state but set status to idle
                pod.status = 'idle';
                pod.temperature = pod.savedState.temperature;
                pod.fanSpeed = pod.savedState.fanSpeed;
                pod.aqi = pod.savedState.aqi;
                pod.suspended = false;
                pod.savedState = null;
                
                renderPodCards();
                console.log('Pod ' + podId + ' unsuspended - status set to idle');
                
                // Show success message
                setTimeout(function() {
                    alert('✓ Pod ' + podId + ' is now operational!\n\nStatus: IDLE\nEmail notifications have been sent to users.');
                }, 100);
            }
        }
        
        // Delete pod
        function deletePod(podId) {
            var pod = podsData.find(p => p.id === podId);
            if (pod && pod.suspended) {
                // Show confirmation dialog
                if (confirm('⚠️ DELETE POD ' + podId + '?\n\n🚨 WARNING: This action is PERMANENT and CANNOT be undone!\n\n• Pod ' + podId + ' will be completely removed from the system\n• All historical data will be lost\n• This pod number cannot be reused\n\nAre you absolutely sure you want to DELETE Pod ' + podId + '?')) {
                    // Double confirmation
                    if (confirm('FINAL CONFIRMATION\n\nClick OK to permanently delete Pod ' + podId + '\nClick Cancel to keep the pod')) {
                        podsData = podsData.filter(p => p.id !== podId);
                        renderPodCards();
                        console.log('Pod ' + podId + ' deleted');
                        
                        // Show success message
                        alert('✓ Pod ' + podId + ' has been permanently deleted from the system.');
                    }
                }
            }
        }
        
        // Update a single pod card
        function updatePodCard(podId) {
            var pod = podsData.find(p => p.id === podId);
            if (pod) {
                $('#pod-' + podId + '-fanspeed').text(pod.fanSpeed);
                $('#pod-' + podId + '-btn-minus').prop('disabled', pod.fanSpeed === 0);
                $('#pod-' + podId + '-btn-plus').prop('disabled', pod.fanSpeed === 5);
            }
        }
        
        // Generate pod card HTML
        function generatePodCard(pod) {
            var aqiClass = pod.suspended ? '' : getAQIClass(pod.aqi);
            var aqiLabel = pod.suspended ? '' : getAQILabel(pod.aqi);
            var grayedClass = pod.suspended ? 'grayed-out' : '';
            
            // Temperature display
            var tempDisplay = pod.suspended ? '<span class="grayed-out">NULL</span>' : `${pod.temperature.toFixed(1)}°C`;
            
            // Fan speed display
            var fanSpeedHTML = '';
            if (pod.suspended) {
                fanSpeedHTML = '<div class="info-value grayed-out">NULL</div>';
            } else {
                fanSpeedHTML = `
                    <div class="fan-control">
                        <button class="fan-btn" id="pod-${pod.id}-btn-minus" 
                                onclick="changeFanSpeed(${pod.id}, -1)"
                                ${pod.fanSpeed === 0 ? 'disabled' : ''}>
                            −
                        </button>
                        <span class="fan-speed-display" id="pod-${pod.id}-fanspeed">${pod.fanSpeed}</span>
                        <button class="fan-btn" id="pod-${pod.id}-btn-plus" 
                                onclick="changeFanSpeed(${pod.id}, 1)"
                                ${pod.fanSpeed === 5 ? 'disabled' : ''}>
                            +
                        </button>
                    </div>
                `;
            }
            
            // AQI display
            var aqiDisplay = pod.suspended ? '<span class="grayed-out">NULL</span>' : `${pod.aqi} <small>(${aqiLabel})</small>`;
            
            // Action buttons
            var actionButtons = '';
            if (pod.suspended) {
                actionButtons = `
                    <div class="pod-actions">
                        <button class="pod-action-btn btn-unsuspend" onclick="unsuspendPod(${pod.id})">
                            <i class="fa fa-play"></i> Operate
                        </button>
                        <button class="pod-action-btn btn-delete" onclick="deletePod(${pod.id})">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </div>
                `;
            } else {
                actionButtons = `
                    <div class="pod-actions">
                        <button class="pod-action-btn btn-suspend" onclick="suspendPod(${pod.id})">
                            <i class="fa fa-pause"></i> Suspend
                        </button>
                        <button class="pod-action-btn btn-delete" onclick="deletePod(${pod.id})" disabled>
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </div>
                `;
            }
            
            // Pod name display (use custom name if exists, otherwise "Pod X")
            var podDisplayName = pod.name || `Pod ${pod.id}`;
            
            return `
                <div class="col-md-6 col-lg-3 col-sm-6">
                    <div class="pod-card ${pod.status}">
                        <div class="pod-header">
                            <div class="pod-number">
                                <i class="fa fa-building"></i> ${podDisplayName}
                            </div>
                            <div class="pod-status ${pod.status}">${pod.status}</div>
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
        function simulateUpdates() {
            podsData.forEach(function(pod) {
                // Skip suspended pods
                if (!pod.suspended) {
                    // Randomly fluctuate temperature slightly (±0.2°C)
                    pod.temperature += (Math.random() - 0.5) * 0.4;
                    pod.temperature = Math.round(pod.temperature * 10) / 10;
                    
                    // Randomly fluctuate AQI slightly (±2)
                    pod.aqi += Math.floor((Math.random() - 0.5) * 4);
                    pod.aqi = Math.max(20, Math.min(150, pod.aqi)); // Keep in range 20-150
                }
            });
            
            renderPodCards();
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
            
            // Initial render
            renderPodCards();
            
            // Update pod data every 5 seconds for realistic monitoring
            setInterval(simulateUpdates, 5000);
            
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