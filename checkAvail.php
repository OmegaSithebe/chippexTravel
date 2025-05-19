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

// Retrieve POST data
$hotel = $_POST['hotel'] ?? '';
$checkIn = $_POST['check_in'] ?? '';
$checkOut = $_POST['check_out'] ?? '';

if (empty($hotel) || empty($checkIn) || empty($checkOut)) {
    echo "<p style='color: red;'>❌ Please fill in all the fields.</p>";
    exit;
}

// SQL to check availability (example assumes a table `bookings` with columns: hotel_name, check_in, check_out)
$sql = "SELECT * FROM bookings 
        WHERE hotel_name = ? 
        AND (check_in < ? AND check_out > ?)";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sss", $hotel, $checkOut, $checkIn);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    echo "<p style='color: red;'>❌ Sorry, no availability for <b>$hotel</b> from <b>$checkIn</b> to <b>$checkOut</b>.</p>";
} else {
    echo "<p style='color: green;'>✅ <b>$hotel</b> is available from <b>$checkIn</b> to <b>$checkOut</b>.</p>";
}

mysqli_close($conn);
?>
