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
$hotelName = mysqli_real_escape_string($conn, $_POST['hotelName'] ?? '');
$checkIn = mysqli_real_escape_string($conn, $_POST['checkIn'] ?? '');
$checkOut = mysqli_real_escape_string($conn, $_POST['checkOut'] ?? '');
$userName = mysqli_real_escape_string($conn, $_POST['userName'] ?? '');
$userEmail = mysqli_real_escape_string($conn, $_POST['userEmail'] ?? '');
$userPhone = mysqli_real_escape_string($conn, $_POST['userPhone'] ?? '');

// Validate required fields
if (empty($hotelName) || empty($checkIn) || empty($checkOut) || empty($userName) || empty($userEmail) || empty($userPhone)) {
    echo json_encode(['status' => 'error', 'message' => 'Please complete all required fields.']);
    exit;
}

// Validate email format
if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']);
    exit;
}

// Insert data into database
$sql = "INSERT INTO check_availability
        (hotel_name, check_in, check_out, user_name, user_email, user_phone) 
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssssss", $hotelName, $checkIn, $checkOut, $userName, $userEmail, $userPhone);

if (mysqli_stmt_execute($stmt)) {
    // Send email notification
    $to = "admin@chippexstravel.co.za";
    $subject = "New Availability Request for $hotelName";
    
    $message = "
    <html>
    <head>
        <title>New Availability Request</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .request-details { background: #f9f9f9; padding: 20px; border-radius: 5px; }
            .request-details h2 { color: #333; margin-top: 0; }
            .detail-row { margin-bottom: 10px; }
            .detail-label { font-weight: bold; display: inline-block; width: 150px; }
        </style>
    </head>
    <body>
        <div class='request-details'>
            <h2>New Availability Request</h2>
            <div class='detail-row'><span class='detail-label'>Hotel:</span> $hotelName</div>
            <div class='detail-row'><span class='detail-label'>Check-in Date:</span> $checkIn</div>
            <div class='detail-row'><span class='detail-label'>Check-out Date:</span> $checkOut</div>
            <div class='detail-row'><span class='detail-label'>Guest Name:</span> $userName</div>
            <div class='detail-row'><span class='detail-label'>Email:</span> $userEmail</div>
            <div class='detail-row'><span class='detail-label'>Phone:</span> $userPhone</div>
            <div class='detail-row'><span class='detail-label'>Request Time:</span> " . date('Y-m-d H:i:s') . "</div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Chippexs Travel <noreply@chippexstravel.co.za>" . "\r\n";
    
    $mailSent = mail($to, $subject, $message, $headers);
    
    // Send confirmation to guest
    $guestSubject = "Your Availability Request for $hotelName";
    $guestMessage = "
    <html>
    <head>
        <title>Availability Request Confirmation</title>
    </head>
    <body>
        <h2>Thank you for your request, $userName!</h2>
        <p>We've received your availability request for $hotelName from $checkIn to $checkOut.</p>
        <p>Our team will check availability and contact you shortly at $userPhone or $userEmail.</p>
        <p>If you have any questions, please don't hesitate to contact us at +27 73 474 2034.</p>
    </body>
    </html>
    ";
    
    $guestHeaders = "MIME-Version: 1.0" . "\r\n";
    $guestHeaders .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $guestHeaders .= "From: Chippexs Travel <noreply@chippexstravel.co.za>" . "\r\n";
    $guestHeaders .= "Reply-To: admin@chippexstravel.co.za" . "\r\n";
    
    $guestMailSent = mail($userEmail, $guestSubject, $guestMessage, $guestHeaders);
    
    $response = [
        'status' => 'success',
        'message' => "Thank you, $userName. Your availability request has been received successfully!",
        'email_sent' => $mailSent,
        'confirmation_sent' => $guestMailSent
    ];
    
    echo json_encode($response);
} else {
    $error = mysqli_error($conn);
    error_log("Availability request failed: " . $error);
    echo json_encode(['status' => 'error', 'message' => 'An error occurred while processing your request. Please try again.', 'debug' => $error]);
}

// Close connection
mysqli_close($conn);
exit;
?>