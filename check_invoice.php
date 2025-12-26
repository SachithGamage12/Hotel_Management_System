<?php
header('Content-Type: application/json');

// Database connection parameters
$host = 'localhost';
$dbname = 'hotelgrandguardi_wedding_bliss';
$username = 'hotelgrandguardi_root';
$password = 'Sun123flower@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'get_highest_invoice_id') {
        // Get the highest invoice_id from payment_history
        $sql = "SELECT MAX(invoice_id) as highest_invoice_id FROM payment_history";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'highest_invoice_id' => $result['highest_invoice_id'] ?? null
        ]);
    } else {
        echo json_encode(['error' => 'Invalid action']);
    }

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>