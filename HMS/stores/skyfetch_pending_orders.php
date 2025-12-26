
<?php
// Database configuration
$conn = new mysqli('localhost', 'hotelgrandguardi_root', 'Sun123flower@', 'hotelgrandguardi_wedding_bliss');
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Fetch pending and partially issued orders, grouped by function_date
$result = $conn->query("
    SELECT DISTINCT os.function_date, os.order_sheet_no, os.item_id, i.item_name, 
           os.requested_qty, os.issued_qty, i.unit, 
           os.request_date, r.name as responsible_name, r.id as responsible_id, 
           os.status, os.function_type, os.day_night
    FROM skyorder_sheet os
    JOIN inventory i ON os.item_id = i.id
    LEFT JOIN responsible r ON os.responsible_id = r.id
    WHERE os.status = 'pending'
       OR os.order_sheet_no IN (
           SELECT DISTINCT order_sheet_no 
           FROM skyorder_sheet 
           WHERE status = 'issued' 
           AND order_sheet_no IN (
               SELECT order_sheet_no 
               FROM skyorder_sheet 
               WHERE status = 'pending'
           )
       )
    ORDER BY os.function_date DESC, os.order_sheet_no DESC, os.item_id
");

$orders_by_date = [];
$item_unload_quantities = [];

// Fetch unload quantities separately to avoid multiplication
$unload_result = $conn->query("
    SELECT item_id, COALESCE(SUM(remaining_qty), 0) as remaining_qty
    FROM skyfunction_unload
    GROUP BY item_id
");
if ($unload_result) {
    while ($unload_row = $unload_result->fetch_assoc()) {
        $item_unload_quantities[$unload_row['item_id']] = (int)$unload_row['remaining_qty'];
    }
} else {
    error_log("Unload query failed: " . $conn->error);
}

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $function_date = $row['function_date'];
        $order_sheet_no = $row['order_sheet_no'];
        $item_id = $row['item_id'];
        $remaining_qty = isset($item_unload_quantities[$item_id]) ? $item_unload_quantities[$item_id] : 0;

        // Initialize function_date entry if not exists
        if (!isset($orders_by_date[$function_date])) {
            $orders_by_date[$function_date] = [
                'function_date' => $function_date,
                'order_sheets' => [],
                'items_by_id' => [],
                'responsible_names' => [],
                'responsible_ids' => [],
                'function_types' => [],
                'day_nights' => []
            ];
        }

        // Initialize order sheet if not exists
        if (!isset($orders_by_date[$function_date]['order_sheets'][$order_sheet_no])) {
            $orders_by_date[$function_date]['order_sheets'][$order_sheet_no] = [
                'order_sheet_no' => $order_sheet_no,
                'request_date' => $row['request_date'],
                'responsible_name' => $row['responsible_name'] ?? 'N/A',
                'responsible_id' => $row['responsible_id'],
                'function_type' => $row['function_type'] ?? 'N/A',
                'day_night' => $row['day_night'] ?? 'N/A',
                'items' => []
            ];
        }

        // Add item to order sheet
        $orders_by_date[$function_date]['order_sheets'][$order_sheet_no]['items'][] = [
            'item_id' => $item_id,
            'item_name' => $row['item_name'],
            'requested_qty' => $row['requested_qty'],
            'issued_qty' => $row['issued_qty'],
            'remaining_qty' => $remaining_qty,
            'unit' => $row['unit'] ?? 'Unit',
            'status' => $row['status']
        ];

        // Aggregate items by item_id
        if (!isset($orders_by_date[$function_date]['items_by_id'][$item_id])) {
            $orders_by_date[$function_date]['items_by_id'][$item_id] = [
                'item_id' => $item_id,
                'item_name' => $row['item_name'],
                'requested_qty' => 0,
                'issued_qty' => 0,
                'remaining_qty' => $remaining_qty,
                'unit' => $row['unit'] ?? 'Unit',
                'order_sheets' => []
            ];
        }

        // Sum quantities for the item
        $orders_by_date[$function_date]['items_by_id'][$item_id]['requested_qty'] += $row['requested_qty'];
        $orders_by_date[$function_date]['items_by_id'][$item_id]['issued_qty'] += $row['issued_qty'];
        if (!in_array($order_sheet_no, $orders_by_date[$function_date]['items_by_id'][$item_id]['order_sheets'])) {
            $orders_by_date[$function_date]['items_by_id'][$item_id]['order_sheets'][] = $order_sheet_no;
        }

        // Collect responsible names, IDs, function types, and day/night
        $orders_by_date[$function_date]['responsible_names'][$row['responsible_name'] ?? 'N/A'] = true;
        if ($row['responsible_id']) {
            $orders_by_date[$function_date]['responsible_ids'][$row['responsible_id']] = true;
        }
        $orders_by_date[$function_date]['function_types'][$row['function_type'] ?? 'N/A'] = true;
        $orders_by_date[$function_date]['day_nights'][$row['day_night'] ?? 'N/A'] = true;
    }

    // Convert associative arrays to indexed arrays for JSON compatibility
    foreach ($orders_by_date as &$date_order) {
        $date_order['responsible_names'] = array_keys($date_order['responsible_names']);
        $date_order['responsible_ids'] = array_keys($date_order['responsible_ids']);
        $date_order['function_types'] = array_keys($date_order['function_types']);
        $date_order['day_nights'] = array_keys($date_order['day_nights']);
        $date_order['order_sheets'] = array_values($date_order['order_sheets']);
        $date_order['items_by_id'] = array_values($date_order['items_by_id']);
    }
    unset($date_order); // Unset reference to avoid issues
} else {
    error_log("Query failed: " . $conn->error);
    die(json_encode(['error' => 'Query failed: ' . $conn->error]));
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($orders_by_date);
?>

