<?php
header('Content-Type: application/json');

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    $db = new PDO('mysql:host=localhost;dbname=hotelgrandguardi_wedding_bliss', 'hotelgrandguardi_root', 'Sun123flower@');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate required fields
    $required = ['booking_reference', 'value_type', 'total_amount', 'payment_type', 'payment_amount', 'pending_amount'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            throw new Exception("Missing required field: $field");
        }
    }

    // Start transaction
    $db->beginTransaction();

    // Ensure invoice_counter table exists and has a record
    $stmt = $db->query("SELECT COUNT(*) FROM invoice_counter");
    if ($stmt->fetchColumn() == 0) {
        $db->exec("INSERT INTO invoice_counter (last_invoice_number) VALUES (1400)");
    }

    // Get and increment invoice number with locking
    $stmt = $db->query("SELECT last_invoice_number FROM invoice_counter LIMIT 1 FOR UPDATE");
    $lastInvoiceNumber = $stmt->fetchColumn();
    $newInvoiceNumber = $lastInvoiceNumber + 1;

    // Check if invoice number exceeds 9999
    if ($newInvoiceNumber > 9999) {
        throw new Exception('Invoice number limit reached (9999). Please reset or modify the counter.');
    }

    // Format invoice number as 4 digits (e.g., 1400, 1401, ..., 9999)
    $formattedInvoiceNumber = sprintf('%04d', $newInvoiceNumber);

    // Update invoice counter
    $stmt = $db->prepare("UPDATE invoice_counter SET last_invoice_number = ?");
    $stmt->execute([$newInvoiceNumber]);

    // Save payment
    $stmt = $db->prepare("
        INSERT INTO payments (
            booking_reference, invoice_number, rate_per_plate, remarks,
            value_type, total_amount, payment_type, payment_amount,
            pending_amount, no_of_pax
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $success = $stmt->execute([
        $data['booking_reference'],
        'INV-' . $formattedInvoiceNumber,
        $data['rate_per_plate'] ?? null,
        $data['remarks'] ?? '',
        $data['value_type'],
        $data['total_amount'],
        $data['payment_type'],
        $data['payment_amount'],
        $data['pending_amount'],
        $data['no_of_pax'] ?? null
    ]);
    
    if (!$success) {
        throw new Exception('Failed to save payment');
    }

    $db->commit();
    
    echo json_encode([
        'success' => true,
        'invoice_number' => 'INV-' . $formattedInvoiceNumber
    ]);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>