<?php
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$servername = "localhost";
$username = "chippyzr_chippexUser";
$password = "chipexTravelDev@24!";
$dbname = "chippyzr_chippex";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Validate required fields
$required = ['name', 'email', 'phone', 'checkIn', 'checkOut', 'guests', 'roomType'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['error'] = 'Please complete all required fields.';
        header("Location: vahlavi_hotel.html");
        exit;
    }
}

// Validate email format
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Please enter a valid email address.';
    header("Location: vahlavi_hotel.html");
    exit;
}

// Process form data
$name = mysqli_real_escape_string($conn, $_POST['name']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$phone = mysqli_real_escape_string($conn, $_POST['phone']);
$checkIn = mysqli_real_escape_string($conn, $_POST['checkIn']);
$checkOut = mysqli_real_escape_string($conn, $_POST['checkOut']);
$guests = mysqli_real_escape_string($conn, $_POST['guests']);
$roomType = mysqli_real_escape_string($conn, $_POST['roomType']);
$specialRequests = isset($_POST['specialRequests']) ? mysqli_real_escape_string($conn, $_POST['specialRequests']) : '';
$packages = isset($_POST['packages']) ? $_POST['packages'] : [];
$packagesString = is_array($packages) ? implode(", ", $packages) : '';

// Insert into database
$sql = "INSERT INTO vahlavi_bookings 
        (full_name, email, phone, check_in, check_out, guests, room_type, packages, special_requests, submitted_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    $_SESSION['error'] = 'Database error. Please try again later.';
    header("Location: vahlavi_hotel.html");
    exit;
}

mysqli_stmt_bind_param($stmt, "sssssssss", $name, $email, $phone, $checkIn, $checkOut, $guests, $roomType, $packagesString, $specialRequests);

if (mysqli_stmt_execute($stmt)) {
    // Store data in session for success page
    $_SESSION['form_data'] = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'checkIn' => $checkIn,
        'checkOut' => $checkOut,
        'guests' => $guests,
        'roomType' => $roomType,
        'packages' => $packagesString,
        'specialRequests' => $specialRequests
    ];
    
    // Send email notification to admin
    $to = "admin@chippexstravel.co.za";
    $subject = "New Booking at Vahlavi Guest House";
    
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
            <div class='detail-row'><span class='detail-label'>Packages:</span> $packagesString</div>
            <div class='detail-row'><span class='detail-label'>Special Requests:</span> " . nl2br($specialRequests) . "</div>
            <div class='detail-row'><span class='detail-label'>Booking Time:</span> " . date('Y-m-d H:i:s') . "</div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Vahlavi Guest House Booking System <noreply@chippexstravel.co.za>" . "\r\n";
    
    mail($to, $subject, $message, $headers);
    
    // Send confirmation to guest
    $guestSubject = "Your Vahlavi Guest House Booking Confirmation";
    $guestMessage = "
    <html>
    <head>
        <title>Booking Confirmation</title>
    </head>
    <body>
        <h2>Thank you for your booking, $name!</h2>
        <p>We've received your booking request for $roomType from $checkIn to $checkOut.</p>
        <p>Selected packages: " . ($packagesString ? $packagesString : 'None') . "</p>
        <p>Our team will review your request and contact you shortly to confirm your reservation.</p>
        <p>If you have any questions, please don't hesitate to contact us at +27 73 474 2034.</p>
    </body>
    </html>
    ";
    
    $guestHeaders = "MIME-Version: 1.0" . "\r\n";
    $guestHeaders .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $guestHeaders .= "From: Vahlavi Guest House <noreply@chippexstravel.co.za>" . "\r\n";
    $guestHeaders .= "Reply-To: admin@chippexstravel.co.za" . "\r\n";
    
    mail($email, $guestSubject, $guestMessage, $guestHeaders);
    
    // Redirect to success page
    header("Location: vahlavi-success.php");
    exit;
} else {
    $_SESSION['error'] = "Error: " . mysqli_error($conn);
    header("Location: vahlavi_hotel.html");
    exit;
}

mysqli_close($conn);
?>