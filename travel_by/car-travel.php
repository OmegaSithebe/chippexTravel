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
    echo "<p style='color: red;'>❌ Connection failed. Try again later.</p>";
    exit;
}

// Get form data
$name        = $_POST['name']        ?? '';
$email       = $_POST['email']       ?? '';
$phone       = $_POST['phone']       ?? '';
$pickup      = $_POST['pickup']      ?? '';
$destination = $_POST['destination'] ?? '';
$travel_date = $_POST['date']        ?? '';
$passengers  = $_POST['passengers']  ?? '';
$notes       = $_POST['notes']       ?? '';

// Validate required fields
if (empty($name) || empty($email) || empty($phone) || empty($pickup) || empty($destination) || empty($travel_date)) {
    echo "<p style='color: red;'>❌ Please fill in all required fields.</p>";
    exit;
}

// Save to DB
$sql = "INSERT INTO car_travel_requests 
        (full_name, email, phone, pickup_location, destination, travel_date, passengers, notes) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssss", $name, $email, $phone, $pickup, $destination, $travel_date, $passengers, $notes);

if ($stmt->execute()) {
    // Store form data in session for success page
    $_SESSION['form_data'] = $formData;

    // Email setup
    $to      = "admin@chippexstravel.co.za";
    $subject = "🚗 New Car Travel Request from $name";

    $body = "
        <h2>New Car Travel Request</h2>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Phone:</strong> $phone</p>
        <p><strong>Pickup Location:</strong> $pickup</p>
        <p><strong>Destination:</strong> $destination</p>
        <p><strong>Travel Date:</strong> $travel_date</p>
        <p><strong>Passengers:</strong> $passengers</p>
        <p><strong>Additional Notes:</strong><br>$notes</p>
        <hr>
        <small>Sent automatically from Chippexs Travel website</small>
    ";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Chippexs Travel <no-reply@chippexstravel.co.za>\r\n";

    mail($to, $subject, $body, $headers);

    header("Location: car-success.php");
    exit;

} else {
    echo "<p style='color: red;'>❌ Error submitting your travel request. Please try again later.</p>";
}

$stmt->close();
$conn->close();
?>
