<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

require_once 'db_connect.php';

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['invoice_id']) || !is_numeric($input['invoice_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid invoice ID']);
    exit();
}

$invoice_id = intval($input['invoice_id']);

try {
    // Fetch invoice items with item details
    $query = "
        SELECT 
            ii.item_id,
            i.item_code,
            i.item_name,
            ii.quantity,
            ii.unit_price,
            ii.total_price
        FROM invoice_items ii
        LEFT JOIN hggitems i ON ii.item_id = i.id
        WHERE ii.invoice_id = ?
        ORDER BY ii.id ASC
    ";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param('i', $invoice_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'items' => $items
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>