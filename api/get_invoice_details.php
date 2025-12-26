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

    // Get invoice number from query parameter
    $invoiceNumber = $_GET['invoice_number'] ?? '';

    // Validate invoice number format (e.g., INV-XXXX)
    if (empty($invoiceNumber) || !preg_match('/^INV-\d{4}$/', $invoiceNumber)) {
        throw new Exception('Invalid invoice number format. Expected format: INV-XXXX');
    }

    // Log the received invoice number
    file_put_contents('debug.log', "Received invoice number: $invoiceNumber\n", FILE_APPEND);

    // Define allowed amount_type values
    $allowedAmountTypes = ['FOC', 'Amount', 'Added to package', 'Add to Menu', 'Add to Repayment'];

    // Fetch payment details with wedding_bookings_history first
    $stmt = $pdo->prepare("
        SELECT 
            rp.id,
            rp.invoice_number,
            rp.booking_reference,
            rp.ac_type,
            rp.meal_plan,
            rp.remarks,
            rp.value_type,
            rp.amount_type,
            rp.total_amount,
            rp.advance_payment,
            rp.pending_amount,
            rp.added_amount,
            rp.payment_date,
            rp.issued_by,
            rp.nic,
            rp.contact_no,
            wbh.full_name,
            wbh.couple_name,
            wbh.booking_date,
            wbh.groom_address,
            wbh.bride_address,
            ft.name AS function_type,
            v.name AS venue
        FROM room_payments rp
        LEFT JOIN wedding_bookings_history wbh ON rp.booking_reference = wbh.booking_reference
        LEFT JOIN function_types ft ON wbh.function_type_id = ft.id
        LEFT JOIN venues v ON wbh.venue_id = v.id
        WHERE rp.invoice_number = :invoice_number
        ORDER BY wbh.updated_at DESC
        LIMIT 1
    ");
    $stmt->execute([':invoice_number' => $invoiceNumber]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    // Log query execution for debugging
    file_put_contents('debug.log', "History query for invoice $invoiceNumber, found: " . ($payment ? 'yes' : 'no') . "\n", FILE_APPEND);

    // If no payment found or no matching booking in wedding_bookings_history, try wedding_bookings
    if (!$payment || !$payment['full_name']) {
        $stmt = $pdo->prepare("
            SELECT 
                rp.id,
                rp.invoice_number,
                rp.booking_reference,
                rp.ac_type,
                rp.meal_plan,
                rp.remarks,
                rp.value_type,
                rp.amount_type,
                rp.total_amount,
                rp.advance_payment,
                rp.pending_amount,
                rp.added_amount,
                rp.payment_date,
                rp.issued_by,
                rp.nic,
                rp.contact_no,
                wb.full_name,
                wb.couple_name,
                wb.booking_date,
                wb.groom_address,
                wb.bride_address,
                ft.name AS function_type,
                v.name AS venue
            FROM room_payments rp
            LEFT JOIN wedding_bookings wb ON rp.booking_reference = wb.booking_reference
            LEFT JOIN function_types ft ON wb.function_type_id = ft.id
            LEFT JOIN venues v ON wb.venue_id = v.id
            WHERE rp.invoice_number = :invoice_number
        ");
        $stmt->execute([':invoice_number' => $invoiceNumber]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        // Log query execution for debugging
        file_put_contents('debug.log', "Bookings query for invoice $invoiceNumber, found: " . ($payment ? 'yes' : 'no') . "\n", FILE_APPEND);
    }

    if (!$payment) {
        throw new Exception('Invoice not found');
    }

    // Validate amount_type
    if (!in_array($payment['amount_type'], $allowedAmountTypes)) {
        file_put_contents('debug.log', "Invalid amount_type for invoice $invoiceNumber: {$payment['amount_type']}\n", FILE_APPEND);
        throw new Exception('Invalid amount type in payment record');
    }

    // Validate added_amount for specific amount_types
    if (in_array($payment['amount_type'], ['Added to package', 'Add to Menu', 'Add to Repayment']) && 
        ($payment['added_amount'] === null || !is_numeric($payment['added_amount']) || (float)$payment['added_amount'] < 0)) {
        file_put_contents('debug.log', "Invalid added_amount for invoice $invoiceNumber: {$payment['added_amount']}\n", FILE_APPEND);
        throw new Exception('Invalid or missing added amount for ' . $payment['amount_type']);
    }

    // Log payment ID for debugging
    $paymentId = $payment['id'];
    file_put_contents('debug.log', "Payment ID: $paymentId\n", FILE_APPEND);

    // Fetch room details
    $stmt = $pdo->prepare("
        SELECT room_number, room_type, hotel
        FROM room_payment_details
        WHERE payment_id = :payment_id
    ");
    $stmt->execute([':payment_id' => $paymentId]);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Log room details fetch
    file_put_contents('debug.log', "Fetched " . count($rooms) . " rooms for payment ID $paymentId\n", FILE_APPEND);

    // Combine data
    $response = array_merge($payment, ['rooms' => $rooms]);
    unset($response['id']); // Remove internal ID from response

    // Log the final response for debugging
    file_put_contents('debug.log', "Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(400);
    file_put_contents('debug.log', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>