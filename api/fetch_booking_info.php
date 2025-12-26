<?php
header('Content-Type: application/json');

// Database connection
try {
    $db = new PDO('mysql:host=localhost;dbname=hotelgrandguardi_wedding_bliss', 'hotelgrandguardi_root', 'Sun123flower@');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$reference = $_GET['reference'] ?? '';

if (empty($reference)) {
    echo json_encode(['error' => 'Booking reference is required']);
    exit;
}

// Query wedding_bookings_history table first
$query = "
    SELECT 
        wbh.full_name, 
        wbh.couple_name,
        wbh.contact_no1, 
        wbh.contact_no2, 
        wbh.booking_date, 
        ft.name AS function_type, 
        v.name AS venue, 
        wbh.no_of_pax, 
        wbh.groom_address, 
        wbh.bride_address,
        wbh.booking_reference
    FROM wedding_bookings_history wbh
    LEFT JOIN function_types ft ON wbh.function_type_id = ft.id
    LEFT JOIN venues v ON wbh.venue_id = v.id
    WHERE wbh.booking_reference = :reference
    ORDER BY wbh.updated_at DESC
    LIMIT 1
";

try {
    $stmt = $db->prepare($query);
    $stmt->execute([':reference' => $reference]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no booking found in wedding_bookings_history, try wedding_bookings
    if (!$booking) {
        $query = "
            SELECT 
                wb.full_name, 
                wb.couple_name,
                wb.contact_no1, 
                wb.contact_no2, 
                wb.booking_date, 
                ft.name AS function_type, 
                v.name AS venue, 
                wb.no_of_pax, 
                wb.groom_address, 
                wb.bride_address,
                wb.booking_reference
            FROM wedding_bookings wb
            LEFT JOIN function_types ft ON wb.function_type_id = ft.id
            LEFT JOIN venues v ON wb.venue_id = v.id
            WHERE wb.booking_reference = :reference
        ";
        
        $stmt = $db->prepare($query);
        $stmt->execute([':reference' => $reference]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$booking) {
        echo json_encode(['error' => 'Booking not found']);
        exit;
    }

    echo json_encode($booking);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
    exit;
}
?>