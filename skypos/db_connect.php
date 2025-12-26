<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'hotelgrandguardi_root'); // Replace with your database username
define('DB_PASS', 'Sun123flower@'); // Replace with your database password
define('DB_NAME', 'hotelgrandguardi_skypos');


// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>