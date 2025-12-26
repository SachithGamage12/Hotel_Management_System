<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
    exit;
}

// Validate required fields
$required_fields = [
    'store_id', 'customer_name', 'customer_phone', 'delivery_address',
    'customer_latitude', 'customer_longitude', 'delivery_type', 'payment_method',
    'items', 'subtotal', 'delivery_charge', 'total', 'status'
];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || (is_string($input[$field]) && empty(trim($input[$field])))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => ucfirst($field) . ' is required']);
        exit;
    }
}

// Validate items
if (!is_array($input['items']) || empty($input['items'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Items are required']);
    exit;
}

// Validate coordinates
$latitude = $input['customer_latitude'];
$longitude = $input['customer_longitude'];
$minLat = 6.6;
$maxLat = 6.85;
$minLng = 80.3;
$maxLng = 80.5;
if (!is_numeric($latitude) || !is_numeric($longitude) || 
    $latitude < $minLat || $latitude > $maxLat || 
    $longitude < $minLng || $longitude > $maxLng) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Customer location must be within Ratnapura-Kuruwita area']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Insert order
    $sql = "INSERT INTO orders (
        store_id, customer_name, customer_phone, delivery_address, 
        customer_latitude, customer_longitude, delivery_type, payment_method,
        subtotal, delivery_charge, total, status, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $input['store_id'],
        $input['customer_name'],
        $input['customer_phone'],
        $input['delivery_address'],
        $input['customer_latitude'],
        $input['customer_longitude'],
        $input['delivery_type'],
        $input['payment_method'],
        $input['subtotal'],
        $input['delivery_charge'],
        $input['total'],
        $input['status']
    ]);

    $order_id = $pdo->lastInsertId();

    // Insert order items
    $item_sql = "INSERT INTO order_items (order_id, item_id, name, price, quantity) VALUES (?, ?, ?, ?, ?)";
    $item_stmt = $pdo->prepare($item_sql);
    foreach ($input['items'] as $item) {
        $item_stmt->execute([
            $order_id,
            $item['item_id'],
            $item['name'],
            $item['price'],
            $item['quantity']
        ]);
    }

    $pdo->commit();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'message' => 'Order placed successfully'
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>