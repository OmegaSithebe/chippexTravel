<?php
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'chippyzr_chippexUser');
define('DB_PASSWORD', 'chipexTravelDev@24!');
define('DB_NAME', 'chippyzr_chippex');

// Email configuration
define('ADMIN_EMAIL', 'admin@chippexstravel.co.za');
define('NOREPLY_EMAIL', 'noreply@chippexstravel.co.za');

header('Content-Type: application/json');

// Log the request for debugging
file_put_contents('payment_debug.log', date('Y-m-d H:i:s') . " - Payment success called\n", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'error' => 'No input data received']);
        exit;
    }
    
    // Create database connection
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if (!$conn) {
        echo json_encode(['success' => false, 'error' => 'Database connection failed']);
        exit;
    }
    
    $bookingId = mysqli_real_escape_string($conn, $input['bookingId'] ?? '');
    $transactionId = mysqli_real_escape_string($conn, $input['transactionId'] ?? '');
    $payerEmail = mysqli_real_escape_string($conn, $input['payerEmail'] ?? '');
    $payerName = mysqli_real_escape_string($conn, $input['payerName'] ?? '');
    $amountUSD = mysqli_real_escape_string($conn, $input['amount'] ?? '');
    $amountZAR = mysqli_real_escape_string($conn, $input['amountZAR'] ?? '');
    $currency = mysqli_real_escape_string($conn, $input['currency'] ?? 'USD');
    $status = mysqli_real_escape_string($conn, $input['status'] ?? '');
    
    // Update booking with payment information
    $sql = "UPDATE nandoni_bookings SET 
            payment_status = 'paid',
            payment_amount = ?,
            paypal_transaction_id = ?,
            payment_date = NOW()
            WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Prepare statement failed: ' . mysqli_error($conn)]);
        mysqli_close($conn);
        exit;
    }
    
    mysqli_stmt_bind_param($stmt, "dsi", $amountZAR, $transactionId, $bookingId);
    
    if (mysqli_stmt_execute($stmt)) {
        // Get booking details for email
        $bookingSql = "SELECT * FROM nandoni_bookings WHERE id = ?";
        $bookingStmt = mysqli_prepare($conn, $bookingSql);
        mysqli_stmt_bind_param($bookingStmt, "i", $bookingId);
        mysqli_stmt_execute($bookingStmt);
        $result = mysqli_stmt_get_result($bookingStmt);
        $booking = $result->fetch_assoc();
        
        if ($booking) {
            // Send payment confirmation email to guest
            sendPaymentConfirmationEmail($booking, $transactionId, $amountZAR, $amountUSD);
            
            // Send payment notification to admin
            sendPaymentNotificationToAdmin($booking, $transactionId, $amountZAR, $amountUSD, $payerEmail, $payerName);
            
            echo json_encode(['success' => true, 'message' => 'Payment recorded successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Booking not found']);
        }
    } else {
        $error = mysqli_error($conn);
        echo json_encode(['success' => false, 'error' => $error]);
    }
    
    mysqli_close($conn);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

function sendPaymentConfirmationEmail($booking, $transactionId, $amountZAR, $amountUSD) {
    $to = $booking['email'];
    $subject = "Payment Confirmed - Nandoni Waterfront Resort (Booking #{$booking['id']})";
    
    $message = "
    <html>
    <head>
        <title>Payment Confirmation</title>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2ecc71; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .detail { margin: 10px 0; }
            .footer { background: #34495e; color: white; padding: 15px; text-align: center; }
            .highlight { background: #e74c3c; color: white; padding: 5px 10px; border-radius: 3px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Payment Confirmed!</h1>
                <p>Nandoni Waterfront Resort</p>
            </div>
            <div class='content'>
                <h2>Hello {$booking['full_name']},</h2>
                <p>Your payment has been successfully processed and your booking is now confirmed!</p>
                
                <h3>Payment Details:</h3>
                <div class='detail'><strong>Booking Reference:</strong> <span class='highlight'>#{$booking['id']}</span></div>
                <div class='detail'><strong>Transaction ID:</strong> $transactionId</div>
                <div class='detail'><strong>Amount Paid (USD):</strong> $" . number_format($amountUSD, 2) . "</div>
                <div class='detail'><strong>Amount Paid (ZAR):</strong> R" . number_format($amountZAR, 2) . "</div>
                <div class='detail'><strong>Payment Date:</strong> " . date('Y-m-d H:i:s') . "</div>
                
                <h3>Booking Details:</h3>
                <div class='detail'><strong>Accommodation:</strong> {$booking['room_type']}</div>
                <div class='detail'><strong>Check-in:</strong> {$booking['check_in']}</div>
                <div class='detail'><strong>Check-out:</strong> {$booking['check_out']}</div>
                <div class='detail'><strong>Guests:</strong> {$booking['guests']}</div>
                
                <p>Your reservation is now secured. We look forward to welcoming you!</p>
                
                <p>If you have any questions, please contact us at +27 73 474 2034.</p>
            </div>
            <div class='footer'>
                <p>Thank you for choosing Nandoni Waterfront Resort!</p>
                <p>© " . date("Y") . " Chippexs Travel | Luxury Stays at Nandoni Waterfront</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: Nandoni Waterfront Resort <" . NOREPLY_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
    
    mail($to, $subject, $message, $headers);
}

function sendPaymentNotificationToAdmin($booking, $transactionId, $amountZAR, $amountUSD, $payerEmail, $payerName) {
    $to = ADMIN_EMAIL;
    $subject = "Payment Received - Booking #{$booking['id']} - Nandoni Waterfront Resort";
    
    $message = "
    <html>
    <head>
        <title>Payment Received</title>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #27ae60; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .detail { margin: 10px 0; }
            .footer { background: #34495e; color: white; padding: 15px; text-align: center; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Payment Received!</h1>
                <p>Nandoni Waterfront Resort</p>
            </div>
            <div class='content'>
                <h2>Payment Details</h2>
                <div class='detail'><strong>Booking ID:</strong> #{$booking['id']}</div>
                <div class='detail'><strong>Guest Name:</strong> {$booking['full_name']}</div>
                <div class='detail'><strong>Payer Name:</strong> $payerName</div>
                <div class='detail'><strong>Payer Email:</strong> $payerEmail</div>
                <div class='detail'><strong>Transaction ID:</strong> $transactionId</div>
                <div class='detail'><strong>Amount (USD):</strong> $" . number_format($amountUSD, 2) . "</div>
                <div class='detail'><strong>Amount (ZAR):</strong> R" . number_format($amountZAR, 2) . "</div>
                <div class='detail'><strong>Payment Date:</strong> " . date('Y-m-d H:i:s') . "</div>
                <div class='detail'><strong>Room Type:</strong> {$booking['room_type']}</div>
                <div class='detail'><strong>Check-in:</strong> {$booking['check_in']}</div>
                <div class='detail'><strong>Check-out:</strong> {$booking['check_out']}</div>
            </div>
            <div class='footer'>
                <p>This booking is now confirmed and paid.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: Nandoni Waterfront Resort <" . NOREPLY_EMAIL . ">\r\n";
    
    mail($to, $subject, $message, $headers);
}
?>