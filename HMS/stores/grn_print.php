<?php
// Start session
session_start();

// Set timezone to Indian Standard Time (IST)
date_default_timezone_set('Asia/Kolkata');

// Prevent browser caching
header("Cache-Control: no-cache, must-revalidate");

// Database connection settings
$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed. Please check server logs.");
}

// Get logged in username from session
$logged_in_user = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// Initialize session variables if not set
if (!isset($_SESSION['grn_items'])) {
    $_SESSION['grn_items'] = [];
}
if (!isset($_SESSION['order_location'])) {
    $_SESSION['order_location'] = 'HGG';
}
if (!isset($_SESSION['checked_by'])) {
    $_SESSION['checked_by'] = '';
}
if (!isset($_SESSION['grn_number'])) {
    $_SESSION['grn_number'] = '';
}

// Clear session data on page refresh (GET requests), but not on AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['term'])) {
    error_log("Clearing session on GET request");
    $_SESSION['grn_items'] = [];
    $_SESSION['checked_by'] = '';
    $_SESSION['grn_number'] = '';
}

// Handle responsible person authentication
$checked_by = '';
$error_message = '';
if (isset($_POST['verify_responsible'])) {
    $responsible_name = trim($_POST['responsible_name']);
    $responsible_password = $_POST['responsible_password'];
    
    $stmt = $conn->prepare("SELECT * FROM responsible WHERE name = ?");
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
                
                // Generate GRN number after verification
                $stmt = $conn->prepare("SELECT MAX(CAST(SUBSTRING(grn_number, 5) AS UNSIGNED)) as max_num FROM grn_records");
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

// Handle autocomplete search via AJAX
if (isset($_GET['term'])) {
    $term = '%' . $_GET['term'] . '%';
    $stmt = $conn->prepare("SELECT item_name, category, unit FROM inventory WHERE item_name LIKE ? OR category LIKE ? LIMIT 10");
    if ($stmt === false) {
        error_log("Prepare failed for autocomplete query: " . $conn->error);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Database query preparation failed: ' . $conn->error . '. Please ensure the inventory table exists with columns item_name, category, and unit.']);
        exit;
    }
    $stmt->bind_param("ss", $term, $term);
    $stmt->execute();
    $result = $stmt->get_result();
    $suggestions = [];
    
    while ($row = $result->fetch_assoc()) {
        $suggestions[] = [
            'label' => $row['item_name'] . ' (' . $row['category'] . ')',
            'value' => $row['item_name'],
            'unit' => $row['unit']
        ];
    }
    $stmt->close();
    header('Content-Type: application/json');
    echo json_encode($suggestions);
    exit;
}

// Handle order location selection
if (isset($_POST['order_location']) && !isset($_POST['add_item']) && !isset($_POST['remove_item'])) {
    $_SESSION['order_location'] = in_array($_POST['order_location'], ['HGG', 'Sapthapadhi']) ? $_POST['order_location'] : 'HGG';
    error_log("Location changed to: " . $_SESSION['order_location']);
}

// Handle adding items
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_name'], $_POST['quantity'], $_POST['unit'], $_POST['add_item'])) {
    $item_name = trim($_POST['item_name']);
    $quantity = (int)$_POST['quantity'];
    $unit = trim($_POST['unit']);
    
    error_log("Received item data: item_name=$item_name, quantity=$quantity, unit=$unit");
    
    $stmt = $conn->prepare("SELECT item_name FROM inventory WHERE item_name = ? AND unit = ?");
    if ($stmt === false) {
        error_log("Prepare failed for item validation: " . $conn->error);
        $error_message = "Database error: Unable to verify item.";
    } else {
        $stmt->bind_param("ss", $item_name, $unit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0 && $quantity > 0) {
            $item_found = false;
            foreach ($_SESSION['grn_items'] as $index => $item) {
                if ($item['item_name'] === $item_name && $item['unit'] === $unit) {
                    $_SESSION['grn_items'][$index]['quantity'] += $quantity;
                    $item_found = true;
                    error_log("Updated quantity for existing item: item_name=$item_name, new_quantity={$_SESSION['grn_items'][$index]['quantity']}, unit=$unit");
                    break;
                }
            }
            if (!$item_found) {
                $_SESSION['grn_items'][] = [
                    'item_name' => $item_name,
                    'quantity' => $quantity,
                    'unit' => $unit
                ];
                error_log("Added new item: item_name=$item_name, quantity=$quantity, unit=$unit");
            }
            error_log("Current session items: " . print_r($_SESSION['grn_items'], true));
        } else {
            $error_message = "Invalid item or quantity.";
            error_log("Item validation failed: item_name=$item_name, unit=$unit, quantity=$quantity");
        }
        $stmt->close();
    }
}

// Handle removing items
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $index = (int)$_POST['remove_item'];
    if (isset($_SESSION['grn_items'][$index])) {
        $removed_item = $_SESSION['grn_items'][$index];
        unset($_SESSION['grn_items'][$index]);
        $_SESSION['grn_items'] = array_values($_SESSION['grn_items']); // Reindex array
        error_log("Removed item at index $index: " . print_r($removed_item, true));
        error_log("Current session items after removal: " . print_r($_SESSION['grn_items'], true));
    } else {
        $error_message = "Invalid item index.";
        error_log("Failed to remove item at index $index: Index does not exist");
    }
}

// Handle clearing items
if (isset($_POST['clear'])) {
    $_SESSION['grn_items'] = [];
    $_SESSION['checked_by'] = '';
    $_SESSION['grn_number'] = '';
    error_log("Session cleared via Clear All");
}

