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
    <title>FORTIROOM | Intelligent Space Access Platform</title>
    <link rel="icon" href="../images/FYP_Logo_small.png" type="image/icon type">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- Morris Charts CSS -->
    <link href="assets/js/morris/morris-0.4.3.min.css" rel="stylesheet" />
    <!-- html2pdf library for PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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
        /* Hide scrollbar */
        ::-webkit-scrollbar { width: 0px; background: transparent; }

        /* Sidebar transform-based show/hide */
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
            color: rgba(255,255,255,0.85);
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .sidebar-nav-link:hover {
            background: rgba(255,255,255,0.08);
            color: #d3af37;
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

        /* form-control compat for dynamically inserted filter HTML */
        .form-control {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 7px 10px;
            font-size: 14px;
            font-family: inherit;
            color: #374151;
            background: #fff;
            outline: none;
            transition: border-color 0.15s;
        }
        .form-control:focus { border-color: #16a34a; box-shadow: 0 0 0 2px rgba(22,163,74,0.1); }
        /* Consistent dropdown corner radius */
        select,
        select.form-control,
        .dataTables_wrapper .dataTables_length select {
            border-radius: 8px !important;
            overflow: hidden;
        }
        select option { border-radius: 8px; }

        /* date input rendering fix */
        input[type="date"] {
            min-width: 150px !important;
            font-family: inherit;
            color: #333 !important;
        }
        input[type="date"]::-webkit-datetime-edit { padding: 0; display: inline-flex !important; }
        input[type="date"]::-webkit-datetime-edit-fields-wrapper { padding: 0; display: inline-flex !important; }
        input[type="date"]::-webkit-datetime-edit-text { padding: 0 0.3em; display: inline !important; }
        input[type="date"]::-webkit-datetime-edit-month-field,
        input[type="date"]::-webkit-datetime-edit-day-field { padding: 0 0.2em; min-width: 2ch !important; display: inline !important; visibility: visible !important; opacity: 1 !important; }
        input[type="date"]::-webkit-datetime-edit-year-field { padding: 0 0.2em; min-width: 4ch !important; display: inline !important; visibility: visible !important; opacity: 1 !important; }
        input[type="date"]::-webkit-calendar-picker-indicator { cursor: pointer; }
        .flatpickr-calendar {
            border-radius: 10px !important;
            border: 1px solid #e5e7eb !important;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12) !important;
            z-index: 10050 !important;
        }
        .flatpickr-input {
            border-radius: 8px !important;
        }
        .fp-month-year-row {
            display: inline-flex;
            align-items: flex-start;
            gap: 18px;
            justify-content: flex-start;
            width: 100%;
            margin-top: 4px;
        }
        .fp-month-nav {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 6px;
        }
        .fp-month-nav .flatpickr-prev-month,
        .fp-month-nav .flatpickr-next-month {
            position: static !important;
            width: auto !important;
            height: auto !important;
            padding: 0 2px !important;
        }
        .fp-month-nav .cur-month {
            font-weight: 700;
            display: inline-block;
            width: 102px;
            text-align: center;
        }
        .flatpickr-current-month {
            left: 0 !important;
            width: 100% !important;
            padding: 0 10px !important;
            text-align: left !important;
        }
        .fp-month-year-row .numInputWrapper {
            width: 76px;
            min-width: 76px;
            margin-top: 6px;
        }
        .fp-month-year-row .numInputWrapper input.cur-year {
            text-align: center;
            padding: 0 18px 0 6px;
        }
        .fp-month-year-row .numInputWrapper .arrowUp,
        .fp-month-year-row .numInputWrapper .arrowDown {
            right: 2px;
        }
        /* Status / label badges */
        .label {
            display: inline-block;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 600;
            color: #fff;
            border-radius: 4px;
            white-space: nowrap;
        }
        .label-info    { background: #0e7490; }
        .label-warning { background: #d97706; }
        .label-success { background: #16a34a; }
        .label-danger  { background: #dc2626; }

        .status-badge { padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; display: inline-block; }
        .status-upcoming  { background: #dbeafe; color: #1e40af; }
        .status-ongoing   { background: #dcfce7; color: #166534; }
        .status-completed { background: #f3f4f6; color: #374151; }

        /* DataTable overrides */
        .elevated-card { box-shadow: 0 8px 22px rgba(15, 23, 42, 0.08) !important; }
        table.dataTable,
        #dataTables-example { border-collapse: collapse !important; width: 100% !important; }
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
            text-align: center;
        }
        table.dataTable tbody td,
        #dataTables-example tbody td {
            padding: 13px 16px;
            font-size: 14px;
            color: #374151;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
            text-align: left;
        }
        table.dataTable tbody tr:hover td,
        #dataTables-example tbody tr:hover td { background: #f9fafb; }
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

        /* Chart section */
        #analytics-chart { overflow: visible !important; }
    </style>
</head>
<body class="bg-[#f7f7f5] font-sans antialiased overflow-x-hidden min-h-screen">
    <div id="wrapper">

        <!-- TOP HEADER -->
        <header class="fixed top-0 left-0 right-0 z-50 h-[60px] bg-white border-b border-gray-200 flex items-center justify-between px-4 lg:px-6">
            <div class="flex items-center gap-3">
                <button class="navbar-toggle lg:hidden flex flex-col gap-[5px] p-2 rounded-lg hover:bg-gray-100 transition-colors" aria-label="Toggle navigation">
                    <span class="w-5 h-0.5 bg-gray-600 block"></span>
                    <span class="w-5 h-0.5 bg-gray-600 block"></span>
                    <span class="w-5 h-0.5 bg-gray-600 block"></span>
                </button>
                <a href="dashboard.php">
                    <img src="../images/header_logo.png" class="h-9 w-auto" alt="Fortiroom">
                </a>
            </div>
            <ul class="navbar-top-links hidden items-center gap-1">
                <li>
                    <a href="profile.php" class="flex items-center gap-2 px-4 py-2 text-sm font-semibold hover:bg-green-100 rounded-lg transition-colors">
                        <i class="fa fa-user-circle"></i> Profile
                    </a>
                </li>
                <li>
                    <a href="logout.php" class="flex items-center gap-2 px-4 py-2 text-sm font-semibold hover:bg-green-100 rounded-lg transition-colors">
                        <i class="fa fa-sign-out"></i> Log Out
                    </a>
                </li>
            </ul>
        </header>

        <!-- SIDEBAR -->
        <aside class="navbar-side fixed top-[60px] left-0 w-[260px] bottom-0 bg-[#1f3a26] overflow-y-auto z-40">
            <nav class="sidebar-collapse py-5">
                <ul id="main-menu" class="space-y-1 px-3">
                    <li>
                        <a class="sidebar-nav-link" href="dashboard.php">
                            <i class="fa fa-dashboard fa-fw"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a class="sidebar-nav-link" href="penalties.php">
                            <i class="fa fa-gavel fa-fw"></i> Penalties
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
                        <a class="sidebar-nav-link active-menu" href="analytics.php">
                            <i class="fa fa-bar-chart-o fa-fw"></i> Analytics
                        </a>
                    </li>
                    <li class="mobile-only" style="display:none;">
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

                <!-- Page Header -->
                <div class="mb-8 pb-5 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-800 tracking-tight">SYSTEM ANALYTICS</h1>
                    </div>
                    <button id="analyticsPrintBtn" onclick="printAnalytics()" disabled
                        class="inline-flex items-center gap-2 bg-gray-100 text-gray-400 text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-all cursor-not-allowed">
                        <i class="fa fa-print"></i> Print
                    </button>
                </div>

                <!-- Filter Controls -->
                <div class="elevated-card bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
                    <div class="flex flex-wrap items-end gap-4">
                        <div class="flex flex-col gap-1">
                            <label for="filterByType" class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Filter By Type</label>
                            <select id="filterByType" class="form-control" style="width: 160px;">
                                <option value="" selected>Please Select</option>
                                <option value="both">Bookings and Penalties</option>
                                <option value="bookings">Bookings Only</option>
                                <option value="penalties">Penalties Only</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1">
                            <label for="filterByParameter" class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Filter By Parameter</label>
                            <select id="filterByParameter" class="form-control" style="width: 180px;">
                                <option value="" selected>Please Select</option>
                                <option value="date">Date Range</option>
                                <option value="month">Month</option>
                                <option value="quarterly">Quarterly</option>
                            </select>
                        </div>

                        <div id="dynamicFilterContainer" class="flex items-end gap-3">
                            <!-- Dynamic filter will be inserted here -->
                        </div>

                        <div class="flex gap-2 items-end">
                            <button onclick="applyAnalyticsFilters()"
                                class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
                                <i class="fa fa-filter"></i> Apply Filters
                            </button>
                            <button onclick="resetAnalyticsFilters()"
                                class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                                <i class="fa fa-refresh"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Analytics Chart -->
                <div class="elevated-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                        <i class="fa fa-bar-chart-o text-green-600"></i>
                        <h2 class="text-base font-semibold text-gray-800">Bookings Made vs Penalties Issued</h2>
                    </div>
                    <div class="p-6">
                        <div id="analytics-chart" style="height: 500px; min-height: 500px; display: none; overflow: visible; margin-bottom: 40px; width: 100%;"></div>

                        <div id="chart-legend" style="display: none; text-align: center; margin-top: 20px; padding: 15px; background-color: #f9fafb; border-top: 1px solid #e5e7eb;">
                            <div style="display: inline-block;">
                                <span id="legend-bookings" style="display: inline-flex; align-items: center; margin-right: 25px;">
                                    <span style="display: inline-block; width: 18px; height: 18px; background-color: #5cb85c; margin-right: 8px; border-radius: 3px;"></span>
                                    <span style="font-weight: 600; color: #374151; font-size: 13px;">Bookings Made</span>
                                </span>
                                <span id="legend-penalties" style="display: inline-flex; align-items: center;">
                                    <span style="display: inline-block; width: 18px; height: 18px; background-color: #d9534f; margin-right: 8px; border-radius: 3px;"></span>
                                    <span style="font-weight: 600; color: #374151; font-size: 13px;">Penalties Issued</span>
                                </span>
                            </div>
                        </div>

                        <div id="no-chart-data" style="display: none; text-align: center; padding: 80px 20px;">
                            <i class="fa fa-bar-chart-o" style="font-size: 64px; color: #d1d5db; display: block; margin-bottom: 16px;"></i>
                            <h3 style="color: #9ca3af; font-weight: 500; font-size: 18px; margin-bottom: 8px;">No Data Found</h3>
                            <p style="color: #d1d5db; font-size: 14px;">There are no bookings or penalties recorded for the selected period.</p>
                        </div>

                        <div id="select-prompt-chart" style="text-align: center; padding: 80px 20px;">
                            <i class="fa fa-filter" style="font-size: 64px; color: #d1d5db; display: block; margin-bottom: 16px;"></i>
                            <h3 style="color: #9ca3af; font-weight: 500; font-size: 18px; margin-bottom: 8px;">Please Select Filters</h3>
                            <p style="color: #d1d5db; font-size: 14px;">Select type of data and parameter above, then click "Apply Filters" to view analytics.</p>
                        </div>
                    </div>
                </div>

                <!-- Records Table -->
                <div class="elevated-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h2 class="text-base font-semibold text-gray-800">Bookings and Penalties Records</h2>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full" id="dataTables-example">
                                <colgroup>
                                    <col style="width:16%;">
                                    <col style="width:20%;">
                                    <col style="width:64%;">
                                </colgroup>
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
                                        <td colspan="3" style="text-align: center; padding: 40px; color: #9ca3af; font-size: 15px;">
                                            <i class="fa fa-inbox" style="font-size: 44px; display: block; margin-bottom: 12px; opacity: 0.3;"></i>
                                            <strong>No Data Found</strong><br>
                                            There are no records for the selected period.
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody id="selectPromptBody">
                                    <tr>
                                        <td colspan="3" style="text-align: center; padding: 40px; color: #9ca3af; font-size: 15px;">
                                            <i class="fa fa-filter" style="font-size: 44px; display: block; margin-bottom: 12px; opacity: 0.3;"></i>
                                            <strong>Please Select Filters</strong><br>
                                            Select type of data and parameter above, then click "Apply Filters" to view records.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </main>
        <!-- /. PAGE WRAPPER -->

    </div>
    <!-- /. WRAPPER -->

    <!-- JS Scripts -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/tailwind-selects.js"></script>
    <script src="assets/js/dataTables/jquery.dataTables.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- Morris Charts -->
    <script src="assets/js/morris/raphael-2.1.0.min.js"></script>
    <script src="assets/js/morris/morris.js"></script>
    <script>
        // Global variables
        var supabase = null;
        var currentUser = null;
        var dataTable = null;
        var morrisChart = null;
        var allRecords = []; // Combined bookings and penalties from database
        var podsData = [];
        var usersData = [];
        var masterAnalyticsData = null;
        var analyticsData = [];
        var startDatePicker = null;
        var endDatePicker = null;

        function formatFlatpickrHeader(instance) {
            if (!instance || !instance.calendarContainer) return;
            var calendar = instance.calendarContainer;
            var currentMonth = calendar.querySelector('.flatpickr-current-month');
            var monthText = currentMonth ? currentMonth.querySelector('.cur-month') : null;
            var yearWrap = currentMonth ? currentMonth.querySelector('.numInputWrapper') : null;
            var prev = calendar.querySelector('.flatpickr-prev-month');
            var next = calendar.querySelector('.flatpickr-next-month');
            if (!currentMonth || !monthText || !yearWrap || !prev || !next) return;
            if (calendar.querySelector('.fp-month-year-row')) return;

            var row = document.createElement('div');
            row.className = 'fp-month-year-row';

            var nav = document.createElement('div');
            nav.className = 'fp-month-nav';

            prev.parentNode.removeChild(prev);
            next.parentNode.removeChild(next);
            monthText.parentNode.removeChild(monthText);
            yearWrap.parentNode.removeChild(yearWrap);

            nav.appendChild(prev);
            nav.appendChild(monthText);
            nav.appendChild(next);
            row.appendChild(nav);
            row.appendChild(yearWrap);
            currentMonth.appendChild(row);
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

            // Load pods first (for lookups)
            await loadPods();
            console.log('Loaded pods:', podsData.length);

            // Load bookings and penalties from database (this will also load users)
            await loadAllRecords();
            console.log('Loaded all records:', allRecords.length);

            // Calculate analytics data from loaded records
            masterAnalyticsData = calculateAnalyticsFromRecords();
            analyticsData = masterAnalyticsData.daily;
            console.log('Calculated analytics data:', masterAnalyticsData);

            // Initialize DataTable and populate table (will be done in document.ready)
            // DataTable initialization moved to document.ready to avoid conflicts
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

        // Load users from Supabase Auth via PHP endpoint
        async function loadUsers(userIds) {
            if (!userIds || userIds.length === 0) {
                console.log('No user IDs to load');
                return {};
            }

            try {
                console.log('Loading users from Auth API for user_ids:', userIds);
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
                    return {};
                }

                if (data.users && Object.keys(data.users).length > 0) {
                    console.log('Loaded users from Auth API:', Object.keys(data.users).length);

                    // data.users is a map of user_id => { id, username, email }
                    var usersMap = {};
                    Object.keys(data.users).forEach(userId => {
                        const user = data.users[userId];
                        usersMap[userId] = user.username || (user.email ? user.email.split('@')[0] : 'User ' + userId.substring(0, 8));
                    });

                    return usersMap;
                } else {
                    console.warn('No users returned from Auth API');
                    return {};
                }
            } catch (error) {
                console.error('Error fetching users from Auth API:', error);
                return {};
            }
        }

        // Load all bookings and penalties from database
        async function loadAllRecords() {
            try {
                console.log('Loading bookings and penalties from database...');

                // Load all bookings
                const { data: bookings, error: bookingsError } = await supabase
                    .from('bookings')
                    .select('id, user_id, pod_id, booking_date, check_in_time, check_out_time, number_of_people')
                    .order('booking_date', { ascending: false })
                    .order('check_in_time', { ascending: true });

                if (bookingsError) {
                    console.error('Error loading bookings:', bookingsError);
                    console.error('Bookings error details:', JSON.stringify(bookingsError, null, 2));

                    // Check for RLS policy errors
                    var errorMsg = bookingsError.message || bookingsError.toString() || '';
                    if (errorMsg.includes('permission') || errorMsg.includes('policy') || errorMsg.includes('RLS') || errorMsg.includes('row-level security')) {
                        console.error('RLS Policy Error: Staff/Admin may not have permission to view all bookings.');
                        console.error('Please ensure RLS policies allow staff/admin users to SELECT all bookings.');
                    }
                    // Set bookings to empty array on error
                    bookings = [];
                } else {
                    console.log('Loaded bookings:', bookings ? bookings.length : 0);
                    if (bookings && bookings.length > 0) {
                        console.log('First booking sample:', bookings[0]);
                    }
                }

                // Load all penalties
                const { data: penalties, error: penaltiesError } = await supabase
                    .from('penalties')
                    .select('id, user_id, pod_id, booking_id, violation_type, penalty_amount, status, violation_date, violation_time')
                    .order('violation_date', { ascending: false });

                if (penaltiesError) {
                    console.error('Error loading penalties:', penaltiesError);
                    console.error('Penalties error details:', JSON.stringify(penaltiesError, null, 2));

                    // Check for RLS policy errors
                    var errorMsg = penaltiesError.message || penaltiesError.toString() || '';
                    if (errorMsg.includes('permission') || errorMsg.includes('policy') || errorMsg.includes('RLS') || errorMsg.includes('row-level security')) {
                        console.error('RLS Policy Error: Staff/Admin may not have permission to view all penalties.');
                        console.error('Please ensure RLS policies allow staff/admin users to SELECT all penalties.');
                    }
                    // Set penalties to empty array on error
                    penalties = [];
                } else {
                    console.log('Loaded penalties:', penalties ? penalties.length : 0);
                    if (penalties && penalties.length > 0) {
                        console.log('First penalty sample:', penalties[0]);
                    }
                }

                // Collect all unique user IDs from bookings and penalties
                var userIds = new Set();
                if (bookings && bookings.length > 0) {
                    bookings.forEach(function(booking) {
                        if (booking.user_id) {
                            userIds.add(booking.user_id);
                        }
                    });
                }
                if (penalties && penalties.length > 0) {
                    penalties.forEach(function(penalty) {
                        if (penalty.user_id) {
                            userIds.add(penalty.user_id);
                        }
                    });
                }

                // Load users from Supabase Auth via PHP endpoint
                var usersMap = {};
                if (userIds.size > 0) {
                    usersMap = await loadUsers(Array.from(userIds));
                    console.log('Loaded users map with', Object.keys(usersMap).length, 'users');
                } else {
                    console.log('No user IDs found in bookings or penalties');
                }

                // Create pods map for quick lookup
                var podsMap = {};
                podsData.forEach(function(pod) {
                    podsMap[pod.id] = pod;
                });
                console.log('Created pods map with', Object.keys(podsMap).length, 'pods');

                // Convert bookings to records format
                var bookingRecords = [];
                if (bookings && bookings.length > 0) {
                    bookingRecords = bookings.map(function(booking) {
                        var username = usersMap[booking.user_id] || ('User ' + (booking.user_id ? booking.user_id.substring(0, 8) : 'Unknown'));
                        var pod = podsMap[booking.pod_id];
                        var podName = pod ? (pod.name || 'Pod ' + pod.id) : (booking.pod_id ? 'Pod ' + booking.pod_id.substring(0, 8) : 'Unknown Pod');

                        // Format time - ensure we have valid time strings
                        var checkInTime = booking.check_in_time || '';
                        var checkOutTime = booking.check_out_time || '';
                        if (checkInTime && typeof checkInTime === 'string') {
                            // Extract HH:MM from time string (could be HH:MM:SS or just HH:MM)
                            checkInTime = checkInTime.substring(0, 5);
                        }
                        if (checkOutTime && typeof checkOutTime === 'string') {
                            checkOutTime = checkOutTime.substring(0, 5);
                        }

                        // Calculate duration
                        var duration = calculateDuration(checkInTime, checkOutTime);
                        var details = checkInTime && checkOutTime ? (checkInTime + ' - ' + checkOutTime + ' (' + duration + ')') : 'Time not specified';

                        return {
                            type: 'booking',
                            username: username,
                            date: booking.booking_date || '',
                            details: details,
                            pod: podName,
                            status: 'completed', // All historical bookings are completed
                            userId: booking.user_id,
                            podId: booking.pod_id,
                            bookingId: booking.id,
                            numberOfPeople: booking.number_of_people || 0
                        };
                    });
                    console.log('Converted', bookingRecords.length, 'bookings to records');
                } else {
                    console.log('No bookings found in database');
                }

                // Convert penalties to records format
                var penaltyRecords = [];
                if (penalties && penalties.length > 0) {
                    penaltyRecords = penalties.map(function(penalty) {
                        var username = usersMap[penalty.user_id] || ('User ' + (penalty.user_id ? penalty.user_id.substring(0, 8) : 'Unknown'));
                        var pod = podsMap[penalty.pod_id];
                        var podName = pod ? (pod.name || 'Pod ' + pod.id) : (penalty.pod_id ? 'Pod ' + penalty.pod_id.substring(0, 8) : 'N/A');

                        var violationType = penalty.violation_type || 'Unknown Violation';
                        var penaltyAmount = penalty.penalty_amount ? '$' + parseFloat(penalty.penalty_amount).toFixed(2) : '';
                        var details = violationType + (penaltyAmount ? ' - ' + penaltyAmount : '');

                        return {
                            type: 'penalty',
                            username: username,
                            date: penalty.violation_date || '',
                            details: details,
                            pod: podName,
                            status: penalty.status || 'pending',
                            userId: penalty.user_id,
                            podId: penalty.pod_id,
                            bookingId: penalty.booking_id,
                            penaltyId: penalty.id,
                            violationType: violationType,
                            penaltyAmount: penalty.penalty_amount
                        };
                    });
                    console.log('Converted', penaltyRecords.length, 'penalties to records');
                } else {
                    console.log('No penalties found in database');
                }

                // Combine bookings and penalties
                allRecords = bookingRecords.concat(penaltyRecords);
                console.log('Total records:', allRecords.length, '(bookings:', bookingRecords.length, ', penalties:', penaltyRecords.length, ')');

                // Sort by date (newest first), then by type if same date
                allRecords.sort(function(a, b) {
                    if (a.date !== b.date) {
                        return b.date.localeCompare(a.date);
                    }
                    // If same date, bookings come before penalties
                    if (a.type !== b.type) {
                        return a.type === 'booking' ? -1 : 1;
                    }
                    return 0;
                });

                console.log('Records sorted and ready for display');

            } catch (error) {
                console.error('Error in loadAllRecords:', error);
                console.error('Error stack:', error.stack);
                allRecords = [];
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

        // Function to calculate analytics data from actual records
        function calculateAnalyticsFromRecords() {
            console.log('Calculating analytics from', allRecords.length, 'records');

            // Initialize daily analytics by day of week (0=Sunday, 1=Monday, etc.)
            var dailyCounts = {
                0: { period: 'Sun', bookings: 0, penalties: 0 }, // Sunday
                1: { period: 'Mon', bookings: 0, penalties: 0 }, // Monday
                2: { period: 'Tue', bookings: 0, penalties: 0 }, // Tuesday
                3: { period: 'Wed', bookings: 0, penalties: 0 }, // Wednesday
                4: { period: 'Thu', bookings: 0, penalties: 0 }, // Thursday
                5: { period: 'Fri', bookings: 0, penalties: 0 }, // Friday
                6: { period: 'Sat', bookings: 0, penalties: 0 }  // Saturday
            };

            var analytics = {
                daily: [
                    dailyCounts[1], // Monday first (most common start of week)
                    dailyCounts[2], // Tuesday
                    dailyCounts[3], // Wednesday
                    dailyCounts[4], // Thursday
                    dailyCounts[5], // Friday
                    dailyCounts[6], // Saturday
                    dailyCounts[0]  // Sunday last
                ],
                monthly: {},
                quarterly: []
            };

            // Find all unique years from records
            var yearsSet = new Set();
            allRecords.forEach(function(record) {
                if (!record.date) return;
                try {
                    // Parse date - handle both YYYY-MM-DD format and Date objects
                    var date = new Date(record.date + 'T00:00:00'); // Add time to avoid timezone issues
                    if (!isNaN(date.getTime())) {
                        yearsSet.add(date.getFullYear());
                    }
                } catch (e) {
                    // Skip invalid dates
                }
            });

            // Add current year and next year if not already present (for future data)
            var currentYear = new Date().getFullYear();
            yearsSet.add(currentYear);
            yearsSet.add(currentYear + 1);

            // Initialize monthly data for all found years
            var years = Array.from(yearsSet).sort();
            years.forEach(function(year) {
                analytics.monthly[year] = {};
                for (var i = 0; i < 12; i++) {
                    analytics.monthly[year][i] = { bookings: 0, penalties: 0 };
                }
            });

            console.log('Initialized analytics for years:', years);

            // Count records by day of week, month, and year
            var bookingCount = 0;
            var penaltyCount = 0;

            allRecords.forEach(function(record) {
                if (!record.date) {
                    console.warn('Record missing date:', record);
                    return;
                }

                try {
                    // Parse date - add time to avoid timezone conversion issues
                    var date = new Date(record.date + 'T00:00:00');
                    if (isNaN(date.getTime())) {
                        console.warn('Invalid date:', record.date);
                        return;
                    }

                    var year = date.getFullYear();
                    var month = date.getMonth(); // 0-11
                    var dayOfWeek = date.getDay(); // 0 (Sunday) to 6 (Saturday)

                    // Ensure year exists in monthly data (should always be true, but check anyway)
                    if (!analytics.monthly[year]) {
                        analytics.monthly[year] = {};
                        for (var i = 0; i < 12; i++) {
                            analytics.monthly[year][i] = { bookings: 0, penalties: 0 };
                        }
                    }

                    // Update analytics based on record type
                    if (record.type === 'booking') {
                        // Update daily analytics (by day of week)
                        dailyCounts[dayOfWeek].bookings++;
                        // Update monthly analytics
                        analytics.monthly[year][month].bookings++;
                        // Count total bookings
                        bookingCount++;
                    } else if (record.type === 'penalty') {
                        // Update daily analytics (by day of week)
                        dailyCounts[dayOfWeek].penalties++;
                        // Update monthly analytics
                        analytics.monthly[year][month].penalties++;
                        // Count total penalties
                        penaltyCount++;
                    }
                } catch (e) {
                    console.error('Error processing record date:', record.date, e);
                }
            });

            // Update daily array with populated counts
            analytics.daily = [
                dailyCounts[1], // Monday
                dailyCounts[2], // Tuesday
                dailyCounts[3], // Wednesday
                dailyCounts[4], // Thursday
                dailyCounts[5], // Friday
                dailyCounts[6], // Saturday
                dailyCounts[0]  // Sunday
            ];

            console.log('Processed', bookingCount, 'bookings and', penaltyCount, 'penalties for analytics');
            console.log('Daily analytics:', analytics.daily);

            // Calculate quarterly data
            var quarterlyData = {};
            allRecords.forEach(function(record) {
                if (!record.date) return;

                try {
                    var date = new Date(record.date + 'T00:00:00');
                    if (isNaN(date.getTime())) return;

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
                } catch (e) {
                    console.error('Error processing record for quarterly:', record.date, e);
                }
            });

            // Convert quarterly object to array and sort by year and quarter
            analytics.quarterly = Object.values(quarterlyData).sort(function(a, b) {
                // Extract year and quarter from period string (e.g., "Q1 2024")
                var aMatch = a.period.match(/Q(\d+)\s+(\d+)/);
                var bMatch = b.period.match(/Q(\d+)\s+(\d+)/);

                if (!aMatch || !bMatch) {
                    return a.period.localeCompare(b.period);
                }

                var aYear = parseInt(aMatch[2]);
                var bYear = parseInt(bMatch[2]);
                var aQuarter = parseInt(aMatch[1]);
                var bQuarter = parseInt(bMatch[1]);

                // Sort by year first, then by quarter
                if (aYear !== bYear) {
                    return aYear - bYear;
                }
                return aQuarter - bQuarter;
            });

            console.log('Analytics calculation complete. Monthly data for years:', Object.keys(analytics.monthly));
            console.log('Quarterly data:', analytics.quarterly.length, 'quarters');

            return analytics;
        }

        function initializeChart(data, hasData, typeFilter) {
            // If no data, show message instead of chart
            if (hasData === false) {
                $('.morris-hover').hide();
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

            // Ensure data is properly formatted and null-safe
            var toNumber = function(value) {
                var num = Number(value);
                return isFinite(num) ? num : 0;
            };
            var rawChartData = data || analyticsData;
            var normalizedMap = {};
            (rawChartData || []).forEach(function(item) {
                if (!item) return;
                var periodKey = (item.period || '').toString().trim();
                if (!periodKey) return;
                normalizedMap[periodKey] = {
                    period: periodKey,
                    bookings: toNumber(item.bookings),
                    penalties: toNumber(item.penalties)
                };
            });
            var chartDataToUse = Object.keys(normalizedMap).map(function(key) { return normalizedMap[key]; });
            if (!chartDataToUse || chartDataToUse.length === 0) {
                console.warn('No chart data to display');
                $('#analytics-chart').hide();
                $('#chart-legend').hide();
                $('#no-chart-data').show();
                return;
            }

            // Log the data being used for debugging
            console.log('Chart data:', chartDataToUse);
            console.log('Chart data length:', chartDataToUse.length);

            // Calculate chart options based on data length
            var xLabelAngle = 0;
            var seriesCount = ykeys.length;
            var barSizeRatio = seriesCount > 1 ? 0.5 : 0.6;
            var barGap = seriesCount > 1 ? 6 : 4;
            var gridTextSize = 11;

            // Adjust chart options based on number of periods
            if (chartDataToUse.length >= 12) {
                xLabelAngle = 45;
                barSizeRatio = seriesCount > 1 ? 0.34 : 0.42;
                gridTextSize = 10;
            } else if (chartDataToUse.length > 8) {
                xLabelAngle = 45;
                barSizeRatio = seriesCount > 1 ? 0.38 : 0.5;
            } else if (chartDataToUse.length > 4) {
                barSizeRatio = seriesCount > 1 ? 0.42 : 0.54;
            } else if (chartDataToUse.length <= 4) {
                barSizeRatio = seriesCount > 1 ? 0.34 : 0.5;
            }

            // Destroy existing chart if it exists to prevent conflicts
            if (morrisChart) {
                try {
                    morrisChart.destroy();
                } catch (e) {
                    // If destroy fails, just clear the element
                    $('#analytics-chart').empty();
                }
                morrisChart = null;
            }

            morrisChart = Morris.Bar({
                element: 'analytics-chart',
                data: chartDataToUse,
                xkey: 'period',
                ykeys: ykeys,
                labels: labels,
                barColors: barColors,
                hideHover: false,  // Show hover tooltip
                resize: true,
                gridTextSize: gridTextSize,
                barSizeRatio: barSizeRatio,
                barGap: barGap,
                xLabelAngle: xLabelAngle,
                parseTime: false,
                axes: true,
                grid: true,
                stacked: false,
                xLabelMargin: 10
            });

            // Store data for label functions
            window.currentChartData = chartDataToUse;
            window.currentTypeFilter = typeFilter;
            window.currentYKeys = ykeys;

            // Wait for chart to render, then add data labels on top of bars
            // Use multiple timeouts to ensure chart is fully rendered
            setTimeout(function() {
                // Center lone bars (when the other series is zero) on the x-axis category
                centerSingleSeriesBars(chartDataToUse, typeFilter);
                // Clear any existing data labels first
                $('#analytics-chart svg .data-label').remove();
                addDataLabels();
            }, 600);  // Increased timeout to ensure chart is fully rendered

            // Morris occasionally leaves the hover tooltip visible after pointer exits chart/card.
            $('#analytics-chart').off('.morrisHoverCleanup');
            $('#analytics-chart').closest('.elevated-card').off('.morrisHoverCleanup');
            $(document).off('mousemove.morrisHoverCleanup');
            $('#analytics-chart').on('mouseleave.morrisHoverCleanup', function() {
                $('.morris-hover').hide();
            });
            $('#analytics-chart').closest('.elevated-card').on('mouseleave.morrisHoverCleanup', function() {
                $('.morris-hover').hide();
            });
            $(document).on('mousemove.morrisHoverCleanup', function(e) {
                var chartEl = document.getElementById('analytics-chart');
                if (!chartEl || $('#analytics-chart').is(':hidden')) {
                    $('.morris-hover').hide();
                    return;
                }

                var rect = chartEl.getBoundingClientRect();
                var insideChart =
                    e.clientX >= rect.left &&
                    e.clientX <= rect.right &&
                    e.clientY >= rect.top &&
                    e.clientY <= rect.bottom;

                if (!insideChart) {
                    $('.morris-hover').hide();
                }
            });
        }

        function centerSingleSeriesBars(chartData, typeFilter) {
            if (typeFilter !== 'both' || !chartData || chartData.length === 0) return;

            var svg = $('#analytics-chart svg');
            if (!svg.length) return;

            var chartHeight = parseFloat(svg.attr('height')) || 400;
            var chartWidth = parseFloat(svg.attr('width')) || 800;
            var chartPadding = 60;
            var chartAreaWidth = chartWidth - (chartPadding * 2);
            var periodSpacing = chartAreaWidth / Math.max(chartData.length, 1);

            var periodToLabelX = {};
            svg.find('text').each(function() {
                var text = ($(this).text() || '').trim();
                var yPos = parseFloat($(this).attr('y')) || 0;
                var xPos = parseFloat($(this).attr('x')) || 0;
                if (yPos > chartHeight - 80) {
                    for (var i = 0; i < chartData.length; i++) {
                        var period = (chartData[i].period || '').toString().trim();
                        if (period === text) {
                            periodToLabelX[period] = xPos;
                            break;
                        }
                    }
                }
            });

            var barsByType = { bookings: [], penalties: [] };
            svg.find('rect').each(function() {
                var fill = (($(this).attr('fill') || '') + '').toLowerCase();
                var width = parseFloat($(this).attr('width')) || 0;
                var height = parseFloat($(this).attr('height')) || 0;
                var x = parseFloat($(this).attr('x')) || 0;
                var y = parseFloat($(this).attr('y')) || 0;
                if (width <= 0 || height <= 1 || y >= chartHeight - 70) return;

                var isGreen = fill === '#5cb85c' || fill === 'rgb(92, 184, 92)';
                var isRed = fill === '#d9534f' || fill === 'rgb(217, 83, 79)';
                if (!isGreen && !isRed) return;

                var bar = {
                    el: this,
                    width: width,
                    centerX: x + (width / 2),
                    used: false
                };
                if (isGreen) barsByType.bookings.push(bar);
                if (isRed) barsByType.penalties.push(bar);
            });

            barsByType.bookings.sort(function(a, b) { return a.centerX - b.centerX; });
            barsByType.penalties.sort(function(a, b) { return a.centerX - b.centerX; });

            function takeNearestBar(type, expectedX) {
                var bars = barsByType[type] || [];
                var bestIndex = -1;
                var bestDistance = Infinity;
                for (var i = 0; i < bars.length; i++) {
                    if (bars[i].used) continue;
                    var distance = Math.abs(bars[i].centerX - expectedX);
                    if (distance < bestDistance) {
                        bestDistance = distance;
                        bestIndex = i;
                    }
                }
                if (bestIndex < 0) return null;
                bars[bestIndex].used = true;
                return bars[bestIndex];
            }

            for (var i = 0; i < chartData.length; i++) {
                var point = chartData[i] || {};
                var periodKey = (point.period || '').toString().trim();
                if (!periodKey) continue;

                var bookingsVal = Number(point.bookings) || 0;
                var penaltiesVal = Number(point.penalties) || 0;
                var hasBookings = bookingsVal > 0;
                var hasPenalties = penaltiesVal > 0;
                if (hasBookings === hasPenalties) continue; // both present or both missing

                var expectedX = periodToLabelX[periodKey];
                if (typeof expectedX !== 'number' || isNaN(expectedX)) {
                    expectedX = chartPadding + (i * periodSpacing);
                }

                var targetType = hasBookings ? 'bookings' : 'penalties';
                var bar = takeNearestBar(targetType, expectedX);
                if (!bar) continue;

                var newX = expectedX - (bar.width / 2);
                bar.el.setAttribute('x', newX);
                bar.centerX = expectedX;
            }
        }

        function addDataLabels() {
            // Get the SVG element
            var svg = $('#analytics-chart svg');
            if (!svg.length) {
                console.warn('Chart SVG not found');
                return;
            }

            var svgElement = svg[0];
            var chartData = window.currentChartData;
            var typeFilter = window.currentTypeFilter;

            if (!chartData || chartData.length === 0) {
                console.warn('No chart data for labels');
                return;
            }

            // Get chart dimensions
            var chartHeight = parseFloat(svg.attr('height')) || 400;
            var chartWidth = parseFloat(svg.attr('width')) || 800;

            // Collect all bars (green and red separately)
            var greenBars = [];
            var redBars = [];

            svg.find('rect').each(function() {
                var fill = $(this).attr('fill');
                var height = parseFloat($(this).attr('height')) || 0;
                var y = parseFloat($(this).attr('y')) || 0;
                var x = parseFloat($(this).attr('x')) || 0;
                var width = parseFloat($(this).attr('width')) || 0;

                // Only process bars that are actual data bars (colored bars with significant height)
                if (height > 1 && y < chartHeight - 70 && width > 0) {
                    var isGreen = fill === '#5cb85c' || fill === 'rgb(92, 184, 92)' || fill.toLowerCase() === '#5cb85c';
                    var isRed = fill === '#d9534f' || fill === 'rgb(217, 83, 79)' || fill.toLowerCase() === '#d9534f';

                    if (isGreen) {
                        greenBars.push({
                            centerX: x + (width / 2),
                            y: y,
                            x: x,
                            width: width
                        });
                    } else if (isRed) {
                        redBars.push({
                            centerX: x + (width / 2),
                            y: y,
                            x: x,
                            width: width
                        });
                    }
                }
            });

            // Get x-axis label positions from Morris.js (these are the period labels)
            // Morris.js creates labels for each data point, positioned at the center of each period group
            var labelPositions = {};
            var allLabels = [];

            svg.find('text').each(function() {
                var text = $(this).text().trim();
                var yPos = parseFloat($(this).attr('y')) || 0;
                var xPos = parseFloat($(this).attr('x')) || 0;

                // X-axis labels are near the bottom of the chart
                // Check if this text matches a period from our data
                if (yPos > chartHeight - 80) {
                    // Try to match this label to a period in our data
                    for (var i = 0; i < chartData.length; i++) {
                        if (chartData[i].period === text || chartData[i].period.trim() === text) {
                            labelPositions[i] = {
                                x: xPos,
                                period: chartData[i].period,
                                index: i
                            };
                            allLabels.push({
                                x: xPos,
                                period: chartData[i].period,
                                index: i
                            });
                            break;
                        }
                    }
                }
            });

            // Sort labels by X position to maintain order
            allLabels.sort(function(a, b) { return a.x - b.x; });

            // Also create a map of period to label position for quick lookup
            var periodToLabelMap = {};
            allLabels.forEach(function(label) {
                periodToLabelMap[label.period] = label.x;
            });

            console.log('Found', allLabels.length, 'x-axis labels out of', chartData.length, 'data points');
            console.log('Label positions:', labelPositions);
            console.log('Green bars found:', greenBars.length);
            console.log('Red bars found:', redBars.length);
            console.log('Bar groups:', barGroups.length);

            // Sort bars by X position (left to right) - this matches data order
            greenBars.sort(function(a, b) { return a.centerX - b.centerX; });
            redBars.sort(function(a, b) { return a.centerX - b.centerX; });

            // Morris.js creates bars in order: for each data point, it creates bars for each ykey
            // When typeFilter is 'both': creates bookings bar, then penalties bar (side by side)
            // When typeFilter is 'bookings': creates bookings bar only
            // When typeFilter is 'penalties': creates penalties bar only
            // Bars are created sequentially, so we can match them by order

            // Group bars by period (bars close together belong to same period)
            // Calculate period spacing
            var chartPadding = 60;
            var chartAreaWidth = chartWidth - (chartPadding * 2);
            var periodSpacing = chartAreaWidth / Math.max(chartData.length, 1);
            var groupingTolerance = periodSpacing * 0.3; // Bars within 30% of period spacing are grouped

            // Group all bars (green and red) by X position
            var allBars = [];
            greenBars.forEach(function(bar) {
                bar.barType = 'bookings';
                allBars.push(bar);
            });
            redBars.forEach(function(bar) {
                bar.barType = 'penalties';
                allBars.push(bar);
            });
            allBars.sort(function(a, b) { return a.centerX - b.centerX; });

            // Group bars that are close together (same period)
            var barGroups = [];
            if (allBars.length > 0) {
                var currentGroup = [allBars[0]];
                for (var i = 1; i < allBars.length; i++) {
                    var lastBar = currentGroup[currentGroup.length - 1];
                    var distance = Math.abs(allBars[i].centerX - lastBar.centerX);
                    if (distance < groupingTolerance) {
                        currentGroup.push(allBars[i]);
                    } else {
                        barGroups.push(currentGroup);
                        currentGroup = [allBars[i]];
                    }
                }
                if (currentGroup.length > 0) {
                    barGroups.push(currentGroup);
                }
            }

            // Match bars to periods using a hybrid approach:
            // 1. Use x-axis label positions as anchors for expected positions
            // 2. Match bars sequentially but verify with label positions
            // 3. For zero values, use label position directly

            var greenBarIndex = 0;
            var redBarIndex = 0;

            for (var i = 0; i < chartData.length; i++) {
                var dataPoint = chartData[i];
                var labelPos = labelPositions[i];

                // Determine expected X position for this period (use label position as anchor)
                var expectedX = null;
                if (labelPos) {
                    // Use actual label position - this is the most accurate
                    expectedX = labelPos.x;
                } else if (allLabels.length > 0) {
                    // Estimate based on other labels - interpolate
                    if (i === 0) {
                        expectedX = allLabels[0].x;
                    } else if (i === chartData.length - 1) {
                        expectedX = allLabels[allLabels.length - 1].x;
                    } else {
                        // Interpolate between existing labels
                        var ratio = i / (chartData.length - 1);
                        expectedX = allLabels[0].x + (ratio * (allLabels[allLabels.length - 1].x - allLabels[0].x));
                    }
                } else {
                    // Fallback: calculate based on chart width
                    var spacing = chartAreaWidth / Math.max(chartData.length - 1, 1);
                    expectedX = chartPadding + (i * spacing);
                }

                // Find bars for this period
                var closestGreenBar = null;
                var closestRedBar = null;

                // For bookings: match sequentially but verify position
                if ((typeFilter === 'both' || typeFilter === 'bookings') && (dataPoint.bookings || 0) > 0) {
                    if (greenBarIndex < greenBars.length) {
                        var candidateBar = greenBars[greenBarIndex];
                        var distance = Math.abs(candidateBar.centerX - expectedX);
                        var tolerance = periodSpacing * 0.5; // Allow 50% tolerance

                        // If bar is close to expected position, use it
                        if (distance < tolerance) {
                            closestGreenBar = candidateBar;
                            greenBarIndex++;
                        } else {
                            // Bar is too far - try to find a closer one
                            var bestBar = null;
                            var bestDistance = Infinity;
                            for (var g = greenBarIndex; g < greenBars.length; g++) {
                                var testBar = greenBars[g];
                                var testDistance = Math.abs(testBar.centerX - expectedX);
                                if (testDistance < tolerance && testDistance < bestDistance) {
                                    bestDistance = testDistance;
                                    bestBar = testBar;
                                }
                            }
                            if (bestBar) {
                                closestGreenBar = bestBar;
                                var bestIndex = greenBars.indexOf(bestBar);
                                greenBars.splice(bestIndex, 1);
                            } else {
                                // No good match found, use sequential anyway
                                closestGreenBar = greenBars[greenBarIndex];
                                greenBarIndex++;
                            }
                        }
                    }
                }

                // For penalties: match sequentially but verify position
                if ((typeFilter === 'both' || typeFilter === 'penalties') && (dataPoint.penalties || 0) > 0) {
                    if (redBarIndex < redBars.length) {
                        var candidateBar = redBars[redBarIndex];
                        var distance = Math.abs(candidateBar.centerX - expectedX);
                        var tolerance = periodSpacing * 0.5; // Allow 50% tolerance

                        // If bar is close to expected position, use it
                        if (distance < tolerance) {
                            closestRedBar = candidateBar;
                            redBarIndex++;
                        } else {
                            // Bar is too far - try to find a closer one
                            var bestBar = null;
                            var bestDistance = Infinity;
                            for (var r = redBarIndex; r < redBars.length; r++) {
                                var testBar = redBars[r];
                                var testDistance = Math.abs(testBar.centerX - expectedX);
                                if (testDistance < tolerance && testDistance < bestDistance) {
                                    bestDistance = testDistance;
                                    bestBar = testBar;
                                }
                            }
                            if (bestBar) {
                                closestRedBar = bestBar;
                                var bestIndex = redBars.indexOf(bestBar);
                                redBars.splice(bestIndex, 1);
                            } else {
                                // No good match found, use sequential anyway
                                closestRedBar = redBars[redBarIndex];
                                redBarIndex++;
                            }
                        }
                    }
                }

                // Add booking label for ALL values (including zeros)
                if ((typeFilter === 'both' || typeFilter === 'bookings')) {
                    var bookingValue = dataPoint.bookings || 0;
                    var barY = chartHeight - 30; // Default position at bottom for zero values

                    if (closestGreenBar) {
                        // Use bar position if bar exists
                        barY = Math.max(closestGreenBar.y - 8, 18);
                    }

                    // Calculate X position - ALWAYS use expectedX (label position) for alignment
                    // This ensures labels align with their periods, not just bars
                    var labelX = expectedX;

                    // When showing both and we have a bar, offset slightly to the left
                    if (typeFilter === 'both' && closestGreenBar) {
                        labelX = closestGreenBar.centerX - (closestGreenBar.width * 0.25);
                    } else if (typeFilter === 'both' && !closestGreenBar) {
                        labelX = expectedX - 15; // Offset left for zero values when showing both
                    } else if (closestGreenBar && typeFilter === 'bookings') {
                        // When showing only bookings, use bar center
                        labelX = closestGreenBar.centerX;
                    }

                    var text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                    text.setAttribute('x', labelX);
                    text.setAttribute('y', barY);
                    text.setAttribute('text-anchor', 'middle');
                    text.setAttribute('font-family', 'Arial, sans-serif');
                    text.setAttribute('font-size', '13px');
                    text.setAttribute('font-weight', 'bold');
                    text.setAttribute('fill', bookingValue > 0 ? '#2c3e50' : '#999'); // Gray for zeros
                    text.setAttribute('class', 'data-label');
                    text.setAttribute('opacity', bookingValue > 0 ? '1' : '0.6'); // Slightly transparent for zeros
                    text.textContent = bookingValue;
                    svgElement.appendChild(text);
                }

                // Add penalty label for ALL values (including zeros)
                if ((typeFilter === 'both' || typeFilter === 'penalties')) {
                    var penaltyValue = dataPoint.penalties || 0;
                    var barY = chartHeight - 30; // Default position at bottom for zero values

                    if (closestRedBar) {
                        // Use bar position if bar exists
                        barY = Math.max(closestRedBar.y - 8, 18);
                    }

                    // Calculate X position - ALWAYS use expectedX (label position) for alignment
                    // This ensures labels align with their periods, not just bars
                    var labelX = expectedX;

                    // When showing both and we have a bar, offset slightly to the right
                    if (typeFilter === 'both' && closestRedBar) {
                        labelX = closestRedBar.centerX + (closestRedBar.width * 0.25);
                    } else if (typeFilter === 'both' && !closestRedBar) {
                        labelX = expectedX + 15; // Offset right for zero values when showing both
                    } else if (closestRedBar && typeFilter === 'penalties') {
                        // When showing only penalties, use bar center
                        labelX = closestRedBar.centerX;
                    }

                    var text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                    text.setAttribute('x', labelX);
                    text.setAttribute('y', barY);
                    text.setAttribute('text-anchor', 'middle');
                    text.setAttribute('font-family', 'Arial, sans-serif');
                    text.setAttribute('font-size', '13px');
                    text.setAttribute('font-weight', 'bold');
                    text.setAttribute('fill', penaltyValue > 0 ? '#2c3e50' : '#999'); // Gray for zeros
                    text.setAttribute('class', 'data-label');
                    text.setAttribute('opacity', penaltyValue > 0 ? '1' : '0.6'); // Slightly transparent for zeros
                    text.textContent = penaltyValue;
                    svgElement.appendChild(text);
                }
            }

            console.log('Added data labels - Data points:', chartData.length, 'X-axis labels found:', allLabels.length);
        }

        function updateDynamicFilter() {
            var parameter = $('#filterByParameter').val();
            var container = $('#dynamicFilterContainer');
            container.empty();

            var filterHTML = '';

            if (parameter === 'date') {
                filterHTML = '<div style="display:flex;align-items:flex-end;gap:10px;">' +
                    '<div style="display:flex;flex-direction:column;gap:4px;"><label style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">From</label>' +
                    '<input type="text" id="startDate" placeholder="dd-mm-yyyy" autocomplete="off" class="form-control" style="display: inline-block; width: 150px;"></div> ' +
                    '<div style="display:flex;flex-direction:column;gap:4px;"><label style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">To</label>' +
                    '<input type="text" id="endDate" placeholder="dd-mm-yyyy" autocomplete="off" class="form-control" style="display: inline-block; width: 150px;"></div>' +
                    '</div>';
            } else if (parameter === 'month') {
                // Get available years from masterAnalyticsData
                var availableYears = Object.keys(masterAnalyticsData ? masterAnalyticsData.monthly : {}).sort(function(a, b) {
                    return parseInt(b) - parseInt(a); // Descending order (newest first)
                });

                // If no years found, use current year and next year
                if (availableYears.length === 0) {
                    var currentYear = new Date().getFullYear();
                    availableYears = [currentYear, currentYear + 1];
                }

                var yearOptions = availableYears.map(function(year) {
                    var selected = year == new Date().getFullYear() ? ' selected' : '';
                    return '<option value="' + year + '"' + selected + '>' + year + '</option>';
                }).join('');

                filterHTML = '<div style="display:flex;flex-direction:column;gap:4px;">' +
                    '<label style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">Select Year</label>' +
                    '<select id="yearSelect" class="form-control" style="display: inline-block; width: 160px;">' +
                    yearOptions +
                    '</select></div>';
            } else if (parameter === 'quarterly') {
                // Get available years from quarterly data or monthly data
                var availableYears = new Set();
                if (masterAnalyticsData && masterAnalyticsData.quarterly && masterAnalyticsData.quarterly.length > 0) {
                    masterAnalyticsData.quarterly.forEach(function(item) {
                        var yearMatch = item.period.match(/\d{4}/);
                        if (yearMatch) {
                            availableYears.add(parseInt(yearMatch[0]));
                        }
                    });
                }
                if (masterAnalyticsData && masterAnalyticsData.monthly) {
                    Object.keys(masterAnalyticsData.monthly).forEach(function(year) {
                        availableYears.add(parseInt(year));
                    });
                }

                // Convert to array and sort
                var yearsArray = Array.from(availableYears).sort(function(a, b) {
                    return b - a; // Descending order (newest first)
                });

                // If no years found, use current year and next year
                if (yearsArray.length === 0) {
                    var currentYear = new Date().getFullYear();
                    yearsArray = [currentYear, currentYear + 1];
                }

                var yearOptions = yearsArray.map(function(year) {
                    var selected = year == new Date().getFullYear() ? ' selected' : '';
                    return '<option value="' + year + '"' + selected + '>' + year + '</option>';
                }).join('');

                filterHTML = '<div style="display:flex;flex-direction:column;gap:4px;">' +
                    '<label style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">Select Year</label>' +
                    '<select id="yearSelectQuarterly" class="form-control" style="display: inline-block; width: 160px;">' +
                    yearOptions +
                    '</select></div>';
            }

            container.html(filterHTML);

            // Add date validation for date range filter
            if (parameter === 'date') {
                initializeAnalyticsDatePickers();

                // When start date changes, update the minimum allowed end date
                $('#startDate').on('change', function() {
                    var startDate = $(this).val();
                    if (startDate) {
                        if (endDatePicker) endDatePicker.set('minDate', startDate);

                        // If end date is already set and is earlier than start date, clear it
                        var endDate = $('#endDate').val();
                        if (endDate && endDate < startDate) {
                            if (endDatePicker) endDatePicker.clear(); else $('#endDate').val('');
                            alert('The "To" date cannot be earlier than the "From" date. Please select a valid date range.');
                        }
                    }
                });

                // When end date changes, update the maximum allowed start date
                $('#endDate').on('change', function() {
                    var endDate = $(this).val();
                    if (endDate) {
                        if (startDatePicker) startDatePicker.set('maxDate', endDate);

                        // If start date is already set and is later than end date, clear end date
                        var startDate = $('#startDate').val();
                        if (startDate && startDate > endDate) {
                            if (endDatePicker) endDatePicker.clear(); else $(this).val('');
                            alert('The "To" date cannot be earlier than the "From" date. Please select a valid date range.');
                        }
                    }
                });
            }
        }

        function initializeAnalyticsDatePickers() {
            if (typeof flatpickr === 'undefined') return;

            if (startDatePicker) { startDatePicker.destroy(); startDatePicker = null; }
            if (endDatePicker) { endDatePicker.destroy(); endDatePicker = null; }

            startDatePicker = flatpickr('#startDate', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd-m-Y',
                altInputClass: 'form-control',
                monthSelectorType: 'static',
                disableMobile: true,
                allowInput: false,
                onReady: function(selectedDates, dateStr, instance) { formatFlatpickrHeader(instance); }
            });

            endDatePicker = flatpickr('#endDate', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd-m-Y',
                altInputClass: 'form-control',
                monthSelectorType: 'static',
                disableMobile: true,
                allowInput: false,
                onReady: function(selectedDates, dateStr, instance) { formatFlatpickrHeader(instance); }
            });
        }

        function setAnalyticsPrintButtonEnabled(enabled) {
            var $btn = $('#analyticsPrintBtn');
            if (!$btn.length) return;

            if (enabled) {
                $btn.prop('disabled', false)
                    .removeClass('bg-gray-100 text-gray-400 cursor-not-allowed')
                    .addClass('bg-green-600 hover:bg-green-700 text-white hover:-translate-y-0.5 hover:shadow-md active:translate-y-0');
            } else {
                $btn.prop('disabled', true)
                    .removeClass('bg-green-600 hover:bg-green-700 text-white hover:-translate-y-0.5 hover:shadow-md active:translate-y-0')
                    .addClass('bg-gray-100 text-gray-400 cursor-not-allowed');
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

            // Get data based on parameter
            var chartData = [];
            var hasData = true; // Default to true

            if (parameter === 'date') {
                var startDate = $('#startDate').val();
                var endDate = $('#endDate').val();

                if (startDate && endDate) {
                    // Parse dates with time to avoid timezone issues
                    var start = new Date(startDate + 'T00:00:00');
                    var end = new Date(endDate + 'T23:59:59'); // Include entire end date

                    // Filter records within the date range
                    var filteredRecords = allRecords.filter(function(record) {
                        if (!record.date) return false;
                        var recordDate = new Date(record.date + 'T00:00:00');
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
                // Use abbreviated month names for better fit
                var monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                                'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

                // Ensure the year exists in monthly data
                if (!masterAnalyticsData || !masterAnalyticsData.monthly || !masterAnalyticsData.monthly[selectedYear]) {
                    console.warn('No monthly data for year:', selectedYear);
                    chartData = [];
                    for (var i = 0; i < 12; i++) {
                        chartData.push({
                            period: monthNames[i],
                            bookings: 0,
                            penalties: 0
                        });
                    }
                    hasData = false;
                } else {
                    // Get all 12 months for the selected year - ALWAYS include all months
                    chartData = [];
                    hasData = false;

                    for (var i = 0; i < 12; i++) {
                        var monthData = masterAnalyticsData.monthly[selectedYear][i] || { bookings: 0, penalties: 0 };
                        chartData.push({
                            period: monthNames[i],
                            bookings: monthData.bookings || 0,
                            penalties: monthData.penalties || 0
                        });

                        // Check if this year has any data (but still show all months)
                        if ((monthData.bookings || 0) > 0 || (monthData.penalties || 0) > 0) {
                            hasData = true;
                        }
                    }
                }

            } else if (parameter === 'quarterly') {
                var selectedYear = $('#yearSelectQuarterly').val() || '2024';

                // Always show all 4 quarters for the selected year
                // Check if we have quarterly data for this year
                var quarterlyDataForYear = masterAnalyticsData.quarterly.filter(function(item) {
                    return item.period.includes(selectedYear);
                });

                // Create a map of quarter data for quick lookup
                var quarterMap = {};
                quarterlyDataForYear.forEach(function(item) {
                    quarterMap[item.period] = item;
                });

                // Build chart data with all 4 quarters (even if some have no data)
                chartData = [];
                hasData = false;

                for (var q = 1; q <= 4; q++) {
                    var quarterKey = 'Q' + q + ' ' + selectedYear;
                    var quarterData = quarterMap[quarterKey] || { period: quarterKey, bookings: 0, penalties: 0 };

                    chartData.push({
                        period: quarterKey,
                        bookings: quarterData.bookings || 0,
                        penalties: quarterData.penalties || 0
                    });

                    // Check if this quarter has any data
                    if ((quarterData.bookings || 0) > 0 || (quarterData.penalties || 0) > 0) {
                        hasData = true;
                    }
                }
            }

            // Apply type filter to chart data - but keep all periods (don't filter out zeros for monthly)
            var filteredChartData = chartData.map(function(item) {
                var newItem = { period: item.period };

                if (typeFilter === 'both') {
                    newItem.bookings = item.bookings || 0;
                    newItem.penalties = item.penalties || 0;
                } else if (typeFilter === 'bookings') {
                    newItem.bookings = item.bookings || 0;
                    newItem.penalties = 0;
                } else if (typeFilter === 'penalties') {
                    newItem.bookings = 0;
                    newItem.penalties = item.penalties || 0;
                }

                return newItem;
            });

            // Log filtered chart data for debugging
            console.log('Filtered chart data:', filteredChartData);
            console.log('Has data:', hasData);

            // Update chart - use a consistent "No Data" state across all parameters
            var showNoData = false;
            if (parameter === 'date' || parameter === 'quarterly') {
                showNoData = !hasData;
            } else if (parameter === 'month') {
                showNoData = !hasData;
            }

            initializeChart(filteredChartData, !showNoData, typeFilter);

            // Enable print as soon as filters are successfully applied and chart is rendered
            setAnalyticsPrintButtonEnabled(true);

            // Repopulate table with all records first, then filter
            populateRecordsTable();

            // Reinitialize DataTable if needed
            if (dataTable) {
                dataTable.fnDestroy();
                dataTable = null;
            }
            dataTable = $('#dataTables-example').dataTable({
                "order": [[ 1, "desc" ]],  // Sort by date (column index 1) - newest first
                "paging": false,  // Disable pagination - show all records
                "searching": false,  // Disable search box
                "info": false  // Hide "Showing X to Y of Z entries" text
            });

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
            setAnalyticsPrintButtonEnabled(false);
            $('.morris-hover').hide();

            // Hide chart, legend and table data
            $('#analytics-chart').hide();
            $('#chart-legend').hide();
            $('#no-chart-data').hide();
            $('#select-prompt-chart').show();

            // Keep records hidden until filters are applied again
            $('#recordsTableBody').empty();

            // Reset DataTable
            if (dataTable) {
                dataTable.fnDestroy();
                dataTable = null;
            }
            $('#selectPromptBody').show();
            $('#noResultsBody').hide();
        }

        function populateRecordsTable() {
            var tbody = $('#recordsTableBody');
            tbody.empty();

            if (allRecords.length === 0) {
                $('#noResultsBody').show();
                $('#selectPromptBody').hide();
                return;
            }

            // Records are already sorted by date (newest first) from loadAllRecords
            allRecords.forEach(function(record) {
                var typeClass = record.type === 'booking' ? 'label-info' : 'label-warning';
                var typeText = record.type === 'booking' ? 'Booking' : 'Penalty';

                var row = '<tr data-type="' + record.type + '" data-date="' + record.date + '" data-username="' + (record.username || '').toLowerCase() + '">' +
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

            // Wait for data to load from DOMContentLoaded before initializing
            // Use a small delay to ensure data is loaded
            setTimeout(function() {
                // Initialize dynamic filter (will be empty initially)
                updateDynamicFilter();

                // Keep records table empty until user applies filters
                $('#recordsTableBody').empty();
                $('#selectPromptBody').show();
                $('#noResultsBody').hide();
            }, 500);

            // Don't initialize chart on page load - show prompts instead
            // Show the select prompt messages
            $('#select-prompt-chart').show();

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
            if ($('#analyticsPrintBtn').prop('disabled')) return;

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

            // Get the chart SVG and scale it for printing
            var chartSVG = $('#analytics-chart').html();

            // If SVG exists, modify it for better print scaling
            if (chartSVG && chartSVG.includes('<svg')) {
                // Create a temporary container to manipulate the SVG
                var tempDiv = $('<div>').html(chartSVG);
                var svgElement = tempDiv.find('svg').first();

                if (svgElement.length > 0) {
                    // Get current dimensions
                    var currentWidth = svgElement.attr('width') || svgElement[0].clientWidth || 800;
                    var currentHeight = svgElement.attr('height') || svgElement[0].clientHeight || 500;

                    // Calculate print dimensions (scale down to fit page)
                    // Target: ~85% of page width (A4 portrait is ~21cm = ~794px at 96dpi)
                    // Use 85% to leave margins: ~675px width
                    var pageWidth = 675; // ~85% of A4 portrait width
                    var pageHeight = 450; // Max height for A4 portrait with margins

                    // Calculate scale factor to fit chart on page
                    var scaleX = pageWidth / currentWidth;
                    var scaleY = pageHeight / currentHeight;
                    var scale = Math.min(scaleX, scaleY, 0.85); // Use smallest scale, max 85%

                    var printWidth = currentWidth * scale;
                    var printHeight = currentHeight * scale;

                    // Ensure reasonable minimum size
                    if (printWidth < 400) {
                        printWidth = 400;
                        printHeight = (currentHeight / currentWidth) * printWidth;
                    }

                    // Limit height for print
                    if (printHeight > 400) {
                        printHeight = 400;
                        printWidth = (currentWidth / currentHeight) * printHeight;
                    }

                    // Preserve original viewBox for proper scaling
                    var originalViewBox = svgElement.attr('viewBox');
                    if (!originalViewBox && currentWidth && currentHeight) {
                        svgElement.attr('viewBox', '0 0 ' + currentWidth + ' ' + currentHeight);
                    }

                    // Set SVG dimensions for print (responsive)
                    svgElement.attr('width', '100%');
                    svgElement.attr('height', 'auto');
                    svgElement.attr('preserveAspectRatio', 'xMidYMid meet');
                    svgElement.css({
                        'width': '100%',
                        'max-width': '100%',
                        'height': 'auto',
                        'max-height': '400px'
                    });

                    // Get updated SVG HTML
                    chartSVG = tempDiv.html();
                }
            }

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

                        .chart-section svg { max-width: 100%; height: auto; }

                        @media print {
                            @page { size: A4 landscape; margin: 0.5cm; }
                            body { margin: 0; padding: 10px; font-size: 12px; width: 100%; max-width: 100%; }
                            .no-print { display: none; }
                            h1 { font-size: 18px; margin-bottom: 6px; }
                            h3 { font-size: 14px; margin-bottom: 6px; }
                            .filter-info { margin-bottom: 12px; padding: 6px; font-size: 10px; }
                            .chart-section { margin-bottom: 20px; page-break-inside: avoid; width: 100%; }
                            .chart-container { width: 100% !important; max-width: 100% !important; overflow: visible !important; text-align: center; margin: 0 auto 10px auto !important; }
                            .chart-container svg { width: 90% !important; max-width: 90% !important; height: auto !important; max-height: 280px !important; min-height: 200px !important; display: block; margin: 0 auto; }
                            .chart-container svg[width], .chart-container svg[height] { width: 90% !important; height: auto !important; }
                            .chart-container svg text { font-size: 9px !important; }
                            .chart-container svg .data-label { font-size: 8px !important; font-weight: bold !important; }
                            .chart-legend-container { font-size: 9px !important; padding: 6px !important; margin-top: 8px !important; text-align: center; }
                            .chart-legend-container span { font-size: 9px !important; }
                            .chart-legend-container span span:first-child { width: 16px !important; height: 16px !important; margin-right: 6px !important; }
                            table { font-size: 10px; }
                            th, td { padding: 4px; font-size: 10px; }
                            .chart-section svg rect, .chart-section svg path, .chart-section svg circle { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
                            .chart-section, .table-section { page-break-inside: avoid; break-inside: avoid; }
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
                        <div class="chart-container" style="width: 100%; overflow: hidden; text-align: center; margin: 0 auto;">${chartSVG || 'No chart data available'}</div>
                        <div class="chart-legend-container">${legendHTML || ''}</div>
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
</body>
</html>







