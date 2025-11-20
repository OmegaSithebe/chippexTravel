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
    
    $paymentId = mysqli_real_escape_string($conn, $input['paymentId'] ?? '');
    $transactionId = mysqli_real_escape_string($conn, $input['transactionId'] ?? '');
    $payerEmail = mysqli_real_escape_string($conn, $input['payerEmail'] ?? '');
    $payerName = mysqli_real_escape_string($conn, $input['payerName'] ?? '');
    $amountUSD = mysqli_real_escape_string($conn, $input['amount'] ?? '');
    $amountZAR = mysqli_real_escape_string($conn, $input['amountZAR'] ?? '');
    $currency = mysqli_real_escape_string($conn, $input['currency'] ?? 'USD');
    $status = mysqli_real_escape_string($conn, $input['status'] ?? '');
    
    // Update payment with payment information
    $sql = "UPDATE payments SET 
            status = 'paid',
            paypal_transaction_id = ?,
            payer_email = ?,
            payer_name = ?,
            payment_date = NOW(),
            updated_at = NOW()
            WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Prepare statement failed: ' . mysqli_error($conn)]);
        mysqli_close($conn);
        exit;
    }
    
    mysqli_stmt_bind_param($stmt, "sssi", $transactionId, $payerEmail, $payerName, $paymentId);
    
    if (mysqli_stmt_execute($stmt)) {
        // Get payment details for email
        $paymentSql = "SELECT * FROM payments WHERE id = ?";
        $paymentStmt = mysqli_prepare($conn, $paymentSql);
        mysqli_stmt_bind_param($paymentStmt, "i", $paymentId);
        mysqli_stmt_execute($paymentStmt);
        $result = mysqli_stmt_get_result($paymentStmt);
        $payment = $result->fetch_assoc();
        
        if ($payment) {
            // Send payment confirmation email to customer
            sendPaymentConfirmationEmail($payment, $transactionId, $amountZAR, $amountUSD);
            
            // Send payment notification to admin
            sendPaymentNotificationToAdmin($payment, $transactionId, $amountZAR, $amountUSD, $payerEmail, $payerName);
            
            echo json_encode(['success' => true, 'message' => 'Payment recorded successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Payment not found']);
        }
    } else {
        $error = mysqli_error($conn);
        echo json_encode(['success' => false, 'error' => $error]);
    }
    
    mysqli_close($conn);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

function sendPaymentConfirmationEmail($payment, $transactionId, $amountZAR, $amountUSD) {
    $to = $payment['email'];
    $subject = "Payment Confirmed - Chippexs Travel (Payment #{$payment['id']})";
    
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
                <p>Chippexs Travel</p>
            </div>
            <div class='content'>
                <h2>Hello {$payment['full_name']},</h2>
                <p>Your payment has been successfully processed and confirmed!</p>
                
                <h3>Payment Details:</h3>
                <div class='detail'><strong>Payment Reference:</strong> <span class='highlight'>#{$payment['id']}</span></div>
                <div class='detail'><strong>Transaction ID:</strong> $transactionId</div>
                <div class='detail'><strong>Amount Paid (USD):</strong> $" . number_format($amountUSD, 2) . "</div>
                <div class='detail'><strong>Amount Paid (ZAR):</strong> R" . number_format($amountZAR, 2) . "</div>
                <div class='detail'><strong>Description:</strong> {$payment['description']}</div>
                <div class='detail'><strong>Payment Date:</strong> " . date('Y-m-d H:i:s') . "</div>
                
                <p>Thank you for your payment! If you have any questions about your booking or services, please contact us.</p>
                
                <p>If you have any questions, please contact us at +27 73 474 2034.</p>
            </div>
            <div class='footer'>
                <p>Thank you for choosing Chippexs Travel!</p>
                <p>© " . date("Y") . " Chippexs Travel | Endless Adventures</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: Chippexs Travel <" . NOREPLY_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
    
    mail($to, $subject, $message, $headers);
}

function sendPaymentNotificationToAdmin($payment, $transactionId, $amountZAR, $amountUSD, $payerEmail, $payerName) {
    $to = ADMIN_EMAIL;
    $subject = "Payment Received - Payment #{$payment['id']} - Chippexs Travel";
    
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
                <p>Chippexs Travel</p>
            </div>
            <div class='content'>
                <h2>Payment Details</h2>
                <div class='detail'><strong>Payment ID:</strong> #{$payment['id']}</div>
                <div class='detail'><strong>Customer Name:</strong> {$payment['full_name']}</div>
                <div class='detail'><strong>Customer Email:</strong> {$payment['email']}</div>
                <div class='detail'><strong>Payer Name:</strong> $payerName</div>
                <div class='detail'><strong>Payer Email:</strong> $payerEmail</div>
                <div class='detail'><strong>Transaction ID:</strong> $transactionId</div>
                <div class='detail'><strong>Amount (USD):</strong> $" . number_format($amountUSD, 2) . "</div>
                <div class='detail'><strong>Amount (ZAR):</strong> R" . number_format($amountZAR, 2) . "</div>
                <div class='detail'><strong>Description:</strong> {$payment['description']}</div>
                <div class='detail'><strong>Payment Date:</strong> " . date('Y-m-d H:i:s') . "</div>
            </div>
            <div class='footer'>
                <p>This payment has been successfully processed.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: Chippexs Travel <" . NOREPLY_EMAIL . ">\r\n";
    
    mail($to, $subject, $message, $headers);
}
?>