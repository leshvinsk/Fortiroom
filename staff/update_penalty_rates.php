<?php
// Update penalty rates endpoint
// Uses service key to update penalty rates in Supabase

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
    echo json_encode(['error' => 'Server not configured']);
    exit;
}

// Get penalty rates from POST data
$input = json_decode(file_get_contents('php://input'), true);
$rates = isset($input['rates']) && is_array($input['rates']) ? $input['rates'] : [];

if (empty($rates)) {
    http_response_code(400);
    echo json_encode(['error' => 'No rates provided']);
    exit;
}

// Get user ID from request (optional, for tracking who updated)
$userId = isset($input['user_id']) ? $input['user_id'] : null;

$results = [];
$errors = [];

// Valid violation types
$validViolationTypes = ['Late Cancellation', 'No Show', 'Late Checkout'];

foreach ($rates as $rate) {
    $violationType = isset($rate['violation_type']) ? $rate['violation_type'] : '';
    $penaltyAmount = isset($rate['penalty_amount']) ? $rate['penalty_amount'] : null;
    
    // Validate violation type
    if (!in_array($violationType, $validViolationTypes)) {
        $errors[] = "Invalid violation type: $violationType";
        continue;
    }
    
    // Validate penalty amount
    if ($penaltyAmount === null || $penaltyAmount === '') {
        continue; // Skip if not provided
    }
    
    $penaltyAmount = floatval($penaltyAmount);
    if ($penaltyAmount < 0) {
        $errors[] = "Invalid penalty amount for $violationType: $penaltyAmount";
        continue;
    }
    
    // Prepare data for upsert
    $data = [
        'violation_type' => $violationType,
        'penalty_amount' => $penaltyAmount,
        'updated_at' => date('c') // ISO 8601 format
    ];
    
    if ($userId) {
        $data['updated_by'] = $userId;
    }
    
    // Use UPSERT: First try to update, if no rows affected, then insert
    // This works better with Supabase's unique constraint on violation_type
    $url = $SUPABASE_URL . '/rest/v1/penalty_rates';
    
    // Step 1: Try to update existing record
    $updateUrl = $url . '?violation_type=eq.' . urlencode($violationType);
    $updateData = [
        'penalty_amount' => $penaltyAmount,
        'updated_at' => date('c')
    ];
    if ($userId) {
        $updateData['updated_by'] = $userId;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $updateUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updateData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'apikey: ' . $SUPABASE_SERVICE_KEY,
        'Authorization: Bearer ' . $SUPABASE_SERVICE_KEY,
        'Prefer: return=representation'
    ]);
    
    $updateResponse = curl_exec($ch);
    $updateHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $updateCurlError = curl_error($ch);
    curl_close($ch);
    
    if ($updateCurlError) {
        $errors[] = "cURL error updating penalty rate for $violationType: $updateCurlError";
        error_log("cURL error updating penalty rate for $violationType: $updateCurlError");
        continue;
    }
    
    $updateResponseData = json_decode($updateResponse, true);
    $wasUpdated = ($updateHttpCode === 200 || $updateHttpCode === 204) && !empty($updateResponseData);
    
    if ($wasUpdated) {
        // Successfully updated existing record
        $results[] = [
            'violation_type' => $violationType,
            'penalty_amount' => $penaltyAmount,
            'status' => 'updated'
        ];
        error_log("Successfully updated penalty rate for $violationType: $penaltyAmount");
    } else {
        // Record doesn't exist, insert it
        $insertData = [
            'violation_type' => $violationType,
            'penalty_amount' => $penaltyAmount
        ];
        if ($userId) {
            $insertData['updated_by'] = $userId;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($insertData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'apikey: ' . $SUPABASE_SERVICE_KEY,
            'Authorization: Bearer ' . $SUPABASE_SERVICE_KEY,
            'Prefer: return=representation'
        ]);
        
        $insertResponse = curl_exec($ch);
        $insertHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $insertCurlError = curl_error($ch);
        curl_close($ch);
        
        if ($insertCurlError) {
            $errors[] = "cURL error inserting penalty rate for $violationType: $insertCurlError";
            error_log("cURL error inserting penalty rate for $violationType: $insertCurlError");
        } else if ($insertHttpCode === 201 || $insertHttpCode === 200) {
            $insertResponseData = json_decode($insertResponse, true);
            if (!empty($insertResponseData)) {
                $results[] = [
                    'violation_type' => $violationType,
                    'penalty_amount' => $penaltyAmount,
                    'status' => 'created'
                ];
                error_log("Successfully created penalty rate for $violationType: $penaltyAmount");
            } else {
                $errors[] = "Failed to create penalty rate for $violationType: Empty response";
                error_log("Failed to create penalty rate for $violationType: Empty response - HTTP $insertHttpCode");
            }
        } else {
            $errors[] = "Failed to insert penalty rate for $violationType: HTTP $insertHttpCode";
            error_log("Failed to insert penalty rate for $violationType: HTTP $insertHttpCode - $insertResponse");
        }
    }
}

if (!empty($errors)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'errors' => $errors,
        'results' => $results
    ]);
} else {
    echo json_encode([
        'success' => true,
        'message' => 'Penalty rates updated successfully',
        'results' => $results
    ]);
}

