<?php
header('Content-Type: application/json');

try {
    $db = new PDO('mysql:host=localhost;dbname=hotelgrandguardi_wedding_bliss', 'hotelgrandguardi_root', 'Sun123flower@');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$invoiceNumber = $_GET['invoice_number'] ?? '';

if (empty($invoiceNumber)) {
    echo json_encode(['error' => 'Invoice number is required']);
    exit;
}

// Normalize invoice number to include INV- prefix if not provided
if (!preg_match('/^INV-/', $invoiceNumber)) {
    $invoiceNumber = 'INV-' . sprintf('%04d', (int)$invoiceNumber);
}

try {
    $stmt = $db->prepare("
        SELECT 
            p.booking_reference,
            p.invoice_number,
            p.contact_no,
            p.whatsapp_no,
            p.email,
            p.rate_per_plate,
            p.additional_plate_rate,
            p.remarks,
            p.value_type,
            p.total_amount,
            p.payment_type,
            p.payment_amount,
            p.pending_amount,
            p.no_of_pax,
            p.issued_by,
            DATE_FORMAT(p.payment_date, '%Y-%m-%d %H:%i:%s') as payment_date
        FROM payments p
        WHERE p.invoice_number = :invoice_number
    ");
    $stmt->execute([':invoice_number' => $invoiceNumber]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        echo json_encode(['error' => 'Payment not found']);
        exit;
    }

    echo json_encode($payment);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
    exit;
}
?>