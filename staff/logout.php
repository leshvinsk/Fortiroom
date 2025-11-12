<?php
session_start();
// Clear any PHP session data you might use elsewhere
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
// Signal the front-end to sign out from Supabase and show the login page
header("Location: ../login.php?logout=1");
exit;
?>