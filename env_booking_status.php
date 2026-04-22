<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'esp32_cam_registry.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function envBookingLoadEnv(string $path): array
{
    $env = [];
    if (!is_file($path)) {
        return $env;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return $env;
    }

    foreach ($lines as $line) {
        if (strpos(ltrim($line), '#') === 0) {
            continue;
        }

        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);
        $len = strlen($value);
        if ($len >= 2 && (($value[0] === '"' && $value[$len - 1] === '"') || ($value[0] === "'" && $value[$len - 1] === "'"))) {
            $value = substr($value, 1, $len - 2);
        }

        $env[$key] = $value;
        $_ENV[$key] = $value;
    }

    return $env;
}

function envBookingJsonResponse(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function envBookingHttpRequest(string $url, array $options = []): array
{
    if (!function_exists('curl_init')) {
        return [
            'ok' => false,
            'status' => 0,
            'error' => 'cURL is required on the server',
            'body' => '',
        ];
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $options['method'] ?? 'GET');
    curl_setopt($ch, CURLOPT_TIMEOUT, $options['timeout'] ?? 20);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    if (!empty($options['headers'])) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $options['headers']);
    }

    if (array_key_exists('body', $options)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $options['body']);
    }

    $body = curl_exec($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'ok' => is_string($body) && $status >= 200 && $status < 300,
        'status' => $status,
        'error' => $error ?: '',
        'body' => is_string($body) ? $body : '',
    ];
}

function envBookingMinutesFromTime(string $timeValue): ?int
{
    $timeValue = trim($timeValue);
    if (!preg_match('/^(\d{2}):(\d{2})/', $timeValue, $matches)) {
        return null;
    }

    return ((int) $matches[1] * 60) + (int) $matches[2];
}

function envBookingResolveCreatorUsername(string $supabaseUrl, string $serviceKey, string $userId): ?string
{
    $userId = trim($userId);
    if ($userId === '') {
        return null;
    }

    $url = rtrim($supabaseUrl, '/') . '/auth/v1/admin/users/' . rawurlencode($userId);
    $response = envBookingHttpRequest($url, [
        'timeout' => 15,
        'headers' => [
            'Authorization: Bearer ' . $serviceKey,
            'apikey: ' . $serviceKey,
        ],
    ]);

    if (!$response['ok']) {
        return null;
    }

    $payload = json_decode($response['body'], true);
    if (!is_array($payload)) {
        return null;
    }

    $username = trim((string) (($payload['user_metadata']['username'] ?? '') ?: ($payload['email'] ?? '')));
    return $username !== '' ? $username : null;
}

function envBookingResolvePodIds(string $supabaseUrl, string $serviceKey, string $requestedPodId): array
{
    $podIds = [];
    $requestedPodId = trim($requestedPodId);
    $isUuid = (bool) preg_match(
        '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-5][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/',
        $requestedPodId
    );

    if ($requestedPodId !== '' && $isUuid) {
        $podIds[] = $requestedPodId;
    }

    $normalizedRequested = strtolower($requestedPodId);
    $shouldResolvePodOne = $normalizedRequested === '' || $normalizedRequested === '1' || $normalizedRequested === 'pod 1';
    if (!$shouldResolvePodOne) {
        return array_values(array_unique($podIds));
    }

    $podsUrl = rtrim($supabaseUrl, '/') . '/rest/v1/pods?select=id,name&order=created_at.asc';
    $podsResponse = envBookingHttpRequest($podsUrl, [
        'timeout' => 15,
        'headers' => [
            'Authorization: Bearer ' . $serviceKey,
            'apikey: ' . $serviceKey,
            'Accept: application/json',
        ],
    ]);

    if (!$podsResponse['ok']) {
        return array_values(array_unique($podIds));
    }

    $pods = json_decode($podsResponse['body'], true);
    if (!is_array($pods)) {
        return array_values(array_unique($podIds));
    }

    foreach ($pods as $pod) {
        if (!is_array($pod)) {
            continue;
        }

        $podName = strtolower(trim((string) ($pod['name'] ?? '')));
        if ($podName === 'pod 1') {
            $resolvedId = trim((string) ($pod['id'] ?? ''));
            if ($resolvedId !== '') {
                $podIds[] = $resolvedId;
            }
        }
    }

    return array_values(array_unique($podIds));
}

$env = envBookingLoadEnv(__DIR__ . DIRECTORY_SEPARATOR . '.env');
$supabaseUrl = rtrim((string) ($env['SUPABASE_URL'] ?? ''), '/');
$serviceKey = (string) ($env['SUPABASE_SERVICE_KEY'] ?? '');

if ($supabaseUrl === '' || $serviceKey === '') {
    envBookingJsonResponse(500, [
        'success' => false,
        'error' => 'Supabase is not configured in .env',
    ]);
}

$timezoneName = (string) ($env['APP_TIMEZONE'] ?? 'Asia/Kuala_Lumpur');
$timezone = new DateTimeZone($timezoneName);
$now = new DateTimeImmutable('now', $timezone);
$today = $now->format('Y-m-d');
$nowMinutes = ((int) $now->format('H') * 60) + (int) $now->format('i');
$requestedPodId = trim((string) ($_GET['pod_id'] ?? '1'));
$targetPodIds = envBookingResolvePodIds($supabaseUrl, $serviceKey, $requestedPodId);
if ($targetPodIds === []) {
    $targetPodIds = ['1'];
}

