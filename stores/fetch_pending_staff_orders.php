<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

try {
    $conn = new PDO("mysql:host=localhost;dbname=hotelgrandguardi_wedding_bliss", "hotelgrandguardi_root", "Sun123flower@");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("
        SELECT DISTINCT os.requesting_date, os.order_sheet_no, os.item_id, i.item_name,
               os.requested_qty, os.issued_qty, os.requested_unit, i.unit,
               os.request_date, m.username as manager_name, m.id as manager_id,
               os.status, os.department
        FROM staff_order_sheet os
        JOIN inventory i ON os.item_id = i.id
        LEFT JOIN managers m ON os.manager_id = m.id
        WHERE os.status = 'pending'
           OR os.order_sheet_no IN (
               SELECT DISTINCT order_sheet_no
               FROM staff_order_sheet
               WHERE status = 'issued'
               AND order_sheet_no IN (
                   SELECT order_sheet_no
                   FROM staff_order_sheet
                   WHERE status = 'pending'
               )
           )
        ORDER BY os.requesting_date DESC, os.order_sheet_no DESC, os.item_id
    ");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $orders_by_date = [];
    foreach ($result as $row) {
        $requesting_date = $row['requesting_date'];
        $order_sheet_no = $row['order_sheet_no'];
        $item_id = $row['item_id'];

        if (!isset($orders_by_date[$requesting_date])) {
            $orders_by_date[$requesting_date] = [
                'requesting_date' => $requesting_date,
                'order_sheets' => [],
                'items_by_id' => [],
                'manager_names' => [],
                'manager_ids' => [],
                'departments' => [],
                'order_sheet_numbers' => []
            ];
        }

        if (!isset($orders_by_date[$requesting_date]['order_sheets'][$order_sheet_no])) {
            $orders_by_date[$requesting_date]['order_sheets'][$order_sheet_no] = [
                'order_sheet_no' => $order_sheet_no,
                'request_date' => $row['request_date'],
                'manager_name' => $row['manager_name'],
                'manager_id' => $row['manager_id'],
                'department' => $row['department'] ?? 'N/A',
                'items' => []
            ];
            $orders_by_date[$requesting_date]['order_sheet_numbers'][] = $order_sheet_no;
        }

        $orders_by_date[$requesting_date]['order_sheets'][$order_sheet_no]['items'][] = [
            'item_id' => $item_id,
            'item_name' => $row['item_name'],
            'requested_qty' => floatval($row['requested_qty']),
            'issued_qty' => floatval($row['issued_qty']),
            'unit' => $row['unit'] ?? 'Unit',
            'status' => $row['status']
        ];

        if (!isset($orders_by_date[$requesting_date]['items_by_id'][$item_id])) {
            $orders_by_date[$requesting_date]['items_by_id'][$item_id] = [
                'item_id' => $item_id,
                'item_name' => $row['item_name'],
                'requested_qty' => 0,
                'issued_qty' => 0,
                'unit' => $row['unit'] ?? 'Unit',
                'order_sheets' => []
            ];
        }

        $orders_by_date[$requesting_date]['items_by_id'][$item_id]['requested_qty'] += floatval($row['requested_qty']);
        $orders_by_date[$requesting_date]['items_by_id'][$item_id]['issued_qty'] += floatval($row['issued_qty']);
        $orders_by_date[$requesting_date]['items_by_id'][$item_id]['order_sheets'][] = $order_sheet_no;
        $orders_by_date[$requesting_date]['manager_names'][$row['manager_name'] ?? 'N/A'] = true;
        $orders_by_date[$requesting_date]['manager_ids'][$row['manager_id']] = true;
        $orders_by_date[$requesting_date]['departments'][$row['department'] ?? 'N/A'] = true;
    }

    foreach ($orders_by_date as &$date_order) {
        $date_order['manager_names'] = array_keys($date_order['manager_names']);
        $date_order['manager_ids'] = array_keys($date_order['manager_ids']);
        $date_order['departments'] = array_keys($date_order['departments']);
        $date_order['order_sheets'] = array_values($date_order['order_sheets']);
        $date_order['items_by_id'] = array_values($date_order['items_by_id']);
    }

    echo json_encode($orders_by_date);
} catch (PDOException $e) {
    error_log("Error fetching pending orders: " . $e->getMessage());
    echo json_encode(['error' => 'Failed to fetch pending orders']);
}
?>