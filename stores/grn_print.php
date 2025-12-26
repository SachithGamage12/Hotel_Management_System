<?php
// Set locale for consistent decimal handling
setlocale(LC_NUMERIC, 'C');

// Start session
session_start();

// Set timezone to Sri Lanka Standard Time (SLT)
date_default_timezone_set('Asia/Colombo');

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
if (!isset($_SESSION['grn_datetime'])) {
    $_SESSION['grn_datetime'] = date("Y-m-d\TH:i"); // Default to current time
}

// Clear session data on page refresh (GET requests), but not on AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['term'])) {
    error_log("Clearing session on GET request");
    $_SESSION['grn_items'] = [];
    $_SESSION['checked_by'] = '';
    $_SESSION['grn_number'] = '';
    $_SESSION['grn_datetime'] = date("Y-m-d\TH:i");
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

// Handle order location and date/time selection
if (isset($_POST['order_location']) && !isset($_POST['add_item']) && !isset($_POST['remove_item'])) {
    $_SESSION['order_location'] = in_array($_POST['order_location'], ['HGG Bar & Restaurent','HGG Main Kitchen','HGG SKY Restaurent','HGG Butchery','HGG Stores','HGG Red Hall','HGG Banquet Hall','HGG Orcheit Hall','HGG Grand Ball Room', 'Sapthapadhi']) ? $_POST['order_location'] : 'HGG';
    if (isset($_POST['grn_datetime']) && !empty($_POST['grn_datetime'])) {
        try {
            $selected_datetime = new DateTime($_POST['grn_datetime'], new DateTimeZone('Asia/Colombo'));
            $_SESSION['grn_datetime'] = $selected_datetime->format('Y-m-d\TH:i');
            error_log("Selected GRN datetime: " . $_SESSION['grn_datetime']);
        } catch (Exception $e) {
            $error_message = "Invalid date and time selected.";
            error_log("Invalid GRN datetime: " . $e->getMessage());
        }
    }
    error_log("Location changed to: " . $_SESSION['order_location']);
}

// Handle adding items
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_name'], $_POST['quantity'], $_POST['unit'], $_POST['unit_price'], $_POST['add_item'])) {
    $item_name = trim($_POST['item_name']);
    $quantity_input = trim($_POST['quantity']);
    $unit = trim($_POST['unit']);
    $unit_price_input = trim($_POST['unit_price']);
   
    error_log("Received item data: item_name=$item_name, quantity=$quantity_input, unit=$unit, unit_price=$unit_price_input");
   
    // Validate quantity
    if (!is_numeric($quantity_input) || floatval($quantity_input) <= 0) {
        $error_message = "Invalid quantity. Please enter a positive number.";
        error_log("Invalid quantity: $quantity_input");
    } 
    // Validate unit price
    elseif (!is_numeric($unit_price_input) || floatval($unit_price_input) < 0) {
        $error_message = "Invalid unit price. Please enter a non-negative number.";
        error_log("Invalid unit price: $unit_price_input");
    } else {
        $quantity = floatval($quantity_input);
        $unit_price = floatval($unit_price_input);
        error_log("Converted: quantity=$quantity, unit_price=$unit_price");
       
        $stmt = $conn->prepare("SELECT item_name FROM inventory WHERE item_name = ? AND unit = ?");
        if ($stmt === false) {
            error_log("Prepare failed for item validation: " . $conn->error);
            $error_message = "Database error: Unable to verify item.";
        } else {
            $stmt->bind_param("ss", $item_name, $unit);
            $stmt->execute();
            $result = $stmt->get_result();
           
            if ($result->num_rows > 0) {
                $item_found = false;
                foreach ($_SESSION['grn_items'] as $index => $item) {
                    if ($item['item_name'] === $item_name && $item['unit'] === $unit) {
                        $_SESSION['grn_items'][$index]['quantity'] += $quantity;
                        $_SESSION['grn_items'][$index]['unit_price'] = $unit_price;
                        $item_found = true;
                        error_log("Updated existing item: $item_name, qty={$quantity}, price={$unit_price}");
                        break;
                    }
                }
                if (!$item_found) {
                    $_SESSION['grn_items'][] = [
                        'item_name' => $item_name,
                        'quantity' => $quantity,
                        'unit' => $unit,
                        'unit_price' => $unit_price
                    ];
                    error_log("Added new item: $item_name, qty=$quantity, price=$unit_price");
                }
            } else {
                $error_message = "Invalid item or unit.";
                error_log("Item validation failed: $item_name, $unit");
            }
            $stmt->close();
        }
    }
}

