<?php
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if payment data exists
if (!isset($_SESSION['payment_data'])) {
    header("Location: payment.html");
    exit;
}

$payment = $_SESSION['payment_data'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Payment - Chippexs Travel</title>
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
        
        .payment-container { 
            max-width: 800px; 
            margin: 20px; 
            padding: 40px; 
            background: rgba(255, 255, 255, 0.95); 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        h1 {
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .payment-summary {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid var(--primary);
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 12px 0;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
        }
        
        .summary-label {
            font-weight: bold;
            color: #555;
        }
        
        .summary-value {
            color: #333;
            text-align: right;
        }
        
        .total-amount {
            background: var(--accent);
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
            font-size: 1.3em;
            font-weight: bold;
        }
        
        .payment-section {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: white;
            border-radius: 10px;
            border: 2px solid #f1f1f1;
        }
        
        #paypal-button-container {
            margin: 20px 0;
            min-height: 200px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .back-button {
            background: #95a5a6;
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
            background: #7f8c8d;
        }
        
        .payment-id {
            background: var(--accent);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin: 10px 0;
        }
        
        @media (max-width: 768px) {
            .payment-container {
                margin: 10px;
                padding: 20px;
            }
            
            .summary-row {
                flex-direction: column;
            }
        }
    </style>
    <!-- PayPal SDK with USD currency -->
    <script src="https://www.paypal.com/sdk/js?client-id=AbxO9PC12iRUU_ChHTkD3GSJy3H0GIR3WSBcD7fqKkkDRTCbjmGs14v9zs1lemA5Kj5TDYSrT3M4lZ6I&currency=USD"></script>
</head>
<body>
    <div class="payment-container">
        <div class="header">
            <h1><i class="fas fa-lock"></i> Secure Payment</h1>
            <p>Complete your payment securely with PayPal</p>
            <div class="payment-id">
                Payment Reference: #<?php echo htmlspecialchars($payment['paymentId']); ?>
            </div>
        </div>
        
        <div class="payment-summary">
            <h3><i class="fas fa-receipt"></i> Payment Summary</h3>
            <div class="summary-row">
                <span class="summary-label">Customer Name:</span>
                <span class="summary-value"><?php echo htmlspecialchars($payment['name']); ?></span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Description:</span>
                <span class="summary-value"><?php echo htmlspecialchars($payment['description']); ?></span>
            </div>
            
            <div class="currency-conversion">
                <h4><i class="fas fa-exchange-alt"></i> Currency Conversion</h4>
                <div class="summary-row">
                    <span class="summary-label">Amount in ZAR:</span>
                    <span class="summary-value">R<?php echo number_format($payment['amountZAR'], 2); ?></span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Exchange Rate:</span>
                    <span class="summary-value">1 USD = <?php echo number_format($payment['exchangeRate'], 2); ?> ZAR</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Amount in USD:</span>
                    <span class="summary-value">$<?php echo number_format($payment['amountUSD'], 2); ?></span>
                </div>
                <p class="exchange-note">
                    <i class="fas fa-info-circle"></i> 
                    Payment will be processed in USD. Your bank may charge a small currency conversion fee.
                    The final amount charged may vary slightly based on your bank's exchange rate.
                </p>
            </div>
        </div>
        
        <div class="total-amount">
            <i class="fas fa-tag"></i> Total Amount: $<?php echo number_format($payment['amountUSD'], 2); ?> USD
        </div>
        
        <div class="payment-section">
            <h3><i class="fas fa-credit-card"></i> Payment Method</h3>
            <p>Pay securely with PayPal</p>
            
            <div id="paypal-button-container"></div>
            
            <p style="font-size: 0.9em; color: #666; margin-top: 20px;">
                <i class="fas fa-shield-alt"></i> Your payment is secure and encrypted
            </p>
        </div>
        
        <div style="text-align: center;">
            <button class="back-button" onclick="window.location.href='payment.html'">
                <i class="fas fa-arrow-left"></i> Back to Payment Form
            </button>
        </div>
    </div>

    <script>
        paypal.Buttons({
            style: {
                layout: 'vertical',
                color: 'blue',
                shape: 'rect',
                label: 'paypal'
            },
            
            createOrder: function(data, actions) {
                const amount = '<?php echo $payment['amountUSD']; ?>';
                
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: amount,
                            currency_code: 'USD'
                        },
                        description: 'Payment #<?php echo $payment['paymentId']; ?> - <?php echo addslashes($payment['description']); ?>'
                    }]
                });
            },
            
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    // Send payment data to server
                    return fetch('payment_success.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            paymentId: <?php echo $payment['paymentId']; ?>,
                            transactionId: details.id,
                            payerEmail: details.payer.email_address,
                            payerName: details.payer.name.given_name + ' ' + (details.payer.name.surname || ''),
                            amount: '<?php echo $payment['amountUSD']; ?>',
                            amountZAR: '<?php echo $payment['amountZAR']; ?>',
                            currency: 'USD',
                            status: details.status
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = 'payment_success_page.php?payment=success&payment_id=<?php echo $payment['paymentId']; ?>';
                        } else {
                            alert('Payment successful but there was an issue updating your payment record. Please contact us.');
                        }
                    });
                });
            },
            
            onError: function(err) {
                console.error('PayPal Checkout onError', err);
                alert('An error occurred during the payment process. Please try again.');
            }
            
        }).render('#paypal-button-container');
    </script>
</body>
</html>