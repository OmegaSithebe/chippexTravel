<?php
session_start();

// Disable error display for production
ini_set('display_errors', 1);
error_reporting(E_ALL);


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
        // Store all form data in session
        $_SESSION['form_data'] = $_POST;
        
        // Capture and sanitize form data
        $formData = [
            'name' => isset($_POST['name']) ? $conn->real_escape_string(trim($_POST['name'])) : '',
            'email' => isset($_POST['email']) ? $conn->real_escape_string(trim($_POST['email'])) : '',
            'phone' => isset($_POST['phone']) ? $conn->real_escape_string(trim($_POST['phone'])) : '',
            'departureAirport' => isset($_POST['departureAirport']) ? $conn->real_escape_string(trim($_POST['departureAirport'])) : '',
            'arrivalAirport' => isset($_POST['arrivalAirport']) ? $conn->real_escape_string(trim($_POST['arrivalAirport'])) : '',
            'flightDate' => isset($_POST['flightDate']) ? $conn->real_escape_string(trim($_POST['flightDate'])) : '',
            'flightClass' => isset($_POST['flightClass']) ? $conn->real_escape_string(trim($_POST['flightClass'])) : '',
            'passengers' => isset($_POST['passengers']) ? $conn->real_escape_string(trim($_POST['passengers'])) : '',
            'notes' => isset($_POST['notes']) ? $conn->real_escape_string(trim($_POST['notes'])) : ''
        ];

        // Validate required fields
        $errors = [];
        if (empty($formData['name'])) $errors[] = "Full name is required";
        if (empty($formData['email']) || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
        if (empty($formData['phone'])) $errors[] = "Phone number is required";
        if (empty($formData['departureAirport'])) $errors[] = "Departure airport is required";
        if (empty($formData['arrivalAirport'])) $errors[] = "Arrival airport is required";
        if (empty($formData['flightDate'])) $errors[] = "Flight date is required";
        
        // Date validation
        if (!empty($formData['flightDate'])) {
            $today = new DateTime();
            $flightDateTime = new DateTime($formData['flightDate']);
            if ($flightDateTime < $today) {
                $errors[] = "Flight date cannot be in the past";
            }
        }

        if (!empty($errors)) {
            throw new Exception("Validation errors: " . implode(", ", $errors));
        }

        // Prepare SQL with error handling
        $sql = "INSERT INTO flight_travel_requests 
                (full_name, email, phone, departure_airport, arrival_airport, flight_date, flight_class, passengers, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        if (!($stmt = $conn->prepare($sql))) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        if (!$stmt->bind_param("sssssssss", 
            $formData['name'], 
            $formData['email'], 
            $formData['phone'], 
            $formData['departureAirport'], 
            $formData['arrivalAirport'], 
            $formData['flightDate'], 
            $formData['flightClass'], 
            $formData['passengers'], 
            $formData['notes']
        )) {
            throw new Exception("Bind failed: " . $stmt->error);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $last_id = $conn->insert_id;
        
        // Email notification
        $to      = "admin@chippexstravel.co.za";
        $cc      = "rogersithebe@gmail.com";
        $subject = "✈️ New Flight Booking Request from {$formData['name']}";
        
        $body = "<h2>New Flight Booking Request</h2>
                <p><strong>Request ID:</strong> $last_id</p>
                <p><strong>Name:</strong> {$formData['name']}</p>
                <p><strong>Email:</strong> {$formData['email']}</p>
                <p><strong>Phone:</strong> {$formData['phone']}</p>
                <p><strong>Departure:</strong> {$formData['departureAirport']}</p>
                <p><strong>Arrival:</strong> {$formData['arrivalAirport']}</p>
                <p><strong>Date:</strong> {$formData['flightDate']}</p>
                <p><strong>Class:</strong> {$formData['flightClass']}</p>
                <p><strong>Passengers:</strong> {$formData['passengers']}</p>
                <p><strong>Notes:</strong><br>" . nl2br($formData['notes']) . "</p>";
        
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: Chippexs Travel <no-reply@chippexstravel.co.za>'
        ];
        
        if (!empty($cc)) {
            $headers[] = "Cc: $cc";
        }
        
        mail($to, $subject, $body, implode("\r\n", $headers));
        
        // Ensure no output has been sent before redirect
        if (!headers_sent()) {
            header("Location: flight-success.php");
            exit;
        } else {
            // Fallback if headers were already sent
            echo "<script>window.location.href='flight-success.php';</script>";
            exit;
        }
    } else {
        // Not a POST request
        header("Location: flight-travel.html");
        exit;
    }
    
} catch (Exception $e) {
    error_log("Error in flight booking: " . $e->getMessage());
    
    // Output error message without preventing redirect
    echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.location.href='flight-travel.html';</script>";
    exit;
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>