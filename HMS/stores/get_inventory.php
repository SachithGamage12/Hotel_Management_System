<?php
header('Content-Type: application/json');

// Database connection
$conn = new mysqli('localhost', 'hotelgrandguardi_root', 'Sun123flower@', 'hotelgrandguardi_wedding_bliss');
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Get inventory with available stock
$result = $conn->query("
    SELECT i.id, i.item_name, i.unit,
           COALESCE(SUM(pi.stock), 0) as available_stock,
           MAX(pi.purchased_date) as last_purchase_date,
           MIN(CASE WHEN pi.expiry_date > CURDATE() THEN pi.expiry_date ELSE NULL END) as expiry_date
    FROM inventory i
    LEFT JOIN purchased_items pi ON i.id = pi.item_id AND pi.stock > 0
    GROUP BY i.id
    ORDER BY i.item_name
");

$inventory = [];
while ($row = $result->fetch_assoc()) {
    $inventory[] = $row;
}

echo json_encode($inventory);
$conn->close();
?>