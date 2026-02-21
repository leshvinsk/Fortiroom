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
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter','sans-serif'] } } } }</script>
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

        /* Modal overlay */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }
        .modal-overlay.active { display: flex; }

        @keyframes slideDown {
            from { transform: translateY(-30px); opacity: 0; }
            to   { transform: translateY(0);     opacity: 1; }
        }
        .modal-animate { animation: slideDown 0.25s ease; }

        /* Password toggle icon positioning */
        .form-group-update { position: relative; }
        .toggle-password {
            position: absolute;
            right: 14px;
            bottom: 13px;
            cursor: pointer;
            color: #9ca3af;
            font-size: 15px;
            transition: color 0.2s;
            z-index: 10;
        }
        .toggle-password:hover { color: #16a34a; }

        /* Password input right padding to avoid overlap with eye icon */
        .pwd-input { padding-right: 42px !important; }
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
    </style>
</head>
<body class="bg-[#f7f7f5] font-sans antialiased overflow-hidden min-h-screen">
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
                    <li class="mobile-only" style="display:none;">
                        <a class="sidebar-nav-link active-menu" href="profile.php">
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
                    <h1 class="text-2xl font-semibold text-gray-800 tracking-tight">MY PROFILE</h1>
                </div>

                <!-- Profile Fields Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden w-full">

                    <!-- Username Field -->
                    <div class="flex items-center gap-5 px-6 py-5 border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fa fa-user text-white text-lg"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Username</p>
                            <p class="text-base font-medium text-gray-800" id="username">Loading...</p>
                        </div>
                    </div>

                    <!-- Email Field -->
                    <div class="flex items-center gap-5 px-6 py-5 border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fa fa-envelope text-white text-lg"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Email Address</p>
                            <p class="text-base font-medium text-gray-800 truncate" id="email">Loading...</p>
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="flex items-center gap-5 px-6 py-5 hover:bg-gray-50 transition-colors">
                        <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fa fa-lock text-white text-lg"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Password</p>
                            <p class="text-base font-medium text-gray-800 tracking-widest">••••••••••</p>
                        </div>
                        <div class="flex-shrink-0">
                            <button onclick="openUpdateModal('password')"
                                class="inline-flex items-center gap-1.5 text-sm font-semibold bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-all hover:-translate-y-0.5 hover:shadow-md active:translate-y-0">
                                <i class="fa fa-edit"></i> Update
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        </main>
        <!-- /. PAGE WRAPPER -->

    </div>
    <!-- /. WRAPPER -->

    <!-- Update Profile Modal -->
    <div id="updateModal" class="modal-overlay">
        <div class="relative bg-white rounded-2xl w-[90%] max-w-lg shadow-2xl modal-animate mx-auto my-auto" style="z-index:1; max-height:90vh; overflow-y:auto;">

            <!-- Modal Header -->
            <div class="px-7 py-5 border-b border-gray-100 flex items-center justify-between">
                <h3 id="modalTitle" class="text-lg font-semibold text-gray-900">Update Information</h3>
                <button onclick="closeUpdateModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 transition-colors">&times;</button>
            </div>

            <!-- Modal Body -->
            <div class="px-7 py-6">
                <form id="updateForm">
                    <!-- Password Update Form -->
                    <div id="passwordForm" class="update-form space-y-5" style="display: none;">

                        <div class="form-group-update">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Current Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" id="currentPassword" placeholder="Enter your current password"
                                class="pwd-input w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100 transition-all" required>
                            <i class="fa fa-eye-slash toggle-password" data-target="currentPassword"></i>
                        </div>

                        <div class="form-group-update">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                New Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" id="newPassword" placeholder="Enter new password (min. 8 characters)"
                                class="pwd-input w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100 transition-all" required>
                            <i class="fa fa-eye-slash toggle-password" data-target="newPassword"></i>
                        </div>

                        <div class="form-group-update">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Confirm New Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" id="confirmPassword" placeholder="Re-enter new password"
                                class="pwd-input w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100 transition-all" required>
                            <i class="fa fa-eye-slash toggle-password" data-target="confirmPassword"></i>
                        </div>

                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="px-7 py-4 bg-gray-50 rounded-b-2xl border-t border-gray-100 flex justify-end gap-3">
                <button onclick="closeUpdateModal()"
                    class="px-5 py-2.5 text-sm font-semibold bg-white hover:bg-gray-100 text-gray-700 rounded-xl border border-gray-200 transition-colors">
                    Cancel
                </button>
                <button onclick="saveUpdate()"
                    class="btn-save px-5 py-2.5 text-sm font-semibold bg-green-600 hover:bg-green-700 text-white rounded-xl transition-all hover:-translate-y-0.5 hover:shadow-md active:translate-y-0">
                    Update
                </button>
            </div>

        </div>
    </div>

    <!-- JS Scripts -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/tailwind-selects.js"></script>
    <script>
        var currentUpdateField = null;
        var supabase = null;
        var currentUser = null;
        var profileData = {
            username: '',
            email: ''
        };

        // Initialize Supabase and load user data
        document.addEventListener('DOMContentLoaded', async function() {
            // Initialize Supabase
            const { createClient } = window.supabase || {};
            if (!createClient) {
                console.error('Supabase library failed to load.');
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
            await loadUserProfile();
        });

        // Load user profile data from Supabase
        async function loadUserProfile() {
            if (!currentUser) return;

            // Get username from user_metadata
            const username = currentUser.user_metadata?.username || currentUser.email?.split('@')[0] || 'Admin';
            const email = currentUser.email || 'No email';

            // Update UI
            document.getElementById('username').textContent = username;
            document.getElementById('email').textContent = email;

            // Store in profileData
            profileData = {
                username: username,
                email: email
            };
        }

        function openUpdateModal(field) {
            currentUpdateField = field;

            // Hide all forms
            $('.update-form').hide();

            // Show the appropriate form and update modal title
            if (field === 'password') {
                $('#passwordForm').show();
                $('#modalTitle').text('Update Password');
            }

            // Show modal
            $('#updateModal').addClass('active');
            $('body').css('overflow', 'hidden');
        }

        function closeUpdateModal() {
            $('#updateModal').removeClass('active');
            $('body').css('overflow', '');

            // Reset form
            $('#updateForm')[0].reset();
            currentUpdateField = null;
        }

        // Helper function to handle logout after password update for security
        async function logoutAfterPasswordUpdate() {
            // Update UI first
            closeUpdateModal();

            // Show security notification
            alert('Password updated successfully!\n\nFor security purposes, the system will log you out. Please log in again with your new password.');

            // Log out from Supabase
            try {
                await supabase.auth.signOut();
                console.log('User logged out after password update');
            } catch (logoutError) {
                console.error('Logout error:', logoutError);
            }

            // Redirect to login page
            setTimeout(() => {
                window.location.href = '../login.php';
            }, 500);
        }

        async function saveUpdate() {
            if (!currentUpdateField || !supabase || !currentUser) return;

            if (currentUpdateField === 'password') {
                var currentPassword = $('#currentPassword').val();
                var newPassword = $('#newPassword').val();
                var confirmPassword = $('#confirmPassword').val();

                if (!currentPassword || !newPassword || !confirmPassword) {
                    alert('Please fill in all fields.');
                    return;
                }

                // Check if new password is the same as current password
                if (currentPassword === newPassword) {
                    alert('New password must be different from your current password.');
                    return;
                }

                if (newPassword !== confirmPassword) {
                    alert('New passwords do not match. Please try again.');
                    return;
                }

                if (newPassword.length < 8) {
                    alert('Password must be at least 8 characters long.');
                    return;
                }

                // Validate password strength
                if (!/[A-Z]/.test(newPassword) || !/[0-9]/.test(newPassword) || !/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(newPassword)) {
                    alert('Password must contain at least 1 uppercase letter, 1 number, and 1 symbol.');
                    return;
                }

                try {
                    // Show loading state
                    var saveBtn = $('.btn-save');
                    var originalBtnText = saveBtn.html();
                    saveBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');

                    // First, verify the current password by attempting to sign in
                    console.log('Verifying current password...');
                    const { data: verifyData, error: verifyError } = await supabase.auth.signInWithPassword({
                        email: currentUser.email,
                        password: currentPassword
                    });

                    if (verifyError) {
                        console.error('Current password verification failed:', verifyError);
                        saveBtn.prop('disabled', false).html(originalBtnText);

                        if (verifyError.message && (
                            verifyError.message.includes('Invalid') ||
                            verifyError.message.includes('invalid') ||
                            verifyError.message.includes('credentials') ||
                            verifyError.message.includes('password')
                        )) {
                            throw new Error('Current password is incorrect. Please try again.');
                        }
                        throw verifyError;
                    }

                    console.log('Current password verified successfully');

                    // Now update to the new password
                    console.log('Updating password...');
                    const { data: updateData, error: updateError } = await supabase.auth.updateUser({
                        password: newPassword
                    });

                    if (updateError) {
                        console.error('Password update error:', updateError);
                        saveBtn.prop('disabled', false).html(originalBtnText);

                        if (updateError.message && updateError.message.includes('same')) {
                            throw new Error('New password must be different from your current password.');
                        }
                        throw updateError;
                    }

                    console.log('Password updated successfully');

                    // Reset button
                    saveBtn.prop('disabled', false).html(originalBtnText);

                    // Log out for security purposes
                    await logoutAfterPasswordUpdate();

                } catch (error) {
                    console.error('Password update error:', error);

                    // Reset button
                    var saveBtn = $('.btn-save');
                    if (saveBtn.prop('disabled')) {
                        saveBtn.prop('disabled', false).html('Update');
                    }

                    let errorMessage = 'Failed to update password. ';
                    if (error.message) {
                        if (error.message.includes('incorrect') || error.message.includes('Invalid')) {
                            errorMessage = error.message;
                        } else if (error.message.includes('same')) {
                            errorMessage = 'New password must be different from your current password.';
                        } else {
                            errorMessage += error.message;
                        }
                    } else {
                        errorMessage += 'Please try again.';
                    }
                    alert(errorMessage);
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
        $(document).on('click', '#updateModal', function(e) {
            if (e.target.id === 'updateModal') {
                closeUpdateModal();
            }
        });

        // Close modal with Escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#updateModal').hasClass('active')) {
                closeUpdateModal();
            }
        });

        // Toggle password visibility
        $(document).on('click', '.toggle-password', function() {
            var targetId = $(this).attr('data-target');
            var passwordInput = $('#' + targetId);

            if (passwordInput.attr('type') === 'password') {
                passwordInput.attr('type', 'text');
                $(this).removeClass('fa-eye-slash').addClass('fa-eye');
            } else {
                passwordInput.attr('type', 'password');
                $(this).removeClass('fa-eye').addClass('fa-eye-slash');
            }
        });

    </script>
</body>
</html>






