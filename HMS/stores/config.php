<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'hotelgrandguardi_root');
define('DB_PASS', 'Sun123flower@');
define('DB_NAME', 'hotelgrandguardi_wedding_bliss');

// Create database connection
try {
    $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session
session_start();

// Set timezone
date_default_timezone_set('Asia/Colombo');
?>