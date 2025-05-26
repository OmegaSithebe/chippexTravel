<?php
session_start();

// Check if form data exists, otherwise redirect to form
if (!isset($_SESSION['form_data'])) {
    header("Location: nandoni_hotel.html");
    exit;
}

// Get form data from session
$data = $_SESSION['form_data'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Nandoni Booking Confirmation</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* Fallback styles if CSS fails to load */
        body { 
            font-family: Arial, sans-serif; 
            color: #fff; 
            margin: 0; 
            padding: 0; 
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('./assets/images/nandoni-bg.jpg');
        }
        .success-container { 
            max-width: 800px; 
            margin: 50px auto; 
            padding: 30px; 
            background: rgba(0, 0, 0, 0.7); 
            border-radius: 15px; 
            text-align: center; 
        }
    </style>
</head>
<body class="nandoni-theme">
    <div class="success-container">
        <h1>🏨 Booking Confirmed, <?php echo htmlspecialchars($data['name']); ?>!</h1>
        <div class="thank-you-message">
            <p>Thank you for booking with Nandoni Waterfront Resort!</p>
            <p>Your reservation from <?php echo htmlspecialchars($data['checkIn']); ?> to <?php echo htmlspecialchars($data['checkOut']); ?> for <?php echo htmlspecialchars($data['guests']); ?> guests has been received.</p>
            <p>We've sent a confirmation to <?php echo htmlspecialchars($data['email']); ?>. Our team will contact you shortly to finalize your stay.</p>
        </div>
        <button class="back-button" onclick="window.location.href='nandoni_hotel.html'">Back to Nandoni</button>
    </div>
    <footer>
        © <?php echo date("Y"); ?> Chippexs Travel | Luxury Stays at Nandoni Waterfront
    </footer>
</body>
</html>
<?php 
// Clear the session data
unset($_SESSION['form_data']);
?>