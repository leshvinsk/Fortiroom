<?php
// Check and cancel deletion request if user logs in (endpoint to be called on login)
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

// Check if user has an in_progress deletion request
$checkUrl = $SUPABASE_URL . '/rest/v1/account_deletion_requests?user_id=eq.' . urlencode($user_id) . '&status=eq.in_progress&select=id,status,deletion_scheduled_at&limit=1';
$ch = curl_init($checkUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . $SUPABASE_SERVICE_KEY,
    'Authorization: Bearer ' . $SUPABASE_SERVICE_KEY,
    'Content-Type: application/json'
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
    $requests = json_decode($response, true);
    if (!empty($requests) && is_array($requests)) {
        $request = $requests[0];
        $request_id = $request['id'];
        
        // Cancel the deletion request
        $updateUrl = $SUPABASE_URL . '/rest/v1/account_deletion_requests?id=eq.' . urlencode($request_id);
        $updateData = json_encode([
            'status' => 'cancelled',
            'updated_at' => date('c')
        ]);
        
        $ch = curl_init($updateUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $updateData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . $SUPABASE_SERVICE_KEY,
            'Authorization: Bearer ' . $SUPABASE_SERVICE_KEY,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $updateResponse = curl_exec($ch);
        $updateHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $updateCurlError = curl_error($ch);
        curl_close($ch);
        
        if (!$updateCurlError && $updateHttpCode >= 200 && $updateHttpCode < 300) {
            echo json_encode([
                'success' => true,
                'cancelled' => true,
                'message' => 'Account deletion request has been cancelled. Your account is safe.',
                'request_id' => $request_id
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'error' => 'Failed to cancel deletion request',
                'cancelled' => false
            ]);
        }
    } else {
        // No in-progress deletion request found
        echo json_encode([
            'success' => true,
            'cancelled' => false,
            'message' => 'No active deletion request found'
        ]);
    }
} else {
    http_response_code($httpCode);
    $errorData = json_decode($response, true);
    echo json_encode([
        'error' => 'Failed to check deletion request',
        'details' => $errorData ?? $response
    ]);
}

