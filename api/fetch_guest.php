<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $grc = $_GET['grc'] ?? '';
    if (!$grc || !is_numeric($grc)) {
        echo json_encode(['success' => false, 'message' => 'Invalid GRC']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM guests WHERE grc_number = :grc");
    $stmt->execute([':grc' => $grc]);
    $guest = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($guest) {
        echo json_encode(['success' => true, 'guest' => $guest]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Guest not found']);
    }
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>