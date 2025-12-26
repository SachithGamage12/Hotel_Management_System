<?php
header('Content-Type: application/json');

// Database connection
$conn = new mysqli('localhost', 'hotelgrandguardi_root', 'Sun123flower@', 'hotelgrandguardi_wedding_bliss');
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Get pending orders
$result = $conn->query("
    SELECT os.order_sheet_no, os.request_date, r.name as responsible,
           JSON_ARRAYAGG(
               JSON_OBJECT(
                   'item_id', i.id,
                   'item_name', i.item_name,
                   'requested_qty', os.requested_qty,
                   'unit', i.unit,
                   'status', os.status
               )
           ) as items
    FROM order_sheet os
    JOIN inventory i ON os.item_id = i.id
    LEFT JOIN responsible r ON os.responsible_id = r.id
    WHERE os.status = 'pending'
    GROUP BY os.order_sheet_no
    ORDER BY os.order_sheet_no DESC
");

$orders = [];
while ($row = $result->fetch_assoc()) {
    $row['items'] = json_decode($row['items'], true);
    $orders[] = $row;
}

echo json_encode($orders);
$conn->close();
?>