<?php
// File: add_item.php
include 'db.php';

if (isset($_POST['add_item'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $unit_type = $_POST['unit_type'];
    $sql = "INSERT INTO items (name, price, unit_type) VALUES ('$name', $price, '$unit_type')";
    if ($conn->query($sql) === TRUE) {
        header('Location: stock.php');
        exit();
    } else {
        echo "<p class='error'>Error: " . $conn->error . "</p>";
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Item</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .error { color: red; }
    </style>
</head>
<body>
    <h2>Add New Item</h2>
    <form action="add_item.php" method="POST">
        <label for="name">Item Name:</label>
        <input type="text" id="name" name="name" required><br><br>
        
        <label for="price">Price:</label>
        <input type="number" id="price" name="price" step="0.01" required><br><br>
        
        <label for="unit_type">Unit Type:</label>
        <select id="unit_type" name="unit_type" required>
            <option value="kg">kg</option>
            <option value="g">g</option>
            <option value="packet">packet</option>
            <option value="liter">liter</option>
            <option value="milliliter">milliliter</option>
            <option value="unit">unit</option>
            <option value="large">large</option>
            <option value="small">small</option>
            <option value="medium">medium</option>
            <option value="xxl">xxl</option>
            <option value="xl">xl</option>
            <option value="books">books</option>
            <option value="box">box</option>
        </select><br><br>
        
        <input type="submit" name="add_item" value="Add Item">
    </form>
    <br>
    <a href="stock.php">Back to Stock Management</a>
</body>
</html>