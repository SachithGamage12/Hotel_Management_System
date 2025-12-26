<?php
ob_start();
session_start();
date_default_timezone_set('Asia/Colombo');
header("Cache-Control: no-cache, must-revalidate");

$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed. Please check server logs.");
}
$conn->set_charset("utf8mb4");

$logged_in_user = isset($_SESSION['username']) ? $_SESSION['username'] : '';
if (!isset($_SESSION['grn_items'])) {
    $_SESSION['grn_items'] = [];
}
if (!isset($_SESSION['order_location'])) {
    $_SESSION['order_location'] = 'Main Warehouse';
}
if (!isset($_SESSION['checked_by'])) {
    $_SESSION['checked_by'] = '';
}
if (!isset($_SESSION['grn_number'])) {
    $_SESSION['grn_number'] = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['term'])) {
    error_log("Clearing session on GET request");
    $_SESSION['grn_items'] = [];
    $_SESSION['checked_by'] = '';
    $_SESSION['grn_number'] = '';
}

if (isset($_GET['term'])) {
    ob_clean();
    $term = '%' . trim($_GET['term']) . '%';
    $stmt = $conn->prepare("SELECT name AS item_name, unit_type AS unit FROM items WHERE name LIKE ? LIMIT 10");
    if ($stmt === false) {
        error_log("Prepare failed for autocomplete query: " . $conn->error);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Database query preparation failed: ' . $conn->error]);
        ob_end_flush();
        exit;
    }
    $stmt->bind_param("s", $term);
    if (!$stmt->execute()) {
        error_log("Execute failed for autocomplete query: " . $stmt->error);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Query execution failed: ' . $stmt->error]);
        $stmt->close();
        ob_end_flush();
        exit;
    }
    $result = $stmt->get_result();
    $suggestions = [];
    while ($row = $result->fetch_assoc()) {
        $suggestions[] = [
            'label' => mb_convert_encoding($row['item_name'], 'UTF-8', 'UTF-8'),
            'value' => mb_convert_encoding($row['item_name'], 'UTF-8', 'UTF-8'),
            'unit' => mb_convert_encoding($row['unit'], 'UTF-8', 'UTF-8')
        ];
    }
    $stmt->close();
    header('Content-Type: application/json; charset=utf-8');
    $json_output = json_encode($suggestions, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    error_log("Autocomplete JSON output: " . $json_output);
    if ($json_output === false) {
        error_log("JSON encoding failed: " . json_last_error_msg());
        echo json_encode(['error' => 'JSON encoding failed: ' . json_last_error_msg()]);
    } else {
        echo $json_output;
    }
    ob_end_flush();
    exit;
}

$checked_by = '';
$error_message = '';
if (isset($_POST['verify_responsible'])) {
    $responsible_name = trim($_POST['responsible_name']);
    $responsible_password = $_POST['responsible_password'];
    $stmt = $conn->prepare("SELECT * FROM logistics_users WHERE name = ?");
    if ($stmt === false) {
        error_log("Prepare failed for responsible query: " . $conn->error);
        $error_message = "Database error: Unable to prepare responsible person query.";
    } else {
        $stmt->bind_param("s", $responsible_name);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $responsible = $result->fetch_assoc();
            if (password_verify($responsible_password, $responsible['password'])) {
                $checked_by = $responsible_name;
                $_SESSION['checked_by'] = $checked_by;
                $stmt = $conn->prepare("SELECT MAX(CAST(SUBSTRING(grn_number, 5) AS UNSIGNED)) as max_num FROM logistics_grn");
                if ($stmt === false) {
                    error_log("Prepare failed for GRN number query: " . $conn->error);
                    $error_message = "Database error: Unable to prepare GRN number query.";
                } else {
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $next_num = ($row['max_num'] && $row['max_num'] >= 1500) ? $row['max_num'] + 1 : 1500;
                    $_SESSION['grn_number'] = "GRN-" . $next_num;
                    error_log("Generated GRN number: " . $_SESSION['grn_number']);
                }
            } else {
                $error_message = "Invalid responsible person credentials";
            }
        } else {
            $error_message = "Responsible person not found";
        }
        $stmt->close();
    }
}

