<?php
// Check email uniqueness against Supabase Auth users
// Uses service key to query users by email

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

// Get email from POST data
$input = json_decode(file_get_contents('php://input'), true);
$email = isset($input['email']) ? trim(strtolower($input['email'])) : '';

if (empty($email)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email is required', 'available' => false]);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format', 'available' => false]);
    exit;
}

// Query Supabase Auth Admin API to check if email exists
// Use the admin API to get user by email
$url = rtrim($SUPABASE_URL, '/') . '/auth/v1/admin/users?email=' . urlencode($email);
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

// If we get a 200, check if user exists in response
// If 404 or empty result, email is available
$emailExists = false;

if ($httpCode === 200) {
    $users = json_decode($response, true);
    
    // Handle different response formats
    $userList = [];
    if (is_array($users)) {
        if (isset($users[0]) && isset($users[0]['id'])) {
            $userList = $users;
        } elseif (isset($users['users']) && is_array($users['users'])) {
            $userList = $users['users'];
        }
    }
    
    // Check if any user has this email (case-insensitive)
    foreach ($userList as $user) {
        $userEmail = isset($user['email']) ? strtolower(trim($user['email'])) : '';
        if ($userEmail === $email) {
            $emailExists = true;
            break;
        }
    }
} elseif ($httpCode === 404) {
    // 404 means no user found, email is available
    $emailExists = false;
} else {
    // Other errors - assume email might exist to be safe
    http_response_code(500);
    echo json_encode(['error' => 'Failed to check email', 'available' => false]);
    exit;
}

echo json_encode([
    'available' => !$emailExists,
    'email' => $email
]);
exit;
?>

