<?php
include 'db.php';

header('Content-Type: application/json');

$term = isset($_GET['term']) ? trim($_GET['term']) : '';

if (empty($term)) {
    echo json_encode([]);
    exit;
}

$items = [];

try {
    // Use MySQLi instead of PDO
    $searchTerm = '%' . $term . '%';
    $stmt = $conn->prepare("SELECT id, name, unit_type FROM items WHERE name LIKE ? ORDER BY name LIMIT 10");
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    $stmt->bind_param("s", $searchTerm);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception("Failed to get result: " . $stmt->error);
    }
    
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'unit_type' => $row['unit_type'] ?? ''
        ];
    }
    
    $stmt->close();
    
} catch(Exception $e) {
    error_log("Search item error: " . $e->getMessage());
    // Return error for debugging (remove this in production)
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

echo json_encode($items);
?>