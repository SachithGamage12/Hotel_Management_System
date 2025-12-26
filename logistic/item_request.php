<?php
// Start session
session_start();

// Set timezone to Asia/Colombo
date_default_timezone_set('Asia/Colombo');

// Database Connection
$conn = new mysqli('localhost', 'hotelgrandguardi_root', 'Sun123flower@', 'hotelgrandguardi_wedding_bliss');
if ($conn->connect_error) {
    die('<div class="alert alert-danger">Connection failed: ' . htmlspecialchars($conn->connect_error) . '</div>');
}
$conn->set_charset("utf8mb4");

// Handle form submission
if (isset($_POST['submit_request'])) {
    $request_date = date('Y-m-d H:i:s');
    $requester_name = trim($_POST['requester_name']);
    $section = trim($_POST['section']);
    $reason = trim($_POST['reason']);
    $last_request_date = trim($_POST['last_request_date']) ?: null;
    $manager_id = trim($_POST['manager_id']);
    $manager_password = $_POST['manager_password'];
    $items = $_POST['items'] ?? [];

    // Validate inputs
    $error = '';
    if (empty($requester_name)) {
        $error = 'Requester name is required.';
    } elseif (empty($section)) {
        $error = 'Section is required.';
    } elseif (empty($reason)) {
        $error = 'Reason for needing goods is required.';
    } elseif (empty($manager_id)) {
        $error = 'Manager selection is required.';
    } elseif (empty($manager_password)) {
        $error = 'Manager password is required.';
    } elseif (empty($items)) {
        $error = 'At least one item is required.';
    } else {
        foreach ($items as $index => $item) {
            if (empty($item['item_id']) || empty($item['quantity']) || empty($item['unit_type'])) {
                $error = "Item details are incomplete for item " . ($index + 1) . ".";
                break;
            }
            $quantity = floatval($item['quantity']);
            if ($quantity <= 0) {
                $error = "Quantity for item " . ($index + 1) . " must be positive.";
                break;
            }
        }
    }

    if (!$error) {
        // Validate manager password
        $stmt = $conn->prepare("SELECT password FROM managers WHERE id = ?");
        if ($stmt === false) {
            $error = "Database error: Unable to prepare manager query.";
            error_log("Prepare failed for manager query: " . $conn->error);
        } else {
            $stmt->bind_param("i", $manager_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $manager = $result->fetch_assoc();
                if (password_verify($manager_password, $manager['password'])) {
                    // Start transaction
                    $conn->begin_transaction();
                    try {
                        // Insert request
                        $stmt_request = $conn->prepare("INSERT INTO item_requests (request_date, requester_name, section, reason, last_request_date, manager_id, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
                        if ($stmt_request === false) {
                            throw new Exception("Prepare failed for item_requests: " . $conn->error);
                        }
                        $stmt_request->bind_param("sssssi", $request_date, $requester_name, $section, $reason, $last_request_date, $manager_id);
                        $stmt_request->execute();
                        $request_id = $stmt_request->insert_id;
                        $stmt_request->close();

                        // Insert items
                        $stmt_items = $conn->prepare("INSERT INTO request_items (request_id, item_id, quantity, unit_type) VALUES (?, ?, ?, ?)");
                        if ($stmt_items === false) {
                            throw new Exception("Prepare failed for request_items: " . $conn->error);
                        }
                        foreach ($items as $item) {
                            $item_id = $item['item_id'];
                            $quantity = floatval($item['quantity']);
                            $unit_type = trim($item['unit_type']);
                            $stmt_items->bind_param("iids", $request_id, $item_id, $quantity, $unit_type);
                            $stmt_items->execute();
                        }
                        $stmt_items->close();

                        // Commit transaction
                        $conn->commit();
                        $success = "Request submitted successfully. Request ID: $request_id. View details at <a href='request_sheet.php?request_id=$request_id'>Request Sheet</a>.";
                    } catch (Exception $e) {
                        $conn->rollback();
                        $error = "Error submitting request: " . $e->getMessage();
                        error_log("Transaction error: " . $e->getMessage());
                    }
                } else {
                    $error = "Invalid manager password.";
                }
            } else {
                $error = "Manager not found.";
            }
            $result->free();
            $stmt->close();
        }
    }
}

// Handle AJAX for item suggestion
if (isset($_GET['term'])) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $term = '%' . trim($_GET['term']) . '%';
        $stmt = $conn->prepare("SELECT id, name, unit_type FROM items WHERE name LIKE ? LIMIT 10");
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("s", $term);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'unit_type' => $row['unit_type']
            ];
        }
        $stmt->close();
        $result->free();
        echo json_encode($items ?: ['message' => 'No items found']);
    } catch (Exception $e) {
        error_log("Item suggestion error: " . $e->getMessage());
        echo json_encode(['error' => 'Failed to fetch items: ' . $e->getMessage()]);
    }
    exit;
}

