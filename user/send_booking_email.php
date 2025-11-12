<?php
header('Content-Type: application/json');

// Load environment variables
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
            $len = strlen($value);
            if ($len >= 2 && (($value[0] === '"' && $value[$len - 1] === '"') || ($value[0] === "'" && $value[$len - 1] === "'"))) {
                $value = substr($value, 1, $len - 2);
            }
            $_ENV[$key] = $value;
        }
    }
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

// Extract booking details
$bookingId = $data['booking_id'] ?? '';
$userEmail = $data['user_email'] ?? '';
$userName = $data['user_name'] ?? '';
$podName = $data['pod_name'] ?? '';
$bookingDate = $data['booking_date'] ?? '';
$checkInTime = $data['check_in_time'] ?? '';
$checkOutTime = $data['check_out_time'] ?? '';
$numberOfPeople = $data['number_of_people'] ?? 1;

// Validate required fields
if (empty($bookingId) || empty($userEmail) || empty($podName) || empty($bookingDate) || empty($checkInTime) || empty($checkOutTime)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Format date for display
$dateObj = new DateTime($bookingDate);
$formattedDate = $dateObj->format('F j, Y');

// Convert 24-hour time to 12-hour format
function convertTo12Hour($time24) {
    $parts = explode(':', $time24);
    $hour = (int)$parts[0];
    $minute = $parts[1];
    $period = $hour >= 12 ? 'PM' : 'AM';
    $hour12 = $hour % 12;
    if ($hour12 === 0) {
        $hour12 = 12;
    }
    return sprintf('%d:%s %s', $hour12, $minute, $period);
}

$checkInFormatted = convertTo12Hour($checkInTime);
$checkOutFormatted = convertTo12Hour($checkOutTime);

// Calculate duration
$checkInParts = explode(':', $checkInTime);
$checkOutParts = explode(':', $checkOutTime);
$checkInMinutes = (int)$checkInParts[0] * 60 + (int)$checkInParts[1];
$checkOutMinutes = (int)$checkOutParts[0] * 60 + (int)$checkOutParts[1];
$durationMinutes = $checkOutMinutes - $checkInMinutes;
$hours = floor($durationMinutes / 60);
$minutes = $durationMinutes % 60;
$duration = $hours . ' hour' . ($hours > 1 ? 's' : '');
if ($minutes > 0) {
    $duration .= ' ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '');
}

// Email subject
$subject = 'Booking Confirmation - FORTIROOM';

// Email body (HTML format)
$emailBody = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .booking-details {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: bold;
            color: #666;
        }
        .detail-value {
            color: #333;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }
        .success-badge {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>FORTIROOM</h1>
        <p>Smart Space Management System</p>
    </div>
    <div class="content">
        <h2>Booking Confirmation</h2>
        <p>Dear ' . htmlspecialchars($userName) . ',</p>
        <p>Your booking has been confirmed successfully!</p>
        
        <div class="success-badge">✓ Booking Confirmed</div>
        
        <div class="booking-details">
            <h3>Booking Details</h3>
            <div class="detail-row">
                <span class="detail-label">Booking ID:</span>
                <span class="detail-value">' . htmlspecialchars($bookingId) . '</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date:</span>
                <span class="detail-value">' . htmlspecialchars($formattedDate) . '</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Pod:</span>
                <span class="detail-value">' . htmlspecialchars($podName) . '</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Check-In Time:</span>
                <span class="detail-value">' . htmlspecialchars($checkInFormatted) . '</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Check-Out Time:</span>
                <span class="detail-value">' . htmlspecialchars($checkOutFormatted) . '</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Duration:</span>
                <span class="detail-value">' . htmlspecialchars($duration) . '</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Number of People:</span>
                <span class="detail-value">' . htmlspecialchars($numberOfPeople) . ' ' . ($numberOfPeople === 1 ? 'person' : 'people') . '</span>
            </div>
        </div>
        
        <p><strong>Important Reminders:</strong></p>
        <ul>
            <li>Please arrive on time for your booking.</li>
            <li>You can check in 15 minutes before your scheduled check-in time.</li>
            <li>Please check out on time to avoid penalties.</li>
            <li>If you need to cancel, please do so at least 1 hour before your booking starts to avoid cancellation charges.</li>
        </ul>
        
        <p>Thank you for using FORTIROOM!</p>
        
        <p>Best regards,<br>FORTIROOM Team</p>
    </div>
    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>&copy; ' . date('Y') . ' FORTIROOM. All rights reserved.</p>
    </div>
</body>
</html>
';

// Email headers
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: FORTIROOM <noreply@fortiroom.com>\r\n";
$headers .= "Reply-To: noreply@fortiroom.com\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Send email
$mailSent = mail($userEmail, $subject, $emailBody, $headers);

if ($mailSent) {
    echo json_encode([
        'success' => true,
        'message' => 'Email sent successfully'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to send email'
    ]);
}
?>

