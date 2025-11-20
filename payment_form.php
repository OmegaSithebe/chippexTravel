<?php
session_start();

// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'chippyzr_chippexUser');
define('DB_PASSWORD', 'chipexTravelDev@24!');
define('DB_NAME', 'chippyzr_chippex');

// Email configuration
define('ADMIN_EMAIL', 'admin@chippexstravel.co.za');
define('NOREPLY_EMAIL', 'noreply@chippexstravel.co.za');
define('CONTACT_PHONE', '+27 73 474 2034');

// Currency conversion (Update this rate regularly)
define('USD_TO_ZAR_RATE', 18.5);

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to prevent header errors
ob_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create database connection
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Check connection
    if (!$conn) {
        $_SESSION['error'] = 'Database connection failed. Please try again later.';
        header("Location: payment.html");
        exit;
    }

    // Validate required fields
    $required = ['name', 'email', 'amount', 'description'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = 'Please complete all required fields.';
            header("Location: payment.html");
            exit;
        }
    }

    // Validate email format
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Please enter a valid email address.';
        header("Location: payment.html");
        exit;
    }

    // Validate amount
    $amountZAR = floatval($_POST['amount']);
    if ($amountZAR <= 0) {
        $_SESSION['error'] = 'Please enter a valid payment amount.';
        header("Location: payment.html");
        exit;
    }

    // Sanitize input
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $amountZAR = mysqli_real_escape_string($conn, $amountZAR);
    
    // Calculate USD amount
    $amountUSD = round($amountZAR / USD_TO_ZAR_RATE, 2);
    
    // Get client info
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    // Insert payment record
    $sql = "INSERT INTO payments 
            (full_name, email, description, amount, currency, payment_method, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, 'ZAR', 'PayPal', ?, ?, NOW())";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        $_SESSION['error'] = 'Database error. Please try again later.';
        header("Location: payment.html");
        exit;
    }

    mysqli_stmt_bind_param($stmt, "sssdss", $name, $email, $description, $amountZAR, $ip_address, $user_agent);
    
    if (!mysqli_stmt_execute($stmt)) {
        $_SESSION['error'] = 'An error occurred while processing your payment. Please try again.';
        header("Location: payment.html");
        exit;
    }

    // Get the payment ID
    $paymentId = mysqli_insert_id($conn);

    // Store payment data in session for payment page
    $_SESSION['payment_data'] = [
        'paymentId' => $paymentId,
        'name' => $name,
        'email' => $email,
        'description' => $description,
        'amountZAR' => $amountZAR,
        'amountUSD' => $amountUSD,
        'exchangeRate' => USD_TO_ZAR_RATE
    ];

    // Send payment request notification to admin
    $to = ADMIN_EMAIL;
    $subject = "New Payment Request - Chippexs Travel (Payment #$paymentId)";
    
    $message = "
    <html>
    <head>
        <title>New Payment Request</title>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #3498db; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .detail { margin: 10px 0; }
            .footer { background: #34495e; color: white; padding: 15px; text-align: center; }
            .amount { color: #e74c3c; font-weight: bold; font-size: 1.2em; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>New Payment Request</h1>
                <p>Chippexs Travel</p>
            </div>
            <div class='content'>
                <h2>Payment Details</h2>
                <div class='detail'><strong>Payment ID:</strong> #$paymentId</div>
                <div class='detail'><strong>Customer Name:</strong> $name</div>
                <div class='detail'><strong>Email:</strong> $email</div>
                <div class='detail'><strong>Description:</strong> $description</div>
                <div class='detail'><strong>Amount (ZAR):</strong> <span class='amount'>R" . number_format($amountZAR, 2) . "</span></div>
                <div class='detail'><strong>Amount (USD):</strong> <span class='amount'>$" . number_format($amountUSD, 2) . "</span></div>
                <div class='detail'><strong>Exchange Rate:</strong> 1 USD = " . USD_TO_ZAR_RATE . " ZAR</div>
                <div class='detail'><strong>Payment Status:</strong> <span style='color: #f39c12;'>Pending Payment</span></div>
                <div class='detail'><strong>Submitted:</strong> " . date('Y-m-d H:i:s') . "</div>
            </div>
            <div class='footer'>
                <p>Customer has been redirected to PayPal payment page. Awaiting payment confirmation.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: Chippexs Travel <" . NOREPLY_EMAIL . ">\r\n";
    $headers .= "Reply-To: $name <$email>\r\n";
    $headers .= "X-Priority: 1\r\n";
    
    mail($to, $subject, $message, $headers);

    // Send confirmation to customer
    $customerSubject = "Your Payment Request - Chippexs Travel (Payment #$paymentId)";
    $customerMessage = "
    <html>
    <head>
        <title>Payment Request Confirmation</title>
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
                <h1>Payment Request Received!</h1>
                <p>Chippexs Travel</p>
            </div>
            <div class='content'>
                <h2>Hello $name,</h2>
                <p>We've received your payment request and you will be redirected to our secure payment page.</p>
                
                <h3>Payment Details:</h3>
                <div class='detail'><strong>Payment Reference:</strong> <span class='highlight'>#$paymentId</span></div>
                <div class='detail'><strong>Description:</strong> $description</div>
                <div class='detail'><strong>Amount:</strong> R" . number_format($amountZAR, 2) . " (Approx. $" . number_format($amountUSD, 2) . " USD)</div>
                
                <p><strong>Next Steps:</strong></p>
                <ul>
                    <li>You will be redirected to our secure PayPal payment page</li>
                    <li>Complete your payment to confirm your transaction</li>
                    <li>Once paid, you will receive a payment confirmation</li>
                </ul>
                
                <p>If you have any questions, please contact us at " . CONTACT_PHONE . " or reply to this email.</p>
            </div>
            <div class='footer'>
                <p>Thank you for choosing Chippexs Travel!</p>
                <p>© " . date("Y") . " Chippexs Travel | Endless Adventures</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $customerHeaders = "MIME-Version: 1.0\r\n";
    $customerHeaders .= "Content-type:text/html;charset=UTF-8\r\n";
    $customerHeaders .= "From: Chippexs Travel <" . NOREPLY_EMAIL . ">\r\n";
    $customerHeaders .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
    $customerHeaders .= "X-Priority: 1\r\n";
    
    mail($email, $customerSubject, $customerMessage, $customerHeaders);

    // Clear output buffer before redirecting
    ob_end_clean();
    
    // Redirect to payment page
    header("Location: payment_processing.php");
    exit;
    
    // Close connection
    mysqli_close($conn);
}

// End output buffering if not redirected
ob_end_flush();
?>