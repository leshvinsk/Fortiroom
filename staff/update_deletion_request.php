<?php
// Update account deletion request endpoint (for admin/staff)
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

// Get request data
$input = json_decode(file_get_contents('php://input'), true);
$request_id = $input['request_id'] ?? null;
$status = $input['status'] ?? null;
$reviewed_by = $input['reviewed_by'] ?? null;
$admin_notes = $input['admin_notes'] ?? null;

if (!$request_id || !$status) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields: request_id and status']);
    exit;
}

// Validate status
$validStatuses = ['approved', 'declined', 'in_progress', 'cancelled', 'completed'];
if (!in_array($status, $validStatuses)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid status. Must be one of: ' . implode(', ', $validStatuses)]);
    exit;
}

// Prepare update data
$updateData = [
    'status' => $status,
    'reviewed_at' => date('c'),
    'updated_at' => date('c')
];

if ($reviewed_by) {
    $updateData['reviewed_by'] = $reviewed_by;
}

if ($admin_notes !== null) {
    $updateData['admin_notes'] = $admin_notes;
}

// If status is 'approved', set deletion_started_at and deletion_scheduled_at (30 days from now)
if ($status === 'approved') {
    $updateData['deletion_started_at'] = date('c');
    $deletionDate = new DateTime();
    $deletionDate->modify('+30 days');
    $updateData['deletion_scheduled_at'] = $deletionDate->format('c');
    // Also set status to 'in_progress' since deletion process starts immediately after approval
    $updateData['status'] = 'in_progress';
}

// Update deletion request
$updateUrl = $SUPABASE_URL . '/rest/v1/account_deletion_requests?id=eq.' . urlencode($request_id);
$payload = json_encode($updateData);

$ch = curl_init($updateUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
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
    echo json_encode(['error' => 'Failed to update deletion request: ' . $curlError]);
    exit;
}

if ($httpCode >= 200 && $httpCode < 300) {
    $data = json_decode($response, true);
    echo json_encode([
        'success' => true,
        'request' => is_array($data) && !empty($data) ? $data[0] : null,
        'message' => 'Deletion request updated successfully'
    ]);
} else {
    http_response_code($httpCode);
    $errorData = json_decode($response, true);
    echo json_encode([
        'error' => 'Failed to update deletion request',
        'details' => $errorData ?? $response
    ]);
}

