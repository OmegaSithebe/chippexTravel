<?php
// Database configuration
$servername = "localhost";
$username = "chippyzr_chippexUser";
$password = "chipexTravelDev@24!";
$dbname = "chippyzr_chippex";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . mysqli_connect_error()]));
}

// Capture and sanitize form data
$name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
$email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
$phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
$checkIn = mysqli_real_escape_string($conn, $_POST['checkIn'] ?? '');
$checkOut = mysqli_real_escape_string($conn, $_POST['checkOut'] ?? '');
$guests = mysqli_real_escape_string($conn, $_POST['guests'] ?? '');
$roomType = mysqli_real_escape_string($conn, $_POST['roomType'] ?? '');
$specialRequests = mysqli_real_escape_string($conn, $_POST['specialRequests'] ?? '');
$addons = isset($_POST['addons']) ? $_POST['addons'] : [];
$addonsString = is_array($addons) ? implode(", ", $addons) : '';

// Validate required fields
if (empty($name) || empty($email) || empty($phone) || empty($checkIn) || empty($checkOut) || empty($guests) || empty($roomType)) {
    echo json_encode(['status' => 'error', 'message' => 'Please complete all required fields.']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']);
    exit;
}

// Insert data into database
$sql = "INSERT INTO xisaka_bookings 
        (full_name, email, phone, check_in, check_out, guests, room_type, addons, special_requests, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sssssssss", $name, $email, $phone, $checkIn, $checkOut, $guests, $roomType, $addonsString, $specialRequests);

if (mysqli_stmt_execute($stmt)) {
    // Send email notification
    $to = "admin@chippexstravel.co.za";
    $subject = "New Booking at Xisaka Guest House";
    
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
            <div class='detail-row'><span class='detail-label'>Add-ons:</span> $addonsString</div>
            <div class='detail-row'><span class='detail-label'>Special Requests:</span> " . nl2br($specialRequests) . "</div>
            <div class='detail-row'><span class='detail-label'>Booking Time:</span> " . date('Y-m-d H:i:s') . "</div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Xisaka Guest House Booking System <noreply@chippexstravel.co.za>" . "\r\n";
    
    $mailSent = mail($to, $subject, $message, $headers);
    
    // Send confirmation to guest
    $guestSubject = "Your Xisaka Guest House Booking Confirmation";
    $guestMessage = "
    <html>
    <head>
        <title>Booking Confirmation</title>
    </head>
    <body>
        <h2>Thank you for your booking, $name!</h2>
        <p>We've received your booking request for $roomType from $checkIn to $checkOut.</p>
        <p>Selected add-ons: " . ($addonsString ? $addonsString : 'None') . "</p>
        <p>Our team will review your request and contact you shortly to confirm your reservation.</p>
        <p>If you have any questions, please don't hesitate to contact us at +27 73 474 2034.</p>
    </body>
    </html>
    ";
    
    $guestHeaders = "MIME-Version: 1.0" . "\r\n";
    $guestHeaders .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $guestHeaders .= "From: Xisaka Guest House <noreply@chippexstravel.co.za>" . "\r\n";
    $guestHeaders .= "Reply-To: admin@chippexstravel.co.za" . "\r\n";
    
    $guestMailSent = mail($email, $guestSubject, $guestMessage, $guestHeaders);
    
    $response = [
        'status' => 'success',
        'message' => "Thank you, $name. Your booking has been received successfully!",
        'email_sent' => $mailSent,
        'confirmation_sent' => $guestMailSent
    ];
    
    echo json_encode($response);
} else {
    $error = mysqli_error($conn);
    error_log("Booking failed: " . $error);
    echo json_encode(['status' => 'error', 'message' => 'An error occurred while saving your booking. Please try again.', 'debug' => $error]);
}

// Close connection
mysqli_close($conn);
exit;
?>