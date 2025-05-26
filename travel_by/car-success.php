<?php
session_start();

// Check if form data exists, otherwise redirect to form
if (!isset($_SESSION['form_data'])) {
    header("Location: car-travel.html");
    exit;
}

// Get form data from session
$data = $_SESSION['form_data'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Road Trip Confirmation</title>
    <link rel="stylesheet" href="assets/css/styles.css"> <!-- Fixed path -->
    <style>
        /* Fallback styles if CSS fails to load */
        body { 
            font-family: Arial, sans-serif; 
            color: #fff; 
            margin: 0; 
            padding: 0; 
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('../images/car-bg.jpg');
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
<body class="car-theme">
    <div class="success-container">
        <h1>🚗 Adventure Awaits, <?php echo htmlspecialchars($data['name']); ?>!</h1>
        <div class="thank-you-message">
            <p>Thank you <?php echo htmlspecialchars($data['name']); ?>, your road trip request has been received!</p>
            <p>Our travel experts are plotting the perfect route and will contact you within 24 hours.</p>
        </div>
        <button class="back-button" onclick="window.location.href='car-travel.html'">Back to Road Trips</button>
    </div>
    <footer>
        © <?php echo date("Y"); ?> Chippexs Travel | Where Every Road Leads to Adventure
    </footer>
</body>
</html>
<?php 
// Clear the session data
unset($_SESSION['form_data']);
?>