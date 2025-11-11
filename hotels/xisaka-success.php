<?php
session_start();

// Check if form data exists, otherwise redirect to form
if (!isset($_SESSION['form_data'])) {
    header("Location: xisaka_hotel.html");
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
    <title>Booking Confirmed - Xisaka Guest House</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #27ae60;
            --secondary-color: #219653;
            --accent-color: #e67e22;
            --success-color: #2ecc71;
        }
        
        body { 
            font-family: 'Arial', sans-serif; 
            color: #333; 
            margin: 0; 
            padding: 0; 
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('./assets/images/xisaka-bg.jpg');
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
        <h1>Booking Request Received!</h1>
        
        <div class="booking-id">
            Booking Reference: #<?php echo htmlspecialchars($data['bookingId'] ?? 'N/A'); ?>
        </div>
        
        <p>Thank you, <strong><?php echo htmlspecialchars($data['name']); ?></strong>! Your booking request has been submitted successfully.</p>
        
        <div class="booking-details">
            <h3>Booking Summary</h3>
            <div class="booking-detail">
                <span class="detail-label">Room Type:</span>
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
                <span class="detail-label">Number of Nights:</span>
                <span class="detail-value"><?php echo htmlspecialchars($data['nights'] ?? 'N/A'); ?></span>
            </div>
            <div class="booking-detail">
                <span class="detail-label">Guests:</span>
                <span class="detail-value"><?php echo htmlspecialchars($data['guests']); ?></span>
            </div>
            <?php if (!empty($data['addons'])): ?>
            <div class="booking-detail">
                <span class="detail-label">Selected Add-ons:</span>
                <span class="detail-value"><?php echo htmlspecialchars($data['addons']); ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($data['specialRequests'])): ?>
            <div class="booking-detail">
                <span class="detail-label">Special Requests:</span>
                <span class="detail-value"><?php echo htmlspecialchars($data['specialRequests']); ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="next-steps">
            <h3>What Happens Next?</h3>
            <p>We've sent a confirmation email to <strong><?php echo htmlspecialchars($data['email']); ?></strong>. Our team will contact you within 24 hours to confirm availability and provide payment details.</p>
        </div>
        
        <button class="back-button" onclick="window.location.href='xisaka_hotel.html'">
            <i class="fas fa-arrow-left"></i> Back to Xisaka Guest House
        </button>
    </div>
    
    <footer>
        © <?php echo date("Y"); ?> Chippexs Travel | Xisaka Guest House
    </footer>
</body>
</html>
<?php 
// Clear the session data
unset($_SESSION['form_data']);
?>