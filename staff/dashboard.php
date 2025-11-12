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
        .status-in-progress-checkin {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-in-progress-occupied {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .status-in-progress-checkout {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-completed {
            background-color: #f5f5f5;
            color: #777;
        }
        /* Legacy support for 'ongoing' status */
        .status-ongoing {
            background-color: #dff0d8;
            color: #3c763d;
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
                        <a class="active-menu" href="dashboard.php"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
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
                            <h1 style="margin: 0; font-size: 26px; font-weight: 400; color: #5a5a5a;">SYSTEM DASHBOARD</h1>
                        </div>
                    </div>
                </div>
                <!-- /. ROW  -->
                
                <!-- Dashboard Cards -->
                <div class="row dashboard-cards">
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card occupancy">
                            <i class="fa fa-percent stat-icon"></i>
                            <h3>Occupancy Rate</h3>
                            <div class="stat-value" id="occupancyRate">0%</div>
                            <div class="stat-label">Current occupancy</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card bookings">
                            <i class="fa fa-calendar-check-o stat-icon"></i>
                            <h3>No. of Bookings</h3>
                            <div class="stat-value" id="totalBookings">0</div>
                            <div class="stat-label">Today's bookings</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card penalties">
                            <i class="fa fa-exclamation-triangle stat-icon"></i>
                            <h3>Penalties Issued</h3>
                            <div class="stat-value" id="totalPenalties">0</div>
                            <div class="stat-label">Active penalties</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card room-status">
                            <i class="fa fa-bed stat-icon"></i>
                            <h3>Pods Status</h3>
                            <div class="stat-value" id="roomStatus">Normal</div>
                            <div class="stat-label">Operational</div>
                        </div>
                    </div>
                </div>
                <!-- /. Dashboard Cards -->
                
                <div class="row">
                    <div class="col-md-12">
                        <!-- Bookings Table -->
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4>Booking Records</h4>
                            </div>
                            <div class="panel-body">
                                <!-- Filter Controls -->
                                <div class="filter-controls">
                                    <div>
                                        <label for="filterStatus">Status:</label>
                                        <select id="filterStatus" class="form-control" style="display: inline-block; width: 180px;">
                                            <option value="all">All Status</option>
                                            <option value="upcoming">Upcoming</option>
                                            <option value="in-progress-checkin">In-Progress (Check-In)</option>
                                            <option value="in-progress-occupied">In Use</option>
                                            <option value="in-progress-checkout">In-Progress (Check-Out)</option>
                                            <option value="completed">Completed</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="filterRoom">Pods:</label>
                                        <select id="filterRoom" class="form-control" style="display: inline-block; width: 140px;">
                                            <option value="all">All Pods</option>
                                            <!-- Pod options will be populated dynamically from database -->
                                        </select>
                                    </div>
                                    <div>
                                        <label for="filterTime">Check-in Time:</label>
                                        <select id="filterTime" class="form-control" style="display: inline-block; width: 140px;">
                                            <option value="">All Times</option>
                                            <option value="08:00">08:00</option>
                                            <option value="08:15">08:15</option>
                                            <option value="08:30">08:30</option>
                                            <option value="08:45">08:45</option>
                                            <option value="09:00">09:00</option>
                                            <option value="09:15">09:15</option>
                                            <option value="09:30">09:30</option>
                                            <option value="09:45">09:45</option>
                                            <option value="10:00">10:00</option>
                                            <option value="10:15">10:15</option>
                                            <option value="10:30">10:30</option>
                                            <option value="10:45">10:45</option>
                                            <option value="11:00">11:00</option>
                                            <option value="11:15">11:15</option>
                                            <option value="11:30">11:30</option>
                                            <option value="11:45">11:45</option>
                                            <option value="12:00">12:00</option>
                                            <option value="12:15">12:15</option>
                                            <option value="12:30">12:30</option>
                                            <option value="12:45">12:45</option>
                                            <option value="13:00">13:00</option>
                                            <option value="13:15">13:15</option>
                                            <option value="13:30">13:30</option>
                                            <option value="13:45">13:45</option>
                                            <option value="14:00">14:00</option>
                                            <option value="14:15">14:15</option>
                                            <option value="14:30">14:30</option>
                                            <option value="14:45">14:45</option>
                                            <option value="15:00">15:00</option>
                                            <option value="15:15">15:15</option>
                                            <option value="15:30">15:30</option>
                                            <option value="15:45">15:45</option>
                                            <option value="16:00">16:00</option>
                                            <option value="16:15">16:15</option>
                                            <option value="16:30">16:30</option>
                                            <option value="16:45">16:45</option>
                                            <option value="17:00">17:00</option>
                                            <option value="17:15">17:15</option>
                                            <option value="17:30">17:30</option>
                                            <option value="17:45">17:45</option>
                                            <option value="18:00">18:00</option>
                                            <option value="18:15">18:15</option>
                                            <option value="18:30">18:30</option>
                                            <option value="18:45">18:45</option>
                                            <option value="19:00">19:00</option>
                                            <option value="19:15">19:15</option>
                                            <option value="19:30">19:30</option>
                                            <option value="19:45">19:45</option>
                                            <option value="20:00">20:00</option>
                                        </select>
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
                                                <th>Date</th>
                                                <th>Username</th>
                                                <th>Pods No.</th>
                                                <th>Check-In Time</th>
                                                <th>Check-Out Time</th>
                                                <th>Booked Duration</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="bookingsTableBody">
                                            <!-- Bookings - Sorted by Date and Check-in Time -->
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
        var bookingsData = [];
        var podsData = [];
        var penaltiesData = [];
        var refreshInterval = null;
        
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
            
            // Check if user is admin/staff
            var userRole = currentUser.user_metadata?.role || 'user';
            console.log('Current user role:', userRole);
            console.log('Current user metadata:', JSON.stringify(currentUser.user_metadata, null, 2));
            
            if (userRole !== 'admin') {
                console.warn('User is not an admin. RLS policies may block access to all bookings.');
            }
            
            console.log('Initializing dashboard - loading all bookings');
            
            // Load pods first (needed for bookings)
            await loadPods();
            console.log('Loaded pods:', podsData.length);
            
            // Load all bookings (no date filter)
            await loadBookings();
            
            // Load penalties
            await loadPenalties();
            
            // Update dashboard cards
            updateDashboardCards();
            
            // Populate filter dropdowns first
            populatePodFilter();
            
            // Initialize DataTable after bookings are loaded
            // Only initialize if there are actual data rows (not message rows)
            setTimeout(function() {
                initializeDataTable();
            }, 100);
            
            // Start refresh interval (10 seconds for real-time status updates)
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
            refreshInterval = setInterval(async function() {
                // Reload pods in case new ones were added
                await loadPods();
                // Reload bookings for selected date
                await loadBookings();
                // Reload penalties
                await loadPenalties();
                // Update dashboard cards
                updateDashboardCards();
                // Update pod filter dropdown
                populatePodFilter();
                // Reinitialize DataTable
                if (dataTable) {
                    try {
                        dataTable.fnDestroy();
                    } catch (e) {
                        console.log('Error destroying DataTable:', e);
                    }
                    dataTable = null;
                }
                // Reinitialize after a short delay
                setTimeout(function() {
                    initializeDataTable();
                    applyFilters(); // Reapply filters after refresh
                }, 100);
            }, 10000); // Update every 10 seconds for real-time status simulation
        });
        
        // Load pods from database
        async function loadPods() {
            try {
                const { data, error } = await supabase
                    .from('pods')
                    .select('id, name, status')
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
        
        // Load all bookings from database (no date filter)
        async function loadBookings() {
            try {
                console.log('Loading all bookings from database...');
                
                // Load ALL bookings, ordered by date and time
                const { data: bookings, error: bookingsError } = await supabase
                    .from('bookings')
                    .select('id, user_id, pod_id, booking_date, check_in_time, check_out_time, number_of_people')
                    .order('booking_date', { ascending: true })
                    .order('check_in_time', { ascending: true });
                
                console.log('Query result - bookings:', bookings);
                console.log('Query - error:', bookingsError);
                
                if (bookingsError) {
                    console.error('Error loading bookings:', bookingsError);
                    console.error('Error code:', bookingsError.code);
                    console.error('Error message:', bookingsError.message);
                    console.error('Error details:', JSON.stringify(bookingsError, null, 2));
                    console.error('Error hint:', bookingsError.hint);
                    bookingsData = [];
                    populateBookingsTable();
                    // Show error message to user
                    $('#bookingsTableBody').html('<tr><td colspan="7" style="text-align: center; padding: 40px; color: #d9534f; font-size: 16px;">Error loading bookings: ' + (bookingsError.message || 'Unknown error') + '<br><small>Check browser console for details.</small></td></tr>');
                    return;
                }
                
                console.log('Loaded all bookings:', bookings ? bookings.length : 0, 'bookings');
                if (bookings && bookings.length > 0) {
                    console.log('Booking dates found:', [...new Set(bookings.map(b => b.booking_date))]);
                    console.log('First booking:', JSON.stringify(bookings[0], null, 2));
                }
                
                if (!bookings || bookings.length === 0) {
                    console.log('No bookings found in database');
                    bookingsData = [];
                    // Show "no bookings" message
                    var message = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: #999; font-size: 16px;">' +
                        '<i class="fa fa-calendar" style="font-size: 48px; display: block; margin-bottom: 15px; opacity: 0.3;"></i>' +
                        'No bookings found in the database' +
                        '</td></tr>';
                    $('#bookingsTableBody').html(message);
                    $('#noResultsBody').hide();
                    return;
                }
                
                console.log('Raw bookings data from database:', JSON.stringify(bookings, null, 2));
                
                // Load usernames and pod names separately
                var userIds = [...new Set(bookings.map(b => b.user_id).filter(id => id))];
                var podIds = [...new Set(bookings.map(b => b.pod_id).filter(id => id))];
                
                var usersMap = {};
                var podsMap = {};
                
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
                        
                        // Always log debug info
                        if (data.debug) {
                            console.log('=== User Lookup Debug Info ===');
                            console.log('Requested user IDs:', data.debug.requested_ids);
                            console.log('Found users:', data.debug.found_count);
                            console.log('Found user IDs:', data.debug.found_ids);
                            if (data.debug.queries) {
                                console.log('Query details:', data.debug.queries);
                            }
                            console.log('==============================');
                        }
                        
                        if (data.error) {
                            console.error('Error loading users:', data.error);
                            console.warn('Using user_id as fallback for usernames');
                        } else if (data.errors && data.errors.length > 0) {
                            console.error('Errors loading users:', data.errors);
                            console.warn('Some users may not be loaded. Using fallback for missing users.');
                        }
                        
                        if (data.users && Object.keys(data.users).length > 0) {
                            console.log('Loaded users from Auth API:', Object.keys(data.users).length);
                            console.log('Users response:', data.users);
                            
                            // data.users is a map of user_id => { id, username, email }
                            Object.keys(data.users).forEach(userId => {
                                const user = data.users[userId];
                                // Use username directly from the response
                                usersMap[userId] = user.username || (user.email ? user.email.split('@')[0] : 'User ' + userId.substring(0, 8));
                                console.log(`Mapped user ${userId}: ${usersMap[userId]}`);
                            });
                            console.log('Final users map:', usersMap);
                        } else {
                            console.warn('No users returned from Auth API');
                            console.warn('Full response data:', JSON.stringify(data, null, 2));
                            
                            // If we have errors, show them
                            if (data.errors && data.errors.length > 0) {
                                console.error('API Errors:', data.errors);
                            }
                        }
                    } catch (error) {
                        console.error('Error fetching users from Auth API:', error);
                        console.warn('Using user_id as fallback for usernames');
                    }
                } else {
                    console.log('No user IDs to load');
                }
                
                // Load pods (use cached podsData if available, otherwise load from database)
                if (podIds.length > 0) {
                    console.log('Loading pods for pod_ids:', podIds);
                    // First check if we already have the pods in podsData
                    podIds.forEach(podId => {
                        var pod = podsData.find(p => p.id === podId);
                        if (pod) {
                            podsMap[pod.id] = pod;
                            console.log('Found pod in cache:', pod.id, pod.name);
                        }
                    });
                    
                    // Load any missing pods
                    var missingPodIds = podIds.filter(id => !podsMap[id]);
                    if (missingPodIds.length > 0) {
                        console.log('Loading missing pods:', missingPodIds);
                        const { data: pods, error: podsError } = await supabase
                            .from('pods')
                            .select('id, name')
                            .in('id', missingPodIds);
                        
                        if (podsError) {
                            console.error('Error loading pods:', podsError);
                            console.error('Pod error details:', JSON.stringify(podsError, null, 2));
                        } else if (pods) {
                            console.log('Loaded pods:', pods.length);
                            pods.forEach(p => {
                                podsMap[p.id] = p;
                                console.log('Mapped pod:', p.id, p.name);
                                // Also add to podsData cache if not already there
                                if (!podsData.find(existing => existing.id === p.id)) {
                                    podsData.push(p);
                                }
                            });
                        } else {
                            console.warn('No pods returned from query');
                        }
                    } else {
                        console.log('All pods found in cache');
                    }
                } else {
                    console.log('No pod IDs to load');
                }
                
                console.log('Processing', bookings.length, 'bookings');
                console.log('Users map:', usersMap);
                console.log('Pods map:', podsMap);
                
                // Map bookings with user and pod data
                bookingsData = bookings.map(booking => {
                    var username = usersMap[booking.user_id];
                    if (!username) {
                        // Try to get from email if available, or use shortened user_id
                        username = 'User ' + (booking.user_id ? booking.user_id.substring(0, 8) : 'Unknown');
                        console.warn('No username found for user_id:', booking.user_id);
                        console.warn('Available user IDs in map:', Object.keys(usersMap));
                        console.warn('Requested user IDs:', userIds);
                        console.warn('Using fallback username:', username);
                    } else {
                        console.log('Found username for user_id:', booking.user_id, '->', username);
                    }
                    
                    var pod = podsMap[booking.pod_id];
                    if (!pod) {
                        pod = { id: booking.pod_id, name: 'Pod ' + (booking.pod_id ? booking.pod_id.substring(0, 8) : 'Unknown') };
                        console.warn('No pod found for pod_id:', booking.pod_id, 'using fallback:', pod.name);
                    }
                    
                    // Handle time formats (could be "HH:MM:SS" or "HH:MM" or timestamp)
                    var checkInTime = '';
                    var checkOutTime = '';
                    
                    if (booking.check_in_time) {
                        if (typeof booking.check_in_time === 'string') {
                            // Extract HH:MM from time string (handles "HH:MM:SS" format)
                            checkInTime = booking.check_in_time.substring(0, 5);
                        } else {
                            // If it's a timestamp or other format, convert it
                            checkInTime = booking.check_in_time;
                        }
                    }
                    
                    if (booking.check_out_time) {
                        if (typeof booking.check_out_time === 'string') {
                            // Extract HH:MM from time string (handles "HH:MM:SS" format)
                            checkOutTime = booking.check_out_time.substring(0, 5);
                        } else {
                            // If it's a timestamp or other format, convert it
                            checkOutTime = booking.check_out_time;
                        }
                    }
                    
                    var duration = calculateDuration(checkInTime, checkOutTime);
                    
                    var bookingData = {
                        id: booking.id,
                        username: username,
                        room: pod.id,
                        roomName: pod.name || 'Pod ' + pod.id,
                        checkIn: checkInTime,
                        checkOut: checkOutTime,
                        duration: duration,
                        booking_date: booking.booking_date,
                        number_of_people: booking.number_of_people || 1
                    };
                    
                    console.log('Mapped booking:', bookingData);
                    return bookingData;
                });
                
                console.log('Mapped bookingsData:', bookingsData.length, 'bookings');
                
                // Populate table with loaded data
                populateBookingsTable();
            } catch (error) {
                console.error('Error in loadBookings:', error);
                bookingsData = [];
                populateBookingsTable();
            }
        }
        
        // Calculate duration between two times
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
        
        // Populate pod filter dropdown
        function populatePodFilter() {
            var filterRoom = $('#filterRoom');
            // Clear existing options except "All Pods"
            filterRoom.find('option:not([value="all"])').remove();
            
            // Add pods from database
            podsData.forEach(function(pod) {
                var podName = pod.name || 'Pod ' + pod.id;
                filterRoom.append('<option value="' + pod.id + '">' + podName + '</option>');
            });
        }
        
        function convertTo12Hour(time24) {
            if (!time24) return '';
            // time24 format: "HH:MM" (e.g., "08:30", "13:00", "17:45")
            var parts = time24.split(':');
            if (parts.length < 2) return time24;
            var hours = parseInt(parts[0]);
            var minutes = parts[1];
            
            var period = hours >= 12 ? 'PM' : 'AM';
            var hours12 = hours % 12 || 12; // Convert 0 to 12 for midnight, 13-23 to 1-11
            
            return hours12 + ':' + minutes + ' ' + period;
        }
        
        // Get booking status (matches user dashboard logic)
        function getBookingStatus(bookingDate, checkIn, checkOut) {
            if (!bookingDate || !checkIn || !checkOut) return 'completed';
            
            var now = new Date();
            var checkInDateTime = new Date(bookingDate + 'T' + checkIn + ':00');
            var checkOutDateTime = new Date(bookingDate + 'T' + checkOut + ':00');
            
            // Calculate time windows (15 minutes)
            var checkInWindow = 15 * 60 * 1000; // 15 minutes in milliseconds
            var checkOutWindow = 15 * 60 * 1000; // 15 minutes in milliseconds
            
            var checkInStart = new Date(checkInDateTime.getTime() - checkInWindow);
            var checkInEnd = checkInDateTime;
            var checkOutStart = new Date(checkOutDateTime.getTime() - checkOutWindow);
            
            try {
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
            } catch (e) {
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
                case 'ongoing': // Legacy support
                    return 'Ongoing';
                default:
                    return 'Unknown';
            }
        }
        
        // Format date for display
        function formatDate(dateStr) {
            if (!dateStr) return '';
            var date = new Date(dateStr + 'T00:00:00');
            var options = { year: 'numeric', month: 'short', day: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        }
        
        function populateBookingsTable() {
            var tbody = $('#bookingsTableBody');
            
            // Destroy DataTable before clearing tbody to avoid conflicts
            if (dataTable) {
                try {
                    dataTable.fnDestroy();
                } catch (e) {
                    console.log('Error destroying DataTable before populate:', e);
                }
                dataTable = null;
            }
            
            tbody.empty();
            
            console.log('Populating table with', bookingsData.length, 'bookings');
            
            if (bookingsData.length === 0) {
                console.log('No bookings to display');
                // Don't show message here - let loadBookings handle it
                // But make sure tbody is empty so DataTable won't initialize
                return;
            }
            
            // Verify all bookings have required fields before creating rows
            bookingsData.forEach(function(booking, index) {
                try {
                    // Validate booking has all required fields
                    if (!booking.booking_date || !booking.checkIn || !booking.checkOut) {
                        console.warn('Booking missing required fields:', booking);
                        return; // Skip this booking
                    }
                    
                    var status = getBookingStatus(booking.booking_date, booking.checkIn, booking.checkOut);
                    var statusClass = 'status-' + status;
                    var statusText = getStatusText(status);
                    
                    // Format date for display
                    var formattedDate = formatDate(booking.booking_date);
                    
                    // Ensure all 7 columns are present
                    var row = '<tr data-status="' + status + '" data-room="' + booking.room + 
                        '" data-checkin="' + booking.checkIn + '" data-date="' + booking.booking_date + 
                        '" data-duration="' + booking.duration + '">' +
                        '<td data-order="' + booking.booking_date + '">' + (formattedDate || '') + '</td>' +  // Column 0: Date
                        '<td>' + (booking.username || 'Unknown') + '</td>' +  // Column 1: Username
                        '<td>' + (booking.roomName || 'Pod ' + booking.room) + '</td>' +  // Column 2: Pods No.
                        '<td data-order="' + booking.checkIn + '">' + convertTo12Hour(booking.checkIn) + '</td>' +  // Column 3: Check-In Time
                        '<td data-order="' + booking.checkOut + '">' + convertTo12Hour(booking.checkOut) + '</td>' +  // Column 4: Check-Out Time
                        '<td>' + (booking.duration || 'N/A') + '</td>' +  // Column 5: Booked Duration
                        '<td><span class="status-badge ' + statusClass + '">' + statusText + '</span></td>' +  // Column 6: Status
                        '</tr>';
                    
                    tbody.append(row);
                } catch (error) {
                    console.error('Error processing booking at index', index, ':', booking, error);
                }
            });
            
            var rowCount = tbody.find('tr').length;
            console.log('Table populated with', rowCount, 'rows');
            
            // Verify all rows have exactly 7 columns
            tbody.find('tr').each(function(index) {
                var colCount = $(this).find('td').length;
                if (colCount !== 7) {
                    console.error('Row', index, 'has', colCount, 'columns, expected 7');
                }
            });
        }
        
        // Load penalties from database
        async function loadPenalties() {
            try {
                // Load all penalties (pending status)
                const { data: penalties, error: penaltiesError } = await supabase
                    .from('penalties')
                    .select('id, status')
                    .eq('status', 'pending');
                
                if (penaltiesError) {
                    console.error('Error loading penalties:', penaltiesError);
                    penaltiesData = [];
                    return;
                }
                
                penaltiesData = penalties || [];
            } catch (error) {
                console.error('Error in loadPenalties:', error);
                penaltiesData = [];
            }
        }
        
        function updateDashboardCards() {
            // Calculate occupied rooms from bookings for today
            var now = new Date();
            var today = now.toISOString().split('T')[0];
            var occupiedRooms = 0;
            
            // Filter bookings for today
            var todayBookings = bookingsData.filter(function(booking) {
                return booking.booking_date === today;
            });
            
            todayBookings.forEach(function(booking) {
                var status = getBookingStatus(booking.booking_date, booking.checkIn, booking.checkOut);
                // Count as occupied if in any in-progress stage
                if (status === 'in-progress-checkin' || status === 'in-progress-occupied' || status === 'in-progress-checkout') {
                    occupiedRooms++;
                }
            });
            
            // Total bookings today
            $('#totalBookings').text(todayBookings.length);
            
            // Total pending penalties
            $('#totalPenalties').text(penaltiesData.length);
            
            // Occupancy rate (occupied rooms / total operational rooms * 100)
            // Only count non-suspended pods as available
            var operationalPods = podsData.filter(pod => pod.status !== 'suspended');
            var totalRooms = operationalPods.length > 0 ? operationalPods.length : 1;
            var occupancyRate = totalRooms > 0 ? ((occupiedRooms / totalRooms) * 100).toFixed(1) : '0.0';
            $('#occupancyRate').text(occupancyRate + '%');
            
            // Room status (occupied/operational)
            // Show "Normal" if occupancy is reasonable, otherwise show the ratio
            if (occupiedRooms === 0 && totalRooms > 0) {
                $('#roomStatus').text('Normal');
            } else {
                $('#roomStatus').text(occupiedRooms + '/' + totalRooms);
            }
        }
        
        function initializeDataTable() {
            // Destroy existing DataTable if it exists
            if (dataTable) {
                try {
                    dataTable.fnDestroy();
                } catch (e) {
                    console.log('Error destroying DataTable:', e);
                }
                dataTable = null;
            }
            
            // Check if table has data rows (not just the "no bookings" message)
            var tbody = $('#bookingsTableBody');
            var hasDataRows = tbody.find('tr').length > 0 && 
                             !tbody.find('tr td[colspan]').length; // No colspan cells (which indicates message rows)
            
            if (!hasDataRows) {
                console.log('No data rows to initialize DataTable with');
                return;
            }
            
            // Initialize DataTable only if there are actual data rows
            try {
                dataTable = $('#dataTables-example').dataTable({
                    "order": [[ 0, "asc" ], [ 3, "asc" ]],  // Sort by date (column 0) then check-in time (column 3)
                    "paging": false,  // Disable pagination - show all records
                    "searching": false,  // Disable search box
                    "info": false,  // Hide "Showing X to Y of Z entries" text
                    "autoWidth": false,  // Disable automatic column width calculation
                    "columnDefs": [
                        { "orderable": true, "targets": [0, 3, 4] },  // Make date and time columns sortable
                        { "orderable": false, "targets": [1, 2, 5, 6] }  // Make other columns non-sortable
                    ]
                });
                console.log('DataTable initialized successfully');
            } catch (error) {
                console.error('Error initializing DataTable:', error);
            }
        }
        
        function applyFilters() {
            if (!dataTable) {
                console.log('DataTable not initialized yet');
                return;
            }
            
            var statusFilter = $('#filterStatus').val();
            var roomFilter = $('#filterRoom').val();
            var timeFilter = $('#filterTime').val();
            var searchText = $('#searchUsername').val().toLowerCase().trim();
            
            console.log('FILTERING - Status:', statusFilter, '| Room:', roomFilter, '| Time:', timeFilter, '| Search:', searchText);
            
            // Show all rows first
            $('#bookingsTableBody tr').show();
            
            // Apply status filter
            if (statusFilter !== 'all') {
                $('#bookingsTableBody tr').each(function() {
                    var rowStatus = $(this).attr('data-status');
                    // Handle legacy 'ongoing' status - map to 'in-progress-occupied'
                    if (statusFilter === 'ongoing') {
                        if (rowStatus !== 'in-progress-occupied' && rowStatus !== 'in-progress-checkin' && rowStatus !== 'in-progress-checkout') {
                            $(this).hide();
                        }
                    } else if (rowStatus !== statusFilter) {
                        $(this).hide();
                    }
                });
            }
            
            // Apply room filter (handle both UUIDs and numeric IDs)
            if (roomFilter !== 'all') {
                $('#bookingsTableBody tr:visible').each(function() {
                    var rowRoom = $(this).attr('data-room');
                    // Compare as strings to handle UUIDs
                    if (String(rowRoom) !== String(roomFilter)) {
                        $(this).hide();
                    }
                });
            }
            
            // Apply time filter
            if (timeFilter !== '') {
                $('#bookingsTableBody tr:visible').each(function() {
                    var rowCheckIn = $(this).attr('data-checkin'); // Format: "HH:MM"
                    if (rowCheckIn !== timeFilter) {
                        $(this).hide();
                    }
                });
            }
            
            // Apply username search filter
            if (searchText !== '') {
                $('#bookingsTableBody tr:visible').each(function() {
                    // Username is in the second column (index 1) after Date column
                    var username = $(this).find('td').eq(1).text().toLowerCase();
                    if (username.indexOf(searchText) === -1) {
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
            // Reset dropdowns, time, and search
            $('#filterStatus').val('all');
            $('#filterRoom').val('all');
            $('#filterTime').val('');
            $('#searchUsername').val('');
            
            // Show all rows and hide no results message
            $('#bookingsTableBody tr').show();
            $('#noResultsBody').hide();
            
            // Reapply filters to refresh the view
            applyFilters();
            
            console.log('Filters reset - showing all bookings');
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
            
            // Attach filter event handlers
            $('#filterStatus').on('change', function() {
                applyFilters();
            });
            
            $('#filterRoom').on('change', function() {
                applyFilters();
            });
            
            // Time filter - filter when time is selected
            $('#filterTime').on('change', function() {
                applyFilters();
            });
            
            // Search input - filter as user types
            $('#searchUsername').on('keyup', function() {
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
    </script>
    <!-- Custom Js -->
    <script src="assets/js/custom-scripts.js"></script>
</body>
</html>