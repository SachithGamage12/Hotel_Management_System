<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";


try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $field_type = $_POST['field_type'];
    $name = $_POST['new_value'];
    
    $stmt = $conn->prepare("INSERT INTO $field_type (name) VALUES (:name)");
    $stmt->bindParam(':name', $name);
    $stmt->execute();
    
    $id = $conn->lastInsertId();
    echo json_encode(['success' => true, 'id' => $id, 'name' => $name]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>