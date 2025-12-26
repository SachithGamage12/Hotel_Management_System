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

    $invoiceNumber = $_GET['invoice_number'] ?? '';

    if (empty($invoiceNumber)) {
        throw new Exception('Invoice number is required');
    }

    // Fetch payment details with wedding_bookings_history first
    $stmt = $pdo->prepare("
        SELECT 
            rp.*,
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

    // If no payment found or no matching booking in wedding_bookings_history, try wedding_bookings
    if (!$payment || !$payment['full_name']) {
        $stmt = $pdo->prepare("
        SELECT 
            rp.*,
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
    }

    if (!$payment) {
        throw new Exception('Invoice not found');
    }

    // Fetch room details
    $stmt = $pdo->prepare("
        SELECT room_number, room_type, hotel
        FROM room_payment_details
        WHERE payment_id = :payment_id
    ");
    $stmt->execute([':payment_id' => $payment['id']]);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Combine data
    $response = array_merge($payment, ['rooms' => $rooms]);
    unset($response['id']); // Remove internal ID from response

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