<?php
include 'db.php';

$term = isset($_GET['term']) ? trim($_GET['term']) : '';
$items = [];

try {
    // Prepare and execute query with LIKE for partial matches
    /** @var PDOStatement $stmt */
    $stmt = $conn->prepare("SELECT id, name, unit_type FROM items WHERE name LIKE :term ORDER BY name LIMIT 10");
    if (!$stmt) {
        throw new PDOException("Failed to prepare item search query");
    }
    $stmt->execute(['term' => "%$term%"]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($items === false) {
        throw new PDOException("Failed to fetch items");
    }
} catch(PDOException $e) {
    error_log("Search item error: " . $e->getMessage());
    $items = [];
    // Return error for debugging
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

header('Content-Type: application/json');
echo json_encode($items);
?>