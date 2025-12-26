<?php
header('Content-Type: application/json');

try {
    require_once 'db_connect.php';
    
    $bookingReference = $_GET['booking_reference'] ?? '';
    
    if (empty($bookingReference)) {
        throw new Exception('Booking reference is required');
    }

    $stmt = $pdo->prepare("
        SELECT 
            invoice_number,
            value_type,
            payment_type,
            payment_amount,
            pending_amount,
            DATE_FORMAT(payment_date, '%Y-%m-%d %H:%i:%s') as payment_date
        FROM payments 
        WHERE booking_reference = ? 
        AND payment_date < NOW()
        ORDER BY payment_date DESC
    ");
    
    $stmt->execute([$bookingReference]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($payments);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>