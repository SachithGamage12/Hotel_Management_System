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

    // Validate booking data and reference
    if (empty($booking_data) || empty($booking_data['booking_reference'])) {
        echo json_encode(['success' => false, 'message' => 'No booking data or booking reference provided']);
        exit;
    }

    // Validate booking_date format
    if (empty($booking_data['booking_date']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $booking_data['booking_date'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid or missing booking date']);
        exit;
    }

    $booking_reference = $booking_data['booking_reference'];

    // SQL to insert into history table
    $sql = "INSERT INTO wedding_bookings_history (
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
        photo_team_count, bridal_team_count, updated_at
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
        :photo_team_count, :bridal_team_count, NOW()
    )";

    $stmt = $conn->prepare($sql);

    // List of all fields to bind
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

    // Bind parameters
    foreach ($fields as $field) {
        $value = $booking_data[$field] ?? null;
        // Handle integer fields
        if (in_array($field, ['venue_id', 'menu_id', 'function_type_id', 'music_type_id', 'wedding_car_id',
                              'jayamangala_gatha_id', 'wes_dance_id', 'ashtaka_id', 'welcome_song_id',
                              'indian_dhol_id', 'floor_dance_id', 'no_of_pax', 'tea_pax', 'drinks_pax',
                              'photo_team_count', 'bridal_team_count'])) {
            $value = $value ? (int)$value : null;
        }
        // Handle date fields
        if (in_array($field, ['changing_room_date', 'honeymoon_room_date', 'dressing_room_date']) && empty($value)) {
            $value = null;
        }
        $stmt->bindValue(":$field", $value, is_int($value) ? PDO::PARAM_INT : ($value === null ? PDO::PARAM_NULL : PDO::PARAM_STR));
    }

    // Execute history insert
    $stmt->execute();
    $history_id = $conn->lastInsertId();

    // Return success response
    echo json_encode(['success' => true, 'message' => 'Booking history saved successfully', 'history_id' => $history_id]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn = null;
?>