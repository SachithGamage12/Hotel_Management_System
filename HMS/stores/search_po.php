<?php

include 'config.php';

if (isset($_POST['search_po'])) {
    $po_number = trim($_POST['po_number']);
    
    try {
        // Fetch PO header by PO number
        $stmt = $conn->prepare("SELECT id FROM purchase_orders WHERE po_number = :po_number");
        $stmt->bindParam(':po_number', $po_number);
        $stmt->execute();
        $po = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($po) {
            // Store PO ID in session and redirect to print_po.php
            $_SESSION['print_po'] = $po['id'];
            header("Location: print_po.php");
            exit();
        } else {
            $_SESSION['error'] = "No Purchase Order found with PO number: " . htmlspecialchars($po_number);
            header("Location: search_po.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: search_po.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Purchase Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        button {
            padding: 10px 15px;
            background: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .message {
            padding: 10px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .error {
            background: #f2dede;
            color: #a94442;
        }
    </style>
</head>
<body>
    <button onclick="window.location.href='../stores.php'" style="background-color: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
        Back
    </button>

    <div class="container">
        <h1>Search Purchase Order</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="po_number">PO Number:</label>
                <input type="text" id="po_number" name="po_number" placeholder="Enter PO number (e.g., PO-1500)" required>
            </div>
            <button type="submit" name="search_po">Search</button>
        </form>
    </div>
</body>
</html>