<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get booking data from POST request
    $booking_data = json_decode($_POST['booking_data'] ?? '{}', true);
    if (json_last_error() !== JSON_ERROR_NONE || empty($booking_data) || empty($booking_data['booking_reference'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid or missing booking data']);
        exit;
    }

    // Validate required fields
    $required_fields = ['full_name', 'contact_no1', 'couple_name', 'groom_address', 'bride_address'];
    $missing_fields = [];
    foreach ($required_fields as $field) {
        if (!isset($booking_data[$field]) || empty(trim($booking_data[$field]))) {
            $missing_fields[] = $field;
        }
    }
    if (!empty($missing_fields)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing_fields)]);
        exit;
    }

    $booking_reference = $booking_data['booking_reference'];
    $conn->beginTransaction();

    // Fields to update or insert
    $fields = [
        'booking_reference', 'full_name', 'contact_no1', 'contact_no2', 'booking_date', 'time_from', 'time_from_am_pm',
        'time_to', 'time_to_am_pm', 'couple_name', 'groom_address', 'bride_address', 'venue_id',
        'menu_id', 'function_type_id', 'day_or_night', 'no_of_pax', 'floor_coordinator',
        'drinks_coordinator', 'bride_dressing', 'groom_dressing', 'bride_arrival_time',
        'bride_arrival_time_am_pm', 'bride_arrival_nakatha', 'groom_arrival_time',
        'groom_arrival_time_am_pm', 'groom_arrival_nakatha', 'morning_tea_time_from',
        'morning_tea_time_from_am_pm', 'morning_tea_time_to', 'morning_tea_time_to_am_pm',
        'morning_tea_nakatha', 'tea_pax', 'kiribath', 'poruwa_time_from', 'poruwa_time_from_am_pm',
        'poruwa_time_to', 'poruwa_time_to_am_pm', 'poruwa_direction', 'registration_time_from',
        'registration_time_from_am_pm', 'registration_time_to', 'registration_time_to_am_pm',
        'registration_direction', 'welcome_drink_time', 'welcome_drink_time_am_pm',
        'floor_table_arrangement', 'drinks_time', 'drinks_time_am_pm', 'drinks_pax',
        'drink_serving', 'bites_source', 'bite_items', 'buffet_open', 'buffet_open_am_pm',
        'buffet_open_nakatha', 'buffet_close', 'buffet_close_am_pm', 'buffet_close_nakatha',
        'buffet_type', 'ice_coffee_time', 'ice_coffee_time_am_pm', 'music_close_time',
        'music_close_time_am_pm', 'departure_time', 'departure_time_am_pm', 'departure_time_nakatha',
        'etc_description', 'music_type_id', 'wedding_car_id', 'jayamangala_gatha_id',
        'wes_dance_id', 'ashtaka_id', 'welcome_song_id', 'indian_dhol_id', 'floor_dance_id',
        'head_table', 'chair_cover', 'table_cloth', 'top_cloth', 'bow', 'napkin', 'vip',
        'changing_room_date', 'changing_room_number', 'honeymoon_room_date', 'honeymoon_room_number',
        'dressing_room_date', 'dressing_room_number', 'theme_color', 'flower_decoration_id',
        'car_decoration_id', 'milk_fountain_id', 'champagne_id', 'cultural_table_id',
        'kiribath_structure_id', 'cake_structure_id', 'additional_notes', 'projector_screen',
        'gsky_arrival_time', 'gsky_arrival_time_am_pm', 'photo_team_count', 'bridal_team_count'
    ];

    // Time fields that should be NULL if empty
    $time_fields = [
        'time_from', 'time_to', 'bride_arrival_time', 'groom_arrival_time',
        'morning_tea_time_from', 'morning_tea_time_to', 'poruwa_time_from',
        'poruwa_time_to', 'registration_time_from', 'registration_time_to',
        'welcome_drink_time', 'drinks_time', 'buffet_open', 'buffet_close',
        'ice_coffee_time', 'music_close_time', 'departure_time', 'gsky_arrival_time'
    ];

    // Integer fields
    $integer_fields = [
        'venue_id', 'menu_id', 'function_type_id', 'music_type_id', 'wedding_car_id',
        'jayamangala_gatha_id', 'wes_dance_id', 'ashtaka_id', 'welcome_song_id',
        'indian_dhol_id', 'floor_dance_id', 'no_of_pax', 'tea_pax', 'drinks_pax',
        'photo_team_count', 'bridal_team_count', 'flower_decoration_id', 'car_decoration_id',
        'milk_fountain_id', 'champagne_id', 'cultural_table_id', 'kiribath_structure_id',
        'cake_structure_id'
    ];

    // Date fields
    $date_fields = [
        'booking_date', 'changing_room_date', 'honeymoon_room_date', 'dressing_room_date'
    ];

    // Prepare data for binding
    $bind_data = [];
    foreach ($fields as $field) {
        $value = $booking_data[$field] ?? null;
        if (in_array($field, $integer_fields)) {
            $value = $value !== null && $value !== '' ? (int)$value : null;
        } elseif (in_array($field, $time_fields) && empty($value)) {
            $value = null;
        } elseif (in_array($field, $date_fields) && empty($value)) {
            $value = null;
        }
        $bind_data[":$field"] = $value;
    }

    // Update wedding_bookings
    $update_sql = "UPDATE wedding_bookings SET " . implode(', ', array_map(fn($f) => "$f = :$f", $fields)) . " WHERE booking_reference = :booking_reference";
    $update_stmt = $conn->prepare($update_sql);
    foreach ($bind_data as $key => $value) {
        $param_type = is_int($value) ? PDO::PARAM_INT : (is_null($value) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $update_stmt->bindValue($key, $value, $param_type);
    }
    $update_stmt->execute();

    // Insert into wedding_bookings_history
    $history_fields = array_merge($fields, ['updated_at']);
    $history_sql = "INSERT INTO wedding_bookings_history (" . implode(', ', $history_fields) . ") VALUES (" . implode(', ', array_map(fn($f) => ":$f", $history_fields)) . ")";
    $history_stmt = $conn->prepare($history_sql);
    
    // Bind history-specific values
    $history_bind_data = array_merge($bind_data, [':updated_at' => date('Y-m-d H:i:s')]);
    foreach ($history_bind_data as $key => $value) {
        $param_type = is_int($value) ? PDO::PARAM_INT : (is_null($value) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $history_stmt->bindValue($key, $value, $param_type);
    }
    $history_stmt->execute();

    // Get the auto-incremented id
    $history_id = $conn->lastInsertId();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Booking updated successfully', 'history_id' => $history_id]);
} catch (PDOException $e) {
    $conn->rollBack();
    error_log("Update booking error: " . $e->getMessage(), 3, "error.log");
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    $conn->rollBack();
    error_log("General error: " . $e->getMessage(), 3, "error.log");
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    $conn = null;
}
?>