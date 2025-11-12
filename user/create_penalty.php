<?php
header('Content-Type: application/json');

// Minimal .env loader (no external deps). Loads KEY=VALUE pairs into $_ENV.
$envPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(ltrim($line), '#') === 0) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            // Remove optional surrounding quotes
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

$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

// Required fields (booking_id and pod_id are optional)
$required_fields = ['user_id', 'violation_type', 'penalty_amount', 'violation_date'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || $input[$field] === '' || $input[$field] === null) {
        echo json_encode(['error' => "Missing required field: $field"]);
        exit;
    }
}

$user_id = $input['user_id'];
// Handle booking_id - it should be a valid UUID string if provided
$booking_id = null;
if (isset($input['booking_id'])) {
    $booking_id_raw = trim($input['booking_id']);
    // Validate UUID format (basic check - 8-4-4-4-12 hex digits)
    $uuid_pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
    if ($booking_id_raw !== '' && $booking_id_raw !== 'null' && $booking_id_raw !== null) {
        if (preg_match($uuid_pattern, $booking_id_raw)) {
            $booking_id = $booking_id_raw;
        } else {
            // Log invalid UUID format but don't fail - just set to null
            error_log('Invalid booking_id UUID format: ' . $booking_id_raw);
        }
    }
}
// Handle pod_id - it should be a valid UUID string if provided
$pod_id = null;
if (isset($input['pod_id'])) {
    $pod_id_raw = trim($input['pod_id']);
    // Validate UUID format (basic check - 8-4-4-4-12 hex digits)
    $uuid_pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
    if ($pod_id_raw !== '' && $pod_id_raw !== 'null' && $pod_id_raw !== null) {
        if (preg_match($uuid_pattern, $pod_id_raw)) {
            $pod_id = $pod_id_raw;
        } else {
            // Log invalid UUID format but don't fail - just set to null
            error_log('Invalid pod_id UUID format: ' . $pod_id_raw);
        }
    }
}
$violation_type = $input['violation_type'];
$penalty_amount = floatval($input['penalty_amount']);
$violation_date = $input['violation_date'];
// Handle violation_time
$violation_time = null;
if (isset($input['violation_time'])) {
    $violation_time_raw = trim($input['violation_time']);
    if ($violation_time_raw !== '' && $violation_time_raw !== 'null' && $violation_time_raw !== null) {
        $violation_time = $violation_time_raw;
    }
}

// Validate violation type
$allowed_violation_types = ['Late Cancellation', 'No Show', 'Late Checkout'];
if (!in_array($violation_type, $allowed_violation_types)) {
    echo json_encode(['error' => 'Invalid violation type']);
    exit;
}

// Validate penalty amount
if ($penalty_amount < 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Penalty amount must be non-negative']);
    exit;
}

// Get the rate_id from penalty_rates table for this violation_type
// This links the penalty to the rate that was used when it was created
$rate_id = null;
$rateFetchSuccess = false;

error_log("Attempting to fetch penalty rate for violation_type: $violation_type");

$ch = curl_init($SUPABASE_URL . '/rest/v1/penalty_rates?violation_type=eq.' . urlencode($violation_type) . '&select=id,penalty_amount');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 second timeout
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'apikey: ' . $SUPABASE_SERVICE_KEY,
    'Authorization: Bearer ' . $SUPABASE_SERVICE_KEY
]);

$rateResponse = curl_exec($ch);
$rateHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$rateCurlError = curl_error($ch);
curl_close($ch);

if (!$rateCurlError && $rateHttpCode === 200) {
    $rateData = json_decode($rateResponse, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("ERROR: Failed to decode rate response JSON: " . json_last_error_msg());
        error_log("Rate response: $rateResponse");
    } else if (!empty($rateData) && isset($rateData[0]) && isset($rateData[0]['id'])) {
        $rate_id = $rateData[0]['id'];
        // ALWAYS use the rate from the database - this ensures new penalties use current rates
        // Old penalties retain their original rate via rate_id
        $penalty_amount = floatval($rateData[0]['penalty_amount']);
        $rateFetchSuccess = true;
        error_log("SUCCESS: Found rate_id for violation_type $violation_type: $rate_id, using amount: $penalty_amount");
    } else {
        error_log("WARNING: No rate found in database for violation_type: $violation_type");
        error_log("Rate response data: " . json_encode($rateData));
        error_log("Rate HTTP code: $rateHttpCode");
        error_log("Using provided amount as fallback: $penalty_amount");
        // If no rate found in database, use the provided amount (fallback for edge cases)
        // But log a warning so we know the penalty_rates table might be empty
    }
} else {
    error_log("ERROR: Failed to fetch rate for violation_type $violation_type: HTTP $rateHttpCode");
    if ($rateCurlError) {
        error_log("cURL error: $rateCurlError");
    }
    error_log("Rate response: $rateResponse");
    error_log("Using provided amount as fallback: $penalty_amount");
    // If rate fetch fails, use the provided amount (fallback for edge cases)
    // This ensures penalties can still be created even if rate table has issues
}

