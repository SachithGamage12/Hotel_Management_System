<?php
// includes/db.php
$conn = new mysqli(
    'localhost',
    'hotelgrandguardi_root',
    'Sun123flower@',
    'hotelgrandguardi_wedding_bliss'
);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'DB connection failed']));
}

// Make sure we return JSON
header('Content-Type: application/json');
?>