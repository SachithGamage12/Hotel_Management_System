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
    require_once 'db_conn.php';

    // Get invoice number from query parameter
    $invoice_number = isset($_GET['invoice_number']) ? trim($_GET['invoice_number']) : '';
    
    if (empty($invoice_number) || !preg_match('/^INV-\d{4}$/', $invoice_number)) {
        throw new Exception('Invalid invoice number format. Expected INV-XXXX');
    }

    // Fetch payment details
    $stmt = $pdo->prepare("
        SELECT 
            rp.booking_reference,
            rp.invoice_number,
            rp.ac_type,
            rp.meal_plan,
            rp.remarks,
            rp.value_type,
            rp.amount_type,
            rp.total_amount,
            rp.advance_payment,
            rp.pending_amount,
            rp.issued_by,
            rp.nic,
            rp.contact_no,
            rp.payment_date
        FROM room_payments rp
        WHERE rp.invoice_number = ?
    ");
    $stmt->execute([$invoice_number]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        throw new Exception('No payment found for the given invoice number');
    }

    // Fetch room details
    $stmt = $pdo->prepare("
        SELECT 
            rpd.room_number,
            rpd.room_type,
            rpd.hotel
        FROM room_payment_details rpd
        WHERE rpd.payment_id = (
            SELECT id FROM room_payments WHERE invoice_number = ?
        )
    ");
    $stmt->execute([$invoice_number]);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rooms)) {
        throw new Exception('No room details found for the given invoice number');
    }

    // Combine payment and room details
    $response = array_merge($payment, ['rooms' => $rooms]);

    echo json_encode([
        'success' => true,
        'data' => $response
    ]);

} catch (Exception $e) {
    http_response_code(400);
    file_put_contents('debug.log', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>