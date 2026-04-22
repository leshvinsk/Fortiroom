<?php
header('Content-Type: application/json');
require_once __DIR__ . DIRECTORY_SEPARATOR . 'esp32_cam_registry.php';

function loadEnvFile(string $path): array
{
    $env = [];
    if (!file_exists($path)) {
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

function jsonResponse(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function httpRequest(string $url, array $options = []): array
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $options['method'] ?? 'GET');
    curl_setopt($ch, CURLOPT_TIMEOUT, $options['timeout'] ?? 20);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    if (!empty($options['headers'])) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $options['headers']);
    }

    if (array_key_exists('body', $options)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $options['body']);
    }

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $contentType = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if ($response === false) {
        return [
            'ok' => false,
            'status' => $status,
            'error' => $error ?: 'Request failed',
            'headers' => '',
            'body' => '',
            'content_type' => $contentType,
        ];
    }

    return [
        'ok' => $status >= 200 && $status < 300,
        'status' => $status,
        'error' => $error,
        'headers' => substr($response, 0, $headerSize),
        'body' => substr($response, $headerSize),
        'content_type' => $contentType,
    ];
}

function detectMimeType(string $body, string $fallback = 'application/octet-stream'): string
{
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = finfo_buffer($finfo, $body);
            finfo_close($finfo);
            if (is_string($mime) && $mime !== '') {
                return $mime;
            }
        }
    }

    return $fallback;
}

function extensionFromMimeType(string $mime): string
{
    $map = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/bmp' => 'bmp',
    ];

    return $map[strtolower($mime)] ?? 'jpg';
}

function extractJsonScore(array $payload): ?float
{
    if (isset($payload['result']) && is_array($payload['result'])) {
        $candidates = [];

        foreach ($payload['result'] as $resultItem) {
            if (!is_array($resultItem)) {
                continue;
            }

            $matches = $resultItem['face_matches'] ?? null;
            if (!is_array($matches)) {
                continue;
            }

            foreach ($matches as $match) {
                if (is_array($match) && isset($match['similarity']) && is_numeric($match['similarity'])) {
                    $candidates[] = (float) $match['similarity'];
                }
            }
        }

        if ($candidates !== []) {
            return max($candidates);
        }
    }

    return null;
}

function pickLatestAvatarPath(array $items, string $prefix): ?string
{
    if ($items === []) {
        return null;
    }

    usort($items, static function ($a, $b): int {
        $aTime = strtotime((string) ($a['updated_at'] ?? $a['created_at'] ?? '')) ?: 0;
        $bTime = strtotime((string) ($b['updated_at'] ?? $b['created_at'] ?? '')) ?: 0;
        if ($aTime === $bTime) {
            return strcmp((string) ($b['name'] ?? ''), (string) ($a['name'] ?? ''));
        }
        return $bTime <=> $aTime;
    });

    $picked = $items[0]['name'] ?? null;
    if (!is_string($picked) || $picked === '') {
        return null;
    }

    return trim($prefix, '/') . '/' . ltrim($picked, '/');
}

function withCacheBuster(string $url): string
{
    $separator = strpos($url, '?') === false ? '?' : '&';
    return $url . $separator . '_ts=' . rawurlencode((string) microtime(true));
}

function decodeDataUrlImage(?string $value): ?array
{
    $raw = trim((string) $value);
    if ($raw === '') {
        return null;
    }

    if (!preg_match('/^data:([^;,]+)?(?:;charset=[^;,]+)?;base64,(.*)$/si', $raw, $matches)) {
        return null;
    }

    $mime = trim((string) ($matches[1] ?? ''));
    $base64 = preg_replace('/\s+/', '', (string) ($matches[2] ?? ''));
    if ($base64 === '') {
        return null;
    }

    $binary = base64_decode($base64, true);
    if ($binary === false || $binary === '') {
        return null;
    }

    return [
        'binary' => $binary,
        'mime' => $mime !== '' ? $mime : 'image/jpeg',
    ];
}

