<?php
header('Content-Type: application/json');

// DB connection
$conn = new mysqli("localhost", "hotelgrandguardi_root", "Sun123flower@", "hotelgrandguardi_wedding_bliss");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$buffer_id = $data['buffer_id'] ?? null;
$unload = $data['unload'] ?? null;
$remaining_quantity = $data['remaining_quantity'] ?? null;

if (!$buffer_id || !is_numeric($unload) || !is_numeric($remaining_quantity)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

// Update kitchen_buffer
$stmt = $conn->prepare("UPDATE hggkitchen_buffer SET quantity = ?, remaining_quantity = ?, last_updated = CURRENT_TIMESTAMP WHERE buffer_id = ?");
$stmt->bind_param("dii", $unload, $remaining_quantity, $buffer_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Update failed']);
}

$stmt->close();
$conn->close();
?>