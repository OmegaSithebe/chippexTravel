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

// Capture and sanitize form inputs
$service = $_POST['service'] ?? '';
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$travel_date = $_POST['date'] ?? '';
$message = $_POST['message'] ?? '';
$requirements = $_POST['requirements'] ?? '';

// Basic validation
if (empty($name) || empty($email) || empty($phone) || empty($message)) {
    echo "<p style='color: red;'>❌ Please fill in all required fields.</p>";
    exit;
}

// Prepare and bind SQL query
$sql = "INSERT INTO service_enquiries (service_type, full_name, email, phone, travel_date, enquiry_message, specific_requirements)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sssssss", $service, $name, $email, $phone, $travel_date, $message, $requirements);

if (mysqli_stmt_execute($stmt)) {
    echo "<p style='color: green;'>✅ Thank you, <b>$name</b>. Your enquiry about <b>$service</b> has been submitted successfully.</p>";
} else {
    echo "<p style='color: red;'>❌ Error submitting enquiry. Please try again later.</p>";
}

mysqli_close($conn);
?>
