<?php
header('Content-Type: application/json');
session_start();

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'debug.log');

// Set time zone to IST (+0530)
date_default_timezone_set('Asia/Kolkata');

try {
    // Include database connection
    require_once 'db_connect.php';
    
    // Get raw input and log for debugging
    $rawInput = file_get_contents('php://input');
    file_put_contents('debug.log', "Raw input: " . $rawInput . "\n", FILE_APPEND);
    
    // Parse JSON input
    $data = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        file_put_contents('debug.log', "JSON error: " . json_last_error_msg() . "\n", FILE_APPEND);
        throw new Exception('Invalid JSON input: ' . json_last_error_msg());
    }
    
    // Validate required fields
    $required = ['booking_reference', 'value_type', 'amount_type'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            throw new Exception("Missing or empty required field: $field");
        }
    }

    // Validate booking_reference length
    if (strlen(trim($data['booking_reference'])) !== 4) {
        throw new Exception('Booking reference must be 4 characters');
    }

    // Validate NIC and contact_no if provided
if (isset($data['nic']) && trim($data['nic']) !== '') {
    $nics = explode('/', trim($data['nic']));
    foreach ($nics as $nic) {
        $nic = trim($nic);
        if (!preg_match('/^(?:\d{9}[VvXx]|\d{12})$/', $nic)) {
            throw new Exception('Invalid NIC format: ' . $nic . '. Expected either 9 digits followed by V, v, X, or x or 12 digits');
        }
    }
}

    if (isset($data['contact_no']) && trim($data['contact_no']) !== '' && !preg_match('/^[0-9]{10}(?:\s*\/\s*[0-9]{10})?$/', trim($data['contact_no']))) {
        throw new Exception('Invalid Contact No format. Expected 10 digits or two 10-digit numbers separated by " / "');
    }

    // Validate rooms array
    if (!isset($data['rooms']) || !is_array($data['rooms']) || empty($data['rooms'])) {
        throw new Exception('At least one room must be provided');
    }

    foreach ($data['rooms'] as $room) {
        if (!isset($room['number']) || empty(trim($room['number']))) {
            throw new Exception('Room number is required for all rooms');
        }
        if (!isset($room['type']) || empty(trim($room['type']))) {
            throw new Exception('Room type is required for all rooms');
        }
        if (!isset($room['hotel']) || empty(trim($room['hotel']))) {
            throw new Exception('Hotel is required for all rooms');
        }
    }

    // Start transaction
    $pdo->beginTransaction();

    // Ensure room_invoice_counter table exists and has a record
    $stmt = $pdo->query("SELECT COUNT(*) FROM room_invoice_counter");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO room_invoice_counter (last_invoice_number) VALUES (2080)");
    }

    // Get and increment invoice number with locking
    $stmt = $pdo->query("SELECT last_invoice_number FROM room_invoice_counter LIMIT 1 FOR UPDATE");
    $lastInvoiceNumber = $stmt->fetchColumn();
    $newInvoiceNumber = $lastInvoiceNumber + 1;

    // Check if invoice number exceeds 9999
    if ($newInvoiceNumber > 9999) {
        throw new Exception('Invoice number limit reached (9999). Please reset or modify the counter.');
    }

    // Format invoice number as INV-XXXX
    $formattedInvoiceNumber = sprintf('INV-%04d', $newInvoiceNumber);

    // Update invoice counter
    $stmt = $pdo->prepare("UPDATE room_invoice_counter SET last_invoice_number = ?");
    $stmt->execute([$newInvoiceNumber]);

    // Prepare payment data
    $total_amount = $data['amount_type'] === 'FOC' ? null : (isset($data['total_amount']) && trim($data['total_amount']) !== '' ? (float)$data['total_amount'] : null);
    $advance_payment = $data['amount_type'] === 'FOC' ? null : (isset($data['advance_payment']) && trim($data['advance_payment']) !== '' ? (float)$data['advance_payment'] : null);
    $pending_amount = $data['amount_type'] === 'FOC' ? null : (isset($data['pending_amount']) && trim($data['pending_amount']) !== '' ? (float)$data['pending_amount'] : null);

    // Save payment to room_payments
    $stmt = $pdo->prepare("
        INSERT INTO room_payments (
            booking_reference, 
            invoice_number, 
            ac_type,
            meal_plan,
            remarks,
            value_type, 
            amount_type,
            total_amount,
            advance_payment,
            pending_amount,
            issued_by,
            nic,
            contact_no
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $success = $stmt->execute([
        trim($data['booking_reference']),
        $formattedInvoiceNumber,
        isset($data['ac_type']) && trim($data['ac_type']) !== '' ? trim($data['ac_type']) : null,
        isset($data['meal_plan']) && trim($data['meal_plan']) !== '' ? trim($data['meal_plan']) : null,
        isset($data['remarks']) && trim($data['remarks']) !== '' ? trim($data['remarks']) : null,
        trim($data['value_type']),
        trim($data['amount_type']),
        $total_amount,
        $advance_payment,
        $pending_amount,
        $_SESSION['username'] ?? 'Admin',
        isset($data['nic']) && trim($data['nic']) !== '' ? trim($data['nic']) : null,
        isset($data['contact_no']) && trim($data['contact_no']) !== '' ? trim($data['contact_no']) : null
    ]);

    if (!$success) {
        throw new Exception('Failed to save payment');
    }

    // Get the newly created payment ID
    $paymentId = $pdo->lastInsertId();

    // Save room details to room_payment_details
    $stmt = $pdo->prepare("
        INSERT INTO room_payment_details (
            payment_id,
            room_number,
            room_type,
            hotel
        ) VALUES (?, ?, ?, ?)
    ");

    foreach ($data['rooms'] as $room) {
        $success = $stmt->execute([
            $paymentId,
            trim($room['number']),
            trim($room['type']),
            trim($room['hotel'])
        ]);

        if (!$success) {
            throw new Exception('Failed to save room details');
        }
    }

    // Get the newly created payment record
    $stmt = $pdo->prepare("SELECT invoice_number, payment_date FROM room_payments WHERE id = ?");
    $stmt->execute([$paymentId]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        throw new Exception('Failed to retrieve payment record');
    }

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
    file_put_contents('debug.log', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>