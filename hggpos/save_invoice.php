<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);
error_log("Input data: " . print_r($data, true), 3, 'debug.log');

// Validate input data
if (!$data || !isset($data['invoiceNumber'], $data['paymentType'], $data['total'], $data['items'], $data['user']) || !is_array($data['items']) || empty($data['items'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid or missing invoice data']);
    exit;
}

// Extract and validate input
$invoiceNumber = trim($data['invoiceNumber']);
$paymentType = $data['paymentType'];
$creditorName = $data['creditorName'] ?? null;
$focResponsible = $data['focResponsible'] ?? null;
$deliveryPlace = $data['deliveryPlace'] ?? null;
$deliveryCharge = isset($data['deliveryCharge']) ? floatval($data['deliveryCharge']) : null;
$discount = isset($data['discount']) ? floatval($data['discount']) : 0.0;
$tableNumber = isset($data['tableNumber']) && is_numeric($data['tableNumber']) ? intval($data['tableNumber']) : null;
$total = floatval($data['total']);
$cashier = trim($data['user']);
$items = $data['items'];

// Validate payment type
$validPaymentTypes = ['cash_customer', 'cash_staff', 'card_customer', 'card_staff', 'credit', 'other_credit', 'foc', 'take_away', 'delivery'];
if (!in_array($paymentType, $validPaymentTypes)) {
    echo json_encode(['success' => false, 'error' => 'Invalid payment type']);
    exit;
}

// Validate required fields for credit, other_credit, FOC, and delivery
if (in_array($paymentType, ['credit', 'other_credit']) && empty($creditorName)) {
    echo json_encode(['success' => false, 'error' => 'Creditor name is required for credit or other credit payment']);
    exit;
}
if ($paymentType === 'foc' && empty($focResponsible)) {
    echo json_encode(['success' => false, 'error' => 'Responsible person is required for FOC payment']);
    exit;
}
if ($paymentType === 'delivery' && (empty($deliveryPlace) || !is_numeric($deliveryCharge) || $deliveryCharge < 0)) {
    echo json_encode(['success' => false, 'error' => 'Delivery place and valid delivery charge are required for delivery payment']);
    exit;
}

// Validate discount
if (!is_numeric($discount) || $discount < 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid discount value']);
    exit;
}

// Validate invoice number format
if (!preg_match('/^\d{4}$/', $invoiceNumber)) {
    echo json_encode(['success' => false, 'error' => 'Invalid invoice number format']);
    exit;
}

// Validate items
foreach ($items as $item) {
    if (!isset($item['id'], $item['quantity'], $item['price'], $item['total']) ||
        !is_numeric($item['id']) || !is_numeric($item['quantity']) || !is_numeric($item['price']) || !is_numeric($item['total']) ||
        $item['quantity'] <= 0 || $item['quantity'] > 1000 || $item['price'] < 0 || $item['total'] < 0 || $item['total'] > 1000000) {
        echo json_encode(['success' => false, 'error' => 'Invalid item data or exceeds limits']);
        exit;
    }
}

// Calculate subtotal, service charge, and total
$subtotal = array_sum(array_column($items, 'total'));
$netSubtotal = $subtotal - $discount;
$serviceCharge = in_array($paymentType, ['cash_customer', 'card_customer', 'other_credit']) ? $netSubtotal * 0.1 : 0.0;

// FOC bills should have total 0, otherwise calculate normally
if ($paymentType === 'foc') {
    $calculatedTotal = 0.0;
} else {
    $calculatedTotal = $netSubtotal + $serviceCharge + ($paymentType === 'delivery' ? $deliveryCharge : 0.0);
}

// Log calculations for debugging
error_log("Payment Type: $paymentType, Subtotal: $subtotal, Discount: $discount, Net Subtotal: $netSubtotal, Service Charge: $serviceCharge, Delivery Charge: $deliveryCharge, Calculated Total: $calculatedTotal, Received Total: $total", 3, 'debug.log');

// Validate total with special handling for FOC
if (abs($total - $calculatedTotal) > 0.01) {
    error_log("Total mismatch: received=$total, calculated=$calculatedTotal, payment_type=$paymentType", 3, 'error.log');
    echo json_encode(['success' => false, 'error' => 'Total mismatch']);
    exit;
}

try {
    $conn->begin_transaction();

    // Insert into invoices table
    $stmt = $conn->prepare("
        INSERT INTO invoices (invoice_number, table_number, payment_type, creditor_name, foc_responsible, delivery_place, delivery_charge, cashier, subtotal, discount, service_charge, grand_total)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$stmt) {
        throw new Exception("Prepare failed for invoices: " . $conn->error);
    }
    $stmt->bind_param(
        'sissssdsdddd',
        $invoiceNumber,
        $tableNumber,
        $paymentType,
        $creditorName,
        $focResponsible,
        $deliveryPlace,
        $deliveryCharge,
        $cashier,
        $subtotal,
        $discount,
        $serviceCharge,
        $total
    );
    $stmt->execute();
    $invoiceId = $conn->insert_id;
    $stmt->close();

    // Insert into invoice_items table - CORRECTED: Using hggitems table
    $stmt = $conn->prepare("
        INSERT INTO invoice_items (invoice_id, item_id, quantity, unit_price, total_price)
        VALUES (?, ?, ?, ?, ?)
    ");
    if (!$stmt) {
        throw new Exception("Prepare failed for invoice_items: " . $conn->error);
    }
    foreach ($items as $item) {
        $itemId = intval($item['id']);
        $quantity = intval($item['quantity']);
        $unitPrice = floatval($item['price']);
        $totalPrice = floatval($item['total']);
        $stmt->bind_param('iiidd', $invoiceId, $itemId, $quantity, $unitPrice, $totalPrice);
        $stmt->execute();
    }
    $stmt->close();

    // Update stock in hggitems table - CORRECTED: Using hggitems instead of items
    $stmt = $conn->prepare("UPDATE hggitems SET stock = stock - ? WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed for stock update: " . $conn->error);
    }
    foreach ($items as $item) {
        $itemId = intval($item['id']);
        $quantity = intval($item['quantity']);
        $stmt->bind_param('ii', $quantity, $itemId);
        $stmt->execute();
    }
    $stmt->close();

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (mysqli_sql_exception $e) {
    $conn->rollback();
    $error = $e->getMessage();
    if ($e->getCode() == 1062) { // Duplicate entry
        $error = "Invoice number $invoiceNumber already exists";
    }
    error_log("Database error: " . $error, 3, 'error.log');
    echo json_encode(['success' => false, 'error' => $error]);
} catch (Exception $e) {
    $conn->rollback();
    error_log("General error: " . $e->getMessage(), 3, 'error.log');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>