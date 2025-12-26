<?php
header('Content-Type: application/json');
session_start();

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    require_once 'db_connect.php';
    
    // Get JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate required fields
    $required = ['booking_reference', 'value_type', 'total_amount', 'payment_type', 'payment_amount', 'pending_amount'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Start transaction
    $pdo->beginTransaction();

    // Ensure invoice_counter table exists and has a record
    $stmt = $pdo->query("SELECT COUNT(*) FROM invoice_counter");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO invoice_counter (last_invoice_number) VALUES (1400)");
    }

    // Get and increment invoice number with locking
    $stmt = $pdo->query("SELECT last_invoice_number FROM invoice_counter LIMIT 1 FOR UPDATE");
    $lastInvoiceNumber = $stmt->fetchColumn();
    $newInvoiceNumber = $lastInvoiceNumber + 1;

    // Check if invoice number exceeds 9999
    if ($newInvoiceNumber > 9999) {
        throw new Exception('Invoice number limit reached (9999). Please reset or modify the counter.');
    }

    // Format invoice number as 4 digits (e.g., 1400, 1401, ..., 9999)
    $formattedInvoiceNumber = sprintf('%04d', $newInvoiceNumber);

    // Update invoice counter
    $stmt = $pdo->prepare("UPDATE invoice_counter SET last_invoice_number = ?");
    $stmt->execute([$newInvoiceNumber]);

    // Save payment with all fields including new ones
    $stmt = $pdo->prepare("
        INSERT INTO payments (
            booking_reference, 
            invoice_number, 
            contact_no,
            whatsapp_no,
            email,
            rate_per_plate, 
            additional_plate_rate,
            remarks,
            value_type, 
            total_amount, 
            payment_type,
            payment_amount,
            pending_amount, 
            no_of_pax,
            issued_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $success = $stmt->execute([
        $data['booking_reference'],
        'INV-' . $formattedInvoiceNumber,
        $data['contact_no'] ?? null,
        $data['whatsapp_no'] ?? null,
        $data['email'] ?? null,
        isset($data['rate_per_plate']) ? (float)$data['rate_per_plate'] : null,
        isset($data['additional_plate_rate']) ? (float)$data['additional_plate_rate'] : null,
        $data['remarks'] ?? null,
        $data['value_type'],
        (float)$data['total_amount'],
        $data['payment_type'],
        (float)$data['payment_amount'],
        (float)$data['pending_amount'],
        isset($data['no_of_pax']) ? (int)$data['no_of_pax'] : null,
        $_SESSION['username'] ?? null
    ]);
    
    if (!$success) {
        throw new Exception('Failed to save payment');
    }

    // Get the newly created payment record
    $paymentId = $pdo->lastInsertId();
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = ?");
    $stmt->execute([$paymentId]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'invoice_number' => $payment['invoice_number'],
        'payment_date' => $payment['payment_date']
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>