// Handle saving GRN to database after print
// Handle saving GRN to database after print
if (isset($_POST['save_grn']) && !empty($_SESSION['grn_items']) && !empty($_SESSION['grn_number'])) {
    error_log("GRN items before saving: " . print_r($_SESSION['grn_items'], true));
    $grn_number = $_SESSION['grn_number'];
    $date = date("Y-m-d H:i:s");
    $location = $_SESSION['order_location'];
    $received_by = $logged_in_user;
    $checked_by = isset($_SESSION['checked_by']) ? $_SESSION['checked_by'] : '';
    
    $stmt = $conn->prepare("INSERT INTO grn_records (grn_number, date, location, received_by, checked_by) VALUES (?, ?, ?, ?, ?)");
    if ($stmt === false) {
        error_log("Prepare failed for GRN insert: " . $conn->error);
        $error_message = "Database error: Unable to save GRN.";
    } else {
        $stmt->bind_param("sssss", $grn_number, $date, $location, $received_by, $checked_by);
        
        if ($stmt->execute()) {
            $grn_id = $conn->insert_id;
            
            $item_stmt = $conn->prepare("INSERT INTO grn_items (grn_id, item_name, quantity, unit) VALUES (?, ?, ?, ?)");
            if ($item_stmt === false) {
                error_log("Prepare failed for GRN items insert: " . $conn->error);
                $error_message = "Database error: Unable to save GRN items.";
            } else {
                foreach ($_SESSION['grn_items'] as $index => $item) {
                    $item_name = $item['item_name'];
                    $quantity = (int)$item['quantity'];
                    $unit = $item['unit'];
                    
                    error_log("Saving item $index: item_name=$item_name, quantity=$quantity, unit=$unit");
                    
                    $item_stmt->bind_param("isis", $grn_id, $item_name, $quantity, $unit);
                    if (!$item_stmt->execute()) {
                        error_log("GRN items insert failed: " . $item_stmt->error);
                    }
                }
                $item_stmt->close();
                
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

$date = date("Y-m-d H:i:s");
$grn_number = isset($_SESSION['grn_number']) && !empty($_SESSION['grn_number']) ? $_SESSION['grn_number'] : 'Pending';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=80mm, initial-scale=1.0">
    <title>Goods Received Note</title>
    <link rel="icon" type="image/avif" href="../images/logo.avif">
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
    <button onclick="window.location.href='../stores.php'" 
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
                            <option value="HGG" <?php echo $_SESSION['order_location'] === 'HGG' ? 'selected' : ''; ?>>HGG</option>
                            <option value="Sapthapadhi" <?php echo $_SESSION['order_location'] === 'Sapthapadhi' ? 'selected' : ''; ?>>Sapthapadhi</option>
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
                        <input type="text" id="item-search" name="item_name" placeholder="Type item name or category" autocomplete="off" required>
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
                <h2>Goods Received Note</h2>
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
                            <?php error_log("Rendering item $index: item_name={$item['item_name']}, quantity={$item['quantity']}, unit={$item['unit']}"); ?>
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
                                $result = $conn->query("SELECT name FROM responsible");
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
        // Debugging: Log session data
        console.log("Initial Session Data:", <?php echo json_encode($_SESSION); ?>);

        // Ensure QZ Tray certificate is loaded (if required)
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
                            console.error("Autocomplete error: ", error);
                            alert("Error fetching item suggestions: " + error);
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

            // Clear form fields and reset autocomplete state after submission
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

            // Debug Print button click
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
            
            // Verify session data
            const grnNo = document.getElementById('print_grn_no').textContent;
            const date = document.getElementById('print_date').textContent;
            const location = document.getElementById('print_location').textContent;
            const checkedBy = '<?php echo isset($_SESSION['checked_by']) ? addslashes(htmlspecialchars($_SESSION['checked_by'])) : ''; ?>';
            const receivedBy = '<?php echo addslashes(htmlspecialchars($logged_in_user)); ?>';
            
            // Clone the table and remove the Action column
            const tableClone = document.getElementById('print_items').cloneNode(true);
            const rows = tableClone.getElementsByTagName('tr');
            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                if (cells.length === 4) {
                    cells[3].remove(); // Remove the Action column cell
                }
            }
            const itemsHtml = tableClone.innerHTML;
            
            console.log("Print data:", { grnNo, date, location, checkedBy, itemsHtml, receivedBy });
            console.log("Table rows for print:", itemsHtml);
            console.log("Container CSS:", {
                width: '80mm',
                margin: '0 auto',
                padding: '0',
                'text-align': 'left'
            });
            console.log("Table CSS:", {
                width: '80mm',
                'table-layout': 'fixed',
                'text-align': 'left',
                margin: '0',
                padding: '0'
            });
            console.log("Redirect URL:", 'grn_print.php');

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
                            <h2>Goods Received Note</h2>
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
            console.log("Table rows for print:", itemsHtml);
            console.log("Container CSS:", {
                width: '80mm',
                margin: '0 auto',
                padding: '0',
                'text-align': 'left'
            });
            console.log("Table CSS:", {
                width: '80mm',
                'table-layout': 'fixed',
                'text-align': 'left',
                margin: '0',
                padding: '0'
            });
            console.log("Redirect URL:", 'grn_print.php');

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
                            console.log("Redirecting to grn_print.php after window.print.");
                            window.location.href = 'grn_print.php';
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
                                console.log("Redirecting to grn_print.php after printer not found.");
                                window.location.href = 'grn_print.php';
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
                        console.log("Redirecting to grn_print.php after successful print.");
                        window.location.href = 'grn_print.php';
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
                            console.log("Redirecting to grn_print.php after print error.");
                            window.location.href = 'grn_print.php';
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
?>