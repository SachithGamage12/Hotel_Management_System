<?php
// Enable error reporting and logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'F:/xampp/htdocs/HMS/stores/php_errors.log');

// Database connection
$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("Database connection successful at " . date('Y-m-d H:i:s'));
    
    // Fetch all order sheets (pending and issued) with function_type, function_date, and day_night
    $stmt = $conn->prepare("
        SELECT os.order_sheet_no, os.item_id, i.item_name, 
               os.requested_qty, i.unit, 
               os.request_date, os.status, 
               r.name AS responsible_name,
               os.function_type, os.function_date, os.day_night
        FROM order_sheet os
        JOIN inventory i ON os.item_id = i.id
        LEFT JOIN responsible r ON os.responsible_id = r.id
        ORDER BY os.order_sheet_no DESC, os.item_id
    ");
    $stmt->execute();
    $pending_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Raw pending requests: " . json_encode($pending_requests));
    
    // Aggregate items by order_sheet_no
    $grouped_requests = [];
    foreach ($pending_requests as $request) {
        $order_sheet_no = $request['order_sheet_no'];
        if (!isset($grouped_requests[$order_sheet_no])) {
            $grouped_requests[$order_sheet_no] = [
                'order_sheet_no' => $request['order_sheet_no'],
                'items' => [],
                'request_date' => $request['request_date'],
                'status' => $request['status'],
                'responsible_name' => $request['responsible_name'],
                'function_type' => $request['function_type'],
                'function_date' => $request['function_date'],
                'day_night' => $request['day_night']
            ];
        }
        $grouped_requests[$order_sheet_no]['items'][] = [
            'item_id' => $request['item_id'],
            'item_name' => $request['item_name'],
            'requested_qty' => $request['requested_qty'],
            'unit' => $request['unit'] ?? 'Unit'
        ];
    }
    
    error_log("Fetched pending orders for API: " . json_encode($grouped_requests));
    header('Content-Type: application/json');
    echo json_encode($grouped_requests);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . htmlspecialchars($e->getMessage())]);
}
?>