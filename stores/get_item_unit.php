<?php
session_start();
header('Content-Type: application/json');

if (!isset($_GET['item_id'])) {
    echo json_encode(['error' => 'Item ID required']);
    exit;
}

$item_id = intval($_GET['item_id']);
$conn = new mysqli('localhost', 'hotelgrandguardi_root', 'Sun123flower@', 'hotelgrandguardi_wedding_bliss');

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("SELECT unit FROM inventory WHERE id = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(['unit' => $row['unit']]);
} else {
    echo json_encode(['unit' => 'Unit']);
}

$conn->close();
?>