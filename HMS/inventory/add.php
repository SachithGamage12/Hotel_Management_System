
<?php
include 'db.php';

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<html>
<head>
<title>Add Inventory Item</title>
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
        max-width: 800px;
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
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }

    input[type="text"], textarea {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        background: white;
    }

    input[type="text"]:focus, textarea:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0,123,255,0.3);
    }

    textarea {
        resize: vertical;
        min-height: 80px;
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
        display: block;
        margin: 20px auto 0;
    }

    .submit-btn:hover {
        background: #218838;
    }

    .add-btn {
        background: #007bff;
        color: white;
        padding: 8px 15px;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        cursor: pointer;
        margin: 10px 0;
    }

    .add-btn:hover {
        background: #0056b3;
    }

    .remove-btn {
        background: #dc3545;
        color: white;
        padding: 6px 10px;
        border: none;
        border-radius: 4px;
        font-size: 12px;
        cursor: pointer;
    }

    .remove-btn:hover {
        background: #c82333;
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

    .item-row {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
        align-items: flex-start;
    }

    .item-row > div {
        flex: 1;
    }

    .item-row .remove-btn {
        flex: 0 0 auto;
        margin-top: 28px;
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
        .item-row {
            flex-direction: column;
        }
        .item-row .remove-btn {
            margin-top: 10px;
        }
    }
</style>
<script>
function addItemRow() {
    const container = document.getElementById('items-container');
    const rowCount = container.querySelectorAll('.item-row').length;
    const newRow = document.createElement('div');
    newRow.className = 'item-row';
    newRow.innerHTML = `
        <div>
            <label for="item_name_${rowCount}">Item Name:</label>
            <input type="text" name="items[${rowCount}][item_name]" id="item_name_${rowCount}" required>
        </div>
        <div>
            <label for="item_type_${rowCount}">Type:</label>
            <input type="text" name="items[${rowCount}][item_type]" id="item_type_${rowCount}">
        </div>
        <div>
            <label for="remarks_${rowCount}">Remarks:</label>
            <textarea name="items[${rowCount}][remarks]" id="remarks_${rowCount}"></textarea>
        </div>
        <button type="button" class="remove-btn" onclick="removeItemRow(this)">Remove</button>
    `;
    container.appendChild(newRow);
    updateRemoveButtons();
}

function removeItemRow(button) {
    button.parentElement.remove();
    updateRemoveButtons();
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('.item-row');
    const removeButtons = document.querySelectorAll('.remove-btn');
    if (rows.length <= 1) {
        removeButtons.forEach(btn => btn.style.display = 'none');
    } else {
        removeButtons.forEach(btn => btn.style.display = 'inline-block');
    }
}

function validateForm() {
    const location = document.querySelector('input[name="location"]').value.trim();
    const itemRows = document.querySelectorAll('.item-row');

    if (location === '') {
        alert('Location is required.');
        return false;
    }
    if (location.length > 100) {
        alert('Location name cannot exceed 100 characters.');
        return false;
    }

    if (itemRows.length === 0) {
        alert('At least one item is required.');
        return false;
    }

    for (let i = 0; i < itemRows.length; i++) {
        const itemName = itemRows[i].querySelector(`input[name="items[${i}][item_name]"]`).value.trim();
        const itemType = itemRows[i].querySelector(`input[name="items[${i}][item_type]"]`).value.trim();
        const remarks = itemRows[i].querySelector(`textarea[name="items[${i}][remarks]"]`).value.trim();

        if (itemName === '') {
            alert(`Item Name is required for item ${i + 1}.`);
            return false;
        }
        if (itemName.length > 100) {
            alert(`Item Name cannot exceed 100 characters for item ${i + 1}.`);
            return false;
        }
        if (itemType.length > 50) {
            alert(`Item Type cannot exceed 50 characters for item ${i + 1}.`);
            return false;
        }
        if (remarks.length > 500) {
            alert(`Remarks cannot exceed 500 characters for item ${i + 1}.`);
            return false;
        }
    }
    return true;
}

document.addEventListener('DOMContentLoaded', () => {
    updateRemoveButtons();
});
</script>
</head>
<body>
<div class="container">
<h1>Add Inventory Items</h1>

<?php
if (isset($_POST['submit'])) {
    $location = trim($_POST['location'] ?? '');
    $items = $_POST['items'] ?? [];

    // Server-side validation
    $errors = [];
    if (empty($location)) {
        $errors[] = 'Location is required.';
    } elseif (strlen($location) > 100) {
        $errors[] = 'Location name cannot exceed 100 characters.';
    }
    if (empty($items)) {
        $errors[] = 'At least one item is required.';
    } else {
        foreach ($items as $index => $item) {
            $item_name = trim($item['item_name'] ?? '');
            $item_type = trim($item['item_type'] ?? '');
            $remarks = trim($item['remarks'] ?? '');
            if (empty($item_name)) {
                $errors[] = "Item Name is required for item " . ($index + 1) . ".";
            } elseif (strlen($item_name) > 100) {
                $errors[] = "Item Name cannot exceed 100 characters for item " . ($index + 1) . ".";
            }
            if (strlen($item_type) > 50) {
                $errors[] = "Item Type cannot exceed 50 characters for item " . ($index + 1) . ".";
            }
            if (strlen($remarks) > 500) {
                $errors[] = "Remarks cannot exceed 500 characters for item " . ($index + 1) . ".";
            }
        }
    }

    if (empty($errors)) {
        try {
            // Begin transaction
            $conn->begin_transaction();

            // Check if location exists (case-insensitive)
            $sql = "SELECT id FROM inv_locations WHERE LOWER(name) = LOWER(?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error preparing location select statement: " . $conn->error);
            }
            $stmt->bind_param("s", $location);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 0) {
                // Add new location
                $sql2 = "INSERT INTO inv_locations (name) VALUES (?)";
                $stmt2 = $conn->prepare($sql2);
                if (!$stmt2) {
                    throw new Exception("Error preparing location insert statement: " . $conn->error);
                }
                $stmt2->bind_param("s", $location);
                if (!$stmt2->execute()) {
                    throw new Exception("Error inserting location: " . $stmt2->error);
                }
                $location_id = $stmt2->insert_id;
                $stmt2->close();
            } else {
                $row = $result->fetch_assoc();
                $location_id = $row['id'];
            }
            $stmt->close();

            // Add items
            $today = date("Y-m-d");
            $initial_qty = 0;
            $sql3 = "INSERT INTO inv_items (location_id, name, type, remarks, last_inventory_date, last_inventory_qty) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt3 = $conn->prepare($sql3);
            if (!$stmt3) {
                throw new Exception("Error preparing item insert statement: " . $conn->error);
            }
            foreach ($items as $item) {
                $item_name = trim($item['item_name']);
                $item_type = trim($item['item_type'] ?? '');
                $remarks = trim($item['remarks'] ?? '');
                $stmt3->bind_param("issssi", $location_id, $item_name, $item_type, $remarks, $today, $initial_qty);
                if (!$stmt3->execute()) {
                    throw new Exception("Error inserting item '$item_name': " . $stmt3->error);
                }
            }
            $stmt3->close();

            // Commit transaction
            $conn->commit();
            echo "<div class='success-message'>Items added successfully.</div>";
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            echo "<div class='error-message'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        foreach ($errors as $error) {
            echo "<div class='error-message'>" . htmlspecialchars($error) . "</div>";
        }
    }
}
$conn->close();
?>

<div class="form-section">
<form method="post" onsubmit="return validateForm()">
    <label for="location">Location:</label>
    <input type="text" name="location" id="location" required>
    
    <div id="items-container">
        <div class="item-row">
            <div>
                <label for="item_name_0">Item Name:</label>
                <input type="text" name="items[0][item_name]" id="item_name_0" required>
            </div>
            <div>
                <label for="item_type_0">Type:</label>
                <input type="text" name="items[0][item_type]" id="item_type_0">
            </div>
            <div>
                <label for="remarks_0">Remarks:</label>
                <textarea name="items[0][remarks]" id="remarks_0"></textarea>
            </div>
            <button type="button" class="remove-btn" onclick="removeItemRow(this)">Remove</button>
        </div>
    </div>
    
    <button type="button" class="add-btn" onclick="addItemRow()">Add Another Item</button>
    <input type="submit" name="submit" value="Add Items" class="submit-btn">
</form>
</div>
</div>
</body>
</html>
