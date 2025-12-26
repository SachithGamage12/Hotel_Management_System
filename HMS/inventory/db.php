
<?php
$conn = new mysqli("localhost", "hotelgrandguardi_root", "Sun123flower@", "hotelgrandguardi_wedding_bliss");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>