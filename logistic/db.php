<?php
// File: db.php
$servername = "localhost";  // Update if needed
$username = "hotelgrandguardi_root";         // Update to your DB username
$password = "Sun123flower@";             // Update to your DB password
$dbname = "hotelgrandguardi_wedding_bliss";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>