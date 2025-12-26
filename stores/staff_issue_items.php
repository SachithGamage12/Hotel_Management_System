<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../stores_login.php");
    exit();
}
$username = htmlspecialchars($_SESSION['username']);

// Enable output buffering
ob_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Stock Issue</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .table-container {
            max-height: 500px;
            overflow-y: auto;
        }
        .error {
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            text-align: center;
        }
        .success {
            background-color: #d1fae5;
            color: #065f46;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            text-align: center;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            align-items: center;
            justify-content: center;
            z-index: 50;
        }
        .modal-content {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            max-width: 600px;
            width: 100%;
            max-height: 80vh;
            overflow-y: auto;
            animation: slideIn 0.3s ease-out;
        }
        .password-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            align-items: center;
            justify-content: center;
            z-index: 60;
        }
        .password-modal-content {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            max-width: 400px;
            width: 100%;
        }
        .error-text {
            color: #b91c1c;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: none;
        }
        .alert-blink {
            animation: blink 1s infinite;
            background-color: #fef08a;
            color: #854d0e;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: bold;
        }
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes blink {
            50% { opacity: 0.5; }
        }
        .out-of-stock {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        /* Print Modal Styles */
        .print-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            align-items: center;
            justify-content: center;
            z-index: 50;
            overflow: auto;
        }
        .print-modal-content {
            width: 80mm;
            max-width: 90vw;
            margin: 20px auto;
            padding: 10px;
            background: #fff;
            font-family: 'Arial', sans-serif;
            font-size: 9pt;
            line-height: 1.5;
            color: #000;
            box-sizing: border-box;
            border-radius: 0.5rem;
        }
        .receipt-container {
            width: 100%;
            margin: 0;
            padding: 0;
            text-align: left;
        }
        .receipt-header {
            margin: 0 0 5mm 0;
            padding: 0 0 3mm 0;
            border-bottom: 2px solid #000;
        }
        .receipt-header h2 {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 2mm;
            text-transform: uppercase;
            text-align: center;
        }
        .receipt-header div {
            font-size: 9pt;
            font-weight: bold;
            display: flex;
            align-items: center;
            margin-bottom: 2mm;
        }
        .receipt-header div strong {
            display: inline-block;
            min-width: 30mm;
            text-align: left;
        }
        .receipt-header div .colon {
            display: inline-block;
            width: 2mm;
            text-align: center;
        }
        .receipt-header div span {
            display: inline-block;
            margin-left: 1mm;
        }
        .receipt-details {
            margin: 0 0 5mm 0;
            padding: 0 0 3mm 0;
            font-size: 9pt;
            text-align: left;
        }
        .receipt-details div {
            margin-bottom: 2mm;
            word-break: break-word;
            font-weight: bold;
        }
        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 5mm 0;
            font-size: 9pt;
        }
        .receipt-table th {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 2mm 1mm;
            text-align: left;
            font-weight: bold;
        }
        .receipt-table th:nth-child(1) { width: 30%; }
        .receipt-table th:nth-child(2) { width: 20%; }
        .receipt-table th:nth-child(3) { width: 20%; }
        .receipt-table th:nth-child(4) { width: 30%; }
        .receipt-table td {
            padding: 2mm 1mm;
            border-bottom: 1px solid #000;
            vertical-align: top;
            font-weight: bold;
        }
        .receipt-table td:first-child {
            word-wrap: break-word;
            white-space: normal;
        }
        .receipt-table td:nth-child(2),
        .receipt-table td:nth-child(3),
        .receipt-table td:nth-child(4) {
            white-space: nowrap;
        }
        .receipt-table tr:last-child td {
            border-bottom: 2px solid #000;
        }
        .receipt-footer {
            margin: 0 0 5mm 0;
            padding: 3mm 0 0 0;
            border-top: 2px dashed #000;
            font-size: 9pt;
            text-align: left;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin: 0 0 5mm 0;
            font-size: 9pt;
            text-align: left;
        }
        .signature-box {
            width: 48%;
            min-width: 35mm;
            text-align: center;
            padding: 3mm;
            overflow: visible;
        }
        .signature-box div {
            font-weight: bold;
            font-size: 10pt;
            white-space: nowrap;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 8mm;
            padding-top: 1mm;
        }
        .controls {
            text-align: center;
            margin: 10px 0;
            padding: 10px 0;
            border-top: 2px dashed #000;
            display: block !important;
        }
        .controls button {
            padding: 5px 10px;
            margin: 0 5px;
            font-size: 10pt;
            cursor: pointer;
            background: #f0f0f0;
            border: 1px solid #000;
            border-radius: 4px;
            font-weight: bold;
            transition: background 0.2s;
        }
        .controls button:hover {
            background: #e0e0e0;
        }
        .no-print {
            display: block !important;
        }
        @media screen and (max-width: 600px) {
            .print-modal-content {
                width: 90vw;
                padding: 15px;
                font-size: 8pt;
            }
            .receipt-table th, .receipt-table td {
                padding: 1mm 0.5mm;
            }
            .receipt-table th:nth-child(1),
            .receipt-table td:nth-child(1) {
                width: 25%;
            }
            .receipt-table th:nth-child(2),
            .receipt-table td:nth-child(2),
            .receipt-table th:nth-child(3),
            .receipt-table td:nth-child(3),
            .receipt-table th:nth-child(4),
            .receipt-table td:nth-child(4) {
                width: 25%;
            }
            .signatures {
                flex-direction: column;
                align-items: center;
            }
            .signature-box {
                width: 100%;
                min-width: unset;
                margin-bottom: 10px;
            }
            .controls button {
                width: 45%;
                padding: 8px;
                font-size: 9pt;
            }
        }
        @media print {
            body, body * {
                visibility: hidden;
                margin: 0;
                padding: 0;
            }
            .print-modal-content, .print-modal-content * {
                visibility: visible;
            }
            .print-modal-content {
                position: static;
                width: 80mm;
                max-width: 80mm;
                margin: 0;
                padding: 0;
                background: #fff;
                box-sizing: border-box;
                border-radius: 0;
            }
            .controls, .no-print {
                display: none !important;
            }
            .receipt-header, .receipt-details, .receipt-table, .receipt-footer, .signatures {
                margin: 0 0 5mm 0;
                padding: 0;
            }
            .receipt-header h2 {
                font-size: 14pt;
                font-weight: bold;
                margin-bottom: 2mm;
                text-transform: uppercase;
            }
            .receipt-header div, .receipt-details div, .receipt-table th, .receipt-table td, .signatures div {
                color: #000 !important;
                font-weight: bold !important;
            }
            .receipt-table {
                width: 80mm;
                border-collapse: collapse;
            }
            .signature-box {
                width: 48%;
                min-width: 35mm;
                text-align: center;
                padding: 3mm;
                font-size: 10pt;
            }
            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-teal-100 to-blue-200 min-h-screen p-6">
    <button onclick="window.location.href='../stores.php'" style="background-color: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
        Back
    </button>
    <div class="container mx-auto max-w-6xl bg-white p-10 rounded-3xl shadow-2xl">
        <h2 class="text-4xl font-bold text-teal-900 mb-10 text-center">Issue Stock for Staff Orders</h2>
        <div id="pending-alert" class="alert-blink hidden">New Pending or Partially Issued Orders Available!</div>
        <div id="print-error" class="error hidden">Failed to print. Please check printer connection and try again.</div>
        <?php
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        ini_set('log_errors', 1);
        ini_set('error_log', 'php_errors.log');

        // Database connection
        $servername = "localhost";
        $username = "hotelgrandguardi_root";
        $password = "Sun123flower@";
        $dbname = "hotelgrandguardi_wedding_bliss";

        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            error_log("Database connection successful at " . date('Y-m-d H:i:s'));

            // Add issued_qty column if it doesn't exist
            $conn->exec("ALTER TABLE staff_order_sheet ADD COLUMN IF NOT EXISTS issued_qty DOUBLE DEFAULT 0");

            // Handle stock issuance
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issue_all'])) {
                $requesting_date = $_POST['requesting_date'];
                $manager_id = (int)$_POST['manager_id'];
                $password = $_POST['password'];
                $today = date('Y-m-d');
                error_log("=== ISSUE ALL STARTED ===");
                error_log("Requesting Date: $requesting_date, Manager ID: $manager_id, Today: $today");

                $conn->beginTransaction();
                try {
                    // Verify manager's password
                    $stmt = $conn->prepare("SELECT id, name, password FROM responsibilities WHERE id = ?");
                    $stmt->execute([$manager_id]);
                    $manager = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$manager) {
                        throw new Exception("Manager not found.");
                    }
                    if (!password_verify($password, $manager['password'])) {
                        throw new Exception("Invalid password for manager.");
                    }
                    $manager_name = $manager['name'];

                    // Fetch order details
                    $stmt = $conn->prepare("
                        SELECT os.order_sheet_no, os.item_id, os.requested_qty, os.issued_qty, os.requested_unit, i.item_name, i.unit, os.department, os.requesting_date
                        FROM staff_order_sheet os
                        JOIN inventory i ON os.item_id = i.id
                        WHERE os.requesting_date = ? AND os.status = 'pending'
                        ORDER BY os.order_sheet_no ASC
                    ");
                    $stmt->execute([$requesting_date]);
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $items_by_id = [];
                    $order_sheets = [];
                    $departments = [];
                    foreach ($result as $row) {
                        $item_id = $row['item_id'];
                        $order_sheet_no = $row['order_sheet_no'];
                        if (!isset($items_by_id[$item_id])) {
                            $items_by_id[$item_id] = [
                                'item_id' => $item_id,
                                'item_name' => $row['item_name'],
                                'requested_qty' => 0,
                                'issued_qty' => 0,
                                'unit' => $row['unit'] ?? 'Unit',
                                'order_sheets' => []
                            ];
                        }
                        $items_by_id[$item_id]['requested_qty'] += floatval($row['requested_qty']);
                        $items_by_id[$item_id]['issued_qty'] += floatval($row['issued_qty']);
                        $items_by_id[$item_id]['order_sheets'][] = [
                            'order_sheet_no' => $order_sheet_no,
                            'requested_qty' => floatval($row['requested_qty']),
                            'issued_qty' => floatval($row['issued_qty'])
                        ];
                        $order_sheets[$order_sheet_no] = true;
                        $departments[$row['department'] ?? 'N/A'] = true;
                    }

                    if (empty($items_by_id)) {
                        throw new Exception("No pending items found for Requesting Date: $requesting_date");
                    }

                    $issued_items = [];
                    $partial_items = [];

                    foreach ($items_by_id as $item_id => &$item) {
                        error_log("Processing item_id: $item_id, item_name: {$item['item_name']}, requested_qty: {$item['requested_qty']}, issued_qty: {$item['issued_qty']}");

                        usort($item['order_sheets'], function($a, $b) {
                            return strcmp($a['order_sheet_no'], $b['order_sheet_no']);
                        });

                        foreach ($item['order_sheets'] as $order) {
                            $order_sheet_no = $order['order_sheet_no'];
                            $order_balance = floatval($order['requested_qty']) - floatval($order['issued_qty']);

                            if ($order_balance <= 0) {
                                continue;
                            }

                            // Fetch available stock
                            $stmt = $conn->prepare("
                                SELECT SUM(pi.stock) as total_stock
                                FROM purchased_items pi
                                WHERE pi.item_id = ? AND pi.stock > 0 AND (pi.expiry_date IS NULL OR pi.expiry_date >= ?)
                            ");
                            $stmt->execute([$item_id, $today]);
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            $regular_stock = floatval($row['total_stock'] ?? 0);

                            $stmt = $conn->prepare("SELECT buffer_stock FROM inventory WHERE id = ?");
                            $stmt->execute([$item_id]);
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            $buffer_stock = floatval($row['buffer_stock'] ?? 0);

                            $total_available_stock = $regular_stock + $buffer_stock;
                            $issued_from_stock = min($order_balance, $total_available_stock);

                            // Deduct from regular stock
                            $remaining_stock_to_issue = $issued_from_stock;
                            $actual_issued_from_stock = 0;
                            if ($remaining_stock_to_issue > 0 && $regular_stock > 0) {
                                $stmt = $conn->prepare("
                                    SELECT id, stock
                                    FROM purchased_items
                                    WHERE item_id = ? AND stock > 0 AND (expiry_date IS NULL OR expiry_date >= ?)
                                    ORDER BY COALESCE(expiry_date, '9999-12-31') ASC, purchased_date ASC
                                ");
                                $stmt->execute([$item_id, $today]);
                                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                foreach ($result as $row) {
                                    $batch_id = $row['id'];
                                    $batch_stock = floatval($row['stock']);
                                    $deduct = min($remaining_stock_to_issue, $batch_stock);
                                    $new_stock = $batch_stock - $deduct;

                                    $update_stmt = $conn->prepare("UPDATE purchased_items SET stock = ? WHERE id = ?");
                                    $update_stmt->execute([$new_stock, $batch_id]);

                                    $remaining_stock_to_issue -= $deduct;
                                    $actual_issued_from_stock += $deduct;
                                    error_log("Deducted $deduct from batch $batch_id, new stock=$new_stock");
                                }
                            }

                            // Deduct from buffer stock if needed
                            if ($remaining_stock_to_issue > 0 && $buffer_stock > 0) {
                                $deduct_buffer = min($remaining_stock_to_issue, $buffer_stock);
                                $new_buffer_stock = $buffer_stock - $deduct_buffer;
                                $stmt = $conn->prepare("UPDATE inventory SET buffer_stock = ? WHERE id = ?");
                                $stmt->execute([$new_buffer_stock, $item_id]);
                                $actual_issued_from_stock += $deduct_buffer;
                                $remaining_stock_to_issue -= $deduct_buffer;
                                error_log("Deducted $deduct_buffer from buffer stock, new buffer=$new_buffer_stock");
                            }

                            $new_issued_qty = floatval($order['issued_qty']) + $actual_issued_from_stock;
                            $remaining_for_order = floatval($order['requested_qty']) - $new_issued_qty;

                            if ($remaining_for_order <= 0) {
                                // Fully issued
                                $stmt = $conn->prepare("
                                    UPDATE staff_order_sheet
                                    SET status = 'issued', issued_qty = ?
                                    WHERE order_sheet_no = ? AND item_id = ?
                                ");
                                $stmt->execute([$new_issued_qty, $order_sheet_no, $item_id]);
                                error_log("Order $order_sheet_no: Fully issued - stock: $actual_issued_from_stock");
                            } else {
                                // Partially issued
                                $stmt = $conn->prepare("
                                    UPDATE staff_order_sheet
                                    SET issued_qty = ?, status = 'pending'
                                    WHERE order_sheet_no = ? AND item_id = ?
                                ");
                                $stmt->execute([$new_issued_qty, $order_sheet_no, $item_id]);

                                $partial_items[] = [
                                    'item_name' => $item['item_name'],
                                    'total_available' => $total_available_stock,
                                    'requested_qty' => floatval($order['requested_qty']),
                                    'issued_qty' => $actual_issued_from_stock,
                                    'remaining_qty' => $remaining_for_order,
                                    'order_sheet_no' => $order_sheet_no
                                ];
                                error_log("Order $order_sheet_no: Partially issued - stock: $actual_issued_from_stock, remaining: $remaining_for_order");
                            }

                            $issued_items[] = [
                                'item_id' => $item_id,
                                'item_name' => $item['item_name'],
                                'total_available' => $total_available_stock,
                                'requested_qty' => floatval($order['requested_qty']),
                                'issued_qty' => $actual_issued_from_stock,
                                'unit' => $item['unit']
                            ];
                        }
                    }

                    if (empty($issued_items)) {
                        throw new Exception("No items could be issued. All items may be fully fulfilled or insufficient stock available.");
                    }

                    $conn->commit();
                    $message = "Successfully issued " . count($issued_items) . " item(s) for Requesting Date: $requesting_date";
                    if (!empty($partial_items)) {
                        $partial_messages = [];
                        foreach ($partial_items as $item) {
                            $partial_messages[] = "{$item['item_name']}: Available {$item['total_available']}, Requested {$item['requested_qty']}, Issued {$item['issued_qty']}, Remaining {$item['remaining_qty']} (Order: {$item['order_sheet_no']})";
                        }
                        $message .= ". Partial issuances (awaiting stock refill): " . implode("; ", $partial_messages);
                    }
                    echo "<div class='success'>$message</div>";
                    $_SESSION['last_issued_order'] = [
                        'requesting_date' => $requesting_date,
                        'items' => $issued_items,
                        'partial_items' => $partial_items,
                        'departments' => array_keys($departments),
                        'manager_name' => $manager_name,
                        'order_sheets' => array_keys($order_sheets)
                    ];
                } catch (Exception $e) {
                    $conn->rollBack();
                    error_log("Error issuing items for Requesting Date: $requesting_date - " . $e->getMessage());
                    echo "<div class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                }
            }

            // Fetch pending orders
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
            unset($date_order);

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            echo "<div class='error'>Error fetching pending orders: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        ?>
        <div class="bg-white p-6 rounded-xl mb-8 shadow-md">
            <h3 class="text-xl font-semibold text-teal-900 mb-4">Select Requesting Date to Issue</h3>
            <?php if (empty($orders_by_date)): ?>
                <p class="text-gray-600 text-center">No pending or partially issued orders to issue.</p>
            <?php else: ?>
                <form method="POST" action="" id="select_date_form">
                    <div class="mb-4">
                        <label for="requesting_date" class="block text-sm font-medium text-gray-700 mb-2">Select Requesting Date</label>
                        <select name="requesting_date" id="requesting_date" onchange="showDateDetails(this.value)" class="block w-full max-w-md p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                            <option value="">-- Select Requesting Date --</option>
                            <?php foreach ($orders_by_date as $date => $order): ?>
                                <option value="<?php echo htmlspecialchars($date); ?>">
                                    Requesting Date: <?php echo htmlspecialchars($date); ?> (<?php echo htmlspecialchars(implode(', ', array_keys($order['departments']))); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        <div id="password_modal" class="password-modal">
            <div class="password-modal-content">
                <h3 class="text-lg font-semibold text-teal-900 mb-4">Verify Manager</h3>
                <form id="password_form" method="POST" action="">
                    <input type="hidden" name="requesting_date" id="password_requesting_date">
                    <div class="mb-4">
                        <label for="password_manager_id" class="block text-sm font-medium text-gray-700 mb-2">Select Manager</label>
                        <select name="manager_id" id="password_manager_id" class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 sm:text-sm" required>
                            <option value="">-- Select Manager --</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="password_input" class="block text-sm font-medium text-gray-700 mb-2">Enter Password</label>
                        <input type="password" name="password" id="password_input" class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 sm:text-sm" required>
                        <p id="password_error" class="error-text">Invalid password. Please try again.</p>
                    </div>
                    <div class="flex justify-between">
                        <button type="submit" name="issue_all" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700">Issue All</button>
                        <button type="button" onclick="closePasswordModal()" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="print_modal" class="print-modal">
            <div class="print-modal-content">
                <div class="receipt-container">
                    <div class="receipt-header">
                        <h2>Issued Staff Order Sheet</h2>
                        <div><strong>Requesting Date</strong><span class="colon">:</span><span id="print_requesting_date"></span></div>
                        <div><strong>Issued Date</strong><span class="colon">:</span><span id="print_date"></span></div>
                        <div><strong>Department</strong><span class="colon">:</span><span id="print_department"></span></div>
                    </div>
                    <div class="receipt-details">
                        <div><strong>Order Sheet No:</strong> <span id="print_order_sheets"></span></div>
                    </div>
                    <table class="receipt-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Req Qty</th>
                                <th>Issued Qty</th>
                                <th>Unit</th>
                            </tr>
                        </thead>
                        <tbody id="print_items"></tbody>
                    </table>
                    <div id="print_partial" class="no-print" style="display: none;">
                        <div><strong>Partial Issuance Note (Awaiting Stock Refill):</strong></div>
                        <div id="print_partial_details"></div>
                    </div>
                    <div class="signatures">
                        <div class="signature-box">
                            <div>Authorised By:</div>
                            <div><span id="print_manager"></span></div>
                            <div class="signature-line"></div>
                        </div>
                        <div class="signature-box">
                            <div>Storeman:</div>
                            <div class="user-greeting"><?php echo $username; ?></div>
                            <div class=" gewehrsignature-line"></div>
                        </div>
                    </div>
                    <div class="receipt-footer">
                        <div class='signature-box'>
                            <div>Received By:</div>
                            <div class='signature-line'></div>
                        </div>
                        <div>System Generated!</div>
                    </div>
                    <div class="controls no-print">
                        <button onclick="printReceipt()">Print</button>
                        <button onclick="closePrintModal()">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="order_modal" class="modal">
            <div class="modal-content">
                <h3 class="text-lg font-semibold text-teal-900 mb-4">Requesting Date Details</h3>
                <p class="text-gray-600 mb-2"><span class="font-medium">Requesting Date:</span> <span id="modal_requesting_date"></span></p>
                <p class="text-gray-600 mb-2"><span class="font-medium">Manager:</span> <span id="modal_manager"></span></p>
                <p class="text-gray-600 mb-2"><span class="font-medium">Department:</span> <span id="modal_department"></span></p>
                <p class="text-gray-600 mb-4"><span class="font-medium">Order Sheets:</span> <span id="modal_order_sheets"></span></p>
                <table class="min-w-full bg-white border border-gray-200 rounded-lg mb-6">
                    <thead class="bg-teal-100">
                        <tr>
                            <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Item Name</th>
                            <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Requested Qty</th>
                            <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Issued Qty</th>
                            <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Unit</th>
                            <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700 stock-column">Available Stock</th>
                            <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Needed Qty</th>
                        </tr>
                    </thead>
                    <tbody id="modal_items"></tbody>
                </table>
                <div class="flex justify-between no-print">
                    <button id="issue_all_button" onclick="showPasswordModalForAll()" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 hidden">Issue All</button>
                    <button id="close_modal" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400">Close</button>
                </div>
            </div>
        </div>
        <h3 class="text-xl font-semibold text-teal-900 mt-8 mb-4">Pending and Partially Issued Staff Orders</h3>
        <div id="pending_orders_container">
            <?php if (empty($orders_by_date)): ?>
                <p class="text-gray-600 text-center">No pending or partially issued orders.</p>
            <?php else: ?>
                <div class="table-container">
                    <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                        <thead class="bg-teal-100">
                            <tr>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Requesting Date</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Order Sheet No</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Department</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Items</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Requested Qty</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Issued Qty</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Needed Qty</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Units</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Request Date</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Manager</th>
                            </tr>
                        </thead>
                        <tbody id="pending_orders_table">
                            <?php
                            $index = 0;
                            foreach ($orders_by_date as $date => $date_order): ?>
                                <?php foreach ($date_order['order_sheets'] as $order): ?>
                                    <tr class="<?php echo $index++ % 2 ? 'bg-gray-50' : 'bg-white'; ?> hover:bg-teal-50">
                                        <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($date); ?></td>
                                        <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($order['order_sheet_no']); ?></td>
                                        <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($order['department'] ?? 'N/A'); ?></td>
                                        <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars(implode(', ', array_column($order['items'], 'item_name'))); ?></td>
                                        <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars(implode(', ', array_column($order['items'], 'requested_qty'))); ?></td>
                                        <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars(implode(', ', array_column($order['items'], 'issued_qty'))); ?></td>
                                        <td class="py-3 px-4 border-b text-sm"><?php
                                            $needed_quantities = array_map(function($item) {
                                                return max(0, (floatval($item['requested_qty']) - floatval($item['issued_qty'])));
                                            }, $order['items']);
                                            echo htmlspecialchars(implode(', ', $needed_quantities));
                                        ?></td>
                                        <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars(implode(', ', array_column($order['items'], 'unit'))); ?></td>
                                        <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($order['request_date']); ?></td>
                                        <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($order['manager_name'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <h3 class="text-xl font-semibold text-teal-900 mt-8 mb-4">Purchased Items Details</h3>
        <div class="table-container">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead class="bg-teal-100">
                    <tr>
                        <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Purchase ID</th>
                        <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Item Name</th>
                        <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Stock</th>
                        <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Unit</th>
                        <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Price</th>
                        <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Unit Price</th>
                        <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Purchased Date</th>
                        <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Expiry Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $stmt = $conn->prepare("
                            SELECT pi.id, i.item_name, pi.stock, i.unit, pi.price, pi.unit_price, pi.purchased_date, pi.expiry_date
                            FROM purchased_items pi
                            JOIN inventory i ON pi.item_id = i.id
                            ORDER BY pi.id DESC
                        ");
                        $stmt->execute();
                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($result as $row) {
                            echo "<tr class='hover:bg-teal-50'>
                                <td class='py-3 px-4 border-b text-sm'>" . htmlspecialchars($row['id']) . "</td>
                                <td class='py-3 px-4 border-b text-sm'>" . htmlspecialchars($row['item_name']) . "</td>
                                <td class='py-3 px-4 border-b text-sm'>" . number_format($row['stock'], 2) . "</td>
                                <td class='py-3 px-4 border-b text-sm'>" . htmlspecialchars($row['unit'] ?? 'Unit') . "</td>
                                <td class='py-3 px-4 border-b text-sm'>" . number_format($row['price'], 2) . "</td>
                                <td class='py-3 px-4 border-b text-sm'>" . number_format($row['unit_price'], 2) . "</td>
                                <td class='py-3 px-4 border-b text-sm'>" . htmlspecialchars($row['purchased_date']) . "</td>
                                <td class='py-3 px-4 border-b text-sm'>" . ($row['expiry_date'] ? htmlspecialchars($row['expiry_date']) : 'N/A') . "</td>
                            </tr>";
                        }
                    } catch (PDOException $e) {
                        error_log("Error fetching purchased items: " . $e->getMessage());
                        echo "<tr><td colspan='8' class='py-3 px-4 border-b text-sm text-center'>Error loading purchased items.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        let ordersByDate = <?php echo json_encode($orders_by_date); ?>;
        let lastFetchedOrders = JSON.stringify(ordersByDate);
        const orderModal = document.getElementById('order_modal');
        const printModal = document.getElementById('print_modal');
        const passwordModal = document.getElementById('password_modal');
        const closeModal = document.getElementById('close_modal');
        const pendingOrdersContainer = document.getElementById('pending_orders_container');
        const requestingDateSelect = document.getElementById('requesting_date');
        const pendingAlert = document.getElementById('pending-alert');
        const issueAllButton = document.getElementById('issue_all_button');
        const printError = document.getElementById('print-error');

        function updatePendingOrdersTable(orders) {
            console.log('Updating pending orders with:', orders);
            if (!orders || typeof orders !== 'object' || orders === null) {
                console.error('Invalid orders data received:', orders);
                pendingOrdersContainer.innerHTML = '<p class="text-gray-600 text-center">Error loading pending orders. Please check the server.</p>';
                pendingAlert.classList.add('hidden');
                requestingDateSelect.innerHTML = '<option value="">-- Select Requesting Date --</option>';
                return;
            }
            const newOrdersString = JSON.stringify(orders);
            if (newOrdersString === lastFetchedOrders) {
                console.log('No new orders to update.');
                return;
            }
            lastFetchedOrders = newOrdersString;
            ordersByDate = orders;
            if (Object.keys(orders).length === 0) {
                console.log('No pending orders found.');
                pendingOrdersContainer.innerHTML = '<p class="text-gray-600 text-center">No pending or partially issued orders.</p>';
                pendingAlert.classList.add('hidden');
                requestingDateSelect.innerHTML = '<option value="">-- Select Requesting Date --</option>';
                return;
            }
            pendingAlert.classList.remove('hidden');
            let tableHTML = `
                <div class="table-container">
                    <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                        <thead class="bg-teal-100">
                            <tr>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Requesting Date</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Order Sheet No</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Department</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Items</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Requested Qty</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Issued Qty</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Needed Qty</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Units</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Request Date</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Manager</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            let index = 0;
            Object.values(orders).forEach(date_order => {
                Object.values(date_order.order_sheets).forEach(order => {
                    const itemNames = order.items.map(item => item.item_name);
                    const requestedQuantities = order.items.map(item => item.requested_qty);
                    const issuedQuantities = order.items.map(item => item.issued_qty);
                    const neededQuantities = order.items.map(item => Math.max(0, (item.requested_qty - item.issued_qty)));
                    const units = order.items.map(item => item.unit);
                    tableHTML += `
                        <tr class="${index % 2 ? 'bg-gray-50' : 'bg-white'} hover:bg-teal-50">
                            <td class="py-3 px-4 border-b text-sm">${date_order.requesting_date}</td>
                            <td class="py-3 px-4 border-b text-sm">${order.order_sheet_no}</td>
                            <td class="py-3 px-4 border-b text-sm">${order.department || 'N/A'}</td>
                            <td class="py-3 px-4 border-b text-sm">${itemNames.join(', ')}</td>
                            <td class="py-3 px-4 border-b text-sm">${requestedQuantities.join(', ')}</td>
                            <td class="py-3 px-4 border-b text-sm">${issuedQuantities.join(', ')}</td>
                            <td class="py-3 px-4 border-b text-sm">${neededQuantities.join(', ')}</td>
                            <td class="py-3 px-4 border-b text-sm">${units.join(', ')}</td>
                            <td class="py-3 px-4 border-b text-sm">${order.request_date}</td>
                            <td class="py-3 px-4 border-b text-sm">${order.manager_name || 'N/A'}</td>
                        </tr>
                    `;
                    index++;
                });
            });
            tableHTML += `
                        </tbody>
                    </table>
                </div>
            `;
            pendingOrdersContainer.innerHTML = tableHTML;
            requestingDateSelect.innerHTML = '<option value="">-- Select Requesting Date --</option>';
            Object.values(orders).forEach(date_order => {
                const option = document.createElement('option');
                option.value = date_order.requesting_date;
                option.textContent = `Requesting Date: ${date_order.requesting_date} (${date_order.departments.join(', ')})`;
                requestingDateSelect.appendChild(option);
            });
            console.log('Pending orders table updated.');
        }

        function fetchPendingOrders() {
            console.log('Fetching pending orders...');
            const timestamp = new Date().getTime();
            fetch(`fetch_pending_staff_orders.php?_=${timestamp}`, {
                cache: 'no-store',
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Network response was not ok: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Fetched pending orders:', data);
                    if (data === null || data === undefined) {
                        throw new Error('Received null or undefined data from server');
                    }
                    updatePendingOrdersTable(data);
                })
                .catch(error => {
                    console.error('Error fetching pending orders:', error);
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'error';
                    errorDiv.textContent = 'Error fetching pending orders: ' + error.message;
                    pendingOrdersContainer.prepend(errorDiv);
                });
        }

        function showDateDetails(requestingDate) {
            console.log('showDateDetails called with requestingDate:', requestingDate);
            console.log('Current ordersByDate:', ordersByDate);
            if (!requestingDate) {
                console.log('No requesting date selected, hiding modal.');
                orderModal.style.display = 'none';
                return;
            }
            const dateOrder = ordersByDate[requestingDate];
            if (!dateOrder) {
                console.error(`Order not found for requesting_date: ${requestingDate}`);
                document.getElementById('modal_items').innerHTML = '<tr><td colspan="6" class="py-3 px-4 border-b text-sm text-center">No order details found.</td></tr>';
                document.getElementById('modal_requesting_date').textContent = requestingDate;
                document.getElementById('modal_manager').textContent = 'None';
                document.getElementById('modal_department').textContent = 'None';
                document.getElementById('modal_order_sheets').textContent = 'None';
                orderModal.style.display = 'flex';
                issueAllButton.classList.add('hidden');
                return;
            }
            console.log(`Showing details for requesting_date: ${requestingDate}`, dateOrder);
            document.getElementById('modal_requesting_date').textContent = dateOrder.requesting_date || 'Unknown';
            document.getElementById('modal_manager').textContent = dateOrder.manager_names && dateOrder.manager_names.length > 0 ? dateOrder.manager_names.join(', ') : 'None';
            document.getElementById('modal_department').textContent = dateOrder.departments && dateOrder.departments.length > 0 ? dateOrder.departments.join(', ') : 'None';
            document.getElementById('modal_order_sheets').textContent = dateOrder.order_sheet_numbers && dateOrder.order_sheet_numbers.length > 0 ? dateOrder.order_sheet_numbers.join(', ') : 'None';
            const modalItems = document.getElementById('modal_items');
            modalItems.innerHTML = '';
            let hasPendingItems = false;
            if (!dateOrder.items_by_id || dateOrder.items_by_id.length === 0) {
                console.log('No items found for this requesting date.');
                modalItems.innerHTML = '<tr><td colspan="6" class="py-3 px-4 border-b text-sm text-center">No items found.</td></tr>';
                orderModal.style.display = 'flex';
                issueAllButton.classList.add('hidden');
                return;
            }
            dateOrder.items_by_id.forEach(item => {
                console.log(`Processing item: ${item.item_name}, item_id: ${item.item_id}, requested_qty: ${item.requested_qty}, issued_qty: ${item.issued_qty}`);
                fetch(`check_stock.php?item_id=${item.item_id}&_=${new Date().getTime()}`, { cache: 'no-store' })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(stockData => {
                        const row = document.createElement('tr');
                        const totalAvailable = (stockData.available_stock || 0) + (stockData.buffer_stock || 0);
                        const neededQty = Math.max(0, (item.requested_qty - item.issued_qty));
                        const isInsufficient = neededQty > 0 && totalAvailable < neededQty;
                        if (isInsufficient) {
                            row.className = 'out-of-stock';
                        }
                        if (neededQty > 0) hasPendingItems = true;
                        row.innerHTML = `
                            <td class="py-3 px-4 border-b text-sm">${item.item_name}</td>
                            <td class="py-3 px-4 border-b text-sm">${item.requested_qty}</td>
                            <td class="py-3 px-4 border-b text-sm">${item.issued_qty}</td>
                            <td class="py-3 px-4 border-b text-sm">${item.unit}</td>
                            <td class="py-3 px-4 border-b text-sm stock-column">${totalAvailable}</td>
                            <td class="py-3 px-4 border-b text-sm">${neededQty}</td>
                        `;
                        modalItems.appendChild(row);
                        issueAllButton.classList.toggle('hidden', !hasPendingItems);
                        orderModal.style.display = 'flex';
                    })
                    .catch(error => {
                        console.error(`Error fetching stock for item_id: ${item.item_id}`, error);
                        const row = document.createElement('tr');
                        const neededQty = Math.max(0, (item.requested_qty - item.issued_qty));
                        if (neededQty > 0) hasPendingItems = true;
                        row.innerHTML = `
                            <td class="py-3 px-4 border-b text-sm">${item.item_name}</td>
                            <td class="py-3 px-4 border-b text-sm">${item.requested_qty}</td>
                            <td class="py-3 px-4 border-b text-sm">${item.issued_qty}</td>
                            <td class="py-3 px-4 border-b text-sm">${item.unit}</td>
                            <td class="py-3 px-4 border-b text-sm stock-column">Error</td>
                            <td class="py-3 px-4 border-b text-sm">${neededQty}</td>
                        `;
                        modalItems.appendChild(row);
                        issueAllButton.classList.toggle('hidden', !hasPendingItems);
                        orderModal.style.display = 'flex';
                    });
            });
        }

        function showPasswordModalForAll() {
            const requestingDate = document.getElementById('modal_requesting_date').textContent;
            const dateOrder = ordersByDate[requestingDate];
            if (!dateOrder) {
                console.error(`No order data found for requesting_date: ${requestingDate}`);
                return;
            }
            document.getElementById('password_requesting_date').value = requestingDate;
            const managerSelect = document.getElementById('password_manager_id');
            managerSelect.innerHTML = '<option value="">-- Select Manager --</option>';
            fetch(`fetch_managers.php?_=${new Date().getTime()}`, { cache: 'no-store' })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        console.error('Error fetching managers:', data.error);
                        document.getElementById('password_error').textContent = data.error;
                        document.getElementById('password_error').style.display = 'block';
                        return;
                    }
                    data.forEach(manager => {
                        const option = document.createElement('option');
                        option.value = manager.id;
                        option.textContent = manager.username;
                        managerSelect.appendChild(option);
                    });
                    document.getElementById('password_input').value = '';
                    document.getElementById('password_error').style.display = 'none';
                    passwordModal.style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error fetching managers:', error);
                    document.getElementById('password_error').textContent = 'Failed to load managers. Please try again.';
                    document.getElementById('password_error').style.display = 'block';
                    passwordModal.style.display = 'flex';
                });
        }

        function closePasswordModal() {
            passwordModal.style.display = 'none';
        }

        function closePrintModal() {
            printModal.style.display = 'none';
            fetch('clear_session.php', { cache: 'no-store' })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        console.log('Session cleared');
                        window.location.href = '../stores.php';
                    }
                })
                .catch(error => {
                    console.error('Error clearing session:', error);
                    window.location.href = '../stores.php';
                });
        }

        function showPrintModal(order) {
            document.getElementById('print_requesting_date').textContent = order.requesting_date || '-';
            document.getElementById('print_department').textContent = order.departments && order.departments.length > 0 ? order.departments.join(', ') : '-';
            document.getElementById('print_manager').textContent = order.manager_name || '-';
            document.getElementById('print_order_sheets').textContent = order.order_sheets && order.order_sheets.length > 0 ? order.order_sheets.join(', ') : '-';
            document.getElementById('print_date').textContent = new Date().toLocaleString();
            const printItems = document.getElementById('print_items');
            printItems.innerHTML = '';
            if (order.items && order.items.length > 0) {
                order.items.forEach(item => {
                    const tr = document.createElement('tr');
                    const issuedQty = item.issued_qty;
                    tr.innerHTML = `
                        <td>${item.item_name}</td>
                        <td>${item.requested_qty}</td>
                        <td>${issuedQty}</td>
                        <td>${item.unit}</td>
                    `;
                    printItems.appendChild(tr);
                });
            }
            const printPartial = document.getElementById('print_partial');
            const printPartialDetails = document.getElementById('print_partial_details');
            if (order.partial_items && order.partial_items.length > 0) {
                printPartial.style.display = 'block';
                printPartialDetails.innerHTML = order.partial_items.map(item => {
                    return `<div>${item.item_name}: Requested ${item.requested_qty}, Issued ${item.issued_qty}, Remaining ${item.remaining_qty} (Order: ${item.order_sheet_no})</div>`;
                }).join('');
            } else {
                printPartial.style.display = 'none';
                printPartialDetails.innerHTML = '';
            }
            printModal.style.display = 'flex';
            printError.classList.add('hidden');
        }

        function printReceipt() {
            window.print();
        }

        closeModal.addEventListener('click', () => {
            console.log('Close button clicked');
            fetch('clear_session.php', { cache: 'no-store' })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        console.log('Session cleared');
                        window.location.href = '../stores.php';
                    }
                })
                .catch(error => {
                    console.error('Error clearing session:', error);
                    window.location.href = '../stores.php';
                });
        });

        orderModal.addEventListener('click', (e) => {
            if (e.target === orderModal) {
                fetch('clear_session.php', { cache: 'no-store' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            console.log('Session cleared');
                            window.location.href = '../stores.php';
                        }
                    })
                    .catch(error => {
                        console.error('Error clearing session:', error);
                        window.location.href = '../stores.php';
                    });
            }
        });

        passwordModal.addEventListener('click', (e) => {
            if (e.target === passwordModal) {
                closePasswordModal();
            }
        });

        printModal.addEventListener('click', (e) => {
            if (e.target === printModal) {
                closePrintModal();
            }
        });

        setInterval(fetchPendingOrders, 10000);
        fetchPendingOrders();

        <?php if (isset($_SESSION['last_issued_order'])): ?>
            showPrintModal(<?php echo json_encode($_SESSION['last_issued_order']); ?>);
            <?php unset($_SESSION['last_issued_order']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
<?php
ob_end_flush();
?>