// Log final penalty amount that will be used
error_log("Final penalty amount to be used: $penalty_amount");

// Create penalty using Supabase service key (bypasses RLS)
$ch = curl_init($SUPABASE_URL . '/rest/v1/penalties');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'apikey: ' . $SUPABASE_SERVICE_KEY,
    'Authorization: Bearer ' . $SUPABASE_SERVICE_KEY,
    'Prefer: return=representation'
]);
// Build payload - always include booking_id and pod_id if they were provided
$payload = [
    'user_id' => $user_id,
    'violation_type' => $violation_type,
    'penalty_amount' => $penalty_amount,
    'violation_date' => $violation_date,
    'status' => 'pending'
];

// Add optional fields
// Note: Supabase PostgREST requires that all columns exist in the table schema
// If a column doesn't exist, it will return a 400 error
if ($violation_time !== null && $violation_time !== '') {
    $payload['violation_time'] = $violation_time;
}

// Add foreign key fields only if they are valid UUIDs and not empty
// We need to be careful here - only include if the column exists in the schema
if ($booking_id !== null && $booking_id !== '') {
    // Validate it's a valid UUID format before adding
    if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $booking_id)) {
        $payload['booking_id'] = $booking_id;
        error_log("Including booking_id in penalty payload: $booking_id");
    } else {
        error_log("WARNING: Invalid booking_id format, skipping: $booking_id");
    }
}

if ($pod_id !== null && $pod_id !== '') {
    // Validate it's a valid UUID format before adding
    if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $pod_id)) {
        $payload['pod_id'] = $pod_id;
        error_log("Including pod_id in penalty payload: $pod_id");
    } else {
        error_log("WARNING: Invalid pod_id format, skipping: $pod_id");
    }
}

// Add rate_id if we found it - this links the penalty to the rate that was used
if ($rate_id !== null && $rate_id !== '') {
    // Validate it's a valid UUID format before adding
    if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $rate_id)) {
        $payload['rate_id'] = $rate_id;
        error_log("Including rate_id in penalty payload: $rate_id");
    } else {
        error_log("WARNING: Invalid rate_id format, skipping: $rate_id");
    }
}

// Log payload for debugging
error_log('Creating penalty with payload: ' . json_encode($payload));
error_log('Payload contains booking_id: ' . (isset($payload['booking_id']) ? 'YES - ' . $payload['booking_id'] : 'NO'));
error_log('Payload contains pod_id: ' . (isset($payload['pod_id']) ? 'YES - ' . $payload['pod_id'] : 'NO'));
error_log('Payload contains rate_id: ' . (isset($payload['rate_id']) ? 'YES - ' . $payload['rate_id'] : 'NO'));

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 second timeout

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    http_response_code(500);
    error_log('ERROR: cURL error creating penalty: ' . $curl_error);
    echo json_encode(['error' => 'Failed to create penalty: ' . $curl_error]);
    exit;
}

// Parse response
$responseData = null;
$responseError = null;

// Try to decode response as JSON
if (!empty($response)) {
    $responseData = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $responseError = json_last_error_msg();
        error_log('WARNING: Could not decode response as JSON: ' . $responseError);
    }
}

