<?php
session_start();

// Disable error display for production (consider logging instead)
ini_set('display_errors', 0);
error_reporting(0);

// DB connection
$servername = "localhost";
$username   = "chippyzr_chippexUser";
$password   = "chipexTravelDev@24!";
$dbname     = "chippyzr_chippex";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed");
    }

    // Get and sanitize form data
    $service      = filter_input(INPUT_POST, 'service', FILTER_SANITIZE_STRING);
    $name         = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email        = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone        = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $travel_date  = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    $message      = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    $requirements = filter_input(INPUT_POST, 'requirements', FILTER_SANITIZE_STRING);

    // Validate
    if (empty($name) || empty($email) || empty($phone) || empty($message)) {
        throw new Exception("Please fill in all required fields");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email address format");
    }

    // Store in session for success page
    $_SESSION['form_data'] = [
        'service' => $service,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'date' => $travel_date,
        'message' => $message,
        'requirements' => $requirements
    ];

    // Save to DB
    $sql = "INSERT INTO service_enquiries 
            (service_type, full_name, email, phone, travel_date, enquiry_message, specific_requirements)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Database preparation failed");
    }

    $stmt->bind_param("sssssss", $service, $name, $email, $phone, $travel_date, $message, $requirements);

    if (!$stmt->execute()) {
        throw new Exception("Could not save your enquiry");
    }

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
    
    // Send email (consider using a library like PHPMailer for better reliability)
    if (!mail($to, $subject, $body, $headers)) {
        // Log email failure but don't show to user
        error_log("Failed to send email for enquiry from $email");
    }

    // Redirect on success
    header('Location: premService-success.php');
    exit();

} catch (Exception $e) {
    // Store error in session and redirect to error page
    $_SESSION['error'] = $e->getMessage();
    header('Location: premService-error.php');
    exit();
} finally {
    // Clean up resources
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}