$podFilter = count($targetPodIds) === 1
    ? 'pod_id=eq.' . rawurlencode($targetPodIds[0])
    : 'pod_id=in.(' . implode(',', array_map(static fn(string $id): string => rawurlencode($id), $targetPodIds)) . ')';

$bookingsUrl = $supabaseUrl
    . '/rest/v1/bookings?select=id,user_id,pod_id,booking_date,check_in_time,check_out_time,number_of_people,secondary_user_username'
    . '&' . $podFilter
    . '&booking_date=eq.' . rawurlencode($today)
    . '&order=check_in_time.asc';

$bookingsResponse = envBookingHttpRequest($bookingsUrl, [
    'timeout' => 20,
    'headers' => [
        'Authorization: Bearer ' . $serviceKey,
        'apikey: ' . $serviceKey,
        'Accept: application/json',
    ],
]);

if (!$bookingsResponse['ok']) {
    envBookingJsonResponse(502, [
        'success' => false,
        'error' => 'Failed to fetch bookings from Supabase',
        'detail' => $bookingsResponse['error'] ?: substr($bookingsResponse['body'], 0, 500),
    ]);
}

$bookings = json_decode($bookingsResponse['body'], true);
if (!is_array($bookings)) {
    envBookingJsonResponse(502, [
        'success' => false,
        'error' => 'Supabase returned an invalid bookings payload',
    ]);
}

$bookingCount = count($bookings);
$relevantBooking = null;
$relevantState = 'idle';

foreach ($bookings as $booking) {
    if (!is_array($booking)) {
        continue;
    }

    $checkInMinutes = envBookingMinutesFromTime((string) ($booking['check_in_time'] ?? ''));
    $checkOutMinutes = envBookingMinutesFromTime((string) ($booking['check_out_time'] ?? ''));
    if ($checkInMinutes === null || $checkOutMinutes === null) {
        continue;
    }

    if ($nowMinutes >= $checkInMinutes && $nowMinutes < $checkOutMinutes) {
        $relevantBooking = $booking;
        $relevantState = 'active';
        break;
    }

    if ($nowMinutes >= ($checkInMinutes - 5) && $nowMinutes < $checkInMinutes) {
        $relevantBooking = $booking;
        $relevantState = 'upcoming';
        break;
    }
}

$primaryUsername = '';
$secondaryUsername = '';
$displayName = '';
$windowOpen = false;
$windowKey = '';

if (is_array($relevantBooking)) {
    $primaryUsername = (string) envBookingResolveCreatorUsername(
        $supabaseUrl,
        $serviceKey,
        (string) ($relevantBooking['user_id'] ?? '')
    );
    $secondaryUsername = trim((string) ($relevantBooking['secondary_user_username'] ?? ''));
    $displayName = trim($primaryUsername . ($secondaryUsername !== '' ? ' / ' . $secondaryUsername : ''));

    $checkInMinutes = envBookingMinutesFromTime((string) ($relevantBooking['check_in_time'] ?? ''));
    $checkOutMinutes = envBookingMinutesFromTime((string) ($relevantBooking['check_out_time'] ?? ''));
    if ($checkInMinutes !== null && $checkOutMinutes !== null) {
        $windowOpen = $nowMinutes >= ($checkInMinutes - 5) && $nowMinutes < $checkOutMinutes;
    }
    $windowKey = trim((string) ($relevantBooking['id'] ?? ''));
}

$cameraDeviceId = trim((string) ($env['ESP32_CAM_DEVICE_ID'] ?? 'fortiroom-cam'));
$cameraRegistryResult = cameraRegistryLoadSelectedEntry($env, $cameraDeviceId);
$cameraEntry = $cameraRegistryResult['entry'];
$cameraOnline = cameraRegistryIsFresh($cameraEntry);

envBookingJsonResponse(200, [
    'success' => true,
    'timezone' => $timezoneName,
    'today' => $today,
    'now_iso' => $now->format(DateTimeInterface::ATOM),
    'pod_id' => $requestedPodId,
    'resolved_pod_ids' => $targetPodIds,
    'bookings_today_count' => $bookingCount,
    'relevant_state' => $relevantState,
    'verification_window_open' => $windowOpen,
    'primary_username' => $primaryUsername,
    'secondary_username' => $secondaryUsername,
    'display_name' => $displayName,
    'window_key' => $windowKey,
    'booking' => $relevantBooking,
    'camera' => [
        'device_id' => $cameraRegistryResult['device_id'],
        'online' => $cameraOnline,
        'button_url' => trim((string) ($cameraEntry['button_url'] ?? '')),
        'trigger_capture_url' => trim((string) ($cameraEntry['trigger_capture_url'] ?? '')),
        'capture_url' => trim((string) ($cameraEntry['capture_url'] ?? '')),
        'base_url' => trim((string) ($cameraEntry['base_url'] ?? '')),
    ],
]);
