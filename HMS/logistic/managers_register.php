<?php
// managers_register.php

// Database connection
$servername = "localhost";
$username   = "hotelgrandguardi_root";      // change if needed
$password   = "Sun123flower@";          // change if needed
$dbname     = "hotelgrandguardi_wedding_bliss"; // change to your DB

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $manager_username = trim($_POST['username']);
    $manager_password = $_POST['password'];

    if (!empty($manager_username) && !empty($manager_password)) {
        // Hash the password
        $hashedPassword = password_hash($manager_password, PASSWORD_DEFAULT);

        // Insert into database
        $stmt = $conn->prepare("INSERT INTO managers (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $manager_username, $hashedPassword);

        if ($stmt->execute()) {
            $message = "<p class='success'>✅ Manager registered successfully!</p>";
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
    <title>Register Manager</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f9fc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-container {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            width: 350px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
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
            background: #007BFF;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #0056b3;
        }
        .success { color: green; text-align: center; margin-top: 10px; }
        .error { color: red; text-align: center; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Register Manager</h2>
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