function optimizeImageForVerification(string $binary, string $mime, int $maxDimension = 640, int $jpegQuality = 82): array
{
    if (!function_exists('imagecreatefromstring')) {
        return ['binary' => $binary, 'mime' => $mime];
    }

    $source = @imagecreatefromstring($binary);
    if ($source === false) {
        return ['binary' => $binary, 'mime' => $mime];
    }

    $width = imagesx($source);
    $height = imagesy($source);
    if ($width <= 0 || $height <= 0) {
        imagedestroy($source);
        return ['binary' => $binary, 'mime' => $mime];
    }

    $scale = min(1.0, $maxDimension / max($width, $height));
    $targetWidth = max(1, (int) round($width * $scale));
    $targetHeight = max(1, (int) round($height * $scale));

    $target = imagecreatetruecolor($targetWidth, $targetHeight);
    if ($target === false) {
        imagedestroy($source);
        return ['binary' => $binary, 'mime' => $mime];
    }

    imagealphablending($target, false);
    imagesavealpha($target, true);
    $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
    imagefill($target, 0, 0, $transparent);

    imagecopyresampled(
        $target,
        $source,
        0,
        0,
        0,
        0,
        $targetWidth,
        $targetHeight,
        $width,
        $height
    );

    ob_start();
    imagejpeg($target, null, $jpegQuality);
    $optimized = ob_get_clean();

    imagedestroy($source);
    imagedestroy($target);

    if (!is_string($optimized) || $optimized === '') {
        return ['binary' => $binary, 'mime' => $mime];
    }

    return ['binary' => $optimized, 'mime' => 'image/jpeg'];
}

function verifyWithCompreface(
    string $verifyUrl,
    string $apiKey,
    string $avatarBinary,
    string $avatarMime,
    string $captureBinary,
    string $captureMime
): array {
    $avatarTemp = tempnam(sys_get_temp_dir(), 'avatar_');
    $captureTemp = tempnam(sys_get_temp_dir(), 'capture_');
    if ($avatarTemp === false || $captureTemp === false) {
        jsonResponse(500, ['error' => 'Unable to prepare temporary files for verification']);
    }

    file_put_contents($avatarTemp, $avatarBinary);
    file_put_contents($captureTemp, $captureBinary);

    $response = httpRequest($verifyUrl, [
        'method' => 'POST',
        'timeout' => 30,
        'headers' => [
            'x-api-key: ' . $apiKey,
        ],
        'body' => [
            'source_image' => new CURLFile(
                $captureTemp,
                $captureMime,
                'esp32-capture.' . extensionFromMimeType($captureMime)
            ),
            'target_image' => new CURLFile(
                $avatarTemp,
                $avatarMime,
                'avatar-image.' . extensionFromMimeType($avatarMime)
            ),
        ],
    ]);

    @unlink($avatarTemp);
    @unlink($captureTemp);

    return $response;
}

$env = array_merge(
    loadEnvFile(__DIR__ . DIRECTORY_SEPARATOR . '.env'),
    cameraRegistryLoadEnv(__DIR__ . DIRECTORY_SEPARATOR . '.env')
);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(405, ['error' => 'Method not allowed']);
}

if (!function_exists('curl_init')) {
    jsonResponse(500, ['error' => 'cURL is required on the server']);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw ?: '{}', true);
if (!is_array($data)) {
    jsonResponse(400, ['error' => 'Invalid JSON body']);
}

$username = trim((string) ($data['username'] ?? ''));
if ($username === '') {
    jsonResponse(400, ['error' => 'Username is required']);
}

