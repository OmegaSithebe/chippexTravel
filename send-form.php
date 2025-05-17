<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Collect and sanitize form inputs
    $name        = htmlspecialchars(trim($_POST["name"]));
    $email       = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $phone       = htmlspecialchars(trim($_POST["phone"]));
    $pickup      = htmlspecialchars(trim($_POST["pickup"]));
    $destination = htmlspecialchars(trim($_POST["destination"]));
    $date        = htmlspecialchars(trim($_POST["date"]));
    $passengers  = htmlspecialchars(trim($_POST["passengers"]));
    $notes       = htmlspecialchars(trim($_POST["notes"]));

    // Validate required fields
    if ($name && $email && $phone && $pickup && $destination && $date && $passengers) {

        // Destination email
        $to = "admin@chippexstrave.co.za";

        // Subject
        $subject = "🚗 New Road Trip Booking Request from $name";

        // Construct HTML message
        $message = "
        <html>
        <head>
          <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .content { background-color: #f9f9f9; padding: 20px; border-radius: 8px; }
            h2 { color: #007bff; }
            strong { color: #555; }
          </style>
        </head>
        <body>
          <div class='content'>
            <h2>🚗 New Road Trip Booking Request</h2>
            <p><strong>Name:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Phone:</strong> $phone</p>
            <p><strong>Pickup Location:</strong> $pickup</p>
            <p><strong>Destination:</strong> $destination</p>
            <p><strong>Travel Date:</strong> $date</p>
            <p><strong>Passengers:</strong> $passengers</p>
            <p><strong>Additional Notes:</strong><br>" . nl2br($notes) . "</p>
            <hr>
            <p style='font-size: 13px; color: #999;'>This message was sent from your website car travel booking form.</p>
          </div>
        </body>
        </html>";

        // Set headers
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: admin@chippexstrave.co.za\r\n"; 
        $headers .= "Reply-To: $email\r\n";
        $headers .= "Cc: rogersithebe@gmail.com\r\n"; 

        // Log message to file (for testing)
        file_put_contents("mail-log.txt", "Sending to: $to\nSubject: $subject\n\n$message\n\n-----\n", FILE_APPEND);

        // Send the email
        if (mail($to, $subject, $message, $headers)) {
            echo "<p>✅ Thank you, <strong>$name</strong>. Your trip request has been sent successfully!</p>";
        } else {
            error_log("❌ Email sending failed to $to");
            echo "<p>❌ Something went wrong. Please try again later or contact us directly.</p>";
        }

    } else {
        echo "<p>⚠️ Please fill in all required fields.</p>";
    }

} else {
    echo "<p>⚠️ Invalid request. Please submit the form correctly.</p>";
}
?>
