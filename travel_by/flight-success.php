<?php
session_start();

// Check if form data exists, otherwise redirect to form
if (!isset($_SESSION['form_data'])) {
    header("Location: flight-travel.html");
    exit;
}

// Get form data from session
$data = $_SESSION['form_data'];

// // Clear the session data immediately after retrieving it
// unset($_SESSION['form_data']);

// // Set no-cache headers to prevent back button issues
// header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
// header("Cache-Control: post-check=0, pre-check=0", false);
// header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Booking Confirmation | Chippexs Travel</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* Fallback styles if CSS fails to load */
        body { 
            font-family: Arial, sans-serif; 
            color: #fff; 
            margin: 0; 
            padding: 0; 
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/images/flight-bg.jpg');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
        }
        .success-container { 
            max-width: 800px; 
            margin: 2rem auto; 
            padding: 2rem; 
            background: rgba(0, 0, 0, 0.7); 
            border-radius: 15px; 
            text-align: center; 
        }
        .back-button {
            display: inline-block;
            margin-top: 1rem;
        }
    </style>
</head>
<body class="flight-theme">
    <div class="success-container">
        <h1>✈️ Ready for Takeoff, <?php echo htmlspecialchars($data['name']); ?>!</h1>
        <div class="thank-you-message">
            <p>Thank you <?php echo htmlspecialchars($data['name']); ?>, your flight request has been confirmed!</p>
            <p>Our sky travel experts will send your itinerary within 24 hours.</p>
        </div>
        <a href="flight-travel.html" class="back-button">Back to Flight Bookings</a>
    </div>
    <footer>
        © <?php echo date("Y"); ?> Chippexs Travel | The Sky Is Not The Limit
    </footer>
</body>
</html>
<?php 
// Clear the session data
unset($_SESSION['form_data']);
?>