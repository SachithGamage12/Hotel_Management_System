<?php
header('Content-Type: application/json');

$conn = new mysqli('localhost', 'hotelgrandguardi_root', 'Sun123flower@', 'hotelgrandguardi_wedding_bliss');
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$result = $conn->query("SELECT id, name FROM responsibilities ORDER BY name ASC");
$responsibles = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $responsibles[] = [
            'id' => $row['id'],
            'name' => $row['name']
        ];
    }
} else {
    error_log("Query failed: " . $conn->error);
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch responsible persons']);
    exit;
}

$conn->close();
echo json_encode($responsibles);
?>