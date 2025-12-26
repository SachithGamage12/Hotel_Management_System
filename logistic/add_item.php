<?php
// File: add_item.php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    // Sanitize and validate input
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $unit_type = filter_input(INPUT_POST, 'unit_type', FILTER_SANITIZE_STRING);

    // Validate inputs
    if ($name && $price !== false && $unit_type) {
        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO items (name, price, unit_type) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $name, $price, $unit_type);

        if ($stmt->execute()) {
            header('Location: add_item.php');
            exit();
        } else {
            $error = "Error adding item: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error = "Please fill in all fields correctly.";
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Item</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #444;
            font-weight: 500;
        }
        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input[type="number"] {
            appearance: textfield;
        }
        select {
            cursor: pointer;
        }
        input[type="submit"] {
            background-color: #28a745;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
        .error {
            color: #dc3545;
            margin-bottom: 20px;
            text-align: center;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
            font-size: 16px;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Add New Item</h2>
        
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form action="add_item.php" method="POST">
            <div class="form-group">
                <label for="name">Item Name</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="unit_type">Unit Type</label>
                <select id="unit_type" name="unit_type" required>
                    <option value="kg">Kilogram (kg)</option>
                    <option value="g">Gram (g)</option>
                    <option value="packet">Packet</option>
                    <option value="l">Liter</option>
                    <option value="ml">Milliliter</option>
                    <option value="unit">Unit</option>
                    <option value="large">Large</option>
                    <option value="medium">Medium</option>
                    <option value="small">Small</option>
                    <option value="xxl">XXL</option>
                    <option value="xl">XL</option>
                    <option value="books">Books</option>
                    <option value="box">Box</option>
                    <option value="pcs">Pcs</option>
                </select>
            </div>

            <input type="submit" name="add_item" value="Add Item">
        </form>

        <a href="stock.php" class="back-link">Back to Stock Management</a>
    </div>
</body>
</html>