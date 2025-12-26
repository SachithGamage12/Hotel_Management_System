<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_GET['grc_number']) || empty($_GET['grc_number'])) {
        echo json_encode(['success' => false, 'error' => 'GRC number is required']);
        exit;
    }

    $grc_number = $_GET['grc_number'];

    // Fetch guest details
    $stmt = $conn->prepare("
        SELECT g.*, mp.name AS meal_plan_name
        FROM lodgeguests g
        LEFT JOIN meal_plans mp ON g.meal_plan_id = mp.id
        WHERE g.grc_number = :grc_number
    ");
    $stmt->bindParam(':grc_number', $grc_number, PDO::PARAM_INT);
    $stmt->execute();
    $guest = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$guest) {
        echo json_encode(['success' => false, 'error' => 'No guest found with the provided GRC number']);
        exit;
    }

    // Fetch room type names
    $stmt = $conn->query("SELECT id, name FROM room_types");
    $room_types_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $room_types = [];
    foreach ($room_types_result as $row) {
        $room_types[$row['id']] = $row['name'];
    }

    // Parse rooms JSON
    $rooms = json_decode($guest['rooms'], true);
    $formatted_rooms = [];
    foreach ($rooms as $room) {
        $formatted_rooms[] = [
            'number' => $room['room_number'],
            'type' => isset($room_types[$room['room_type']]) ? $room_types[$room['room_type']] : 'Unknown',
            'ac_type' => $room['ac_type'],
            'rate' => $room['room_rate']
        ];
    }

    // Format check-in and check-out times
    $check_in_time = sprintf(
        '%s %s',
        $guest['check_in_time'],
        $guest['check_in_time_am_pm']
    );
    $check_out_time = sprintf(
        '%s %s',
        $guest['check_out_time'],
        $guest['check_out_time_am_pm']
    );

    // Calculate total amount
    $total_amount = array_sum(array_column($rooms, 'room_rate'));

    echo json_encode([
        'success' => true,
        'guest' => [
            'grc_number' => $guest['grc_number'],
            'guest_name' => $guest['guest_name'],
            'contact_number' => $guest['contact_number'],
            'email' => $guest['email'],
            'address' => $guest['address'],
            'id_type' => $guest['id_type'],
            'id_number' => $guest['id_number'],
            'check_in_date' => $guest['check_in_date'],
            'check_in_time' => $check_in_time,
            'check_out_date' => $guest['check_out_date'],
            'check_out_time' => $check_out_time,
            'rooms' => $formatted_rooms,
            'meal_plan' => $guest['meal_plan_name'],
            'number_of_pax' => $guest['number_of_pax'],
            'remarks' => $guest['remarks'],
            'total_amount' => $total_amount
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>