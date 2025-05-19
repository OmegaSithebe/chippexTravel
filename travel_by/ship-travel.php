<?php
$servername = "chippexstravel.co.za";
$username = "chippyzr_chippexUser";
$password = "chipexTravelDev@24!";
$dbname = "chippyzr_chippex";

// Connect to database
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Capture and sanitize form data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$departurePort = $_POST['departurePort'] ?? '';
$arrivalPort = $_POST['arrivalPort'] ?? '';
$sailingDate = $_POST['sailingDate'] ?? '';
$cabinType = $_POST['cabinType'] ?? 'inside';
$passengers = $_POST['passengers'] ?? '';
$notes = $_POST['notes'] ?? '';

// Validate required fields
if (empty($name) || empty($email) || empty($phone) || empty($departurePort) || empty($arrivalPort) || empty($sailingDate)) {
    echo "<p style='color: red;'>❌ Please complete all required fields.</p>";
    exit;
}

// Insert into database
$sql = "INSERT INTO ship_travel_requests 
        (full_name, email, phone, departure_port, arrival_port, sailing_date, cabin_type, passengers, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sssssssss", $name, $email, $phone, $departurePort, $arrivalPort, $sailingDate, $cabinType, $passengers, $notes);

if (mysqli_stmt_execute($stmt)) {
    echo "<p style='color: green;'>✅ Thank you, <b>$name</b>. Your ship travel booking request has been received successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ An error occurred. Please try again later.</p>";
}

// Close connection
mysqli_close($conn);
?>
