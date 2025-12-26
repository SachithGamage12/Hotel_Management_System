<?php
require_once '../config/db_connection.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    error_log("Redirecting to logistic_login.php: Session username or user_id not set");
    header("Location: ./logistic_login.php");
    exit();
}

// Set timezone to Sri Lanka Standard Time
date_default_timezone_set('Asia/Colombo');

$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'validate_credentials') {
    try {
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Username and password are required']);
            exit;
        }

        $sql = "SELECT id, password FROM logestic_users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid username']);
            exit;
        }

        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['confirmed_by'] = $user['id'];
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid password']);
        }
        exit;
    } catch (Exception $e) {
        error_log("Error in credential validation: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create_po') {
    try {
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        if (!isset($_SESSION['confirmed_by'])) {
            throw new Exception("User not authenticated");
        }

        if (!isset($_SESSION['user_id'])) {
            error_log("Error: user_id not set in session");
            throw new Exception("Logged-in user ID not found");
        }

        if (!isset($_POST['supplier_id']) || empty($_POST['supplier_id'])) {
            throw new Exception("Supplier selection is required");
        }

        $sql = "SELECT MAX(po_number) AS max_po FROM logistic_purchase_orders";
        $result = $conn->query($sql);
        if ($result === false) {
            error_log("PO number query failed: " . $conn->error);
            throw new Exception("PO number query failed: " . $conn->error);
        }
        $row = $result->fetch_assoc();
        $po_number = ($row['max_po'] ? $row['max_po'] + 1 : 1500);

        $po_datetime = date('Y-m-d H:i:s');
        $supplier_id = (int)$_POST['supplier_id'];
        $confirmed_by = (int)$_SESSION['confirmed_by'];
        $requested_by = (int)$_SESSION['user_id'];
        error_log("Attempting to insert PO: po_number=$po_number, supplier_id=$supplier_id, confirmed_by=$confirmed_by, requested_by=$requested_by");

        $sql = "INSERT INTO logistic_purchase_orders (po_number, created_at, supplier_id, confirmed_by, requested_by) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("isiii", $po_number, $po_datetime, $supplier_id, $confirmed_by, $requested_by);
        if (!$stmt->execute()) {
            throw new Exception("Insert failed: " . $stmt->error);
        }
        $po_id = $stmt->insert_id;
        if ($po_id <= 0) {
            throw new Exception("Failed to retrieve valid PO ID after insert");
        }
        error_log("Created PO with id: $po_id, po_number: $po_number, supplier_id: $supplier_id, requested_by: $requested_by");

        if (!isset($_POST['item_id']) || !is_array($_POST['item_id'])) {
            throw new Exception("Invalid item data submitted");
        }
        $item_ids = $_POST['item_id'];
        $quantities = $_POST['quantity'];
        $units = $_POST['unit'];

        for ($i = 0; $i < count($item_ids); $i++) {
            if (!empty($item_ids[$i]) && !empty($quantities[$i]) && !empty($units[$i])) {
                $sql = "INSERT INTO logistic_po_items (po_id, item_id, quantity, unit) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    throw new Exception("Prepare failed for items: " . $conn->error);
                }
                $stmt->bind_param("iids", $po_id, $item_ids[$i], $quantities[$i], $units[$i]);
                if (!$stmt->execute()) {
                    throw new Exception("Insert failed for item $i: " . $stmt->error);
                }
                error_log("Inserted item: item_id={$item_ids[$i]}, quantity={$quantities[$i]}, unit={$units[$i]} for po_id=$po_id");
            }
        }

        $_SESSION['print_po'] = $po_id;
        unset($_SESSION['confirmed_by']);
        error_log("Stored PO id in session: $po_id");
        echo json_encode(['success' => true, 'redirect' => 'print_po.php']);
        exit;
    } catch (Exception $e) {
        error_log("Error in create_po.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => "Error creating PO: " . $e->getMessage()]);
        exit;
    }
}

// Fetch items for datalist
$sql = "SELECT id, name, unit_type FROM items";
$items_result = $conn->query($sql);
if ($items_result === false) {
    error_log("Items query failed: " . $conn->error);
}

// Fetch users for dropdown
$sql = "SELECT id, username FROM logestic_users";
$users_result = $conn->query($sql);
if ($users_result === false) {
    error_log("Users query failed: " . $conn->error);
}

// Fetch suppliers for dropdown
$sql = "SELECT id, name FROM suppliers";
$suppliers_result = $conn->query($sql);
if ($suppliers_result === false) {
    error_log("Suppliers query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Purchase Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 10px;
            font-weight: bold;
        }
        input, select {
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .button-row {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .add-btn, .submit-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .add-btn {
            background-color: #4CAF50;
            color: white;
        }
        .add-btn:hover {
            background-color: #45a049;
        }
        .submit-btn {
            background-color: #2196F3;
            color: white;
        }
        .submit-btn:hover {
            background-color: #0b7dda;
        }
        .remove-btn {
            padding: 5px 10px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[list] {
            position: relative;
        }
        input[readonly] {
            background-color: #f0f0f0;
            cursor: not-allowed;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
        }
        .modal-content h2 {
            margin-top: 0;
        }
        .modal-content label {
            display: block;
            margin-bottom: 5px;
        }
        .modal-content select, .modal-content input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }
        .modal-content button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .modal-content .submit-credentials {
            background-color: #2196F3;
            color: white;
        }
        .modal-content .cancel-btn {
            background-color: #f44336;
            color: white;
            margin-left: 10px;
        }
        .error-message {
            color: #a94442;
            margin-bottom: 10px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
         <button onclick="window.location.href='../logistic.php'" 
                style="background-color: #f09424ff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                Back
            </button>
        <?php if (isset($_SESSION['error'])): ?>
            <div style="background: #f2dede; color: #a94442; padding: 10px; margin-bottom: 10px;">
                <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        <h1>Create Purchase Order</h1>
        <form id="po-form" method="POST">
            <?php
            $sql = "SELECT MAX(po_number) AS max_po FROM logistic_purchase_orders";
            $result = $conn->query($sql);
            if ($result === false) {
                error_log("PO number query failed: " . $conn->error);
                $next_po = 1500; // Fallback if query fails
            } else {
                $row = $result->fetch_assoc();
                $next_po = ($row['max_po'] ? $row['max_po'] + 1 : 1500);
            }
            ?>
            <label>PO Number:</label>
            <input type="text" value="<?php echo htmlspecialchars($next_po); ?>" readonly>
            <label>PO Date & Time:</label>
            <input type="text" value="<?php echo date('d-M-Y H:i'); ?>" readonly>
            <label>Requested By:</label>
            <input type="text" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" readonly>
            <label>Supplier:</label>
            <select name="supplier_id" id="supplier_id" required>
                <option value="">Select a supplier</option>
                <?php
                if ($suppliers_result && $suppliers_result->num_rows > 0) {
                    while ($row = $suppliers_result->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['name']) . '</option>';
                    }
                }
                ?>
            </select>
            <h2>Items</h2>
            <table id="items-table">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="item-row">
                        <td>
                            <input type="text" name="item_name[]" list="items" class="item-name" required>
                            <input type="hidden" name="item_id[]" class="item-id">
                        </td>
                        <td><input type="number" step="0.01" name="quantity[]" required></td>
                        <td><input type="text" name="unit[]" class="unit-type" readonly required></td>
                        <td><button type="button" class="remove-btn" style="display:none;">Remove</button></td>
                    </tr>
                </tbody>
            </table>
            <div class="button-row">
                <button type="button" class="add-btn">Add Item</button>
                <button type="button" class="submit-btn" onclick="showModal()">Create PO</button>
            </div>
        </form>
    </div>
    <div id="auth-modal" class="modal">
        <div class="modal-content">
            <h2>Authenticate</h2>
            <div id="error-message" class="error-message"></div>
            <label for="username">Username:</label>
            <select id="username" name="username">
                <option value="">Select a user</option>
                <?php
                if ($users_result && $users_result->num_rows > 0) {
                    while ($row = $users_result->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($row['username']) . '">' . htmlspecialchars($row['username']) . '</option>';
                    }
                }
                ?>
            </select>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password">
            <div>
                <button class="submit-credentials" onclick="validateCredentials()">Submit</button>
                <button class="cancel-btn" onclick="closeModal()">Cancel</button>
            </div>
        </div>
    </div>
    <datalist id="items">
        <?php
        if ($items_result && $items_result->num_rows > 0) {
            while ($row = $items_result->fetch_assoc()) {
                echo '<option value="' . htmlspecialchars($row['name']) . '" data-id="' . $row['id'] . '" data-unit="' . htmlspecialchars($row['unit_type']) . '">';
            }
        }
        ?>
    </datalist>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('items-table').querySelector('tbody');
            const addBtn = document.querySelector('.add-btn');
            addBtn.addEventListener('click', function() {
                const supplierId = document.getElementById('supplier_id').value;
                if (!supplierId) {
                    alert('Please select a supplier before adding items.');
                    return;
                }
                const newRow = document.createElement('tr');
                newRow.classList.add('item-row');
                newRow.innerHTML = `
                    <td>
                        <input type="text" name="item_name[]" list="items" class="item-name" required>
                        <input type="hidden" name="item_id[]" class="item-id">
                    </td>
                    <td><input type="number" step="0.01" name="quantity[]" required></td>
                    <td><input type="text" name="unit[]" class="unit-type" readonly required></td>
                    <td><button type="button" class="remove-btn" style="display:inline-block;">Remove</button></td>
                `;
                table.appendChild(newRow);
                attachEventListeners(newRow);
            });
            table.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-btn')) {
                    if (table.querySelectorAll('.item-row').length > 1) {
                        e.target.closest('.item-row').remove();
                    }
                }
            });
            function attachEventListeners(row) {
                const input = row.querySelector('.item-name');
                input.addEventListener('change', function() {
                    const options = document.getElementById('items').options;
                    for (let option of options) {
                        if (option.value === input.value) {
                            row.querySelector('.item-id').value = option.getAttribute('data-id');
                            row.querySelector('.unit-type').value = option.getAttribute('data-unit');
                            return;
                        }
                    }
                    row.querySelector('.item-id').value = '';
                    row.querySelector('.unit-type').value = '';
                    alert('Please select a valid item from the list.');
                });
            }
            attachEventListeners(table.querySelector('.item-row'));
            document.getElementById('po-form').addEventListener('submit', function(e) {
                const supplierId = document.getElementById('supplier_id').value;
                if (!supplierId) {
                    e.preventDefault();
                    alert('Please select a supplier.');
                }
            });
        });
        function showModal() {
            const supplierId = document.getElementById('supplier_id').value;
            if (!supplierId) {
                alert('Please select a supplier before creating the PO.');
                return;
            }
            document.getElementById('auth-modal').style.display = 'flex';
            document.getElementById('error-message').style.display = 'none';
            document.getElementById('username').value = '';
            document.getElementById('password').value = '';
        }
        function closeModal() {
            document.getElementById('auth-modal').style.display = 'none';
        }
        function validateCredentials() {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const errorMessage = document.getElementById('error-message');
            fetch('create_po.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=validate_credentials&username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const form = document.getElementById('po-form');
                    const formData = new FormData(form);
                    formData.append('action', 'create_po');
                    fetch('create_po.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = data.redirect;
                        } else {
                            alert('Error creating PO: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('Error submitting form: ' + error.message);
                    });
                } else {
                    errorMessage.textContent = data.message;
                    errorMessage.style.display = 'block';
                }
            })
            .catch(error => {
                errorMessage.textContent = 'Error validating credentials: ' . error.message;
                errorMessage.style.display = 'block';
            });
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>