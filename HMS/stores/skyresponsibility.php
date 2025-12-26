<?php
include 'config.php';

// Add new responsibility
if(isset($_POST['add_responsibility'])) {
    $name = $_POST['name'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    try {
        $stmt = $conn->prepare("INSERT INTO storeresponsible (name, password) VALUES (:name, :password)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        
        $_SESSION['message'] = "Responsibility added successfully!";
        header("Location: skyresponsibility.php");
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: skyresponsibility.php");
        exit();
    }
}

// Fetch all responsibilities
$responsibilities = [];
try {
    $stmt = $conn->query("SELECT * FROM storeresponsible ORDER BY name");
    $responsibilities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $_SESSION['error'] = "Error fetching responsibilities: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsibility Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input { width: 100%; padding: 8px; box-sizing: border-box; }
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
        <h1>Responsibility Management</h1>
        
        <?php if(isset($_SESSION['message'])): ?>
            <div class="message"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <h2>Add New Responsibility</h2>
        <form method="post">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" name="add_responsibility">Add Responsibility</button>
        </form>
        
        <h2>Responsibility List</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($responsibilities as $resp): ?>
                <tr>
                    <td><?= $resp['id'] ?></td>
                    <td><?= htmlspecialchars($resp['name']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>