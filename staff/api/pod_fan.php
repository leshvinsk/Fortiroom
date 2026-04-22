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
        if (strpos(ltrim($line), '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim(trim($value), "\"'");
    }
}

function requestJson(string $url, string $method, ?string $jsonBody, int $timeoutSeconds): array
{
    $headers = ['Accept: application/json'];
    if ($jsonBody !== null) {
        $headers[] = 'Content-Type: application/json';
    }

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_CONNECTTIMEOUT => $timeoutSeconds,
            CURLOPT_TIMEOUT => $timeoutSeconds,
            CURLOPT_HTTPHEADER => $headers,
        ]);
        if ($jsonBody !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
        }
        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        return [$httpCode, is_string($body) ? $body : '', $error];
    }

    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'timeout' => $timeoutSeconds,
            'header' => implode("\r\n", $headers),
            'content' => $jsonBody ?? '',
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

$deviceId = trim((string) ($_ENV['ESP_ENV_DEVICE_ID'] ?? 'fortiroom-main'));
$registryResult = envRegistryLoadSelectedEntry($deviceId);
$entry = $registryResult['entry'];
if (!envRegistryIsFresh($entry)) {
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => 'Environment controller is offline or registry entry is stale', 'device_id' => $deviceId]);
    exit;
}

$timeoutSec = (int) ($_ENV['ESP_HTTP_TIMEOUT_SEC'] ?? 6);
if ($timeoutSec < 1) $timeoutSec = 1;
if ($timeoutSec > 10) $timeoutSec = 10;

$method = strtoupper(trim((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')));
$baseUrl = trim((string) ($entry['base_url'] ?? ''));
$targetUrl = trim((string) (($entry['fan_url'] ?? '') !== '' ? $entry['fan_url'] : ($baseUrl . '/fan')));

$jsonBody = null;
if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $payload = json_decode($raw ?: '{}', true);
    if (!is_array($payload)) {
        $payload = [];
    }
    $jsonBody = json_encode($payload);
}

[$statusCode, $body, $transportError] = requestJson($targetUrl, $method, $jsonBody, $timeoutSec);
if ($statusCode < 200 || $statusCode >= 300) {
    http_response_code($statusCode > 0 ? $statusCode : 502);
    echo json_encode([
        'ok' => false,
        'error' => 'Fan request failed',
        'device_id' => $deviceId,
        'target_url' => $targetUrl,
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
        'error' => 'Fan response was not valid JSON',
        'device_id' => $deviceId,
        'target_url' => $targetUrl,
        'raw_response' => $body,
    ]);
    exit;
}

echo json_encode([
    'ok' => !empty($payload['ok']),
    'device_id' => $deviceId,
    'target_url' => $targetUrl,
    'esp_response' => $payload,
    'timestamp_utc' => gmdate('c'),
]);
