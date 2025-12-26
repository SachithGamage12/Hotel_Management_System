<?php
header('Content-Type: application/json');

// Database connection
try {
    $db = new PDO('mysql:host=localhost;dbname=wedding_bliss', 'root', 'Sun123flower@');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$reference = $_GET['reference'] ?? '';

// Validate booking reference (4 characters, alphanumeric)
if (empty($reference) || !preg_match('/^[A-Za-z0-9]{4}$/', $reference)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid booking reference. Must be a 4-character alphanumeric code.']);
    exit;
}

// Query for wedding_bookings_history first
$booking = null;
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
} catch (PDOException $e) {
    error_log('Error querying wedding_bookings_history: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error querying booking history']);
    exit;
}

// If not found in wedding_bookings_history, try wedding_bookings
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

    try {
        $stmt = $db->prepare($query);
        $stmt->execute([':reference' => $reference]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error querying wedding_bookings: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error querying booking details']);
        exit;
    }
}

if (!$booking) {
    http_response_code(404);
    echo json_encode(['error' => 'Booking not found in either wedding_bookings_history or wedding_bookings']);
    exit;
}

echo json_encode($booking);
?>