<?php
// initial_stock.php
// Start output buffering to prevent stray output
ob_start();

// Start session
session_start();

// Set timezone to Sri Lanka Standard Time
date_default_timezone_set('Asia/Colombo');

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

// Set UTF-8 encoding
$conn->set_charset("utf8mb4");

// Get logged in username from session
$logged_in_user = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// Initialize session variables if not set
if (!isset($_SESSION['stock_items'])) {
    $_SESSION['stock_items'] = [];
}
if (!isset($_SESSION['stock_location'])) {
    $_SESSION['stock_location'] = 'Main Warehouse';
}

// Clear session data on page refresh (GET requests), but not on AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['term'])) {
    $_SESSION['stock_items'] = [];
}

// Handle autocomplete search via AJAX
if (isset($_GET['term'])) {
    // Clear any previous output
    ob_clean();
    
    $term = '%' . trim($_GET['term']) . '%';
    $stmt = $conn->prepare("SELECT name AS item_name, unit_type AS unit FROM items WHERE name LIKE ? LIMIT 10");
    if ($stmt === false) {
        error_log("Prepare failed for autocomplete query: " . $conn->error);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error' => 'Database query preparation failed: ' . $conn->error . '. Please ensure the items table exists with columns name and unit_type.'
        ]);
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
        // Ensure UTF-8 encoding for JSON compatibility
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

// Handle location selection
if (isset($_POST['stock_location']) && !isset($_POST['add_item']) && !isset($_POST['remove_item'])) {
    $_SESSION['stock_location'] = in_array($_POST['stock_location'], ['Main Warehouse', 'Secondary Warehouse']) ? $_POST['stock_location'] : 'Main Warehouse';
}

// Handle adding items
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_name'], $_POST['quantity'], $_POST['unit'], $_POST['add_item'])) {
    $item_name = trim($_POST['item_name']);
    $quantity = (int)$_POST['quantity'];
    $unit = trim($_POST['unit']);
    
    $stmt = $conn->prepare("SELECT name FROM items WHERE name = ? AND unit_type = ?");
    if ($stmt === false) {
        $error_message = "Database error: Unable to verify item.";
    } else {
        $stmt->bind_param("ss", $item_name, $unit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0 && $quantity > 0) {
            $item_found = false;
            foreach ($_SESSION['stock_items'] as $index => $item) {
                if ($item['item_name'] === $item_name && $item['unit'] === $unit) {
                    $_SESSION['stock_items'][$index]['quantity'] = $quantity; // Override for initial stock
                    $item_found = true;
                    break;
                }
            }
            if (!$item_found) {
                $_SESSION['stock_items'][] = [
                    'item_name' => $item_name,
                    'quantity' => $quantity,
                    'unit' => $unit
                ];
            }
        } else {
            $error_message = "Invalid item or quantity.";
        }
        $stmt->close();
    }
}

// Handle removing items
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $index = (int)$_POST['remove_item'];
    if (isset($_SESSION['stock_items'][$index])) {
        unset($_SESSION['stock_items'][$index]);
        $_SESSION['stock_items'] = array_values($_SESSION['stock_items']);
    }
}

// Handle clearing items
if (isset($_POST['clear'])) {
    $_SESSION['stock_items'] = [];
}

