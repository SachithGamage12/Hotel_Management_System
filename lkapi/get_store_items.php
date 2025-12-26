<?php
// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/php-error.log'); // Update with actual path

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        error_log('DB connection failed: ' . $conn->connect_error);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database connection failed']);
        exit;
    }

    $store_id = $_GET['store_id'] ?? '';
    if (empty($store_id)) {
        echo json_encode(['success' => false, 'error' => 'Missing store_id']);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT id, name, description, price, quantity, image, created_at 
        FROM uber_food_items 
        WHERE store_id = ? AND quantity > 0 
        ORDER BY created_at DESC
    ");
    if (!$stmt) {
        error_log('Prepare failed: ' . $conn->error);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Query preparation failed']);
        exit;
    }

    $stmt->bind_param('s', $store_id);
    if (!$stmt->execute()) {
        error_log('Execute failed: ' . $stmt->error);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Query execution failed']);
        exit;
    }

    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['image'])) {
            $row['image'] = "https://hotelgrandguardian.org/uploads/uber_foods/" . $row['image'];
        }
        $items[] = $row;
    }

    echo json_encode([
        'success' => true,
        'items' => $items,
        'count' => count($items)
    ]);

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    error_log('Exception in get_store_items.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>