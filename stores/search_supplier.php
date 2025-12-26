<?php
include 'config.php';

header('Content-Type: application/json');

if(isset($_GET['query'])) {
    $query = '%' . $_GET['query'] . '%';
    
    try {
        $stmt = $conn->prepare("SELECT id, name, contact_number FROM suppliers 
                              WHERE name LIKE :query OR contact_number LIKE :query 
                              ORDER BY name LIMIT 10");
        $stmt->bindParam(':query', $query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($results);
    } catch(PDOException $e) {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}
?>