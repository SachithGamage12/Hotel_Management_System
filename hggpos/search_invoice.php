<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

try {
    $invoice_number = isset($_GET['invoice_number']) ? trim($_GET['invoice_number']) : '';

    if (empty($invoice_number)) {
        echo json_encode(['success' => false, 'error' => 'Invoice number is required']);
        exit;
    }

    // Fetch invoice details
    $stmt = $conn->prepare("
        SELECT id, invoice_number, table_number, payment_type, creditor_name, other_creditor_name, 
               foc_responsible, cashier, subtotal, discount, service_charge, grand_total, 
               status, created_at, delivery_place, delivery_charge
        FROM invoices 
        WHERE invoice_number = ?
    ");
    $stmt->bind_param('s', $invoice_number);
    $stmt->execute();
    $result = $stmt->get_result();
    $invoice = $result->fetch_assoc();

    if (!$invoice) {
        echo json_encode(['success' => false, 'error' => 'Invoice not found']);
        exit;
    }

    // Fetch invoice items with item details from items table
    $stmt = $conn->prepare("
        SELECT ii.item_id, i.item_name, i.item_code, ii.quantity, ii.unit_price AS price, ii.total_price AS total
        FROM invoice_items ii
        JOIN hggitems i ON ii.item_id = i.id
        WHERE ii.invoice_id = ?
    ");
    $stmt->bind_param('i', $invoice['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'success' => true,
        'invoice' => $invoice,
        'items' => $items
    ]);

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>