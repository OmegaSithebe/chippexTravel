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

// Room prices configuration
define('ROOM_PRICES', [
    'clubhouse-mansion' => 4000,
    'three-bedroom-villa' => 7500,
    'two-bedroom-villa' => 6800,
    'presidential-villa' => 15000
]);

// Currency conversion (Update this rate regularly)
define('USD_TO_ZAR_RATE', 18.5);

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

    // Validate dates
    $checkIn = $_POST['checkIn'];
    $checkOut = $_POST['checkOut'];
    if (strtotime($checkOut) <= strtotime($checkIn)) {
        $_SESSION['error'] = 'Check-out date must be after check-in date.';
        header("Location: nandoni_hotel.html");
        exit;
    }

    // Sanitize input
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $checkIn = mysqli_real_escape_string($conn, $checkIn);
    $checkOut = mysqli_real_escape_string($conn, $checkOut);
    $guests = mysqli_real_escape_string($conn, $_POST['guests']);
    $roomType = mysqli_real_escape_string($conn, $_POST['roomType']);
    $specialRequests = isset($_POST['specialRequests']) ? mysqli_real_escape_string($conn, $_POST['specialRequests']) : '';

    // Map room types to display names for emails
    $roomTypeDisplay = [
        'clubhouse-mansion' => 'Clubhouse Mansion',
        'three-bedroom-villa' => 'Three Bedroom Villa',
        'two-bedroom-villa' => 'Two Bedroom Villa',
        'presidential-villa' => 'Presidential Two Sleeper Villa'
    ];

    $roomTypeName = isset($roomTypeDisplay[$roomType]) ? $roomTypeDisplay[$roomType] : $roomType;

    // Calculate number of nights and total amounts
    $nights = floor((strtotime($checkOut) - strtotime($checkIn)) / (60 * 60 * 24));
    $pricePerNight = ROOM_PRICES[$roomType] ?? 0;
    $totalAmountZAR = $pricePerNight * $nights;
    $totalAmountUSD = round($totalAmountZAR / USD_TO_ZAR_RATE, 2);

    // Insert data using prepared statement
    $sql = "INSERT INTO nandoni_bookings 
            (full_name, email, phone, check_in, check_out, guests, room_type, special_requests, payment_amount, submitted_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        $_SESSION['error'] = 'Database error. Please try again later.';
        header("Location: nandoni_hotel.html");
        exit;
    }

    mysqli_stmt_bind_param($stmt, "ssssssssd", $name, $email, $phone, $checkIn, $checkOut, $guests, $roomType, $specialRequests, $totalAmountZAR);
    
    if (!mysqli_stmt_execute($stmt)) {
        $_SESSION['error'] = 'An error occurred while saving your booking. Please try again.';
        header("Location: nandoni_hotel.html");
        exit;
    }

    // Get the booking ID
    $bookingId = mysqli_insert_id($conn);

    // Store form data in session for payment page
    $_SESSION['booking_data'] = [
        'bookingId' => $bookingId,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'checkIn' => $checkIn,
        'checkOut' => $checkOut,
        'guests' => $guests,
        'roomType' => $roomTypeName,
        'roomTypeCode' => $roomType,
        'specialRequests' => $specialRequests,
        'nights' => $nights,
        'pricePerNight' => $pricePerNight,
        'totalAmountZAR' => $totalAmountZAR,
        'totalAmountUSD' => $totalAmountUSD,
        'exchangeRate' => USD_TO_ZAR_RATE
    ];

    // Send initial booking notification to admin
    $to = ADMIN_EMAIL;
    $subject = "New Booking Request - Nandoni Waterfront Resort (Booking #$bookingId)";
    
    $message = "
    <html>
    <head>
        <title>New Booking Notification</title>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #3498db; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .detail { margin: 10px 0; }
            .footer { background: #34495e; color: white; padding: 15px; text-align: center; }
            .amount { color: #e74c3c; font-weight: bold; font-size: 1.2em; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>New Booking Request</h1>
                <p>Nandoni Waterfront Resort</p>
            </div>
            <div class='content'>
                <h2>Booking Details</h2>
                <div class='detail'><strong>Booking ID:</strong> #$bookingId</div>
                <div class='detail'><strong>Guest Name:</strong> $name</div>
                <div class='detail'><strong>Email:</strong> $email</div>
                <div class='detail'><strong>Phone:</strong> $phone</div>
                <div class='detail'><strong>Check-in Date:</strong> $checkIn</div>
                <div class='detail'><strong>Check-out Date:</strong> $checkOut</div>
                <div class='detail'><strong>Number of Nights:</strong> $nights</div>
                <div class='detail'><strong>Number of Guests:</strong> $guests</div>
                <div class='detail'><strong>Accommodation Type:</strong> $roomTypeName</div>
                <div class='detail'><strong>Price per Night:</strong> R" . number_format($pricePerNight, 2) . "</div>
                <div class='detail'><strong>Total Amount (ZAR):</strong> <span class='amount'>R" . number_format($totalAmountZAR, 2) . "</span></div>
                <div class='detail'><strong>Total Amount (USD):</strong> <span class='amount'>$" . number_format($totalAmountUSD, 2) . "</span></div>
                <div class='detail'><strong>Exchange Rate:</strong> 1 USD = " . USD_TO_ZAR_RATE . " ZAR</div>
                <div class='detail'><strong>Special Requests:</strong> " . ($specialRequests ? nl2br($specialRequests) : 'None') . "</div>
                <div class='detail'><strong>Payment Status:</strong> <span style='color: #f39c12;'>Pending Payment</span></div>
                <div class='detail'><strong>Submitted:</strong> " . date('Y-m-d H:i:s') . "</div>
            </div>
            <div class='footer'>
                <p>Guest has been redirected to payment page. Awaiting payment confirmation.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: Nandoni Waterfront Resort <" . NOREPLY_EMAIL . ">\r\n";
    $headers .= "Reply-To: $name <$email>\r\n";
    $headers .= "X-Priority: 1\r\n";
    
    mail($to, $subject, $message, $headers);

    // Send confirmation to guest
    $guestSubject = "Your Nandoni Waterfront Resort Booking Request #$bookingId";
    $guestMessage = "
    <html>
    <head>
        <title>Booking Confirmation</title>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2ecc71; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .detail { margin: 10px 0; }
            .footer { background: #34495e; color: white; padding: 15px; text-align: center; }
            .highlight { background: #e74c3c; color: white; padding: 5px 10px; border-radius: 3px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Thank You for Your Booking Request!</h1>
                <p>Nandoni Waterfront Resort</p>
            </div>
            <div class='content'>
                <h2>Hello $name,</h2>
                <p>We've received your booking request and will contact you shortly to confirm your reservation.</p>
                
                <h3>Your Booking Details:</h3>
                <div class='detail'><strong>Booking Reference:</strong> <span class='highlight'>#$bookingId</span></div>
                <div class='detail'><strong>Accommodation:</strong> $roomTypeName</div>
                <div class='detail'><strong>Check-in:</strong> $checkIn</div>
                <div class='detail'><strong>Check-out:</strong> $checkOut</div>
                <div class='detail'><strong>Number of Nights:</strong> $nights</div>
                <div class='detail'><strong>Guests:</strong> $guests</div>
                <div class='detail'><strong>Total Amount:</strong> R" . number_format($totalAmountZAR, 2) . " (Approx. $" . number_format($totalAmountUSD, 2) . " USD)</div>
                " . ($specialRequests ? "<div class='detail'><strong>Special Requests:</strong> " . nl2br($specialRequests) . "</div>" : "") . "
                
                <p><strong>Next Steps:</strong></p>
                <ul>
                    <li>You will be redirected to our secure payment page</li>
                    <li>Complete your payment to confirm your reservation</li>
                    <li>Once paid, your booking will be secured</li>
                </ul>
                
                <p>If you have any questions, please contact us at " . CONTACT_PHONE . " or reply to this email.</p>
            </div>
            <div class='footer'>
                <p>We look forward to welcoming you to Nandoni Waterfront Resort!</p>
                <p>© " . date("Y") . " Chippexs Travel | Luxury Stays at Nandoni Waterfront</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $guestHeaders = "MIME-Version: 1.0\r\n";
    $guestHeaders .= "Content-type:text/html;charset=UTF-8\r\n";
    $guestHeaders .= "From: Nandoni Waterfront Resort <" . NOREPLY_EMAIL . ">\r\n";
    $guestHeaders .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
    $guestHeaders .= "X-Priority: 1\r\n";
    
    mail($email, $guestSubject, $guestMessage, $guestHeaders);

    // Redirect to payment page
    header("Location: nandoni_payment.php");
    exit;
    
    // Close connection
    mysqli_close($conn);
}
?>