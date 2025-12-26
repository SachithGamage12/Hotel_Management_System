<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once 'db_connect.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

$response = [];

if (empty($query)) {
    echo json_encode($response);
    exit;
}

try {
    $sql = "SELECT i.id, i.item_code, i.item_name, i.price, i.discount_price, i.discount_start_date, i.discount_end_date, c.name as category_name
            FROM hggitems i
            LEFT JOIN categories c ON i.category_id = c.id
            WHERE i.status = 'active' 
            AND (i.item_name LIKE ? OR i.item_code LIKE ?)";
    
    $like_params = ["%$query%", "%$query%"];
    $types = 'ss';
    $params = $like_params;
    
    if ($category_id > 0) {
        $sql .= " AND i.category_id = ?";
        $types .= 'i';
        $params[] = $category_id;
    }
    
    $sql .= " ORDER BY i.item_name ASC LIMIT 5";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Use discount price if valid (considering current date)
        $current_date = date('Y-m-d');
        $price = $row['discount_price'] && 
                 (!empty($row['discount_start_date']) && $row['discount_start_date'] <= $current_date) && 
                 (!empty($row['discount_end_date']) && $row['discount_end_date'] >= $current_date) 
                 ? $row['discount_price'] 
                 : $row['price'];
        
        $response[] = [
            'id' => $row['id'],
            'item_code' => $row['item_code'],
            'item_name' => $row['item_name'],
            'price' => floatval($price),
            'category_name' => $row['category_name'] ?? 'Uncategorized'
        ];
    }
    
    $stmt->close();
} catch (Exception $e) {
    error_log("Search error: " . $e->getMessage()); // Log for server logs
    http_response_code(500);
    $response = ['error' => 'Search failed: ' . $e->getMessage()];
}

$conn->close();
echo json_encode($response);
?>