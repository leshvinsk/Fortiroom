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
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FORTIROOM | Intelligent Space Access Platform</title>
    <link rel="icon" href="../images/FYP_Logo_small.png" type="image/icon type">
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome 4.7 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                }
            }
        }
    </script>
    <!-- Supabase JS v2 -->
    <script defer src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    <script>
        window.__SUPABASE__ = {
            url: "<?php echo htmlspecialchars($SUPABASE_URL, ENT_QUOTES, 'UTF-8'); ?>",
            anonKey: "<?php echo htmlspecialchars($SUPABASE_ANON_KEY, ENT_QUOTES, 'UTF-8'); ?>"
        };
    </script>
    <style>
        /* Sidebar mobile behavior */
        .navbar-side {
            transform: translateX(-260px);
            transition: transform 0.3s ease;
        }
        @media (min-width: 1024px) {
            .navbar-side { transform: translateX(0) !important; }
            #page-wrapper { margin-left: 260px !important; }
            .navbar-top-links { display: flex !important; }
            .mobile-only { display: none !important; }
        }
        .navbar-side.in { transform: translateX(0) !important; }
        
        @media (max-width: 1023px) {
            .navbar-side { background-color: #3a6b4d !important; }
            #sidebar-overlay {
                background: rgba(0,0,0,0.78) !important;
                z-index: 39 !important;
            }
            .navbar-top-links { display: none !important; }
            .mobile-only { display: block !important; }
        }

        /* Sidebar nav links */
        .sidebar-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            color: rgba(255,255,255,0.85);
            transition: all 0.15s ease;
            text-decoration: none;
        }
        .sidebar-nav-link:hover {
            color: #d3af37;
            background: rgba(255,255,255,0.08);
            text-decoration: none;
        }
        .sidebar-nav-link.active-menu {
            color: #d3af37;
            background: rgba(255,255,255,0.1);
            position: relative;
            padding-left: 26px;
        }
        .sidebar-nav-link.active-menu::before {
            content: "";
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            border-top: 5px solid transparent;
            border-bottom: 5px solid transparent;
            border-left: 7px solid #d3af37;
        }
        .sidebar-nav-link i { width: 16px; text-align: center; }
        
        /* Consistent dropdown corner radius */
        select,
        .dataTables_wrapper .dataTables_length select {
            border-radius: 8px !important;
            overflow: hidden;
        }
        select option { border-radius: 8px; }

        /* DataTable overrides */
        .elevated-card { box-shadow: 0 8px 22px rgba(15, 23, 42, 0.08) !important; }
        table.dataTable,
        #dataTables-example { width: 100% !important; border-collapse: collapse !important; }
        #dataTables-example { table-layout: fixed; }
        table.dataTable thead th,
        #dataTables-example thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            padding: 11px 16px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            white-space: nowrap;
            text-align: left;
        }
        table.dataTable tbody td,
        #dataTables-example tbody td {
            padding: 13px 16px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
            color: #374151;
            vertical-align: middle;
            text-align: left;
        }
        table.dataTable tbody tr:hover,
        #dataTables-example tbody tr:hover { background: #f9fafb; }
        #dataTables-example tbody td.empty-state { text-align: center !important; }
        table.dataTable thead .sorting:after,
        table.dataTable thead .sorting_asc:after,
        table.dataTable thead .sorting_desc:after { color: #9ca3af; }

        /* Status badges */
        .status-badge {
            display: inline-flex; align-items: center;
            padding: 3px 10px; border-radius: 9999px;
            font-size: 11px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.3px;
        }
        .status-upcoming { background: #dbeafe; color: #1e40af; }
        .status-in-progress-checkin { background: #fef9c3; color: #854d0e; }
        .status-in-progress-occupied { background: #dcfce7; color: #166534; }
        .status-in-progress-checkout { background: #fef9c3; color: #854d0e; }
        .status-completed { background: #f3f4f6; color: #6b7280; }
        .status-ongoing { background: #dcfce7; color: #166534; }

        /* Stat card accent borders */
        .card-green { border-left: 4px solid #16a34a; }
        .card-blue  { border-left: 4px solid #3b82f6; }
        .card-amber { border-left: 4px solid #f59e0b; }
        .card-red   { border-left: 4px solid #ef4444; }
        .bg-white.rounded-xl.shadow-sm {
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.08) !important;
        }
                button[class*="bg-green-600"],
        button[class*="hover:bg-green-700"],
        a[class*="bg-green-600"],
        a[class*="hover:bg-green-700"] {
            color: #ffffff !important;
        }
        button[class*="bg-green-600"]:hover,
        button[class*="hover:bg-green-700"]:hover,
        a[class*="bg-green-600"]:hover,
        a[class*="hover:bg-green-700"]:hover {
            color: #d3af37 !important;
        }

        /* Scrollbar hidden */
        ::-webkit-scrollbar { display: none; }
        * { scrollbar-width: none; }
    </style>
</head>
<body class="bg-[#f7f7f5] font-sans antialiased overflow-x-hidden">
<div id="wrapper" class="min-h-screen">

    <!-- TOP NAVBAR -->
    <header class="fixed top-0 left-0 right-0 z-50 h-[60px] bg-white border-b border-gray-200 flex items-center justify-between px-4 lg:px-6">
        <button class="navbar-toggle lg:hidden p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none" aria-label="Toggle navigation">
            <span class="block w-5 h-px bg-current mb-[5px]"></span>
            <span class="block w-5 h-px bg-current mb-[5px]"></span>
            <span class="block w-5 h-px bg-current"></span>
        </button>
        <a href="dashboard.php" class="flex items-center">
            <img src="../images/header_logo.png" class="h-9 w-auto" alt="Fortiroom">
        </a>
        <div class="navbar-top-links hidden items-center gap-1">
            <a href="profile.php" class="flex items-center gap-2 px-4 py-2 text-sm font-semibold hover:bg-green-100 rounded-lg transition-colors">
                <i class="fa fa-user-circle"></i> Profile
            </a>
            <a href="logout.php" class="flex items-center gap-2 px-4 py-2 text-sm font-semibold hover:bg-green-100 rounded-lg transition-colors">
                <i class="fa fa-sign-out"></i> Log Out
            </a>
        </div>
    </header>

    <!-- SIDEBAR -->
    <aside class="navbar-side fixed top-[60px] left-0 w-[260px] bottom-0 bg-[#1f3a26] overflow-y-auto z-40">
        <nav class="sidebar-collapse py-5">
            <ul id="main-menu" class="space-y-1 px-3">
                <li>
                    <a class="sidebar-nav-link active-menu" href="dashboard.php">
                        <i class="fa fa-dashboard fa-fw"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a class="sidebar-nav-link" href="penalties.php">
                        <i class="fa fa-exclamation-triangle fa-fw"></i> Penalties
                    </a>
                </li>
                <li>
                    <a class="sidebar-nav-link" href="pods.php">
                        <i class="fa fa-building fa-fw"></i> Pods Management
                    </a>
                </li>
                <li>
                    <a class="sidebar-nav-link" href="users.php">
                        <i class="fa fa-users fa-fw"></i> User Management
                    </a>
                </li>
                <li>
                    <a class="sidebar-nav-link" href="analytics.php">
                        <i class="fa fa-bar-chart-o fa-fw"></i> Analytics
                    </a>
                </li>
                <li class="mobile-only" style="display:none; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 8px; margin-top: 8px;">
                    <a class="sidebar-nav-link" href="profile.php">
                        <i class="fa fa-user-circle fa-fw"></i> Profile
                    </a>
                </li>
                <li class="mobile-only" style="display:none;">
                    <a class="sidebar-nav-link" href="logout.php">
                        <i class="fa fa-sign-out fa-fw"></i> Log Out
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main id="page-wrapper" class="mt-[60px] min-h-screen p-6 lg:p-8">
        <div id="page-inner">

            <!-- Page Heading -->
            <div class="border-b border-gray-200 pb-4 mb-8">
                <h1 class="text-2xl font-light tracking-wide font-semibold text-gray-700 uppercase font-weight bold">System Dashboard</h1>
            </div>

            <!-- Stat Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8 dashboard-cards">
                <!-- Occupancy Rate -->
                <div class="stat-card occupancy bg-white rounded-xl p-6 shadow-sm card-green hover:-translate-y-1 transition-transform duration-200 cursor-default">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Occupancy Rate</p>
                            <div id="occupancyRate" class="stat-value text-4xl font-bold text-gray-900 mb-1">0%</div>
                        
                        </div>
                        <i class="fa fa-percent stat-icon text-5xl text-gray-500"></i>
                    </div>
                </div>
                <!-- No. of Bookings -->
                <div class="stat-card bookings bg-white rounded-xl p-6 shadow-sm card-blue hover:-translate-y-1 transition-transform duration-200 cursor-default">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">No. of Bookings Today</p>
                            <div id="totalBookings" class="stat-value text-4xl font-bold text-gray-900 mb-1">0</div>
                        
                        </div>
                        <i class="fa fa-calendar-check-o stat-icon text-5xl text-gray-500"></i>
                    </div>
                </div>
                <!-- Penalties Issued -->
                <div class="stat-card penalties bg-white rounded-xl p-6 shadow-sm card-amber hover:-translate-y-1 transition-transform duration-200 cursor-default">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Penalties Issued</p>
                            <div id="totalPenalties" class="stat-value text-4xl font-bold text-gray-900 mb-1">0</div>
                    
                        </div>
                        <i class="fa fa-exclamation-triangle stat-icon text-5xl text-gray-500"></i>
                    </div>
                </div>
                <!-- Pods Status -->
                <div class="stat-card room-status bg-white rounded-xl p-6 shadow-sm card-red hover:-translate-y-1 transition-transform duration-200 cursor-default">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Pods Status</p>
                            <div id="roomStatus" class="stat-value text-4xl font-bold text-gray-900 mb-1">Normal</div>
                    
                        </div>
                        <i class="fa fa-bed stat-icon text-5xl text-gray-500"></i>
                    </div>
                </div>
            </div>

            <!-- Bookings Table Card -->
            <div class="elevated-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-visible">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Today's Booking</h4>
                </div>
                <div class="p-6">
                    <!-- Filter Controls -->
                    <div class="filter-controls flex flex-wrap gap-4 items-end mb-5">
                        <div>
                            <label for="filterStatus" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Status</label>
                            <select id="filterStatus" class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 w-[180px]">
                                <option value="all" selected>All Status</option>
                                <option value="upcoming">Upcoming</option>
                                <option value="in-progress-checkin">In-Progress (Check-In)</option>
                                <option value="in-progress-occupied">In Use</option>
                                <option value="in-progress-checkout">In-Progress (Check-Out)</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div>
                            <label for="filterRoom" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Pod</label>
                            <select id="filterRoom" class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 w-[140px]">
                                <option value="all" selected>All Pods</option>
                            </select>
                        </div>
                        <div>
                            <label for="filterTime" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Check-in Time</label>
                            <select id="filterTime" class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 w-[140px]">
                                <option value="" selected>All Times</option>
                                <option value="08:00">08:00</option><option value="08:15">08:15</option><option value="08:30">08:30</option><option value="08:45">08:45</option>
                                <option value="09:00">09:00</option><option value="09:15">09:15</option><option value="09:30">09:30</option><option value="09:45">09:45</option>
                                <option value="10:00">10:00</option><option value="10:15">10:15</option><option value="10:30">10:30</option><option value="10:45">10:45</option>
                                <option value="11:00">11:00</option><option value="11:15">11:15</option><option value="11:30">11:30</option><option value="11:45">11:45</option>
                                <option value="12:00">12:00</option><option value="12:15">12:15</option><option value="12:30">12:30</option><option value="12:45">12:45</option>
                                <option value="13:00">13:00</option><option value="13:15">13:15</option><option value="13:30">13:30</option><option value="13:45">13:45</option>
                                <option value="14:00">14:00</option><option value="14:15">14:15</option><option value="14:30">14:30</option><option value="14:45">14:45</option>
                                <option value="15:00">15:00</option><option value="15:15">15:15</option><option value="15:30">15:30</option><option value="15:45">15:45</option>
                                <option value="16:00">16:00</option><option value="16:15">16:15</option><option value="16:30">16:30</option><option value="16:45">16:45</option>
                                <option value="17:00">17:00</option><option value="17:15">17:15</option><option value="17:30">17:30</option><option value="17:45">17:45</option>
                                <option value="18:00">18:00</option><option value="18:15">18:15</option><option value="18:30">18:30</option><option value="18:45">18:45</option>
                                <option value="19:00">19:00</option><option value="19:15">19:15</option><option value="19:30">19:30</option><option value="19:45">19:45</option>
                                <option value="20:00">20:00</option>
                            </select>
                        </div>
                        <div>
                            <label for="searchUsername" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Search Username</label>
                            <input type="text" id="searchUsername" placeholder="Enter username..." class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 w-[200px] placeholder-gray-300">
                        </div>
                        <button onclick="resetFilters()" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            <i class="fa fa-refresh"></i> Reset
                        </button>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table id="dataTables-example" class="w-full">
                            <colgroup>
                                <col style="width:20%;">
                                <col style="width:16%;">
                                <col style="width:16%;">
                                <col style="width:16%;">
                                <col style="width:18%;">
                                <col style="width:14%;">
                            </colgroup>
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
                            <tbody id="bookingsTableBody"></tbody>
                            <tbody id="noResultsBody" style="display:none;">
                                <tr>
                                    <td colspan="6" class="empty-state text-center py-16 text-gray-700">
                                        <i class="fa fa-search text-5xl block mb-4 opacity-20"></i>
                                        No bookings found with the current filters
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- JS Scripts -->
<script src="assets/js/jquery-1.10.2.js"></script>
<script src="assets/js/tailwind-selects.js"></script>
<script src="assets/js/dataTables/jquery.dataTables.js"></script>
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
        const { createClient } = window.supabase || {};
        if (!createClient) {
            console.error('Supabase library failed to load.');
            alert('Failed to load database connection. Please refresh the page.');
            return;
        }
        supabase = createClient(window.__SUPABASE__.url, window.__SUPABASE__.anonKey);

        const { data: sessionData, error: sessionError } = await supabase.auth.getSession();
        if (sessionError || !sessionData?.session) {
            window.location.href = '../login.php';
            return;
        }

        currentUser = sessionData.session.user;

        var userRole = currentUser.user_metadata?.role || 'user';
        console.log('Current user role:', userRole);
        console.log('Current user metadata:', JSON.stringify(currentUser.user_metadata, null, 2));

        if (userRole !== 'admin') {
            console.warn('User is not an admin. RLS policies may block access to all bookings.');
        }

        console.log('Initializing dashboard - loading all bookings');

        await loadPods();
        console.log('Loaded pods:', podsData.length);

        await loadBookings();
        await loadPenalties();
        updateDashboardCards();
        populatePodFilter();

        setTimeout(function() {
            initializeDataTable();
        }, 100);

        if (refreshInterval) clearInterval(refreshInterval);
        refreshInterval = setInterval(async function() {
            await loadPods();
            await loadBookings();
            await loadPenalties();
            updateDashboardCards();
            populatePodFilter();
            if (dataTable) {
                try { dataTable.fnDestroy(); } catch (e) { console.log('Error destroying DataTable:', e); }
                dataTable = null;
            }
            setTimeout(function() {
                initializeDataTable();
                applyFilters();
            }, 100);
        }, 10000);
    });

    async function loadPods() {
        try {
            const { data, error } = await supabase
                .from('pods')
                .select('id, name, status')
                .order('created_at', { ascending: true });
            if (error) { console.error('Error loading pods:', error); return; }
            podsData = data || [];
        } catch (error) { console.error('Error in loadPods:', error); }
    }

    async function loadBookings() {
        try {
            console.log('Loading all bookings from database...');
            const { data: bookings, error: bookingsError } = await supabase
                .from('bookings')
                .select('id, user_id, pod_id, booking_date, check_in_time, check_out_time, number_of_people')
                .order('booking_date', { ascending: true })
                .order('check_in_time', { ascending: true });

            console.log('Query result - bookings:', bookings);
            console.log('Query - error:', bookingsError);

            if (bookingsError) {
                console.error('Error loading bookings:', bookingsError);
                bookingsData = [];
                populateBookingsTable();
                $('#bookingsTableBody').html('<tr><td colspan="6" class="empty-state text-center py-10 text-red-500">Error loading bookings: ' + (bookingsError.message || 'Unknown error') + '<br><small>Check browser console for details.</small></td></tr>');
                return;
            }

            console.log('Loaded all bookings:', bookings ? bookings.length : 0, 'bookings');

            if (!bookings || bookings.length === 0) {
                console.log('No such bookings exist in the database');
                bookingsData = [];
                $('#bookingsTableBody').html('<tr><td colspan="6" class="empty-state text-center py-16 text-gray-700"><i class="fa fa-calendar" style="font-size:48px;display:block;margin-bottom:15px;opacity:0.2;"></i>No such bookings exist in the database</td></tr>');
                $('#noResultsBody').hide();
                return;
            }

            var userIds = [...new Set(bookings.map(b => b.user_id).filter(id => id))];
            var podIds  = [...new Set(bookings.map(b => b.pod_id).filter(id => id))];
            var usersMap = {};
            var podsMap  = {};

            if (userIds.length > 0) {
                try {
                    const response = await fetch('get_users_info.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ user_ids: userIds })
                    });
                    if (!response.ok) throw new Error('Failed to fetch users: ' + response.statusText);
                    const data = await response.json();
                    if (data.debug) {
                        console.log('=== User Lookup Debug Info ===');
                        console.log('Requested user IDs:', data.debug.requested_ids);
                        console.log('Found users:', data.debug.found_count);
                        console.log('Found user IDs:', data.debug.found_ids);
                        if (data.debug.queries) console.log('Query details:', data.debug.queries);
                        console.log('==============================');
                    }
                    if (data.error) {
                        console.error('Error loading users:', data.error);
                    } else if (data.errors && data.errors.length > 0) {
                        console.error('Errors loading users:', data.errors);
                    }
                    if (data.users && Object.keys(data.users).length > 0) {
                        Object.keys(data.users).forEach(userId => {
                            const user = data.users[userId];
                            usersMap[userId] = user.username || (user.email ? user.email.split('@')[0] : 'User ' + userId.substring(0, 8));
                        });
                    }
                } catch (error) {
                    console.error('Error fetching users from Auth API:', error);
                }
            }

            if (podIds.length > 0) {
                podIds.forEach(podId => {
                    var pod = podsData.find(p => p.id === podId);
                    if (pod) podsMap[pod.id] = pod;
                });
                var missingPodIds = podIds.filter(id => !podsMap[id]);
                if (missingPodIds.length > 0) {
                    const { data: pods, error: podsError } = await supabase
                        .from('pods').select('id, name').in('id', missingPodIds);
                    if (!podsError && pods) {
                        pods.forEach(p => {
                            podsMap[p.id] = p;
                            if (!podsData.find(existing => existing.id === p.id)) podsData.push(p);
                        });
                    }
                }
            }

            bookingsData = bookings.map(booking => {
                var username = usersMap[booking.user_id] || 'User ' + (booking.user_id ? booking.user_id.substring(0, 8) : 'Unknown');
                var pod = podsMap[booking.pod_id] || { id: booking.pod_id, name: 'Pod ' + (booking.pod_id ? booking.pod_id.substring(0, 8) : 'Unknown') };
                var checkInTime  = booking.check_in_time  ? booking.check_in_time.substring(0, 5)  : '';
                var checkOutTime = booking.check_out_time ? booking.check_out_time.substring(0, 5) : '';
                var duration = calculateDuration(checkInTime, checkOutTime);
                return {
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
            });

            populateBookingsTable();
        } catch (error) {
            console.error('Error in loadBookings:', error);
            bookingsData = [];
            populateBookingsTable();
        }
    }

    function calculateDuration(checkIn, checkOut) {
        if (!checkIn || !checkOut) return 'N/A';
        try {
            var inParts = checkIn.split(':');
            var outParts = checkOut.split(':');
            var inMinutes  = parseInt(inParts[0]) * 60 + parseInt(inParts[1]);
            var outMinutes = parseInt(outParts[0]) * 60 + parseInt(outParts[1]);
            var diffMinutes = outMinutes - inMinutes;
            if (diffMinutes < 0) return 'N/A';
            var hours = Math.floor(diffMinutes / 60);
            var minutes = diffMinutes % 60;
            if (hours === 0) return minutes + ' min';
            if (minutes === 0) return hours + (hours === 1 ? ' hour' : ' hours');
            return hours + (hours === 1 ? ' hour' : ' hours') + ' ' + minutes + ' min';
        } catch (e) { return 'N/A'; }
    }

    function populatePodFilter() {
        var filterRoom = $('#filterRoom');
        filterRoom.find('option:not([value="all"])').remove();
        podsData.forEach(function(pod) {
            filterRoom.append('<option value="' + pod.id + '">' + (pod.name || 'Pod ' + pod.id) + '</option>');
        });
    }

    function convertTo12Hour(time24) {
        if (!time24) return '';
        var parts = time24.split(':');
        if (parts.length < 2) return time24;
        var hours = parseInt(parts[0]);
        var minutes = parts[1];
        var period = hours >= 12 ? 'PM' : 'AM';
        var hours12 = hours % 12 || 12;
        return hours12 + ':' + minutes + ' ' + period;
    }

    function getBookingStatus(bookingDate, checkIn, checkOut) {
        if (!bookingDate || !checkIn || !checkOut) return 'completed';
        var now = new Date();
        var checkInDateTime  = new Date(bookingDate + 'T' + checkIn + ':00');
        var checkOutDateTime = new Date(bookingDate + 'T' + checkOut + ':00');
        var checkInWindow  = 15 * 60 * 1000;
        var checkOutWindow = 15 * 60 * 1000;
        var checkInStart  = new Date(checkInDateTime.getTime() - checkInWindow);
        var checkInEnd    = checkInDateTime;
        var checkOutStart = new Date(checkOutDateTime.getTime() - checkOutWindow);
        try {
            if (now < checkInStart) return 'upcoming';
            else if (now >= checkInStart && now < checkInEnd) return 'in-progress-checkin';
            else if (now >= checkInEnd && now < checkOutStart) return 'in-progress-occupied';
            else if (now >= checkOutStart && now < checkOutDateTime) return 'in-progress-checkout';
            else return 'completed';
        } catch (e) { return 'completed'; }
    }

    function getStatusText(status) {
        switch(status) {
            case 'upcoming': return 'Upcoming';
            case 'in-progress-checkin': return 'In-Progress (Check-In)';
            case 'in-progress-occupied': return 'In Use';
            case 'in-progress-checkout': return 'In-Progress (Check-Out)';
            case 'completed': return 'Completed';
            case 'ongoing': return 'Ongoing';
            default: return 'Unknown';
        }
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        var date = new Date(dateStr + 'T00:00:00');
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }

    function populateBookingsTable() {
        var tbody = $('#bookingsTableBody');
        if (dataTable) {
            try { dataTable.fnDestroy(); } catch (e) { console.log('Error destroying DataTable before populate:', e); }
            dataTable = null;
        }
        tbody.empty();
        console.log('Populating table with', bookingsData.length, 'bookings');
        if (bookingsData.length === 0) return;

        bookingsData.forEach(function(booking, index) {
            try {
                if (!booking.booking_date || !booking.checkIn || !booking.checkOut) {
                    console.warn('Booking missing required fields:', booking);
                    return;
                }
                var status = getBookingStatus(booking.booking_date, booking.checkIn, booking.checkOut);
                var statusClass = 'status-' + status;
                var statusText  = getStatusText(status);
                var row = '<tr data-status="' + status + '" data-room="' + booking.room +
                    '" data-checkin="' + booking.checkIn + '" data-date="' + booking.booking_date +
                    '" data-duration="' + booking.duration + '">' +
                    '<td>' + (booking.username || 'Unknown') + '</td>' +
                    '<td>' + (booking.roomName || 'Pod ' + booking.room) + '</td>' +
                    '<td data-order="' + booking.checkIn  + '">' + convertTo12Hour(booking.checkIn) + '</td>' +
                    '<td data-order="' + booking.checkOut + '">' + convertTo12Hour(booking.checkOut) + '</td>' +
                    '<td>' + (booking.duration || 'N/A') + '</td>' +
                    '<td><span class="status-badge ' + statusClass + '">' + statusText + '</span></td>' +
                    '</tr>';
                tbody.append(row);
            } catch (error) { console.error('Error processing booking at index', index, ':', booking, error); }
        });
        console.log('Table populated with', tbody.find('tr').length, 'rows');
    }

    async function loadPenalties() {
        try {
            const { data: penalties, error: penaltiesError } = await supabase
                .from('penalties').select('id, status').eq('status', 'pending');
            if (penaltiesError) { console.error('Error loading penalties:', penaltiesError); penaltiesData = []; return; }
            penaltiesData = penalties || [];
        } catch (error) { console.error('Error in loadPenalties:', error); penaltiesData = []; }
    }

    function updateDashboardCards() {
        var now = new Date();
        var today = now.toISOString().split('T')[0];
        var occupiedRooms = 0;
        var todayBookings = bookingsData.filter(function(booking) { return booking.booking_date === today; });
        todayBookings.forEach(function(booking) {
            var status = getBookingStatus(booking.booking_date, booking.checkIn, booking.checkOut);
            if (status === 'in-progress-checkin' || status === 'in-progress-occupied' || status === 'in-progress-checkout') {
                occupiedRooms++;
            }
        });
        $('#totalBookings').text(todayBookings.length);
        $('#totalPenalties').text(penaltiesData.length);
        var operationalPods = podsData.filter(pod => pod.status !== 'suspended');
        var totalRooms = operationalPods.length > 0 ? operationalPods.length : 1;
        var occupancyRate = totalRooms > 0 ? ((occupiedRooms / totalRooms) * 100).toFixed(1) : '0.0';
        $('#occupancyRate').text(occupancyRate + '%');
        if (occupiedRooms === 0 && totalRooms > 0) {
            $('#roomStatus').text('Normal');
        } else {
            $('#roomStatus').text(occupiedRooms + '/' + totalRooms);
        }
    }

    function initializeDataTable() {
        if (dataTable) {
            try { dataTable.fnDestroy(); } catch (e) { console.log('Error destroying DataTable:', e); }
            dataTable = null;
        }
        var tbody = $('#bookingsTableBody');
        var hasDataRows = tbody.find('tr').length > 0 && !tbody.find('tr td[colspan]').length;
        if (!hasDataRows) { console.log('No data rows to initialize DataTable with'); return; }
        try {
            dataTable = $('#dataTables-example').dataTable({
                "order": [[ 2, "asc" ]],
                "paging": false,
                "searching": false,
                "info": false,
                "autoWidth": false,
                "columnDefs": [
                    { "orderable": true, "targets": [2, 3, 4] },
                    { "orderable": false, "targets": [0, 1, 5] }
                ]
            });
            console.log('DataTable initialized successfully');
        } catch (error) { console.error('Error initializing DataTable:', error); }
    }

    function applyFilters() {
        if (!dataTable) { console.log('DataTable not initialized yet'); return; }
        var statusFilter = $('#filterStatus').val();
        var roomFilter   = $('#filterRoom').val();
        var timeFilter   = $('#filterTime').val();
        var searchText   = $('#searchUsername').val().toLowerCase().trim();

        $('#bookingsTableBody tr').show();
        if (statusFilter !== 'all') {
            $('#bookingsTableBody tr').each(function() {
                var rowStatus = $(this).attr('data-status');
                if (statusFilter === 'ongoing') {
                    if (rowStatus !== 'in-progress-occupied' && rowStatus !== 'in-progress-checkin' && rowStatus !== 'in-progress-checkout') $(this).hide();
                } else if (rowStatus !== statusFilter) { $(this).hide(); }
            });
        }
        if (roomFilter !== 'all') {
            $('#bookingsTableBody tr:visible').each(function() {
                if (String($(this).attr('data-room')) !== String(roomFilter)) $(this).hide();
            });
        }
        if (timeFilter !== '') {
            $('#bookingsTableBody tr:visible').each(function() {
                if ($(this).attr('data-checkin') !== timeFilter) $(this).hide();
            });
        }
        if (searchText !== '') {
            $('#bookingsTableBody tr:visible').each(function() {
                if ($(this).find('td').eq(0).text().toLowerCase().indexOf(searchText) === -1) $(this).hide();
            });
        }
        var visibleCount = $('#bookingsTableBody tr:visible').length;
        if (visibleCount === 0) { $('#noResultsBody').show(); } else { $('#noResultsBody').hide(); }
    }

    function resetFilters() {
        $('#filterStatus').val('all');
        $('#filterRoom').val('all');
        $('#filterTime').val('');
        $('#searchUsername').val('');
        $('#bookingsTableBody tr').show();
        $('#noResultsBody').hide();
        applyFilters();
        console.log('Filters reset - showing all bookings');
    }

    function resetSidebar() {
        $('.navbar-side').removeClass('in');
        $('.navbar-side').css('left', '');
        $('.navbar-side').attr('style', '');
        $('#sidebar-overlay').remove();
        $('body').css('overflow', '');
    }

    $(window).on('beforeunload unload pagehide', function() { resetSidebar(); });

    $(document).ready(function () {
        setTimeout(function() { resetSidebar(); }, 100);

        $('#filterStatus').on('change', function() { applyFilters(); });
        $('#filterRoom').on('change', function() { applyFilters(); });
        $('#filterTime').on('change', function() { applyFilters(); });
        $('#searchUsername').on('keyup', function() { applyFilters(); });

        $('.navbar-toggle').on('click', function() {
            $('.navbar-side').toggleClass('in');
            if ($('.navbar-side').hasClass('in')) {
                if (!$('#sidebar-overlay').length) {
                    $('body').append('<div id="sidebar-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.78);z-index:39;"></div>');
                }
            } else { $('#sidebar-overlay').remove(); }
        });
        $(document).on('click', '#sidebar-overlay', function() {
            $('.navbar-side').removeClass('in');
            $(this).remove();
        });
        $('.navbar-side a').on('click', function() {
            if ($(window).width() <= 991) { $('.navbar-side').removeClass('in'); $('#sidebar-overlay').remove(); }
        });
    });

    window.addEventListener('pageshow', function(event) { setTimeout(function() { resetSidebar(); }, 50); });
    window.addEventListener('load', function() { setTimeout(function() { resetSidebar(); }, 100); });
</script>
<script src="assets/js/custom-scripts.js"></script>
</body>
</html>








