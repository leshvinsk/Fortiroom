<?php
// Check username uniqueness against Supabase Auth users
// Uses service key to query user metadata

// Load env
$envPath = __DIR__ . DIRECTORY_SEPARATOR . '.env';
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
$SUPABASE_SERVICE_KEY = $_ENV['SUPABASE_SERVICE_KEY'] ?? '';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!$SUPABASE_URL || !$SUPABASE_SERVICE_KEY) {
    http_response_code(500);
    echo json_encode(['error' => 'Server not configured']);
    exit;
}

// Get username from POST data
$input = json_decode(file_get_contents('php://input'), true);
$username = isset($input['username']) ? trim($input['username']) : '';

if (empty($username)) {
    http_response_code(400);
    echo json_encode(['error' => 'Username is required', 'available' => false]);
    exit;
}

// Query Supabase Auth Admin API to check if username exists
// We'll use the REST API to list users and check their metadata
$url = rtrim($SUPABASE_URL, '/') . '/auth/v1/admin/users';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . $SUPABASE_SERVICE_KEY,
    'Authorization: Bearer ' . $SUPABASE_SERVICE_KEY,
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to check username', 'available' => false]);
    exit;
}

$users = json_decode($response, true);
$usernameExists = false;

// Check if any user has this username in their metadata
// Supabase Admin API returns users in different formats depending on version
// Handle both array format and object with 'users' key
$userList = [];
if (is_array($users)) {
    // Check if it's a direct array of users
    if (isset($users[0]) && isset($users[0]['id'])) {
        $userList = $users;
    } elseif (isset($users['users']) && is_array($users['users'])) {
        $userList = $users['users'];
    }
}

foreach ($userList as $user) {
    $userMetadata = $user['user_metadata'] ?? [];
    $existingUsername = $userMetadata['username'] ?? '';
    
    // Case-insensitive comparison
    if (strtolower(trim($existingUsername)) === strtolower($username)) {
        $usernameExists = true;
        break;
    }
}

echo json_encode([
    'available' => !$usernameExists,
    'username' => $username
]);
exit;
?>