if (isset($_POST['order_location']) && !isset($_POST['add_item']) && !isset($_POST['remove_item'])) {
    $_SESSION['order_location'] = in_array($_POST['order_location'], ['Main Warehouse', 'Secondary Warehouse']) ? $_POST['order_location'] : 'Main Warehouse';
    error_log("Location changed to: " . $_SESSION['order_location']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_name'], $_POST['quantity'], $_POST['unit'], $_POST['add_item'])) {
    $item_name = trim($_POST['item_name']);
    $quantity = (int)$_POST['quantity'];
    $unit = trim($_POST['unit']);
    $added_date = date("Y-m-d H:i:s");
    error_log("Received item data: item_name=$item_name, quantity=$quantity, unit=$unit, added_date=$added_date");
    $stmt = $conn->prepare("SELECT name FROM items WHERE name = ? AND unit_type = ?");
    if ($stmt === false) {
        error_log("Prepare failed for item validation: " . $conn->error);
        $error_message = "Database error: Unable to verify item.";
    } else {
        $stmt->bind_param("ss", $item_name, $unit);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0 && $quantity > 0) {
            $_SESSION['grn_items'][] = [
                'item_name' => $item_name,
                'quantity' => $quantity,
                'unit' => $unit,
                'added_date' => $added_date
            ];
            error_log("Added new item: item_name=$item_name, quantity=$quantity, unit=$unit, added_date=$added_date");
            error_log("Current session items: " . print_r($_SESSION['grn_items'], true));
        } else {
            $error_message = "Invalid item or quantity.";
            error_log("Item validation failed: item_name=$item_name, unit=$unit, quantity=$quantity");
        }
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $index = (int)$_POST['remove_item'];
    if (isset($_SESSION['grn_items'][$index])) {
        $removed_item = $_SESSION['grn_items'][$index];
        unset($_SESSION['grn_items'][$index]);
        $_SESSION['grn_items'] = array_values($_SESSION['grn_items']);
        error_log("Removed item at index $index: " . print_r($removed_item, true));
        error_log("Current session items after removal: " . print_r($_SESSION['grn_items'], true));
    } else {
        $error_message = "Invalid item index.";
        error_log("Failed to remove item at index $index: Index does not exist");
    }
}

if (isset($_POST['clear'])) {
    $_SESSION['grn_items'] = [];
    $_SESSION['checked_by'] = '';
    $_SESSION['grn_number'] = '';
    error_log("Session cleared via Clear All");
}

if (isset($_POST['save_grn']) && !empty($_SESSION['grn_items']) && !empty($_SESSION['grn_number'])) {
    error_log("GRN items before saving: " . print_r($_SESSION['grn_items'], true));
    $grn_number = $_SESSION['grn_number'];
    $date = date("Y-m-d H:i:s");
    $location = $_SESSION['order_location'];
    $received_by = $logged_in_user;
    $checked_by = isset($_SESSION['checked_by']) ? $_SESSION['checked_by'] : '';
    $check_stmt = $conn->prepare("SELECT id FROM logistics_grn WHERE grn_number = ?");
    $check_stmt->bind_param("s", $grn_number);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        $error_message = "GRN $grn_number already exists. Cannot save duplicate.";
        error_log("Duplicate GRN detected: $grn_number");
        $check_stmt->close();
    } else {
        $check_stmt->close();
        $stmt = $conn->prepare("INSERT INTO logistics_grn (grn_number, date, location, received_by, checked_by) VALUES (?, ?, ?, ?, ?)");
        if ($stmt === false) {
            error_log("Prepare failed for GRN insert: " . $conn->error);
            $error_message = "Database error: Unable to save GRN.";
        } else {
            $stmt->bind_param("sssss", $grn_number, $date, $location, $received_by, $checked_by);
            if ($stmt->execute()) {
                $grn_id = $conn->insert_id;
                $item_stmt = $conn->prepare("INSERT INTO logistics_grn_details (grn_id, item_name, quantity, unit, added_date) VALUES (?, ?, ?, ?, ?)");
                if ($item_stmt === false) {
                    error_log("Prepare failed for GRN items insert: " . $conn->error);
                    $error_message = "Database error: Unable to save GRN items.";
                } else {
                    foreach ($_SESSION['grn_items'] as $index => $item) {
                        $item_name = $item['item_name'];
                        $quantity = (int)$item['quantity'];
                        $unit = $item['unit'];
                        $added_date = $item['added_date'];
                        error_log("Saving item $index: item_name=$item_name, quantity=$quantity, unit=$unit, added_date=$added_date");
                        $item_stmt->bind_param("isiss", $grn_id, $item_name, $quantity, $unit, $added_date);
                        if (!$item_stmt->execute()) {
                            error_log("GRN items insert failed: " . $item_stmt->error);
                        }
                    }
                    $item_stmt->close();
                    foreach ($_SESSION['grn_items'] as $item) {
                        $item_name = $item['item_name'];
                        $quantity = (int)$item['quantity'];
                        $added_date = $item['added_date'];
                        $id_stmt = $conn->prepare("SELECT id FROM items WHERE name = ?");
                        if ($id_stmt === false) {
                            error_log("Prepare failed for item_id query: " . $conn->error);
                            continue;
                        }
                        $id_stmt->bind_param("s", $item_name);
                        $id_stmt->execute();
                        $result = $id_stmt->get_result();
                        if ($row = $result->fetch_assoc()) {
                            $item_id = $row['id'];
                        } else {
                            error_log("Item not found in items table: $item_name");
                            $id_stmt->close();
                            continue;
                        }
                        $id_stmt->close();
                        $history_stmt = $conn->prepare("INSERT INTO stock_additions (item_id, location, quantity, added_date, grn_id) VALUES (?, ?, ?, ?, ?)");
                        if ($history_stmt === false) {
                            error_log("Prepare failed for stock_additions insert: " . $conn->error);
                        } else {
                            $history_stmt->bind_param("isisi", $item_id, $location, $quantity, $added_date, $grn_id);
                            if (!$history_stmt->execute()) {
                                error_log("Stock additions insert failed: " . $history_stmt->error);
                            }
                            $history_stmt->close();
                        }
                        $stock_stmt = $conn->prepare("SELECT available_quantity, total_quantity FROM item_stock WHERE item_id = ? AND location = ?");
                        if ($stock_stmt === false) {
                            error_log("Prepare failed for stock check: " . $conn->error);
                            continue;
                        }
                        $stock_stmt->bind_param("is", $item_id, $location);
                        $stock_stmt->execute();
                        $stock_result = $stock_stmt->get_result();
                        if ($stock_result->num_rows > 0) {
                            $stock_row = $stock_result->fetch_assoc();
                            $old_available = $stock_row['available_quantity'];
                            $old_total = $stock_row['total_quantity'];
                            $new_available = $old_available + $quantity;
                            $new_total = $old_total + $quantity;
                            $update_stmt = $conn->prepare("UPDATE item_stock SET available_quantity = ?, last_added_quantity = ?, last_added_date = ?, total_quantity = ? WHERE item_id = ? AND location = ?");
                            if ($update_stmt === false) {
                                error_log("Prepare failed for stock update: " . $conn->error);
                                $stock_stmt->close();
                                continue;
                            }
                            $update_stmt->bind_param("iisiss", $new_available, $quantity, $added_date, $new_total, $item_id, $location);
                            if (!$update_stmt->execute()) {
                                error_log("Stock update failed: " . $update_stmt->error);
                            }
                            $update_stmt->close();
                        } else {
                            $new_available = $quantity;
                            $new_total = $quantity;
                            $insert_stmt = $conn->prepare("INSERT INTO item_stock (item_id, location, available_quantity, last_added_quantity, last_added_date, total_quantity) VALUES (?, ?, ?, ?, ?, ?)");
                            if ($insert_stmt === false) {
                                error_log("Prepare failed for stock insert: " . $conn->error);
                                $stock_stmt->close();
                                continue;
                            }
                            $insert_stmt->bind_param("isiisi", $item_id, $location, $new_available, $quantity, $added_date, $new_total);
                            if (!$insert_stmt->execute()) {
                                error_log("Stock insert failed: " . $insert_stmt->error);
                            }
                            $insert_stmt->close();
                        }
                        $stock_stmt->close();
                    }
                    $_SESSION['grn_items'] = [];
                    $_SESSION['checked_by'] = '';
                    $_SESSION['grn_number'] = '';
                    $success_message = "GRN $grn_number saved successfully!";
                }
            } else {
                $error_message = "Error saving GRN: " . $stmt->error;
                error_log("GRN insert failed: " . $stmt->error);
            }
            $stmt->close();
        }
    }
}

