<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";
try {
    // Establish database connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Decode booking data
    $raw_data = $_POST['booking_data'] ?? '{}';
    $booking_data = json_decode($raw_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg(), 3, "error.log");
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data provided']);
        exit;
    }
    if (empty($booking_data)) {
        echo json_encode(['success' => false, 'message' => 'No booking data provided']);
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
    // Begin transaction
    $conn->beginTransaction();
    // Generate a sequential 4-digit booking reference starting from 5000
    $start_number = 5000;
    $booking_reference = null;
    $stmt = $conn->query("SELECT booking_reference FROM wedding_bookings ORDER BY CAST(booking_reference AS UNSIGNED) DESC LIMIT 1");
    $last_reference = $stmt->fetchColumn();
    if ($last_reference === false || !is_numeric($last_reference) || (int)$last_reference < $start_number) {
        $booking_reference = str_pad($start_number, 4, '0', STR_PAD_LEFT);
    } else {
        $next_number = (int)$last_reference + 1;
        $booking_reference = str_pad($next_number, 4, '0', STR_PAD_LEFT);
    }
    // Check for uniqueness with a maximum of 10 attempts
    $attempts = 0;
    $max_attempts = 10;
    do {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM wedding_bookings WHERE booking_reference = :booking_reference");
        $stmt->bindValue(':booking_reference', $booking_reference, PDO::PARAM_STR);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        if ($count > 0) {
            $next_number = (int)$booking_reference + 1;
            $booking_reference = str_pad($next_number, 4, '0', STR_PAD_LEFT);
            $attempts++;
        }
    } while ($count > 0 && $attempts < $max_attempts);
    if ($attempts >= $max_attempts) {
        $conn->rollBack();
        error_log("Failed to generate unique booking reference after $max_attempts attempts", 3, "error.log");
        echo json_encode(['success' => false, 'message' => 'Unable to generate a unique booking reference. Please try again.']);
        exit;
    }
    // Insert booking data
    $sql = "INSERT INTO wedding_bookings (
        booking_reference, full_name, contact_no1, contact_no2, booking_date, time_from, time_from_am_pm, time_to, time_to_am_pm,
        couple_name, groom_address, bride_address, venue_id, menu_id, function_type_id, day_or_night, no_of_pax,
        floor_coordinator, drinks_coordinator, bride_dressing, groom_dressing, bride_arrival_time, bride_arrival_time_am_pm, bride_arrival_nakatha,
        groom_arrival_time, groom_arrival_time_am_pm, groom_arrival_nakatha, morning_tea_time_from, morning_tea_time_from_am_pm,
        morning_tea_time_to, morning_tea_time_to_am_pm, morning_tea_nakatha, tea_pax, kiribath, poruwa_time_from, poruwa_time_from_am_pm,
        poruwa_time_to, poruwa_time_to_am_pm, poruwa_direction, registration_time_from, registration_time_from_am_pm,
        registration_time_to, registration_time_to_am_pm, registration_direction, welcome_drink_time, welcome_drink_time_am_pm,
        floor_table_arrangement, drinks_time, drinks_time_am_pm, drinks_pax, drink_serving, bites_source, bite_items,
        buffet_open, buffet_open_am_pm, buffet_open_nakatha, buffet_close, buffet_close_am_pm, buffet_close_nakatha,
        buffet_type, ice_coffee_time, ice_coffee_time_am_pm, music_close_time, music_close_time_am_pm, departure_time,
        departure_time_am_pm, departure_time_nakatha, etc_description, music_type_id, wedding_car_id, jayamangala_gatha_id,
        wes_dance_id, ashtaka_id, welcome_song_id, indian_dhol_id, floor_dance_id, head_table, chair_cover, table_cloth,
        top_cloth, bow, napkin, vip, changing_room_date, changing_room_number, honeymoon_room_date, honeymoon_room_number,
        dressing_room_date, dressing_room_number, theme_color, flower_decoration_id, car_decoration_id, milk_fountain_id,
        champagne_id, cultural_table_id, kiribath_structure_id, cake_structure_id, projector_screen, gsky_arrival_time,
        gsky_arrival_time_am_pm, photo_team_count, bridal_team_count, additional_notes
    ) VALUES (
        :booking_reference, :full_name, :contact_no1, :contact_no2, :booking_date, :time_from, :time_from_am_pm, :time_to, :time_to_am_pm,
        :couple_name, :groom_address, :bride_address, :venue_id, :menu_id, :function_type_id, :day_or_night, :no_of_pax,
        :floor_coordinator, :drinks_coordinator, :bride_dressing, :groom_dressing, :bride_arrival_time, :bride_arrival_time_am_pm, :bride_arrival_nakatha,
        :groom_arrival_time, :groom_arrival_time_am_pm, :groom_arrival_nakatha, :morning_tea_time_from, :morning_tea_time_from_am_pm,
        :morning_tea_time_to, :morning_tea_time_to_am_pm, :morning_tea_nakatha, :tea_pax, :kiribath, :poruwa_time_from, :poruwa_time_from_am_pm,
        :poruwa_time_to, :poruwa_time_to_am_pm, :poruwa_direction, :registration_time_from, :registration_time_from_am_pm,
        :registration_time_to, :registration_time_to_am_pm, :registration_direction, :welcome_drink_time, :welcome_drink_time_am_pm,
        :floor_table_arrangement, :drinks_time, :drinks_time_am_pm, :drinks_pax, :drink_serving, :bites_source, :bite_items,
        :buffet_open, :buffet_open_am_pm, :buffet_open_nakatha, :buffet_close, :buffet_close_am_pm, :buffet_close_nakatha,
        :buffet_type, :ice_coffee_time, :ice_coffee_time_am_pm, :music_close_time, :music_close_time_am_pm, :departure_time,
        :departure_time_am_pm, :departure_time_nakatha, :etc_description, :music_type_id, :wedding_car_id, :jayamangala_gatha_id,
        :wes_dance_id, :ashtaka_id, :welcome_song_id, :indian_dhol_id, :floor_dance_id, :head_table, :chair_cover, :table_cloth,
        :top_cloth, :bow, :napkin, :vip, :changing_room_date, :changing_room_number, :honeymoon_room_date, :honeymoon_room_number,
        :dressing_room_date, :dressing_room_number, :theme_color, :flower_decoration_id, :car_decoration_id, :milk_fountain_id,
        :champagne_id, :cultural_table_id, :kiribath_structure_id, :cake_structure_id, :projector_screen, :gsky_arrival_time,
        :gsky_arrival_time_am_pm, :photo_team_count, :bridal_team_count, :additional_notes
    )";
    $stmt = $conn->prepare($sql);
    // List of TIME fields that should be NULL if empty
    $time_fields = [
        'time_from', 'time_to', 'bride_arrival_time', 'groom_arrival_time', 'morning_tea_time_from', 'morning_tea_time_to',
        'poruwa_time_from', 'poruwa_time_to', 'registration_time_from', 'registration_time_to', 'welcome_drink_time',
        'drinks_time', 'buffet_open', 'buffet_close', 'ice_coffee_time', 'music_close_time', 'departure_time', 'gsky_arrival_time'
    ];
    // List of fields that should be treated as integers
    $int_fields = [
        'venue_id', 'menu_id', 'function_type_id', 'music_type_id', 'wedding_car_id', 'jayamangala_gatha_id',
        'wes_dance_id', 'ashtaka_id', 'welcome_song_id', 'indian_dhol_id', 'floor_dance_id', 'no_of_pax', 'tea_pax',
        'drinks_pax', 'photo_team_count', 'bridal_team_count', 'flower_decoration_id', 'car_decoration_id',
        'milk_fountain_id', 'champagne_id', 'cultural_table_id', 'kiribath_structure_id', 'cake_structure_id'
    ];
    // List of date fields that should be NULL if empty
    $date_fields = ['booking_date', 'changing_room_date', 'honeymoon_room_date', 'dressing_room_date'];
    // List of all fields
    $fields = [
        'booking_reference', 'full_name', 'contact_no1', 'contact_no2', 'booking_date', 'time_from', 'time_from_am_pm',
        'time_to', 'time_to_am_pm', 'couple_name', 'groom_address', 'bride_address', 'venue_id', 'menu_id',
        'function_type_id', 'day_or_night', 'no_of_pax', 'floor_coordinator', 'drinks_coordinator', 'bride_dressing',
        'groom_dressing', 'bride_arrival_time', 'bride_arrival_time_am_pm', 'bride_arrival_nakatha',
        'groom_arrival_time', 'groom_arrival_time_am_pm', 'groom_arrival_nakatha', 'morning_tea_time_from',
        'morning_tea_time_from_am_pm', 'morning_tea_time_to', 'morning_tea_time_to_am_pm', 'morning_tea_nakatha',
        'tea_pax', 'kiribath', 'poruwa_time_from', 'poruwa_time_from_am_pm', 'poruwa_time_to', 'poruwa_time_to_am_pm',
        'poruwa_direction', 'registration_time_from', 'registration_time_from_am_pm', 'registration_time_to',
        'registration_time_to_am_pm', 'registration_direction', 'welcome_drink_time', 'welcome_drink_time_am_pm',
        'floor_table_arrangement', 'drinks_time', 'drinks_time_am_pm', 'drinks_pax', 'drink_serving', 'bites_source',
        'bite_items', 'buffet_open', 'buffet_open_am_pm', 'buffet_open_nakatha', 'buffet_close', 'buffet_close_am_pm',
        'buffet_close_nakatha', 'buffet_type', 'ice_coffee_time', 'ice_coffee_time_am_pm', 'music_close_time',
        'music_close_time_am_pm', 'departure_time', 'departure_time_am_pm', 'departure_time_nakatha', 'etc_description',
        'music_type_id', 'wedding_car_id', 'jayamangala_gatha_id', 'wes_dance_id', 'ashtaka_id', 'welcome_song_id',
        'indian_dhol_id', 'floor_dance_id', 'head_table', 'chair_cover', 'table_cloth', 'top_cloth', 'bow', 'napkin',
        'vip', 'changing_room_date', 'changing_room_number', 'honeymoon_room_date', 'honeymoon_room_number',
        'dressing_room_date', 'dressing_room_number', 'theme_color', 'flower_decoration_id', 'car_decoration_id',
        'milk_fountain_id', 'champagne_id', 'cultural_table_id', 'kiribath_structure_id', 'cake_structure_id',
        'projector_screen', 'gsky_arrival_time', 'gsky_arrival_time_am_pm', 'photo_team_count', 'bridal_team_count',
        'additional_notes'
    ];
    // Bind parameters
    foreach ($fields as $field) {
        $value = $field === 'booking_reference' ? $booking_reference : ($booking_data[$field] ?? null);
        // Sanitize string inputs
        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                $value = null;
            }
        }
        // Validate integer fields
        if (in_array($field, $int_fields)) {
            if ($value !== null && !is_numeric($value)) {
                $conn->rollBack();
                error_log("Invalid integer value for $field: $value", 3, "error.log");
                echo json_encode(['success' => false, 'message' => "Invalid value for $field: must be a number"]);
                exit;
            }
            $value = $value ? (int)$value : null;
        }
        // Handle date and time fields
        if (in_array($field, $date_fields) && empty($value)) {
            $value = null;
        }
        if (in_array($field, $time_fields) && empty($value)) {
            $value = null;
        }
        $stmt->bindValue(":$field", $value, is_int($value) ? PDO::PARAM_INT : ($value === null ? PDO::PARAM_NULL : PDO::PARAM_STR));
    }
    $stmt->execute();
    $booking_id = $conn->lastInsertId();
    // Commit transaction
    $conn->commit();
    echo json_encode(['success' => true, 'booking_id' => $booking_id, 'booking_reference' => $booking_reference]);
} catch (PDOException $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    error_log("Database error: " . $e->getMessage(), 3, "error.log");
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    error_log("General error: " . $e->getMessage(), 3, "error.log");
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
$conn = null;
?>