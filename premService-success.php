<?php
// Start session to potentially carry over any data if needed
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You for Your Enquiry | Chippex Travel</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 30px;
            border: 1px solid #c3e6cb;
        }
        .btn-back {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            transition: background-color 0.3s;
            margin-top: 20px;
        }
        .btn-back:hover {
            background-color: #2980b9;
        }
        footer {
            margin-top: 50px;
            padding: 20px;
            background-color: #2c3e50;
            color: white;
            text-align: center;
        }
        .contact-info {
            margin-top: 30px;
            font-size: 0.9em;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Thank You for Your Enquiry</h1>
        
        <div class="success-message">
            <p>✅ Your enquiry has been successfully submitted. Our team will get back to you shortly.</p>
        </div>
        
        <?php
        // Display the submitted data if available
        if (isset($_SESSION['form_data'])) {
            $formData = $_SESSION['form_data'];
            echo '<div class="submitted-data" style="text-align: left; margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 4px;">';
            echo '<h3 style="margin-top: 0;">Your Enquiry Details:</h3>';
            echo '<p><strong>Service:</strong> ' . htmlspecialchars($formData['service'] ?? '') . '</p>';
            echo '<p><strong>Name:</strong> ' . htmlspecialchars($formData['name'] ?? '') . '</p>';
            echo '<p><strong>Email:</strong> ' . htmlspecialchars($formData['email'] ?? '') . '</p>';
            echo '<p><strong>Phone:</strong> ' . htmlspecialchars($formData['phone'] ?? '') . '</p>';
            if (!empty($formData['date'])) {
                echo '<p><strong>Travel Date:</strong> ' . htmlspecialchars($formData['date']) . '</p>';
            }
            echo '</div>';
            
            // Clear the session data
            unset($_SESSION['form_data']);
        }
        ?>
        
        <p>We appreciate you considering Chippex Travel for your needs. One of our travel experts will contact you within 24 hours to discuss your requirements.</p>
        
        <div class="contact-info">
            <p>For immediate assistance, please call us at <strong>+27 12 345 6789</strong> or email <strong>info@chippexstravel.co.za</strong></p>
        </div>
        
        <a href="javascript:history.back()" class="btn-back">Back to Services</a>
        <a href="/" class="btn-back" style="background-color: #2c3e50; margin-left: 10px;">Home Page</a>
    </div>
    
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Chippex Travel. All rights reserved.</p>
    </footer>
</body>
</html>