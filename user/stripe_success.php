<?php
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

$STRIPE_SECRET_KEY = $_ENV['STRIPE_SECRET_KEY'] ?? '';
$SUPABASE_URL = $_ENV['SUPABASE_URL'] ?? '';
$SUPABASE_SERVICE_KEY = $_ENV['SUPABASE_SERVICE_KEY'] ?? '';

// Load Stripe PHP library
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// Initialize Stripe
\Stripe\Stripe::setApiKey($STRIPE_SECRET_KEY);

// Get session ID and penalty ID from URL
$sessionId = $_GET['session_id'] ?? '';
$penaltyId = $_GET['penalty_id'] ?? '';

if (empty($sessionId) || empty($penaltyId)) {
    // Redirect to penalties page with error
    header('Location: penalties.php?payment=error&message=' . urlencode('Missing session or penalty information'));
    exit;
}

try {
    // Retrieve the Stripe session to verify payment
    $session = \Stripe\Checkout\Session::retrieve($sessionId);
    
    // Check if payment was successful
    if ($session->payment_status === 'paid') {
        // Generate receipt number
        $receiptNumber = 'RCP-' . date('Ymd') . '-' . strtoupper(substr(md5($penaltyId), 0, 8));
        $paidAt = date('Y-m-d\TH:i:s\Z');
        
        // Update penalty status in Supabase
        $ch = curl_init($SUPABASE_URL . '/rest/v1/penalties?id=eq.' . urlencode($penaltyId));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'apikey: ' . $SUPABASE_SERVICE_KEY,
            'Authorization: Bearer ' . $SUPABASE_SERVICE_KEY,
            'Prefer: return=representation'
        ]);
        
        $updateData = [
            'status' => 'paid',
            'receipt_number' => $receiptNumber,
            'paid_at' => $paidAt
        ];
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updateData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            // Payment successful - redirect to penalties page with success message
            header('Location: penalties.php?payment=success&penalty_id=' . urlencode($penaltyId));
        } else {
            // Update failed but payment was successful - log error
            error_log('Failed to update penalty status after Stripe payment. Penalty ID: ' . $penaltyId . ', HTTP Code: ' . $httpCode);
            header('Location: penalties.php?payment=success&penalty_id=' . urlencode($penaltyId) . '&warning=update_failed');
        }
    } else {
        // Payment not completed
        header('Location: penalties.php?payment=error&message=' . urlencode('Payment was not completed'));
    }
} catch (\Stripe\Exception\ApiErrorException $e) {
    error_log('Stripe API error: ' . $e->getMessage());
    header('Location: penalties.php?payment=error&message=' . urlencode('Failed to verify payment'));
} catch (Exception $e) {
    error_log('Error processing payment: ' . $e->getMessage());
    header('Location: penalties.php?payment=error&message=' . urlencode('An error occurred'));
}
exit;
?>

