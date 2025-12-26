<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection
$host = 'localhost';
$dbname = 'hotelgrandguardi_lakway_delivery';
$username = 'hotelgrandguardi_root';
$password = 'Sun123flower@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

if (!isset($_GET['store_id']) || empty(trim($_GET['store_id']))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Store ID is required']);
    exit;
}

$store_id = trim($_GET['store_id']);
$base_url = 'https://hotelgrandguardian.org/uploads/uber_foods/';

try {
    $stmt = $pdo->prepare("SELECT id, name, description, price, quantity, image 
                           FROM uber_food_items WHERE store_id = ? ORDER BY created_at DESC");
    $stmt->execute([$store_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Construct full image URLs
    foreach ($items as &$item) {
        $item['image_url'] = $item['image'] ? $base_url . $item['image'] : null;
    }

    echo json_encode([
        'success' => true,
        'items' => $items
    ]);
} catch (Exception $e) {
    error_log('Error fetching food items: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to fetch food items']);
}
?>