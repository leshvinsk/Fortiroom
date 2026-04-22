<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'esp32_env_registry.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function loadEnv(string $basePath): void
{
    if (!file_exists($basePath)) {
        return;
    }

    $lines = file($basePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
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
        $_ENV[$key] = $value;
    }
}

function fetchJson(string $url, int $timeoutSeconds): ?array
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $timeoutSeconds,
            CURLOPT_TIMEOUT => $timeoutSeconds,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);
        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $httpCode < 200 || $httpCode >= 300) {
            return null;
        }

        $decoded = json_decode($body, true);
        return is_array($decoded) ? $decoded : null;
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => $timeoutSeconds,
            'header' => "Accept: application/json\r\n",
        ],
    ]);
    $body = @file_get_contents($url, false, $context);
    if ($body === false) {
        return null;
    }

    $decoded = json_decode($body, true);
    return is_array($decoded) ? $decoded : null;
}

function numericOrNull($value): ?float
{
    return is_numeric($value) ? (float) $value : null;
}

loadEnv(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '.env');

$sensorsUrl = trim($_ENV['ESP_SENSORS_URL'] ?? '');
if ($sensorsUrl === '') {
    $envDeviceId = trim((string) ($_ENV['ESP_ENV_DEVICE_ID'] ?? 'fortiroom-main'));
    $registryResult = envRegistryLoadSelectedEntry($envDeviceId);
    $registryEntry = $registryResult['entry'];
    if (envRegistryIsFresh($registryEntry)) {
        $registrySensorsUrl = trim((string) ($registryEntry['sensors_url'] ?? ''));
        if ($registrySensorsUrl !== '') {
            $sensorsUrl = $registrySensorsUrl;
        }
    }
}
if ($sensorsUrl === '') {
    $espBaseUrl = rtrim(trim($_ENV['ESP_BASE_URL'] ?? 'http://esp32.local'), '/');
    $sensorsPath = '/' . ltrim(trim($_ENV['ESP_SENSORS_PATH'] ?? '/api/sensors'), '/');
    $sensorsUrl = $espBaseUrl . $sensorsPath;
}

$timeoutSec = (int) ($_ENV['ESP_HTTP_TIMEOUT_SEC'] ?? 3);
if ($timeoutSec < 1) {
    $timeoutSec = 1;
}
if ($timeoutSec > 10) {
    $timeoutSec = 10;
}

$maxAttempts = 2;
$retryDelayMs = 180;
$payload = null;
for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
    $payload = fetchJson($sensorsUrl, $timeoutSec);
    if ($payload !== null) {
        break;
    }
    if ($attempt < $maxAttempts) {
        usleep($retryDelayMs * 1000);
    }
}

if ($payload === null) {
    http_response_code(502);
    echo json_encode([
        'ok' => false,
        'error' => 'Unable to read ESP metrics',
        'source' => $sensorsUrl,
        'attempts' => $maxAttempts,
    ]);
    exit;
}

$temperature = numericOrNull($payload['temp_c'] ?? null);
$humidity = numericOrNull($payload['hum'] ?? null);
$aqi = numericOrNull($payload['air_index'] ?? null);

echo json_encode([
    'ok' => true,
    'temperature' => $temperature,
    'humidity' => $humidity,
    'aqi' => $aqi === null ? null : (int) round($aqi),
    'source' => $sensorsUrl,
    'timestamp_utc' => gmdate('c'),
]);
