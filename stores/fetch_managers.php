<?php
$conn = new mysqli('localhost', 'hotelgrandguardi_root', 'Sun123flower@', 'hotelgrandguardi_wedding_bliss');
if ($conn->connect_error) {
    echo json_encode(['error' => 'Connection failed']);
    exit;
}
$result = $conn->query("SELECT id, name FROM responsibilities");
$managers = [];
while ($row = $result->fetch_assoc()) {
    $managers[] = ['id' => $row['id'], 'username' => $row['name']]; // Map 'name' to 'username' for JS compatibility
}
$conn->close();
echo json_encode($managers);
?>