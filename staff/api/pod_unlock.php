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

function postJson(string $url, int $timeoutSeconds): array
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => '',
            CURLOPT_CONNECTTIMEOUT => $timeoutSeconds,
            CURLOPT_TIMEOUT => $timeoutSeconds,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);
        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return [$httpCode, is_string($body) ? $body : '', $error];
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'timeout' => $timeoutSeconds,
            'header' => "Accept: application/json\r\nContent-Length: 0\r\n",
            'content' => '',
            'ignore_errors' => true,
        ],
    ]);
    $body = @file_get_contents($url, false, $context);
    $statusCode = 0;
    if (isset($http_response_header) && is_array($http_response_header)) {
        foreach ($http_response_header as $headerLine) {
            if (preg_match('/HTTP\/\S+\s+(\d{3})/', $headerLine, $matches)) {
                $statusCode = (int) $matches[1];
                break;
            }
        }
    }
    return [$statusCode, is_string($body) ? $body : '', ''];
}

loadEnv(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '.env');

$podId = trim((string) ($_REQUEST['pod_id'] ?? ''));
$deviceId = trim((string) ($_ENV['ESP_ENV_DEVICE_ID'] ?? 'fortiroom-main'));

$registryResult = envRegistryLoadSelectedEntry($deviceId);
$entry = $registryResult['entry'];
if (!envRegistryIsFresh($entry)) {
    http_response_code(503);
    echo json_encode([
        'ok' => false,
        'error' => 'Environment controller is offline or registry entry is stale',
        'device_id' => $deviceId,
    ]);
    exit;
}

$unlockUrl = trim((string) (($entry['unlock_url'] ?? '') !== '' ? $entry['unlock_url'] : (($entry['base_url'] ?? '') . '/unlock')));
if ($unlockUrl === '') {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Unlock URL is missing from the environment controller registration',
        'device_id' => $deviceId,
    ]);
    exit;
}

$timeoutSec = (int) ($_ENV['ESP_HTTP_TIMEOUT_SEC'] ?? 6);
if ($timeoutSec < 1) {
    $timeoutSec = 1;
}
if ($timeoutSec > 10) {
    $timeoutSec = 10;
}

[$statusCode, $body, $transportError] = postJson($unlockUrl, $timeoutSec);
$decodedError = json_decode($body, true);
$resolvedError = is_array($decodedError) && !empty($decodedError['error'])
    ? (string) $decodedError['error']
    : 'Unlock request failed';
if ($statusCode < 200 || $statusCode >= 300) {
    http_response_code($statusCode > 0 ? $statusCode : 502);
    echo json_encode([
        'ok' => false,
        'error' => $resolvedError,
        'device_id' => $deviceId,
        'unlock_url' => $unlockUrl,
        'status_code' => $statusCode,
        'transport_error' => $transportError,
        'raw_response' => $body,
    ]);
    exit;
}

$payload = json_decode($body, true);
if (!is_array($payload)) {
    http_response_code(502);
    echo json_encode([
        'ok' => false,
        'error' => 'Unlock response was not valid JSON',
        'device_id' => $deviceId,
        'unlock_url' => $unlockUrl,
        'raw_response' => $body,
    ]);
    exit;
}

echo json_encode([
    'ok' => !empty($payload['ok']),
    'device_id' => $deviceId,
    'pod_id' => $podId,
    'unlock_url' => $unlockUrl,
    'esp_response' => $payload,
    'timestamp_utc' => gmdate('c'),
]);
