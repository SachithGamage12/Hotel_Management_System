<?php
// File: stock.php
include 'db.php';

function get_category_and_base($unit_type) {
    $unit_type = strtolower($unit_type);
    if ($unit_type === 'kg') {
        return ['category' => 'weight', 'options' => ['kg', 'g']];
    } elseif ($unit_type === 'g') {
        return ['category' => 'weight', 'options' => ['g', 'kg']];
    } elseif ($unit_type === 'liter') {
        return ['category' => 'volume', 'options' => ['liter', 'milliliter']];
    } elseif ($unit_type === 'milliliter') {
        return ['category' => 'volume', 'options' => ['milliliter', 'liter']];
    } else {
        return ['category' => 'count', 'options' => [$unit_type]];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logistics Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f8f9fa; }
        .fade-in-section { animation: fadeIn 0.5s ease-in-out; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .error { color: red; }
        .success { color: green; }
        #suggestions { position: absolute; z-index: 1000; width: inherit; max-height: 200px; overflow-y: auto; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .table-container { overflow-x: auto; }
        tr { transition: opacity 0.3s ease; }
        .container { box-shadow: 0 0 20px rgba(0,0,0,0.1); border-radius: 10px; padding: 20px; background: white; }
        .btn-remove { color: red; border: none; background: none; cursor: pointer; }
    </style>
</head>
<body>
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

    <div class="container my-4 fade-in-section">
        <h2>Add Stock for Date</h2>
        <form action="stock.php" method="POST" id="stockForm">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="date" class="form-label">Select Date:</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                        <input type="date" id="date" name="date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="mb-3 position-relative">
                <label for="item_search" class="form-label">Search and Select Item:</label>
                <input type="text" id="item_search" class="form-control" oninput="searchItems(this.value)" placeholder="Type item name...">
                <div id="suggestions" class="list-group"></div>
            </div>
            
            <div class="table-container">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="items_table_body">
                        <!-- Items will be added dynamically -->
                    </tbody>
                </table>
            </div>
            <button type="submit" name="add_stock" class="btn btn-primary">Add Stock</button>
        </form>
        <?php
        if (isset($_POST['add_stock'])) {
            $date = $_POST['date'];
            $qtys = $_POST['qty'] ?? [];
            $units = $_POST['unit'] ?? [];
            $unit_types = $_POST['unit_type'] ?? [];
            $success = true;

            foreach ($qtys as $itemId => $qty) {
                // Validate quantity
                $qty = floatval($qty);
                if ($qty <= 0) {
                    echo "<p class='error'>Invalid quantity for item ID $itemId: Quantity must be greater than zero.</p>";
                    $success = false;
                    continue;
                }

                $unit = $units[$itemId] ?? '';
                $unit_type = $unit_types[$itemId] ?? '';
                if (empty($unit) || empty($unit_type)) {
                    echo "<p class='error'>Missing unit or unit type for item ID $itemId.</p>";
                    $success = false;
                    continue;
                }

                $info = get_category_and_base($unit_type);
                if (!in_array($unit, $info['options'])) {
                    echo "<p class='error'>Invalid unit '$unit' for item ID $itemId.</p>";
                    $success = false;
                    continue;
                }

                // Update unit_type in items table
                $updateSql = "UPDATE items SET unit_type = ? WHERE id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("si", $unit, $itemId);
                if ($updateStmt->execute() !== TRUE) {
                    echo "<p class='error'>Error updating unit type for item ID $itemId: " . $conn->error . "</p>";
                    $success = false;
                }
                $updateStmt->close();

                // Insert into stock table with raw quantity and selected unit
                $insertSql = "INSERT INTO stock (item_id, date, quantity, unit) VALUES (?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertSql);
                $insertStmt->bind_param("isds", $itemId, $date, $qty, $unit);
                if ($insertStmt->execute() !== TRUE) {
                    echo "<p class='error'>Error adding stock for item ID $itemId: " . $conn->error . "</p>";
                    $success = false;
                }
                $insertStmt->close();
            }
            if ($success && !empty($qtys)) {
                echo "<p class='success'>Stock and unit types saved successfully!</p>";
            } elseif (empty($qtys)) {
                echo "<p class='error'>No items selected.</p>";
            }
        }
        ?>
    </div>

    <!-- Section: View Stock -->
    <div class="container my-4 fade-in-section">
        <h2>View Stock</h2>
        <div class="table-container">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Date</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT i.name, s.date, s.quantity, s.unit, i.price 
                            FROM stock s 
                            JOIN items i ON s.item_id = i.id 
                            ORDER BY s.date DESC";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                            echo "<td>" . number_format($row['quantity'], 2) . "</td>";
                            echo "<td>" . htmlspecialchars($row['unit']) . "</td>";
                            echo "<td>LKR " . number_format($row['price'], 2) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No stock records found.</td></tr>";
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search items with debounce
        let debounceTimeout;
        function searchItems(term) {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => {
                if (term.length < 1) {
                    document.getElementById('suggestions').innerHTML = '';
                    return;
                }
                fetch('stock_search_items.php?term=' + encodeURIComponent(term))
                    .then(response => response.json())
                    .then(data => {
                        let suggestions = document.getElementById('suggestions');
                        suggestions.innerHTML = '';
                        data.forEach(item => {
                            let a = document.createElement('a');
                            a.classList.add('list-group-item', 'list-group-item-action');
                            a.textContent = item.name;
                            a.onclick = () => addItemToTable(item);
                            suggestions.appendChild(a);
                        });
                    })
                    .catch(error => console.error('Error fetching suggestions:', error));
            }, 300); // 300ms debounce delay
        }

        // Add item to table with animation
        function addItemToTable(item) {
            let tbody = document.getElementById('items_table_body');
            let itemId = item.id;
            if (document.getElementById('row_' + itemId)) return;
            
            // Duplicate get_category_and_base in JS for unit options
            function getCategoryAndBase(unit_type) {
                unit_type = unit_type.toLowerCase();
                if (unit_type === 'kg') {
                    return {category: 'weight', options: ['kg', 'g']};
                } else if (unit_type === 'g') {
                    return {category: 'weight', options: ['g', 'kg']};
                } else if (unit_type === 'liter') {
                    return {category: 'volume', options: ['liter', 'milliliter']};
                } else if (unit_type === 'milliliter') {
                    return {category: 'volume', options: ['milliliter', 'liter']};
                } else {
                    return {category: 'count', options: [unit_type]};
                }
            }
            
            let info = getCategoryAndBase(item.unit_type);
            let options = info.options;
            let tr = document.createElement('tr');
            tr.id = 'row_' + itemId;
            tr.style.opacity = '0';
            tr.innerHTML = `
                <td>${item.name}</td>
                <td><input type='number' id='qty_${itemId}' name='qty[${itemId}]' step='0.01' min='0.01' class='form-control' required></td>
                <td>
                    <select id='unit_${itemId}' name='unit[${itemId}]' class='form-select'>
                        ${options.map(opt => `<option value='${opt}' ${opt === item.unit_type ? 'selected' : ''}>${opt}</option>`).join('')}
                    </select>
                </td>
                <td>
                    <button class="btn-remove" onclick="removeItem(${itemId})"><i class="bi bi-trash"></i></button>
                </td>
                <input type='hidden' name='unit_type[${itemId}]' value='${item.unit_type}'>
            `;
            tbody.appendChild(tr);
            setTimeout(() => { tr.style.opacity = '1'; }, 10);
            document.getElementById('suggestions').innerHTML = '';
            document.getElementById('item_search').value = '';
        }

        // Remove item with animation
        function removeItem(itemId) {
            let row = document.getElementById('row_' + itemId);
            if (row) {
                row.style.opacity = '0';
                setTimeout(() => { row.remove(); }, 300);
            }
        }

        // Client-side validation before form submission
        document.getElementById('stockForm').addEventListener('submit', function(event) {
            const qtyInputs = document.querySelectorAll('input[name^="qty["]');
            let valid = true;
            qtyInputs.forEach(input => {
                const qty = parseFloat(input.value);
                if (isNaN(qty) || qty <= 0) {
                    valid = false;
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            if (!valid) {
                event.preventDefault();
                alert('Please enter valid quantities greater than zero for all items.');
            }
        });
    </script>
</body>
</html>