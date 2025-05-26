<?php
session_start();

// Check if form data exists, otherwise redirect to form
if (!isset($_SESSION['form_data'])) {
    header("Location: ship-travel.html");
    exit;
}

// Get form data from session
$data = $_SESSION['form_data'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cruise Confirmation</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* Fallback styles if CSS fails to load */
        body { 
            font-family: Arial, sans-serif; 
            color: #fff; 
            margin: 0; 
            padding: 0; 
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('../images/ship-bg.png');
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
<body class="ship-theme">
    <div class="success-container">
        <h1>🛳️ Bon Voyage, <?php echo htmlspecialchars($data['name']); ?>!</h1>
        <div class="thank-you-message">
            <p>Thank you <?php echo htmlspecialchars($data['name']); ?>, your cruise application has been received!</p>
            <p>Our voyage team is preparing your sea adventure and will contact you soon.</p>
        </div>
        <button class="back-button" onclick="window.location.href='ship-travel.html'">Back to Cruises</button>
    </div>
    <footer>
        © <?php echo date("Y"); ?> Chippexs Travel | Sail Into The Sunset
    </footer>
</body>
</html>
<?php 
// Clear the session data
unset($_SESSION['form_data']);
?>