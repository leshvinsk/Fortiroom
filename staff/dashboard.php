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
                            <div class="stat-value">8</div>
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
                                <h4>Today's Booking Records</h4>
                            </div>
                            <div class="panel-body">
                                <!-- Filter Controls -->
                                <div class="filter-controls">
                                    <div>
                                        <label for="filterStatus">Status:</label>
                                        <select id="filterStatus" class="form-control" style="display: inline-block; width: 140px;">
                                            <option value="all">All Status</option>
                                            <option value="upcoming">Upcoming</option>
                                            <option value="ongoing">Ongoing</option>
                                            <option value="completed">Completed</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="filterRoom">Pods:</label>
                                        <select id="filterRoom" class="form-control" style="display: inline-block; width: 140px;">
                                            <option value="all">All Pods</option>
                                            <option value="1">Pod 1</option>
                                            <option value="2">Pod 2</option>
                                            <option value="3">Pod 3</option>
                                            <option value="4">Pod 4</option>
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
                                                <th>Username</th>
                                                <th>Pods No.</th>
                                                <th>Check-In Time</th>
                                                <th>Check-Out Time</th>
                                                <th>Booked Duration</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="bookingsTableBody">
                                            <!-- Today's Bookings - Sorted by Check-in Time -->
                                        </tbody>
                                        <tbody id="noResultsBody" style="display: none;">
                                            <tr>
                                                <td colspan="6" style="text-align: center; padding: 40px; color: #999; font-size: 16px;">
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
        var dataTable;
        
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
        
        function populateBookingsTable() {
            var tbody = $('#bookingsTableBody');
            tbody.empty();
            
            var today = new Date().toISOString().split('T')[0];
            var ongoingRooms = new Set();
            
            bookingsData.forEach(function(booking) {
                var status = getBookingStatus(booking.checkIn, booking.checkOut);
                var statusClass = 'status-' + status;
                var statusText = status.charAt(0).toUpperCase() + status.slice(1);
                
                // Track occupied rooms
                if (status === 'ongoing') {
                    ongoingRooms.add(booking.room);
                }
                
                var row = '<tr data-status="' + status + '" data-room="' + booking.room + 
                    '" data-checkin="' + booking.checkIn + '" data-duration="' + booking.duration + '">' +
                    '<td>' + booking.username + '</td>' +
                    '<td>' + booking.room + '</td>' +
                    '<td data-order="' + booking.checkIn + '">' + convertTo12Hour(booking.checkIn) + '</td>' +
                    '<td data-order="' + booking.checkOut + '">' + convertTo12Hour(booking.checkOut) + '</td>' +
                    '<td>' + booking.duration + '</td>' +
                    '<td><span class="status-badge ' + statusClass + '">' + statusText + '</span></td>' +
                    '</tr>';
                
                tbody.append(row);
            });
            
            // Update dashboard cards
            updateDashboardCards(ongoingRooms.size);
        }
        
        function updateDashboardCards(occupiedRooms) {
            // Total bookings today
            $('#totalBookings').text(bookingsData.length);
            
            // Occupancy rate (occupied rooms / total rooms * 100)
            var totalRooms = 4;
            var occupancyRate = ((occupiedRooms / totalRooms) * 100).toFixed(1);
            $('#occupancyRate').text(occupancyRate + '%');
            
            // Room status (occupied/total)
            $('#roomStatus').text(occupiedRooms + '/' + totalRooms);
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
                    if (rowStatus !== statusFilter) {
                        $(this).hide();
                    }
                });
            }
            
            // Apply room filter
            if (roomFilter !== 'all') {
                $('#bookingsTableBody tr:visible').each(function() {
                    var rowRoom = $(this).attr('data-room');
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
                    var username = $(this).find('td:first').text().toLowerCase();
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
            
            // Populate table with today's bookings
            populateBookingsTable();
            
            // Initialize DataTable - no pagination, search, or info
            dataTable = $('#dataTables-example').dataTable({
                "order": [[ 2, "asc" ]],  // Sort by check-in time (column index 2)
                "paging": false,  // Disable pagination - show all records
                "searching": false,  // Disable search box
                "info": false  // Hide "Showing X to Y of Z entries" text
            });
            
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
            
            // Update status every 30 seconds
            setInterval(function() {
                populateBookingsTable();
                dataTable.fnDestroy();
                dataTable = $('#dataTables-example').dataTable({
                    "order": [[ 2, "asc" ]],
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