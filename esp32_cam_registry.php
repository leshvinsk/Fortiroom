<?php

if (!defined('CAMERA_REGISTRY_FILE')) {
    define('CAMERA_REGISTRY_FILE', __DIR__ . DIRECTORY_SEPARATOR . 'esp32_cam_registry_store.json');
}

if (!defined('CAMERA_REGISTRY_TTL')) {
    define('CAMERA_REGISTRY_TTL', 900);
}

if (!function_exists('cameraRegistryLoadEnv')) {
    function cameraRegistryLoadEnv(string $envPath): array
    {
        $env = [];
        if (!file_exists($envPath)) {
            return $env;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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
        }

        return $env;
    }
}

if (!function_exists('cameraRegistryNormalizeUrl')) {
    function cameraRegistryNormalizeUrl(string $value): string
    {
        $value = trim($value);
        while ($value !== '' && substr($value, -1) === '/') {
            $value = substr($value, 0, -1);
        }
        return $value;
    }
}

if (!function_exists('cameraRegistryLoadLocalRaw')) {
    function cameraRegistryLoadLocalRaw(): ?array
    {
        if (!is_file(CAMERA_REGISTRY_FILE)) {
            return null;
        }

        $raw = file_get_contents(CAMERA_REGISTRY_FILE);
        if (!is_string($raw) || trim($raw) === '') {
            return null;
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }
}

if (!function_exists('cameraRegistryNormalize')) {
    function cameraRegistryNormalize(?array $data): array
    {
        if (!is_array($data)) {
            return [
                'devices' => [],
                'latest_device_id' => null,
                'updated_at_unix' => 0,
                'updated_at_iso' => null,
            ];
        }

        if (isset($data['devices']) && is_array($data['devices'])) {
            return [
                'devices' => $data['devices'],
                'latest_device_id' => isset($data['latest_device_id']) ? (string) $data['latest_device_id'] : null,
                'updated_at_unix' => (int) ($data['updated_at_unix'] ?? 0),
                'updated_at_iso' => (string) ($data['updated_at_iso'] ?? ''),
            ];
        }

        $legacyDeviceId = trim((string) ($data['device_id'] ?? $data['device'] ?? 'fortiroom-cam'));
        if ($legacyDeviceId === '') {
            $legacyDeviceId = 'fortiroom-cam';
        }

        return [
            'devices' => [
                $legacyDeviceId => $data,
            ],
            'latest_device_id' => $legacyDeviceId,
            'updated_at_unix' => (int) ($data['last_seen_unix'] ?? 0),
            'updated_at_iso' => (string) ($data['last_seen_iso'] ?? ''),
        ];
    }
}

if (!function_exists('cameraRegistrySaveLocal')) {
    function cameraRegistrySaveLocal(array $payload): bool
    {
        return file_put_contents(
            CAMERA_REGISTRY_FILE,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        ) !== false;
    }
}

if (!function_exists('cameraRegistryIsFresh')) {
    function cameraRegistryIsFresh(?array $entry): bool
    {
        if (!is_array($entry)) {
            return false;
        }

        $lastSeen = (int) ($entry['last_seen_unix'] ?? 0);
        if ($lastSeen <= 0) {
            return false;
        }

        return (time() - $lastSeen) <= CAMERA_REGISTRY_TTL;
    }
}

if (!function_exists('cameraRegistrySelectDevice')) {
    function cameraRegistrySelectDevice(array $registry, ?string $requestedDeviceId): array
    {
        $requestedDeviceId = trim((string) $requestedDeviceId);
        $devices = $registry['devices'] ?? [];

        if ($requestedDeviceId !== '' && isset($devices[$requestedDeviceId])) {
            return [$requestedDeviceId, $devices[$requestedDeviceId]];
        }

        $latestDeviceId = trim((string) ($registry['latest_device_id'] ?? ''));
        if ($latestDeviceId !== '' && isset($devices[$latestDeviceId])) {
            return [$latestDeviceId, $devices[$latestDeviceId]];
        }

        foreach ($devices as $deviceId => $entry) {
            if (is_array($entry)) {
                return [(string) $deviceId, $entry];
            }
        }

        return [null, null];
    }
}

if (!function_exists('cameraRegistryLoadSelectedEntry')) {
    function cameraRegistryLoadSelectedEntry(array $env, string $deviceId = 'fortiroom-cam'): array
    {
        $registry = cameraRegistryNormalize(cameraRegistryLoadLocalRaw());
        [$selectedDeviceId, $entry] = cameraRegistrySelectDevice($registry, $deviceId);
        return [
            'registry' => $registry,
            'device_id' => $selectedDeviceId,
            'entry' => $entry,
            'source' => 'local',
        ];
    }
}

if (!function_exists('esp32RegistryJsonResponse')) {
    function esp32RegistryJsonResponse(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }
}

if (!function_exists('esp32RegistryHandleRequest')) {
    function esp32RegistryHandleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($method === 'GET') {
            $deviceId = trim((string) ($_GET['device_id'] ?? 'fortiroom-cam'));
            $result = cameraRegistryLoadSelectedEntry([], $deviceId);
            $entry = $result['entry'];
            esp32RegistryJsonResponse(200, [
                'success' => true,
                'device_id' => $result['device_id'],
                'found' => cameraRegistryIsFresh($entry),
                'registration' => $entry,
                'devices' => $result['registry']['devices'] ?? [],
                'latest_device_id' => $result['registry']['latest_device_id'] ?? null,
                'ttl_seconds' => CAMERA_REGISTRY_TTL,
                'source' => 'local',
            ]);
        }

        if ($method !== 'POST') {
            esp32RegistryJsonResponse(405, ['success' => false, 'error' => 'Method not allowed']);
        }

        $raw = file_get_contents('php://input');
        $data = json_decode($raw ?: '{}', true);
        if (!is_array($data)) {
            esp32RegistryJsonResponse(400, ['success' => false, 'error' => 'Invalid JSON body']);
        }

        $ip = trim((string) ($data['ip'] ?? ''));
        $baseUrl = cameraRegistryNormalizeUrl((string) ($data['base_url'] ?? ''));
        $captureUrl = trim((string) ($data['capture_url'] ?? ''));
        $deviceId = trim((string) ($data['device_id'] ?? $data['device'] ?? 'fortiroom-cam'));

        if ($deviceId === '') {
            $deviceId = 'fortiroom-cam';
        }

        if ($ip === '' || $baseUrl === '' || $captureUrl === '') {
            esp32RegistryJsonResponse(400, [
                'success' => false,
                'error' => 'ip, base_url, and capture_url are required',
            ]);
        }

        $entry = [
            'device_id' => $deviceId,
            'device' => trim((string) ($data['device'] ?? 'fortiroom-cam')),
            'hostname' => trim((string) ($data['hostname'] ?? 'fortiroom-cam')),
            'ssid' => trim((string) ($data['ssid'] ?? '')),
            'ip' => $ip,
            'base_url' => $baseUrl,
            'capture_url' => $captureUrl,
            'health_url' => trim((string) ($data['health_url'] ?? ($baseUrl . '/health'))),
            'button_url' => trim((string) ($data['button_url'] ?? ($baseUrl . '/button'))),
            'trigger_capture_url' => trim((string) ($data['trigger_capture_url'] ?? ($baseUrl . '/trigger-capture'))),
            'mac' => trim((string) ($data['mac'] ?? '')),
            'rssi' => (int) ($data['rssi'] ?? 0),
            'last_seen_unix' => time(),
            'last_seen_iso' => gmdate('c'),
            'reported_by' => trim((string) ($data['reported_by'] ?? 'esp32')),
        ];

        $registry = cameraRegistryNormalize(cameraRegistryLoadLocalRaw());
        $registry['devices'][$deviceId] = $entry;
        $registry['latest_device_id'] = $deviceId;
        $registry['updated_at_unix'] = time();
        $registry['updated_at_iso'] = gmdate('c');

        if (!cameraRegistrySaveLocal($registry)) {
            esp32RegistryJsonResponse(500, ['success' => false, 'error' => 'Failed to save registry entry']);
        }

        esp32RegistryJsonResponse(200, [
            'success' => true,
            'device_id' => $deviceId,
            'message' => 'ESP32 camera registration updated',
            'registration' => $entry,
            'latest_device_id' => $registry['latest_device_id'],
            'source' => 'local',
        ]);
    }
}

$scriptFilename = realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''));
if ($scriptFilename !== false && $scriptFilename === __FILE__) {
    esp32RegistryHandleRequest();
}
