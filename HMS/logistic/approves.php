<?php
// approvers_register.php

// Database connection
$servername = "localhost";
$username   = "hotelgrandguardi_root";        // change if needed
$password   = "Sun123flower@";            // change if needed
$dbname     = "hotelgrandguardi_wedding_bliss"; // change to your DB name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $approver_username = trim($_POST['username']);
    $approver_password = $_POST['password'];

    if (!empty($approver_username) && !empty($approver_password)) {
        // Hash password
        $hashedPassword = password_hash($approver_password, PASSWORD_DEFAULT);

        // Insert into DB
        $stmt = $conn->prepare("INSERT INTO approvers (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $approver_username, $hashedPassword);

        if ($stmt->execute()) {
            $message = "<p class='success'>✅ Approver registered successfully!</p>";
        } else {
            $message = "<p class='error'>❌ Error: " . $stmt->error . "</p>";
        }

        $stmt->close();
    } else {
        $message = "<p class='error'>⚠️ Please fill in all fields.</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Approver</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-container {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            width: 360px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
        }
        input[type=text], input[type=password] {
            width: 100%;
            padding: 10px;
            margin: 8px 0 15px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        button {
            width: 100%;
            background: #28a745;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #218838;
        }
        .success { color: green; text-align: center; margin-top: 10px; }
        .error { color: red; text-align: center; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Register Approver</h2>
        <form method="POST">
            <label>Username:</label>
            <input type="text" name="username" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <button type="submit">Register</button>
        </form>
        <?php echo $message; ?>
    </div>
</body>
</html>