$safeUsername = strtolower((string) preg_replace('/[^a-zA-Z0-9_-]/', '_', $username));
$supabaseUrl = rtrim((string) ($env['SUPABASE_URL'] ?? ''), '/');
$supabaseServiceKey = (string) ($env['SUPABASE_SERVICE_KEY'] ?? '');
$avatarsBucket = (string) ($env['AVATARS_BUCKET'] ?? 'avatars');
$espCaptureUrl = trim((string) ($data['esp32_capture_url'] ?? ''));
$espCameraDeviceId = trim((string) ($env['ESP32_CAM_DEVICE_ID'] ?? 'fortiroom-cam'));
if ($espCaptureUrl === '') {
    $registryResult = cameraRegistryLoadSelectedEntry($env, $espCameraDeviceId);
    $entry = $registryResult['entry'];
    if (cameraRegistryIsFresh($entry)) {
        $registeredCaptureUrl = trim((string) ($entry['capture_url'] ?? ''));
        if ($registeredCaptureUrl !== '') {
            $espCaptureUrl = $registeredCaptureUrl;
        }
    }
}
$comprefaceBaseUrl = rtrim((string) ($env['COMPRE_FACE_BASE_URL'] ?? ($env['COMPREFACE_BASE_URL'] ?? '')), '/');
$comprefaceApiKey = (string) ($env['COMPRE_FACE_API_KEY'] ?? ($env['COMPREFACE_API_KEY'] ?? ''));
$threshold = (float) ($data['threshold'] ?? ($env['COMPRE_FACE_SIMILARITY_THRESHOLD'] ?? ($env['COMPREFACE_SIMILARITY_THRESHOLD'] ?? 0.75)));
$avatarOverrideUrl = trim((string) ($data['avatar_url'] ?? ''));

if ($supabaseUrl === '' || $supabaseServiceKey === '') {
    jsonResponse(500, ['error' => 'Supabase is not configured in .env']);
}

if ($espCaptureUrl === '') {
    jsonResponse(500, ['error' => 'ESP32 capture URL is not configured']);
}

if ($comprefaceBaseUrl === '' || $comprefaceApiKey === '') {
    jsonResponse(500, ['error' => 'Compreface is not configured in .env']);
}

$avatarPublicUrl = $avatarOverrideUrl;
$avatarBinary = '';
$avatarMime = '';
$avatarStoragePath = '';
$avatarPreviewData = null;
$capturePreviewData = null;
$captureBinary = '';
$captureMime = 'image/jpeg';
$captureImageData = decodeDataUrlImage((string) ($data['capture_image_data'] ?? ''));

if ($avatarOverrideUrl !== '') {
    $avatarFetch = httpRequest($avatarOverrideUrl, ['timeout' => 20]);
    if (!$avatarFetch['ok']) {
        jsonResponse(502, [
            'error' => 'Failed to download the avatar image override URL',
            'detail' => $avatarFetch['error'] ?: ('HTTP ' . $avatarFetch['status']),
        ]);
    }

    $avatarBinary = $avatarFetch['body'];
    $avatarMime = detectMimeType($avatarBinary, $avatarFetch['content_type'] ?: 'image/jpeg');
} else {
    $listUrl = $supabaseUrl . '/storage/v1/object/list/' . rawurlencode($avatarsBucket);
    $listPayload = json_encode([
        'prefix' => $safeUsername,
        'limit' => 100,
        'offset' => 0,
        'sortBy' => ['column' => 'created_at', 'order' => 'desc'],
    ]);

    $listResponse = httpRequest($listUrl, [
        'method' => 'POST',
        'timeout' => 20,
        'headers' => [
            'Authorization: Bearer ' . $supabaseServiceKey,
            'apikey: ' . $supabaseServiceKey,
            'Content-Type: application/json',
        ],
        'body' => $listPayload,
    ]);

    if (!$listResponse['ok']) {
        jsonResponse(502, [
            'error' => 'Failed to list avatar images from Supabase Storage',
            'detail' => substr($listResponse['body'], 0, 500),
        ]);
    }

    $listData = json_decode($listResponse['body'], true);
    if (!is_array($listData)) {
        jsonResponse(502, ['error' => 'Supabase returned an unexpected avatar list response']);
    }

    $avatarStoragePath = (string) pickLatestAvatarPath($listData, $safeUsername);
    if ($avatarStoragePath === '') {
        jsonResponse(404, ['error' => 'No avatar image found for this username in Supabase Storage']);
    }

    $avatarPublicUrl = $supabaseUrl . '/storage/v1/object/public/' . rawurlencode($avatarsBucket) . '/' . str_replace('%2F', '/', rawurlencode($avatarStoragePath));
    $avatarFetchUrl = $supabaseUrl . '/storage/v1/object/authenticated/' . rawurlencode($avatarsBucket) . '/' . str_replace('%2F', '/', rawurlencode($avatarStoragePath));
    $avatarFetch = httpRequest($avatarFetchUrl, [
        'timeout' => 20,
        'headers' => [
            'Authorization: Bearer ' . $supabaseServiceKey,
            'apikey: ' . $supabaseServiceKey,
        ],
    ]);

    if (!$avatarFetch['ok']) {
        jsonResponse(502, [
            'error' => 'Failed to download the avatar image from Supabase Storage',
            'detail' => $avatarFetch['error'] ?: ('HTTP ' . $avatarFetch['status']),
        ]);
    }

    $avatarBinary = $avatarFetch['body'];
    $avatarMime = detectMimeType($avatarBinary, $avatarFetch['content_type'] ?: 'image/jpeg');
}

