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

$STRIPE_SECRET_KEY = $_ENV['STRIPE_SECRET_KEY'] ?? '';
$SUPABASE_URL = $_ENV['SUPABASE_URL'] ?? '';
$SUPABASE_SERVICE_KEY = $_ENV['SUPABASE_SERVICE_KEY'] ?? '';

// Check if Stripe is configured
if (empty($STRIPE_SECRET_KEY)) {
    http_response_code(500);
    echo json_encode(['error' => 'Stripe is not configured. Please add STRIPE_SECRET_KEY to .env file.']);
    exit;
}

// Load Stripe PHP library
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

// Validate required fields
$required_fields = ['penalty_id', 'amount', 'user_id', 'violation_type', 'pod_number'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || $input[$field] === '' || $input[$field] === null) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit;
    }
}

$penaltyId = $input['penalty_id'];
$amount = floatval($input['amount']);
$userId = $input['user_id'];
$violationType = $input['violation_type'];
$podNumber = $input['pod_number'];

// Validate amount
if ($amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Amount must be greater than 0']);
    exit;
}

// Initialize Stripe
\Stripe\Stripe::setApiKey($STRIPE_SECRET_KEY);

// Get base URL for success/cancel URLs
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . '://' . $host;
$basePath = dirname($_SERVER['SCRIPT_NAME']);

// Create Stripe Checkout Session
try {
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => 'Penalty Payment - ' . $violationType,
                    'description' => 'Pod ' . $podNumber . ' - ' . $violationType . ' Penalty',
                ],
                'unit_amount' => round($amount * 100), // Convert to cents
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => $baseUrl . $basePath . '/stripe_success.php?session_id={CHECKOUT_SESSION_ID}&penalty_id=' . urlencode($penaltyId),
        'cancel_url' => $baseUrl . $basePath . '/penalties.php?canceled=true',
        'client_reference_id' => $penaltyId, // Store penalty ID for reference
        'metadata' => [
            'penalty_id' => $penaltyId,
            'user_id' => $userId,
            'violation_type' => $violationType,
            'pod_number' => $podNumber,
        ],
    ]);

    echo json_encode([
        'success' => true,
        'sessionId' => $session->id,
        'url' => $session->url
    ]);
} catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to create Stripe session',
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'An error occurred',
        'message' => $e->getMessage()
    ]);
}
?>