// Check for specific error about missing booking_id column
if ($http_code === 400) {
    $errorMessage = '';
    if ($responseData && isset($responseData['message'])) {
        $errorMessage = $responseData['message'];
    } else if ($responseData && isset($responseData['error'])) {
        $errorMessage = $responseData['error'];
    } else {
        $errorMessage = $response;
    }
    
    error_log('ERROR: Supabase returned 400 error');
    error_log('Error message: ' . $errorMessage);
    error_log('Full response: ' . $response);
    
    // Check if it's a schema/cache error about booking_id
    if (stripos($errorMessage, "booking_id") !== false || stripos($errorMessage, "schema cache") !== false || stripos($errorMessage, "Could not find") !== false) {
        error_log('ERROR: booking_id column does not exist in penalties table or schema cache is stale');
        http_response_code(400);
        echo json_encode([
            'error' => 'Database schema error: booking_id column missing from penalties table',
            'details' => 'The penalties table is missing the booking_id column. Please run add_booking_id_to_penalties.sql in Supabase SQL Editor to add it.',
            'supabase_error' => $responseData ? $responseData : ['raw_response' => $response],
            'solution' => 'Run: add_booking_id_to_penalties.sql in Supabase SQL Editor',
            'http_code' => $http_code
        ]);
        exit;
    }
    
    // Check for other missing columns
    if (stripos($errorMessage, "rate_id") !== false) {
        error_log('ERROR: rate_id column does not exist in penalties table');
        http_response_code(400);
        echo json_encode([
            'error' => 'Database schema error: rate_id column missing from penalties table',
            'details' => 'Please run add_penalty_rate_connection.sql in Supabase SQL Editor to add the missing column.',
            'supabase_error' => $responseData ? $responseData : ['raw_response' => $response]
        ]);
        exit;
    }
    
    // Generic 400 error
    http_response_code(400);
    echo json_encode([
        'error' => 'Failed to create penalty: HTTP 400',
        'details' => $errorMessage,
        'supabase_error' => $responseData ? $responseData : ['raw_response' => $response],
        'http_code' => $http_code
    ]);
    exit;
}

if ($http_code >= 200 && $http_code < 300) {
    if ($responseError) {
        error_log('ERROR: Failed to decode Supabase response: ' . $responseError);
        error_log('Raw response: ' . $response);
        http_response_code(500);
        echo json_encode(['error' => 'Failed to decode response: ' . $responseError, 'raw_response' => $response]);
        exit;
    }
    
    $result = $responseData;
    if ($result === null) {
        error_log('ERROR: Response data is null');
        error_log('Raw response: ' . $response);
        http_response_code(500);
        echo json_encode(['error' => 'Invalid response from server', 'raw_response' => $response]);
        exit;
    }
    // Log the response for debugging
    error_log('SUCCESS: Penalty created successfully');
    error_log('Supabase response: ' . json_encode($result));
    if (isset($result[0])) {
        error_log('Penalty created with ID: ' . ($result[0]['id'] ?? 'NULL'));
        error_log('Penalty booking_id: ' . (isset($result[0]['booking_id']) ? $result[0]['booking_id'] : 'NULL'));
        error_log('Penalty amount: ' . (isset($result[0]['penalty_amount']) ? $result[0]['penalty_amount'] : 'NULL'));
        error_log('Penalty rate_id: ' . (isset($result[0]['rate_id']) ? $result[0]['rate_id'] : 'NULL'));
        echo json_encode(['success' => true, 'penalty' => $result[0]]);
    } else if (isset($result['id'])) {
        error_log('Penalty created with ID: ' . $result['id']);
        error_log('Penalty booking_id: ' . (isset($result['booking_id']) ? $result['booking_id'] : 'NULL'));
        error_log('Penalty amount: ' . (isset($result['penalty_amount']) ? $result['penalty_amount'] : 'NULL'));
        error_log('Penalty rate_id: ' . (isset($result['rate_id']) ? $result['rate_id'] : 'NULL'));
        echo json_encode(['success' => true, 'penalty' => $result]);
    } else {
        error_log('WARNING: Unexpected response format from Supabase');
        error_log('Response: ' . json_encode($result));
        echo json_encode(['success' => true, 'penalty' => $result, 'warning' => 'Unexpected response format']);
    }
} else {
    // Log the error response for debugging
    error_log('ERROR: Failed to create penalty: HTTP ' . $http_code);
    error_log('Error response: ' . $response);
    error_log('Payload that was sent: ' . json_encode($payload));
    echo json_encode(['error' => 'Failed to create penalty: HTTP ' . $http_code, 'details' => $response]);
}
?>

