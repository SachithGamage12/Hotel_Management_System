<!DOCTYPE html>
<html lang="en">
<?php
// Start output buffering to prevent stray output
ob_start();
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Audit</title>
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
        .conversion-info {
            font-size: 0.85rem;
            color: #6c757d;
            font-style: italic;
        }
        .converted-quantity {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 8px;
            border-radius: 4px;
            font-weight: 500;
            color: #495057;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Inventory Audit</a>
        </div>
        <button onclick="window.location.href='stores.php'" style="background-color: #e6451dff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-right: 50px;">
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

        // Set UTF-8 encoding
        $conn->set_charset("utf8mb4");

        // Define unit conversion factors
        $unit_conversions = [
            'kg' => [
                'g' => 1000,      // 1 kg = 1000 g
                'kg' => 1
            ],
            'g' => [
                'kg' => 0.001,    // 1 g = 0.001 kg
                'g' => 1
            ],
            'l' => [
                'ml' => 1000,     // 1 l = 1000 ml
                'l' => 1
            ],
            'ml' => [
                'l' => 0.001,     // 1 ml = 0.001 l
                'ml' => 1
            ],
            'pcs' => [
                'pcs' => 1        // Pieces (no conversion needed)
            ]
        ];

        // Fetch unique unit types from items table
        $sql_units = "SELECT DISTINCT unit_type FROM items WHERE unit_type IS NOT NULL AND unit_type != '' ORDER BY unit_type";
        $result_units = $conn->query($sql_units);
        $unit_types = [];
        if ($result_units && $result_units->num_rows > 0) {
            while ($row = $result_units->fetch_assoc()) {
                $unit_types[] = $row['unit_type'];
            }
            $result_units->free();
        }

        // Handle Inventory Audit Form Submission
        if (isset($_POST['add_audit'])) {
            $audit_date = $_POST['audit_date'];
            $quantities = $_POST['quantity'] ?? [];
            $units = $_POST['unit'] ?? [];

            // Validate date
            if (empty($audit_date)) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Audit date is required!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            } elseif (empty($quantities)) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">No quantities entered for audit!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            } else {
                $has_valid_entry = false;
                foreach ($quantities as $item_id => $qty) {
                    // Skip if quantity is empty, not numeric, or negative
                    if ($qty === '' || !is_numeric($qty) || (float)$qty < 0) {
                        continue; // Skip silently for empty or invalid fields
                    }
                    
                    $input_unit = $units[$item_id] ?? '';
                    if (empty($input_unit)) {
                        echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">No unit selected for item ID ' . $item_id . '. Skipping.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                        continue;
                    }
                    if (!in_array($input_unit, $unit_types)) {
                        echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">Invalid unit for item ID ' . $item_id . '. Skipping.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                        continue;
                    }

                    // Get base unit from items
                    $sql_base = "SELECT name, unit_type FROM items WHERE id = ?";
                    $stmt_base = $conn->prepare($sql_base);
                    if ($stmt_base === false) {
                        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Prepare failed for base unit query: ' . $conn->error . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                        continue;
                    }
                    $stmt_base->bind_param("i", $item_id);
                    $stmt_base->execute();
                    $result_base = $stmt_base->get_result();
                    $row_base = $result_base->fetch_assoc();
                    $item_name = $row_base ? $row_base['name'] : '';
                    $base_unit = $row_base ? $row_base['unit_type'] : '';
                    $stmt_base->close();

                    if (empty($base_unit)) {
                        echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">No base unit found for item ID ' . $item_id . '. Skipping.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                        continue;
                    }

                    // Since JavaScript converts to base unit, verify input unit is base unit
                    if ($input_unit !== $base_unit) {
                        echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">Input unit for item ID ' . $item_id . ' does not match base unit (' . $base_unit . '). Skipping.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                        continue;
                    }

                    // Use the quantity directly since it's already in base unit
                    $converted_qty = (float)$qty;

                    // Insert into inventory_audits
                    $sql_insert_audit = "INSERT INTO inventory_audits (item_id, audit_date, quantity_at_audit, unit_type) VALUES (?, ?, ?, ?)";
                    $stmt_insert_audit = $conn->prepare($sql_insert_audit);
                    if ($stmt_insert_audit === false) {
                        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Prepare failed for insert into inventory_audits: ' . $conn->error . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                        continue;
                    }
                    $stmt_insert_audit->bind_param("isds", $item_id, $audit_date, $converted_qty, $base_unit);
                    if ($stmt_insert_audit->execute()) {
                        $has_valid_entry = true;
                        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Successfully recorded audit for ' . $item_name . ' on ' . $audit_date . ' with quantity ' . $converted_qty . ' ' . $base_unit . '.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                    } else {
                        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Error adding inventory audit for ' . $item_name . ': ' . $conn->error . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                    }
                    $stmt_insert_audit->close();
                }
                if ($has_valid_entry) {
                    echo '<script>setTimeout(function() { window.location.href = "inventory_audit.php"; }, 2000);</script>';
                } else {
                    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">No valid entries were recorded. Please check your inputs.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                }
            }
        }
        ?>

        <!-- Form to Record Inventory Audit -->
        <div class="card mb-4">
            <div class="card-header">Record Inventory Audit</div>
            <div class="card-body">
                <?php
                // Fetch items for audit form
                $sql = "SELECT id, name AS item_name, unit_type FROM items ORDER BY name";
                $result = $conn->query($sql);
                if ($result === false) {
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Error fetching items for audit: ' . $conn->error . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                } elseif ($result->num_rows == 0) {
                    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">No items found for audit. Please add items to the items table first.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                } else {
                ?>
                    <form action="" method="POST" id="addAuditForm">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="audit_date" class="form-label">Audit Date</label>
                                <input type="date" class="form-control" id="audit_date" name="audit_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Base Unit</th>
                                    <th>Input Quantity</th>
                                    <th>Input Unit</th>
                                    <th>Quantity in Base Unit (for audit)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($row = $result->fetch_assoc()) {
                                    $id = $row['id'];
                                    $item_name = htmlspecialchars($row['item_name']);
                                    $base_unit = htmlspecialchars($row['unit_type'] ?? 'N/A');
                                    
                                    // Get compatible units for this item
                                    $compatible_units = [$base_unit];
                                    if (isset($unit_conversions[$base_unit])) {
                                        $compatible_units = array_merge($compatible_units, array_keys($unit_conversions[$base_unit]));
                                        $compatible_units = array_unique($compatible_units);
                                    }
                                    
                                    echo "<tr data-item-id='$id'>
                                        <td>$item_name</td>
                                        <td><span class='badge bg-primary'>$base_unit</span></td>
                                        <td>
                                            <input type='number' class='form-control input-quantity' id='input-qty-$id' min='0' step='0.01' data-base-unit='$base_unit' placeholder='Enter quantity'>
                                        </td>
                                        <td>
                                            <select class='form-select unit-select' id='input-unit-$id' data-base-unit='$base_unit'>
                                                <option value=''>Select unit</option>";
                                    foreach ($compatible_units as $unit) {
                                        if (in_array($unit, $unit_types)) {
                                            $selected = ($unit === $base_unit) ? 'selected' : '';
                                            echo "<option value='$unit' $selected>$unit</option>";
                                        }
                                    }
                                    echo "</select>
                                        </td>
                                        <td>
                                            <div class='converted-quantity' id='converted-qty-$id'>
                                                <input type='hidden' name='quantity[$id]' id='hidden-qty-$id' value=''>
                                                <input type='hidden' name='unit[$id]' id='hidden-unit-$id' value=''>
                                                <span id='display-qty-$id' class='text-muted'>Enter quantity above</span>
                                            </div>
                                        </td>
                                    </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                        <button type="submit" name="add_audit" class="btn btn-primary">Record Audit</button>
                    </form>
                <?php
                }
                $result->free();
                ?>
            </div>
        </div>

        <!-- Inventory Report -->
        <div class="card">
            <div class="card-header">Inventory Report (Since Last Audit)</div>
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Last Audit Date</th>
                            <th>Last Audit Quantity</th>
                            <th>Received Quantity Since Last Audit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Query all items
                        $sql_items = "SELECT id, name AS item_name FROM items ORDER BY name";
                        $result_items = $conn->query($sql_items);
                        if ($result_items === false) {
                            echo "<tr><td colspan='4' class='text-center'>Error fetching items: " . $conn->error . "</td></tr>";
                        } elseif ($result_items->num_rows == 0) {
                            echo "<tr><td colspan='4' class='text-center'>No items found</td></tr>";
                        } else {
                            while ($item = $result_items->fetch_assoc()) {
                                $item_id = $item['id'];
                                $item_name = htmlspecialchars($item['item_name']);

                                // Get last audit for the item
                                $sql_last_audit = "SELECT audit_date, quantity_at_audit, unit_type FROM inventory_audits WHERE item_id = ? ORDER BY audit_date DESC LIMIT 1";
                                $stmt_last_audit = $conn->prepare($sql_last_audit);
                                if ($stmt_last_audit === false) {
                                    echo "<tr><td colspan='4' class='text-center'>Error preparing audit query: " . $conn->error . "</td></tr>";
                                    continue;
                                }
                                $stmt_last_audit->bind_param("i", $item_id);
                                $stmt_last_audit->execute();
                                $result_last_audit = $stmt_last_audit->get_result();
                                $last_audit = $result_last_audit->fetch_assoc();
                                $stmt_last_audit->close();

                                $last_audit_date = $last_audit ? htmlspecialchars($last_audit['audit_date']) : 'N/A';
                                $last_audit_quantity = $last_audit ? number_format((float)$last_audit['quantity_at_audit'], 2) . ' ' . htmlspecialchars($last_audit['unit_type']) : 'N/A';

                                // Calculate received quantity since last audit
                                $received_since = 0;
                                if ($last_audit) {
                                    $sql_received = "SELECT SUM(quantity) AS total_received FROM purchases WHERE item_id = ? AND purchased_date > ?";
                                    $stmt_received = $conn->prepare($sql_received);
                                    if ($stmt_received === false) {
                                        echo "<tr><td colspan='4' class='text-center'>Error preparing received query: " . $conn->error . "</td></tr>";
                                        continue;
                                    }
                                    $stmt_received->bind_param("is", $item_id, $last_audit_date);
                                    $stmt_received->execute();
                                    $result_received = $stmt_received->get_result();
                                    $row_received = $result_received->fetch_assoc();
                                    $received_since = $row_received['total_received'] ? number_format((float)$row_received['total_received'], 2) : 0;
                                    $stmt_received->close();
                                } else {
                                    $sql_received = "SELECT SUM(quantity) AS total_received FROM purchases WHERE item_id = ?";
                                    $stmt_received = $conn->prepare($sql_received);
                                    if ($stmt_received === false) {
                                        echo "<tr><td colspan='4' class='text-center'>Error preparing received query: " . $conn->error . "</td></tr>";
                                        continue;
                                    }
                                    $stmt_received->bind_param("i", $item_id);
                                    $stmt_received->execute();
                                    $result_received = $stmt_received->get_result();
                                    $row_received = $result_received->fetch_assoc();
                                    $received_since = $row_received['total_received'] ? number_format((float)$row_received['total_received'], 2) : 0;
                                    $stmt_received->close();
                                }

                                echo "<tr>
                                    <td>$item_name</td>
                                    <td>$last_audit_date</td>
                                    <td>$last_audit_quantity</td>
                                    <td>$received_since</td>
                                </tr>";
                            }
                            $result_items->free();
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
    // Unit conversion factors (mirroring PHP array)
    const unitConversions = {
        'kg': { 'g': 1000, 'kg': 1 },
        'g': { 'kg': 0.001, 'g': 1 },
        'l': { 'ml': 1000, 'l': 1 },
        'ml': { 'l': 0.001, 'ml': 1 },
        'pcs': { 'pcs': 1 }
    };

    // Function to convert quantity from input unit to base unit
    function convertToBaseUnit(quantity, fromUnit, toUnit) {
        if (fromUnit === toUnit) {
            return quantity;
        }
        
        // Direct conversion
        if (unitConversions[fromUnit] && unitConversions[fromUnit][toUnit]) {
            return quantity * unitConversions[fromUnit][toUnit];
        }
        
        // Reverse conversion
        if (unitConversions[toUnit] && unitConversions[toUnit][fromUnit]) {
            return quantity / unitConversions[toUnit][fromUnit];
        }
        
        return quantity; // No conversion possible
    }

    // Function to update conversion display and hidden inputs
    function updateConversion(itemId) {
        const inputQtyElement = document.getElementById(`input-qty-${itemId}`);
        const inputUnitElement = document.getElementById(`input-unit-${itemId}`);
        const displayQtyElement = document.getElementById(`display-qty-${itemId}`);
        const hiddenQtyElement = document.getElementById(`hidden-qty-${itemId}`);
        const hiddenUnitElement = document.getElementById(`hidden-unit-${itemId}`);
        
        const inputQuantity = parseFloat(inputQtyElement.value) || 0;
        const inputUnit = inputUnitElement.value;
        const baseUnit = inputUnitElement.dataset.baseUnit;
        
        if (inputQuantity > 0 && inputUnit) {
            // Convert to base unit
            const convertedQuantity = convertToBaseUnit(inputQuantity, inputUnit, baseUnit);
            
            // Update display
            if (inputUnit === baseUnit) {
                displayQtyElement.innerHTML = `<strong>${convertedQuantity.toFixed(4)} ${baseUnit}</strong>`;
            } else {
                displayQtyElement.innerHTML = `<strong>${convertedQuantity.toFixed(4)} ${baseUnit}</strong><br><small class="text-muted">(${inputQuantity} ${inputUnit} → ${convertedQuantity.toFixed(4)} ${baseUnit})</small>`;
            }
            
            // Update hidden inputs for form submission
            hiddenQtyElement.value = convertedQuantity.toFixed(4);
            hiddenUnitElement.value = baseUnit;
        } else if (inputQuantity > 0 && !inputUnit) {
            displayQtyElement.innerHTML = '<span class="text-warning">Please select a unit</span>';
            hiddenQtyElement.value = '';
            hiddenUnitElement.value = '';
        } else {
            displayQtyElement.innerHTML = '<span class="text-muted">Enter quantity above</span>';
            hiddenQtyElement.value = '';
            hiddenUnitElement.value = '';
        }
    }

    // Initialize event listeners when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize bootstrap-select
        try {
            $('.unit-select').selectpicker();
        } catch (e) {
            console.log('Bootstrap-select not available, using standard select');
        }

        // Add event listeners for all quantity inputs and unit selects
        document.querySelectorAll('.input-quantity').forEach(function(element) {
            const itemId = element.closest('tr').dataset.itemId;
            
            // Update on input change
            element.addEventListener('input', function() {
                // Allow only numbers and decimal point
                this.value = this.value.replace(/[^0-9.]/g, '');
                updateConversion(itemId);
            });
            
            // Update on blur (when user leaves the field)
            element.addEventListener('blur', function() {
                updateConversion(itemId);
            });
        });

        // Add event listeners for unit selects
        document.querySelectorAll('.unit-select').forEach(function(element) {
            const itemId = element.closest('tr').dataset.itemId;
            
            element.addEventListener('change', function() {
                updateConversion(itemId);
            });
        });
    });

    // Form validation before submission
    document.getElementById('addAuditForm').addEventListener('submit', function(e) {
        const auditDate = document.getElementById('audit_date').value;
        if (!auditDate) {
            e.preventDefault();
            alert('Audit date is required!');
            return;
        }
        
        let hasValidEntry = false;
        
        // Check if at least one valid entry exists
        document.querySelectorAll('.input-quantity').forEach(function(element) {
            const itemId = element.closest('tr').dataset.itemId;
            const hiddenQty = document.getElementById(`hidden-qty-${itemId}`).value;
            const hiddenUnit = document.getElementById(`hidden-unit-${itemId}`).value;
            
            if (hiddenQty && parseFloat(hiddenQty) > 0 && hiddenUnit) {
                hasValidEntry = true;
            }
        });
        
        if (!hasValidEntry) {
            e.preventDefault();
            alert('Please enter at least one valid quantity with unit selection!');
            return;
        }
    });
</script>
</body>
</html>