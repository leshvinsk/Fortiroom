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
    <link rel="stylesheet" href="assets/css/tailwind.css">
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
            font-size: 14px; font-weight: 500; color: rgba(255,255,255,0.85);
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

        /* Pod card status top borders */
        .pod-card.available  { border-top: 4px solid #16a34a; }
        .pod-card.occupied   { border-top: 4px solid #f59e0b; }
        .pod-card.maintenance{ border-top: 4px solid #ef4444; }
        .pod-card.cleaning   { border-top: 4px solid #3b82f6; }
        .pod-card.suspended  { border-top: 4px solid #9ca3af; opacity: 0.75; }
        .pod-card.idle       { border-top: 4px solid #8b5cf6; }

        /* Pod status badge */
        .pod-status { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
        .pod-status.available   { background: #dcfce7; color: #166534; }
        .pod-status.occupied    { background: #fef9c3; color: #854d0e; }
        .pod-status.maintenance { background: #fee2e2; color: #991b1b; }
        .pod-status.cleaning    { background: #dbeafe; color: #1e40af; }
        .pod-status.suspended   { background: #f3f4f6; color: #6b7280; }
        .pod-status.idle        { background: #ede9fe; color: #5b21b6; }

        /* AQI colors */
        .aqi-good     { color: #16a34a; }
        .aqi-moderate { color: #d97706; }
        .aqi-poor     { color: #dc2626; }
        .grayed-out   { color: #d1d5db; }

        /* Fan controls */
        .fan-btn {
            width: 22px; height: 22px; border-radius: 50%;
            border: 1.5px solid #16a34a; background: #fff; color: #16a34a;
            font-size: 13px; font-weight: bold; cursor: pointer;
            display: inline-flex; align-items: center; justify-content: center;
            transition: all 0.15s ease;
        }
        .fan-btn:hover:not(:disabled) { background: #16a34a; color: #fff; transform: scale(1.1); }
        .fan-btn:disabled { opacity: 0.35; cursor: not-allowed; border-color: #d1d5db; color: #9ca3af; }
        .fan-mode-toggle {
            width: 36px; height: 20px; border-radius: 10px;
            border: 1.5px solid #16a34a; background: #fbbf24; color: #333;
            font-size: 10px; font-weight: 700; cursor: pointer;
            display: inline-flex; align-items: center; justify-content: center;
            text-transform: uppercase; transition: all 0.15s ease;
        }
        .fan-mode-toggle.manual { background: #16a34a; color: #fff; }
        .fan-control-disabled { opacity: 0.45; pointer-events: none; }
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

        /* Modal animation */
        @keyframes slideDown {
            from { transform: translateY(-40px); opacity: 0; }
            to   { transform: translateY(0); opacity: 1; }
        }
        .modal-animate { animation: slideDown 0.25s ease; }

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
                <li><a class="sidebar-nav-link" href="penalties.php"><i class="fa fa-exclamation-triangle fa-fw"></i> Penalties</a></li>
                <li><a class="sidebar-nav-link active-menu" href="pods.php"><i class="fa fa-building fa-fw"></i> Pods Management</a></li>
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
                <h1 class="text-2xl font-light tracking-wide font-semibold text-gray-700 uppercase">Pods Management</h1>
                <button onclick="openCreatePodModal()" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors shadow-sm">
                    <i class="fa fa-plus-circle"></i> Create New Pod
                </button>
            </div>

            <!-- Pod Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 dashboard-cards" id="podCardsContainer">
                <!-- Pod cards generated dynamically -->
            </div>

        </div>
    </main>
</div>

<!-- Create Pod Modal -->
<div id="createPodModal" class="fixed inset-0 z-[9999]" style="display:none; align-items:center; justify-content:center;">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-2xl w-[90%] max-w-md shadow-2xl modal-animate mx-auto my-auto" style="position:relative; z-index:1;">
        <div class="flex items-center justify-between px-8 py-6 border-b border-gray-100">
            <h2 class="text-xl font-semibold text-gray-900">Create New Pod</h2>
            <button class="modal-close text-gray-400 hover:text-gray-600 text-2xl leading-none font-light transition-colors" onclick="closeCreatePodModal()">&times;</button>
        </div>
        <form id="createPodForm" onsubmit="createNewPod(event)" class="px-8 py-6 space-y-5">
            <div>
                <label for="podName" class="block text-sm font-medium text-gray-700 mb-2">Pod Name <span class="text-red-500">*</span></label>
                <input type="text" id="podName" name="podName" placeholder="e.g., Pod 5" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-colors">
            </div>
            <div>
                <label for="podCapacity" class="block text-sm font-medium text-gray-700 mb-2">Pod Capacity <span class="text-red-500">*</span></label>
                <input type="number" id="podCapacity" name="podCapacity" placeholder="e.g., 2" min="1" max="10" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-colors">
            </div>
            <div>
                <label for="podHardwareId" class="block text-sm font-medium text-gray-700 mb-2">Pod Hardware ID <span class="text-red-500">*</span></label>
                <input type="text" id="podHardwareId" name="podHardwareId" placeholder="e.g., HW-POD-005" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-colors">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeCreatePodModal()"
                    class="flex-1 py-3 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Cancel</button>
                <button type="submit"
                    class="flex-1 py-3 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors shadow-sm">Create Pod</button>
            </div>
        </form>
    </div>
</div>

<!-- JS Scripts -->
<script src="assets/js/jquery-1.10.2.js"></script>
<script src="assets/js/tailwind-selects.js"></script>
<script>
    var supabase = null;
    var currentUser = null;
    var podsData = [];
    var bookingsData = [];
    var simulationInterval = null;

    document.addEventListener('DOMContentLoaded', async function() {
        const { createClient } = window.supabase || {};
        if (!createClient) { alert('Failed to load database connection. Please refresh the page.'); return; }
        supabase = createClient(window.__SUPABASE__.url, window.__SUPABASE__.anonKey);

        const { data: sessionData, error: sessionError } = await supabase.auth.getSession();
        if (sessionError || !sessionData?.session) { window.location.href = '../login.php'; return; }
        currentUser = sessionData.session.user;

        await loadBookings();
        await loadPods();

        if (simulationInterval) clearInterval(simulationInterval);
        simulationInterval = setInterval(async function() {
            await loadBookings();
            await updatePodStatusesFromBookings();
            simulateUpdates();
        }, 10000);
    });

    async function loadBookings() {
        try {
            const { data: bookings, error: bookingsError } = await supabase
                .from('bookings')
                .select('id, pod_id, booking_date, check_in_time, check_out_time, number_of_people')
                .order('booking_date', { ascending: false })
                .order('check_in_time', { ascending: true });
            if (bookingsError) { console.error('Error loading bookings:', bookingsError); bookingsData = []; return; }
            bookingsData = bookings || [];
        } catch (error) { console.error('Error in loadBookings:', error); bookingsData = []; }
    }

    function getBookingStatus(bookingDate, checkIn, checkOut) {
        var now = new Date();
        var checkInDateTime  = new Date(bookingDate + 'T' + checkIn + ':00');
        var checkOutDateTime = new Date(bookingDate + 'T' + checkOut + ':00');
        var checkInWindow  = 15 * 60 * 1000;
        var checkOutWindow = 15 * 60 * 1000;
        var checkInStart  = new Date(checkInDateTime.getTime()  - checkInWindow);
        var checkInEnd    = checkInDateTime;
        var checkOutStart = new Date(checkOutDateTime.getTime() - checkOutWindow);
        if (now < checkInStart) return 'upcoming';
        else if (now >= checkInStart && now < checkInEnd) return 'in-progress-checkin';
        else if (now >= checkInEnd && now < checkOutStart) return 'in-progress-occupied';
        else if (now >= checkOutStart && now < checkOutDateTime) return 'in-progress-checkout';
        else return 'completed';
    }

    function hasActiveBooking(podId) {
        for (var i = 0; i < bookingsData.length; i++) {
            var booking = bookingsData[i];
            if (String(booking.pod_id) === String(podId)) {
                var checkInTime  = booking.check_in_time  ? booking.check_in_time.substring(0, 5)  : '';
                var checkOutTime = booking.check_out_time ? booking.check_out_time.substring(0, 5) : '';
                if (checkInTime && checkOutTime) {
                    var status = getBookingStatus(booking.booking_date, checkInTime, checkOutTime);
                    if (status === 'in-progress-checkin' || status === 'in-progress-occupied' || status === 'in-progress-checkout') return true;
                }
            }
        }
        return false;
    }

    async function updatePodStatusesFromBookings() {
        if (!supabase || podsData.length === 0) return;
        try {
            for (var i = 0; i < podsData.length; i++) {
                var pod = podsData[i];
                if (pod.status === 'suspended') continue;
                var hasActive = hasActiveBooking(pod.id);
                var newStatus = hasActive ? 'occupied' : 'idle';
                if (pod.status !== newStatus) {
                    const { error: updateError } = await supabase.from('pods').update({ status: newStatus }).eq('id', pod.id);
                    if (updateError) { console.error('Error updating pod status for pod ' + pod.id + ':', updateError); }
                    else { pod.status = newStatus; }
                }
            }
            renderPodCards();
        } catch (error) { console.error('Error in updatePodStatusesFromBookings:', error); }
    }

    function calculateTargetTemperature(fanSpeed) {
        if (fanSpeed === null || fanSpeed === undefined) return null;
        var baseTemp = 30 - (fanSpeed * 2);
        return baseTemp + (Math.random() * 2 - 1);
    }

    async function loadPods() {
        try {
            const { data, error } = await supabase
                .from('pods')
                .select('id, name, capacity, hardware_id, status, saved_state, created_at, updated_at')
                .order('created_at', { ascending: true });
            if (error) { console.error('Error loading pods:', error); alert('Failed to load pods from database: ' + error.message); return; }

            podsData = (data || []).map(pod => {
                var isSuspended = pod.status === 'suspended';
                var simulatedFanSpeed = isSuspended ? null : 3;
                var simulatedFanMode  = isSuspended ? null : 'M';
                var simulatedTemp = isSuspended ? null : calculateTargetTemperature(simulatedFanSpeed);
                var simulatedAqi  = isSuspended ? null : (30 + Math.floor(Math.random() * 20));
                var initialStatus = pod.status || 'idle';
                if (!isSuspended && bookingsData.length > 0) {
                    initialStatus = hasActiveBooking(pod.id) ? 'occupied' : 'idle';
                }
                return {
                    id: pod.id, name: pod.name || `Pod ${pod.id}`,
                    capacity: pod.capacity || 1, hardwareId: pod.hardware_id || '',
                    status: initialStatus, temperature: simulatedTemp,
                    fanSpeed: simulatedFanSpeed, fanMode: simulatedFanMode,
                    aqi: simulatedAqi, suspended: isSuspended,
                    savedState: pod.saved_state ? (typeof pod.saved_state === 'string' ? JSON.parse(pod.saved_state) : pod.saved_state) : null
                };
            });

            await updatePodStatusesFromBookings();
            renderPodCards();
        } catch (error) { console.error('Error in loadPods:', error); alert('Failed to load pods: ' + error.message); }
    }

    function openCreatePodModal() {
        $('#createPodModal').css('display', 'flex');
        $('body').css('overflow', 'hidden');
    }

    function closeCreatePodModal() {
        $('#createPodModal').css('display', 'none');
        $('body').css('overflow', '');
        $('#createPodForm')[0].reset();
    }

    async function createNewPod(event) {
        event.preventDefault();
        if (!supabase) { alert('Database connection not available. Please refresh the page.'); return; }
        var podName = $('#podName').val().trim();
        var podCapacity = parseInt($('#podCapacity').val());
        var podHardwareId = $('#podHardwareId').val().trim();
        if (!podName || !podCapacity || !podHardwareId) { alert('Please fill in all required fields.'); return; }
        try {
            const { data: newPod, error: insertError } = await supabase
                .from('pods')
                .insert([{ name: podName, capacity: podCapacity, hardware_id: podHardwareId, status: 'idle', saved_state: null }])
                .select().single();
            if (insertError) { console.error('Error creating pod:', insertError); alert('Failed to create pod: ' + insertError.message); return; }
            closeCreatePodModal();
            await loadPods();
            alert('✓ Pod created successfully!\n\nPod Name: ' + podName + '\nCapacity: ' + podCapacity + ' person(s)\nHardware ID: ' + podHardwareId + '\nStatus: IDLE\n\nThe pod is now ready for operation.');
        } catch (error) { console.error('Error in createNewPod:', error); alert('Failed to create pod: ' + error.message); }
    }

    $(document).on('click', function(event) {
        if ($(event.target).is('#createPodModal')) closeCreatePodModal();
    });

    function getAQIClass(aqi) {
        if (aqi === null || aqi === undefined) return '';
        if (aqi <= 50) return 'aqi-good';
        if (aqi <= 100) return 'aqi-moderate';
        return 'aqi-poor';
    }

    function getAQILabel(aqi) {
        if (aqi === null || aqi === undefined) return '';
        if (aqi <= 50) return 'Good';
        if (aqi <= 100) return 'Moderate';
        return 'Poor';
    }

    function toggleFanMode(podId) {
        podId = String(podId);
        var pod = podsData.find(p => String(p.id) === podId);
        if (pod && !pod.suspended) {
            pod.fanMode = pod.fanMode === 'M' ? 'A' : 'M';
            updatePodCard(podId);
        }
    }

    function changeFanSpeed(podId, change) {
        podId = String(podId);
        var pod = podsData.find(p => String(p.id) === podId);
        if (pod && !pod.suspended && pod.fanMode === 'A') {
            var currentSpeed = pod.fanSpeed || 3;
            var newSpeed = currentSpeed + change;
            if (newSpeed >= 0 && newSpeed <= 5) {
                var targetTemp = calculateTargetTemperature(newSpeed);
                var currentTemp = pod.temperature || calculateTargetTemperature(3);
                pod.fanSpeed = newSpeed;
                pod.temperature = currentTemp + ((targetTemp - currentTemp) * 0.6);
                updatePodCard(podId);
            }
        }
    }

    async function suspendPod(podId) {
        if (!supabase) { alert('Database connection not available. Please refresh the page.'); return; }
        podId = String(podId);
        var pod = podsData.find(p => String(p.id) === podId);
        var podName = pod ? (pod.name || 'Pod ' + podId) : 'Pod ' + podId;
        if (pod && !pod.suspended) {
            if (confirm('Suspend ' + podName + '?\n\n⚠️ Warning:\n• All bookings for this pod will be removed\n• No penalties will be applied\n• Email notifications will be sent to all affected users\n• Pod systems will be shut down for maintenance\n\nDo you want to proceed?')) {
                try {
                    var deletedCount = 0;
                    const { data: deletedBookings, error: deleteError } = await supabase.from('bookings').delete().eq('pod_id', podId).select();
                    if (deleteError) { console.warn('Warning: Could not delete bookings for pod. Continuing with suspension.'); }
                    else { deletedCount = deletedBookings ? deletedBookings.length : 0; }
                    const { error: updateError } = await supabase.from('pods').update({ status: 'suspended', saved_state: JSON.stringify({ status: pod.status }) }).eq('id', podId);
                    if (updateError) { alert('Failed to suspend pod: ' + updateError.message); return; }
                    await loadBookings();
                    await loadPods();
                    var msg = deletedCount > 0 ? deletedCount + ' booking(s) removed. ' : '';
                    alert('✓ ' + podName + ' has been suspended.\n\n' + msg + 'No penalties were applied.\nEmail notifications have been sent to affected users.');
                } catch (error) { console.error('Error in suspendPod:', error); alert('Failed to suspend pod: ' + error.message); }
            }
        }
    }

    async function unsuspendPod(podId) {
        if (!supabase) { alert('Database connection not available. Please refresh the page.'); return; }
        podId = String(podId);
        var pod = podsData.find(p => String(p.id) === podId);
        var podName = pod ? (pod.name || 'Pod ' + podId) : 'Pod ' + podId;
        if (pod && pod.suspended) {
            alert('Operate ' + podName + '?\n\n📧 Email notifications will be sent to all users to acknowledge the pod\'s new status.\n\nThe pod will be set to IDLE status and users can make bookings again.');
            try {
                await loadBookings();
                const { error: updateError } = await supabase.from('pods').update({ status: 'idle', saved_state: null }).eq('id', podId);
                if (updateError) { alert('Failed to unsuspend pod: ' + updateError.message); return; }
                await loadPods();
                setTimeout(function() { alert('✓ ' + podName + ' is now operational!\n\nStatus: IDLE\nUsers can now make bookings for this pod.\nEmail notifications have been sent to users.'); }, 100);
            } catch (error) { console.error('Error in unsuspendPod:', error); alert('Failed to unsuspend pod: ' + error.message); }
        }
    }

    async function deletePod(podId) {
        if (!supabase) { alert('Database connection not available. Please refresh the page.'); return; }
        podId = String(podId);
        var pod = podsData.find(p => String(p.id) === podId);
        var podName = pod ? (pod.name || 'Pod ' + podId) : 'Pod ' + podId;
        if (pod && pod.suspended) {
            if (confirm('⚠️ DELETE ' + podName.toUpperCase() + '?\n\n🚨 WARNING: This action is PERMANENT and CANNOT be undone!\n\n• ' + podName + ' will be completely removed from the system\n• All historical data will be lost\n\nAre you absolutely sure?')) {
                if (confirm('FINAL CONFIRMATION\n\nClick OK to permanently delete ' + podName + '\nClick Cancel to keep the pod')) {
                    try {
                        const { error: deleteError } = await supabase.from('pods').delete().eq('id', podId);
                        if (deleteError) { alert('Failed to delete pod: ' + deleteError.message); return; }
                        await loadPods();
                        alert('✓ ' + podName + ' has been permanently deleted from the system.');
                    } catch (error) { console.error('Error in deletePod:', error); alert('Failed to delete pod: ' + error.message); }
                }
            }
        }
    }

    function updatePodCard(podId) {
        podId = String(podId);
        var pod = podsData.find(p => String(p.id) === podId);
        if (!pod) return;
        var podCard = $('#pod-' + pod.id);
        if (podCard.length === 0) return;

        var tempElement = podCard.find('.info-value').first();
        if (tempElement.length) {
            if (pod.suspended || pod.temperature === null) tempElement.html('<span class="grayed-out">NULL</span>');
            else tempElement.html(pod.temperature.toFixed(1) + '°C');
        }
        var aqiElement = podCard.find('.info-value').last();
        if (aqiElement.length) {
            if (pod.suspended || pod.aqi === null) aqiElement.html('<span class="grayed-out">NULL</span>');
            else {
                var aqiClass = getAQIClass(pod.aqi); var aqiLabel = getAQILabel(pod.aqi);
                aqiElement.html(pod.aqi + ' <small>(' + aqiLabel + ')</small>');
                aqiElement.removeClass('aqi-good aqi-moderate aqi-poor').addClass(aqiClass);
            }
        }
        var statusElement = podCard.find('.pod-status');
        if (statusElement.length) {
            statusElement.text(pod.status);
            statusElement.removeClass('available occupied maintenance cleaning suspended idle').addClass(pod.status);
        }
        $('#pod-' + pod.id + '-fanspeed').text(pod.fanSpeed || 3);
        var modeBtn = $('#pod-' + pod.id + '-fan-mode');
        if (modeBtn.length) {
            modeBtn.text(pod.fanMode || 'M');
            modeBtn.toggleClass('manual', pod.fanMode === 'A');
        }
        var isManualMode = pod.fanMode === 'A';
        var isDisabled = pod.suspended || !isManualMode;
        $('#pod-' + pod.id + '-btn-minus').prop('disabled', isDisabled || (pod.fanSpeed || 3) === 0);
        $('#pod-' + pod.id + '-btn-plus').prop('disabled', isDisabled || (pod.fanSpeed || 3) === 5);
    }

    function generatePodCard(pod) {
        var aqiClass  = (pod.suspended || pod.aqi === null) ? '' : getAQIClass(pod.aqi);
        var aqiLabel  = (pod.suspended || pod.aqi === null) ? '' : getAQILabel(pod.aqi);
        var tempDisplay = (pod.suspended || pod.temperature === null) ? '<span class="grayed-out">NULL</span>' : pod.temperature.toFixed(1) + '°C';
        var podIdEscaped = String(pod.id).replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '\\"');
        var isManualMode = pod.fanMode === 'A';
        var isDisabled = pod.suspended || !isManualMode;

        var fanSpeedHTML = '';
        if (pod.suspended) {
            fanSpeedHTML = '<div class="grayed-out text-sm font-medium">NULL</div>';
        } else {
            fanSpeedHTML = `
                <div class="flex flex-col items-center gap-1">
                    <button class="fan-mode-toggle ${isManualMode ? 'manual' : ''}"
                            id="pod-${pod.id}-fan-mode"
                            onclick="toggleFanMode('${podIdEscaped}')"
                            title="${isManualMode ? 'Manual Mode' : 'Auto Mode'}">
                        ${pod.fanMode || 'M'}
                    </button>
                    <div class="flex items-center gap-1.5 ${!isManualMode ? 'fan-control-disabled' : ''}">
                        <button class="fan-btn" id="pod-${pod.id}-btn-minus"
                                onclick="changeFanSpeed('${podIdEscaped}', -1)"
                                ${isDisabled || pod.fanSpeed === 0 ? 'disabled' : ''}>−</button>
                        <span class="font-bold text-base text-gray-800 min-w-[20px] text-center" id="pod-${pod.id}-fanspeed">${pod.fanSpeed || 3}</span>
                        <button class="fan-btn" id="pod-${pod.id}-btn-plus"
                                onclick="changeFanSpeed('${podIdEscaped}', 1)"
                                ${isDisabled || pod.fanSpeed === 5 ? 'disabled' : ''}>+</button>
                    </div>
                </div>`;
        }

        var aqiDisplay = (pod.suspended || pod.aqi === null) ? '<span class="grayed-out">NULL</span>' : `${pod.aqi} <small>(${aqiLabel})</small>`;

        var actionButtons = '';
        if (pod.suspended) {
            actionButtons = `
                <div class="flex gap-2.5 mt-4">
                    <button class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors"
                            onclick="unsuspendPod('${podIdEscaped}')">
                        <i class="fa fa-play"></i> Operate
                    </button>
                    <button class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-semibold text-white bg-red-500 hover:bg-red-600 rounded-lg transition-colors"
                            onclick="deletePod('${podIdEscaped}')">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </div>`;
        } else {
            actionButtons = `
                <div class="flex gap-2.5 mt-4">
                    <button class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-semibold text-white bg-amber-500 hover:bg-amber-600 rounded-lg transition-colors"
                            onclick="suspendPod('${podIdEscaped}')">
                        <i class="fa fa-pause"></i> Suspend
                    </button>
                    <button class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-semibold text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed"
                            onclick="deletePod('${podIdEscaped}')" disabled>
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </div>`;
        }

        return `
            <div class="pod-card ${pod.status} bg-white rounded-xl shadow-sm overflow-hidden hover:-translate-y-1 transition-transform duration-200" id="pod-${pod.id}">
                <div class="p-5">
                    <div class="flex items-start justify-between mb-4 pb-4 border-b border-gray-100">
                        <div>
                            <div class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                <i class="fa fa-building text-gray-400"></i> ${pod.name || 'Pod ' + pod.id}
                            </div>
                            <div class="mt-1 text-xs text-gray-400 font-medium">
                                <i class="fa fa-users text-green-500 mr-1"></i>
                                ${pod.capacity || 1} ${(pod.capacity || 1) === 1 ? 'person' : 'people'}
                            </div>
                        </div>
                        <div class="pod-status ${pod.status}">${pod.status}</div>
                    </div>

                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2.5">
                            <div class="text-xs font-semibold text-gray-500 flex items-center gap-2">
                                <i class="fa fa-thermometer-half text-gray-400"></i> Temperature
                            </div>
                            <div class="info-value text-sm font-bold text-gray-800">${tempDisplay}</div>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2.5">
                            <div class="text-xs font-semibold text-gray-500 flex items-center gap-2">
                                <i class="fa fa-circle-o-notch text-gray-400"></i> Fan Speed
                            </div>
                            ${fanSpeedHTML}
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2.5">
                            <div class="text-xs font-semibold text-gray-500 flex items-center gap-2">
                                <i class="fa fa-leaf text-gray-400"></i> AQI Index
                            </div>
                            <div class="info-value text-sm font-bold ${aqiClass}">${aqiDisplay}</div>
                        </div>
                    </div>

                    ${actionButtons}
                </div>
            </div>`;
    }

    function renderPodCards() {
        var container = $('#podCardsContainer');
        container.empty();
        podsData.forEach(function(pod) { container.append(generatePodCard(pod)); });
    }

    function simulateUpdates() {
        if (podsData.length === 0) return;
        for (var i = 0; i < podsData.length; i++) {
            var pod = podsData[i];
            if (!pod.suspended && pod.temperature !== null) {
                var targetTemp = calculateTargetTemperature(pod.fanSpeed || 3);
                if (targetTemp !== null) {
                    var adjustment = (targetTemp - pod.temperature) * 0.05;
                    var newTemp = pod.temperature + adjustment + (Math.random() - 0.5) * 0.2;
                    newTemp = Math.round(newTemp * 10) / 10;
                    var minTemp = 18 + ((5 - (pod.fanSpeed || 3)) * 2);
                    var maxTemp = 30 - ((pod.fanSpeed || 3) * 2);
                    pod.temperature = Math.max(minTemp, Math.min(maxTemp, newTemp));
                }
                var newAqi = (pod.aqi || 30) + Math.floor((Math.random() - 0.5) * 4);
                pod.aqi = Math.max(20, Math.min(150, newAqi));
            }
        }
        for (var i = 0; i < podsData.length; i++) { updatePodCard(podsData[i].id); }
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
</script>
<script src="assets/js/custom-scripts.js"></script>
</body>
</html>







