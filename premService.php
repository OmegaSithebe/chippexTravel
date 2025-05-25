<?php
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
$service      = $_POST['service']      ?? '';
$name         = $_POST['name']         ?? '';
$email        = $_POST['email']        ?? '';
$phone        = $_POST['phone']        ?? '';
$travel_date  = $_POST['date']         ?? '';
$message      = $_POST['message']      ?? '';
$requirements = $_POST['requirements'] ?? '';

// Validate
if (empty($name) || empty($email) || empty($phone) || empty($message)) {
    echo "<p style='color: red;'>❌ Please fill in all required fields.</p>";
    exit;
}

// Save to DB
$sql = "INSERT INTO service_enquiries 
        (service_type, full_name, email, phone, travel_date, enquiry_message, specific_requirements)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", $service, $name, $email, $phone, $travel_date, $message, $requirements);

if ($stmt->execute()) {
    echo "<p style='color: green;'>✅ Thank you, <b>$name</b>. Your enquiry has been submitted successfully.</p>";

    // Email content
    $to      = "admin@chippexstravel.co.za";    
    $subject = "📝 New Service Enquiry from $name";

    $body = "
        <h2>New Enquiry Received</h2>
        <p><strong>Service:</strong> $service</p>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Phone:</strong> $phone</p>
        <p><strong>Travel Date:</strong> $travel_date</p>
        <p><strong>Message:</strong><br>$message</p>
        <p><strong>Requirements:</strong><br>$requirements</p>
        <hr>
        <small>Sent automatically from Chippex Travel website</small>
    ";

    // Email headers
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Chippex Travel <no-reply@chippexstravel.co.za>\r\n";
    $headers .= "Cc: $cc\r\n";

    // Send email
    mail($to, $subject, $body, $headers);
} else {
    echo "<p style='color: red;'>❌ Could not save your enquiry. Please try again.</p>";
}

$stmt->close();
$conn->close();
?>
