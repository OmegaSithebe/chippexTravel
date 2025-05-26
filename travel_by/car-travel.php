<?php
// Start session to persist data for success page
session_start();

// Disable error display for production
ini_set('display_errors', 0);
error_reporting(0);

// DB connection
$servername = "localhost";
$username   = "chippyzr_chippexUser";
$password   = "chipexTravelDev@24!";
$dbname     = "chippyzr_chippex";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("<p style='color: red;'>❌ Connection failed. Try again later.</p>");
}

// Get ALL form data
$formData = $_POST; // Store everything from the form

// Validate required fields
if (empty($formData['name']) || empty($formData['email']) || empty($formData['phone']) || 
    empty($formData['pickup']) || empty($formData['destination']) || empty($formData['date'])) {
    die("<p style='color: red;'>❌ Please fill in all required fields.</p>");
}

// Save to DB
$sql = "INSERT INTO car_travel_requests 
        (full_name, email, phone, pickup_location, destination, travel_date, passengers, notes) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssss", 
    $formData['name'], 
    $formData['email'], 
    $formData['phone'], 
    $formData['pickup'], 
    $formData['destination'], 
    $formData['date'], 
    $formData['passengers'], 
    $formData['notes']
);

if ($stmt->execute()) {
    // Store ALL form data in session for success page
    $_SESSION['form_data'] = $formData;
    
    // Email setup
    $to      = "admin@chippexstravel.co.za";
    $subject = "🚗 New Car Travel Request from {$formData['name']}";
    
    $body = "
        <h2>New Car Travel Request</h2>
        <p><strong>Name:</strong> {$formData['name']}</p>
        <p><strong>Email:</strong> {$formData['email']}</p>
        <p><strong>Phone:</strong> {$formData['phone']}</p>
        <p><strong>Pickup Location:</strong> {$formData['pickup']}</p>
        <p><strong>Destination:</strong> {$formData['destination']}</p>
        <p><strong>Travel Date:</strong> {$formData['date']}</p>
        <p><strong>Passengers:</strong> {$formData['passengers']}</p>
        <p><strong>Additional Notes:</strong><br>{$formData['notes']}</p>
        <hr>
        <small>Sent automatically from Chippexs Travel website</small>
    ";
    
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Chippexs Travel <no-reply@chippexstravel.co.za>\r\n";
    
    mail($to, $subject, $body, $headers);
    
    // Ensure no output before header redirect
    if (!headers_sent()) {
        header("Location: car-success.php");
        exit;
    } else {
        die("Redirect failed. Please <a href='car-success.php'>click here</a> to continue.");
    }
} else {
    die("<p style='color: red;'>❌ Error submitting your travel request. Please try again later.</p>");
}

$stmt->close();
$conn->close();
?>