// Handle removing items
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $index = (int)$_POST['remove_item'];
    if (isset($_SESSION['grn_items'][$index])) {
        $removed_item = $_SESSION['grn_items'][$index];
        unset($_SESSION['grn_items'][$index]);
        $_SESSION['grn_items'] = array_values($_SESSION['grn_items']);
        error_log("Removed item at index $index: " . print_r($removed_item, true));
    } else {
        $error_message = "Invalid item index.";
    }
}

// Handle clearing items
if (isset($_POST['clear'])) {
    $_SESSION['grn_items'] = [];
    $_SESSION['checked_by'] = '';
    $_SESSION['grn_number'] = '';
    $_SESSION['grn_datetime'] = date("Y-m-d\TH:i");
    error_log("Session cleared via Clear All");
}

// Handle saving GRN to database after print
if (isset($_POST['save_grn']) && !empty($_SESSION['grn_items']) && !empty($_SESSION['grn_number'])) {
    error_log("GRN items before saving: " . print_r($_SESSION['grn_items'], true));
    $grn_number = $_SESSION['grn_number'];
    $date = (new DateTime($_SESSION['grn_datetime'], new DateTimeZone('Asia/Colombo')))->format("Y-m-d H:i:s");
    $location = $_SESSION['order_location'];
    $received_by = $logged_in_user;
    $checked_by = $_SESSION['checked_by'] ?? '';
   
    $stmt = $conn->prepare("INSERT INTO grn_records (grn_number, date, location, received_by, checked_by) VALUES (?, ?, ?, ?, ?)");
    if ($stmt === false) {
        error_log("Prepare failed for GRN insert: " . $conn->error);
        $error_message = "Database error: Unable to save GRN.";
    } else {
        $stmt->bind_param("sssss", $grn_number, $date, $location, $received_by, $checked_by);
       
        if ($stmt->execute()) {
            $grn_id = $conn->insert_id;
           
            $item_stmt = $conn->prepare("INSERT INTO grn_items (grn_id, item_name, quantity, unit, unit_price) VALUES (?, ?, ?, ?, ?)");
            if ($item_stmt === false) {
                error_log("Prepare failed for GRN items insert: " . $conn->error);
                $error_message = "Database error: Unable to save GRN items.";
            } else {
                foreach ($_SESSION['grn_items'] as $item) {
                    $item_name = $item['item_name'];
                    $quantity = floatval($item['quantity']);
                    $unit = $item['unit'];
                    $unit_price = floatval($item['unit_price'] ?? 0);

                    $item_stmt->bind_param("isdsd", $grn_id, $item_name, $quantity, $unit, $unit_price);
                    if (!$item_stmt->execute()) {
                        error_log("Failed to save item: " . $item_stmt->error);
                        $error_message = "Error saving item: " . $item_stmt->error;
                    }
                }
                $item_stmt->close();
               
                if (!isset($error_message)) {
                    $_SESSION['grn_items'] = [];
                    $_SESSION['checked_by'] = '';
                    $_SESSION['grn_number'] = '';
                    $success_message = "GRN $grn_number saved successfully!";
                }
            }
        } else {
            $error_message = "Error saving GRN: " . $stmt->error;
            error_log("GRN insert failed: " . $stmt->error);
        }
        $stmt->close();
    }
}

