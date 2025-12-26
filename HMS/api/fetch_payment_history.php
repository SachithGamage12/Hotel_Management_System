<?php
header('Content-Type: application/json');

try {
    $db = new PDO('mysql:host=localhost;dbname=hotelgrandguardi_wedding_bliss', 'hotelgrandguardi_root', 'Sun123flower@');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$bookingReference = $_GET['booking_reference'] ?? '';

if (empty($bookingReference)) {
    echo json_encode(['error' => 'Booking reference is required']);
    exit;
}

try {
    $stmt = $db->prepare("
        SELECT 
            invoice_number,
            booking_reference,
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
            issued_by,
            DATE_FORMAT(payment_date, '%Y-%m-%d %H:%i:%s') as payment_date
        FROM payments 
        WHERE booking_reference = ? 
        AND payment_date < NOW()
        ORDER BY payment_date DESC
    ");
    
    $stmt->execute([$bookingReference]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($payments) {
        echo json_encode($payments);
    } else {
        echo json_encode(['error' => 'No payments found for the provided booking reference']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
    exit;
}
?>