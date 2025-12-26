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

    // Initialize names array with default null values
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
        'floor_dance_name' => null,
        'flower_decoration_name' => null,
        'car_decoration_name' => null,
        'milk_fountain_name' => null,
        'champagne_name' => null,
        'cultural_table_name' => null,
        'kiribath_structure_name' => null,
        'cake_structure_name' => null
    ];

    // Define queries for fetching names
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
        'floor_dance_id' => ['table' => 'floor_dances', 'name' => 'floor_dance_name'],
        'flower_decoration_id' => ['table' => 'flower_decorations', 'name' => 'flower_decoration_name'],
        'car_decoration_id' => ['table' => 'car_decorations', 'name' => 'car_decoration_name'],
        'milk_fountain_id' => ['table' => 'milk_fountains', 'name' => 'milk_fountain_name'],
        'champagne_id' => ['table' => 'champagnes', 'name' => 'champagne_name'],
        'cultural_table_id' => ['table' => 'cultural_tables', 'name' => 'cultural_table_name'],
        'kiribath_structure_id' => ['table' => 'kiribath_structures', 'name' => 'kiribath_structure_name'],
        'cake_structure_id' => ['table' => 'cake_structures', 'name' => 'cake_structure_name']
    ];

    // Fetch names for each ID if provided
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
    error_log("Database error in get_names.php: " . $e->getMessage(), 3, "error.log");
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
$conn = null;
?>