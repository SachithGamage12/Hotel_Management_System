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
$base_url = 'https://hotelgrandguardian.org/uploads/uber_stores/';

try {
    $stmt = $pdo->prepare("SELECT id, user_id, type, name, contact, address, business_reg, open_time, close_time, 
                           profile_pic, food_licence, nic_front, nic_back, status 
                           FROM uber_store_registrations WHERE user_id = ?");
    $stmt->execute([$store_id]);
    $registration = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($registration) {
        // Construct full URLs for image fields
        $registration['profile_pic_url'] = $registration['profile_pic'] ? $base_url . $registration['profile_pic'] : null;
        $registration['food_licence_url'] = $registration['food_licence'] ? $base_url . $registration['food_licence'] : null;
        $registration['nic_front_url'] = $registration['nic_front'] ? $base_url . $registration['nic_front'] : null;
        $registration['nic_back_url'] = $registration['nic_back'] ? $base_url . $registration['nic_back'] : null;

        echo json_encode([
            'success' => true,
            'has_registration' => true,
            'registration' => $registration
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'has_registration' => false
        ]);
    }
} catch (Exception $e) {
    error_log('Error checking registration: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to check registration']);
}
?>