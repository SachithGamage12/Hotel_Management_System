<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Buffer Stock Management</title>
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
        .modal-content {
            border-radius: 10px;
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
            <a class="navbar-brand" href="#">Store Buffer Stock Management</a>
            <div class="ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form action="" method="POST" class="d-inline">
                        <button type="submit" name="logout" class="btn btn-light btn-sm">Logout</button>
                    </form>
                <?php else: ?>
                    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Login Modal -->
    <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="loginModalLabel">Login to Edit Stock</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Username</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="container">
        <!-- Alerts -->
        <?php
        // Database Connection
        $conn = new mysqli('localhost', 'hotelgrandguardi_root', 'Sun123flower@', 'hotelgrandguardi_wedding_bliss');
        if ($conn->connect_error) {
            die('<div class="alert alert-danger alert-dismissible fade show" role="alert">Database connection failed: ' . $conn->connect_error . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        // Handle Login
        if (isset($_POST['login'])) {
            $name = $_POST['name'];
            $password = $_POST['password'];
            $sql = "SELECT * FROM responsibilities WHERE name = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Login prepare failed: ' . $conn->error . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            } else {
                $stmt->bind_param("s", $name);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    if (password_verify($password, $user['password'])) {
                        $_SESSION['user_id'] = $user['id'];
                        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Login successful!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                    } else {
                        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Invalid password!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                    }
                } else {
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Invalid username!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                }
                $stmt->close();
            }
        }

        // Handle Logout
        if (isset($_POST['logout'])) {
            session_destroy();
            $_SESSION = array();
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Logged out successfully!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        }

        // Handle Edit Form Submission
        if (isset($_POST['edit_item'])) {
            if (isset($_SESSION['user_id'])) {
                $item_id = $_POST['item_id'];
                $buffer_stock = (int)$_POST['buffer_stock'];
                $unit = $_POST['unit'];

                $sql = "UPDATE inventory SET buffer_stock = ?, unit = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Prepare failed: ' . $conn->error . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                } else {
                    $stmt->bind_param("isi", $buffer_stock, $unit, $item_id);
                    if ($stmt->execute()) {
                        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Item updated successfully!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                    } else {
                        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Error updating item: ' . $conn->error . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                    }
                    $stmt->close();
                }
            } else {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Unauthorized access!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            }
        }

        // Handle Add Item Form Submission
        if (isset($_POST['add_item'])) {
            $item_name = trim($_POST['item_name']);
            $category = $_POST['category'];
            $buffer_stock = (int)$_POST['buffer_stock'];
            $unit = $_POST['unit'];

            // Validate inputs
            if (empty($item_name)) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Item name cannot be empty!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            } elseif ($buffer_stock < 0) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Buffer stock must be non-negative!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            } else {
                // Check for duplicate item name (case-insensitive)
                $sql = "SELECT COUNT(*) as count FROM inventory WHERE LOWER(item_name) = LOWER(?)";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Prepare failed: ' . $conn->error . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                } else {
                    $stmt->bind_param("s", $item_name);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $stmt->close();

                    if ($row['count'] > 0) {
                        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Item name \'' . htmlspecialchars($item_name) . '\' already exists!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                    } else {
                        $sql = "INSERT INTO inventory (item_name, category, buffer_stock, unit) VALUES (?, ?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        if ($stmt === false) {
                            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Prepare failed: ' . $conn->error . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                        } else {
                            $stmt->bind_param("ssis", $item_name, $category, $buffer_stock, $unit);
                            if ($stmt->execute()) {
                                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Item added successfully!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                            } else {
                                if ($conn->errno == 1062) {
                                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Item name \'' . htmlspecialchars($item_name) . '\' already exists!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                                } else {
                                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Error adding item: ' . $conn->error . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                                }
                            }
                            $stmt->close();
                        }
                    }
                }
            }
        }
        ?>

        <!-- Form to Add Item -->
        <div class="card mb-4">
            <div class="card-header">Add New Item</div>
            <div class="card-body">
                <form action="" method="POST" id="addItemForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="item_name" class="form-label">Item Name</label>
                            <input type="text" class="form-control" id="item_name" name="item_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="Dry Items">Dry Items</option>
                                <option value="Beverages">Beverages</option>
                                <option value="Fruits">Fruits</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="buffer_stock" class="form-label">Buffer Stock Level</label>
                            <input type="number" class="form-control" id="buffer_stock" name="buffer_stock" min="0" step="1" required>
                        </div>
                        <div class="col-md-6">
                            <label for="unit" class="form-label">Unit</label>
                            <select class="form-select" id="unit" name="unit" required>
                                <option value="units">Units</option>
                                <option value="kg">Kilograms (kg)</option>
                                <option value="g">Grams (g)</option>
                                <option value="mg">Milligrams (mg)</option>
                                <option value="liters">Liters (L)</option>
                                <option value="milliliters">Milliliters (mL)</option>
                                <option value="tin">Tin</option>
                                <option value="packet">Packet</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" name="add_item" class="btn btn-primary w-100">Add Item</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Search Form -->
        <div class="card mb-4">
            <div class="card-header">Search Stock</div>
            <div class="card-body">
                <form action="" method="GET">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search by item name" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <?php if (isset($_GET['search']) && $_GET['search']): ?>
                            <a href="hotel_buffer_stock.php" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Display Stock Table -->
        <div class="card">
            <div class="card-header">Current Stock</div>
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Buffer Stock</th>
                            <th>Unit</th>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <th>Action</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Handle Search and Display Stock
                        $search = isset($_GET['search']) ? $_GET['search'] : '';
                        $stmt_active = false;
                        if ($search) {
                            $sql = "SELECT * FROM inventory WHERE item_name LIKE ?";
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
                            $sql = "SELECT * FROM inventory";
                            $result = $conn->query($sql);
                            if ($result === false) {
                                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Query failed: ' . $conn->error . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                            }
                        }

                        if (isset($result) && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                    <td>" . htmlspecialchars($row['item_name']) . "</td>
                                    <td>" . htmlspecialchars($row['category']) . "</td>
                                    <td>{$row['buffer_stock']}</td>
                                    <td>" . htmlspecialchars($row['unit']) . "</td>";
                                if (isset($_SESSION['user_id'])) {
                                    echo "<td>
                                        <button class='btn btn-sm btn-warning' data-bs-toggle='modal' data-bs-target='#editModal{$row['id']}'>Edit</button>
                                        <!-- Edit Modal -->
                                        <div class='modal fade' id='editModal{$row['id']}' tabindex='-1' aria-labelledby='editModalLabel{$row['id']}' aria-hidden='true'>
                                            <div class='modal-dialog'>
                                                <div class='modal-content'>
                                                    <div class='modal-header'>
                                                        <h5 class='modal-title' id='editModalLabel{$row['id']}'>Edit Item: " . htmlspecialchars($row['item_name']) . "</h5>
                                                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                                    </div>
                                                    <div class='modal-body'>
                                                        <form action='' method='POST' class='edit-item-form'>
                                                            <input type='hidden' name='item_id' value='{$row['id']}'>
                                                            <div class='mb-3'>
                                                                <label for='buffer_stock{$row['id']}' class='form-label'>Buffer Stock Level</label>
                                                                <input type='number' class='form-control' id='buffer_stock{$row['id']}' name='buffer_stock' value='{$row['buffer_stock']}' min='0' step='1' required>
                                                            </div>
                                                            <div class='mb-3'>
                                                                <label for='unit{$row['id']}' class='form-label'>Unit</label>
                                                                <select class='form-select' id='unit{$row['id']}' name='unit' required>
                                                                    <option value='units'" . ($row['unit'] == 'units' ? ' selected' : '') . ">Units</option>
                                                                    <option value='kg'" . ($row['unit'] == 'kg' ? ' selected' : '') . ">Kilograms (kg)</option>
                                                                    <option value='g'" . ($row['unit'] == 'g' ? ' selected' : '') . ">Grams (g)</option>
                                                                    <option value='mg'" . ($row['unit'] == 'mg' ? ' selected' : '') . ">Milligrams (mg)</option>
                                                                    <option value='liters'" . ($row['unit'] == 'liters' ? ' selected' : '') . ">Liters (L)</option>
                                                                    <option value='milliliters'" . ($row['unit'] == 'milliliters' ? ' selected' : '') . ">Milliliters (mL)</option>
                                                                    <option value='tin'" . ($row['unit'] == 'tin' ? ' selected' : '') . ">Tin</option>
                                                                    <option value='packet'" . ($row['unit'] == 'packet' ? ' selected' : '') . ">Packet</option>
                                                                </select>
                                                            </div>
                                                            <button type='submit' name='edit_item' class='btn btn-primary'>Save Changes</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>";
                                }
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='" . (isset($_SESSION['user_id']) ? 5 : 4) . "' class='text-center'>No items found</td></tr>";
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enforce integer input for number fields
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value && parseInt(this.value) < 0) {
                    this.value = 0;
                }
            });
        });

        // Validate add item form on submit
        document.getElementById('addItemForm').addEventListener('submit', function(e) {
            const itemName = document.getElementById('item_name').value.trim();
            const bufferStock = document.getElementById('buffer_stock').value;

            if (!itemName) {
                e.preventDefault();
                alert('Item name cannot be empty!');
                return;
            }
            if (bufferStock === '' || parseInt(bufferStock) < 0) {
                e.preventDefault();
                alert('Buffer stock must be a non-negative integer!');
                return;
            }
        });

        // Validate edit item forms on submit
        document.querySelectorAll('.edit-item-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const bufferStock = this.querySelector('input[name="buffer_stock"]').value;

                if (bufferStock === '' || parseInt(bufferStock) < 0) {
                    e.preventDefault();
                    alert('Buffer stock must be a non-negative integer!');
                    return;
                }
            });
        });
    </script>
</body>
</html>