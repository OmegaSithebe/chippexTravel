<?php
// Database configuration - put these in a separate config.php file if possible
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'chippyzr_chippexUser');
define('DB_PASSWORD', 'chipexTravelDev@24!');
define('DB_NAME', 'chippyzr_chippex');

// Email configuration
define('ADMIN_EMAIL', 'admin@chippexstravel.co.za');
define('NOREPLY_EMAIL', 'noreply@chippexstravel.co.za');
define('CONTACT_PHONE', '+27 73 474 2034');

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create database connection
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Check connection
    if (!$conn) {
        error_log("Database connection failed: " . mysqli_connect_error());
        http_response_code(500);
        die(json_encode(['status' => 'error', 'message' => 'Database connection failed. Please try again later.']));
    }

    // Validate required fields
    $required = ['name', 'email', 'phone', 'checkIn', 'checkOut', 'guests', 'roomType'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            http_response_code(400);
            die(json_encode(['status' => 'error', 'message' => 'Please complete all required fields.']));
        }
    }

    // Validate email format
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        die(json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']));
    }

    // Sanitize input
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $checkIn = mysqli_real_escape_string($conn, $_POST['checkIn']);
    $checkOut = mysqli_real_escape_string($conn, $_POST['checkOut']);
    $guests = mysqli_real_escape_string($conn, $_POST['guests']);
    $roomType = mysqli_real_escape_string($conn, $_POST['roomType']);
    $specialRequests = isset($_POST['specialRequests']) ? mysqli_real_escape_string($conn, $_POST['specialRequests']) : '';

    // Insert data using prepared statement - adjusted for your submitted_at field
    $sql = "INSERT INTO nandoni_bookings 
            (full_name, email, phone, check_in, check_out, guests, room_type, special_requests, submitted_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($conn));
        http_response_code(500);
        die(json_encode(['status' => 'error', 'message' => 'Database error. Please try again later.']));
    }

    mysqli_stmt_bind_param($stmt, "ssssssss", $name, $email, $phone, $checkIn, $checkOut, $guests, $roomType, $specialRequests);
    
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Booking failed: " . mysqli_error($conn));
        http_response_code(500);
        die(json_encode(['status' => 'error', 'message' => 'An error occurred while saving your booking. Please try again.']));
    }

    // Booking saved successfully - now send email
    $to = ADMIN_EMAIL;
    $subject = "New Booking at Nandoni Hotel";
    
    $message = "
    <html>
    <head>
        <title>New Booking Notification</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .booking-details { background: #f9f9f9; padding: 20px; border-radius: 5px; }
            .booking-details h2 { color: #333; margin-top: 0; }
            .detail-row { margin-bottom: 10px; }
            .detail-label { font-weight: bold; display: inline-block; width: 150px; }
        </style>
    </head>
    <body>
        <div class='booking-details'>
            <h2>New Booking Details</h2>
            <div class='detail-row'><span class='detail-label'>Guest Name:</span> $name</div>
            <div class='detail-row'><span class='detail-label'>Email:</span> $email</div>
            <div class='detail-row'><span class='detail-label'>Phone:</span> $phone</div>
            <div class='detail-row'><span class='detail-label'>Check-in Date:</span> $checkIn</div>
            <div class='detail-row'><span class='detail-label'>Check-out Date:</span> $checkOut</div>
            <div class='detail-row'><span class='detail-label'>Number of Guests:</span> $guests</div>
            <div class='detail-row'><span class='detail-label'>Room Type:</span> $roomType</div>
            <div class='detail-row'><span class='detail-label'>Special Requests:</span> " . nl2br($specialRequests) . "</div>
            <div class='detail-row'><span class='detail-label'>Booking Time:</span> " . date('Y-m-d H:i:s') . "</div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Nandoni Hotel Booking System <" . NOREPLY_EMAIL . ">" . "\r\n";
    $headers .= "Reply-To: $name <$email>" . "\r\n";
    
    $mailSent = mail($to, $subject, $message, $headers);
    
    if (!$mailSent) {
        error_log("Failed to send booking confirmation email to admin");
    }
    
    // Send confirmation to guest
    $guestSubject = "Your Nandoni Waterfront Resort Booking Confirmation";
    $guestMessage = "
    <html>
    <head>
        <title>Booking Confirmation</title>
    </head>
    <body>
        <h2>Thank you for your booking, $name!</h2>
        <p>We've received your booking request for $roomType from $checkIn to $checkOut.</p>
        <p>Our team will review your request and contact you shortly to confirm your reservation.</p>
        <p>If you have any questions, please don't hesitate to contact us at " . CONTACT_PHONE . " or reply to this email.</p>
    </body>
    </html>
    ";
    
    $guestHeaders = "MIME-Version: 1.0" . "\r\n";
    $guestHeaders .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $guestHeaders .= "From: Nandoni Hotel Booking System <" . NOREPLY_EMAIL . ">" . "\r\n";
    $guestHeaders .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
    
    $guestMailSent = mail($email, $guestSubject, $guestMessage, $guestHeaders);
    
    $response = [
        'status' => 'success',
        'message' => "Thank you, $name. Your booking has been received successfully!",
        'email_sent' => $mailSent,
        'confirmation_sent' => $guestMailSent
    ];
    
    echo json_encode($response);
    
    // Close connection
    mysqli_close($conn);
    exit;
}
?>