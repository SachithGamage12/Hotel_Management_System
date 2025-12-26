<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $booking_data = json_decode($_POST['booking_data'] ?? '{}', true);

    if (empty($booking_data)) {
        echo json_encode(['success' => false, 'message' => 'No booking data provided']);
        exit;
    }

    // Validate booking_date
    if (empty($booking_data['booking_date']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $booking_data['booking_date'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid or missing booking date. Please select a valid date.']);
        exit;
    }

    // Generate a sequential 4-digit booking reference starting from 5000
    $booking_reference = null;
    $attempts = 0;
    $max_attempts = 10;
    $start_number = 5000;
    
    // Get the latest booking reference from the database
    $stmt = $conn->prepare("SELECT booking_reference FROM wedding_bookings ORDER BY booking_reference DESC LIMIT 1");
    $stmt->execute();
    $last_reference = $stmt->fetchColumn();
    
    // If no previous reference exists, start at 5000; otherwise, increment the last one
    if ($last_reference === false || !is_numeric($last_reference) || (int)$last_reference < $start_number) {
        $booking_reference = str_pad($start_number, 4, '0', STR_PAD_LEFT);
    } else {
        $next_number = (int)$last_reference + 1;
        $booking_reference = str_pad($next_number, 4, '0', STR_PAD_LEFT);
    }
    
    // Check for uniqueness and increment if necessary
    do {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM wedding_bookings WHERE booking_reference = :booking_reference");
        $stmt->bindValue(':booking_reference', $booking_reference, PDO::PARAM_STR);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        if ($count > 0) {
            $next_number = (int)$booking_reference + 1;
            $booking_reference = str_pad($next_number, 4, '0', STR_PAD_LEFT);
        }
        $attempts++;
    } while ($count > 0 && $attempts < $max_attempts);

    if ($attempts >= $max_attempts) {
        echo json_encode(['success' => false, 'message' => 'Unable to generate a unique booking reference. Please try again.']);
        exit;
    }

    $sql = "INSERT INTO wedding_bookings (
        booking_reference, full_name, contact_no1, contact_no2, booking_date, time_from, time_from_am_pm,
        time_to, time_to_am_pm, couple_name, groom_address, bride_address, venue_id,
        menu_id, function_type_id, day_or_night, no_of_pax, floor_coordinator,
        drinks_coordinator, bride_dressing, groom_dressing, bride_arrival_time,
        bride_arrival_time_am_pm, groom_arrival_time, groom_arrival_time_am_pm,
        morning_tea_time_from, morning_tea_time_from_am_pm, morning_tea_time_to,
        morning_tea_time_to_am_pm, tea_pax, kiribath, poruwa_time_from,
        poruwa_time_from_am_pm, poruwa_time_to, poruwa_time_to_am_pm,
        poruwa_direction, registration_time_from, registration_time_from_am_pm,
        registration_time_to, registration_time_to_am_pm, registration_direction,
        welcome_drink_time, welcome_drink_time_am_pm, floor_table_arrangement,
        drinks_time, drinks_time_am_pm, drinks_pax, drink_serving, bites_source,
        bite_items, buffet_open, buffet_open_am_pm, buffet_close, buffet_close_am_pm,
        buffet_type, ice_coffee_time, ice_coffee_time_am_pm, music_close_time,
        music_close_time_am_pm, departure_time, departure_time_am_pm, etc_description,
        music_type_id, wedding_car_id, jayamangala_gatha_id, wes_dance_id,
        ashtaka_id, welcome_song_id, indian_dhol_id, floor_dance_id, head_table,
        chair_cover, table_cloth, top_cloth, bow, napkin, vip, changing_room_date,
        changing_room_number, honeymoon_room_date, honeymoon_room_number,
        dressing_room_date, dressing_room_number, theme_color, flower_decor,
        car_decoration, milk_fountain, champaign, cultural_table, kiribath_structure,
        cake_structure, projector_screen, gsky_arrival_time, gsky_arrival_time_am_pm,
        photo_team_count, bridal_team_count
    ) VALUES (
        :booking_reference, :full_name, :contact_no1, :contact_no2, :booking_date, :time_from, :time_from_am_pm,
        :time_to, :time_to_am_pm, :couple_name, :groom_address, :bride_address, :venue_id,
        :menu_id, :function_type_id, :day_or_night, :no_of_pax, :floor_coordinator,
        :drinks_coordinator, :bride_dressing, :groom_dressing, :bride_arrival_time,
        :bride_arrival_time_am_pm, :groom_arrival_time, :groom_arrival_time_am_pm,
        :morning_tea_time_from, :morning_tea_time_from_am_pm, :morning_tea_time_to,
        :morning_tea_time_to_am_pm, :tea_pax, :kiribath, :poruwa_time_from,
        :poruwa_time_from_am_pm, :poruwa_time_to, :poruwa_time_to_am_pm,
        :poruwa_direction, :registration_time_from, :registration_time_from_am_pm,
        :registration_time_to, :registration_time_to_am_pm, :registration_direction,
        :welcome_drink_time, :welcome_drink_time_am_pm, :floor_table_arrangement,
        :drinks_time, :drinks_time_am_pm, :drinks_pax, :drink_serving, :bites_source,
        :bite_items, :buffet_open, :buffet_open_am_pm, :buffet_close, :buffet_close_am_pm,
        :buffet_type, :ice_coffee_time, :ice_coffee_time_am_pm, :music_close_time,
        :music_close_time_am_pm, :departure_time, :departure_time_am_pm, :etc_description,
        :music_type_id, :wedding_car_id, :jayamangala_gatha_id, :wes_dance_id,
        :ashtaka_id, :welcome_song_id, :indian_dhol_id, :floor_dance_id, :head_table,
        :chair_cover, :table_cloth, :top_cloth, :bow, :napkin, :vip, :changing_room_date,
        :changing_room_number, :honeymoon_room_date, :honeymoon_room_number,
        :dressing_room_date, :dressing_room_number, :theme_color, :flower_decor,
        :car_decoration, :milk_fountain, :champaign, :cultural_table, :kiribath_structure,
        :cake_structure, :projector_screen, :gsky_arrival_time, :gsky_arrival_time_am_pm,
        :photo_team_count, :bridal_team_count
    )";

    $stmt = $conn->prepare($sql);

    // Bind parameters
    $fields = [
        'booking_reference', 'full_name', 'contact_no1', 'contact_no2', 'booking_date', 'time_from', 'time_from_am_pm',
        'time_to', 'time_to_am_pm', 'couple_name', 'groom_address', 'bride_address', 'venue_id',
        'menu_id', 'function_type_id', 'day_or_night', 'no_of_pax', 'floor_coordinator',
        'drinks_coordinator', 'bride_dressing', 'groom_dressing', 'bride_arrival_time',
        'bride_arrival_time_am_pm', 'groom_arrival_time', 'groom_arrival_time_am_pm',
        'morning_tea_time_from', 'morning_tea_time_from_am_pm', 'morning_tea_time_to',
        'morning_tea_time_to_am_pm', 'tea_pax', 'kiribath', 'poruwa_time_from',
        'poruwa_time_from_am_pm', 'poruwa_time_to', 'poruwa_time_to_am_pm',
        'poruwa_direction', 'registration_time_from', 'registration_time_from_am_pm',
        'registration_time_to', 'registration_time_to_am_pm', 'registration_direction',
        'welcome_drink_time', 'welcome_drink_time_am_pm', 'floor_table_arrangement',
        'drinks_time', 'drinks_time_am_pm', 'drinks_pax', 'drink_serving', 'bites_source',
        'bite_items', 'buffet_open', 'buffet_open_am_pm', 'buffet_close', 'buffet_close_am_pm',
        'buffet_type', 'ice_coffee_time', 'ice_coffee_time_am_pm', 'music_close_time',
        'music_close_time_am_pm', 'departure_time', 'departure_time_am_pm', 'etc_description',
        'music_type_id', 'wedding_car_id', 'jayamangala_gatha_id', 'wes_dance_id',
        'ashtaka_id', 'welcome_song_id', 'indian_dhol_id', 'floor_dance_id', 'head_table',
        'chair_cover', 'table_cloth', 'top_cloth', 'bow', 'napkin', 'vip', 'changing_room_date',
        'changing_room_number', 'honeymoon_room_date', 'honeymoon_room_number',
        'dressing_room_date', 'dressing_room_number', 'theme_color', 'flower_decor',
        'car_decoration', 'milk_fountain', 'champaign', 'cultural_table', 'kiribath_structure',
        'cake_structure', 'projector_screen', 'gsky_arrival_time', 'gsky_arrival_time_am_pm',
        'photo_team_count', 'bridal_team_count'
    ];

    foreach ($fields as $field) {
        $value = $field === 'booking_reference' ? $booking_reference : ($booking_data[$field] ?? null);
        if (in_array($field, ['venue_id', 'menu_id', 'function_type_id', 'music_type_id', 'wedding_car_id',
                              'jayamangala_gatha_id', 'wes_dance_id', 'ashtaka_id', 'welcome_song_id',
                              'indian_dhol_id', 'floor_dance_id', 'no_of_pax', 'tea_pax', 'drinks_pax',
                              'photo_team_count', 'bridal_team_count'])) {
            $value = $value ? (int)$value : null;
        }
        // Handle optional date fields
        if (in_array($field, ['changing_room_date', 'honeymoon_room_date', 'dressing_room_date']) && empty($value)) {
            $value = null;
        }
        $stmt->bindValue(":$field", $value, is_int($value) ? PDO::PARAM_INT : ($value === null ? PDO::PARAM_NULL : PDO::PARAM_STR));
    }

    $stmt->execute();
    $booking_id = $conn->lastInsertId();

    echo json_encode(['success' => true, 'booking_id' => $booking_id, 'booking_reference' => $booking_reference]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn = null;
?>