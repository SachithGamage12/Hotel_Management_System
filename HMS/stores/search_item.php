<?php
include 'config.php'; // Include your PDO database connection

header('Content-Type: application/json');

try {
    $query = isset($_GET['query']) ? trim($_GET['query']) : '';
    $results = [];

    if (strlen($query) > 2) {
        $stmt = $conn->prepare("SELECT id, item_name, unit FROM inventory WHERE item_name LIKE :query ORDER BY item_name");
        $stmt->bindValue(':query', "%$query%");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode($results);
} catch (Exception $e) {
    echo json_encode([]);
}
?>