<?php
session_start();

// Check if payment was successful or if we have session data
$paymentSuccess = isset($_GET['payment']) && $_GET['payment'] === 'success';
$bookingId = $_GET['booking_id'] ?? null;

if ($paymentSuccess && $bookingId) {
    // Database connection to get booking details
    $conn = mysqli_connect('localhost', 'chippyzr_chippexUser', 'chipexTravelDev@24!', 'chippyzr_chippex');
    $sql = "SELECT * FROM nandoni_bookings WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $bookingId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $booking = $result->fetch_assoc();
    mysqli_close($conn);
    
    $data = [
        'name' => $booking['full_name'],
        'email' => $booking['email'],
        'checkIn' => $booking['check_in'],
        'checkOut' => $booking['check_out'],
        'guests' => $booking['guests'],
        'roomType' => $booking['room_type'],
        'specialRequests' => $booking['special_requests'],
        'bookingId' => $booking['id'],
        'paymentStatus' => $booking['payment_status']
    ];
    
    // Clear session data
    unset($_SESSION['booking_data']);
} elseif (isset($_SESSION['form_data'])) {
    $data = $_SESSION['form_data'];
    unset($_SESSION['form_data']);
} else {
    header("Location: nandoni_hotel.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - Nandoni Waterfront Resort</title>
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
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .booking-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: left;
        }
        
        .booking-detail {
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
        
        .booking-id {
            background: var(--accent-color);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin: 10px 0;
        }
        
        .back-button {
            background: var(--primary-color);
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
            background: var(--secondary-color);
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
            
            .booking-detail {
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
        
        <?php if ($paymentSuccess): ?>
            <div class="payment-badge">
                <i class="fas fa-check"></i> Payment Successful!
            </div>
            <h1>Booking Confirmed!</h1>
        <?php else: ?>
            <h1>Booking Request Received!</h1>
        <?php endif; ?>
        
        <div class="booking-id">
            Booking Reference: #<?php echo htmlspecialchars($data['bookingId']); ?>
        </div>
        
        <p>Thank you, <strong><?php echo htmlspecialchars($data['name']); ?></strong>! 
        <?php if ($paymentSuccess): ?>
            Your booking has been confirmed and payment has been processed successfully.
        <?php else: ?>
            Your booking request has been submitted successfully.
        <?php endif; ?>
        </p>
        
        <div class="booking-details">
            <h3>Booking Summary</h3>
            <div class="booking-detail">
                <span class="detail-label">Accommodation:</span>
                <span class="detail-value"><?php echo htmlspecialchars($data['roomType']); ?></span>
            </div>
            <div class="booking-detail">
                <span class="detail-label">Check-in:</span>
                <span class="detail-value"><?php echo htmlspecialchars($data['checkIn']); ?></span>
            </div>
            <div class="booking-detail">
                <span class="detail-label">Check-out:</span>
                <span class="detail-value"><?php echo htmlspecialchars($data['checkOut']); ?></span>
            </div>
            <div class="booking-detail">
                <span class="detail-label">Guests:</span>
                <span class="detail-value"><?php echo htmlspecialchars($data['guests']); ?></span>
            </div>
            <?php if (!empty($data['specialRequests'])): ?>
            <div class="booking-detail">
                <span class="detail-label">Special Requests:</span>
                <span class="detail-value"><?php echo htmlspecialchars($data['specialRequests']); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($paymentSuccess): ?>
            <div class="booking-detail">
                <span class="detail-label">Payment Status:</span>
                <span class="detail-value" style="color: var(--success-color); font-weight: bold;">Paid</span>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="next-steps">
            <h3>What Happens Next?</h3>
            <?php if ($paymentSuccess): ?>
                <p>We've sent a payment confirmation email to <strong><?php echo htmlspecialchars($data['email']); ?></strong>. Your reservation is now secured, and we look forward to welcoming you!</p>
                <p>You will receive a detailed confirmation email with all your booking information shortly.</p>
            <?php else: ?>
                <p>We've sent a confirmation email to <strong><?php echo htmlspecialchars($data['email']); ?></strong>. Please proceed to the payment page to complete your reservation.</p>
            <?php endif; ?>
        </div>
        
        <button class="back-button" onclick="window.location.href='nandoni_hotel.html'">
            <i class="fas fa-arrow-left"></i> Back to Nandoni Resort
        </button>
    </div>
    
    <footer>
        © <?php echo date("Y"); ?> Chippexs Travel | Luxury Stays at Nandoni Waterfront Resort
    </footer>
</body>
</html>