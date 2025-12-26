<?php
// config/db_connection.php

define('DB_HOST', 'localhost');
define('DB_USER', 'hotelgrandguardi_root');
define('DB_PASS', 'Sun123flower@');
define('DB_NAME', 'hotelgrandguardi_wedding_bliss');

function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}
?>