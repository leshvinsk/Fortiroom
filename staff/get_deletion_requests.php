<?php
// Get account deletion requests endpoint (for admin/staff)
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

// Fetch all deletion requests using service key (bypasses RLS)
$requestUrl = $SUPABASE_URL . '/rest/v1/account_deletion_requests?select=id,user_id,status,requested_at,reviewed_at,deletion_started_at,deletion_scheduled_at,reason,admin_notes&order=requested_at.desc';

$ch = curl_init($requestUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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
    echo json_encode(['error' => 'Database connection error: ' . $curlError]);
    exit;
}

if ($httpCode >= 200 && $httpCode < 300) {
    // Parse response
    $requests = json_decode($response, true);
    
    // Check for JSON parsing errors
    if ($requests === null && json_last_error() !== JSON_ERROR_NONE) {
        error_log('Failed to parse Supabase response: ' . json_last_error_msg());
        error_log('Response: ' . substr($response, 0, 500));
        http_response_code(500);
        echo json_encode(['error' => 'Failed to parse response: ' . json_last_error_msg()]);
        exit;
    }
    
    // Ensure requests is always an array
    // Supabase returns an array directly, not wrapped in an object
    if (!is_array($requests)) {
        // If it's not an array, it might be null (empty result) or an object
        if ($requests === null) {
            $requests = [];
        } else if (is_object($requests)) {
            // If it's an object, try to extract array from it
            $requests = (array)$requests;
            // If still not array after conversion, set to empty array
            if (!is_array($requests)) {
                error_log('Response is object but cannot convert to array. Type: ' . gettype($requests));
                $requests = [];
            }
        } else {
            error_log('Supabase response is not an array. Type: ' . gettype($requests));
            error_log('Response: ' . substr($response, 0, 500));
            $requests = [];
        }
    }
    
    // Final safety check - ensure it's an array
    if (!is_array($requests)) {
        $requests = [];
    }
    
    echo json_encode([
        'success' => true,
        'requests' => $requests
    ]);
} else {
    // Handle errors (table doesn't exist, permission denied, etc.)
    $errorData = json_decode($response, true);
    error_log('Supabase API error. HTTP Code: ' . $httpCode);
    error_log('Response: ' . substr($response, 0, 500));
    
    // Check if table doesn't exist
    $errorMessage = is_array($errorData) ? ($errorData['message'] ?? $errorData['error'] ?? $response) : $response;
    if (stripos($errorMessage, 'does not exist') !== false || stripos($errorMessage, 'relation') !== false) {
        http_response_code(404);
        echo json_encode([
            'error' => 'Table does not exist',
            'message' => 'The account_deletion_requests table has not been created yet. Please run create_account_deletion_requests_table.sql in Supabase SQL Editor.',
            'http_code' => $httpCode
        ]);
    } else {
        http_response_code($httpCode);
        echo json_encode([
            'error' => 'Failed to fetch deletion requests',
            'details' => $errorData ?? $response,
            'http_code' => $httpCode
        ]);
    }
}

