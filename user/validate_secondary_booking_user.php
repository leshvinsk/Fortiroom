<?php
// Validate a secondary booking username against auth users and optional penalty rules.

$envPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
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
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

if (!$SUPABASE_URL || !$SUPABASE_SERVICE_KEY) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server not configured']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$username = trim((string)($input['username'] ?? ''));
$currentUserId = trim((string)($input['current_user_id'] ?? ''));
$enforcePenaltyBlock = !empty($input['enforce_penalty_block']);

if ($username === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'valid' => false, 'error' => 'Username is required']);
    exit;
}

$usersUrl = rtrim($SUPABASE_URL, '/') . '/auth/v1/admin/users';
$ch = curl_init($usersUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . $SUPABASE_SERVICE_KEY,
    'Authorization: Bearer ' . $SUPABASE_SERVICE_KEY,
    'Content-Type: application/json',
]);
$usersResponse = curl_exec($ch);
$usersHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$usersCurlError = curl_error($ch);
curl_close($ch);

if ($usersCurlError || $usersHttpCode !== 200) {
    http_response_code(500);
    echo json_encode(['success' => false, 'valid' => false, 'error' => 'Unable to validate username']);
    exit;
}

$decoded = json_decode((string)$usersResponse, true);
$userList = [];
if (is_array($decoded)) {
    if (isset($decoded['users']) && is_array($decoded['users'])) {
        $userList = $decoded['users'];
    } elseif (isset($decoded[0]) && is_array($decoded[0])) {
        $userList = $decoded;
    }
}

$matchedUser = null;
foreach ($userList as $user) {
    $metadata = $user['user_metadata'] ?? [];
    $existingUsername = trim((string)($metadata['username'] ?? ''));
    if (strcasecmp($existingUsername, $username) === 0) {
        $matchedUser = $user;
        break;
    }
}

if (!$matchedUser) {
    echo json_encode(['success' => true, 'valid' => false, 'error' => 'Cannot add this username. Username is not registered.']);
    exit;
}

$matchedUserId = trim((string)($matchedUser['id'] ?? ''));
if ($matchedUserId !== '' && $currentUserId !== '' && $matchedUserId === $currentUserId) {
    echo json_encode(['success' => true, 'valid' => false, 'error' => 'Cannot add this username. Please use a different registered user.']);
    exit;
}

if ($enforcePenaltyBlock && $matchedUserId !== '') {
    $penaltyUrl = rtrim($SUPABASE_URL, '/') . '/rest/v1/penalties?select=id&user_id=eq.' . rawurlencode($matchedUserId) . '&status=eq.pending&limit=1';
    $ch = curl_init($penaltyUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $SUPABASE_SERVICE_KEY,
        'Authorization: Bearer ' . $SUPABASE_SERVICE_KEY,
        'Content-Type: application/json',
    ]);
    $penaltyResponse = curl_exec($ch);
    $penaltyHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $penaltyCurlError = curl_error($ch);
    curl_close($ch);

    if ($penaltyCurlError || $penaltyHttpCode !== 200) {
        http_response_code(500);
        echo json_encode(['success' => false, 'valid' => false, 'error' => 'Unable to verify pending penalties for this username.']);
        exit;
    }

    $penalties = json_decode((string)$penaltyResponse, true);
    if (is_array($penalties) && count($penalties) > 0) {
        echo json_encode(['success' => true, 'valid' => false, 'error' => 'Cannot add this username. The user has pending penalties.']);
        exit;
    }
}

echo json_encode([
    'success' => true,
    'valid' => true,
    'user_id' => $matchedUserId,
    'username' => $username
]);
