<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function loadEnvFile(string $path): void
{
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) return;
    foreach ($lines as $line) {
        if (strpos(ltrim($line), '#') === 0 || strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim(trim($value), "\"'");
    }
}

function supabaseRequest(string $method, string $pathAndQuery, ?array $payload = null): array
{
    $baseUrl = rtrim((string) ($_ENV['SUPABASE_URL'] ?? ''), '/');
    $serviceKey = (string) ($_ENV['SUPABASE_SERVICE_KEY'] ?? '');
    if ($baseUrl === '' || $serviceKey === '') {
        return [500, '', 'Supabase environment variables are missing'];
    }

    $ch = curl_init($baseUrl . '/rest/v1/' . ltrim($pathAndQuery, '/'));
    $headers = [
        'Content-Type: application/json',
        'apikey: ' . $serviceKey,
        'Authorization: Bearer ' . $serviceKey,
    ];
    if ($method === 'POST') {
        $headers[] = 'Prefer: return=representation';
    }
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 20,
    ]);
    if ($payload !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    }
    $body = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    return [$httpCode, is_string($body) ? $body : '', $error];
}

function isUuid(?string $value): bool
{
    return is_string($value) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value) === 1;
}

function getPenaltyRate(string $violationType, float $fallback): array
{
    [$code, $body] = supabaseRequest('GET', 'penalty_rates?violation_type=eq.' . rawurlencode($violationType) . '&select=id,penalty_amount&limit=1');
    if ($code === 200) {
        $data = json_decode($body, true);
        if (is_array($data) && isset($data[0])) {
            return [
                'rate_id' => isUuid($data[0]['id'] ?? null) ? $data[0]['id'] : null,
                'amount' => isset($data[0]['penalty_amount']) ? (float) $data[0]['penalty_amount'] : $fallback,
            ];
        }
    }
    return ['rate_id' => null, 'amount' => $fallback];
}

function penaltyExists(string $userId, string $bookingId, string $violationType): bool
{
    $query = 'penalties?user_id=eq.' . rawurlencode($userId)
        . '&booking_id=eq.' . rawurlencode($bookingId)
        . '&violation_type=eq.' . rawurlencode($violationType)
        . '&select=id&limit=1';
    [$code, $body] = supabaseRequest('GET', $query);
    if ($code !== 200) return false;
    $data = json_decode($body, true);
    return is_array($data) && count($data) > 0;
}

function createPenalty(string $userId, string $bookingId, ?string $podId, string $violationType, array $rate): array
{
    if (penaltyExists($userId, $bookingId, $violationType)) {
        return ['skipped' => true, 'reason' => 'Penalty already exists', 'user_id' => $userId];
    }

    $payload = [
        'user_id' => $userId,
        'booking_id' => $bookingId,
        'violation_type' => $violationType,
        'penalty_amount' => $rate['amount'],
        'violation_date' => gmdate('Y-m-d'),
        'violation_time' => gmdate('H:i:s'),
        'status' => 'pending',
    ];
    if (isUuid($podId)) {
        $payload['pod_id'] = $podId;
    }
    if (isUuid($rate['rate_id'] ?? null)) {
        $payload['rate_id'] = $rate['rate_id'];
    }

    [$code, $body, $error] = supabaseRequest('POST', 'penalties', $payload);
    $decoded = json_decode($body, true);
    return [
        'success' => $code >= 200 && $code < 300,
        'status_code' => $code,
        'transport_error' => $error,
        'response' => $decoded ?? $body,
        'user_id' => $userId,
    ];
}

loadEnvFile(__DIR__ . DIRECTORY_SEPARATOR . '.env');

$input = json_decode(file_get_contents('php://input') ?: '{}', true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON payload']);
    exit;
}

$bookingId = trim((string) ($input['booking_id'] ?? ''));
$podId = trim((string) ($input['pod_id'] ?? ''));
$primaryUserId = trim((string) ($input['primary_user_id'] ?? ''));
$secondaryUserId = trim((string) ($input['secondary_user_id'] ?? ''));

if (!isUuid($bookingId) || !isUuid($primaryUserId)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing or invalid booking_id / primary_user_id']);
    exit;
}

$violationType = 'Late Checkout';
$rate = getPenaltyRate($violationType, 20.00);
$results = [];
$results[] = createPenalty($primaryUserId, $bookingId, $podId, $violationType, $rate);
if (isUuid($secondaryUserId) && strtolower($secondaryUserId) !== strtolower($primaryUserId)) {
    $results[] = createPenalty($secondaryUserId, $bookingId, $podId, $violationType, $rate);
}

$allOk = true;
foreach ($results as $result) {
    if (empty($result['success']) && empty($result['skipped'])) {
        $allOk = false;
    }
}

if (!$allOk) {
    http_response_code(502);
}

echo json_encode([
    'ok' => $allOk,
    'violation_type' => $violationType,
    'rate' => $rate,
    'results' => $results,
    'timestamp_utc' => gmdate('c'),
]);