// Test endpoint for debugging items table
if (isset($_GET['test_items'])) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $stmt = $conn->prepare("SELECT id, name, unit_type FROM items LIMIT 5");
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $stmt->close();
        $result->free();
        echo json_encode($items ?: ['message' => 'No items found']);
    } catch (Exception $e) {
        error_log("Test items error: " . $e->getMessage());
        echo json_encode(['error' => 'Failed to fetch test items: ' . $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Request Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Added Bootstrap Icons for the back arrow -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            position: relative; /* Ensures proper positioning context if needed */
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
        }
        .card-title {
            border-bottom: 1px solid #ced4da;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .form-control, .form-select, .ui-autocomplete-input {
            border-radius: 5px;
            border: 1px solid #ced4da;
        }
        .btn-primary, .btn-secondary, .btn-danger {
            border-radius: 5px;
            padding: 8px 20px;
        }
        .item-row {
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .alert {
            border-radius: 5px;
        }
        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
        }
        @media (max-width: 576px) {
            .card {
                padding: 15px;
            }
            .item-row {
                padding: 10px;
            }
            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Back Button - Top Left Corner -->
        <div class="d-flex justify-content-start mb-3">
            <a href="../order.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Orders
            </a>
        </div>

        <!-- Alerts -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Request Form -->
        <div class="card">
            <h4 class="card-title">Item Request Form</h4>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="request_date" class="form-label">Request Date</label>
                    <input type="text" class="form-control" id="request_date" value="<?php echo date('Y-m-d H:i:s'); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="requester_name" class="form-label">Requester Name</label>
                    <input type="text" class="form-control" id="requester_name" name="requester_name" required>
                </div>
                <div class="mb-3">
                    <label for="section" class="form-label">Section</label>
                    <select class="form-select" id="section" name="section" required>
                        <option value="">Select Section</option>
                        <option value="Main office">Main office</option>
                        <option value="Back office">Back office</option>
                        <option value="Kitchen">Kitchen</option>
                        <option value="Pastry">Pastry</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="IT and Network">IT and Network</option>
                        <option value="Security">Security</option>
                        <option value="House Keeping">House Keeping</option>
                        <option value="Cleaning">Cleaning</option>
                    </select>
                </div>
                <div id="items-container">
                    <div class="item-row">
                        <label class="form-label">Item</label>
                        <input type="text" class="form-control item-name" name="items[0][item_name]" placeholder="Type to search item" required>
                        <input type="hidden" class="item-id" name="items[0][item_id]">
                        <div class="mt-2">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" step="0.01" class="form-control" name="items[0][quantity]" min="0.01" required>
                        </div>
                        <div class="mt-2">
                            <label for="unit_type" class="form-label">Unit Type</label>
                            <input type="text" class="form-control unit-type" name="items[0][unit_type]" required>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary mb-3" id="add-item">Add Item</button>
                <div class="mb-3">
                    <label for="reason" class="form-label">Reason for Needing Goods</label>
                    <textarea class="form-control" id="reason" name="reason" rows="4" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="last_request_date" class="form-label">Date of Last Request (Optional)</label>
                    <input type="date" class="form-control" id="last_request_date" name="last_request_date">
                </div>
                <div class="mb-3">
                    <label for="manager_id" class="form-label">Manager Name</label>
                    <select class="form-select" id="manager_id" name="manager_id" required>
                        <option value="">Select Manager</option>
                        <?php
                        $stmt = $conn->prepare("SELECT id, username FROM managers ORDER BY username");
                        if ($stmt) {
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>" . htmlspecialchars($row['username']) . "</option>";
                            }
                            $stmt->close();
                            $result->free();
                        } else {
                            echo "<option value=''>Error loading managers: " . htmlspecialchars($conn->error) . "</option>";
                            error_log("Prepare failed for managers query: " . $conn->error);
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="manager_password" class="form-label">Manager Password</label>
                    <input type="password" class="form-control" id="manager_password" name="manager_password" required>
                </div>
                <button type="submit" name="submit_request" class="btn btn-primary">Submit Request</button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let itemIndex = 1;

        function initAutocomplete(input) {
            console.log("Initializing autocomplete for input:", input);
            input.autocomplete({
                source: function(request, response) {
                    console.log("Sending AJAX request for term:", request.term);
                    $.ajax({
                        url: window.location.pathname,
                        dataType: "json",
                        data: { term: request.term },
                        success: function(data) {
                            console.log("AJAX Success:", data);
                            if (data.error) {
                                response([{ label: "Error: " + data.error, value: "" }]);
                            } else if (!data.length && !data.message) {
                                response([{ label: "No results found", value: "" }]);
                            } else {
                                response($.map(data, function(item) {
                                    return {
                                        label: item.name + " (" + item.unit_type + ")",
                                        value: item.name,
                                        id: item.id,
                                        unit_type: item.unit_type
                                    };
                                }));
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", status, error, xhr.responseText);
                            response([{ label: "Error fetching items: " + (xhr.responseText || status), value: "" }]);
                        }
                    });
                },
                minLength: 2,
                select: function(event, ui) {
                    console.log("Item selected:", ui.item);
                    if (ui.item.id) {
                        const row = $(this).closest('.item-row');
                        $(this).val(ui.item.value);
                        row.find('.item-id').val(ui.item.id);
                        row.find('.unit-type').val(ui.item.unit_type);
                    }
                    return false;
                },
                open: function() {
                    console.log("Autocomplete dropdown opened");
                },
                close: function() {
                    console.log("Autocomplete dropdown closed");
                }
            });
        }

        $(document).ready(function() {
            // Check if jQuery UI is loaded
            if (!$.ui) {
                console.error("jQuery UI not loaded");
                alert("jQuery UI failed to load. Please check your internet connection or CDN availability.");
                return;
            }
            console.log("jQuery UI loaded successfully");

            // Initialize autocomplete for the first item
            initAutocomplete($('.item-name').first());

            // Test AJAX endpoint
            $.ajax({
                url: window.location.pathname + "?test_items=1",
                dataType: "json",
                success: function(data) {
                    console.log("Test items endpoint response:", data);
                },
                error: function(xhr, status, error) {
                    console.error("Test items endpoint error:", status, error, xhr.responseText);
                }
            });

            // Add new item row
            $('#add-item').click(function() {
                console.log("Adding new item row, index:", itemIndex);
                const newRow = `
                    <div class="item-row">
                        <label class="form-label">Item</label>
                        <input type="text" class="form-control item-name" name="items[${itemIndex}][item_name]" placeholder="Type to search item" required>
                        <input type="hidden" class="item-id" name="items[${itemIndex}][item_id]">
                        <div class="mt-2">
                            <label class="form-label">Quantity</label>
                            <input type="number" step="0.01" class="form-control" name="items[${itemIndex}][quantity]" min="0.01" required>
                        </div>
                        <div class="mt-2">
                            <label for="unit_type" class="form-label">Unit Type</label>
                            <input type="text" class="form-control unit-type" name="items[${itemIndex}][unit_type]" required>
                        </div>
                        <button type="button" class="btn btn-danger mt-2 remove-item">Remove</button>
                    </div>
                `;
                $('#items-container').append(newRow);
                initAutocomplete($('#items-container .item-name').last());
                itemIndex++;
            });

            // Remove item row
            $(document).on('click', '.remove-item', function() {
                console.log("Removing item row");
                $(this).closest('.item-row').remove();
            });

            // Client-side form validation
            $('form').on('submit', function(e) {
                let valid = true;
                if (!$('#requester_name').val()) {
                    alert('Requester name is required.');
                    valid = false;
                }
                if (!$('#section').val()) {
                    alert('Section is required.');
                    valid = false;
                }
                if (!$('#reason').val()) {
                    alert('Reason is required.');
                    valid = false;
                }
                if (!$('#manager_id').val()) {
                    alert('Manager selection is required.');
                    valid = false;
                }
                if (!$('#manager_password').val()) {
                    alert('Manager password is required.');
                    valid = false;
                }
                $('.item-row').each(function(index) {
                    if (!$(this).find('.item-id').val()) {
                        alert('Please select a valid item for item ' + (index + 1) + '.');
                        valid = false;
                    }
                    const quantity = parseFloat($(this).find('input[name$="[quantity]"]').val());
                    if (isNaN(quantity) || quantity <= 0) {
                        alert('Quantity for item ' + (index + 1) + ' must be a positive number.');
                        valid = false;
                    }
                    if (!$(this).find('.unit-type').val()) {
                        alert('Unit type for item ' + (index + 1) + ' is required.');
                        valid = false;
                    }
                });
                if (!valid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>