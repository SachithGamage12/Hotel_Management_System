<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
$host = 'localhost';
$dbname = 'hotelgrandguardi_wedding_bliss';
$username = 'hotelgrandguardi_root';
$password = 'Sun123flower@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Get the GRC number from the query string
$grcNumber = isset($_GET['grc_number']) ? $_GET['grc_number'] : '';

if (empty($grcNumber)) {
    echo json_encode(['success' => false, 'message' => 'GRC number is required']);
    exit;
}

try {
    // Search for guests with the given GRC number
    $stmt = $pdo->prepare("SELECT * FROM guests WHERE grc_number = ?");
    $stmt->execute([$grcNumber]);
    $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($guests) {
        echo json_encode(['success' => true, 'data' => $guests]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No guest found with that GRC number']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>