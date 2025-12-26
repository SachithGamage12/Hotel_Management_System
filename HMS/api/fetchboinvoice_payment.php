
<?php
header('Content-Type: application/json');

// Set time zone to IST (+0530)
date_default_timezone_set('Asia/Kolkata');

try {
    require_once 'db_connect.php';
    
    $invoiceNumber = $_GET['invoice_number'] ?? '';
    
    if (empty($invoiceNumber) || !preg_match('/^INV-\d{4}$/', $invoiceNumber)) {
        throw new Exception('Valid invoice number (e.g., INV-2080) is required');
    }

    $stmt = $pdo->prepare("
        SELECT 
            booking_reference,
            invoice_number,
            room_number,
            ac_type,
            meal_plan,
            remarks,
            value_type,
            amount_type,
            total_amount,
            advance_payment,
            pending_amount,
            issued_by,
            DATE_FORMAT(payment_date, '%Y/%m/%d') as payment_date
        FROM room_payments 
        WHERE invoice_number = ?
    ");
    
    $stmt->execute([$invoiceNumber]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payment) {
        throw new Exception('No payment found for invoice number: ' . $invoiceNumber);
    }
    
    echo json_encode($payment);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>
