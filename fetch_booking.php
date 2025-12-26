<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $booking_reference = isset($_GET['booking_reference']) ? trim($_GET['booking_reference']) : '';

    if (empty($booking_reference)) {
        echo json_encode(['success' => false, 'message' => 'Booking reference is required']);
        exit;
    }

    // Check the edited_bookings table first
    $stmt = $conn->prepare("SELECT * FROM edited_bookings WHERE booking_reference = :booking_reference");
    $stmt->bindValue(':booking_reference', $booking_reference, PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        // If not found in edited_bookings, check the history table (latest version)
        $stmt = $conn->prepare("SELECT * FROM wedding_bookings_history WHERE booking_reference = :booking_reference ORDER BY updated_at DESC LIMIT 1");
        $stmt->bindValue(':booking_reference', $booking_reference, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$data) {
        // If not found in history, check the original table
        $stmt = $conn->prepare("SELECT * FROM wedding_bookings WHERE booking_reference = :booking_reference");
        $stmt->bindValue(':booking_reference', $booking_reference, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($data) {
        // Clean time fields to replace '00:00:00' or null with empty string
        $timeFields = [
            'time_from', 'time_to', 'bride_arrival_time', 'groom_arrival_time', 'morning_tea_time_from',
            'morning_tea_time_to', 'poruwa_time_from', 'poruwa_time_to', 'registration_time_from',
            'registration_time_to', 'welcome_drink_time', 'drinks_time', 'buffet_open', 'buffet_close',
            'ice_coffee_time', 'music_close_time', 'departure_time', 'gsky_arrival_time'
        ];
        foreach ($timeFields as $field) {
            if (isset($data[$field]) && ($data[$field] === '00:00:00' || $data[$field] === null || $data[$field] === '')) {
                $data[$field] = '';
            }
        }
        // Clean AM/PM fields if corresponding time is empty
        $amPmFields = [
            'time_from_am_pm', 'time_to_am_pm', 'bride_arrival_time_am_pm', 'groom_arrival_time_am_pm',
            'morning_tea_time_from_am_pm', 'morning_tea_time_to_am_pm', 'poruwa_time_from_am_pm',
            'poruwa_time_to_am_pm', 'registration_time_from_am_pm', 'registration_time_to_am_pm',
            'welcome_drink_time_am_pm', 'drinks_time_am_pm', 'buffet_open_am_pm', 'buffet_close_am_pm',
            'ice_coffee_time_am_pm', 'music_close_time_am_pm', 'departure_time_am_pm', 'gsky_arrival_time_am_pm'
        ];
        foreach ($amPmFields as $field) {
            $timeField = str_replace('_am_pm', '', $field);
            if (isset($data[$timeField]) && $data[$timeField] === '' && isset($data[$field])) {
                $data[$field] = '';
            }
        }

        echo json_encode([
            'success' => true,
            'booking_reference' => $booking_reference,
            'data' => $data,
            'source' => isset($data['source']) ? $data['source'] : 'wedding_bookings' // Optional: indicate source
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn = null;
?>