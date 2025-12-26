<?php
// File 3: db_connect.php (if not already exists)
// Database connection configuration

$host = 'localhost';
$dbname = 'hotelgrandguardi_lakway_delivery';
$username = 'hotelgrandguardi_root';
$password = 'Sun123flower@';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed']));
}

$conn->set_charset('utf8mb4');
?>

