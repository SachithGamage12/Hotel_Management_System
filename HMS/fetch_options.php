<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $field_type = $_GET['field_type'] ?? '';
    $valid_tables = [
        'venues', 'menus', 'function_types', 'music_types', 'wedding_cars',
        'jayamangala_gathas', 'wes_dances', 'ashtakas', 'welcome_songs',
        'indian_dhols', 'floor_dances'
    ];

    if (!in_array($field_type, $valid_tables)) {
        echo json_encode([]);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, name FROM $field_type ORDER BY name");
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($items);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn = null;
?>