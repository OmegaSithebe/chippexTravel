<?php
// Enable full error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log errors to a file
ini_set('log_errors', 1);
ini_set('error_log', 'flight_booking_errors.log');

// DB connection
$servername = "localhost";
$username   = "chippyzr_chippexUser";
$password   = "chipexTravelDev@24!";
$dbname     = "chippyzr_chippex";

// Create connection with error reporting
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Check if form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Debug: Log all POST data
        error_log("Received POST data: " . print_r($_POST, true));
        
        // Capture and sanitize form data
        $name             = isset($_POST['name']) ? $conn->real_escape_string(trim($_POST['name'])) : '';
        $email            = isset($_POST['email']) ? $conn->real_escape_string(trim($_POST['email'])) : '';
        $phone            = isset($_POST['phone']) ? $conn->real_escape_string(trim($_POST['phone'])) : '';
        $departureAirport = isset($_POST['departureAirport']) ? $conn->real_escape_string(trim($_POST['departureAirport'])) : '';
        $arrivalAirport   = isset($_POST['arrivalAirport']) ? $conn->real_escape_string(trim($_POST['arrivalAirport'])) : '';
        $flightDate       = isset($_POST['flightDate']) ? $conn->real_escape_string(trim($_POST['flightDate'])) : '';
        $flightClass      = isset($_POST['flightClass']) ? $conn->real_escape_string(trim($_POST['flightClass'])) : '';
        $passengers       = isset($_POST['passengers']) ? $conn->real_escape_string(trim($_POST['passengers'])) : '';
        $notes            = isset($_POST['notes']) ? $conn->real_escape_string(trim($_POST['notes'])) : '';

        // Validate required fields
        $errors = [];
        if (empty($name)) $errors[] = "Full name is required";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
        if (empty($phone)) $errors[] = "Phone number is required";
        if (empty($departureAirport)) $errors[] = "Departure airport is required";
        if (empty($arrivalAirport)) $errors[] = "Arrival airport is required";
        if (empty($flightDate)) $errors[] = "Flight date is required";
        
        // Date validation
        if (!empty($flightDate)) {
            $today = new DateTime();
            $flightDateTime = new DateTime($flightDate);
            if ($flightDateTime < $today) {
                $errors[] = "Flight date cannot be in the past";
            }
        }

        if (!empty($errors)) {
            throw new Exception("Validation errors: " . implode(", ", $errors));
        }

        // Prepare SQL with error handling (modified to remove created_at)
        $sql = "INSERT INTO flight_travel_requests 
                (full_name, email, phone, departure_airport, arrival_airport, flight_date, flight_class, passengers, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        if (!($stmt = $conn->prepare($sql))) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        if (!$stmt->bind_param("sssssssss", $name, $email, $phone, $departureAirport, $arrivalAirport, $flightDate, $flightClass, $passengers, $notes)) {
            throw new Exception("Bind failed: " . $stmt->error);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $last_id = $conn->insert_id;
        error_log("Successfully inserted booking with ID: $last_id");
        
        // Success response
        $response = [
            'status' => 'success',
            'message' => "✅ Thank you, $name. Your flight request (ID: $last_id) has been received!",
            'id' => $last_id
        ];
        
        // Email notification
        $to      = "admin@chippexstravel.co.za";
        $cc      = "rogersithebe@gmail.com";
        $subject = "✈️ New Flight Booking Request from $name";
        
        $body = "<h2>New Flight Booking Request</h2>
                <p><strong>Request ID:</strong> $last_id</p>
                <p><strong>Name:</strong> $name</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Phone:</strong> $phone</p>
                <p><strong>Departure:</strong> $departureAirport</p>
                <p><strong>Arrival:</strong> $arrivalAirport</p>
                <p><strong>Date:</strong> $flightDate</p>
                <p><strong>Class:</strong> $flightClass</p>
                <p><strong>Passengers:</strong> $passengers</p>
                <p><strong>Notes:</strong><br>" . nl2br($notes) . "</p>";
        
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: Chippexs Travel <no-reply@chippexstravel.co.za>'
        ];
        
        if (!empty($cc)) {
            $headers[] = "Cc: $cc";
        }
        
        if (!mail($to, $subject, $body, implode("\r\n", $headers))) {
            error_log("Failed to send email notification for booking ID: $last_id");
            $response['email_sent'] = false;
        } else {
            $response['email_sent'] = true;
        }
        
        // Output JSON response
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
        
    } else {
        // Not a POST request
        header("Location: flight-booking-form.html");
        exit;
    }
    
} catch (Exception $e) {
    error_log("Error in flight booking: " . $e->getMessage());
    
    $response = [
        'status' => 'error',
        'message' => "❌ Error: " . $e->getMessage(),
        'details' => $e->getTraceAsString()
    ];
    
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode($response);
    exit;
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>