<?php
// Start session to persist form data
session_start();

// Check if form data exists, otherwise redirect to form
if (!isset($_SESSION['form_data'])) {
    header("Location: car-travel.html");
    exit;
}

// Get form data from session
$formData = $_SESSION['form_data'];
$name = htmlspecialchars($formData['name']);
$surname = htmlspecialchars($formData['surname'] ?? ''); // Adjust based on your actual form fields
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Road Trip Confirmation | Chippexs Travel</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/images/car-bg.jpg');
            background-size: cover;
            background-position: center;
        }
        .adventure-icon {
            color: #FFD700;
        }
    </style>
</head>
<body>
    <div class="hero">
        <header>
            <img src="assets/images/logo.png" alt="Chippexs Travel Logo" class="logo">
            <h1>Road Trip Confirmed!</h1>
        </header>
        
        <div class="success-card">
            <div class="icon-circle adventure-icon">
                <i class="fas fa-car"></i>
            </div>
            <h2>Thank You <?php echo $name; ?> <?php echo $surname; ?>!</h2>
            <p class="success-message">
                Your road trip application has been received. Our travel specialists are mapping out the perfect route for your adventure.
            </p>
            <p class="contact-message">
                One of our representatives will contact you within 24 hours to finalize your journey details.
            </p>
            <button onclick="window.history.back()" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Road Trips
            </button>
        </div>
    </div>
    
    <footer>
        <div class="footer-content">
            <img src="assets/images/logo.png" alt="Chippexs Travel Logo" class="footer-logo">
            <div class="contact-info">
                <p><i class="fas fa-phone"></i> +1 (800) ROAD-TRIP</p>
                <p><i class="fas fa-envelope"></i> roadtrips@chippexstravel.co.za</p>
            </div>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; <?php echo date('Y'); ?> Chippexs Travel. All rights reserved.</p>
        </div>
    </footer>

    <!-- Font Awesome for icons -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
<?php
// Clear the form data from session
unset($_SESSION['form_data']);
?>