$avatarPreviewData = 'data:' . $avatarMime . ';base64,' . base64_encode($avatarBinary);

if (is_array($captureImageData)) {
    $captureBinary = $captureImageData['binary'];
    $captureMime = detectMimeType($captureBinary, (string) $captureImageData['mime']);
} else {
    $captureFetchUrl = withCacheBuster($espCaptureUrl);
    $captureResponse = httpRequest($captureFetchUrl, ['timeout' => 20]);
    if (!$captureResponse['ok']) {
        jsonResponse(502, [
            'error' => 'Failed to capture an image from the ESP32-CAM',
            'detail' => $captureResponse['error'] ?: ('HTTP ' . $captureResponse['status']),
        ]);
    }

    $captureBinary = $captureResponse['body'];
    $captureMime = detectMimeType($captureBinary, $captureResponse['content_type'] ?: 'image/jpeg');
}
$capturePreviewData = 'data:' . $captureMime . ';base64,' . base64_encode($captureBinary);

$optimizedAvatar = optimizeImageForVerification($avatarBinary, $avatarMime);
$optimizedCapture = optimizeImageForVerification($captureBinary, $captureMime);

$verifyUrl = $comprefaceBaseUrl . '/api/v1/verification/verify';
$verifyAttempt = 'optimized';
$verifyResponse = verifyWithCompreface(
    $verifyUrl,
    $comprefaceApiKey,
    $optimizedAvatar['binary'],
    $optimizedAvatar['mime'],
    $optimizedCapture['binary'],
    $optimizedCapture['mime']
);

if (!$verifyResponse['ok'] && stripos($verifyResponse['body'], 'No face is found') !== false) {
    $originalVerifyResponse = verifyWithCompreface(
        $verifyUrl,
        $comprefaceApiKey,
        $avatarBinary,
        $avatarMime,
        $captureBinary,
        $captureMime
    );

    $verifyAttempt = $originalVerifyResponse['ok'] ? 'original_retry' : 'optimized_then_original_retry_failed';
    $verifyResponse = $originalVerifyResponse;
}

if (!$verifyResponse['ok']) {
    jsonResponse(502, [
        'error' => 'Compreface verification request failed',
        'detail' => substr($verifyResponse['body'], 0, 1000),
        'verify_attempt' => $verifyAttempt,
        'username' => $username,
        'avatar_storage_path' => $avatarStoragePath,
        'avatar_url' => $avatarPublicUrl,
        'esp32_capture_url' => $espCaptureUrl,
        'avatar_preview' => $avatarPreviewData,
        'capture_preview' => $capturePreviewData,
    ]);
}

$verifyJson = json_decode($verifyResponse['body'], true);
if (!is_array($verifyJson)) {
    jsonResponse(502, ['error' => 'Compreface returned an invalid JSON response']);
}

$similarity = extractJsonScore($verifyJson);
$verified = $similarity !== null && $similarity >= $threshold;

jsonResponse(200, [
    'success' => true,
    'verified' => $verified,
    'message' => $verified ? 'Photo verified' : 'Photo not verified',
    'username' => $username,
    'threshold' => $threshold,
    'similarity' => $similarity,
    'avatar_storage_path' => $avatarStoragePath,
    'avatar_url' => $avatarPublicUrl,
    'esp32_capture_url' => $espCaptureUrl,
    'verify_attempt' => $verifyAttempt,
    'avatar_preview' => $avatarPreviewData,
    'capture_preview' => $capturePreviewData,
    'compreface_response' => $verifyJson,
]);
