<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->query("SELECT MAX(grc_number) as max_grc FROM guests");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $next_grc = $result['max_grc'] ? $result['max_grc'] + 1 : 1200;
    
    echo json_encode([
        'success' => true,
        'grc_number' => $next_grc
    ]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>