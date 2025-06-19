<?php
// Database credentials
$servername = "your_server";
$username   = "your_db_user";
$password   = "your_db_password";
$dbname     = "your_database";

// Connect to database
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Capture and sanitize form data
$amount = isset($_POST['amount']) ? preg_replace('/[^0-9.]/', '', $_POST['amount']) : null;
$currency = 'ZAR';
$payment_method = 'PayFast';
$status = 'pending';
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Validate amount
if (empty($amount) || !is_numeric($amount)) {
    echo "<p style='color: red;'>❌ Invalid payment amount provided.</p>";
    exit;
}

// Insert into database
$sql = "INSERT INTO payments (amount, currency, payment_method, status, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "dsssss", $amount, $currency, $payment_method, $status, $ip_address, $user_agent);

if (mysqli_stmt_execute($stmt)) {
    echo "<p style='color: green;'>✅ Payment entry recorded successfully. Redirecting to PayFast...</p>";
    // TODO: Integrate with PayFast API or redirect to their gateway here.
} else {
    echo "<p style='color: red;'>❌ Payment entry failed. Try again.</p>";
}


mysqli_close($conn);
?>
