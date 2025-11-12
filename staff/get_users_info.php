<?php
// Get user information from Supabase Auth based on user IDs
// Uses service key to query Auth Admin API

// Load env
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!$SUPABASE_URL || !$SUPABASE_SERVICE_KEY) {
    http_response_code(500);
    echo json_encode(['error' => 'Server not configured', 'supabase_url_set' => !empty($SUPABASE_URL), 'service_key_set' => !empty($SUPABASE_SERVICE_KEY)]);
    exit;
}

// Get user IDs from POST data
$input = json_decode(file_get_contents('php://input'), true);
$userIds = isset($input['user_ids']) && is_array($input['user_ids']) ? $input['user_ids'] : [];

if (empty($userIds)) {
    echo json_encode(['users' => [], 'error' => 'No user IDs provided']);
    exit;
}

// Helper function to extract user info from user object
function extractUserInfo($user) {
    $userId = $user['id'] ?? '';
    
    // Try multiple ways to get user_metadata (different Supabase versions/formats)
    $userMetadata = [];
    if (isset($user['user_metadata']) && is_array($user['user_metadata'])) {
        $userMetadata = $user['user_metadata'];
    } elseif (isset($user['raw_user_meta_data']) && is_array($user['raw_user_meta_data'])) {
        $userMetadata = $user['raw_user_meta_data'];
    } elseif (isset($user['app_metadata']) && is_array($user['app_metadata'])) {
        $userMetadata = $user['app_metadata'];
    }
    
    $email = $user['email'] ?? '';
    
    // Get username from user_metadata - check multiple possible keys
    $username = '';
    if (!empty($userMetadata['username'])) {
        $username = trim($userMetadata['username']);
    } elseif (!empty($userMetadata['name'])) {
        $username = trim($userMetadata['name']);
    } elseif (!empty($userMetadata['full_name'])) {
        $username = trim($userMetadata['full_name']);
    }
    
    // If still no username, try email prefix
    if (empty($username) && !empty($email)) {
        $username = explode('@', $email)[0];
    }
    
    // Final fallback
    if (empty($username)) {
        $username = 'User ' . substr($userId, 0, 8);
    }
    
    return [
        'id' => $userId,
        'username' => $username,
        'email' => $email,
        'metadata' => $userMetadata
    ];
}

// Query Supabase Auth Admin API to get user information
$usersMap = [];
$errors = [];
$debugInfo = [
    'requested_count' => count($userIds),
    'requested_ids' => $userIds,
    'supabase_url' => $SUPABASE_URL ? (substr($SUPABASE_URL, 0, 30) . '...') : 'NOT SET',
    'service_key_set' => !empty($SUPABASE_SERVICE_KEY),
    'strategy' => 'individual_queries'
];

$userIdsSet = array_flip($userIds); // For faster lookup

// Strategy 1: Query users individually by ID
$individualSuccessCount = 0;
foreach ($userIds as $userId) {
    // Supabase Auth Admin API endpoint: /auth/v1/admin/users/{user_id}
    $url = rtrim($SUPABASE_URL, '/') . '/auth/v1/admin/users/' . urlencode($userId);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $SUPABASE_SERVICE_KEY,
        'Authorization: Bearer ' . $SUPABASE_SERVICE_KEY,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_VERBOSE, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    curl_close($ch);
    
    $queryDebug = [
        'user_id' => $userId,
        'url' => $url,
        'http_code' => $httpCode,
        'curl_error' => $curlError,
        'curl_errno' => $curlErrno,
        'response_length' => strlen($response)
    ];
    
    if ($curlErrno !== 0) {
        $errors[] = "CURL error for user $userId: $curlError (errno: $curlErrno)";
        $queryDebug['error'] = 'CURL error';
        $debugInfo['queries'][] = $queryDebug;
        continue;
    }
    
    if ($httpCode === 200) {
        $user = json_decode($response, true);
        $jsonError = json_last_error();
        
        if ($jsonError !== JSON_ERROR_NONE) {
            $errors[] = "JSON decode error for user $userId: " . json_last_error_msg();
            $queryDebug['json_error'] = json_last_error_msg();
            $queryDebug['response_preview'] = substr($response, 0, 200);
            $debugInfo['queries'][] = $queryDebug;
            continue;
        }
        
        if (isset($user['id'])) {
            $usersMap[$userId] = extractUserInfo($user);
            $queryDebug['success'] = true;
            $queryDebug['username'] = $usersMap[$userId]['username'];
            $queryDebug['email'] = $usersMap[$userId]['email'];
            $individualSuccessCount++;
        } else {
            $errors[] = "User $userId: Response does not contain 'id' field";
            $queryDebug['response'] = $user;
        }
    } elseif ($httpCode === 404) {
        $errors[] = "User $userId not found (404)";
        $queryDebug['error'] = 'User not found';
    } else {
        // Try to parse error response
        $errorData = json_decode($response, true);
        $errorMessage = isset($errorData['message']) ? $errorData['message'] : (isset($errorData['error']) ? $errorData['error'] : $response);
        $errors[] = "HTTP $httpCode for user $userId: $errorMessage";
        $queryDebug['error'] = "HTTP $httpCode";
        $queryDebug['error_response'] = $errorData ? $errorData : substr($response, 0, 200);
    }
    
    $debugInfo['queries'][] = $queryDebug;
}

// Strategy 2: If individual queries failed, try fetching all users and filtering
if ($individualSuccessCount === 0 && count($userIds) > 0) {
    $debugInfo['fallback_strategy'] = 'fetch_all_users';
    
    $url = rtrim($SUPABASE_URL, '/') . '/auth/v1/admin/users';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $SUPABASE_SERVICE_KEY,
        'Authorization: Bearer ' . $SUPABASE_SERVICE_KEY,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    curl_close($ch);
    
    $debugInfo['fallback_query'] = [
        'url' => $url,
        'http_code' => $httpCode,
        'curl_error' => $curlError,
        'curl_errno' => $curlErrno,
        'response_length' => strlen($response)
    ];
    
    if ($httpCode === 200 && $curlErrno === 0) {
        $usersData = json_decode($response, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            // Handle different response formats
            $userList = [];
            if (is_array($usersData)) {
                if (isset($usersData[0]) && isset($usersData[0]['id'])) {
                    $userList = $usersData;
                } elseif (isset($usersData['users']) && is_array($usersData['users'])) {
                    $userList = $usersData['users'];
                } elseif (isset($usersData['data']) && is_array($usersData['data'])) {
                    $userList = $usersData['data'];
                }
            }
            
            $debugInfo['fallback_query']['users_found'] = count($userList);
            
            foreach ($userList as $user) {
                $userId = $user['id'] ?? null;
                if ($userId && isset($userIdsSet[$userId])) {
                    $usersMap[$userId] = extractUserInfo($user);
                }
            }
            
            $debugInfo['fallback_query']['matched_count'] = count($usersMap);
        } else {
            $debugInfo['fallback_query']['json_error'] = json_last_error_msg();
        }
    } else {
        $debugInfo['fallback_query']['error'] = "HTTP $httpCode" . ($curlError ? " - $curlError" : "");
    }
}

$debugInfo['found_count'] = count($usersMap);
$debugInfo['found_ids'] = array_keys($usersMap);
$debugInfo['errors'] = $errors;

// Return users map with debug info
$response = [
    'users' => $usersMap,
    'debug' => $debugInfo
];

if (!empty($errors)) {
    $response['errors'] = $errors;
}

echo json_encode($response, JSON_PRETTY_PRINT);
exit;
?>
