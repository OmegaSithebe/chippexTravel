<?php
session_start();

// Check if payment was successful
$paymentSuccess = isset($_GET['payment']) && $_GET['payment'] === 'success';
$paymentId = $_GET['payment_id'] ?? null;

if ($paymentSuccess && $paymentId) {
    // Database connection to get payment details
    $conn = mysqli_connect('localhost', 'chippyzr_chippexUser', 'chipexTravelDev@24!', 'chippyzr_chippex');
    $sql = "SELECT * FROM payments WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $paymentId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $payment = $result->fetch_assoc();
    mysqli_close($conn);
    
    $data = [
        'name' => $payment['full_name'],
        'email' => $payment['email'],
        'description' => $payment['description'],
        'amountZAR' => $payment['amount'],
        'paymentId' => $payment['id'],
        'transactionId' => $payment['paypal_transaction_id'],
        'paymentStatus' => $payment['status']
    ];
    
    // Clear session data
    unset($_SESSION['payment_data']);
} else {
    header("Location: payment.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Chippexs Travel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --success-color: #2ecc71;
        }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333; 
            margin: 0; 
            padding: 0; 
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('./assets/images/travel-bg.jpg');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .success-container { 
            max-width: 800px; 
            margin: 20px; 
            padding: 40px; 
            background: rgba(255, 255, 255, 0.95); 
            border-radius: 15px; 
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .success-icon {
            font-size: 4rem;
            color: var(--success-color);
            margin-bottom: 20px;
        }
        
        .payment-badge {
            background: var(--success-color);
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin: 10px 0;
        }
        
        h1 {
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        .payment-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: left;
        }
        
        .payment-detail {
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
        }
        
        .detail-label {
            font-weight: bold;
            color: #555;
        }
        
        .detail-value {
            color: #333;
        }
        
        .payment-id {
            background: var(--accent);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin: 10px 0;
        }
        
        .back-button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
            transition: background 0.3s;
        }
        
        .back-button:hover {
            background: var(--secondary);
        }
        
        footer {
            color: white;
            text-align: center;
            margin-top: 20px;
            padding: 20px;
        }
        
        @media (max-width: 768px) {
            .success-container {
                margin: 10px;
                padding: 20px;
            }
            
            .payment-detail {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <div class="payment-badge">
            <i class="fas fa-check"></i> Payment Successful!
        </div>
        <h1>Payment Confirmed!</h1>
        
        <div class="payment-id">
            Payment Reference: #<?php echo htmlspecialchars($data['paymentId']); ?>
        </div>
        
        <p>Thank you, <strong><?php echo htmlspecialchars($data['name']); ?></strong>! Your payment has been processed successfully.</p>
        
        <div class="payment-details">
            <h3>Payment Summary</h3>
            <div class="payment-detail">
                <span class="detail-label">Description:</span>
                <span class="detail-value"><?php echo htmlspecialchars($data['description']); ?></span>
            </div>
            <div class="payment-detail">
                <span class="detail-label">Amount Paid:</span>
                <span class="detail-value">R<?php echo number_format($data['amountZAR'], 2); ?></span>
            </div>
            <div class="payment-detail">
                <span class="detail-label">Transaction ID:</span>
                <span class="detail-value"><?php echo htmlspecialchars($data['transactionId']); ?></span>
            </div>
            <div class="payment-detail">
                <span class="detail-label">Payment Status:</span>
                <span class="detail-value" style="color: var(--success-color); font-weight: bold;">Paid</span>
            </div>
        </div>
        
        <div class="next-steps">
            <h3>What Happens Next?</h3>
            <p>We've sent a payment confirmation email to <strong><?php echo htmlspecialchars($data['email']); ?></strong>. Your payment has been processed successfully.</p>
            <p>If this payment was for a booking or service, our team will contact you shortly with further details.</p>
        </div>
        
        <button class="back-button" onclick="window.location.href='index.html'">
            <i class="fas fa-home"></i> Back to Homepage
        </button>
        
        <div style="margin-top: 20px;">
            <button class="back-button" onclick="window.location.href='payment.html'" style="background: #95a5a6;">
                <i class="fas fa-credit-card"></i> Make Another Payment
            </button>
        </div>
    </div>
</body>
</html>