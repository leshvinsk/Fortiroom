<?php

if (!defined('ENV_REGISTRY_FILE')) {
    define('ENV_REGISTRY_FILE', __DIR__ . DIRECTORY_SEPARATOR . 'esp32_env_registry_store.json');
}

if (!defined('ENV_REGISTRY_TTL')) {
    define('ENV_REGISTRY_TTL', 900);
}

if (!function_exists('envRegistryNormalizeUrl')) {
    function envRegistryNormalizeUrl(string $value): string
    {
        $value = trim($value);
        while ($value !== '' && substr($value, -1) === '/') {
            $value = substr($value, 0, -1);
        }
        return $value;
    }
}

if (!function_exists('envRegistryLoadLocalRaw')) {
    function envRegistryLoadLocalRaw(): ?array
    {
        if (!is_file(ENV_REGISTRY_FILE)) {
            return null;
        }

        $raw = file_get_contents(ENV_REGISTRY_FILE);
        if (!is_string($raw) || trim($raw) === '') {
            return null;
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }
}

if (!function_exists('envRegistryNormalize')) {
    function envRegistryNormalize(?array $data): array
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

        $legacyDeviceId = trim((string) ($data['device_id'] ?? $data['device'] ?? 'fortiroom-main'));
        if ($legacyDeviceId === '') {
            $legacyDeviceId = 'fortiroom-main';
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

if (!function_exists('envRegistrySaveLocal')) {
    function envRegistrySaveLocal(array $payload): bool
    {
        return file_put_contents(
            ENV_REGISTRY_FILE,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        ) !== false;
    }
}

if (!function_exists('envRegistryIsFresh')) {
    function envRegistryIsFresh(?array $entry): bool
    {
        if (!is_array($entry)) {
            return false;
        }

        $lastSeen = (int) ($entry['last_seen_unix'] ?? 0);
        if ($lastSeen <= 0) {
            return false;
        }

        return (time() - $lastSeen) <= ENV_REGISTRY_TTL;
    }
}

if (!function_exists('envRegistrySelectDevice')) {
    function envRegistrySelectDevice(array $registry, ?string $requestedDeviceId): array
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

if (!function_exists('envRegistryLoadSelectedEntry')) {
    function envRegistryLoadSelectedEntry(string $deviceId = 'fortiroom-main'): array
    {
        $registry = envRegistryNormalize(envRegistryLoadLocalRaw());
        [$selectedDeviceId, $entry] = envRegistrySelectDevice($registry, $deviceId);
        return [
            'registry' => $registry,
            'device_id' => $selectedDeviceId,
            'entry' => $entry,
            'source' => 'local',
        ];
    }
}

if (!function_exists('envRegistryJsonResponse')) {
    function envRegistryJsonResponse(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }
}

if (!function_exists('envRegistryHandleRequest')) {
    function envRegistryHandleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($method === 'GET') {
            $deviceId = trim((string) ($_GET['device_id'] ?? 'fortiroom-main'));
            $result = envRegistryLoadSelectedEntry($deviceId);
            $entry = $result['entry'];
            envRegistryJsonResponse(200, [
                'success' => true,
                'device_id' => $result['device_id'],
                'found' => envRegistryIsFresh($entry),
                'registration' => $entry,
                'devices' => $result['registry']['devices'] ?? [],
                'latest_device_id' => $result['registry']['latest_device_id'] ?? null,
                'ttl_seconds' => ENV_REGISTRY_TTL,
                'source' => 'local',
            ]);
        }

        if ($method !== 'POST') {
            envRegistryJsonResponse(405, ['success' => false, 'error' => 'Method not allowed']);
        }

        $raw = file_get_contents('php://input');
        $data = json_decode($raw ?: '{}', true);
        if (!is_array($data)) {
            envRegistryJsonResponse(400, ['success' => false, 'error' => 'Invalid JSON body']);
        }

        $ip = trim((string) ($data['ip'] ?? ''));
        $baseUrl = envRegistryNormalizeUrl((string) ($data['base_url'] ?? ''));
        $sensorsUrl = trim((string) ($data['sensors_url'] ?? ''));
        $deviceId = trim((string) ($data['device_id'] ?? $data['device'] ?? 'fortiroom-main'));

        if ($deviceId === '') {
            $deviceId = 'fortiroom-main';
        }

        if ($ip === '' || $baseUrl === '' || $sensorsUrl === '') {
            envRegistryJsonResponse(400, [
                'success' => false,
                'error' => 'ip, base_url, and sensors_url are required',
            ]);
        }

        $entry = [
            'device_id' => $deviceId,
            'device' => trim((string) ($data['device'] ?? 'fortiroom-main')),
            'hostname' => trim((string) ($data['hostname'] ?? 'fortiroom-main')),
            'ssid' => trim((string) ($data['ssid'] ?? '')),
            'ip' => $ip,
            'base_url' => $baseUrl,
            'sensors_url' => $sensorsUrl,
            'health_url' => trim((string) ($data['health_url'] ?? ($baseUrl . '/health'))),
            'time_url' => trim((string) ($data['time_url'] ?? ($baseUrl . '/api/time'))),
            'unlock_url' => trim((string) ($data['unlock_url'] ?? ($baseUrl . '/unlock'))),
            'door_url' => trim((string) ($data['door_url'] ?? ($baseUrl . '/api/door'))),
            'light_url' => trim((string) ($data['light_url'] ?? ($baseUrl . '/light-toggle'))),
            'light_state_url' => trim((string) ($data['light_state_url'] ?? ($baseUrl . '/api/light'))),
            'fan_url' => trim((string) ($data['fan_url'] ?? ($baseUrl . '/fan'))),
            'mac' => trim((string) ($data['mac'] ?? '')),
            'rssi' => (int) ($data['rssi'] ?? 0),
            'last_seen_unix' => time(),
            'last_seen_iso' => gmdate('c'),
            'reported_by' => trim((string) ($data['reported_by'] ?? 'esp32')),
        ];

        $registry = envRegistryNormalize(envRegistryLoadLocalRaw());
        $registry['devices'][$deviceId] = $entry;
        $registry['latest_device_id'] = $deviceId;
        $registry['updated_at_unix'] = time();
        $registry['updated_at_iso'] = gmdate('c');

        if (!envRegistrySaveLocal($registry)) {
            envRegistryJsonResponse(500, ['success' => false, 'error' => 'Failed to save registry entry']);
        }

        envRegistryJsonResponse(200, [
            'success' => true,
            'device_id' => $deviceId,
            'message' => 'ESP32 environment registration updated',
            'registration' => $entry,
            'latest_device_id' => $registry['latest_device_id'],
            'source' => 'local',
        ]);
    }
}

$scriptFilename = realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''));
if ($scriptFilename !== false && $scriptFilename === __FILE__) {
    envRegistryHandleRequest();
}
