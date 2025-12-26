<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../stores_login.php");
    exit();
}
$username = htmlspecialchars($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Issue</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../assets/js/qz-tray.js"></script>
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
        }
        .print-modal-content {
            width: 80mm;
            margin: 0;
            padding: 0;
            background: #fff;
            font-family: 'Arial', sans-serif;
            font-size: 9pt;
            line-height: 1.5;
            color: #000;
            box-sizing: border-box;
        }
        .receipt-container {
            width: 80mm;
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
            width: 80mm;
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
        .receipt-table th:nth-child(2) { width: 15%; }
        .receipt-table th:nth-child(3) { width: 20%; }
        .receipt-table th:nth-child(4) { width: 15%; }
        .receipt-table th:nth-child(5) { width: 20%; }
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
        .receipt-table td:nth-child(2) { white-space: nowrap; }
        .receipt-table td:nth-child(3) { white-space: nowrap; }
        .receipt-table td:nth-child(4) { white-space: nowrap; }
        .receipt-table td:nth-child(5) { white-space: nowrap; }
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
            margin: 0 0 5mm 0;
            padding-top: 3mm;
            border-top: 2px dashed #000;
        }
        .controls button {
            padding: 2mm 4mm;
            margin: 0 2mm;
            font-size: 9pt;
            cursor: pointer;
            background: #f0f0f0;
            border: 1px solid #000;
            font-weight: bold;
        }
        .no-print {
            display: block;
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
                margin: 0;
                padding: 0;
                background: #fff;
                box-sizing: border-box;
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
            .receipt-table th, .receipt-table td {
                width: inherit;
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
        <h2 class="text-4xl font-bold text-teal-900 mb-10 text-center">Issue Stock for HGG/R Orders</h2>

        <div id="pending-alert" class="alert-blink hidden">New Pending or Partially Issued Orders Available!</div>
        <div id="print-error" class="error hidden">Failed to print. Please check printer connection and try again.</div>

        <?php
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        ini_set('log_errors', 1);
        ini_set('error_log', 'F:/xampp/htdocs/HMS/stores/php_errors.log');

        $conn = new mysqli('localhost', 'hotelgrandguardi_root', 'Sun123flower@', 'hotelgrandguardi_wedding_bliss');
        if ($conn->connect_error) {
            error_log("Connection failed: " . $conn->connect_error);
            die("<div class='error'>Connection failed: " . htmlspecialchars($conn->connect_error) . "</div>");
        }

        function fulfillPendingOrders($conn, $item_id, $today) {
            $item_unload_qty_result = $conn->query("
                SELECT COALESCE(SUM(remaining_qty), 0) as unload_qty
                FROM hggfunction_unload
                WHERE item_id = $item_id
            ");
            $item_unload_qty_row = $item_unload_qty_result->fetch_assoc();
            $unload_qty = (int)$item_unload_qty_row['unload_qty'];

            $stmt = $conn->prepare("
                SELECT order_sheet_no, requested_qty, issued_qty
                FROM hggorder_sheet
                WHERE item_id = ? AND status = 'pending'
                ORDER BY request_date ASC
            ");
            $stmt->bind_param("i", $item_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $pending_orders = [];
            while ($row = $result->fetch_assoc()) {
                $pending_orders[] = [
                    'order_sheet_no' => $row['order_sheet_no'],
                    'requested_qty' => (int)$row['requested_qty'],
                    'issued_qty' => (int)$row['issued_qty']
                ];
            }
            $stmt->close();

            if (empty($pending_orders)) return;

            // Allocate unload_qty across pending orders
            $allocated_unload = [];
            $remaining_unload = $unload_qty;
            foreach ($pending_orders as $key => $order) {
                $order_balance = $order['requested_qty'] - $order['issued_qty'];
                $alloc_unl = min($order_balance, $remaining_unload);
                $allocated_unload[$key] = $alloc_unl;
                $remaining_unload -= $alloc_unl;
            }

            // Calculate used_unload initially as sum of allocated
            $used_unload = $unload_qty - $remaining_unload;

            // Now process each order
            foreach ($pending_orders as $key => $order) {
                $order_sheet_no = $order['order_sheet_no'];
                $order_balance = $order['requested_qty'] - $order['issued_qty'];
                $alloc_unl = $allocated_unload[$key];
                $effective_needed = $order_balance - $alloc_unl;

                if ($effective_needed <= 0) {
                    // Fulfilled by unload/pre-existing issued
                    $stmt = $conn->prepare("
                        UPDATE hggorder_sheet 
                        SET status = 'issued'
                        WHERE order_sheet_no = ? AND item_id = ?
                    ");
                    $stmt->bind_param("ii", $order_sheet_no, $item_id);
                    $stmt->execute();
                    $stmt->close();
                    continue;
                }

                // Get available stock
                $stmt = $conn->prepare("
                    SELECT SUM(pi.stock) as total_stock
                    FROM purchased_items pi
                    WHERE pi.item_id = ? AND pi.stock > 0 AND (pi.expiry_date IS NULL OR pi.expiry_date >= ?)
                ");
                $stmt->bind_param("is", $item_id, $today);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $regular_stock = intval($row['total_stock'] ?? 0);
                $stmt->close();

                $stmt = $conn->prepare("SELECT buffer_stock FROM inventory WHERE id = ?");
                $stmt->bind_param("i", $item_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $buffer_stock = intval($row['buffer_stock'] ?? 0);
                $stmt->close();

                $total_available = $regular_stock + $buffer_stock;
                if ($total_available <= 0) continue;

                $issue_qty = min($effective_needed, $total_available);
                $remaining = $issue_qty;

                // Deduct from regular stock batches
                if ($remaining > 0 && $regular_stock > 0) {
                    $stmt = $conn->prepare("
                        SELECT id, stock
                        FROM purchased_items
                        WHERE item_id = ? AND stock > 0 AND (expiry_date IS NULL OR expiry_date >= ?)
                        ORDER BY COALESCE(expiry_date, '9999-12-31') ASC, purchased_date ASC
                    ");
                    $stmt->bind_param("is", $item_id, $today);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    while ($remaining > 0 && $row = $result->fetch_assoc()) {
                        $batch_id = $row['id'];
                        $batch_stock = $row['stock'];
                        $deduct = min($remaining, $batch_stock);
                        $new_stock = $batch_stock - $deduct;

                        $update_stmt = $conn->prepare("UPDATE purchased_items SET stock = ? WHERE id = ?");
                        $update_stmt->bind_param("ii", $new_stock, $batch_id);
                        $update_stmt->execute();
                        $update_stmt->close();
                        $remaining -= $deduct;
                    }
                    $stmt->close();
                }

                // Deduct from buffer stock
                if ($remaining > 0 && $buffer_stock > 0) {
                    $deduct_buffer = min($remaining, $buffer_stock);
                    $new_buffer_stock = $buffer_stock - $deduct_buffer;
                    $stmt = $conn->prepare("UPDATE inventory SET buffer_stock = ? WHERE id = ?");
                    $stmt->bind_param("ii", $new_buffer_stock, $item_id);
                    $stmt->execute();
                    $stmt->close();
                    $remaining -= $deduct_buffer;
                }

                $new_issued_qty = $order['issued_qty'] + $issue_qty;

                if ($new_issued_qty + $alloc_unl >= $order['requested_qty']) {
                    $stmt = $conn->prepare("
                        UPDATE hggorder_sheet 
                        SET status = 'issued', issued_qty = ? 
                        WHERE order_sheet_no = ? AND item_id = ?
                    ");
                    $stmt->bind_param("iii", $new_issued_qty, $order_sheet_no, $item_id);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    $stmt = $conn->prepare("
                        UPDATE hggorder_sheet 
                        SET issued_qty = ? 
                        WHERE order_sheet_no = ? AND item_id = ?
                    ");
                    $stmt->bind_param("iii", $new_issued_qty, $order_sheet_no, $item_id);
                    $stmt->execute();
                    $stmt->close();

                    // Create new partial order
                    $remaining_qty = $order['requested_qty'] - ($new_issued_qty + $alloc_unl);
                    $stmt = $conn->prepare("
                        INSERT INTO hggorder_sheet (order_sheet_no, item_id, requested_qty, issued_qty, status, responsible_id, request_date, function_type, function_date, day_night)
                        SELECT CONCAT(order_sheet_no, '_P', UNIX_TIMESTAMP()), item_id, ?, 0, 'pending', responsible_id, request_date, function_type, function_date, day_night
                        FROM hggorder_sheet
                        WHERE order_sheet_no = ? AND item_id = ?
                    ");
                    $stmt->bind_param("iii", $remaining_qty, $order_sheet_no, $item_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            // Handle excess unload by adding to the last order's issued_qty
            if ($remaining_unload > 0 && !empty($pending_orders)) {
                $last_order_sheet_no = $pending_orders[count($pending_orders) - 1]['order_sheet_no'];
                $stmt = $conn->prepare("
                    UPDATE hggorder_sheet 
                    SET issued_qty = issued_qty + ? 
                    WHERE order_sheet_no = ? AND item_id = ?
                ");
                $stmt->bind_param("iii", $remaining_unload, $last_order_sheet_no, $item_id);
                $stmt->execute();
                $stmt->close();
                $used_unload += $remaining_unload;
            }

            // Deduct used_unload from function_unload batches
            if ($used_unload > 0) {
                $stmt = $conn->prepare("
                    SELECT id, remaining_qty
                    FROM hggfunction_unload
                    WHERE item_id = ? AND remaining_qty > 0
                    ORDER BY id ASC
                ");
                $stmt->bind_param("i", $item_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $remaining_deduct = $used_unload;
                while ($remaining_deduct > 0 && $row = $result->fetch_assoc()) {
                    $unload_id = $row['id'];
                    $current_qty = $row['remaining_qty'];
                    $deduct = min($remaining_deduct, $current_qty);
                    $new_qty = $current_qty - $deduct;
                    $update_stmt = $conn->prepare("UPDATE hggfunction_unload SET remaining_qty = ? WHERE id = ?");
                    $update_stmt->bind_param("ii", $new_qty, $unload_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                    $remaining_deduct -= $deduct;
                }
                $stmt->close();
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issue_all'])) {
    $function_date = $_POST['function_date'];
    $responsible_id = intval($_POST['responsible_id']);
    $password = $_POST['password'];
    $today = date('Y-m-d');

    $conn->begin_transaction();
    try {
        // Verify responsible person's password
        $stmt = $conn->prepare("SELECT password, name FROM responsibilities WHERE id = ?");
        $stmt->bind_param("i", $responsible_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stored_password = $row['password'] ?? '';
        $responsible_name = $row['name'] ?? 'N/A';
        $stmt->close();

        if (!password_verify($password, $stored_password)) {
            throw new Exception("Invalid password for responsible person.");
        }

        $item_unload_quantities = [];
        $unload_result = $conn->query("
            SELECT item_id, COALESCE(SUM(remaining_qty), 0) as unload_qty
            FROM hggfunction_unload
            GROUP BY item_id
        ");
        if ($unload_result) {
            while ($unload_row = $unload_result->fetch_assoc()) {
                $item_unload_quantities[$unload_row['item_id']] = (int)$unload_row['unload_qty'];
            }
        } else {
            error_log("Unload query failed: " . $conn->error);
        }

        $stmt = $conn->prepare("
            SELECT os.order_sheet_no, os.item_id, os.requested_qty, os.issued_qty, i.item_name, i.unit, os.function_type, os.function_date, os.day_night
            FROM hggorder_sheet os
            JOIN inventory i ON os.item_id = i.id
            WHERE os.function_date = ? AND os.status = 'pending'
            ORDER BY os.order_sheet_no ASC
        ");
        $stmt->bind_param("s", $function_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $items_by_id = [];
        $order_sheets = [];
        $function_types = [];
        $day_nights = [];
        while ($row = $result->fetch_assoc()) {
            $item_id = $row['item_id'];
            $order_sheet_no = $row['order_sheet_no'];
            $unload_qty = isset($item_unload_quantities[$item_id]) ? $item_unload_quantities[$item_id] : 0;
            if (!isset($items_by_id[$item_id])) {
                $items_by_id[$item_id] = [
                    'item_id' => $item_id,
                    'item_name' => $row['item_name'],
                    'requested_qty' => 0,
                    'issued_qty' => 0,
                    'unload_qty' => $unload_qty,
                    'unit' => $row['unit'] ?? 'Unit',
                    'order_sheets' => []
                ];
            }
            $items_by_id[$item_id]['requested_qty'] += $row['requested_qty'];
            $items_by_id[$item_id]['issued_qty'] += $row['issued_qty'];
            $items_by_id[$item_id]['order_sheets'][] = [
                'order_sheet_no' => $order_sheet_no,
                'requested_qty' => (int)$row['requested_qty'],
                'issued_qty' => (int)$row['issued_qty']
            ];
            $order_sheets[$order_sheet_no] = true;
            $function_types[$row['function_type'] ?? 'N/A'] = true;
            $day_nights[$row['day_night'] ?? 'N/A'] = true;
        }
        $stmt->close();

        if (empty($items_by_id)) {
            throw new Exception("No pending items found for Function Date: $function_date");
        }

        $issued_items = [];
        $partial_items = [];
        foreach ($items_by_id as $item_id => $item) {
            $unload_qty = $item['unload_qty'];

            // Sort order_sheets by order_sheet_no
            usort($item['order_sheets'], function($a, $b) {
                return strcmp($a['order_sheet_no'], $b['order_sheet_no']);
            });
            $items_by_id[$item_id]['order_sheets'] = $item['order_sheets'];

            // Allocate unload_qty across orders
            $allocated_unload = [];
            $remaining_unload = $unload_qty;
            foreach ($item['order_sheets'] as $key => $order) {
                $order_balance = $order['requested_qty'] - $order['issued_qty'];
                $alloc_unl = min($order_balance, $remaining_unload);
                $allocated_unload[$key] = $alloc_unl;
                $remaining_unload -= $alloc_unl;
            }

            $used_unload = $unload_qty - $remaining_unload;

            // Calculate total needed from stock
            $needed_qty = 0;
            foreach ($item['order_sheets'] as $key => $order) {
                $order_balance = $order['requested_qty'] - $order['issued_qty'];
                $alloc_unl = $allocated_unload[$key];
                $effective_needed = max(0, $order_balance - $alloc_unl);
                $needed_qty += $effective_needed;
            }

            // Get available stock
            $stmt = $conn->prepare("
                SELECT SUM(pi.stock) as total_stock
                FROM purchased_items pi
                WHERE pi.item_id = ? AND pi.stock > 0 AND (pi.expiry_date IS NULL OR pi.expiry_date >= ?)
            ");
            $stmt->bind_param("is", $item_id, $today);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $regular_stock = intval($row['total_stock'] ?? 0);
            $stmt->close();

            $stmt = $conn->prepare("SELECT buffer_stock FROM inventory WHERE id = ?");
            $stmt->bind_param("i", $item_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $buffer_stock = intval($row['buffer_stock'] ?? 0);
            $stmt->close();

            $total_available = $regular_stock + $buffer_stock;

            // Determine issued_qty based on unload_qty and requested_qty
            $total_requested = $item['requested_qty'];
            $total_issued_from_stock = 0;

            if ($unload_qty >= $total_requested) {
                // Unload quantity is sufficient; no stock deduction needed
                $total_issued_from_stock = 0;
                $item_issued_qty = $unload_qty - $total_requested; // Excess unload as issued
            } else {
                // Unload quantity is insufficient; issue from stock
                $issue_qty = min($needed_qty, $total_available);
                $total_issued_from_stock = $issue_qty;
                $item_issued_qty = $issue_qty; // Issued from stock only
                $remaining = $issue_qty;

                // Deduct from regular stock batches
                if ($remaining > 0 && $regular_stock > 0) {
                    $stmt = $conn->prepare("
                        SELECT id, stock
                        FROM purchased_items
                        WHERE item_id = ? AND stock > 0 AND (expiry_date IS NULL OR expiry_date >= ?)
                        ORDER BY COALESCE(expiry_date, '9999-12-31') ASC, purchased_date ASC
                    ");
                    $stmt->bind_param("is", $item_id, $today);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    while ($remaining > 0 && $row = $result->fetch_assoc()) {
                        $batch_id = $row['id'];
                        $batch_stock = $row['stock'];
                        $deduct = min($remaining, $batch_stock);
                        $new_stock = $batch_stock - $deduct;

                        $update_stmt = $conn->prepare("UPDATE purchased_items SET stock = ? WHERE id = ?");
                        $update_stmt->bind_param("ii", $new_stock, $batch_id);
                        $update_stmt->execute();
                        $update_stmt->close();
                        $remaining -= $deduct;
                    }
                    $stmt->close();
                }

                // Deduct from buffer stock
                if ($remaining > 0 && $buffer_stock > 0) {
                    $deduct_buffer = min($remaining, $buffer_stock);
                    $new_buffer_stock = $buffer_stock - $deduct_buffer;
                    $stmt = $conn->prepare("UPDATE inventory SET buffer_stock = ? WHERE id = ?");
                    $stmt->bind_param("ii", $new_buffer_stock, $item_id);
                    $stmt->execute();
                    $stmt->close();
                    $remaining -= $deduct_buffer;
                }
            }

            // Update order sheets
            $remaining_issue = $total_issued_from_stock;
            foreach ($item['order_sheets'] as $key => $order) {
                $order_sheet_no = $order['order_sheet_no'];
                $order_balance = $order['requested_qty'] - $order['issued_qty'];
                $alloc_unl = $allocated_unload[$key];
                $effective_needed = max(0, $order_balance - $alloc_unl);

                if ($effective_needed <= 0) {
                    // Order fulfilled by unload_qty
                    $stmt = $conn->prepare("
                        UPDATE hggorder_sheet 
                        SET status = 'issued', issued_qty = ?
                        WHERE order_sheet_no = ? AND item_id = ?
                    ");
                    $new_issued_qty = $order['issued_qty'];
                    $stmt->bind_param("iii", $new_issued_qty, $order_sheet_no, $item_id);
                    $stmt->execute();
                    $stmt->close();
                    continue;
                }

                $order_issue_qty = min($effective_needed, $remaining_issue);
                $remaining_issue -= $order_issue_qty;
                $new_issued_qty = $order['issued_qty'] + $order_issue_qty;

                if ($new_issued_qty + $alloc_unl >= $order['requested_qty']) {
                    $stmt = $conn->prepare("
                        UPDATE hggorder_sheet 
                        SET status = 'issued', issued_qty = ? 
                        WHERE order_sheet_no = ? AND item_id = ?
                    ");
                    $stmt->bind_param("iii", $new_issued_qty, $order_sheet_no, $item_id);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    $stmt = $conn->prepare("
                        UPDATE hggorder_sheet 
                        SET issued_qty = ? 
                        WHERE order_sheet_no = ? AND item_id = ?
                    ");
                    $stmt->bind_param("iii", $new_issued_qty, $order_sheet_no, $item_id);
                    $stmt->execute();
                    $stmt->close();

                    $remaining_qty = $order['requested_qty'] - ($new_issued_qty + $alloc_unl);
                    $partial_items[] = [
                        'item_name' => $item['item_name'],
                        'total_available' => $total_available,
                        'requested_qty' => $order['requested_qty'],
                        'issued_qty' => $order_issue_qty,
                        'unload_qty' => $alloc_unl,
                        'remaining_qty' => $remaining_qty
                    ];

                    $stmt = $conn->prepare("
                        INSERT INTO hggorder_sheet (order_sheet_no, item_id, requested_qty, issued_qty, status, responsible_id, request_date, function_type, function_date, day_night)
                        SELECT CONCAT(?, '_P', UNIX_TIMESTAMP()), item_id, ?, 0, 'pending', responsible_id, request_date, function_type, function_date, day_night
                        FROM hggorder_sheet
                        WHERE order_sheet_no = ? AND item_id = ?
                    ");
                    $stmt->bind_param("siii", $order_sheet_no, $remaining_qty, $order_sheet_no, $item_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            // Handle excess unload by adding to the last order's issued_qty
            if ($remaining_unload > 0 && !empty($item['order_sheets'])) {
                $last_key = count($item['order_sheets']) - 1;
                $last_order_sheet_no = $item['order_sheets'][$last_key]['order_sheet_no'];
                $stmt = $conn->prepare("
                    UPDATE hggorder_sheet 
                    SET issued_qty = issued_qty + ? 
                    WHERE order_sheet_no = ? AND item_id = ?
                ");
                $stmt->bind_param("isi", $remaining_unload, $last_order_sheet_no, $item_id);
                $stmt->execute();
                $stmt->close();
                $used_unload += $remaining_unload;
            }

            // Deduct used_unload from function_unload batches
            if ($used_unload > 0) {
                $stmt = $conn->prepare("
                    SELECT id, remaining_qty
                    FROM hggfunction_unload
                    WHERE item_id = ? AND remaining_qty > 0
                    ORDER BY id ASC
                ");
                $stmt->bind_param("i", $item_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $remaining_deduct = $used_unload;
                while ($remaining_deduct > 0 && $row = $result->fetch_assoc()) {
                    $unload_id = $row['id'];
                    $current_qty = $row['remaining_qty'];
                    $deduct = min($remaining_deduct, $current_qty);
                    $new_qty = $current_qty - $deduct;
                    $update_stmt = $conn->prepare("UPDATE hggfunction_unload SET remaining_qty = ? WHERE id = ?");
                    $update_stmt->bind_param("ii", $new_qty, $unload_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                    $remaining_deduct -= $deduct;
                }
                $stmt->close();
            }

            if ($item_issued_qty > 0 || $unload_qty > 0) {
                $issued_items[] = [
                    'item_id' => $item_id,
                    'item_name' => $item['item_name'],
                    'total_available' => $total_available,
                    'requested_qty' => $item['requested_qty'],
                    'issued_qty' => $item_issued_qty, // Reflects excess unload or stock issued
                    'unload_qty' => $unload_qty,
                    'unit' => $item['unit']
                ];
            }
        }

        if (empty($issued_items)) {
            throw new Exception("No items could be issued due to insufficient stock or sufficient unload quantities.");
        }

        $conn->commit();
        $message = "Successfully issued " . count($issued_items) . " item(s) for Function Date: $function_date";
        if (!empty($partial_items)) {
            $partial_messages = [];
            foreach ($partial_items as $item) {
                $partial_messages[] = "{$item['item_name']}: Available {$item['total_available']}, Requested {$item['requested_qty']}, Issued {$item['issued_qty']}, Unload {$item['unload_qty']}, Pending {$item['remaining_qty']}";
            }
            $message .= ". Partial issuances: " . implode("; ", $partial_messages);
        }
        echo "<div class='success'>$message</div>";

        $_SESSION['last_issued_order'] = [
            'function_date' => $function_date,
            'items' => $issued_items,
            'partial_items' => $partial_items,
            'function_types' => array_keys($function_types),
            'day_nights' => array_keys($day_nights),
            'responsible_name' => $responsible_name,
            'order_sheets' => array_keys($order_sheets)
        ];
    } catch (Exception $e) {
        $conn->rollback();
        echo "<div class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        error_log("Error issuing items for Function Date: $function_date - " . $e->getMessage());
    }
}
        
        $result = $conn->query("
            SELECT DISTINCT os.function_date, os.order_sheet_no, os.item_id, i.item_name, 
                   os.requested_qty, os.issued_qty, i.unit, 
                   os.request_date, r.name as responsible_name, r.id as responsible_id, 
                   os.status, os.function_type, os.day_night
            FROM hggorder_sheet os
            JOIN inventory i ON os.item_id = i.id
            LEFT JOIN responsible r ON os.responsible_id = r.id
            WHERE os.status = 'pending'
               OR os.order_sheet_no IN (
                   SELECT DISTINCT order_sheet_no 
                   FROM hggorder_sheet 
                   WHERE status = 'issued' 
                   AND order_sheet_no IN (
                       SELECT order_sheet_no 
                       FROM hggorder_sheet 
                       WHERE status = 'pending'
                   )
               )
            ORDER BY os.function_date DESC, os.order_sheet_no DESC, os.item_id
        ");
        $orders_by_date = [];
        $item_unload_quantities = [];
        $unload_result = $conn->query("
            SELECT item_id, COALESCE(SUM(remaining_qty), 0) as remaining_qty
            FROM hggfunction_unload
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
                if (!isset($orders_by_date[$function_date])) {
                    $orders_by_date[$function_date] = [
                        'function_date' => $function_date,
                        'order_sheets' => [],
                        'items_by_id' => [],
                        'responsible_names' => [],
                        'responsible_ids' => [],
                        'function_types' => [],
                        'day_nights' => [],
                        'order_sheet_numbers' => []
                    ];
                }
                if (!isset($orders_by_date[$function_date]['order_sheets'][$order_sheet_no])) {
                    $orders_by_date[$function_date]['order_sheets'][$order_sheet_no] = [
                        'order_sheet_no' => $order_sheet_no,
                        'request_date' => $row['request_date'],
                        'responsible_name' => $row['responsible_name'],
                        'responsible_id' => $row['responsible_id'],
                        'function_type' => $row['function_type'] ?? 'N/A',
                        'day_night' => $row['day_night'] ?? 'N/A',
                        'items' => []
                    ];
                    $orders_by_date[$function_date]['order_sheet_numbers'][] = $order_sheet_no;
                }
                $orders_by_date[$function_date]['order_sheets'][$order_sheet_no]['items'][] = [
                    'item_id' => $item_id,
                    'item_name' => $row['item_name'],
                    'requested_qty' => $row['requested_qty'],
                    'issued_qty' => $row['issued_qty'],
                    'remaining_qty' => $remaining_qty,
                    'unit' => $row['unit'] ?? 'Unit',
                    'status' => $row['status']
                ];
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
                $orders_by_date[$function_date]['items_by_id'][$item_id]['requested_qty'] += $row['requested_qty'];
                $orders_by_date[$function_date]['items_by_id'][$item_id]['issued_qty'] += $row['issued_qty'];
                $orders_by_date[$function_date]['items_by_id'][$item_id]['order_sheets'][] = $order_sheet_no;
                $orders_by_date[$function_date]['responsible_names'][$row['responsible_name'] ?? 'N/A'] = true;
                $orders_by_date[$function_date]['responsible_ids'][$row['responsible_id']] = true;
                $orders_by_date[$function_date]['function_types'][$row['function_type'] ?? 'N/A'] = true;
                $orders_by_date[$function_date]['day_nights'][$row['day_night'] ?? 'N/A'] = true;
            }
            foreach ($orders_by_date as &$date_order) {
                $date_order['responsible_names'] = array_keys($date_order['responsible_names']);
                $date_order['responsible_ids'] = array_keys($date_order['responsible_ids']);
                $date_order['function_types'] = array_keys($date_order['function_types']);
                $date_order['day_nights'] = array_keys($date_order['day_nights']);
                $date_order['order_sheets'] = array_values($date_order['order_sheets']);
                $date_order['items_by_id'] = array_values($date_order['items_by_id']);
            }
            unset($date_order);
        } else {
            error_log("Query failed: " . $conn->error);
            echo "<div class='error'>Error fetching pending orders: " . htmlspecialchars($conn->error) . "</div>";
        }
        $conn->close();
        ?>
        <div class="bg-white p-6 rounded-xl mb-8 shadow-md">
            <h3 class="text-xl font-semibold text-teal-900 mb-4">Select Function Date to Issue</h3>
            <?php if (empty($orders_by_date)): ?>
                <p class="text-gray-600 text-center">No pending or partially issued orders to issue.</p>
            <?php else: ?>
                <form method="POST" action="" id="select_date_form">
                    <div class="mb-4">
                        <label for="function_date" class="block text-sm font-medium text-gray-700 mb-2">Select Function Date</label>
                        <select name="function_date" id="function_date" onchange="showDateDetails(this.value)" class="block w-full max-w-md p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                            <option value="">-- Select Function Date --</option>
                            <?php foreach ($orders_by_date as $date => $order): ?>
                                <option value="<?php echo htmlspecialchars($date); ?>">
                                    Function Date: <?php echo htmlspecialchars($date); ?> (<?php echo htmlspecialchars(implode(', ', array_keys($order['function_types']))); ?> - <?php echo htmlspecialchars(implode(', ', array_keys($order['day_nights']))); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <div id="password_modal" class="password-modal">
            <div class="password-modal-content">
                <h3 class="text-lg font-semibold text-teal-900 mb-4">Verify Responsible Person</h3>
                <form id="password_form" method="POST" action="">
                    <input type="hidden" name="function_date" id="password_function_date">
                    <div class="mb-4">
                        <label for="password_responsible_id" class="block text-sm font-medium text-gray-700 mb-2">Select Responsible Person</label>
                        <select name="responsible_id" id="password_responsible_id" class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 sm:text-sm" required>
                            <option value="">-- Select Responsible Person --</option>
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
                        <h2>Issued Order Sheet <br>HGG Restaurant</h2>
                        <div><strong>Requesting Date</strong><span class="colon">:</span><span id="print_function_date"></span></div>
                        <div><strong>Issued Date</strong><span class="colon">:</span><span id="print_date"></span></div>
                        <div><strong>Function Time</strong><span class="colon">:</span><span id="print_day_night"></span></div>
                        <div><strong>Function Type</strong><span class="colon">:</span><span id="print_function_type"></span></div>
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
                                <th>Unload Qty</th>
                                <th>Unit</th>
                            </tr>
                        </thead>
                        <tbody id="print_items"></tbody>
                    </table>
                    <div id="print_partial" class="no-print" style="display: none;">
                        <div><strong>Partial Issuance Note:</strong></div>
                        <div id="print_partial_details"></div>
                    </div>
                    <div class="signatures">
                        <div class="signature-box">
                            <div>Authorised By:</div>
                            <div><span id="print_responsible"></span></div>
                            <div class="signature-line"></div>
                        </div>
                        <div class="signature-box">
                            <div>Storeman:</div>
                            <div class="user-greeting"><?php echo $username; ?></div>
                            <div class="signature-line"></div>
                        </div>
                    </div>
                    <div class="receipt-footer">
                         <div class='signature-box'>
                                <div>Recieved By:</div>
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
                <h3 class="text-lg font-semibold text-teal-900 mb-4">Function Date Details</h3>
                <p class="text-gray-600 mb-2"><span class="font-medium">Function Date:</span> <span id="modal_function_date"></span></p>
                <p class="text-gray-600 mb-2"><span class="font-medium">Responsible:</span> <span id="modal_responsible"></span></p>
                <p class="text-gray-600 mb-2"><span class="font-medium">Function Locations:</span> <span id="modal_function_type"></span></p>
                <p class="text-gray-600 mb-2"><span class="font-medium">Function Times:</span> <span id="modal_day_night"></span></p>
                <p class="text-gray-600 mb-4"><span class="font-medium">Order Sheets:</span> <span id="modal_order_sheets"></span></p>
                <table class="min-w-full bg-white border border-gray-200 rounded-lg mb-6">
                    <thead class="bg-teal-100">
                        <tr>
                            <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Item Name</th>
                            <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Requested Qty</th>
                            <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Issued Qty</th>
                            <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Unload Qty</th>
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

        <h3 class="text-xl font-semibold text-teal-900 mt-8 mb-4">Pending and Partially Issued Orders</h3>
        <div id="pending_orders_container">
            <?php if (empty($orders_by_date)): ?>
                <p class="text-gray-600 text-center">No pending or partially issued orders.</p>
            <?php else: ?>
                <div class="table-container">
                    <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                        <thead class="bg-teal-100">
                            <tr>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Function Date</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Order Sheet No</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Function Location</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Function Time</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Items</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Requested Qty</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Issued Qty</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Unload Qty</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Units</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Request Date</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Responsible Person</th>
                            </tr>
                        </thead>
                        <tbody id="pending_orders_table">
                            <?php foreach ($orders_by_date as $date => $date_order): ?>
                                <?php foreach ($date_order['order_sheets'] as $order): ?>
                                    <tr class="<?php echo $index++ % 2 ? 'bg-gray-50' : 'bg-white'; ?> hover:bg-teal-50">
                                        <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($date); ?></td>
                                        <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($order['order_sheet_no']); ?></td>
                                        <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($order['function_type'] ?? 'N/A'); ?></td>
                                        <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($order['day_night'] ?? 'N/A'); ?></td>
                                        <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars(implode(', ', array_column($order['items'], 'item_name'))); ?></td>
                                        <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars(implode(', ', array_column($order['items'], 'requested_qty'))); ?></td>
                                        <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars(implode(', ', array_column($order['items'], 'issued_qty'))); ?></td>
                                        <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars(implode(', ', array_map(function($item) { return $item['remaining_qty'] !== null ? $item['remaining_qty'] : '0'; }, $order['items']))); ?></td>
                                        <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars(implode(', ', array_column($order['items'], 'unit'))); ?></td>
                                        <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($order['request_date']); ?></td>
                                        <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($order['responsible_name'] ?? 'N/A'); ?></td>
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
                    $conn = new mysqli('localhost', 'root', 'Sun123flower@', 'wedding_bliss');
                    if ($conn->connect_error) {
                        error_log("Connection failed: " . $conn->connect_error);
                        die("<div class='error'>Connection failed: " . htmlspecialchars($conn->connect_error) . "</div>");
                    }
                    $result = $conn->query("
                        SELECT pi.id, i.item_name, pi.stock, i.unit, pi.price, pi.unit_price, pi.purchased_date, pi.expiry_date
                        FROM purchased_items pi
                        JOIN inventory i ON pi.item_id = i.id
                        ORDER BY pi.id DESC
                    ");
                    if ($result) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr class='hover:bg-teal-50'>
                                <td class='py-3 px-4 border-b text-sm'>" . htmlspecialchars($row['id']) . "</td>
                                <td class='py-3 px-4 border-b text-sm'>" . htmlspecialchars($row['item_name']) . "</td>
                                <td class='py-3 px-4 border-b text-sm'>" . htmlspecialchars($row['stock']) . "</td>
                                <td class='py-3 px-4 border-b text-sm'>" . htmlspecialchars($row['unit'] ?? 'Unit') . "</td>
                                <td class='py-3 px-4 border-b text-sm'>" . number_format($row['price'], 2) . "</td>
                                <td class='py-3 px-4 border-b text-sm'>" . number_format($row['unit_price'], 2) . "</td>
                                <td class='py-3 px-4 border-b text-sm'>" . htmlspecialchars($row['purchased_date']) . "</td>
                                <td class='py-3 px-4 border-b text-sm'>" . ($row['expiry_date'] ? htmlspecialchars($row['expiry_date']) : 'N/A') . "</td>
                            </tr>";
                        }
                    }
                    $conn->close();
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
const functionDateSelect = document.getElementById('function_date');
const pendingAlert = document.getElementById('pending-alert');
const issueAllButton = document.getElementById('issue_all_button');
const printError = document.getElementById('print-error');

function updatePendingOrdersTable(orders) {
    console.log('Updating pending orders with:', orders);
    if (!orders || typeof orders !== 'object' || orders === null) {
        console.error('Invalid orders data received:', orders);
        pendingOrdersContainer.innerHTML = '<p class="text-gray-600 text-center">Error loading pending orders. Please check the server.</p>';
        pendingAlert.classList.add('hidden');
        functionDateSelect.innerHTML = '<option value="">-- Select Function Date --</option>';
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
        functionDateSelect.innerHTML = '<option value="">-- Select Function Date --</option>';
        return;
    }

    pendingAlert.classList.remove('hidden');
    let tableHTML = `
        <div class="table-container">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead class="bg-teal-100">
                    <tr>
                        <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Function Date</th>
                        <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Order Sheet No</th>
                        <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Function Location</th>
                        <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Function Time</th>
                        <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Items</th>
                        <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Requested Qty</th>
                        <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Issued Qty</th>
                        <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Unload Qty</th>
                        <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Needed Qty</th>
                        <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Units</th>
                        <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Request Date</th>
                        <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Responsible Person</th>
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
            const unloadQuantities = order.items.map(item => item.unload_qty !== null ? item.unload_qty : 0);
            const neededQuantities = order.items.map(item => {
                const unload = item.unload_qty !== null ? item.unload_qty : 0;
                return Math.max(0, (item.requested_qty - item.issued_qty - unload));
            });
            const units = order.items.map(item => item.unit);

            tableHTML += `
                <tr class="${index % 2 ? 'bg-gray-50' : 'bg-white'} hover:bg-teal-50">
                    <td class="py-3 px-4 border-b text-sm">${date_order.function_date}</td>
                    <td class="py-3 px-4 border-b text-sm">${order.order_sheet_no}</td>
                    <td class="py-3 px-4 border-b text-sm">${order.function_type || 'N/A'}</td>
                    <td class="py-3 px-4 border-b text-sm">${order.day_night || 'N/A'}</td>
                    <td class="py-3 px-4 border-b text-sm">${itemNames.join(', ')}</td>
                    <td class="py-3 px-4 border-b text-sm">${requestedQuantities.join(', ')}</td>
                    <td class="py-3 px-4 border-b text-sm">${issuedQuantities.join(', ')}</td>
                    <td class="py-3 px-4 border-b text-sm">${unloadQuantities.join(', ')}</td>
                    <td class="py-3 px-4 border-b text-sm">${neededQuantities.join(', ')}</td>
                    <td class="py-3 px-4 border-b text-sm">${units.join(', ')}</td>
                    <td class="py-3 px-4 border-b text-sm">${order.request_date}</td>
                    <td class="py-3 px-4 border-b text-sm">${order.responsible_name || 'N/A'}</td>
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

    functionDateSelect.innerHTML = '<option value="">-- Select Function Date --</option>';
    Object.values(orders).forEach(date_order => {
        const option = document.createElement('option');
        option.value = date_order.function_date;
        option.textContent = `Function Date: ${date_order.function_date} (${date_order.function_types.join(', ')} - ${date_order.day_nights.join(', ')})`;
        functionDateSelect.appendChild(option);
    });
    console.log('Pending orders table updated.');
}

function fetchPendingOrders() {
    console.log('Fetching pending orders...');
    fetch('hggfetch_pending_orders.php', {
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

function showDateDetails(functionDate) {
    console.log('showDateDetails called with functionDate:', functionDate);
    console.log('Current ordersByDate:', ordersByDate);

    if (!functionDate) {
        console.log('No function date selected, hiding modal.');
        orderModal.style.display = 'none';
        return;
    }

    const dateOrder = ordersByDate[functionDate];
    if (!dateOrder) {
        console.error(`Order not found for function_date: ${functionDate}`);
        document.getElementById('modal_items').innerHTML = '<tr><td colspan="7" class="py-3 px-4 border-b text-sm text-center">No order details found.</td></tr>';
        document.getElementById('modal_function_date').textContent = functionDate;
        document.getElementById('modal_responsible').textContent = 'None';
        document.getElementById('modal_function_type').textContent = 'None';
        document.getElementById('modal_day_night').textContent = 'None';
        document.getElementById('modal_order_sheets').textContent = 'None';
        orderModal.style.display = 'flex';
        issueAllButton.classList.add('hidden');
        return;
    }

    console.log(`Showing details for function_date: ${functionDate}`, dateOrder);

    document.getElementById('modal_function_date').textContent = dateOrder.function_date || 'Unknown';
    document.getElementById('modal_responsible').textContent = dateOrder.responsible_names && dateOrder.responsible_names.length > 0 ? dateOrder.responsible_names.join(', ') : 'None';
    document.getElementById('modal_function_type').textContent = dateOrder.function_types && dateOrder.function_types.length > 0 ? dateOrder.function_types.join(', ') : 'None';
    document.getElementById('modal_day_night').textContent = dateOrder.day_nights && dateOrder.day_nights.length > 0 ? dateOrder.day_nights.join(', ') : 'None';
    document.getElementById('modal_order_sheets').textContent = dateOrder.order_sheet_numbers && dateOrder.order_sheet_numbers.length > 0 ? dateOrder.order_sheet_numbers.join(', ') : 'None';

    const modalItems = document.getElementById('modal_items');
    modalItems.innerHTML = '';
    let hasPendingItems = false;

    if (!dateOrder.items_by_id || dateOrder.items_by_id.length === 0) {
        console.log('No items found for this function date.');
        modalItems.innerHTML = '<tr><td colspan="7" class="py-3 px-4 border-b text-sm text-center">No items found.</td></tr>';
        orderModal.style.display = 'flex';
        issueAllButton.classList.add('hidden');
        return;
    }

    dateOrder.items_by_id.forEach(item => {
        console.log(`Processing item: ${item.item_name}, item_id: ${item.item_id}, requested_qty: ${item.requested_qty}, issued_qty: ${item.issued_qty}, unload_qty: ${item.unload_qty}`);
        fetch(`check_stock.php?item_id=${item.item_id}`, { cache: 'no-store' })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(stockData => {
                const row = document.createElement('tr');
                const totalAvailable = (stockData.available_stock || 0) + (stockData.buffer_stock || 0);
                const unloadQty = item.unload_qty !== null && item.unload_qty !== undefined ? item.unload_qty : 0;
                const neededQty = Math.max(0, (item.requested_qty - item.issued_qty - unloadQty));

                const isInsufficient = neededQty > 0 && totalAvailable < neededQty;
                if (isInsufficient) {
                    row.className = 'out-of-stock';
                }
                if (neededQty > 0) hasPendingItems = true;

                row.innerHTML = `
                    <td class="py-3 px-4 border-b text-sm">${item.item_name}</td>
                    <td class="py-3 px-4 border-b text-sm">${item.requested_qty}</td>
                    <td class="py-3 px-4 border-b text-sm">${item.issued_qty}</td>
                    <td class="py-3 px-4 border-b text-sm">${unloadQty}</td>
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
                const unloadQty = item.unload_qty !== null && item.unload_qty !== undefined ? item.unload_qty : 0;
                const neededQty = Math.max(0, (item.requested_qty - item.issued_qty - unloadQty));

                if (neededQty > 0) hasPendingItems = true;

                row.innerHTML = `
                    <td class="py-3 px-4 border-b text-sm">${item.item_name}</td>
                    <td class="py-3 px-4 border-b text-sm">${item.requested_qty}</td>
                    <td class="py-3 px-4 border-b text-sm">${item.issued_qty}</td>
                    <td class="py-3 px-4 border-b text-sm">${unloadQty}</td>
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
    const functionDate = document.getElementById('modal_function_date').textContent;
    const dateOrder = ordersByDate[functionDate];
    if (!dateOrder) {
        console.error(`No order data found for function_date: ${functionDate}`);
        return;
    }

    document.getElementById('password_function_date').value = functionDate;
    const responsibleSelect = document.getElementById('password_responsible_id');
    responsibleSelect.innerHTML = '<option value="">-- Select Responsible Person --</option>';

    // Fetch responsible persons from the server
    fetch('fetch_responsible_persons.php', { cache: 'no-store' })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.error) {
                console.error('Error fetching responsible persons:', data.error);
                document.getElementById('password_error').textContent = data.error;
                document.getElementById('password_error').style.display = 'block';
                return;
            }
            data.forEach(person => {
                const option = document.createElement('option');
                option.value = person.id;
                option.textContent = person.name;
                responsibleSelect.appendChild(option);
            });
            document.getElementById('password_input').value = '';
            document.getElementById('password_error').style.display = 'none';
            passwordModal.style.display = 'flex';
        })
        .catch(error => {
            console.error('Error fetching responsible persons:', error);
            document.getElementById('password_error').textContent = 'Failed to load responsible persons. Please try again.';
            document.getElementById('password_error').style.display = 'block';
            passwordModal.style.display = 'flex';
        });
}

function closePasswordModal() {
    passwordModal.style.display = 'none';
}

function closePrintModal() {
    printModal.style.display = 'none';
    window.location.reload();
}

function showPrintModal(order) {
    document.getElementById('print_function_date').textContent = order.function_date || '-';
    document.getElementById('print_function_type').textContent = order.function_types && order.function_types.length > 0 ? order.function_types.join(', ') : '-';
    document.getElementById('print_day_night').textContent = order.day_nights && order.day_nights.length > 0 ? order.day_nights.join(', ') : '-';
    document.getElementById('print_responsible').textContent = order.responsible_name || '-';
    document.getElementById('print_order_sheets').textContent = order.order_sheets && order.order_sheets.length > 0 ? order.order_sheets.join(', ') : '-';
    document.getElementById('print_date').textContent = new Date().toLocaleString();

    const printItems = document.getElementById('print_items');
    printItems.innerHTML = '';
    if (order.items && order.items.length > 0) {
        order.items.forEach(item => {
            const tr = document.createElement('tr');
            const unloadQty = item.unload_qty !== null && item.unload_qty !== undefined ? item.unload_qty : 0;
            const issuedQty = unloadQty >= item.requested_qty ? unloadQty - item.requested_qty : item.issued_qty;

            tr.innerHTML = `
                <td>${item.item_name}</td>
                <td>${item.requested_qty}</td>
                <td>${issuedQty}</td>
                <td>${unloadQty}</td>
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
            const unloadQty = item.unload_qty !== null && item.unload_qty !== undefined ? item.unload_qty : 0;
            return `<div>${item.item_name}: Requested ${item.requested_qty}, Issued ${item.issued_qty}, Unload ${unloadQty}, Pending ${item.remaining_qty}</div>`;
        }).join('');
    } else {
        printPartial.style.display = 'none';
        printPartialDetails.innerHTML = '';
    }

    printModal.style.display = 'flex';
    printError.classList.add('hidden');
}

function printReceipt() {
    if (typeof qz !== 'undefined') {
        qz.websocket.connect().then(() => {
            console.log("✅ QZ Tray connected");
            return qz.printers.find("POSPrinter POS-80C");
        }).then(printer => {
            console.log("🖨️ Printer found: " + printer);
            const config = qz.configs.create(printer, {
                margins: { top: 0, right: 0, bottom: 0, left: 0 },
                size: { width: 80, height: 'auto' },
                units: 'mm'
            });

            const printContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <style>
                        * {
                            margin: 0;
                            padding: 0;
                            box-sizing: border-box;
                            font-family: 'Arial', sans-serif;
                        }
                        body {
                            font-size: 9pt;
                            line-height: 1.5;
                            color: #000;
                            background: #fff;
                            width: 80mm;
                        }
                        .receipt-container {
                            width: 80mm;
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
                            width: 80mm;
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
                        .receipt-table th:nth-child(2) { width: 15%; }
                        .receipt-table th:nth-child(3) { width: 20%; }
                        .receipt-table th:nth-child(4) { width: 15%; }
                        .receipt-table th:nth-child(5) { width: 20%; }
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
                        .receipt-table td:nth-child(2) { white-space: nowrap; }
                        .receipt-table td:nth-child(3) { white-space: nowrap; }
                        .receipt-table td:nth-child(4) { white-space: nowrap; }
                        .receipt-table td:nth-child(5) { white-space: nowrap; }
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
                    </style>
                </head>
                <body>
                    <div class='receipt-container'>
                        <div class='receipt-header'>
                            <h2>Issued Order Sheet <br> HGG Restaurant</h2>
                            <div><strong>Requesting Date</strong><span class='colon'>:</span><span>${document.getElementById('print_function_date').textContent}</span></div>
                            <div><strong>Issued Date</strong><span class='colon'>:</span><span>${document.getElementById('print_date').textContent}</span></div>
                            <div><strong>Function Time</strong><span class='colon'>:</span><span>${document.getElementById('print_day_night').textContent}</span></div>
                            <div><strong>Function Type</strong><span class='colon'>:</span><span>${document.getElementById('print_function_type').textContent}</span></div>
                        </div>
                        <div class='receipt-details'>
                            
                            <div><strong>Order Sheet No:</strong> ${document.getElementById('print_order_sheets').textContent}</div>
                        </div>
                        <table class='receipt-table'>
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Req Qty</th>
                                    <th>Issued Qty</th>
                                    <th>Unload Qty</th>
                                    <th>Unit</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${document.getElementById('print_items').innerHTML}
                            </tbody>
                        </table>
                      
                        <div class='signatures'>
                            <div class='signature-box'>
                                <div>Authorised By:</div>
                                <div>${document.getElementById('print_responsible').textContent}</div>
                                <div class='signature-line'></div>
                            </div>
                            <div class='signature-box'>
    <div>Storeman:</div>
    <div>
        <?php 
            if (isset($_SESSION['username'])) {
                echo htmlspecialchars($_SESSION['username']); 
            } else {
                echo "Guest";
            }
        ?>
    </div>
    <div class='signature-line'></div>
</div>

                            
                        </div>
                          <div class='receipt-footer'>
                         <div class='signature-box'>
                                <div>Recieved By:</div>
                                <div class='signature-line'></div>
                            </div>
                            <div>System Generated!</div>
                        </div>
                    </div>
                </body>
                </html>
            `;

            return qz.print(config, [{
                type: 'html',
                format: 'plain',
                data: printContent
            }]);
        }).then(() => {
            console.log("✅ Print job sent successfully");
            qz.websocket.disconnect();
            closePrintModal();
        }).catch(err => {
            console.error("❌ Print error:", err);
            printError.classList.remove('hidden');
            qz.websocket.disconnect();
        });
    } else {
        console.error("❌ QZ Tray not loaded");
        printError.classList.remove('hidden');
    }
}

// Initialize event listeners
closeModal.addEventListener('click', () => {
    orderModal.style.display = 'none';
});

orderModal.addEventListener('click', (e) => {
    if (e.target === orderModal) {
        orderModal.style.display = 'none';
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

// Set up periodic fetching of pending orders
setInterval(fetchPendingOrders, 10000);
fetchPendingOrders();

// Show print modal if there's a last issued order
<?php if (isset($_SESSION['last_issued_order'])): ?>
    showPrintModal(<?php echo json_encode($_SESSION['last_issued_order']); ?>);
    <?php unset($_SESSION['last_issued_order']); ?>
<?php endif; ?>
    </script>
</body>
</html>