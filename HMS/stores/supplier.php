<?php
include 'config.php';

// Add new supplier
if(isset($_POST['add_supplier'])) {
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $remarks = $_POST['remarks'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO suppliers (name, contact_number, address, email, remarks) 
                              VALUES (:name, :contact, :address, :email, :remarks)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':contact', $contact);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':remarks', $remarks);
        $stmt->execute();
        
        $_SESSION['message'] = "Supplier added successfully!";
        header("Location: supplier.php");
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: supplier.php");
        exit();
    }
}

// Fetch all suppliers
$suppliers = [];
try {
    $stmt = $conn->query("SELECT * FROM suppliers ORDER BY name");
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $_SESSION['error'] = "Error fetching suppliers: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        button { padding: 10px 15px; background: #4CAF50; color: white; border: none; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .message { padding: 10px; margin-bottom: 15px; background: #dff0d8; color: #3c763d; }
        .error { padding: 10px; margin-bottom: 15px; background: #f2dede; color: #a94442; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Supplier Management</h1>
        
        <?php if(isset($_SESSION['message'])): ?>
            <div class="message"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <h2>Add New Supplier</h2>
        <form method="post">
            <div class="form-group">
                <label for="name">Supplier Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="contact">Contact Number:</label>
                <input type="text" id="contact" name="contact" required>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email">
            </div>
            <div class="form-group">
                <label for="remarks">Remarks:</label>
                <textarea id="remarks" name="remarks" rows="2"></textarea>
            </div>
            <button type="submit" name="add_supplier">Add Supplier</button>
        </form>
        
        <h2>Supplier List</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Address</th>
                    <th>Email</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($suppliers as $supplier): ?>
                <tr>
                    <td><?= $supplier['id'] ?></td>
                    <td><?= htmlspecialchars($supplier['name']) ?></td>
                    <td><?= htmlspecialchars($supplier['contact_number']) ?></td>
                    <td><?= htmlspecialchars($supplier['address']) ?></td>
                    <td><?= htmlspecialchars($supplier['email']) ?></td>
                    <td><?= htmlspecialchars($supplier['remarks']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>