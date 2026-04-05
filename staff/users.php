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
        
        /* Consistent dropdown corner radius */
        select,
        .dataTables_wrapper .dataTables_length select {
            border-radius: 8px !important;
            overflow: hidden;
        }
        select option { border-radius: 8px; }

        /* Status Badges */
        .status-badge {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            display: inline-block;
        }
        .status-pending    { background: #fef9c3; color: #854d0e; }
        .status-approved   { background: #dcfce7; color: #166534; }
        .status-declined   { background: #dbeafe; color: #1e40af; }
        .status-in_progress{ background: #fef3c7; color: #92400e; }
        .status-cancelled  { background: #f3f4f6; color: #374151; }
        .status-completed  { background: #fee2e2; color: #991b1b; }

        /* DataTable overrides */
        table.dataTable,
        #dataTables-example { border-collapse: collapse !important; width: 100% !important; }
        .elevated-card { box-shadow: 0 8px 22px rgba(15, 23, 42, 0.08) !important; }
        #dataTables-example { table-layout: fixed; }
        table.dataTable thead th,
        #dataTables-example thead th {
            background: #f9fafb;
            color: #374151;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }
        table.dataTable tbody td,
        #dataTables-example tbody td {
            padding: 12px 16px;
            font-size: 14px;
            color: #374151;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
            text-align: left;
        }
        table.dataTable tbody tr:hover td,
        #dataTables-example tbody tr:hover td { background: #f9fafb; }
        #dataTables-example tbody td.empty-state { text-align: center !important; }
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 13px;
            outline: none;
        }
        .dataTables_wrapper .dataTables_filter input:focus { border-color: #16a34a; }
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 4px 8px;
            font-size: 13px;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #16a34a !important;
            color: #fff !important;
            border: none !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f0fdf4 !important;
            color: #16a34a !important;
            border: none !important;
        }
        .dataTables_wrapper .dataTables_info { font-size: 13px; color: #6b7280; }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_paginate,
        .dataTables_wrapper .dataTables_info { display: none !important; }
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
        .flatpickr-calendar {
            border-radius: 10px !important;
            border: 1px solid #e5e7eb !important;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12) !important;
            z-index: 10050 !important;
        }
        .flatpickr-input { border-radius: 8px !important; }

        /* Review Modal */
        .review-modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }
        .review-modal-overlay.active { display: flex; }

        @keyframes slideDown {
            from { transform: translateY(-30px); opacity: 0; }
            to   { transform: translateY(0);     opacity: 1; }
        }
        .modal-animate { animation: slideDown 0.25s ease; }
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
                        <a class="sidebar-nav-link active-menu" href="users.php">
                            <i class="fa fa-users fa-fw"></i> User Management
                        </a>
                    </li>
                    <li>
                        <a class="sidebar-nav-link" href="analytics.php">
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
                <div class="mb-8 pb-5 border-b border-gray-200">
                    <h1 class="text-2xl font-semibold text-gray-700 tracking-tight">USER MANAGEMENT</h1>
                </div>

                <!-- Users Table Card -->
                <div class="elevated-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-visible">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-base font-semibold text-gray-800">ACCOUNT DELETION REQUESTS</h2>
                    </div>
                    <div class="p-6">
                        <div class="filter-controls flex flex-wrap gap-4 items-end mb-5">
                            <div>
                                <label for="filterStatusUsers" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Status</label>
                                <select id="filterStatusUsers" class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 w-[160px]">
                                    <option value="all">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="declined">Declined</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                            <div>
                                <label for="filterDateUsers" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Date</label>
                                <input type="text" id="filterDateUsers" placeholder="dd-mm-yyyy" autocomplete="off" class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 w-[160px]">
                            </div>
                            <div>
                                <label for="searchUsernameUsers" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Search Username</label>
                                <input type="text" id="searchUsernameUsers" placeholder="Enter username..." class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 w-[200px] placeholder-gray-300">
                            </div>
                            <button onclick="resetUsersFilters()" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                                <i class="fa fa-refresh"></i> Reset
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full" id="dataTables-example">
                                <colgroup>
                                    <col style="width:18%;">
                                    <col style="width:28%;">
                                    <col style="width:20%;">
                                    <col style="width:16%;">
                                    <col style="width:18%;">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Requested Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTableBody">
                                    <!-- Deletion Request Records -->
                                </tbody>
                                <tbody id="noResultsBody" style="display:none;">
                                    <tr>
                                        <td colspan="5" class="empty-state text-center py-16 text-gray-700">
                                            <i class="fa fa-search text-5xl block mb-4 opacity-20"></i>
                                            No deletion requests found with the current filters
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

    <!-- Review Request Modal -->
    <div id="reviewModal" class="review-modal-overlay">
        <div class="relative bg-white rounded-2xl w-[90%] max-w-lg shadow-2xl modal-animate mx-auto my-auto" style="z-index:1;">
            <!-- Modal Header -->
            <div class="px-7 py-5 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Review Account Deletion Request</h3>
            </div>

            <!-- Modal Body -->
            <div class="px-7 py-5 space-y-4">
                <p class="text-sm text-gray-600">Do you accept the request for Account Deletion?</p>

                <div class="bg-gray-50 rounded-xl p-4 space-y-2 text-sm">
                    <div><span class="font-semibold text-gray-700">Username:</span> <span id="modalUsername" class="text-gray-600"></span></div>
                    <div><span class="font-semibold text-gray-700">Email:</span> <span id="modalEmail" class="text-gray-600"></span></div>
                    <div><span class="font-semibold text-gray-700">Requested Date:</span> <span id="modalRequestedDate" class="text-gray-600"></span></div>
                    <div><span class="font-semibold text-gray-700">Request ID:</span> <span id="modalRequestId" class="text-gray-500 text-xs font-mono"></span></div>
                </div>

                <p class="text-xs text-gray-500 leading-relaxed">
                    <strong class="text-gray-600">Note:</strong> If approved, the account deletion process will start immediately and take 30 days to complete.
                    If the user logs in during this period, the deletion will be cancelled automatically.
                    Bookings and penalties records will be retained for analytics purposes.
                </p>
                <p class="text-xs text-gray-400 italic">An email notification will be sent to the user regarding your decision.</p>
            </div>

            <!-- Modal Footer -->
            <div class="px-7 py-4 bg-gray-50 rounded-b-2xl border-t border-gray-100 flex justify-end gap-3">
                <button onclick="declineTermination()"
                    class="px-5 py-2.5 text-sm font-semibold bg-red-500 hover:bg-red-600 text-white rounded-xl transition-all hover:-translate-y-0.5 hover:shadow-md active:translate-y-0">
                    No — Decline Request
                </button>
                <button onclick="acceptTermination()"
                    class="px-5 py-2.5 text-sm font-semibold bg-green-600 hover:bg-green-700 text-white rounded-xl transition-all hover:-translate-y-0.5 hover:shadow-md active:translate-y-0">
                    Yes — Accept Request
                </button>
            </div>
        </div>
    </div>

    <!-- JS Scripts -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/tailwind-selects.js"></script>
    <script src="assets/js/dataTables/jquery.dataTables.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        var dataTable;
        var supabase = null;
        var currentUser = null;
        var deletionRequestsData = []; // Always keep as array
        var usersMap = {}; // Map of user_id to user info (username, email)
        var currentReviewRequest = null;
        var usersDatePicker = null;

        // Ensure deletionRequestsData is always an array (defensive programming)
        if (!Array.isArray(deletionRequestsData)) {
            deletionRequestsData = [];
        }

        // Initialize Supabase and load deletion requests
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

            // Load deletion requests and user info
            await loadDeletionRequests();
            initUsersDateFilterPicker();

            $('#filterStatusUsers').on('change', function() { applyUsersFilters(); });
            $('#filterDateUsers').on('change', function() { applyUsersFilters(); });
            $('#searchUsernameUsers').on('keyup', function() { applyUsersFilters(); });
        });

        // Load deletion requests from database via PHP endpoint (uses service key, bypasses RLS)
        async function loadDeletionRequests() {
            try {
                // Load deletion requests via PHP endpoint (uses service key, bypasses RLS)
                const response = await fetch('get_deletion_requests.php', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Failed to fetch deletion requests. Status:', response.status, 'Response:', errorText);
                    throw new Error('Failed to fetch deletion requests: ' + response.statusText);
                }

                const data = await response.json();
                console.log('Deletion requests API response:', data);

                // Ensure requests is an array - defensive validation
                if (!data) {
                    console.error('No data received from API');
                    deletionRequestsData = [];
                } else if (data.error) {
                    console.error('API returned error:', data.error);
                    // Check if it's a table doesn't exist error
                    if (data.message && (data.message.includes('does not exist') || data.message.includes('Table does not exist'))) {
                        alert('⚠️ Database Setup Required\n\nThe account_deletion_requests table has not been created yet.\n\nPlease run create_account_deletion_requests_table.sql in your Supabase SQL Editor.');
                        deletionRequestsData = [];
                    } else {
                        // For other errors, show error but don't throw (allow page to load with empty data)
                        console.error('Error loading deletion requests:', data.error);
                        deletionRequestsData = [];
                    }
                } else if (!data.hasOwnProperty('requests')) {
                    console.error('Unexpected response structure - missing "requests" property:', data);
                    deletionRequestsData = [];
                } else if (!Array.isArray(data.requests)) {
                    console.error('Requests is not an array. Type:', typeof data.requests, 'Value:', data.requests);
                    // Force to array - handle null/undefined/object cases
                    if (data.requests === null || data.requests === undefined) {
                        deletionRequestsData = [];
                    } else if (Array.isArray(data.requests)) {
                        deletionRequestsData = data.requests;
                    } else {
                        // Not an array, force to empty array
                        deletionRequestsData = [];
                    }
                } else {
                    // Valid array
                    deletionRequestsData = data.requests;
                }

                // Final safety check - ensure it's definitely an array
                if (!Array.isArray(deletionRequestsData)) {
                    console.error('deletionRequestsData is still not an array after validation. Forcing to empty array.');
                    deletionRequestsData = [];
                }

                console.log('Loaded deletion requests:', deletionRequestsData.length, 'requests');
                console.log('Deletion requests data type:', typeof deletionRequestsData, 'Is array:', Array.isArray(deletionRequestsData));

                // Get unique user IDs (only if we have valid array data)
                var userIds = [];
                if (Array.isArray(deletionRequestsData) && deletionRequestsData.length > 0) {
                    try {
                        userIds = [...new Set(deletionRequestsData.map(function(r) {
                            return r && r.user_id ? r.user_id : null;
                        }).filter(function(id) {
                            return id !== null && id !== undefined;
                        }))];
                    } catch (mapError) {
                        console.error('Error mapping user IDs:', mapError);
                        console.error('deletionRequestsData:', deletionRequestsData);
                        userIds = [];
                    }
                }

                console.log('User IDs to fetch:', userIds.length, 'user IDs');

                // Load user info for all user IDs
                if (userIds.length > 0) {
                    await loadUsersInfo(userIds);
                }

                // Populate table
                populateUsersTable();

                // Initialize or refresh DataTable
                initializeDataTable();

            } catch (error) {
                console.error('Error in loadDeletionRequests:', error);
                console.error('Error stack:', error.stack);
                alert('Failed to load deletion requests: ' + error.message);
                deletionRequestsData = [];
                populateUsersTable();
                initializeDataTable();
            }
        }

        // Load user info from Supabase Auth via PHP endpoint
        async function loadUsersInfo(userIds) {
            try {
                const response = await fetch('get_users_info.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        user_ids: userIds
                    })
                });

                if (!response.ok) {
                    throw new Error('Failed to fetch user info: ' + response.statusText);
                }

                const data = await response.json();
                if (data.error) {
                    throw new Error(data.error);
                }

                usersMap = data.users || {};
                console.log('Loaded user info for', Object.keys(usersMap).length, 'users');
            } catch (error) {
                console.error('Error loading user info:', error);
                // Continue even if user info fails to load
            }
        }

        // Populate users table with deletion requests
        function populateUsersTable() {
            var tbody = $('#usersTableBody');
            tbody.empty();

            // Safety check - ensure deletionRequestsData is an array
            if (!Array.isArray(deletionRequestsData)) {
                console.error('populateUsersTable: deletionRequestsData is not an array:', typeof deletionRequestsData);
                deletionRequestsData = [];
            }

            if (deletionRequestsData.length === 0) {
                tbody.append('<tr><td colspan="5" class="empty-state text-center py-16 text-gray-700"><i class="fa fa-search text-5xl block mb-4 opacity-20"></i>No account deletion requests found.</td></tr>');
                $('#noResultsBody').hide();
                return;
            }

            try {
                deletionRequestsData.forEach(function(request) {
                var userInfo = usersMap[request.user_id] || {};
                var username = userInfo.username || 'User ' + (request.user_id ? request.user_id.substring(0, 8) : 'Unknown');
                var email = userInfo.email || 'N/A';

                // Format requested date
                var requestedDate = 'N/A';
                if (request.requested_at) {
                    var date = new Date(request.requested_at);
                    requestedDate = date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }

                // Get status text and class
                var statusClass = 'status-' + request.status;
                var statusText = '';
                switch (request.status) {
                    case 'pending':
                        statusText = 'Pending Review';
                        break;
                    case 'approved':
                        statusText = 'Approved';
                        break;
                    case 'declined':
                        statusText = 'Declined';
                        break;
                    case 'in_progress':
                        statusText = 'Deletion in Progress';
                        break;
                    case 'cancelled':
                        statusText = 'Cancelled';
                        break;
                    case 'completed':
                        statusText = 'Completed';
                        break;
                    default:
                        statusText = request.status;
                }

                // Only show review button for pending requests
                var actionButton = '';
                if (request.status === 'pending') {
                    actionButton = '<button class="inline-flex items-center gap-1.5 text-xs font-semibold bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg transition-colors" onclick="reviewRequest(\'' + request.id + '\')"><i class="fa fa-search"></i> Review Request</button>';
                } else {
                    actionButton = '<span style="color: #9ca3af; font-style: italic; font-size: 13px;">No action available</span>';
                }

                var requestedDateOnly = request.requested_at ? request.requested_at.substring(0, 10) : '';
                var row = '<tr data-request-id="' + request.id + '" data-status="' + escapeHtml(request.status || '') + '" data-date="' + escapeHtml(requestedDateOnly) + '" data-username="' + escapeHtml((username || '').toLowerCase()) + '">' +
                    '<td>' + escapeHtml(username) + '</td>' +
                    '<td>' + escapeHtml(email) + '</td>' +
                    '<td>' + requestedDate + '</td>' +
                    '<td class="status-cell"><span class="status-badge ' + statusClass + '">' + statusText + '</span></td>' +
                    '<td>' + actionButton + '</td>' +
                    '</tr>';

                tbody.append(row);
                });
            } catch (forEachError) {
                console.error('Error in populateUsersTable forEach:', forEachError);
                console.error('deletionRequestsData:', deletionRequestsData);
                tbody.append('<tr><td colspan="5" class="empty-state text-center py-10 text-red-500">Error loading deletion requests. Please check console.</td></tr>');
            }
            $('#noResultsBody').hide();
        }

        function initUsersDateFilterPicker() {
            if (typeof flatpickr === 'undefined') return;
            if (usersDatePicker) return;
            usersDatePicker = flatpickr('#filterDateUsers', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd-m-Y',
                altInputClass: 'px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 w-[160px]',
                monthSelectorType: 'static',
                disableMobile: true,
                allowInput: false,
                onChange: function() { applyUsersFilters(); }
            });
        }

        function applyUsersFilters() {
            var statusFilter = $('#filterStatusUsers').val();
            var dateFilter = $('#filterDateUsers').val();
            var searchText = $('#searchUsernameUsers').val().toLowerCase().trim();

            var wasDataTableActive = false;
            if (dataTable) { try { dataTable.fnDestroy(); dataTable = null; wasDataTableActive = true; } catch (e) {} }

            $('#usersTableBody tr').show();
            var totalRows = $('#usersTableBody tr').length;

            if (statusFilter !== 'all') {
                $('#usersTableBody tr').each(function() {
                    if ($(this).attr('data-status') !== statusFilter) $(this).hide();
                });
            }
            if (dateFilter !== '') {
                $('#usersTableBody tr:visible').each(function() {
                    if ($(this).attr('data-date') !== dateFilter) $(this).hide();
                });
            }
            if (searchText !== '') {
                $('#usersTableBody tr:visible').each(function() {
                    if ($(this).find('td:first').text().toLowerCase().indexOf(searchText) === -1) $(this).hide();
                });
            }

            var visibleCount = $('#usersTableBody tr:visible').length;
            if (visibleCount === 0 && totalRows > 0) { $('#noResultsBody').show(); } else { $('#noResultsBody').hide(); }
            if (wasDataTableActive && visibleCount > 0) { setTimeout(function() { initializeDataTable(); }, 100); }
        }

        function resetUsersFilters() {
            $('#filterStatusUsers').val('all');
            if (usersDatePicker) { usersDatePicker.clear(); } else { $('#filterDateUsers').val(''); }
            $('#searchUsernameUsers').val('');
            if (dataTable) { try { dataTable.fnDestroy(); dataTable = null; } catch (e) {} }
            $('#usersTableBody tr').show();
            $('#noResultsBody').hide();
            if ($('#usersTableBody tr').length > 0) { setTimeout(function() { initializeDataTable(); }, 100); }
        }

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            if (!text) return '';
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        // Review deletion request
        function reviewRequest(requestId) {
            // Find request data
            var request = deletionRequestsData.find(function(r) { return r.id === requestId; });
            if (!request) {
                alert('Request not found.');
                return;
            }

            // Get user info
            var userInfo = usersMap[request.user_id] || {};
            var username = userInfo.username || 'User ' + (request.user_id ? request.user_id.substring(0, 8) : 'Unknown');
            var email = userInfo.email || 'N/A';

            // Format requested date
            var requestedDate = 'N/A';
            if (request.requested_at) {
                var date = new Date(request.requested_at);
                requestedDate = date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }

            // Store current request being reviewed
            currentReviewRequest = requestId;

            // Populate modal with request info
            $('#modalUsername').text(username);
            $('#modalEmail').text(email);
            $('#modalRequestedDate').text(requestedDate);
            $('#modalRequestId').text(requestId);

            // Show modal
            $('#reviewModal').addClass('active');
            $('body').css('overflow', 'hidden');
        }

        function closeReviewModal() {
            $('#reviewModal').removeClass('active');
            $('body').css('overflow', '');
            currentReviewRequest = null;
        }

        // Accept deletion request
        async function acceptTermination() {
            if (!currentReviewRequest || !currentUser) {
                alert('No request selected or user not logged in.');
                return;
            }

            try {
                // Update request status to approved (which will set it to in_progress)
                const response = await fetch('update_deletion_request.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        request_id: currentReviewRequest,
                        status: 'approved',
                        reviewed_by: currentUser.id,
                        admin_notes: 'Request approved by administrator'
                    })
                });

                const data = await response.json();

                if (!response.ok || data.error) {
                    throw new Error(data.error || 'Failed to update deletion request');
                }

                // Close modal
                closeReviewModal();

                // Show confirmation
                alert('Request accepted! The account deletion process has started and will take 30 days to complete.\n\nIf the user logs in during this period, the deletion will be cancelled automatically.\n\nAn email notification has been sent to the user.');

                // Reload the page after alert is dismissed
                window.location.reload();

            } catch (error) {
                console.error('Error accepting termination:', error);
                alert('Failed to accept request: ' + (error.message || error));
            }
        }

        // Decline deletion request
        async function declineTermination() {
            if (!currentReviewRequest || !currentUser) {
                alert('No request selected or user not logged in.');
                return;
            }

            try {
                // Update request status to declined
                const response = await fetch('update_deletion_request.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        request_id: currentReviewRequest,
                        status: 'declined',
                        reviewed_by: currentUser.id,
                        admin_notes: 'Request declined by administrator'
                    })
                });

                const data = await response.json();

                if (!response.ok || data.error) {
                    throw new Error(data.error || 'Failed to update deletion request');
                }

                // Close modal
                closeReviewModal();

                // Show confirmation
                alert('Request declined. The user status has been updated to "Declined".\n\nAn email notification has been sent to the user.');

                // Reload the page after alert is dismissed
                window.location.reload();

            } catch (error) {
                console.error('Error declining termination:', error);
                alert('Failed to decline request: ' + (error.message || error));
            }
        }

        // Initialize DataTable
        function initializeDataTable() {
            // Destroy existing DataTable if it exists
            if (dataTable) {
                try {
                    dataTable.fnDestroy();
                } catch (e) {
                    console.warn('Error destroying DataTable:', e);
                }
                dataTable = null;
            }

            // Wait a bit for DOM to update
            setTimeout(function() {
                if ($('#dataTables-example').length) {
                    var rowCount = $('#dataTables-example tbody tr').length;
                    // Only initialize if there are rows (not just the "No requests" message)
                    if (rowCount > 0 && !$('#dataTables-example tbody tr td[colspan]').length) {
                        try {
                            dataTable = $('#dataTables-example').dataTable({
                                "order": [[ 2, "desc" ]], // Sort by requested date (newest first)
                                "paging": false,
                                "searching": false,
                                "info": false,
                                "autoWidth": false,
                                "columnDefs": [
                                    { "orderable": true, "targets": [0, 1, 2, 3] },
                                    { "orderable": false, "targets": 4 } // Disable sorting on Actions column
                                ]
                            });
                        } catch (e) {
                            console.error('Error initializing DataTable:', e);
                        }
                    }
                }
            }, 100);
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

        // Close modal when clicking outside of it
        $(document).on('click', '#reviewModal', function(e) {
            if (e.target.id === 'reviewModal') {
                closeReviewModal();
            }
        });

        // Close modal with Escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#reviewModal').hasClass('active')) {
                closeReviewModal();
            }
        });
    </script>
</body>
</html>







