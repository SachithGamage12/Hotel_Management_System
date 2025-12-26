<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $field_type = $_POST['field_type'] ?? '';
    $new_value = trim($_POST['new_value'] ?? '');

    $valid_tables = [
        'venues', 'menus', 'function_types', 'music_types', 'wedding_cars',
        'jayamangala_gathas', 'wes_dances', 'ashtakas', 'welcome_songs',
        'indian_dhols', 'floor_dances'
    ];

    if (!in_array($field_type, $valid_tables) || empty($new_value)) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO $field_type (name) VALUES (:name)");
    $stmt->bindParam(':name', $new_value);
    $stmt->execute();

    $id = $conn->lastInsertId();
    echo json_encode(['success' => true, 'id' => $id, 'name' => $new_value]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn = null;
?>