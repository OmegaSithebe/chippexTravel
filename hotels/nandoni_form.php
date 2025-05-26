<?php
session_start();

// Database configuration
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
        $_SESSION['error'] = 'Database connection failed. Please try again later.';
        header("Location: nandoni_hotel.html");
        exit;
    }

    // Validate required fields
    $required = ['name', 'email', 'phone', 'checkIn', 'checkOut', 'guests', 'roomType'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = 'Please complete all required fields.';
            header("Location: nandoni_hotel.html");
            exit;
        }
    }

    // Validate email format
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Please enter a valid email address.';
        header("Location: nandoni_hotel.html");
        exit;
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

    // Insert data using prepared statement
    $sql = "INSERT INTO nandoni_bookings 
            (full_name, email, phone, check_in, check_out, guests, room_type, special_requests, submitted_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        $_SESSION['error'] = 'Database error. Please try again later.';
        header("Location: nandoni_hotel.html");
        exit;
    }

    mysqli_stmt_bind_param($stmt, "ssssssss", $name, $email, $phone, $checkIn, $checkOut, $guests, $roomType, $specialRequests);
    
    if (!mysqli_stmt_execute($stmt)) {
        $_SESSION['error'] = 'An error occurred while saving your booking. Please try again.';
        header("Location: nandoni_hotel.html");
        exit;
    }

    // Store form data in session for success page
    $_SESSION['form_data'] = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'checkIn' => $checkIn,
        'checkOut' => $checkOut,
        'guests' => $guests,
        'roomType' => $roomType,
        'specialRequests' => $specialRequests
    ];

    // Send email to admin
    $to = ADMIN_EMAIL;
    $subject = "New Booking at Nandoni Hotel";
    
    $message = "
    <html>
    <head>
        <title>New Booking Notification</title>
    </head>
    <body>
        <h2>New Booking Details</h2>
        <p><strong>Guest Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Phone:</strong> $phone</p>
        <p><strong>Check-in Date:</strong> $checkIn</p>
        <p><strong>Check-out Date:</strong> $checkOut</p>
        <p><strong>Number of Guests:</strong> $guests</p>
        <p><strong>Room Type:</strong> $roomType</p>
        <p><strong>Special Requests:</strong> " . nl2br($specialRequests) . "</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: Nandoni Hotel Booking System <" . NOREPLY_EMAIL . ">\r\n";
    $headers .= "Reply-To: $name <$email>\r\n";
    
    mail($to, $subject, $message, $headers);

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
    </body>
    </html>
    ";
    
    $guestHeaders = "MIME-Version: 1.0\r\n";
    $guestHeaders .= "Content-type:text/html;charset=UTF-8\r\n";
    $guestHeaders .= "From: Nandoni Hotel Booking System <" . NOREPLY_EMAIL . ">\r\n";
    $guestHeaders .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
    
    mail($email, $guestSubject, $guestMessage, $guestHeaders);

    // Redirect to success page
    header("Location: nandoni-success.php");
    exit;
    
    // Close connection
    mysqli_close($conn);
}
?>