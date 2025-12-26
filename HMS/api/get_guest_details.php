<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'hotelgrandguardi_root', 'Sun123flower@', 'hotelgrandguardi_wedding_bliss');
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed']));
}
$grc_number = $_GET['grc_number'] ?? '';
if (empty($grc_number)) {
    echo json_encode(['success' => false, 'error' => 'GRC number is required']);
    exit;
}
$stmt = $conn->prepare("SELECT * FROM guests WHERE grc_number = ?");
$stmt->bind_param("i", $grc_number);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $guest = $result->fetch_assoc();
    $guest['rooms'] = json_decode($guest['rooms'], true);
    echo json_encode(['success' => true, 'data' => $guest]);
} else {
    echo json_encode(['success' => false, 'error' => 'Guest not found']);
}
$stmt->close();
$conn->close();
?>