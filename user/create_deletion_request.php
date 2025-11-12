<?php
// Create account deletion request endpoint
header('Content-Type: application/json');

// Load environment variables
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

if (!$SUPABASE_URL || !$SUPABASE_SERVICE_KEY) {
    http_response_code(500);
    echo json_encode(['error' => 'Server configuration error: Missing Supabase credentials']);
    exit;
}

// Get user ID from request
$input = json_decode(file_get_contents('php://input'), true);
$user_id = $input['user_id'] ?? null;

if (!$user_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing user_id']);
    exit;
}

// Check if user already has a pending or in_progress deletion request
$checkUrl = $SUPABASE_URL . '/rest/v1/account_deletion_requests?user_id=eq.' . urlencode($user_id) . '&status=in.(pending,approved,in_progress)&select=id,status&limit=1';
$ch = curl_init($checkUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . $SUPABASE_SERVICE_KEY,
    'Authorization: Bearer ' . $SUPABASE_SERVICE_KEY,
    'Content-Type: application/json',
    'Prefer: return=representation'
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection error: ' . $curlError]);
    exit;
}

if ($httpCode === 200) {
    $existingRequests = json_decode($response, true);
    if (!empty($existingRequests) && is_array($existingRequests)) {
        http_response_code(409);
        echo json_encode([
            'error' => 'User already has a pending deletion request',
            'existing_request' => $existingRequests[0]
        ]);
        exit;
    }
}

// Create new deletion request
$requestUrl = $SUPABASE_URL . '/rest/v1/account_deletion_requests';
$payload = json_encode([
    'user_id' => $user_id,
    'status' => 'pending',
    'requested_at' => date('c') // ISO 8601 format
]);

$ch = curl_init($requestUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . $SUPABASE_SERVICE_KEY,
    'Authorization: Bearer ' . $SUPABASE_SERVICE_KEY,
    'Content-Type: application/json',
    'Prefer: return=representation'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create deletion request: ' . $curlError]);
    exit;
}

if ($httpCode >= 200 && $httpCode < 300) {
    $data = json_decode($response, true);
    if (is_array($data) && !empty($data)) {
        echo json_encode([
            'success' => true,
            'request' => $data[0]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Deletion request created successfully'
        ]);
    }
} else {
    http_response_code($httpCode);
    $errorData = json_decode($response, true);
    echo json_encode([
        'error' => 'Failed to create deletion request',
        'details' => $errorData ?? $response
    ]);
}