// Handle saving initial stock
if (isset($_POST['save_stock']) && !empty($_SESSION['stock_items'])) {
    $location = $_SESSION['stock_location'];
    
    foreach ($_SESSION['stock_items'] as $item) {
        $item_name = $item['item_name'];
        $quantity = (int)$item['quantity'];
        
        // Get item_id
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
            $id_stmt->close();
            continue;
        }
        $id_stmt->close();
        
        // Check if stock exists for this item and location
        $stock_stmt = $conn->prepare("SELECT id FROM item_stock WHERE item_id = ? AND location = ?");
        if ($stock_stmt === false) {
            error_log("Prepare failed for stock check: " . $conn->error);
            continue;
        }
        $stock_stmt->bind_param("is", $item_id, $location);
        $stock_stmt->execute();
        $stock_result = $stock_stmt->get_result();
        
        if ($stock_result->num_rows > 0) {
            // Update existing
            $update_stmt = $conn->prepare("UPDATE item_stock SET available_quantity = ?, last_added_quantity = 0, last_added_date = NULL, total_quantity = ? WHERE item_id = ? AND location = ?");
            if ($update_stmt === false) {
                error_log("Prepare failed for stock update: " . $conn->error);
                $stock_stmt->close();
                continue;
            }
            $update_stmt->bind_param("iiis", $quantity, $quantity, $item_id, $location);
            if (!$update_stmt->execute()) {
                error_log("Stock update failed: " . $update_stmt->error);
            }
            $update_stmt->close();
        } else {
            // Insert new
            $insert_stmt = $conn->prepare("INSERT INTO item_stock (item_id, location, available_quantity, last_added_quantity, last_added_date, total_quantity) VALUES (?, ?, ?, 0, NULL, ?)");
            if ($insert_stmt === false) {
                error_log("Prepare failed for stock insert: " . $conn->error);
                $stock_stmt->close();
                continue;
            }
            $insert_stmt->bind_param("isii", $item_id, $location, $quantity, $quantity);
            if (!$insert_stmt->execute()) {
                error_log("Stock insert failed: " . $insert_stmt->error);
            }
            $insert_stmt->close();
        }
        $stock_stmt->close();
    }
    
    $_SESSION['stock_items'] = [];
    $success_message = "Initial stock saved successfully!";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Initial Stock</title>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <style>
        /* Simplified styles for this page */
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-container { margin-bottom: 20px; }
        .form-group { margin-bottom: 10px; }
        label { display: block; }
        input, select { width: 100%; padding: 5px; }
        button { padding: 5px 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px; }
        .alert { color: red; }
    </style>
</head>
<body>
    <h2>Set Initial Stock</h2>
    
    <?php if (isset($success_message)): ?>
        <div class="alert" style="color: green;"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <div class="alert"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
    
    <div class="form-container">
        <form method="POST" action="">
            <div class="form-group">
                <label for="stock_location">Location</label>
                <select name="stock_location" id="stock_location" onchange="this.form.submit()">
                    <option value="Main Warehouse" <?php echo $_SESSION['stock_location'] === 'Main Warehouse' ? 'selected' : ''; ?>>Main Warehouse</option>
                    <option value="Secondary Warehouse" <?php echo $_SESSION['stock_location'] === 'Secondary Warehouse' ? 'selected' : ''; ?>>Secondary Warehouse</option>
                </select>
            </div>
        </form>
    </div>

    <div class="form-container">
        <form method="POST" action="" onsubmit="return validateForm()" id="add-item-form">
            <input type="hidden" name="stock_location" value="<?php echo htmlspecialchars($_SESSION['stock_location']); ?>">
            <input type="hidden" name="add_item" value="1">
            <div class="form-group">
                <label for="item-search">Search Item</label>
                <input type="text" id="item-search" name="item_name" placeholder="Type item name" autocomplete="off" required>
                <input type="hidden" id="item-unit" name="unit" required>
            </div>
            <div class="form-group">
                <label for="quantity">Initial Quantity</label>
                <input type="number" id="quantity" name="quantity" min="0" placeholder="Enter quantity" required>
            </div>
            <button type="submit">Add/Update Item</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Unit</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($_SESSION['stock_items'])): ?>
                <tr>
                    <td colspan="4">No items added.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($_SESSION['stock_items'] as $index => $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                        <td><?php echo (int)$item['quantity']; ?></td>
                        <td><?php echo strtoupper(htmlspecialchars($item['unit'])); ?></td>
                        <td>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="remove_item" value="<?php echo $index; ?>">
                                <input type="hidden" name="stock_location" value="<?php echo htmlspecialchars($_SESSION['stock_location']); ?>">
                                <button type="submit">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if (!empty($_SESSION['stock_items'])): ?>
        <div style="margin-top: 20px;">
            <form method="POST" action="">
                <input type="hidden" name="save_stock" value="1">
                <button type="submit">Save Initial Stock</button>
                <button type="submit" name="clear">Clear All</button>
            </form>
        </div>
    <?php endif; ?>

    <script>
        let selectedItem = false;

        $("#item-search").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>",
                    dataType: "json",
                    data: { term: request.term },
                    success: function(data) {
                        if (data.error) {
                            alert("Error fetching suggestions: " + data.error);
                            response([]);
                        } else {
                            response(data);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert("Error fetching item suggestions: " + xhr.responseText);
                        response([]);
                    }
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

        function validateForm() {
            if (!selectedItem) {
                alert('Please select an item from the suggestions.');
                return false;
            }
            const quantity = document.getElementById('quantity').value;
            if (!quantity || quantity < 0) {
                alert('Please enter a valid quantity.');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>

<?php
$conn->close();
ob_end_flush();
?>