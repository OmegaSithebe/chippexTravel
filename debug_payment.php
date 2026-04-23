<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'chippyzr_chippexUser');
define('DB_PASSWORD', 'chipexTravelDev@24!');
define('DB_NAME', 'chippyzr_chippex');

echo "<h2>Payment System Debug</h2>";

// Test database connection
echo "<h3>Database Connection Test:</h3>";
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if (!$conn) {
    echo "<p style='color: red;'>Database connection failed: " . mysqli_connect_error() . "</p>";
} else {
    echo "<p style='color: green;'>Database connection successful!</p>";
    
    // Check if payments table exists and has correct structure
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'payments'");
    if (mysqli_num_rows($result) > 0) {
        echo "<p style='color: green;'>Payments table exists</p>";
        
        // Show table structure
        $structure = mysqli_query($conn, "DESCRIBE payments");
        echo "<h4>Payments Table Structure:</h4>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = mysqli_fetch_assoc($structure)) {
            echo "<tr>";
            echo "<td>{$row['Field']}</td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "<td>{$row['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>Payments table does not exist!</p>";
    }
    
    mysqli_close($conn);
}

// Test file permissions
echo "<h3>File Permissions:</h3>";
$files = ['payment_form.php', 'payment_processing.php', 'payment_success.php', 'payment_success_page.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>$file exists</p>";
    } else {
        echo "<p style='color: red;'>$file does not exist</p>";
    }
}

// Test session
echo "<h3>Session Test:</h3>";
session_start();
if (isset($_SESSION['payment_data'])) {
    echo "<p style='color: green;'>Payment data in session: " . print_r($_SESSION['payment_data'], true) . "</p>";
} else {
    echo "<p style='color: orange;'>No payment data in session</p>";
}

echo "<h3>PHP Configuration:</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Memory Limit: " . ini_get('memory_limit') . "</p>";
echo "<p>Max Execution Time: " . ini_get('max_execution_time') . "</p>";
echo "<p>Post Max Size: " . ini_get('post_max_size') . "</p>";
echo "<p>Upload Max Filesize: " . ini_get('upload_max_filesize') . "</p>";
?>