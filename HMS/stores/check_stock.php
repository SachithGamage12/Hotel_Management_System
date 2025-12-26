<?php
$conn = new mysqli('localhost', 'hotelgrandguardi_root', 'Sun123flower@', 'hotelgrandguardi_wedding_bliss');
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed']));
}

$item_id = intval($_GET['item_id']);
$today = date('Y-m-d');

$stmt = $conn->prepare("
    SELECT SUM(pi.stock) as total_stock
    FROM purchased_items pi
    WHERE pi.item_id = ? AND pi.stock > 0 AND (pi.expiry_date IS NULL OR pi.expiry_date >= ?)
");
$stmt->bind_param("is", $item_id, $today);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$regular_stock = intval($row['total_stock'] ?? 0);
$stmt->close();

$stmt = $conn->prepare("SELECT buffer_stock FROM inventory WHERE id = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$buffer_stock = intval($row['buffer_stock'] ?? 0);
$stmt->close();

$conn->close();

echo json_encode([
    'available_stock' => $regular_stock,
    'buffer_stock' => $buffer_stock
]);
?>