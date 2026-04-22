<?php
// Update access rules endpoint
// Uses service key to upsert a single active access rules record in Supabase

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

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request payload']);
    exit;
}

$lateCancellationThresholdHours = isset($input['late_cancellation_threshold_hours']) ? intval($input['late_cancellation_threshold_hours']) : null;
$faceVerificationMode = isset($input['face_verification_mode']) ? trim((string)$input['face_verification_mode']) : 'single';
$operationTime = isset($input['operation_time']) ? trim((string)$input['operation_time']) : '';
$peakHours = isset($input['peak_hours']) ? trim((string)$input['peak_hours']) : '';
$peakMinDurationMins = isset($input['peak_min_duration_mins']) ? intval($input['peak_min_duration_mins']) : null;
$peakMaxDurationMins = isset($input['peak_max_duration_mins']) ? intval($input['peak_max_duration_mins']) : null;
$checkInGracePeriodMins = isset($input['check_in_grace_period_mins']) ? intval($input['check_in_grace_period_mins']) : null;
$blockEntryIfOutstandingPenalties = !empty($input['block_entry_if_outstanding_penalties']);
$blockSecondaryUserIfHasPenalty = !empty($input['block_secondary_user_if_has_penalty']);
$blackoutDates = isset($input['blackout_dates']) && is_array($input['blackout_dates']) ? array_values(array_filter(array_map('trim', $input['blackout_dates']), function ($v) {
    return $v !== '';
})) : [];
$userId = isset($input['user_id']) ? trim((string)$input['user_id']) : null;

if ($lateCancellationThresholdHours === null || $lateCancellationThresholdHours < 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid late cancellation threshold']);
    exit;
}

if (!in_array($faceVerificationMode, ['single', 'dual'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid face verification mode']);
    exit;
}

if (!preg_match('/^\d{2}:\d{2}\s*-\s*\d{2}:\d{2}$/', $operationTime)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid operation time format']);
    exit;
}

if (!preg_match('/^\d{2}:\d{2}\s*-\s*\d{2}:\d{2}$/', $peakHours)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid peak hours format']);
    exit;
}

$intFields = [
    'peak minimum duration' => $peakMinDurationMins,
    'peak maximum duration' => $peakMaxDurationMins,
    'check-in grace period' => $checkInGracePeriodMins
];

foreach ($intFields as $label => $value) {
    if ($value === null || $value < 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid ' . $label]);
        exit;
    }
}

if ($peakMinDurationMins > $peakMaxDurationMins) {
    http_response_code(400);
    echo json_encode(['error' => 'Peak minimum duration cannot exceed peak maximum duration']);
    exit;
}

$baseUrl = rtrim($SUPABASE_URL, '/') . '/rest/v1/access_rules';
$commonHeaders = [
    'Content-Type: application/json',
    'apikey: ' . $SUPABASE_SERVICE_KEY,
    'Authorization: Bearer ' . $SUPABASE_SERVICE_KEY,
    'Prefer: return=representation'
];

$payload = [
    'id' => 1,
    'late_cancellation_threshold_hours' => $lateCancellationThresholdHours,
    'face_verification_mode' => $faceVerificationMode,
    'operation_time' => $operationTime,
    'peak_hours' => $peakHours,
    'peak_min_duration_mins' => $peakMinDurationMins,
    'peak_max_duration_mins' => $peakMaxDurationMins,
    'blackout_dates' => $blackoutDates,
    'check_in_grace_period_mins' => $checkInGracePeriodMins,
    'block_entry_if_outstanding_penalties' => $blockEntryIfOutstandingPenalties,
    'block_secondary_user_if_has_penalty' => $blockSecondaryUserIfHasPenalty,
    'updated_at' => date('c')
];

if ($userId) {
    $payload['updated_by'] = $userId;
}

$url = $baseUrl . '?id=eq.1';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, $commonHeaders);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    http_response_code(500);
    echo json_encode(['error' => 'cURL error updating access rules: ' . $curlError]);
    exit;
}

$decoded = json_decode((string)$response, true);
if (($httpCode === 200 || $httpCode === 204) && !empty($decoded)) {
    echo json_encode(['success' => true, 'message' => 'Access rules updated successfully', 'data' => $decoded]);
    exit;
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, $commonHeaders);
$insertResponse = curl_exec($ch);
$insertHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$insertCurlError = curl_error($ch);
curl_close($ch);

if ($insertCurlError) {
    http_response_code(500);
    echo json_encode(['error' => 'cURL error creating access rules: ' . $insertCurlError]);
    exit;
}

if ($insertHttpCode === 200 || $insertHttpCode === 201) {
    echo json_encode(['success' => true, 'message' => 'Access rules updated successfully', 'data' => json_decode((string)$insertResponse, true)]);
    exit;
}

http_response_code(500);
echo json_encode([
    'error' => 'Failed to update access rules',
    'http_code' => $insertHttpCode,
    'detail' => $insertResponse
]);