$date = date("Y-m-d H:i:s");
$grn_number = isset($_SESSION['grn_number']) && !empty($_SESSION['grn_number']) ? $_SESSION['grn_number'] : 'Pending';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=80mm, initial-scale=1.0">
    <title>Logistics Goods Received Note</title>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        body {
            font-size: 8pt;
            line-height: 1.5;
            width: 100%;
            margin: 0;
            padding: 0;
            color: #000;
            background: #fff;
            min-height: 150mm;
            display: flex;
            justify-content: center;
        }
        .wrapper {
            width: 80mm;
            margin: 0 auto;
        }
        .grn-container {
            width: 80mm;
            padding: 3mm;
            text-align: left;
        }
        .grn-header {
            margin: 0 0 5mm 0;
            padding: 0 0 3mm 0;
        }
        .grn-header h2 {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 4mm;
            text-transform: uppercase;
            text-align: center;
            border-bottom: 1.5px solid #000;
        }
        .grn-header div {
            font-size: 8pt;
            font-weight: bold;
            display: flex;
            align-items: center;
            margin-bottom: 3mm;
        }
        .grn-header div strong {
            display: inline-block;
            min-width: 20mm;
            text-align: left;
        }
        .grn-header div .colon {
            display: inline-block;
            width: 2mm;
            text-align: center;
        }
        .grn-header div span {
            display: inline-block;
            margin-left: 1mm;
        }
        .grn-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 5mm 0;
            font-size: 8pt;
            text-align: left;
        }
        .grn-table th {
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            padding: 2mm;
            text-align: left;
            font-weight: bold;
        }
        .grn-table th:nth-child(2) {
            width: 15%;
            text-align: center;
        }
        .grn-table th:nth-child(3) {
            width: 15%;
            text-align: center;
        }
        .grn-table th:nth-child(4) {
            width: 20%;
            text-align: center;
        }
        .grn-table td {
            padding: 2mm;
            border-bottom: 0.5px solid #000;
            vertical-align: top;
            font-weight: bold;
        }
        .grn-table td:first-child {
            width: 50%;
            word-wrap: break-word;
            white-space: normal;
        }
        .grn-table td:nth-child(2) {
            width: 15%;
            text-align: center;
            white-space: nowrap;
        }
        .grn-table td:nth-child(3) {
            width: 15%;
            text-align: center;
            white-space: nowrap;
        }
        .grn-table td:nth-child(4) {
            width: 20%;
            text-align: center;
        }
        .grn-table tr:last-child td {
            border-bottom: 1.5px solid #000;
        }
        .remove-btn {
            padding: 1mm 3mm;
            font-size: 8pt;
            cursor: pointer;
            background: #f2dede;
            border: 1px solid #a94442;
            color: #a94442;
            font-weight: bold;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin: 0 0 5mm 0;
            font-size: 8pt;
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
            font-size: 8pt;
            white-space: nowrap;
            margin-bottom: 3mm;
        }
        .signature-line {
            border-top: 1.5px solid #000;
            margin-top: 6mm;
            padding-top: 2mm;
        }
        .grn-footer {
            text-align: center;
            font-size: 8pt;
            font-weight: bold;
            margin-top: 5mm;
            padding-top: 3mm;
            border-top: 1px dashed #000;
        }
        .controls, .form-container, .user-info, .alert, .responsible-form {
            text-align: center;
            margin: 0 0 5mm 0;
            padding-top: 3mm;
            border-top: 2px dashed #000;
            width: 80mm;
        }
        .controls button, .form-container button, .responsible-form button {
            padding: 2mm 4mm;
            margin: 0 2mm;
            font-size: 8pt;
            cursor: pointer;
            background: #f0f0f0;
            border: 1px solid #000;
            font-weight: bold;
        }
        .controls button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .form-container {
            background: white;
            padding: 3mm;
            margin-bottom: 5mm;
        }
        .form-group {
            margin-bottom: 3mm;
        }
        label {
            display: block;
            margin-bottom: 1mm;
            font-weight: bold;
            font-size: 8pt;
        }
        select, input[type="text"], input[type="number"], input[type="password"] {
            width: 100%;
            padding: 2mm;
            border: 1px solid #000;
            font-size: 8pt;
            box-sizing: border-box;
        }
        select:focus, input[type="text"]:focus, input[type="number"]:focus, input[type="password"]:focus {
            border-color: #000;
            outline: none;
        }
        .alert {
            background: #f2dede;
            color: #a94442;
            padding: 2mm;
            margin: 0 0 4mm 0;
            font-size: 8pt;
            border: 1px solid #a94442;
            font-weight: bold;
            text-align: left;
        }
        .responsible-form {
            background: #f5f5f5;
            padding: 3mm;
            margin-top: 5mm;
        }
        .responsible-form h3 {
            margin-bottom: 2mm;
            font-size: 9pt;
        }
        .print-content {
            display: none;
        }
        @page {
            size: 80mm auto;
            margin: 0;
        }
        @media print {
            body {
                width: 80mm;
                margin: 0;
                padding: 0;
                font-size: 9pt;
                line-height: 1.6;
                color: #000 !important;
                background: #fff !important;
                min-height: 0;
                display: block;
            }
            .wrapper {
                width: 80mm;
                margin: 0 auto;
            }
            .grn-container, .print-content {
                width: 80mm;
                margin: 0 auto;
                padding: 0;
                text-align: left;
            }
            .grn-header {
                margin: 0 0 5mm 0;
                padding: 0 0 3mm 0;
                text-align: left;
            }
            .grn-header h2 {
                font-size: 12pt;
                margin-bottom: 4mm;
                border-bottom: 1.5px solid #000;
                text-align: center;
            }
            .grn-header div {
                font-size: 9pt;
                margin-bottom: 3mm;
            }
            .grn-table {
                width: 80mm;
                border-collapse: collapse;
                margin: 0;
                padding: 0;
                font-size: 9pt;
                table-layout: fixed;
                text-align: left;
            }
            .grn-table tr {
                margin-bottom: 3mm;
            }
            .grn-table th {
                padding: 2mm;
                border-top: 1.5px solid #000;
                border-bottom: 1.5px solid #000;
                text-align: left;
                font-weight: bold;
            }
            .grn-table td {
                padding: 2mm;
                border-bottom: 0.5px solid #000;
                text-align: left;
                font-weight: bold;
            }
            .grn-table th:first-child, .grn-table td:first-child {
                width: 50%;
                word-wrap: break-word;
                white-space: normal;
            }
            .grn-table th:nth-child(2), .grn-table td:nth-child(2) {
                width: 25%;
                text-align: center;
            }
            .grn-table th:nth-child(3), .grn-table td:nth-child(3) {
                width: 25%;
                text-align: center;
            }
            .grn-table th:nth-child(4), .grn-table td:nth-child(4) {
                display: none;
            }
            .grn-table tr:last-child td {
                border-bottom: 1.5px solid #000;
            }
            .signatures {
                margin: 0 0 5mm 0;
                text-align: left;
            }
            .signature-box {
                padding: 3mm;
                font-size: 9pt;
            }
            .signature-box div {
                margin-bottom: 3mm;
            }
            .signature-line {
                margin-top: 6mm;
                padding-top: 2mm;
            }
            .grn-footer {
                margin: 0 0 5mm 0;
                padding: 3mm 0 0 0;
                font-size: 9pt;
                text-align: center;
            }
            .controls, .form-container, .user-info, .alert, .responsible-form, .no-print, .remove-btn {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                width: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
                position: absolute !important;
                left: -9999px !important;
            }
            .print-content {
                display: block !important;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div style="position: absolute; top: 20px; left: 20px;">
           <style>
@media print {
  .no-print {
    display: none !important;
  }
}
</style>

<button onclick="window.location.href='../logistic.php'" 
        class="no-print"
        style="background-color: #f09424ff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
    Back
</button>

        </div>

        <div class="no-print">
            <?php if (!empty($logged_in_user)): ?>
                <div class="user-info">Logged in as: <?php echo htmlspecialchars($logged_in_user); ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="order_location">Location</label>
                        <select name="order_location" id="order_location" onchange="this.form.submit()">
                            <option value="Main Warehouse" <?php echo $_SESSION['order_location'] === 'Main Warehouse' ? 'selected' : ''; ?>>Main Warehouse</option>
                            <option value="Secondary Warehouse" <?php echo $_SESSION['order_location'] === 'Secondary Warehouse' ? 'selected' : ''; ?>>Secondary Warehouse</option>
                        </select>
                    </div>
                </form>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div class="alert"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST" action="" onsubmit="return validateForm()" id="add-item-form">
                    <input type="hidden" name="order_location" value="<?php echo htmlspecialchars($_SESSION['order_location']); ?>">
                    <input type="hidden" name="add_item" value="1">
                    <div class="form-group">
                        <label for="item-search">Search Item</label>
                        <input type="text" id="item-search" name="item_name" placeholder="Type item name" autocomplete="off" required>
                        <input type="hidden" id="item-unit" name="unit" required>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" id="quantity" name="quantity" min="1" placeholder="Enter quantity" required>
                    </div>
                    <button type="submit" class="btn-primary">Add Item</button>
                </form>
            </div>
        </div>

        <div class="grn-container">
            <div class="grn-header">
                <h2>Logistic <br>Goods Received Note</h2>
                <div><strong>GRN No</strong><span class="colon">: </span><span id="print_grn_no"><?php echo htmlspecialchars($grn_number); ?></span></div>
                <div><strong>Date</strong><span class="colon">: </span><span id="print_date"><?php echo date('d-M-Y H:i', strtotime($date)); ?></span></div>
                <div><strong>Location</strong><span class="colon">: </span><span id="print_location"><?php echo htmlspecialchars($_SESSION['order_location']); ?></span></div>
            </div>

            <table class="grn-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="print_items">
                    <?php if (empty($_SESSION['grn_items'])): ?>
                        <tr>
                            <td colspan="4">No items added.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($_SESSION['grn_items'] as $index => $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                <td><?php echo (int)$item['quantity']; ?></td>
                                <td><?php echo strtoupper(htmlspecialchars($item['unit'])); ?></td>
                                <td>
                                    <form method="POST" action="" style="display:inline;">
                                        <input type="hidden" name="remove_item" value="<?php echo $index; ?>">
                                        <input type="hidden" name="order_location" value="<?php echo htmlspecialchars($_SESSION['order_location']); ?>">
                                        <button type="submit" class="remove-btn">Remove</button>
                                    </form>
                                </td>
                            </tr>
                            <?php error_log("Rendering item $index: item_name={$item['item_name']}, quantity={$item['quantity']}, unit={$item['unit']}, added_date={$item['added_date']}"); ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="signatures">
                <div class="signature-box">
                    <div>Received By:</div>
                    <div><?php echo htmlspecialchars($logged_in_user); ?></div>
                    <div class="signature-line"></div>
                </div>
                <div class="signature-box">
                    <div>Checked By:</div>
                    <div><?php echo isset($_SESSION['checked_by']) ? htmlspecialchars($_SESSION['checked_by']) : ''; ?></div>
                    <div class="signature-line"></div>
                </div>
            </div>

            <div class="grn-footer">System Generated!</div>
        </div>

        <div id="print-content" class="print-content"></div>

        <?php if (!empty($_SESSION['grn_items'])): ?>
            <div class="no-print controls">
                <form id="grn-form" method="POST" action="">
                    <input type="hidden" name="order_location" value="<?php echo htmlspecialchars($_SESSION['order_location']); ?>">
                    <input type="hidden" name="save_grn" value="1">
                    <button type="submit" name="clear" class="btn-danger">Clear All</button>
                    <button type="button" onclick="printGRN()" class="btn-warning" id="print-btn" <?php echo empty($_SESSION['checked_by']) ? 'disabled' : ''; ?>>Print GRN</button>
                </form>
            </div>
            
            <?php if (empty($_SESSION['checked_by'])): ?>
                <div class="no-print responsible-form">
                    <h3>Verify Responsible Person</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="responsible_name">Responsible Person</label>
                            <select name="responsible_name" id="responsible_name" required>
                                <option value="">Select Responsible Person</option>
                                <?php
                                $result = $conn->query("SELECT name FROM logistics_users");
                                if ($result === false) {
                                    error_log("Query failed: " . $conn->error);
                                    echo "<option value=''>Error loading responsible persons</option>";
                                } else {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='" . htmlspecialchars($row['name']) . "'>" . htmlspecialchars($row['name']) . "</option>";
                                    }
                                    $result->free();
                                }
                                ?>
                            </select>
                        </div>
                        <input type="password" name="responsible_password" placeholder="Password" required>
                        <button type="submit" name="verify_responsible" class="btn-primary">Verify</button>
                    </form>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div id="print-error" class="no-print alert" style="display: none;">Print failed. Please check printer settings.</div>
    </div>

    <script src="../assets/js/qz-tray.js"></script>
    <script>
        console.log("Initial Session Data:", <?php echo json_encode($_SESSION); ?>);
        if (typeof qz !== 'undefined') {
            qz.security.setCertificatePromise(function(resolve, reject) {
                console.log("Setting QZ Tray certificate...");
                resolve();
            });
        } else {
            console.error("QZ Tray not loaded.");
        }

        $(document).ready(function() {
            let selectedItem = false;
            $("#item-search").autocomplete({
                source: function(request, response) {
                    console.log("Fetching autocomplete suggestions for term:", request.term);
                    $.ajax({
                        url: "<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>",
                        dataType: "json",
                        data: { term: request.term },
                        success: function(data) {
                            console.log("Autocomplete data received:", data);
                            if (data.error) {
                                alert("Error fetching suggestions: " + data.error);
                                response([]);
                            } else {
                                response(data);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Autocomplete AJAX error: ", xhr.responseText);
                            alert("Error fetching item suggestions: " + xhr.responseText);
                            response([]);
                        }
                    });
                },
                minLength: 2,
                select: function(event, ui) {
                    console.log("Item selected:", ui.item);
                    $("#item-search").val(ui.item.value);
                    $("#item-unit").val(ui.item.unit);
                    selectedItem = true;
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $("#item-search").val('');
                        $("#item-unit").val('');
                        selectedItem = false;
                    }
                }
            });

            $("#item-search").on('input', function() {
                selectedItem = false;
                $("#item-unit").val('');
            });

            $("#add-item-form").on('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                    return false;
                }
                console.log("Submitting item form:", {
                    item_name: $("#item-search").val(),
                    quantity: $("#quantity").val(),
                    unit: $("#item-unit").val()
                });
                setTimeout(function() {
                    $("#item-search").val('');
                    $("#item-unit").val('');
                    $("#quantity").val('');
                    selectedItem = false;
                    $("#item-search").autocomplete("close");
                }, 100);
                return true;
            });

            $("#print-btn").on('click', function() {
                if ($(this).prop('disabled')) {
                    console.error("Print button is disabled. Check if responsible person is verified.");
                    alert("Please verify responsible person before printing.");
                } else {
                    console.log("Print button clicked, calling printGRN...");
                }
            });
        });

        function validateForm() {
            if (!selectedItem) {
                alert('Please select an item from the suggestions.');
                return false;
            }
            const quantity = document.getElementById('quantity').value;
            if (!quantity || quantity <= 0) {
                alert('Please enter a valid quantity.');
                return false;
            }
            return true;
        }

        function printGRN() {
            console.log("Starting printGRN...");
            const printError = document.getElementById('print-error');
            const printContentDiv = document.getElementById('print-content');
            const grnNo = document.getElementById('print_grn_no').textContent;
            const date = document.getElementById('print_date').textContent;
            const location = document.getElementById('print_location').textContent;
            const checkedBy = '<?php echo isset($_SESSION['checked_by']) ? addslashes(htmlspecialchars($_SESSION['checked_by'])) : ''; ?>';
            const receivedBy = '<?php echo addslashes(htmlspecialchars($logged_in_user)); ?>';
            const tableClone = document.getElementById('print_items').cloneNode(true);
            const rows = tableClone.getElementsByTagName('tr');
            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                if (cells.length === 4) {
                    cells[3].remove();
                }
            }
            const itemsHtml = tableClone.innerHTML;
            console.log("Print data:", { grnNo, date, location, checkedBy, itemsHtml, receivedBy });
            if (!grnNo || grnNo === 'Pending') {
                console.error("Invalid GRN number.");
                alert("Error: GRN number not generated. Please verify responsible person.");
                return;
            }
            if (!itemsHtml || itemsHtml.includes("No items added")) {
                console.error("No items to print.");
                alert("Error: No items added to GRN.");
                return;
            }
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
                            line-height: 1.6;
                            color: #000;
                            background: #fff;
                            width: 80mm;
                            margin: 0;
                            padding: 0;
                        }
                        .grn-container {
                            width: 80mm;
                            margin: 0 auto;
                            padding: 0;
                            text-align: left;
                        }
                        .grn-header {
                            margin: 0 0 5mm 0;
                            padding: 0 0 3mm 0;
                            text-align: left;
                        }
                        .grn-header h2 {
                            font-size: 12pt;
                            font-weight: bold;
                            margin-bottom: 4mm;
                            text-transform: uppercase;
                            text-align: center;
                            border-bottom: 1.5px solid #000;
                        }
                        .grn-header div {
                            font-size: 9pt;
                            font-weight: bold;
                            display: flex;
                            align-items: center;
                            margin-bottom: 3mm;
                        }
                        .grn-header div strong {
                            display: inline-block;
                            min-width: 20mm;
                            text-align: left;
                        }
                        .grn-header div .colon {
                            display: inline-block;
                            width: 2mm;
                            text-align: center;
                        }
                        .grn-header div span {
                            display: inline-block;
                            margin-left: 1mm;
                        }
                        .grn-table {
                            width: 80mm;
                            border-collapse: collapse;
                            margin: 0;
                            padding: 0;
                            font-size: 9pt;
                            table-layout: fixed;
                            text-align: left;
                        }
                        .grn-table tr {
                            margin-bottom: 3mm;
                        }
                        .grn-table th {
                            border-top: 1.5px solid #000;
                            border-bottom: 1.5px solid #000;
                            padding: 2mm;
                            text-align: left;
                            font-weight: bold;
                        }
                        .grn-table td {
                            padding: 2mm;
                            border-bottom: 0.5px solid #000;
                            text-align: left;
                            font-weight: bold;
                        }
                        .grn-table th:first-child, .grn-table td:first-child {
                            width: 50%;
                            word-wrap: break-word;
                            white-space: normal;
                        }
                        .grn-table th:nth-child(2), .grn-table td:nth-child(2) {
                            width: 25%;
                            text-align: center;
                        }
                        .grn-table th:nth-child(3), .grn-table td:nth-child(3) {
                            width: 25%;
                            text-align: center;
                        }
                        .grn-table tr:last-child td {
                            border-bottom: 1.5px solid #000;
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
                            font-size: 9pt;
                            white-space: nowrap;
                            margin-bottom: 3mm;
                        }
                        .signature-line {
                            border-top: 1.5px solid #000;
                            margin-top: 6mm;
                            padding-top: 2mm;
                        }
                        .grn-footer {
                            margin: 0 0 5mm 0;
                            padding: 3mm 0 0 0;
                            border-top: 1px dashed #000;
                            font-size: 9pt;
                            text-align: center;
                            font-weight: bold;
                        }
                    </style>
                </head>
                <body>
                    <div class='grn-container'>
                        <div class='grn-header'>
                            <h2>Logistic <br>Goods Received Note</h2>
                            <div><strong>GRN No</strong><span class='colon'>: </span><span>${grnNo}</span></div>
                            <div><strong>Date</strong><span class='colon'>: </span><span>${date}</span></div>
                            <div><strong>Location</strong><span class='colon'>: </span><span>${location}</span></div>
                        </div>
                        <table class='grn-table'>
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Unit</th>
                                </tr>
                            </thead>
                            <tbody>${itemsHtml}</tbody>
                        </table>
                        <div class='signatures'>
                            <div class='signature-box'>
                                <div>Received By:</div>
                                <div>${receivedBy}</div>
                                <div class='signature-line'></div>
                            </div>
                            <div class='signature-box'>
                                <div>Checked By:</div>
                                <div>${checkedBy}</div>
                                <div class='signature-line'></div>
                            </div>
                        </div>
                        <div class='grn-footer'>System Generated!</div>
                    </div>
                </body>
                </html>
            `;
            console.log("Print content prepared:", printContent.substring(0, 200) + "...");
            printContentDiv.innerHTML = printContent;
            setTimeout(function () {
                if (typeof qz === 'undefined') {
                    console.error("❌ QZ Tray JS library not loaded. Falling back to window.print().");
                    printError.style.display = 'block';
                    window.print();
                    setTimeout(() => {
                        console.log("Hiding print error after 5 seconds.");
                        printError.style.display = 'none';
                        console.log("Submitting form after window.print.");
                        document.getElementById('grn-form').submit();
                        setTimeout(() => {
                            console.log("Redirecting to logistics_grn_system.php after window.print.");
                            window.location.href = 'logistics_grn_system.php';
                        }, 1000);
                    }, 5000);
                    return;
                }
                console.log("Attempting QZ Tray connection...");
                qz.websocket.connect().then(function () {
                    console.log("✅ QZ Tray connected");
                    return qz.printers.find("POSPrinter POS-80C").catch(function(err) {
                        console.error("Printer not found: ", err);
                        printError.style.display = 'block';
                        alert("Printer not found. Check QZ Tray settings. Saving to database.");
                        setTimeout(() => {
                            console.log("Hiding print error after 5 seconds.");
                            printError.style.display = 'none';
                            console.log("Submitting form after printer not found.");
                            document.getElementById('grn-form').submit();
                            setTimeout(() => {
                                console.log("Redirecting to logistics_grn_system.php after printer not found.");
                                window.location.href = 'logistics_grn_system.php';
                            }, 1000);
                        }, 5000);
                        throw err;
                    });
                }).then(function (printer) {
                    console.log("🖨️ Printer found: " + printer);
                    const config = qz.configs.create(printer, {
                        margins: { top: 0, right: 0, bottom: 0, left: 0 },
                        size: { width: 80, height: 'auto' },
                        units: 'mm'
                    });
                    console.log("Print config created:", config);
                    const data = [{
                        type: 'html',
                        format: 'plain',
                        data: printContent
                    }];
                    console.log("Sending print job...");
                    return qz.print(config, data);
                }).then(function () {
                    console.log("✅ Print job sent");
                    console.log("Submitting form after successful print.");
                    document.getElementById('grn-form').submit();
                    setTimeout(() => {
                        console.log("Redirecting to logistics_grn_system.php after successful print.");
                        window.location.href = 'logistics_grn_system.php';
                    }, 1000);
                    return qz.websocket.disconnect();
                }).catch(function (err) {
                    console.error("❌ QZ Tray error: ", err);
                    printError.style.display = 'block';
                    alert("Print failed: " + err.message + ". Saving to database.");
                    setTimeout(() => {
                        console.log("Hiding print error after 5 seconds.");
                        printError.style.display = 'none';
                        console.log("Submitting form after print error.");
                        document.getElementById('grn-form').submit();
                        setTimeout(() => {
                            console.log("Redirecting to logistics_grn_system.php after print error.");
                            window.location.href = 'logistics_grn_system.php';
                        }, 1000);
                    }, 5000);
                });
            }, 500);
        }
    </script>
</body>
</html>
<?php
$conn->close();
ob_end_flush();
?>