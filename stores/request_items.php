<?php
// Database Connection
$conn = new mysqli('localhost', 'hotelgrandguardi_root', 'Sun123flower@', 'hotelgrandguardi_wedding_bliss');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $requester_name = $_POST['requester_name'];
    $department = $_POST['department'];
    $request_date = $_POST['request_date'];
    $items = $_POST['items'];
    $quantities = $_POST['quantities'];
    $purposes = $_POST['purposes'];
    
    // Insert request header
    $sql = "INSERT INTO item_requests (requester_name, department, request_date, status) 
            VALUES (?, ?, ?, 'Pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $requester_name, $department, $request_date);
    $stmt->execute();
    $request_id = $stmt->insert_id;
    $stmt->close();
    
    // Insert request items
    for ($i = 0; $i < count($items); $i++) {
        if (!empty($items[$i])) {
            $sql = "INSERT INTO request_items (request_id, item_id, quantity, purpose) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiis", $request_id, $items[$i], $quantities[$i], $purposes[$i]);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    header("Location: request_items.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Request System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .form-control, .form-select {
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
        .item-row {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Item Request System</a>
        </div>
    </nav>

    <div class="container">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Request submitted successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">New Item Request</div>
            <div class="card-body">
                <form method="POST" id="requestForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="requester_name" class="form-label">Requester Name</label>
                            <input type="text" class="form-control" id="requester_name" name="requester_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-select" id="department" name="department" required>
                                <option value="">Select Department</option>
                                <option value="Kitchen">Kitchen</option>
                                <option value="Housekeeping">Housekeeping</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Front Office">Front Office</option>
                                <option value="Management">Management</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="request_date" class="form-label">Request Date</label>
                            <input type="date" class="form-control" id="request_date" name="request_date" required>
                        </div>
                    </div>
                    
                    <h5 class="mt-4">Items Requested</h5>
                    <div id="itemsContainer">
                        <div class="item-row row g-3">
                            <div class="col-md-5">
                                <label class="form-label">Item</label>
                                <select class="form-select item-select" name="items[]" required>
                                    <option value="">Select Item</option>
                                    <?php
                                    $sql = "SELECT id, item_name, stock FROM inventory ORDER BY item_name";
                                    $result = $conn->query($sql);
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='{$row['id']}' data-stock='{$row['stock']}'>{$row['item_name']} (Stock: {$row['stock']})</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Quantity</label>
                                <input type="number" class="form-control quantity" name="quantities[]" min="1" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Purpose</label>
                                <input type="text" class="form-control" name="purposes[]" required>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-danger remove-item">Remove</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <button type="button" id="addItem" class="btn btn-secondary">Add Another Item</button>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Set today's date as default
            $('#request_date').val(new Date().toISOString().substr(0, 10));
            
            // Add new item row
            $('#addItem').click(function() {
                const newRow = $('.item-row:first').clone();
                newRow.find('select').val('');
                newRow.find('input').val('');
                $('#itemsContainer').append(newRow);
            });
            
            // Remove item row
            $(document).on('click', '.remove-item', function() {
                if ($('.item-row').length > 1) {
                    $(this).closest('.item-row').remove();
                } else {
                    alert("You must have at least one item in the request.");
                }
            });
            
            // Validate quantity against available stock
            $(document).on('change', '.item-select', function() {
                const selectedOption = $(this).find('option:selected');
                const maxStock = parseInt(selectedOption.data('stock'));
                const quantityInput = $(this).closest('.item-row').find('.quantity');
                quantityInput.attr('max', maxStock);
                
                if (maxStock <= 0) {
                    alert('This item is out of stock!');
                    $(this).val('');
                }
            });
            
            // Form submission validation
            $('#requestForm').submit(function() {
                let valid = true;
                
                $('.item-row').each(function() {
                    const itemSelect = $(this).find('.item-select');
                    const quantityInput = $(this).find('.quantity');
                    const maxStock = parseInt(itemSelect.find('option:selected').data('stock'));
                    const quantity = parseInt(quantityInput.val());
                    
                    if (quantity > maxStock) {
                        alert(`Requested quantity for ${itemSelect.find('option:selected').text()} exceeds available stock!`);
                        valid = false;
                        return false; // break out of loop
                    }
                });
                
                return valid;
            });
        });
    </script>
</body>
</html>