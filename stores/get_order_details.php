<?php
header('Content-Type: application/json');

// Database connection
$conn = new mysqli('localhost', 'hotelgrandguardi_root', 'Sun123flower@', 'hotelgrandguardi_wedding_bliss');
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

$orderNo = intval($_GET['order_no']);

// Get order header info
$stmt = $conn->prepare("
    SELECT os.order_sheet_no, os.request_date, r.name as responsible, 
           (SELECT COUNT(*) FROM order_sheet WHERE order_sheet_no = ? AND status = 'pending') as pending_items
    FROM order_sheet os
    LEFT JOIN responsible r ON os.responsible_id = r.id
    WHERE os.order_sheet_no = ?
    GROUP BY os.order_sheet_no
");
$stmt->bind_param("ii", $orderNo, $orderNo);
$stmt->execute();
$result = $stmt->get_result();
$orderData = $result->fetch_assoc();

// Get order items with stock info
$stmt = $conn->prepare("
    SELECT os.item_id, i.item_name, os.requested_qty, i.unit, os.status,
           (SELECT COALESCE(SUM(pi.stock), 0) 
            FROM purchased_items pi 
            WHERE pi.item_id = os.item_id AND pi.stock > 0 
            AND (pi.expiry_date IS NULL OR pi.expiry_date >= CURDATE())) as available_stock
    FROM order_sheet os
    JOIN inventory i ON os.item_id = i.id
    WHERE os.order_sheet_no = ?
");
$stmt->bind_param("i", $orderNo);
$stmt->execute();
$result = $stmt->get_result();
$orderData['items'] = $result->fetch_all(MYSQLI_ASSOC);

// Determine overall status
$orderData['status'] = ($orderData['pending_items'] == 0) ? 'issued' : 'pending';

echo json_encode($orderData);
$conn->close();
?>