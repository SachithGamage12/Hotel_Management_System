<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

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

        // Simplified query to get booking counts per date
        $stmt = $pdo->prepare('
            SELECT booking_date, COUNT(*) as booking_count
            FROM (
                -- Get latest version from history table
                SELECT h.booking_date, h.booking_reference
                FROM wedding_bookings_history h
                INNER JOIN (
                    SELECT booking_reference, MAX(updated_at) as max_updated
                    FROM wedding_bookings_history
                    WHERE booking_date BETWEEN :start AND :end
                    GROUP BY booking_reference
                ) latest ON h.booking_reference = latest.booking_reference AND h.updated_at = latest.max_updated
                WHERE h.booking_date BETWEEN :start AND :end
                
                UNION
                
                -- Get from main table where no history exists
                SELECT booking_date, booking_reference
                FROM wedding_bookings 
                WHERE booking_date BETWEEN :start AND :end
                AND booking_reference NOT IN (
                    SELECT DISTINCT booking_reference FROM wedding_bookings_history
                )
            ) AS all_bookings
            GROUP BY booking_date
            ORDER BY booking_date
        ');
        
        $stmt->execute(['start' => $start, 'end' => $end]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $events = [];
        foreach ($bookings as $booking) {
            $title = $booking['booking_count'] . ' Booking' . ($booking['booking_count'] > 1 ? 's' : '');
            
            $events[] = [
                'title' => $title,
                'start' => $booking['booking_date'],
                'allDay' => true,
                'booking_count' => (int)$booking['booking_count'],
                // Add display properties for calendar
                'display' => 'background',
                'color' => $booking['booking_count'] >= 5 ? '#ff4444' : 
                          ($booking['booking_count'] >= 3 ? '#ffaa00' : '#4CAF50'),
                'textColor' => '#ffffff'
            ];
        }

        echo json_encode($events);

    } elseif ($action === 'get_booking_details') {
        $date = $_GET['date'] ?? '';

        if (empty($date)) {
            echo json_encode(['error' => 'Missing date parameter']);
            exit;
        }

        // Improved query to get booking details with all required fields
        $stmt = $pdo->prepare('
            SELECT 
                COALESCE(h.booking_reference, wb.booking_reference) as booking_reference,
                COALESCE(h.couple_name, wb.couple_name) as couple_name,
                COALESCE(h.no_of_pax, wb.no_of_pax) as no_of_pax,
                COALESCE(h.time_from, wb.time_from) as time_from,
                COALESCE(h.time_from_am_pm, wb.time_from_am_pm) as time_from_am_pm,
                COALESCE(h.time_to, wb.time_to) as time_to,
                COALESCE(h.time_to_am_pm, wb.time_to_am_pm) as time_to_am_pm,
                v.name AS venue_name,
                COALESCE(h.full_name, wb.full_name) as full_name,
                COALESCE(h.contact_no1, wb.contact_no1) as contact_no1,
                COALESCE(h.contact_no2, wb.contact_no2) as contact_no2,
                COALESCE(h.groom_address, wb.groom_address) as groom_address,
                COALESCE(h.bride_address, wb.bride_address) as bride_address,
                COALESCE(h.day_or_night, wb.day_or_night) as day_or_night,
                CASE 
                    WHEN h.booking_reference IS NOT NULL THEN "history"
                    ELSE "current"
                END as source
            FROM wedding_bookings wb
            LEFT JOIN venues v ON wb.venue_id = v.id
            LEFT JOIN (
                SELECT h1.*
                FROM wedding_bookings_history h1
                INNER JOIN (
                    SELECT booking_reference, MAX(updated_at) as max_updated
                    FROM wedding_bookings_history
                    WHERE booking_date = :date
                    GROUP BY booking_reference
                ) h2 ON h1.booking_reference = h2.booking_reference AND h1.updated_at = h2.max_updated
                WHERE h1.booking_date = :date
            ) h ON wb.booking_reference = h.booking_reference
            WHERE wb.booking_date = :date
            
            UNION
            
            SELECT 
                h.booking_reference,
                h.couple_name,
                h.no_of_pax,
                h.time_from,
                h.time_from_am_pm,
                h.time_to,
                h.time_to_am_pm,
                v.name AS venue_name,
                h.full_name,
                h.contact_no1,
                h.contact_no2,
                h.groom_address,
                h.bride_address,
                h.day_or_night,
                "history_only" as source
            FROM wedding_bookings_history h
            LEFT JOIN venues v ON h.venue_id = v.id
            WHERE h.booking_date = :date
            AND h.booking_reference NOT IN (
                SELECT booking_reference FROM wedding_bookings WHERE booking_date = :date
            )
            AND (h.booking_reference, h.updated_at) IN (
                SELECT booking_reference, MAX(updated_at)
                FROM wedding_bookings_history
                WHERE booking_date = :date
                GROUP BY booking_reference
            )
            ORDER BY time_from, couple_name
        ');
        
        $stmt->execute(['date' => $date]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($bookings) {
            $response = array_map(function($booking) {
                $timeFrom = !empty($booking['time_from']) ? 
                    $booking['time_from'] . ' ' . ($booking['time_from_am_pm'] ?? '') : 'N/A';
                $timeTo = !empty($booking['time_to']) ? 
                    $booking['time_to'] . ' ' . ($booking['time_to_am_pm'] ?? '') : 'N/A';
                    
                return [
                    'booking_reference' => $booking['booking_reference'] ?? 'N/A',
                    'couple_name' => $booking['couple_name'] ?? 'Unknown Couple',
                    'no_of_pax' => $booking['no_of_pax'] ?? 0,
                    'venue_name' => $booking['venue_name'] ?? 'Unknown Venue',
                    'time' => $timeFrom . ' - ' . $timeTo,
                    'full_name' => $booking['full_name'] ?? 'N/A',
                    'contact_no1' => $booking['contact_no1'] ?? 'N/A',
                    'contact_no2' => $booking['contact_no2'] ?? 'N/A',
                    'groom_address' => $booking['groom_address'] ?? 'N/A',
                    'bride_address' => $booking['bride_address'] ?? 'N/A',
                    'day_or_night' => $booking['day_or_night'] ?? 'N/A',
                    'source' => $booking['source'] ?? 'unknown'
                ];
            }, $bookings);
            echo json_encode($response);
        } else {
            echo json_encode([]);
        }

    } elseif ($action === 'delete_booking' && $_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        $booking_reference = $input['booking_reference'] ?? $_GET['booking_reference'] ?? '';

        if (empty($booking_reference)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing booking reference']);
            exit;
        }

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
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    error_log("General error: " . $e->getMessage());
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>