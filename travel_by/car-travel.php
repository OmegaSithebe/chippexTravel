<?php
$servername = "chippexstravel.co.za";
$username = "chippyzr_chippexUser";
$password = "chipexTravelDev@24!";
$dbname = "chippyzr_chippex";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Capture and sanitize input
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$pickup = $_POST['pickup'] ?? '';
$destination = $_POST['destination'] ?? '';
$travel_date = $_POST['date'] ?? '';
$passengers = $_POST['passengers'] ?? '';
$notes = $_POST['notes'] ?? '';

// Validate required fields
if (empty($name) || empty($email) || empty($phone) || empty($pickup) || empty($destination) || empty($travel_date)) {
    echo "<p style='color: red;'>❌ Please fill in all required fields.</p>";
    exit;
}

// SQL insert
$sql = "INSERT INTO car_travel_requests 
        (full_name, email, phone, pickup_location, destination, travel_date, passengers, notes) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssssssss", $name, $email, $phone, $pickup, $destination, $travel_date, $passengers, $notes);

if (mysqli_stmt_execute($stmt)) {
    echo "<p style='color: green;'>✅ Thank you, <b>$name</b>. Your travel request has been submitted successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Error submitting your travel request. Please try again later.</p>";
}

mysqli_close($conn);
?>
