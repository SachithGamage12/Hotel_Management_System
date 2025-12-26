<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust for production
header('Access-Control-Allow-Methods: GET, DELETE'); // Allow DELETE method

try {
    // Database connection
    $pdo = new PDO('mysql:host=localhost;dbname=hotelgrandguardi_wedding_bliss;charset=utf8mb4', 'hotelgrandguardi_root', 'Sun123flower@');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get the action from query parameter
    $action = $_GET['action'] ?? '';

    if ($action === 'get_bookings') {
        $start = $_GET['start'] ?? '';
        $end = $_GET['end'] ?? '';

        if (empty($start) || empty($end)) {
            echo json_encode(['error' => 'Missing start or end date']);
            exit;
        }

        // Fetch bookings, preferring latest wedding_bookings_history for duplicates
        $stmt = $pdo->prepare('
            SELECT booking_date, COUNT(*) as booking_count, GROUP_CONCAT(couple_name) as couple_names
            FROM (
                SELECT booking_date, couple_name
                FROM (
                    SELECT booking_date, couple_name, booking_reference,
                           ROW_NUMBER() OVER (PARTITION BY booking_reference ORDER BY updated_at DESC) as rn
                    FROM wedding_bookings_history
                    WHERE booking_date BETWEEN :start AND :end
                ) eb
                WHERE rn = 1
                UNION
                SELECT wb.booking_date, wb.couple_name
                FROM wedding_bookings wb
                WHERE wb.booking_date BETWEEN :start AND :end
                AND wb.booking_reference NOT IN (
                    SELECT booking_reference FROM wedding_bookings_history
                )
            ) AS combined_bookings
            GROUP BY booking_date
        ');
        $stmt->execute(['start' => $start, 'end' => $end]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($bookings)) {
            error_log("No bookings found for range $start to $end");
        }

        $events = array_map(function($booking) {
            $title = $booking['booking_count'] > 1 
                ? $booking['booking_count'] . ' Bookings'
                : ($booking['couple_names'] ?? 'Unknown Couple');
            return [
                'title' => $title,
                'start' => $booking['booking_date'],
                'allDay' => true,
                'booking_count' => $booking['booking_count']
            ];
        }, $bookings);

        echo json_encode($events);
    } elseif ($action === 'get_booking_details') {
        $date = $_GET['date'] ?? '';

        if (empty($date)) {
            echo json_encode(['error' => 'Missing date parameter']);
            exit;
        }

        // Fetch latest bookings for the date, preferring wedding_bookings_history
        $stmt = $pdo->prepare('
            SELECT 
                booking_reference,
                couple_name,
                no_of_pax,
                time_from,
                time_from_am_pm,
                time_to,
                time_to_am_pm,
                venue_name,
                full_name,
                contact_no1,
                contact_no2,
                groom_address,
                bride_address
            FROM (
                SELECT 
                    wb.booking_reference,
                    wb.couple_name,
                    wb.no_of_pax,
                    wb.time_from,
                    wb.time_from_am_pm,
                    wb.time_to,
                    wb.time_to_am_pm,
                    v.name AS venue_name,
                    NULL AS full_name,
                    NULL AS contact_no1,
                    NULL AS contact_no2,
                    NULL AS groom_address,
                    NULL AS bride_address,
                    wb.booking_date
                FROM wedding_bookings wb
                LEFT JOIN venues v ON wb.venue_id = v.id
                WHERE wb.booking_date = :date
                AND wb.booking_reference NOT IN (
                    SELECT booking_reference FROM wedding_bookings_history
                )
                UNION
                SELECT 
                    eb.booking_reference,
                    eb.couple_name,
                    eb.no_of_pax,
                    eb.time_from,
                    eb.time_from_am_pm,
                    eb.time_to,
                    eb.time_to_am_pm,
                    v.name AS venue_name,
                    eb.full_name,
                    eb.contact_no1,
                    eb.contact_no2,
                    eb.groom_address,
                    eb.bride_address,
                    eb.booking_date
                FROM (
                    SELECT *,
                           ROW_NUMBER() OVER (PARTITION BY booking_reference ORDER BY updated_at DESC) as rn
                    FROM wedding_bookings_history
                ) eb
                LEFT JOIN venues v ON eb.venue_id = v.id
                WHERE eb.booking_date = :date AND eb.rn = 1
            ) AS combined_bookings
        ');
        $stmt->execute(['date' => $date]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($bookings) {
            $response = array_map(function($booking) {
                return [
                    'booking_reference' => $booking['booking_reference'] ?? 'N/A',
                    'couple_name' => $booking['couple_name'] ?? 'Unknown',
                    'no_of_pax' => $booking['no_of_pax'] ?? 0,
                    'venue_name' => $booking['venue_name'] ?? 'Unknown',
                    'time' => sprintf(
                        '%s %s - %s %s',
                        $booking['time_from'] ?? 'N/A',
                        $booking['time_from_am_pm'] ?? '',
                        $booking['time_to'] ?? 'N/A',
                        $booking['time_to_am_pm'] ?? ''
                    ),
                    'full_name' => $booking['full_name'] ?? null,
                    'contact_no1' => $booking['contact_no1'] ?? null,
                    'contact_no2' => $booking['contact_no2'] ?? null,
                    'groom_address' => $booking['groom_address'] ?? null,
                    'bride_address' => $booking['bride_address'] ?? null
                ];
            }, $bookings);
            echo json_encode($response);
        } else {
            error_log("No bookings found for date $date");
            echo json_encode([]);
        }
    } elseif ($action === 'delete_booking' && $_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $booking_reference = $_GET['booking_reference'] ?? '';

        if (empty($booking_reference)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing booking reference']);
            exit;
        }

        // Delete from both wedding_bookings and wedding_bookings_history
        $pdo->beginTransaction();
        try {
            // Delete from wedding_bookings
            $stmt1 = $pdo->prepare('DELETE FROM wedding_bookings WHERE booking_reference = :booking_reference');
            $stmt1->execute(['booking_reference' => $booking_reference]);

            // Delete from wedding_bookings_history
            $stmt2 = $pdo->prepare('DELETE FROM wedding_bookings_history WHERE booking_reference = :booking_reference');
            $stmt2->execute(['booking_reference' => $booking_reference]);

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Booking deleted successfully']);
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Delete error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to delete booking: ' . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action or method']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error']);
}
?>