<?php
header('Content-Type: application/json');

$store_id = isset($_GET['store_id']) ? $_GET['store_id'] : null;

if (!$store_id) {
    echo json_encode(['success' => false, 'error' => 'Store ID required']);
    exit;
}

// Your database connection
$conn = new mysqli("localhost", "hotelgrandguardi_root", "Sun123flower@", "hotelgrandguardi_lakway_delivery");

$query = "SELECT id, store_id, name, description, price, quantity, image FROM uber_food_items WHERE store_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $store_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    // Add 10% commission to the price
    $row['price'] = $row['price'] * 1.10;
    $items[] = $row;
}

echo json_encode(['success' => true, 'items' => $items]);
$stmt->close();
$conn->close();
?>