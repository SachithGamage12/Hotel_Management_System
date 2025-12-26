<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchased Item Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .container {
            max-width: 1200px;
            margin-top: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #007bff;
            color: white;
            border-radius: 10px 10px 0 0;
            font-weight: 500;
        }
        .form-control, .form-select, .bootstrap-select .dropdown-toggle {
            border-radius: 5px;
            border: 1px solid #ced4da;
        }
        .btn-primary {
            border-radius: 5px;
            padding: 8px 20px;
        }
        .btn-secondary {
            border-radius: 5px;
        }
        .table {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
        }
        .table th {
            background-color: #e9ecef;
            font-weight: 500;
        }
        .table td {
            vertical-align: middle;
        }
        .alert {
            border-radius: 5px;
        }
    </style>
</head>
<body>
    
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Purchased Item Details</a>
        </div>
        <button onclick="window.location.href='../stores.php'" style="background-color: #e6451dff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-right: 50px;">
            Back
        </button>
    </nav>
  
    <div class="container">
        <!-- Alerts -->
        <?php
        // Database Connection
        $conn = new mysqli('localhost', 'hotelgrandguardi_root', 'Sun123flower@', 'hotelgrandguardi_wedding_bliss');
        if ($conn->connect_error) {
            die('<div class="alert alert-danger alert-dismissible fade show" role="alert">Database connection failed: ' . $conn->connect_error . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        // Handle Add Purchase Form Submission
        if (isset($_POST['add_purchase'])) {
            $item_id = $_POST['item_id'];
            $stock = (float)$_POST['stock'];
            $unit = $_POST['unit'];
            $unit_price = (float)$_POST['unit_price'];
            $price = $unit_price * $stock; // Calculate total price
            $expiry_date = $_POST['expiry_date'] ?: null;
            $purchased_date = $_POST['purchased_date'];

            // Validate inputs
            if (empty($item_id)) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Please select an item!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            } elseif ($stock <= 0) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Stock must be a positive number!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            } elseif ($unit_price < 0) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Unit price must be non-negative!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            } elseif (empty($purchased_date)) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Purchased date is required!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            } else {
                // Start a transaction to ensure atomicity
                $conn->begin_transaction();

                try {
                    // Check for potential duplicate purchase in backup
                    $sql_check_backup = "SELECT id FROM purchased_items_backup WHERE item_id = ? AND stock = ? AND price = ? AND purchased_date = ? AND (expiry_date = ? OR (expiry_date IS NULL AND ? IS NULL))";
                    $stmt_check_backup = $conn->prepare($sql_check_backup);
                    if ($stmt_check_backup === false) {
                        throw new Exception("Prepare failed for duplicate check in backup: " . $conn->error);
                    }
                    $stmt_check_backup->bind_param("iddsss", $item_id, $stock, $price, $purchased_date, $expiry_date, $expiry_date);
                    $stmt_check_backup->execute();
                    $result_check_backup = $stmt_check_backup->get_result();

                    if ($result_check_backup->num_rows > 0) {
                        throw new Exception("This purchase appears to be a duplicate. Please verify the details or edit the existing purchase.");
                    }
                    $stmt_check_backup->close();

                    // Insert original purchase record into backup table
                    $sql_insert_backup = "INSERT INTO purchased_items_backup (item_id, stock, unit, price, unit_price, expiry_date, purchased_date) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt_insert_backup = $conn->prepare($sql_insert_backup);
                    if ($stmt_insert_backup === false) {
                        throw new Exception("Prepare failed for insert into purchased_items_backup: " . $conn->error);
                    }
                    $stmt_insert_backup->bind_param("idsddss", $item_id, $stock, $unit, $price, $unit_price, $expiry_date, $purchased_date);
                    if (!$stmt_insert_backup->execute()) {
                        throw new Exception("Error adding purchase to purchased_items_backup: " . $conn->error);
                    }
                    $stmt_insert_backup->close();

                    // Check inventory for buffer_stock and threshold
                    $sql_inventory = "SELECT buffer_stock, threshold FROM inventory WHERE id = ?";
                    $stmt_inventory = $conn->prepare($sql_inventory);
                    if ($stmt_inventory === false) {
                        throw new Exception("Prepare failed: " . $conn->error);
                    }
                    $stmt_inventory->bind_param("i", $item_id);
                    $stmt_inventory->execute();
                    $result_inventory = $stmt_inventory->get_result();
                    $inventory = $result_inventory->fetch_assoc();
                    $stmt_inventory->close();

                    if (!$inventory) {
                        throw new Exception("Item not found in inventory.");
                    }

                    $current_buffer_stock = (float)$inventory['buffer_stock'];
                    $threshold = (float)$inventory['threshold'];

                    $stock_to_add = 0;
                    if ($current_buffer_stock < $threshold) {
                        // Calculate how much stock is needed to reach threshold
                        $stock_needed = $threshold - $current_buffer_stock;
                        $stock_to_add = min($stock, $stock_needed); // Use only what's needed or available

                        // Update buffer_stock in inventory
                        $new_buffer_stock = $current_buffer_stock + $stock_to_add;
                        $sql_update_inventory = "UPDATE inventory SET buffer_stock = ? WHERE id = ?";
                        $stmt_update_inventory = $conn->prepare($sql_update_inventory);
                        if ($stmt_update_inventory === false) {
                            throw new Exception("Prepare failed for inventory update: " . $conn->error);
                        }
                        $stmt_update_inventory->bind_param("di", $new_buffer_stock, $item_id);
                        if (!$stmt_update_inventory->execute()) {
                            throw new Exception("Error updating inventory: " . $conn->error);
                        }
                        $stmt_update_inventory->close();
                    }

                    // Calculate remaining stock and price after refilling buffer
                    $remaining_stock = $stock - $stock_to_add;
                    $insert_purchase = ($remaining_stock > 0);

                    if ($insert_purchase) {
                        $remaining_price = $unit_price * $remaining_stock; // Calculate remaining total price
                        $remaining_unit_price = $unit_price; // Unit price remains the same

                        // Check for potential duplicate in purchased_items
                        $sql_check_purchased = "SELECT id FROM purchased_items WHERE item_id = ? AND stock = ? AND price = ? AND purchased_date = ? AND (expiry_date = ? OR (expiry_date IS NULL AND ? IS NULL))";
                        $stmt_check_purchased = $conn->prepare($sql_check_purchased);
                        if ($stmt_check_purchased === false) {
                            throw new Exception("Prepare failed for duplicate check in purchased_items: " . $conn->error);
                        }
                        $stmt_check_purchased->bind_param("iddsss", $item_id, $remaining_stock, $remaining_price, $purchased_date, $expiry_date, $expiry_date);
                        $stmt_check_purchased->execute();
                        $result_check_purchased = $stmt_check_purchased->get_result();

                        if ($result_check_purchased->num_rows > 0) {
                            throw new Exception("This purchase appears to be a duplicate in purchased_items. Please verify the details or edit the existing purchase.");
                        }
                        $stmt_check_purchased->close();

                        // Insert into purchased_items
                        $sql_insert = "INSERT INTO purchased_items (item_id, stock, unit, price, unit_price, expiry_date, purchased_date) VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $stmt_insert = $conn->prepare($sql_insert);
                        if ($stmt_insert === false) {
                            throw new Exception("Prepare failed for insert into purchased_items: " . $conn->error);
                        }
                        $stmt_insert->bind_param("idsddss", $item_id, $remaining_stock, $unit, $remaining_price, $remaining_unit_price, $expiry_date, $purchased_date);
                        if (!$stmt_insert->execute()) {
                            throw new Exception("Error adding purchase to purchased_items: " . $conn->error);
                        }
                        $stmt_insert->close();
                    }

                    // Commit the transaction
                    $conn->commit();
                    $conn->close();
                    header("Location: purchased_items.php");
                    exit();

                } catch (Exception $e) {
                    // Rollback transaction on error
                    $conn->rollback();
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $e->getMessage() . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                    $conn->close();
                }
            }
        }
        ?>

        <!-- Form to Add Purchase -->
        <div class="card mb-4">
            <div class="card-header">Add New Purchase</div>
            <div class="card-body">
                <?php
                // Check inventory table for items
                $sql = "SELECT id, item_name, unit FROM inventory ORDER BY item_name";
                $result = $conn->query($sql);
                if ($result === false) {
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Error fetching items: ' . $conn->error . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                } elseif ($result->num_rows == 0) {
                    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">No items found in inventory. Please add items to the inventory first.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                } else {
                ?>
                    <form action="" method="POST" id="addPurchaseForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="item_id" class="form-label">Item Name</label>
                                <select class="selectpicker form-select" id="item_id" name="item_id" data-live-search="true" required>
                                    <option value="" disabled selected>Select an item</option>
                                    <?php
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='{$row['id']}' data-unit='{$row['unit']}'>" . htmlspecialchars($row['item_name']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="stock" class="form-label">Stock</label>
                                <input type="text" class="form-control" id="stock" name="stock" required>
                                <div class="form-text">Enter a positive number (e.g., 1.345)</div>
                            </div>
                            <div class="col-md-6">
                                <label for="unit" class="form-label">Unit</label>
                                <input type="text" class="form-control" id="unit" name="unit" readonly required>
                            </div>
                            <div class="col-md-6">
                                <label for="unit_price" class="form-label">Unit Price (LKR)</label>
                                <div class="input-group">
                                    <span class="input-group-text">LKR</span>
                                    <input type="number" class="form-control" id="unit_price" name="unit_price" min="0" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="total_price" class="form-label">Total Price (LKR)</label>
                                <div class="input-group">
                                    <span class="input-group-text">LKR</span>
                                    <input type="number" class="form-control" id="total_price" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="expiry_date" class="form-label">Expiry Date (Optional)</label>
                                <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                            </div>
                            <div class="col-md-6">
                                <label for="purchased_date" class="form-label">Purchased Date</label>
                                <input type="date" class="form-control" id="purchased_date" name="purchased_date" required>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button type="submit" name="add_purchase" class="btn btn-primary w-100">Add Purchase</button>
                            </div>
                        </div>
                    </form>
                <?php
                }
                ?>
            </div>
        </div>

        <!-- Search Form -->
        <div class="card mb-4">
            <div class="card-header">Search Purchases</div>
            <div class="card-body">
                <form action="" method="GET">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search by item name" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <?php if (isset($_GET['search']) && $_GET['search']): ?>
                            <a href="purchased_items.php" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Display Purchases Table -->
        <div class="card">
            <div class="card-header">Purchased Items (Latest 5)</div>
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Purchase ID</th>
                            <th>Item Name</th>
                            <th>Stock</th>
                            <th>Unit</th>
                            <th>Expiry Date</th>
                            <th>Purchased Date</th>
                            <th>Total Price (LKR)</th>
                            <th>Unit Price (LKR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Handle Search and Display Purchases (Limited to latest 5)
                        $search = isset($_GET['search']) ? $_GET['search'] : '';
                        $stmt_active = false;
                        if ($search) {
                            $sql = "SELECT p.id, p.item_id, p.stock, p.unit, p.price, p.unit_price, p.expiry_date, p.purchased_date, i.item_name 
                                    FROM purchased_items p 
                                    JOIN inventory i ON p.item_id = i.id 
                                    WHERE i.item_name LIKE ? 
                                    ORDER BY p.id DESC 
                                    LIMIT 5";
                            $stmt = $conn->prepare($sql);
                            if ($stmt === false) {
                                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Search prepare failed: ' . $conn->error . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                            } else {
                                $search_term = "%" . $search . "%";
                                $stmt->bind_param("s", $search_term);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $stmt_active = true;
                            }
                        } else {
                            $sql = "SELECT p.id, p.item_id, p.stock, p.unit, p.price, p.unit_price, p.expiry_date, p.purchased_date, i.item_name 
                                    FROM purchased_items p 
                                    JOIN inventory i ON p.item_id = i.id 
                                    ORDER BY p.id DESC 
                                    LIMIT 5";
                            $result = $conn->query($sql);
                            if ($result === false) {
                                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Query failed: ' . $conn->error . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                            }
                        }

                        if (isset($result) && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $expiry_date = $row['expiry_date'] ? htmlspecialchars($row['expiry_date']) : 'N/A';
                                echo "<tr>
                                    <td>" . htmlspecialchars($row['id']) . "</td>
                                    <td>" . htmlspecialchars($row['item_name']) . "</td>
                                    <td>" . number_format($row['stock'], 2) . "</td>
                                    <td>" . htmlspecialchars($row['unit']) . "</td>
                                    <td>" . $expiry_date . "</td>
                                    <td>" . htmlspecialchars($row['purchased_date']) . "</td>
                                    <td>LKR " . number_format($row['price'], 2) . "</td>
                                    <td>LKR " . number_format($row['unit_price'], 2) . "</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8' class='text-center'>No purchases found</td></tr>";
                        }
                        if ($stmt_active) {
                            $stmt->close();
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
    <script>
        // Initialize bootstrap-select after DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            try {
                $('.selectpicker').selectpicker();
            } catch (e) {
                console.error('Error initializing bootstrap-select:', e);
            }
        });

        // Handle stock input to allow decimal numbers
        document.getElementById('stock').addEventListener('input', function() {
            let value = this.value;

            // Allow only digits and one decimal point
            value = value.replace(/[^0-9.]/g, '');
            
            // Prevent multiple decimal points
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }

            // Update the input value
            this.value = value;

            // Update total price
            updateTotalPrice();
        });

        // Enforce numeric input for unit price
        document.getElementById('unit_price').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9.]/g, '');
            if (this.value && parseFloat(this.value) < 0) {
                this.value = 0;
            }
            updateTotalPrice();
        });

        // Populate unit field based on selected item
        document.getElementById('item_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const unit = selectedOption.getAttribute('data-unit');
            document.getElementById('unit').value = unit || '';
        });

        // Update total price dynamically
        function updateTotalPrice() {
            let stock = document.getElementById('stock').value;
            const unitPrice = parseFloat(document.getElementById('unit_price').value) || 0;

            // If stock is empty or just a decimal point, treat as 0 for calculation
            if (stock === '' || stock === '.') {
                document.getElementById('total_price').value = '0.00';
                return;
            }

            // Parse stock value - if it ends with decimal, we'll keep it as string for display
            // but use the numeric value for calculation
            let stockValue;
            if (stock.endsWith('.')) {
                // If ends with decimal, remove it for calculation but keep in display
                stockValue = parseFloat(stock.slice(0, -1)) || 0;
            } else {
                stockValue = parseFloat(stock) || 0;
            }

            const totalPrice = (stockValue * unitPrice).toFixed(2);
            document.getElementById('total_price').value = totalPrice;
        }

        // Validate add purchase form on submit
        document.getElementById('addPurchaseForm').addEventListener('submit', function(e) {
            const itemId = document.getElementById('item_id').value;
            let stock = document.getElementById('stock').value;
            const unitPrice = document.getElementById('unit_price').value;
            const purchasedDate = document.getElementById('purchased_date').value;

            // Convert stock to float, handling trailing decimal and empty values
            if (stock === '' || stock === '.') {
                e.preventDefault();
                alert('Stock must be a positive number!');
                return;
            }
            
            // Remove trailing decimal point if present for validation
            if (stock.endsWith('.')) {
                stock = stock.slice(0, -1);
            }
            
            const stockValue = parseFloat(stock);

            if (!itemId) {
                e.preventDefault();
                alert('Please select an item!');
                return;
            }
            if (isNaN(stockValue) || stockValue <= 0) {
                e.preventDefault();
                alert('Stock must be a positive number!');
                return;
            }
            if (unitPrice === '' || parseFloat(unitPrice) < 0) {
                e.preventDefault();
                alert('Unit price must be a non-negative number!');
                return;
            }
            if (!purchasedDate) {
                e.preventDefault();
                alert('Purchased date is required!');
                return;
            }
        });
    </script>
</body>
</html>