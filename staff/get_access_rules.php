<?php
// Load access rules endpoint
// Uses service key to fetch the latest active access rules row from Supabase

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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!$SUPABASE_URL || !$SUPABASE_SERVICE_KEY) {
    http_response_code(500);
    echo json_encode(['error' => 'Server not configured']);
    exit;
}

$url = rtrim($SUPABASE_URL, '/') . '/rest/v1/access_rules'
    . '?select=late_cancellation_threshold_hours,face_verification_mode,operation_time,peak_hours,peak_min_duration_mins,peak_max_duration_mins,blackout_dates,check_in_grace_period_mins,block_entry_if_outstanding_penalties,block_secondary_user_if_has_penalty,updated_at'
    . '&order=updated_at.desc'
    . '&limit=1';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'apikey: ' . $SUPABASE_SERVICE_KEY,
    'Authorization: Bearer ' . $SUPABASE_SERVICE_KEY
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    http_response_code(500);
    echo json_encode(['error' => 'cURL error loading access rules: ' . $curlError]);
    exit;
}

if ($httpCode !== 200) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load access rules', 'http_code' => $httpCode, 'detail' => $response]);
    exit;
}

$decoded = json_decode((string)$response, true);
if (!is_array($decoded)) {
    http_response_code(500);
    echo json_encode(['error' => 'Unexpected response while loading access rules']);
    exit;
}

$record = count($decoded) > 0 ? $decoded[0] : null;
echo json_encode(['success' => true, 'data' => $record]);
