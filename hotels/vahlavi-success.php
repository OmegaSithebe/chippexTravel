<?php
session_start();

// Check if form data exists, otherwise redirect to form
if (!isset($_SESSION['form_data'])) {
    header("Location: vahlavi_hotel.html");
    exit;
}

// Get form data from session
$data = $_SESSION['form_data'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vahlavi Booking Confirmation</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Fallback styles if CSS fails to load */
        body { 
            font-family: Arial, sans-serif; 
            color: #fff; 
            margin: 0; 
            padding: 0; 
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('./assets/images/vahlavi-bg.jpg');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
        }
        .success-container { 
            max-width: 800px; 
            margin: 50px auto; 
            padding: 30px; 
            background: rgba(0, 0, 0, 0.7); 
            border-radius: 15px; 
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        .thank-you-message {
            margin: 30px 0;
            line-height: 1.8;
        }
        .back-button {
            background: #e67e22;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        .back-button:hover {
            background: #d35400;
            transform: translateY(-2px);
        }
        footer {
            text-align: center;
            padding: 20px;
            background: rgba(0, 0, 0, 0.7);
            margin-top: 50px;
        }
        .booking-details {
            text-align: left;
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .booking-details h3 {
            border-bottom: 1px solid #e67e22;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .detail-row {
            margin-bottom: 10px;
        }
    </style>
</head>
<body class="vahlavi-theme">
    <div class="success-container animated">
        <h1><i class="fas fa-check-circle"></i> Booking Confirmed, <?php echo htmlspecialchars($data['name']); ?>!</h1>
        
        <div class="thank-you-message">
            <p>Thank you for choosing Vahlavi Guest House for your stay!</p>
            <p>We've sent a confirmation email to <?php echo htmlspecialchars($data['email']); ?> with your booking details.</p>
        </div>
        
        <div class="booking-details">
            <h3>Your Booking Details</h3>
            <div class="detail-row"><strong>Check-in:</strong> <?php echo htmlspecialchars($data['checkIn']); ?></div>
            <div class="detail-row"><strong>Check-out:</strong> <?php echo htmlspecialchars($data['checkOut']); ?></div>
            <div class="detail-row"><strong>Room Type:</strong> <?php echo htmlspecialchars($data['roomType']); ?></div>
            <div class="detail-row"><strong>Guests:</strong> <?php echo htmlspecialchars($data['guests']); ?></div>
            <?php if (!empty($data['packages'])): ?>
            <div class="detail-row"><strong>Packages:</strong> <?php echo htmlspecialchars($data['packages']); ?></div>
            <?php endif; ?>
            <?php if (!empty($data['specialRequests'])): ?>
            <div class="detail-row"><strong>Special Requests:</strong> <?php echo nl2br(htmlspecialchars($data['specialRequests'])); ?></div>
            <?php endif; ?>
        </div>
        
        <p>Our team will contact you shortly to confirm your reservation. If you have any questions, please call us at +27 73 474 2034.</p>
        
        <button class="back-button" onclick="window.location.href='vahlavi_hotel.html'">
            <i class="fas fa-arrow-left"></i> Back to Vahlavi Guest House
        </button>
    </div>
    
    <footer>
        © <?php echo date("Y"); ?> Chippexs Travel | Vahlavi Guest House
    </footer>
</body>
</html>
<?php 
// Clear the session data
unset($_SESSION['form_data']);
?>