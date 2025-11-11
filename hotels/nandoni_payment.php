<?php
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if booking data exists
if (!isset($_SESSION['booking_data'])) {
    header("Location: nandoni_hotel.html");
    exit;
}

$booking = $_SESSION['booking_data'];

// Debug output
echo "<!-- Debug Info: ";
echo "Booking ID: " . $booking['bookingId'] . " | ";
echo "Total Amount: " . $booking['totalAmount'] . " | ";
echo "Room Type: " . $booking['roomType'] . " -->";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Payment - Nandoni Waterfront Resort</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --accent-color: #e74c3c;
            --success-color: #2ecc71;
        }
        
        body { 
            font-family: 'Arial', sans-serif; 
            color: #333; 
            margin: 0; 
            padding: 0; 
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('./assets/images/nandoni-bg.jpg');
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
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .booking-summary {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid var(--primary-color);
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
            background: var(--accent-color);
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
        
        .booking-id {
            background: var(--accent-color);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin: 10px 0;
        }
        
        .debug-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 12px;
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
            <p>Complete your booking by making a secure payment</p>
            <div class="booking-id">
                Booking Reference: #<?php echo htmlspecialchars($booking['bookingId']); ?>
            </div>
        </div>
        
        <div class="booking-summary">
            <h3><i class="fas fa-receipt"></i> Booking Summary</h3>
            <div class="summary-row">
                <span class="summary-label">Guest Name:</span>
                <span class="summary-value"><?php echo htmlspecialchars($booking['name']); ?></span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Accommodation:</span>
                <span class="summary-value"><?php echo htmlspecialchars($booking['roomType']); ?></span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Check-in:</span>
                <span class="summary-value"><?php echo htmlspecialchars($booking['checkIn']); ?></span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Check-out:</span>
                <span class="summary-value"><?php echo htmlspecialchars($booking['checkOut']); ?></span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Number of Nights:</span>
                <span class="summary-value"><?php echo htmlspecialchars($booking['nights']); ?></span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Guests:</span>
                <span class="summary-value"><?php echo htmlspecialchars($booking['guests']); ?></span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Price per Night:</span>
                <span class="summary-value">R<?php echo number_format($booking['pricePerNight'], 2); ?></span>
            </div>
        </div>
        
        <div class="total-amount">
            <i class="fas fa-tag"></i> Total Amount: R<?php echo number_format($booking['totalAmount'], 2); ?>
        </div>
        
        <div class="currency-conversion">
            <h4><i class="fas fa-exchange-alt"></i> Currency Conversion</h4>
            <div class="summary-row">
                <span class="summary-label">Total in ZAR:</span>
                <span class="summary-value">R<?php echo number_format($booking['totalAmountZAR'], 2); ?></span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Exchange Rate:</span>
                <span class="summary-value">1 USD = <?php echo number_format($booking['exchangeRate'], 2); ?> ZAR</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Total in USD:</span>
                <span class="summary-value">$<?php echo number_format($booking['totalAmountUSD'], 2); ?></span>
            </div>
            <p class="exchange-note">
                <i class="fas fa-info-circle"></i> 
                Payment will be processed in USD. Your bank may charge a small currency conversion fee.
                The final amount charged may vary slightly based on your bank's exchange rate.
            </p>
        </div>
        
        <div class="total-amount">
            <i class="fas fa-tag"></i> Total Amount: $<?php echo number_format($booking['totalAmountUSD'], 2); ?> USD
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
            <button class="back-button" onclick="window.location.href='nandoni_hotel.html'">
                <i class="fas fa-arrow-left"></i> Back to Booking
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
                const amount = '<?php echo $booking['totalAmountUSD']; ?>';
                
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: amount,
                            currency_code: 'USD'
                        },
                        description: 'Booking #<?php echo $booking['bookingId']; ?> - <?php echo $booking['roomType']; ?>'
                    }]
                });
            },
            
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    // Send payment data to server
                    return fetch('nandoni_payment_success.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            bookingId: <?php echo $booking['bookingId']; ?>,
                            transactionId: details.id,
                            payerEmail: details.payer.email_address,
                            payerName: details.payer.name.given_name + ' ' + (details.payer.name.surname || ''),
                            amount: '<?php echo $booking['totalAmountUSD']; ?>',
                            amountZAR: '<?php echo $booking['totalAmountZAR']; ?>',
                            currency: 'USD',
                            status: details.status
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = 'nandoni-success.php?payment=success&booking_id=<?php echo $booking['bookingId']; ?>';
                        } else {
                            alert('Payment successful but there was an issue updating your booking. Please contact us.');
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