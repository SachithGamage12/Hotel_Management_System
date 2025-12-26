<?php
include 'db.php';

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<html>
<head>
<title>Inventory Update</title>
<!-- Include Flatpickr CSS and JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        background: #f5f5f5;
        padding: 20px;
    }

    .container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 30px;
        max-width: 100%;
        margin: 0 auto;
    }

    h1 {
        text-align: center;
        color: #333;
        margin-bottom: 30px;
        font-size: 24px;
    }

    .form-section {
        margin-bottom: 25px;
        padding: 15px;
        background: #fafafa;
        border-radius: 5px;
        border-left: 4px solid #007bff;
        display: flex;
        gap: 15px;
        align-items: center;
        flex-wrap: wrap;
    }

    select, input[type="text"], input[type="number"] {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        background: white;
    }

    select:focus, input:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0,123,255,0.3);
    }

    .table-wrapper {
        width: 100%;
        border: 1px solid #ddd;
        border-radius: 5px;
        background: white;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    th, td {
        padding: 10px 6px;
        text-align: center;
        border-bottom: 1px solid #eee;
        border-right: 1px solid #eee;
        vertical-align: middle;
        white-space: normal;
        word-wrap: break-word;
    }

    th {
        background: #007bff;
        color: white;
        font-weight: 600;
        white-space: nowrap;
    }

    th:last-child, td:last-child {
        border-right: none;
    }

    tr:nth-child(even) {
        background: #f9f9f9;
    }

    tr:hover {
        background: #f0f8ff;
    }

    .item-name {
        text-align: left !important;
        font-weight: 600;
        color: #333;
        max-width: 150px;
    }

    .last_qty {
        background: #28a745;
        color: white;
        padding: 4px 8px;
        border-radius: 3px;
        font-weight: 600;
        display: inline-block;
        min-width: 40px;
    }

    .total_qty {
        background: #ffc107;
        color: #333;
        padding: 4px 8px;
        border-radius: 3px;
        font-weight: 600;
        display: inline-block;
        min-width: 40px;
    }

    input[type="number"] {
        width: 80px;
        padding: 6px;
        border: 1px solid #ddd;
        border-radius: 3px;
        text-align: center;
        font-size: 13px;
    }

    input[type="number"]:focus {
        border-color: #007bff;
        box-shadow: 0 0 3px rgba(0,123,255,0.3);
        outline: none;
    }

    input[name*="present_qty"] {
        background: #e3f2fd;
        border-color: #2196f3;
        font-weight: 600;
    }

    select {
        max-width: 120px;
        font-size: 12px;
    }

    input[type="text"].flatpickr-input {
        width: 130px;
        font-size: 12px;
    }

    .submit-btn {
        background: #28a745;
        color: white;
        padding: 12px 30px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 20px;
        display: block;
        margin-left: auto;
        margin-right: auto;
    }

    .submit-btn:hover {
        background: #218838;
    }

    .print-btn {
        background: #17a2b8;
        color: white;
        padding: 12px 30px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        display: block;
        margin: 20px auto;
    }

    .print-btn:hover {
        background: #138496;
    }

    .success-message {
        background: #d4edda;
        color: #155724;
        padding: 15px;
        border-radius: 5px;
        border: 1px solid #c3e6cb;
        margin-bottom: 20px;
        text-align: center;
        font-weight: 600;
    }

    .error-message {
        background: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 5px;
        border: 1px solid #f5c6cb;
        margin-bottom: 20px;
        text-align: center;
        font-weight: 600;
    }

    .no-items {
        text-align: center;
        padding: 40px;
        color: #666;
        font-size: 16px;
        background: #f8f9fa;
        border-radius: 5px;
        border: 1px solid #dee2e6;
    }

    @media (max-width: 768px) {
        body {
            padding: 10px;
        }
        .container {
            padding: 15px;
        }
        h1 {
            font-size: 20px;
        }
        table {
            font-size: 11px;
        }
        th, td {
            padding: 6px 4px;
        }
        input[type="number"] {
            width: 70px;
        }
        .item-name {
            max-width: 100px;
        }
        .form-section {
            flex-direction: column;
            align-items: stretch;
        }
    }

    @media print {
        .form-section, .submit-btn, .print-btn {
            display: none;
        }
        .container {
            box-shadow: none;
            padding: 0;
        }
        table {
            font-size: 8pt;
            width: 100%;
        }
        th, td {
            padding: 4px;
            border: 1px solid #ccc;
        }
        .item-name {
            max-width: 120px;
        }
        @page {
            size: landscape;
            margin: 10mm;
        }
        tr {
            page-break-inside: avoid;
        }
    }
</style>
<script>
function calculateTotal(row) {
    var lastQty = parseInt(row.querySelector('.last_qty').innerText) || 0;
    var newIssue = parseInt(row.querySelector('input[name^="new_issue"]').value) || 0;
    var transferQty = parseInt(row.querySelector('input[name^="transfer_qty"]').value) || 0;
    var returnQty = parseInt(row.querySelector('input[name^="return_qty"]').value) || 0;
    var damageQty = parseInt(row.querySelector('input[name^="damage_qty"]').value) || 0;
    var total = lastQty + newIssue - transferQty - returnQty - damageQty;
    row.querySelector('.total_qty').innerText = total;
    row.querySelector('input[name^="present_qty"]').value = total; // Auto-populate Present Qty
    
    // Visual feedback for negative totals
    var totalElement = row.querySelector('.total_qty');
    if (total < 0) {
        totalElement.style.background = '#dc3545';
        totalElement.style.color = 'white';
    } else {
        totalElement.style.background = '#ffc107';
        totalElement.style.color = '#333';
    }
}

function validateForm() {
    const dateInputs = document.querySelectorAll('input.flatpickr-input');
    for (let input of dateInputs) {
        const value = input.value.trim();
        if (value) {
            if (!/^\d{4}-\d{2}-\d{2}$/.test(value) || value.length !== 10) {
                alert('Invalid date for ' + input.name + '. Please select a valid date in YYYY-MM-DD format or leave it empty.');
                return false;
            }
            const [year, month, day] = value.split('-').map(Number);
            const date = new Date(year, month - 1, day);
            if (isNaN(date.getTime()) || date.getFullYear() !== year || date.getMonth() + 1 !== month || date.getDate() !== day) {
                alert('Invalid calendar date for ' + input.name + '. Please select a valid date in YYYY-MM-DD format.');
                return false;
            }
        }
    }
    // Specifically validate present_date
    const presentDate = document.querySelector('input[name="present_date"]').value.trim();
    if (!presentDate || !/^\d{4}-\d{2}-\d{2}$/.test(presentDate) || presentDate.length !== 10) {
        alert('Invalid present inventory date. Please select a valid date in YYYY-MM-DD format.');
        return false;
    }
    const [year, month, day] = presentDate.split('-').map(Number);
    const date = new Date(year, month - 1, day);
    if (isNaN(date.getTime()) || date.getFullYear() !== year || date.getMonth() + 1 !== month || date.getDate() !== day) {
        alert('Invalid present inventory date. Please select a valid calendar date in YYYY-MM-DD format.');
        return false;
    }
    return true;
}

// Initialize Flatpickr on all date inputs
document.addEventListener('DOMContentLoaded', function() {
    flatpickr('input.flatpickr-input', {
        dateFormat: 'Y-m-d',
        allowInput: false, // Prevent manual typing
        allowInvalidPreload: false,
        placeholder: 'YYYY-MM-DD'
    });
});
</script>
</head>
<body>
<div class="container">
<h1>Update Inventory</h1>

<div class="form-section">
<strong>Select Location:</strong>
<form method="get" id="loc_form" style="display: inline-block; margin-left: 15px;">
<select name="location_id" onchange="document.getElementById('loc_form').submit();">
<option value="">Select Location</option>
<?php
$sql = "SELECT * FROM inv_locations ORDER BY name";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $selected = (isset($_GET['location_id']) && $_GET['location_id'] == $row['id']) ? 'selected' : '';
        echo "<option value='{$row['id']}' $selected>" . htmlspecialchars($row['name']) . "</option>";
    }
} else {
    echo "<div class='error-message'>Error fetching locations: " . htmlspecialchars($conn->error) . "</div>";
}
?>
</select>
</form>
</div>

