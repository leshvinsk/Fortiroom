<?php
// Minimal .env loader (no external deps). Loads KEY=VALUE pairs into $_ENV.
$envPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(ltrim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter','sans-serif'] } } } }</script>
    <script defer src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    <script>
        window.__SUPABASE__ = {
            url: "<?php echo htmlspecialchars($SUPABASE_URL, ENT_QUOTES, 'UTF-8'); ?>",
            anonKey: "<?php echo htmlspecialchars($SUPABASE_ANON_KEY, ENT_QUOTES, 'UTF-8'); ?>"
        };
    </script>
    <style>
        .navbar-side { transform: translateX(-260px); transition: transform 0.3s ease; }
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

        .sidebar-nav-link {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 16px; border-radius: 8px;
            font-size: 14px; font-weight: 500;
            color: rgba(255,255,255,0.85);
            transition: all 0.15s ease; text-decoration: none;
        }
        .sidebar-nav-link:hover { color: #d3af37; background: rgba(255,255,255,0.08); text-decoration: none; }
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

        table.dataTable,
        #dataTables-example { width: 100% !important; border-collapse: collapse !important; }
        .elevated-card { box-shadow: 0 8px 22px rgba(15, 23, 42, 0.08) !important; }
        #dataTables-example { table-layout: fixed; }
        table.dataTable thead th,
        #dataTables-example thead th {
            background: #f9fafb; border-bottom: 2px solid #e5e7eb;
            padding: 11px 16px; font-size: 11px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; white-space: nowrap;
            text-align: left;
        }
        table.dataTable tbody td,
        #dataTables-example tbody td {
            padding: 13px 16px; border-bottom: 1px solid #f3f4f6;
            font-size: 14px; color: #374151; vertical-align: middle;
            text-align: left;
        }
        table.dataTable tbody tr:hover,
        #dataTables-example tbody tr:hover { background: #f9fafb; }
        #dataTables-example tbody td.empty-state { text-align: center !important; }

        .status-badge {
            display: inline-flex; align-items: center; padding: 3px 10px;
            border-radius: 9999px; font-size: 11px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.3px;
        }
        .status-pending  { background: #fef9c3; color: #854d0e; }
        .status-paid     { background: #dcfce7; color: #166534; }

        /* Modal animation */
        @keyframes slideDown {
            from { transform: translateY(-40px); opacity: 0; }
            to   { transform: translateY(0); opacity: 1; }
        }
        .modal-animate { animation: slideDown 0.25s ease; }

        /* Date input fix */
        input[type="date"] { min-width: 150px; font-family: inherit; color: #333; }
        input[type="date"]::-webkit-datetime-edit-fields-wrapper { display: inline-flex; }
        .flatpickr-calendar {
            border-radius: 10px !important;
            border: 1px solid #e5e7eb !important;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12) !important;
            z-index: 10050 !important;
        }
        .flatpickr-input {
            min-width: 160px;
            border-radius: 8px !important;
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
        ::-webkit-scrollbar { display: none; }
        * { scrollbar-width: none; }
    </style>
</head>
<body class="bg-[#f7f7f5] font-sans antialiased overflow-x-hidden min-h-screen">
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
                <li><a class="sidebar-nav-link" href="dashboard.php"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a></li>
                <li><a class="sidebar-nav-link active-menu" href="penalties.php"><i class="fa fa-exclamation-triangle fa-fw"></i> Penalties</a></li>
                <li><a class="sidebar-nav-link" href="pods.php"><i class="fa fa-building fa-fw"></i> Pods Management</a></li>
                <li><a class="sidebar-nav-link" href="users.php"><i class="fa fa-users fa-fw"></i> User Management</a></li>
                <li><a class="sidebar-nav-link" href="analytics.php"><i class="fa fa-bar-chart-o fa-fw"></i> Analytics</a></li>
                <li class="mobile-only" style="display:none; border-top:1px solid rgba(255,255,255,0.1); padding-top:8px; margin-top:8px;">
                    <a class="sidebar-nav-link" href="profile.php"><i class="fa fa-user-circle fa-fw"></i> Profile</a>
                </li>
                <li class="mobile-only" style="display:none;">
                    <a class="sidebar-nav-link" href="logout.php"><i class="fa fa-sign-out fa-fw"></i> Log Out</a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main id="page-wrapper" class="mt-[60px] min-h-screen p-6 lg:p-8">
        <div id="page-inner">

            <!-- Page Heading -->
            <div class="border-b border-gray-200 pb-4 mb-8 flex items-center justify-between flex-wrap gap-4">
                <h1 class="text-2xl font-light tracking-wide font-semibold text-gray-700 uppercase">Penalties Management</h1>
                <button onclick="openPenaltyModal()" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors shadow-sm">
                    <i class="fa fa-plus-circle"></i> Manage Penalty Rates
                </button>
            </div>

            <!-- Penalties Table Card -->
            <div class="elevated-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-visible">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Penalty Records</h4>
                </div>
                <div class="p-6">
                    <!-- Filters -->
                    <div class="filter-controls flex flex-wrap gap-4 items-end mb-5">
                        <div>
                            <label for="filterStatus" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Status</label>
                            <select id="filterStatus" class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 w-[140px]">
                                <option value="all">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                        <div>
                            <label for="filterViolation" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Violation Type</label>
                            <select id="filterViolation" class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 w-[170px]">
                                <option value="all">All Types</option>
                                <option value="No Show">No Show</option>
                                <option value="Late Checkout">Late Checkout</option>
                                <option value="Late Cancellation">Late Cancellation</option>
                            </select>
                        </div>
                        <div>
                            <label for="filterDate" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Date</label>
                            <input type="text" id="filterDate" placeholder="dd-mm-yyyy" autocomplete="off" class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 w-[160px]">
                        </div>
                        <div>
                            <label for="searchUsername" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Search Username</label>
                            <input type="text" id="searchUsername" placeholder="Enter username…" class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 w-[200px] placeholder-gray-300">
                        </div>
                        <button onclick="resetFilters()" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            <i class="fa fa-refresh"></i> Reset
                        </button>
                    </div>
                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table id="dataTables-example" class="w-full">
                            <colgroup>
                                <col style="width:14%;">
                                <col style="width:12%;">
                                <col style="width:20%;">
                                <col style="width:27%;">
                                <col style="width:15%;">
                                <col style="width:12%;">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Pods No.</th>
                                    <th>Violation Type</th>
                                    <th>Cancellation Date &amp; Time</th>
                                    <th>Penalty Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="penaltiesTableBody"></tbody>
                            <tbody id="noResultsBody" style="display:none;">
                                <tr>
                                    <td colspan="6" class="empty-state text-center py-16 text-gray-700">
                                        <i class="fa fa-search text-5xl block mb-4 opacity-20"></i>
                                        No penalties found with the current filters
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

<!-- Penalty Rate Management Modal -->
<div id="penaltyModal" class="fixed inset-0 z-[9999] hidden items-center justify-content-center" style="display:none; align-items:center; justify-content:center;">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closePenaltyModal()"></div>
    <div class="relative bg-white rounded-2xl w-[90%] max-w-lg shadow-2xl modal-animate mx-auto my-auto" style="position:relative; z-index:1;">
        <!-- Header -->
        <div class="flex items-center justify-between px-8 py-6 border-b border-gray-100">
            <h3 class="text-xl font-semibold text-gray-900">Manage Penalty Rates</h3>
            <button onclick="closePenaltyModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none font-light transition-colors">&times;</button>
        </div>
        <!-- Body -->
        <div class="px-8 py-6 space-y-5">
            <div>
                <label for="lateCancellationRate" class="block text-sm font-medium text-gray-700 mb-2">
                    Late Cancellation Penalty <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">$</span>
                    <input type="number" id="lateCancellationRate" placeholder="10.00" step="0.01" min="0"
                        class="w-full pl-8 pr-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-colors">
                </div>
            </div>
            <div>
                <label for="noShowRate" class="block text-sm font-medium text-gray-700 mb-2">
                    No Show Penalty <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">$</span>
                    <input type="number" id="noShowRate" placeholder="25.00" step="0.01" min="0"
                        class="w-full pl-8 pr-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-colors">
                </div>
            </div>
            <div>
                <label for="lateCheckoutRate" class="block text-sm font-medium text-gray-700 mb-2">
                    Late Checkout Penalty <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">$</span>
                    <input type="number" id="lateCheckoutRate" placeholder="15.00" step="0.01" min="0"
                        class="w-full pl-8 pr-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-colors">
                </div>
            </div>
        </div>
        <!-- Footer -->
        <div class="flex items-center justify-end gap-3 px-8 py-5 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
            <button class="btn-cancel px-6 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-100 rounded-lg transition-colors" onclick="closePenaltyModal()">Cancel</button>
            <button class="btn-set-penalty px-6 py-2.5 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors shadow-sm" onclick="setPenaltyRates()">Set Rates</button>
        </div>
    </div>
</div>

<!-- JS Scripts -->
<script src="assets/js/jquery-1.10.2.js"></script>
<script src="assets/js/tailwind-selects.js"></script>
<script src="assets/js/dataTables/jquery.dataTables.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // Global variables
    var supabase = null;
    var currentUser = null;
    var dataTable = null;
    var penaltiesData = [];
    var podsData = [];
    var penaltyRates = {};
    var filterDatePicker = null;

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

    document.addEventListener('DOMContentLoaded', async function() {
        const { createClient } = window.supabase || {};
        if (!createClient) { console.error('Supabase library failed to load.'); alert('Failed to load database connection. Please refresh the page.'); return; }
        supabase = createClient(window.__SUPABASE__.url, window.__SUPABASE__.anonKey);

        const { data: sessionData, error: sessionError } = await supabase.auth.getSession();
        if (sessionError || !sessionData?.session) { window.location.href = '../login.php'; return; }
        currentUser = sessionData.session.user;

        await loadPods();
        await loadPenaltyRates();
        await loadPenalties();
        initDateFilterPicker();

        $('#filterStatus').on('change', function() { applyFilters(); });
        $('#filterDate').on('change', function() { applyFilters(); });
        $('#filterViolation').on('change', function() { applyFilters(); });
        $('#searchUsername').on('keyup', function() { applyFilters(); });
    });

    async function loadPods() {
        try {
            const { data, error } = await supabase.from('pods').select('id, name').order('created_at', { ascending: true });
            if (error) { console.error('Error loading pods:', error); podsData = []; return; }
            podsData = data || [];
        } catch (error) { console.error('Error in loadPods:', error); podsData = []; }
    }

    async function loadPenaltyRates() {
        try {
            const { data, error } = await supabase.from('penalty_rates').select('violation_type, penalty_amount').order('violation_type', { ascending: true });
            if (error) { console.error('Error loading penalty rates:', error); penaltyRates = { 'Late Cancellation': 10.00, 'No Show': 25.00, 'Late Checkout': 15.00 }; return; }
            penaltyRates = {};
            if (data && data.length > 0) {
                data.forEach(function(rate) { penaltyRates[rate.violation_type] = parseFloat(rate.penalty_amount) || 0; });
            } else {
                penaltyRates = { 'Late Cancellation': 10.00, 'No Show': 25.00, 'Late Checkout': 15.00 };
            }
        } catch (error) { console.error('Error in loadPenaltyRates:', error); penaltyRates = { 'Late Cancellation': 10.00, 'No Show': 25.00, 'Late Checkout': 15.00 }; }
    }

    async function loadPenalties() {
        try {
            const { data: penalties, error: penaltiesError } = await supabase
                .from('penalties')
                .select('id, user_id, pod_id, booking_id, violation_type, penalty_amount, status, violation_date, violation_time, receipt_number, paid_at, created_at, updated_at')
                .order('violation_date', { ascending: false })
                .order('violation_time', { ascending: false });

            if (penaltiesError) {
                console.error('Error loading penalties:', penaltiesError);
                var errorMsg = penaltiesError.message || penaltiesError.toString() || '';
                if (errorMsg.includes('does not exist') || errorMsg.includes('relation') || errorMsg.includes('42P01')) {
                    console.error('Penalties table does not exist.');
                } else if (errorMsg.includes('permission') || errorMsg.includes('policy') || errorMsg.includes('RLS')) {
                    alert('Permission Error: Cannot load penalties. Please ensure RLS policies are configured for staff/admin users.');
                } else {
                    alert('Error loading penalties: ' + (penaltiesError.message || 'Unknown error'));
                }
                penaltiesData = []; populateTable(); return;
            }

            if (!penalties || penalties.length === 0) { penaltiesData = []; populateTable(); return; }

            var podsMap = {};
            podsData.forEach(function(pod) { podsMap[pod.id] = pod; });

            var userIds = [...new Set(penalties.map(p => p.user_id).filter(id => id))];
            var usersMap = {};

            if (userIds.length > 0) {
                try {
                    const response = await fetch('get_users_info.php', {
                        method: 'POST', headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ user_ids: userIds })
                    });
                    if (!response.ok) throw new Error('Failed to fetch users: ' + response.statusText);
                    const data = await response.json();
                    if (data.users && Object.keys(data.users).length > 0) {
                        Object.keys(data.users).forEach(userId => {
                            const user = data.users[userId];
                            usersMap[userId] = user.username || (user.email ? user.email.split('@')[0] : 'User ' + userId.substring(0, 8));
                        });
                    }
                } catch (error) { console.error('Error fetching users from Auth API:', error); }
            }

            penaltiesData = penalties.map(function(penalty) {
                var username = 'Unknown';
                if (penalty.user_id) username = usersMap[penalty.user_id] || 'User ' + penalty.user_id.substring(0, 8);

                var pod = penalty.pod_id ? podsMap[penalty.pod_id] : null;
                var podName = pod ? (pod.name || 'Pod ' + pod.id.substring(0, 8)) : 'N/A';

                var dateStr = penalty.violation_date || '';
                var timeStr = penalty.violation_time || '';
                var dateTimeStr = '';
                if (dateStr) {
                    var date = new Date(dateStr + (timeStr ? 'T' + timeStr : ''));
                    if (!isNaN(date.getTime())) {
                        var monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                        var formattedDate = date.getDate() + ' ' + monthNames[date.getMonth()] + ' ' + date.getFullYear();
                        if (timeStr) {
                            var hours = date.getHours(); var minutes = date.getMinutes();
                            var period = hours >= 12 ? 'PM' : 'AM';
                            var hours12 = hours % 12 || 12;
                            formattedDate += ' ' + hours12 + ':' + (minutes < 10 ? '0' : '') + minutes + ' ' + period;
                        }
                        dateTimeStr = formattedDate;
                    } else { dateTimeStr = dateStr + (timeStr ? ' ' + timeStr : ''); }
                }

                return {
                    id: penalty.id,
                    username: username,
                    room: penalty.pod_id || 'N/A',
                    roomName: podName,
                    violationType: penalty.violation_type || '',
                    date: dateStr,
                    dateTime: dateTimeStr,
                    amount: '$' + parseFloat(penalty.penalty_amount || 0).toFixed(2),
                    status: penalty.status || 'pending',
                    userId: penalty.user_id,
                    podId: penalty.pod_id
                };
            });

            penaltiesData.sort(function(a, b) {
                if (a.status === 'pending' && b.status !== 'pending') return -1;
                if (a.status !== 'pending' && b.status === 'pending') return 1;
                return b.date.localeCompare(a.date);
            });

            populateTable();

            setTimeout(function() {
                $('#penaltiesTableBody tr').show();
                $('#noResultsBody').hide();
            }, 200);

        } catch (error) { console.error('Error in loadPenalties:', error); penaltiesData = []; populateTable(); }
    }

    function initializeDataTable() {
        if (dataTable) { try { dataTable.fnDestroy(); dataTable = null; } catch (e) {} }
        var tbody = $('#penaltiesTableBody');
        var rows = tbody.find('tr');
        var hasDataRows = rows.length > 0 && !rows.first().find('td[colspan]').length;
        if (!hasDataRows) { console.log('No data rows to initialize DataTable with'); return; }
        rows.show();
        setTimeout(function() {
            try {
                $('#penaltiesTableBody tr').show();
                dataTable = $('#dataTables-example').dataTable({
                    "order": [[ 3, "desc" ]],
                    "paging": false, "searching": false, "info": false, "autoWidth": false,
                    "columnDefs": [{ "orderable": true, "targets": [0,1,2,3,4,5] }]
                });
                $('#penaltiesTableBody tr').show();
                $('#noResultsBody').hide();
            } catch (e) { console.error('Error initializing DataTable:', e); }
        }, 150);
    }

    function populateTable() {
        var tbody = $('#penaltiesTableBody');
        tbody.empty();
        if (penaltiesData.length === 0) {
            $('#noResultsBody').show();
            if (dataTable) { try { dataTable.fnDestroy(); dataTable = null; } catch (e) {} }
            return;
        }
        $('#noResultsBody').hide();

        penaltiesData.forEach(function(penalty) {
            var statusClass = 'status-' + penalty.status;
            var statusText  = penalty.status.charAt(0).toUpperCase() + penalty.status.slice(1);
            var escapeHtml = function(text) {
                var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
                return (text || '').toString().replace(/[&<>"']/g, function(m) { return map[m]; });
            };
            var row = '<tr data-status="' + escapeHtml(penalty.status) + '" data-date="' + escapeHtml(penalty.date) +
                '" data-violation="' + escapeHtml(penalty.violationType) + '" data-username="' + escapeHtml(penalty.username.toLowerCase()) + '">' +
                '<td>' + escapeHtml(penalty.username)     + '</td>' +
                '<td>' + escapeHtml(penalty.roomName)     + '</td>' +
                '<td>' + escapeHtml(penalty.violationType)+ '</td>' +
                '<td>' + escapeHtml(penalty.dateTime)     + '</td>' +
                '<td>' + escapeHtml(penalty.amount)       + '</td>' +
                '<td><span class="status-badge ' + statusClass + '">' + escapeHtml(statusText) + '</span></td>' +
                '</tr>';
            tbody.append(row);
        });
        initializeDataTable();
    }

    function applyFilters() {
        var statusFilter    = $('#filterStatus').val();
        var dateFilter      = $('#filterDate').val();
        var violationFilter = $('#filterViolation').val();
        var searchText      = $('#searchUsername').val().toLowerCase().trim();

        var wasDataTableActive = false;
        if (dataTable) { try { dataTable.fnDestroy(); wasDataTableActive = true; dataTable = null; } catch (e) {} }

        $('#penaltiesTableBody tr').show();
        var totalRows = $('#penaltiesTableBody tr').length;

        if (statusFilter !== 'all') {
            $('#penaltiesTableBody tr').each(function() {
                if ($(this).attr('data-status') !== statusFilter) $(this).hide();
            });
        }
        if (dateFilter !== '') {
            $('#penaltiesTableBody tr:visible').each(function() {
                if ($(this).attr('data-date') !== dateFilter) $(this).hide();
            });
        }
        if (violationFilter !== 'all') {
            $('#penaltiesTableBody tr:visible').each(function() {
                if ($(this).attr('data-violation') !== violationFilter) $(this).hide();
            });
        }
        if (searchText !== '') {
            $('#penaltiesTableBody tr:visible').each(function() {
                if ($(this).find('td:first').text().toLowerCase().indexOf(searchText) === -1) $(this).hide();
            });
        }

        var visibleCount = $('#penaltiesTableBody tr:visible').length;
        if (visibleCount === 0 && totalRows > 0) { $('#noResultsBody').show(); } else { $('#noResultsBody').hide(); }
        if (wasDataTableActive && visibleCount > 0) { setTimeout(function() { initializeDataTable(); }, 100); }
    }

    function initDateFilterPicker() {
        if (typeof flatpickr === 'undefined') return;
        if (filterDatePicker) return;

        filterDatePicker = flatpickr('#filterDate', {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd-m-Y',
            altInputClass: 'px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 w-[160px]',
            monthSelectorType: 'static',
            disableMobile: true,
            allowInput: false,
            onReady: function(selectedDates, dateStr, instance) { formatFlatpickrHeader(instance); },
            onChange: function() { applyFilters(); }
        });
    }

    function resetFilters() {
        $('#filterStatus').val('all');
        if (filterDatePicker) { filterDatePicker.clear(); } else { $('#filterDate').val(''); }
        $('#filterViolation').val('all');
        $('#searchUsername').val('');
        if (dataTable) { try { dataTable.fnDestroy(); dataTable = null; } catch (e) {} }
        $('#penaltiesTableBody tr').show(); $('#noResultsBody').hide();
        var visibleCount = $('#penaltiesTableBody tr:visible').length;
        if (visibleCount > 0) { setTimeout(function() { initializeDataTable(); }, 100); }
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
        if ("Notification" in window && Notification.permission === "default") Notification.requestPermission();

        $('.navbar-toggle').on('click', function() {
            $('.navbar-side').toggleClass('in');
            if ($('.navbar-side').hasClass('in')) {
                if (!$('#sidebar-overlay').length) $('body').append('<div id="sidebar-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.78);z-index:39;"></div>');
            } else { $('#sidebar-overlay').remove(); }
        });
        $(document).on('click', '#sidebar-overlay', function() { $('.navbar-side').removeClass('in'); $(this).remove(); });
        $('.navbar-side a').on('click', function() {
            if ($(window).width() <= 991) { $('.navbar-side').removeClass('in'); $('#sidebar-overlay').remove(); }
        });
    });

    window.addEventListener('pageshow', function(event) { setTimeout(function() { resetSidebar(); }, 50); });
    window.addEventListener('load', function() { setTimeout(function() { resetSidebar(); }, 100); });

    async function openPenaltyModal() {
        await loadPenaltyRates();
        $('#lateCancellationRate').val(penaltyRates['Late Cancellation'] || '');
        $('#noShowRate').val(penaltyRates['No Show'] || '');
        $('#lateCheckoutRate').val(penaltyRates['Late Checkout'] || '');
        $('#penaltyModal').css('display', 'flex');
        $('body').css('overflow', 'hidden');
    }

    function closePenaltyModal() {
        $('#penaltyModal').css('display', 'none');
        $('body').css('overflow', '');
    }

    async function setPenaltyRates() {
        var lateCancellation = $('#lateCancellationRate').val().trim();
        var noShow = $('#noShowRate').val().trim();
        var lateCheckout = $('#lateCheckoutRate').val().trim();

        if (!lateCancellation && !noShow && !lateCheckout) { alert('Please enter at least one penalty rate to update.'); return; }

        var ratesToUpdate = [];
        if (lateCancellation) {
            var lateCancellationNum = parseFloat(lateCancellation);
            if (isNaN(lateCancellationNum) || lateCancellationNum < 0) { alert('Invalid penalty amount for Late Cancellation.'); return; }
            ratesToUpdate.push({ violation_type: 'Late Cancellation', penalty_amount: lateCancellationNum });
        }
        if (noShow) {
            var noShowNum = parseFloat(noShow);
            if (isNaN(noShowNum) || noShowNum < 0) { alert('Invalid penalty amount for No Show.'); return; }
            ratesToUpdate.push({ violation_type: 'No Show', penalty_amount: noShowNum });
        }
        if (lateCheckout) {
            var lateCheckoutNum = parseFloat(lateCheckout);
            if (isNaN(lateCheckoutNum) || lateCheckoutNum < 0) { alert('Invalid penalty amount for Late Checkout.'); return; }
            ratesToUpdate.push({ violation_type: 'Late Checkout', penalty_amount: lateCheckoutNum });
        }

        if (ratesToUpdate.length === 0) { alert('Please enter at least one valid penalty rate to update.'); return; }

        var setButton = $('.btn-set-penalty');
        var originalText = setButton.text();
        setButton.prop('disabled', true).text('Saving…');

        try {
            const response = await fetch('update_penalty_rates.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ rates: ratesToUpdate, user_id: currentUser ? currentUser.id : null })
            });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.error || 'Failed to update penalty rates');
            await loadPenaltyRates();
            closePenaltyModal();
            showPenaltyRateUpdateNotification();
        } catch (error) {
            console.error('Error updating penalty rates:', error);
            alert('Error updating penalty rates: ' + (error.message || 'Unknown error') + '\n\nPlease try again.');
        } finally { setButton.prop('disabled', false).text(originalText); }
    }

    function showPenaltyRateUpdateNotification() {
        if ("Notification" in window) {
            if (Notification.permission === "granted") {
                try {
                    var notification = new Notification("Penalty Rates Updated!", {
                        body: "All users will be informed about the new charges. Only new penalties will apply the updated rates.",
                        icon: "../images/FYP_Logo_small.png", requireInteraction: false
                    });
                    setTimeout(function() { notification.close(); }, 5000);
                } catch (e) { alert('Penalty Rates Updated!\n\nAll users will be informed about the new charges.'); }
            } else if (Notification.permission !== "denied") {
                Notification.requestPermission().then(function(permission) {
                    if (permission === "granted") {
                        var notification = new Notification("Penalty Rates Updated!", {
                            body: "All users will be informed about the new charges.",
                            icon: "../images/FYP_Logo_small.png", requireInteraction: false
                        });
                        setTimeout(function() { notification.close(); }, 5000);
                    } else { alert('Penalty Rates Updated!\n\nAll users will be informed about the new charges.'); }
                });
            } else { alert('Penalty Rates Updated!\n\nAll users will be informed about the new charges.'); }
        } else { alert('Penalty Rates Updated!\n\nAll users will be informed about the new charges.'); }
    }

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#penaltyModal').css('display') !== 'none') closePenaltyModal();
    });
</script>
<script src="assets/js/custom-scripts.js"></script>
</body>
</html>






