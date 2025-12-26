<?php
header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'data' => [], 'count' => 0]);
// Enable error reporting and logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'F:/xampp/htdocs/HMS/stores/php_errors.log');

// CORS headers for cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Content-Type: application/json; charset=utf8mb4');

// Database configuration
$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

try {
    // Initialize PDO connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    error_log("Database connection successful at " . date('Y-m-d H:i:s'));

    // Verify that tables exist
    $tableCheck = $conn->query("SHOW TABLES LIKE 'skykitchen_buffer'");
    if ($tableCheck->rowCount() == 0) {
        throw new PDOException("Table 'skykitchen_buffer' does not exist in database '$dbname'");
    }
    $tableCheck = $conn->query("SHOW TABLES LIKE 'inventory'");
    if ($tableCheck->rowCount() == 0) {
        throw new PDOException("Table 'inventory' does not exist in database '$dbname'");
    }

    // Fetch items from skykitchen_buffer where remaining_quantity < quantity
    $stmt = $conn->prepare("
        SELECT b.item_id, i.item_name, i.unit, b.quantity, b.remaining_quantity, 
               (b.quantity - b.remaining_quantity) AS usage
        FROM skykitchen_buffer b
        JOIN inventory i ON b.item_id = i.id
        WHERE b.remaining_quantity < b.quantity
    ");
    $stmt->execute();
    $buffer_items = $stmt->fetchAll();

    // Log the number of items fetched
    error_log("Fetched " . count($buffer_items) . " buffer items for SKY Buffer Stock Refill at " . date('Y-m-d H:i:s'));

    // Return JSON response
    echo json_encode([
        'status' => 'success',
        'data' => $buffer_items,
        'count' => count($buffer_items)
    ]);

} catch (PDOException $e) {
    // Log detailed error information
    $errorMessage = "Error fetching buffer items: " . $e->getMessage() . " (Code: " . $e->getCode() . ") at " . date('Y-m-d H:i:s');
    error_log($errorMessage);

    // Return detailed error response
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'error' => 'Failed to fetch buffer items',
        'details' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
} catch (Exception $e) {
    // Handle unexpected errors
    $errorMessage = "Unexpected error: " . $e->getMessage() . " at " . date('Y-m-d H:i:s');
    error_log($errorMessage);

    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'error' => 'Unexpected server error',
        'details' => $e->getMessage()
    ]);
} finally {
    // Close the database connection
    $conn = null;
}
?>