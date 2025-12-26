<?php
include '../hggpos/db_connect.php'; // Database connection

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        // Hash password for security
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO hgg_users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashedPassword);

        if ($stmt->execute()) {
            $message = "✅ User added successfully!";
        } else {
            $message = "❌ Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "⚠ Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            animation: bgMove 10s infinite alternate ease-in-out;
        }

        @keyframes bgMove {
            0% { background: linear-gradient(135deg, #6a11cb, #2575fc); }
            100% { background: linear-gradient(135deg, #2575fc, #6a11cb); }
        }

        .form-container {
            background: #fff;
            padding: 30px;
            width: 100%;
            max-width: 400px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
            animation: slideUp 1s ease-out;
        }

        @keyframes slideUp {
            0% { transform: translateY(50px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .input-group {
            margin-bottom: 20px;
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            transition: 0.3s;
        }

        .input-group input:focus {
            border-color: #2575fc;
            outline: none;
            box-shadow: 0 0 8px rgba(37,117,252,0.3);
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(90deg, #2575fc, #6a11cb);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: 0.4s ease;
        }

        button:hover {
            background: linear-gradient(90deg, #6a11cb, #2575fc);
            transform: scale(1.05);
        }

        .message {
            margin-top: 15px;
            font-size: 14px;
            color: #444;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Add New User</h2>
        <form method="POST" action="">
            <div class="input-group">
                <input type="text" name="username" placeholder="Enter Username" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Enter Password" required>
            </div>
            <button type="submit">Add User</button>
        </form>
        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
