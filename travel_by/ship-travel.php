<?php
// Enable error display for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// DB connection
$servername = "localhost";
$username   = "chippyzr_chippexUser";
$password   = "chipexTravelDev@24!";
$dbname     = "chippyzr_chippex";

// Create connection with error reporting
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection with detailed error
if ($conn->connect_error) {
    die("<p style='color: red;'>❌ Connection failed: " . $conn->connect_error . "</p>");
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture form data with additional sanitization
    $name          = isset($_POST['name']) ? $conn->real_escape_string($_POST['name']) : '';
    $email         = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
    $phone         = isset($_POST['phone']) ? $conn->real_escape_string($_POST['phone']) : '';
    $departurePort = isset($_POST['departurePort']) ? $conn->real_escape_string($_POST['departurePort']) : '';
    $arrivalPort   = isset($_POST['arrivalPort']) ? $conn->real_escape_string($_POST['arrivalPort']) : '';
    $sailingDate   = isset($_POST['sailingDate']) ? $conn->real_escape_string($_POST['sailingDate']) : '';
    $cabinType     = isset($_POST['cabinType']) ? $conn->real_escape_string($_POST['cabinType']) : '';
    $passengers    = isset($_POST['passengers']) ? $conn->real_escape_string($_POST['passengers']) : '';
    $notes         = isset($_POST['notes']) ? $conn->real_escape_string($_POST['notes']) : '';

    // Debug: Show received data
    echo "<pre style='display:none;'>";
    print_r($_POST);
    echo "</pre>";

    // Validate required fields
    $errors = [];
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($phone)) $errors[] = "Phone is required";
    if (empty($departurePort)) $errors[] = "Departure port is required";
    if (empty($arrivalPort)) $errors[] = "Arrival port is required";
    if (empty($sailingDate)) $errors[] = "Sailing date is required";

    if (!empty($errors)) {
        echo "<div style='background:#ffeeee; padding:15px; border-radius:5px; margin-bottom:20px;'>";
        echo "<p style='color: red; font-weight:bold;'>❌ Please fix the following errors:</p>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li style='color: red;'>$error</li>";
        }
        echo "</ul>";
        echo "</div>";
    } else {
        // Prepare SQL with error handling
        $sql = "INSERT INTO ship_travel_requests 
                (full_name, email, phone, departure_port, arrival_port, sailing_date, cabin_type, passengers, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            die("<p style='color: red;'>❌ Prepare failed: " . $conn->error . "</p>");
        }
        
        $bind_result = $stmt->bind_param("sssssssss", $name, $email, $phone, $departurePort, $arrivalPort, $sailingDate, $cabinType, $passengers, $notes);
        
        if (!$bind_result) {
            die("<p style='color: red;'>❌ Bind failed: " . $stmt->error . "</p>");
        }
        
        $execute_result = $stmt->execute();
        
        if ($execute_result) {
            $last_id = $conn->insert_id;
            echo "<div style='background:#eeffee; padding:15px; border-radius:5px; margin-bottom:20px;'>";
            echo "<p style='color: green; font-weight:bold;'>✅ Thank you, <b>$name</b>. Your booking request (ID: $last_id) has been received successfully!</p>";
            echo "</div>";

            // Email notification
            $to      = "admin@chippexstravel.co.za";
            $subject = "🛳️ New Ship Travel Request from $name";

            $body = "
                <h2>New Ship Travel Request</h2>
                <p><strong>Request ID:</strong> $last_id</p>
                <p><strong>Name:</strong> $name</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Phone:</strong> $phone</p>
                <p><strong>Departure Port:</strong> $departurePort</p>
                <p><strong>Arrival Port:</strong> $arrivalPort</p>
                <p><strong>Sailing Date:</strong> $sailingDate</p>
                <p><strong>Cabin Type:</strong> $cabinType</p>
                <p><strong>Passengers:</strong> $passengers</p>
                <p><strong>Additional Notes:</strong><br>$notes</p>
                <hr>
                <small>Sent automatically from Chippexs Travel website</small>
            ";

            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: Chippexs Travel <no-reply@chippexstravel.co.za>\r\n";
            
            if (!empty($cc)) {
                $headers .= "Cc: $cc\r\n";
            }

            $mail_sent = mail($to, $subject, $body, $headers);
            
            if (!$mail_sent) {
                echo "<p style='color: orange;'>⚠️ Booking was saved but email notification failed to send.</p>";
            }
        } else {
            echo "<div style='background:#ffeeee; padding:15px; border-radius:5px; margin-bottom:20px;'>";
            echo "<p style='color: red; font-weight:bold;'>❌ Error: " . $stmt->error . "</p>";
            echo "<p>SQL: " . htmlspecialchars($sql) . "</p>";
            echo "</div>";
        }

        $stmt->close();
    }
    
    $conn->close();
}
?>