<?php
// Create inv_history table if it doesn't exist
$sql_create_table = "CREATE TABLE IF NOT EXISTS inv_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT,
    location_id INT,
    present_date DATE,
    last_inventory_date DATE,
    last_inventory_qty INT,
    new_issue_qty INT,
    transfer_date DATE,
    transfer_location_id INT,
    transfer_qty INT,
    return_date DATE,
    return_location_id INT,
    return_qty INT,
    damage_qty INT,
    total_qty INT,
    present_qty INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (!$conn->query($sql_create_table)) {
    echo "<div class='error-message'>Error creating inv_history table: " . htmlspecialchars($conn->error) . "</div>";
}

if (isset($_GET['location_id']) && $_GET['location_id'] != '') {
    $location_id = (int)$_GET['location_id'];
    $sql = "SELECT i.*, l.name AS location_name FROM inv_items i JOIN inv_locations l ON i.location_id = l.id WHERE i.location_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "<div class='error-message'>Error preparing statement: " . htmlspecialchars($conn->error) . "</div>";
    } else {
        $stmt->bind_param("i", $location_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            echo "<form method='post' onsubmit='return validateForm()'>
            <input type='hidden' name='location_id' value='$location_id'>
            <div class='form-section'>
            <strong>Present Inventory Date:</strong>
            <input type='text' name='present_date' class='flatpickr-input' required style='margin-left: 15px;' placeholder='YYYY-MM-DD'>
            </div>
            <div class='table-wrapper'>
            <table>
            <tr>
            <th>Item Name</th>
            <th>Location</th>
            <th>Last Date</th>
            <th>Last Qty</th>
            <th>New Issue Qty</th>
            <th>Transfer Date</th>
            <th>Transfer Location</th>
            <th>Transfer Qty</th>
            <th>Return Date</th>
            <th>Return Location</th>
            <th>Return Qty</th>
            <th>Damage Qty</th>
            <th>Total Qty</th>
            <th>Present Qty</th>
            </tr>";
            while ($row = $result->fetch_assoc()) {
                $item_id = $row['id'];
                $initial_total_qty = (int)$row['last_inventory_qty'];
                echo "<tr>
                <td class='item-name'>" . htmlspecialchars($row['name']) . "</td>
                <td>" . htmlspecialchars($row['location_name']) . "</td>
                <td>" . ($row['last_inventory_date'] ? htmlspecialchars($row['last_inventory_date']) : '-') . "</td>
                <td class='last_qty'>" . (int)$row['last_inventory_qty'] . "</td>
                <td><input type='number' name='new_issue[$item_id]' value='0' min='0' oninput='calculateTotal(this.closest(\"tr\"))'></td>
                <td><input type='text' name='transfer_date[$item_id]' class='flatpickr-input' placeholder='YYYY-MM-DD'></td>
                <td><select name='transfer_location[$item_id]'>
                <option value=''>Select</option>";
                $loc_sql = "SELECT * FROM inv_locations WHERE id != ?";
                $loc_stmt = $conn->prepare($loc_sql);
                $loc_stmt->bind_param("i", $location_id);
                $loc_stmt->execute();
                $loc_result = $loc_stmt->get_result();
                while ($loc_row = $loc_result->fetch_assoc()) {
                    echo "<option value='{$loc_row['id']}'>" . htmlspecialchars($loc_row['name']) . "</option>";
                }
                $loc_stmt->close();
                echo "</select></td>
                <td><input type='number' name='transfer_qty[$item_id]' value='0' min='0' oninput='calculateTotal(this.closest(\"tr\"))'></td>
                <td><input type='text' name='return_date[$item_id]' class='flatpickr-input' placeholder='YYYY-MM-DD'></td>
                <td><select name='return_location[$item_id]'>
                <option value=''>Select</option>";
                $loc_stmt = $conn->prepare($loc_sql);
                $loc_stmt->bind_param("i", $location_id);
                $loc_stmt->execute();
                $loc_result = $loc_stmt->get_result();
                while ($loc_row = $loc_result->fetch_assoc()) {
                    echo "<option value='{$loc_row['id']}'>" . htmlspecialchars($loc_row['name']) . "</option>";
                }
                $loc_stmt->close();
                echo "</select></td>
                <td><input type='number' name='return_qty[$item_id]' value='0' min='0' oninput='calculateTotal(this.closest(\"tr\"))'></td>
                <td><input type='number' name='damage_qty[$item_id]' value='0' min='0' oninput='calculateTotal(this.closest(\"tr\"))'></td>
                <td class='total_qty'>" . $initial_total_qty . "</td>
                <td><input type='number' name='present_qty[$item_id]' value='$initial_total_qty' min='0' required></td>
                </tr>";
            }
            echo "</table>
            </div>
            <input type='submit' name='submit_inventory' value='Update Inventory' class='submit-btn'>
            <button type='button' class='print-btn' onclick='printTable()'>Print Table</button>
            </form>";
        } else {
            echo "<div class='no-items'>No items found for this location.</div>";
        }
        $stmt->close();
    }
}

