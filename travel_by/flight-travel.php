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

// Collect and sanitize input
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$departureAirport = $_POST['departureAirport'] ?? '';
$arrivalAirport = $_POST['arrivalAirport'] ?? '';
$flightDate = $_POST['flightDate'] ?? '';
$flightClass = $_POST['flightClass'] ?? 'economy';
$passengers = $_POST['passengers'] ?? '';
$notes = $_POST['notes'] ?? '';

// Validate required fields
if (empty($name) || empty($email) || empty($phone) || empty($departureAirport) || empty($arrivalAirport) || empty($flightDate)) {
    echo "<p style='color: red;'>❌ Please fill in all required fields.</p>";
    exit;
}

// Prepare SQL query
$sql = "INSERT INTO flight_travel_requests 
        (full_name, email, phone, departure_airport, arrival_airport, flight_date, flight_class, passengers, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sssssssss", $name, $email, $phone, $departureAirport, $arrivalAirport, $flightDate, $flightClass, $passengers, $notes);

if (mysqli_stmt_execute($stmt)) {
    echo "<p style='color: green;'>✅ Thank you, <b>$name</b>. Your flight booking request has been submitted successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ There was an error submitting your request. Please try again later.</p>";
}

mysqli_close($conn);
?>
