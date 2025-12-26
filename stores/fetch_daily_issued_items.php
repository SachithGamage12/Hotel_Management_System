<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'F:/xampp/htdocs/HMS/stores/php_errors.log');

$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("Connected to database for fetch_daily_issued_items");

    $unload_date = $_GET['unload_date'] ?? '';
    $debug = isset($_GET['debug']) && $_GET['debug'] == '1';
    if (empty($unload_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $unload_date)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid unload date format']);
        exit;
    }

    // Calculate previous date for night functions
    $previous_date = date('Y-m-d', strtotime($unload_date . ' -1 day'));
    error_log("Processing unload_date=$unload_date, previous_date=$previous_date");

    // Check if unload already exists for this date
    $stmt = $conn->prepare("SELECT item_id FROM unload_sheet WHERE unload_date = ?");
    $stmt->execute([$unload_date]);
    if ($stmt->fetch()) {
        error_log("Unload already recorded for unload_date=$unload_date");
        http_response_code(400);
        echo json_encode(['error' => 'Unload already recorded for this date']);
        exit;
    }

    // Debug: Log all order sheets for the dates
    if ($debug) {
        $stmt = $conn->prepare("
            SELECT order_sheet_no, function_date, day_night, function_type, status, item_id, requested_qty
            FROM order_sheet
            WHERE function_date IN (?, ?)
            AND function_date IS NOT NULL
        ");
        $stmt->execute([$unload_date, $previous_date]);
        $debug_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Debug: All order sheets for dates $unload_date, $previous_date: " . json_encode($debug_orders, JSON_PRETTY_PRINT));
    }

    // Fetch issued items
    $stmt = $conn->prepare("
        SELECT os.item_id, i.item_name, SUM(os.requested_qty) AS total_issued_qty, i.unit
        FROM order_sheet os
        LEFT JOIN inventory i ON os.item_id = i.id
        WHERE os.status = 'completed'
        AND os.function_date IS NOT NULL
        AND os.requested_qty > 0
        AND (
            (os.function_date = ? AND (os.day_night = 'Day' OR os.day_night IS NULL))
            OR (os.function_date = ? AND os.day_night = 'Night')
        )
        GROUP BY os.item_id, i.item_name, i.unit
        HAVING total_issued_qty > 0
        ORDER BY i.item_name
    ");
    $stmt->execute([$unload_date, $previous_date]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($items)) {
        error_log("No items found for unload_date=$unload_date, previous_date=$previous_date");
        // Debug: Check if any completed order sheets exist
        if ($debug) {
            $stmt = $conn->prepare("
                SELECT COUNT(*) as count
                FROM order_sheet
                WHERE status = 'completed'
                AND function_date IS NOT NULL
                AND requested_qty > 0
                AND (
                    (function_date = ? AND (day_night = 'Day' OR day_night IS NULL))
                    OR (function_date = ? AND day_night = 'Night')
                )
            ");
            $stmt->execute([$unload_date, $previous_date]);
            $count = $stmt->fetchColumn();
            error_log("Debug: Completed order sheets count for $unload_date, $previous_date: $count");
        }
        echo json_encode([]);
    } else {
        error_log("Fetched " . count($items) . " items for unload_date=$unload_date");
        header('Content-Type: application/json');
        echo json_encode($items);
    }
} catch(PDOException $e) {
    error_log("Fetch daily issued items error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>