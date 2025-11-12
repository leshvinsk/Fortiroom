<?php
// Secure server-side upload to Supabase Storage using service role key
// Expects multipart/form-data with field 'file'

// Load env
$envPath = __DIR__ . DIRECTORY_SEPARATOR . '.env';
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
$AVATARS_BUCKET = $_ENV['AVATARS_BUCKET'] ?? 'avatars';
$STORAGE_ENDPOINT = $_ENV['STORAGE_ENDPOINT'] ?? ''; // optional S3-compatible endpoint (not required for REST)

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
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'File is required']);
    exit;
}

$bucket = $AVATARS_BUCKET; // Ensure this bucket exists and is public for reads

// Get username from form data
$username = $_POST['username'] ?? '';
if (empty($username)) {
    http_response_code(400);
    echo json_encode(['error' => 'Username is required']);
    exit;
}

// Sanitize username for use in path (only alphanumeric, underscore, hyphen)
$safeUsername = preg_replace('/[^a-zA-Z0-9_-]/', '_', $username);
$safeUsername = strtolower($safeUsername); // Make it lowercase for consistency

$originalName = $_FILES['file']['name'] ?? 'avatar';
$ext = pathinfo($originalName, PATHINFO_EXTENSION);
$safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
$unique = bin2hex(random_bytes(8));
// Store under username folder: username/filename_unique.ext
$objectPath = $safeUsername . '/' . $safeBase . '_' . $unique . ($ext ? ('.' . $ext) : '');
$storageUrl = rtrim($SUPABASE_URL, '/') . '/storage/v1/object/' . rawurlencode($bucket) . '/' . $objectPath;

$fileData = file_get_contents($_FILES['file']['tmp_name']);
if ($fileData === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to read upload']);
    exit;
}

$ch = curl_init($storageUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $fileData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $SUPABASE_SERVICE_KEY,
    'apikey: ' . $SUPABASE_SERVICE_KEY,
    'Content-Type: application/octet-stream',
    'x-upsert: true'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

if ($code < 200 || $code >= 300) {
    http_response_code(500);
    echo json_encode(['error' => 'Supabase upload failed (HTTP ' . $code . ')' , 'detail' => substr((string)$resp, 0, 300)]);
    exit;
}

// If the bucket is public, construct a public URL
$publicUrl = rtrim($SUPABASE_URL, '/') . '/storage/v1/object/public/' . $bucket . '/' . $objectPath;
echo json_encode([
    'path' => $objectPath,
    'publicUrl' => $publicUrl
]);
exit;
?>