$grn_number = !empty($_SESSION['grn_number']) ? $_SESSION['grn_number'] : 'Pending';
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
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Arial', sans-serif; }
        body { font-size: 8pt; line-height: 1.5; width: 100%; margin: 0; padding: 0; color: #000; background: #fff; min-height: 150mm; display: flex; justify-content: center; }
        .wrapper { width: 80mm; margin: 0 auto; position: relative; }
        .grn-container { width: 80mm; padding: 3mm; text-align: left; }
        .grn-header { margin: 0 0 5mm 0; padding: 0 0 3mm 0; }
        .grn-header h2 { font-size: 11pt; font-weight: bold; margin-bottom: 4mm; text-transform: uppercase; text-align: center; border-bottom: 1.5px solid #000; }
        .grn-header div { font-size: 8pt; font-weight: bold; display: flex; align-items: center; margin-bottom: 3mm; }
        .grn-header div strong { display: inline-block; min-width: 20mm; text-align: left; }
        .grn-header div .colon { display: inline-block; width: 2mm; text-align: center; }
        .grn-header div span { display: inline-block; margin-left: 1mm; }
        .grn-table { width: 100%; border-collapse: collapse; margin: 0 0 5mm 0; font-size: 8pt; text-align: left; }
        .grn-table th { border-top: 1.5px solid #000; border-bottom: 1.5px solid #000; padding: 2mm; text-align: left; font-weight: bold; }
        .grn-table th:nth-child(2), .grn-table th:nth-child(3), .grn-table th:nth-child(4) { width: 16%; text-align: center; }
        .grn-table td { padding: 2mm; border-bottom: 0.5px solid #000; vertical-align: top; font-weight: bold; }
        .grn-table td:first-child { width: 52%; word-wrap: break-word; white-space: normal; }
        .grn-table td:nth-child(2), .grn-table td:nth-child(3), .grn-table td:nth-child(4) { text-align: center; white-space: nowrap; }
        .grn-table tr:last-child td { border-bottom: 1.5px solid #000; }
        .remove-btn { padding: 1mm 3mm; font-size: 8pt; cursor: pointer; background: #f2dede; border: 1px solid #a94442; color: #a94442; font-weight: bold; }
        .signatures { display: flex; justify-content: space-between; margin: 0 0 5mm 0; font-size: 8pt; text-align: left; }
        .signature-box { width: 48%; min-width: 35mm; text-align: center; padding: 3mm; overflow: visible; }
        .signature-box div { font-weight: bold; font-size: 8pt; white-space: nowrap; margin-bottom: 3mm; }
        .signature-line { border-top: 1.5px solid #000; margin-top: 6mm; padding-top: 2mm; }
        .grn-footer { text-align: center; font-size: 8pt; font-weight: bold; margin-top: 5mm; padding-top: 3mm; border-top: 1px dashed #000; }
        .controls, .form-container, .user-info, .alert, .responsible-form { text-align: center; margin: 0 0 5mm 0; padding-top: 3mm; border-top: 2px dashed #000; width: 80mm; }
        .controls button, .form-container button, .responsible-form button { padding: 2mm 4mm; margin: 0 2mm; font-size: 8pt; cursor: pointer; background: #f0f0f0; border: 1px solid #000; font-weight: bold; }
        .controls button:disabled { background: #ccc; cursor: not-allowed; }
        .form-container { background: white; padding: 3mm; margin-bottom: 5mm; }
        .form-group { margin-bottom: 3mm; }
        label { display: block; margin-bottom: 1mm; font-weight: bold; font-size: 8pt; }
        select, input[type="text"], input[type="password"], input[type="datetime-local"] { width: 100%; padding: 2mm; border: 1px solid #000; font-size: 8pt; box-sizing: border-box; }
        select:focus, input[type="text"]:focus, input[type="password"]:focus, input[type="datetime-local"]:focus { border-color: #000; outline: none; }
        .alert { background: #f2dede; color: #a94442; padding: 2mm; margin: 0 0 4mm 0; font-size: 8pt; border: 1px solid #a94442; font-weight: bold; text-align: left; }
        .responsible-form { background: #f5f5f5; padding: 3mm; margin-top: 5mm; }
        .responsible-form h3 { margin-bottom: 2mm; font-size: 9pt; }
        .back-button { position: fixed; top: 20px; left: 20px; z-index: 1000; background-color: #f09424ff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        @page { size: 80mm auto; margin: 0; }
        @media print {
            body { width: 80mm; margin: 0; padding: 0; font-size: 9pt; line-height: 1.6; color: #000 !important; background: #fff !important; min-height: 0; display: block; }
            .wrapper { width: 80mm; margin: 0 auto; }
            .grn-container { width: 80mm; margin: 0 auto; padding: 0; text-align: left; }
            .grn-header h2 { font-size: 12pt; margin-bottom: 4mm; border-bottom: 1.5px solid #000; text-align: center; }
            .grn-header div { font-size: 9pt; margin-bottom: 3mm; }
            .grn-table { width: 80mm; border-collapse: collapse; margin: 0; padding: 0; font-size: 9pt; table-layout: fixed; text-align: left; }
            .grn-table th, .grn-table td { padding: 2mm; border-bottom: 0.5px solid #000; text-align: left; font-weight: bold; }
            .grn-table th:first-child, .grn-table td:first-child { width: 45%; word-wrap: break-word; white-space: normal; }
            .grn-table th:nth-child(2), .grn-table td:nth-child(2) { width: 15%; text-align: center; }
            .grn-table th:nth-child(3), .grn-table td:nth-child(3) { width: 15%; text-align: center; }
            .grn-table th:nth-child(4), .grn-table td:nth-child(4) { width: 20%; text-align: center; }
            .grn-table th:nth-child(5), .grn-table td:nth-child(5) { display: none; }
            .grn-table tr:last-child td { border-bottom: 1.5px solid #000; }
            .signatures { margin: 0 0 5mm 0; text-align: left; }
            .signature-box { padding: 3mm; font-size: 9pt; }
            .signature-line { margin-top: 6mm; padding-top: 2mm; }
            .grn-footer { margin: 0 0 5mm 0; padding: 3mm 0 0 0; font-size: 9pt; text-align: center; }
            .controls, .form-container, .user-info, .alert, .responsible-form, .no-print, .remove-btn, .back-button { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <button onclick="window.location.href='../stores.php'" class="back-button no-print">Back</button>
        <div class="no-print">
            <?php if (!empty($logged_in_user)): ?>
                <div class="user-info">Logged in as: <?php echo htmlspecialchars($logged_in_user); ?></div>
            <?php endif; ?>
           
            <div class="form-container">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="order_location">Location</label>
                        <select name="order_location" id="order_location">
                            <option value="HGG" <?php echo $_SESSION['order_location'] === 'HGG' ? 'selected' : ''; ?>>HGG</option>
                            <option value="HGG Bar & Restaurent" <?php echo $_SESSION['order_location'] === 'HGG Bar & Restaurent' ? 'selected' : ''; ?>>HGG Bar & Restaurent</option>
                            <option value="HGG Main Kitchen" <?php echo $_SESSION['order_location'] === 'HGG Main Kitchen' ? 'selected' : ''; ?>>HGG Main Kitchen</option>
                            <option value="HGG SKY Restaurent" <?php echo $_SESSION['order_location'] === 'HGG SKY Restaurent' ? 'selected' : ''; ?>>HGG SKY Restaurent</option>
                            <option value="HGG Butchery" <?php echo $_SESSION['order_location'] === 'HGG Butchery' ? 'selected' : ''; ?>>HGG Butchery</option>
                            <option value="HGG Stores" <?php echo $_SESSION['order_location'] === 'HGG Stores' ? 'selected' : ''; ?>>HGG Stores</option>
                            <option value="HGG Red Hall" <?php echo $_SESSION['order_location'] === 'HGG Red Hall' ? 'selected' : ''; ?>>HGG Red Hall</option>
                            <option value="HGG Banquet Hall" <?php echo $_SESSION['order_location'] === 'HGG Banquet Hall' ? 'selected' : ''; ?>>HGG Banquet Hall</option>
                            <option value="HGG Orcheit Hall" <?php echo $_SESSION['order_location'] === 'HGG Orcheit Hall' ? 'selected' : ''; ?>>HGG Orcheit Hall</option>
                            <option value="HGG Grand Ball Room" <?php echo $_SESSION['order_location'] === 'HGG Grand Ball Room' ? 'selected' : ''; ?>>HGG Grand Ball Room</option>
                            <option value="Sapthapadhi" <?php echo $_SESSION['order_location'] === 'Sapthapadhi' ? 'selected' : ''; ?>>Sapthapadhi</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="grn_datetime">GRN Date and Time (SLT)</label>
                        <input type="datetime-local" name="grn_datetime" id="grn_datetime" value="<?php echo htmlspecialchars($_SESSION['grn_datetime']); ?>" required>
                    </div>
                    <button type="submit" class="btn-primary">Update</button>
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
                        <input type="text" id="quantity" name="quantity" placeholder="e.g. 1.5" pattern="\d*\.?\d+" title="Enter a valid number" required>
                    </div>
                    <div class="form-group">
                        <label for="unit_price">Unit Price (Rs.)</label>
                        <input type="text" id="unit_price" name="unit_price" placeholder="e.g. 125.50" pattern="\d*\.?\d*" title="Enter a valid price" required>
                    </div>
                    <button type="submit" class="btn-primary">Add Item</button>
                </form>
            </div>
        </div>

        <div class="grn-container">
            <div class="grn-header">
                <h2>Goods Received Note</h2>
                <div><strong>GRN No</strong><span class="colon">: </span><span id="print_grn_no"><?php echo htmlspecialchars($grn_number); ?></span></div>
                <div><strong>Date</strong><span class="colon">: </span><span id="print_date"><?php echo (new DateTime($_SESSION['grn_datetime'], new DateTimeZone('Asia/Colombo')))->format('d-M-Y H:i'); ?></span></div>
                <div><strong>Location</strong><span class="colon">: </span><span id="print_location"><?php echo htmlspecialchars($_SESSION['order_location']); ?></span></div>
            </div>
            <table class="grn-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Price (Rs.)</th>
                        <th class="no-print">Action</th>
                    </tr>
                </thead>
                <tbody id="print_items">
                    <?php if (empty($_SESSION['grn_items'])): ?>
                        <tr><td colspan="5">No items added.</td></tr>
                    <?php else: ?>
                        <?php foreach ($_SESSION['grn_items'] as $index => $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                <td><?php echo (floor($item['quantity']) == $item['quantity']) ? (int)$item['quantity'] : number_format($item['quantity'], 3); ?></td>
                                <td><?php echo strtoupper(htmlspecialchars($item['unit'])); ?></td>
                                <td><?php echo number_format($item['unit_price'], 2); ?></td>
                                <td class="no-print">
                                    <form method="POST" action="" style="display:inline;">
                                        <input type="hidden" name="remove_item" value="<?php echo $index; ?>">
                                        <input type="hidden" name="order_location" value="<?php echo htmlspecialchars($_SESSION['order_location']); ?>">
                                        <button type="submit" class="remove-btn">Remove</button>
                                    </form>
                                </td>
                            </tr>
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
                    <div><?php echo !empty($_SESSION['checked_by']) ? htmlspecialchars($_SESSION['checked_by']) : ''; ?></div>
                    <div class="signature-line"></div>
                </div>
            </div>
            <div class="grn-footer">System Generated!</div>
        </div>

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
                                if ($result) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='" . htmlspecialchars($row['name']) . "'>" . htmlspecialchars($row['name']) . "</option>";
                                    }
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

    <script>
        let isPrinting = false;
        $(document).ready(function() {
            let selectedItem = false;
            $("#item-search").autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: "<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>",
                        dataType: "json",
                        data: { term: request.term },
                        success: function(data) { response(data.error ? [] : data); },
                        error: function() { response([]); }
                    });
                },
                minLength: 2,
                select: function(event, ui) {
                    $("#item-search").val(ui.item.value);
                    $("#item-unit").val(ui.item.unit);
                    selectedItem = true;
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) { $("#item-search").val(''); $("#item-unit").val(''); selectedItem = false; }
                }
            });

            $("#add-item-form").on('submit', function() {
                if (!validateForm()) return false;
                setTimeout(() => { $("#item-search,#quantity,#unit_price").val(''); $("#item-unit").val(''); selectedItem = false; }, 100);
            });
        });

        function validateForm() {
            if (!$("#item-unit").val()) { alert('Please select a valid item.'); return false; }
            if (!isValidDecimal($("#quantity").val())) { alert('Enter valid quantity.'); return false; }
            if (!isValidDecimal($("#unit_price").val(), true)) { alert('Enter valid unit price.'); return false; }
            return true;
        }
        function isValidDecimal(val, allowZero = false) {
            const n = parseFloat(val);
            return !isNaN(n) && n > (allowZero ? -1 : 0);
        }

        function printGRN() {
            if (isPrinting) return;
            isPrinting = true;

            const grnNo = document.getElementById('print_grn_no').textContent;
            const date = '<?php echo (new DateTime($_SESSION['grn_datetime'], new DateTimeZone('Asia/Colombo')))->format('d-M-Y H:i'); ?>';
            const location = document.getElementById('print_location').textContent;
            const checkedBy = '<?php echo addslashes(htmlspecialchars($_SESSION['checked_by'] ?? '')); ?>';
            const receivedBy = '<?php echo addslashes(htmlspecialchars($logged_in_user)); ?>';

            const tableClone = document.getElementById('print_items').cloneNode(true);
            for (let row of tableClone.rows) {
                if (row.cells.length === 5) row.deleteCell(4); // remove Action
            }

            const printContent = `<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
                body{font-family:Arial;font-size:9pt;width:80mm;margin:0;padding:0;color:#000;background:#fff}
                .grn-container{width:80mm;margin:0 auto;padding:0}
                .grn-header h2{font-size:12pt;text-align:center;border-bottom:1.5px solid #000;margin-bottom:4mm}
                .grn-header div{font-size:9pt;font-weight:bold;margin-bottom:3mm;display:flex}
                .grn-header strong{min-width:20mm}
                .grn-table{width:100%;border-collapse:collapse;font-size:9pt}
                .grn-table th,.grn-table td{padding:2mm;border-bottom:0.5px solid #000;font-weight:bold}
                .grn-table th:first-child,.grn-table td:first-child{width:45%;word-wrap:break-word}
                .grn-table th:nth-child(2),.td:nth-child(2),.grn-table th:nth-child(3),.td:nth-child(3),.grn-table th:nth-child(4),.td:nth-child(4){width:18%;text-align:center}
                .grn-table tr:last-child td{border-bottom:1.5px solid #000}
                .signatures{display:flex;justify-content:space-between;margin:5mm 0}
                .signature-box{width:48%;text-align:center;padding:3mm}
                .signature-line{border-top:1.5px solid #000;margin-top:6mm}
                .grn-footer{text-align:center;font-weight:bold;margin-top:5mm;padding-top:3mm;border-top:1px dashed #000}
            </style></head><body>
                <div class="grn-container">
                    <div class="grn-header">
                        <h2>Goods Received Note</h2>
                        <div><strong>GRN No</strong><span>: </span><span>${grnNo}</span></div>
                        <div><strong>Date</strong><span>: </span><span>${date}</span></div>
                        <div><strong>Location</strong><span>: </span><span>${location}</span></div>
                    </div>
                    <table class="grn-table">
                        <thead><tr><th>Item</th><th>Qty</th><th>Unit</th><th>Unit Price (Rs.)</th></tr></thead>
                        <tbody>${tableClone.innerHTML}</tbody>
                    </table>
                    <div class="signatures">
                        <div class="signature-box"><div>Received By:</div><div>${receivedBy}</div><div class="signature-line"></div></div>
                        <div class="signature-box"><div>Checked By:</div><div>${checkedBy}</div><div class="signature-line"></div></div>
                    </div>
                    <div class="grn-footer">System Generated!</div>
                </div>
            </body></html>`;

            const win = window.open('', '_blank', 'width=80mm,height=auto');
            if (!win) { alert("Popup blocked!"); isPrinting = false; return; }
            win.document.write(printContent);
            win.document.close();
            win.onload = () => win.print();
            win.onafterprint = () => {
                setTimeout(() => { win.close(); document.getElementById('grn-form').submit(); }, 500);
                setTimeout(() => { window.location.href = 'grn_print.php'; isPrinting = false; }, 1500);
            };
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>