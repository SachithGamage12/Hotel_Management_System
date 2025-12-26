<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include 'config.php'; // Your database connection

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $category = $_GET['category'] ?? 'All';
    $store_id = $_GET['store_id'] ?? null;
    
    if (!$store_id) {
        throw new Exception('Store ID is required');
    }
    
    $sql = "SELECT id, name, category, type, contact, address, profile_pic_url, 
                   COALESCE(rating, 4.0) as rating,
                   COALESCE(delivery_time, '30-45 min') as delivery_time,
                   COALESCE(delivery_fee, 'Free') as delivery_fee
            FROM restaurants 
            WHERE store_id = ? AND status = 'active'";
    
    if ($category !== 'All') {
        $sql .= " AND (category = ? OR type = ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$store_id, $category, $category]);
    } else {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$store_id]);
    }
    
    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ensure consistent field names
    $result = array_map(function($restaurant) {
        return [
            'id' => $restaurant['id'],
            'name' => $restaurant['name'],
            'category' => $restaurant['category'] ?: $restaurant['type'],
            'type' => $restaurant['type'],
            'contact' => $restaurant['contact'],
            'address' => $restaurant['address'],
            'profile_pic_url' => $restaurant['profile_pic_url'],
            'rating' => (float)$restaurant['rating'],
            'delivery_time' => $restaurant['delivery_time'],
            'delivery_fee' => $restaurant['delivery_fee']
        ];
    }, $restaurants);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>