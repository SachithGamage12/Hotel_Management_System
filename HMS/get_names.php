<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data']);
        exit;
    }

    $names = [
        'venue_name' => null,
        'menu_name' => null,
        'function_type_name' => null,
        'music_type_name' => null,
        'wedding_car_name' => null,
        'jayamangala_gatha_name' => null,
        'wes_dance_name' => null,
        'ashtaka_name' => null,
        'welcome_song_name' => null,
        'indian_dhol_name' => null,
        'floor_dance_name' => null
    ];

    // Fetch names for each ID if provided
    $queries = [
        'venue_id' => ['table' => 'venues', 'name' => 'venue_name'],
        'menu_id' => ['table' => 'menus', 'name' => 'menu_name'],
        'function_type_id' => ['table' => 'function_types', 'name' => 'function_type_name'],
        'music_type_id' => ['table' => 'music_types', 'name' => 'music_type_name'],
        'wedding_car_id' => ['table' => 'wedding_cars', 'name' => 'wedding_car_name'],
        'jayamangala_gatha_id' => ['table' => 'jayamangala_gathas', 'name' => 'jayamangala_gatha_name'],
        'wes_dance_id' => ['table' => 'wes_dances', 'name' => 'wes_dance_name'],
        'ashtaka_id' => ['table' => 'ashtakas', 'name' => 'ashtaka_name'],
        'welcome_song_id' => ['table' => 'welcome_songs', 'name' => 'welcome_song_name'],
        'indian_dhol_id' => ['table' => 'indian_dhols', 'name' => 'indian_dhol_name'],
        'floor_dance_id' => ['table' => 'floor_dances', 'name' => 'floor_dance_name']
    ];

    foreach ($queries as $id_field => $info) {
        if (!empty($input[$id_field])) {
            $stmt = $conn->prepare("SELECT name FROM {$info['table']} WHERE id = :id");
            $stmt->bindParam(':id', $input[$id_field], PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $names[$info['name']] = $result ? $result['name'] : null;
        }
    }

    echo json_encode(['success' => true, 'names' => $names]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn = null;
?>