if (isset($_POST['submit_inventory'])) {
    // Log raw POST data for debugging
    error_log("Raw POST data: " . print_r($_POST, true));

    if (!isset($_POST['present_date']) || !isset($_POST['location_id']) || !isset($_POST['present_qty'])) {
        echo "<div class='error-message'>Missing required form data.</div>";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($_POST['present_date'])) || strlen(trim($_POST['present_date'])) !== 10) {
        echo "<div class='error-message'>Invalid present date format. Please use YYYY-MM-DD.</div>";
    } else {
        $present_date = trim($_POST['present_date']);
        // Validate present_date is a real date
        $date_parts = explode('-', $present_date);
        if (!checkdate((int)$date_parts[1], (int)$date_parts[2], (int)$date_parts[0])) {
            echo "<div class='error-message'>Invalid present date: '$present_date'. Must be a valid calendar date.</div>";
        } else {
            $location_id = (int)$_POST['location_id'];
            
            // Begin transaction
            $conn->begin_transaction();
            
            try {
                // Prepare the history insert statement
                $history_sql = "INSERT INTO inv_history (
                    item_id, 
                    location_id, 
                    present_date, 
                    last_inventory_date, 
                    last_inventory_qty, 
                    new_issue_qty, 
                    transfer_date, 
                    transfer_location_id, 
                    transfer_qty, 
                    return_date, 
                    return_location_id, 
                    return_qty, 
                    damage_qty, 
                    total_qty, 
                    present_qty
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($history_sql);
                if (!$stmt) {
                    throw new Exception("Error preparing history insert statement: " . $conn->error);
                }
                
                foreach ($_POST['present_qty'] as $item_id => $present_qty) {
                    $item_id = (int)$item_id;
                    $present_qty = (int)$present_qty;
                    
                    // Get item details
                    $item_sql = "SELECT last_inventory_date, last_inventory_qty, name FROM inv_items WHERE id = ?";
                    $item_stmt = $conn->prepare($item_sql);
                    if (!$item_stmt) {
                        throw new Exception("Error preparing item select statement: " . $conn->error);
                    }
                    $item_stmt->bind_param("i", $item_id);
                    $item_stmt->execute();
                    $item_result = $item_stmt->get_result();
                    if ($item_result->num_rows == 0) {
                        throw new Exception("Item ID $item_id not found.");
                    }
                    $item = $item_result->fetch_assoc();
                    $item_stmt->close();
                    
                    $last_inventory_date = $item['last_inventory_date'] ?: null;
                    $last_inventory_qty = (int)$item['last_inventory_qty'];
                    $new_issue_qty = isset($_POST['new_issue'][$item_id]) ? (int)$_POST['new_issue'][$item_id] : 0;
                    
                    // Process transfer date - store full date or null
                    $transfer_date = null;
                    if (!empty($_POST['transfer_date'][$item_id]) && trim($_POST['transfer_date'][$item_id]) !== '') {
                        $transfer_input = filter_var(trim($_POST['transfer_date'][$item_id]), FILTER_SANITIZE_STRING);
                        error_log("Transfer date input for item ID $item_id: '$transfer_input'");
                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $transfer_input) && strlen($transfer_input) === 10) {
                            $date_parts = explode('-', $transfer_input);
                            if (checkdate((int)$date_parts[1], (int)$date_parts[2], (int)$date_parts[0])) {
                                $transfer_date = $transfer_input;
                            } else {
                                throw new Exception("Invalid transfer date for item '{$item['name']}': '$transfer_input'. Must be a valid date in YYYY-MM-DD format.");
                            }
                        } else {
                            throw new Exception("Invalid transfer date format for item '{$item['name']}': '$transfer_input'. Must be YYYY-MM-DD.");
                        }
                    }
                    
                    $transfer_location_id = !empty($_POST['transfer_location'][$item_id]) ? (int)$_POST['transfer_location'][$item_id] : null;
                    $transfer_qty = isset($_POST['transfer_qty'][$item_id]) ? (int)$_POST['transfer_qty'][$item_id] : 0;
                    
                    // Process return date - store full date or null (identical to transfer_date)
                    $return_date = null;
                    if (!empty($_POST['return_date'][$item_id]) && trim($_POST['return_date'][$item_id]) !== '') {
                        $return_input = filter_var(trim($_POST['return_date'][$item_id]), FILTER_SANITIZE_STRING);
                        error_log("Return date input for item ID $item_id: '$return_input'");
                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $return_input) && strlen($return_input) === 10) {
                            $date_parts = explode('-', $return_input);
                            if (checkdate((int)$date_parts[1], (int)$date_parts[2], (int)$date_parts[0])) {
                                $return_date = $return_input;
                            } else {
                                throw new Exception("Invalid return date for item '{$item['name']}': '$return_input'. Must be a valid date in YYYY-MM-DD format.");
                            }
                        } else {
                            throw new Exception("Invalid return date format for item '{$item['name']}': '$return_input'. Must be YYYY-MM-DD.");
                        }
                    }
                    
                    $return_location_id = !empty($_POST['return_location'][$item_id]) ? (int)$_POST['return_location'][$item_id] : null;
                    $return_qty = isset($_POST['return_qty'][$item_id]) ? (int)$_POST['return_qty'][$item_id] : 0;
                    $damage_qty = isset($_POST['damage_qty'][$item_id]) ? (int)$_POST['damage_qty'][$item_id] : 0;
                    $total_qty = $last_inventory_qty + $new_issue_qty - $transfer_qty - $return_qty - $damage_qty;
                    
                    // Debug binding values
                    error_log("Binding values for item ID $item_id: return_date='" . ($return_date ?? 'NULL') . "', transfer_date='" . ($transfer_date ?? 'NULL') . "'");
                    
                    // Bind parameters for history insert
 $stmt->bind_param(
    "iissiisiisiiiii",
    $item_id,
    $location_id,
    $present_date,
    $last_inventory_date,
    $last_inventory_qty,
    $new_issue_qty,
    $transfer_date,
    $transfer_location_id,
    $transfer_qty,
    $return_date,
    $return_location_id,
    $return_qty,
    $damage_qty,
    $total_qty,
    $present_qty
);


                    
                    if (!$stmt->execute()) {
                        throw new Exception("Error inserting history for item ID $item_id: " . $stmt->error);
                    }
                    
                    // Update inv_items
                    $update_sql = "UPDATE inv_items SET last_inventory_date = ?, last_inventory_qty = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    if (!$update_stmt) {
                        throw new Exception("Error preparing item update statement: " . $conn->error);
                    }
                    $update_stmt->bind_param("sii", $present_date, $present_qty, $item_id);
                    if (!$update_stmt->execute()) {
                        throw new Exception("Error updating item ID $item_id: " . $update_stmt->error);
                    }
                    $update_stmt->close();
                }
                $stmt->close();
                
                // Commit transaction
                $conn->commit();
                echo "<div class='success-message'>Inventory updated successfully and history saved!</div>";
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                echo "<div class='error-message'>Error updating inventory: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }
}
$conn->close();
?>
</div>
</body>
</html>