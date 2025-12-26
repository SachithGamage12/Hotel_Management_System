
<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'hotelgrandguardi_root', 'Sun123flower@', 'hotelgrandguardi_wedding_bliss');
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    echo json_encode(['error' => 'Connection failed']);
    exit;
}
$item_id = intval($_GET['item_id']);
$stmt = $conn->prepare("SELECT COALESCE(SUM(remaining_qty), 0) as remaining_qty FROM function_unload WHERE item_id = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
echo json_encode(['remaining_qty' => $row['remaining_qty']]);
$stmt->close();
$conn->close();
?>
