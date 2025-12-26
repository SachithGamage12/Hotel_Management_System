<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsible Manager Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], input[type="password"], input[type="file"] {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .error {
            color: red;
            font-size: 0.9em;
        }
        .success {
            color: green;
            font-size: 0.9em;
        }
        button {
            padding: 10px 20px;
            background-color: #dc3545;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <h2>Responsible Manager Registration</h2>
    <?php
    // Initialize variables for error/success messages
    $error = '';
    $success = '';

    // Database connection settings
    $host = 'localhost';
    $dbUsername = 'hotelgrandguardi_root'; // Replace with your DB username
    $dbPassword = 'Sun123flower@'; // Replace with your DB password
    $dbName = 'hotelgrandguardi_wedding_bliss'; // Replace with your DB name

    // Create connection
    $conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get form data
        $name = trim($_POST['name']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);
        $signature_path = null;

        // Validate inputs
        if (empty($name) || empty($password) || empty($confirm_password)) {
            $error = "Name and password fields are required.";
        } elseif (strlen($name) < 3 || strlen($name) > 100) {
            $error = "Name must be between 3 and 100 characters.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // Handle signature file upload
            if (!empty($_FILES['signature']['name'])) {
                $target_dir = "uploads/signatures/";
                // Create uploads directory if it doesn't exist
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $signature_file = $_FILES['signature'];
                $file_ext = strtolower(pathinfo($signature_file['name'], PATHINFO_EXTENSION));
                $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

                if (!in_array($file_ext, $allowed_exts)) {
                    $error = "Signature must be a JPG, PNG, or GIF file.";
                } elseif ($signature_file['size'] > 5 * 1024 * 1024) { // 5MB limit
                    $error = "Signature file size must not exceed 5MB.";
                } else {
                    $signature_path = $target_dir . uniqid() . '.' . $file_ext;
                    if (!move_uploaded_file($signature_file['tmp_name'], $signature_path)) {
                        $error = "Failed to upload signature file.";
                    }
                }
            }

            if (!$error) {
                // Check if name already exists
                $stmt = $conn->prepare("SELECT id FROM responsibilities WHERE name = ?");
                $stmt->bind_param("s", $name);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $error = "Name already exists.";
                } else {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Insert into database
                    $stmt = $conn->prepare("INSERT INTO responsibilities (name, password, signature_path) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $name, $hashed_password, $signature_path);

                    if ($stmt->execute()) {
                        $success = "Responsible manager registered successfully!";
                    } else {
                        $error = "Error occurred during registration. Please try again.";
                    }
                }
                $stmt->close();
            }
        }
    }

    $conn->close();
    ?>

    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p class="success"><?php echo $success; ?></p>
    <?php endif; ?>

    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password">
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password">
        </div>
        <div class="form-group">
            <label for="signature">Signature (Optional, JPG/PNG/GIF, max 5MB)</label>
            <input type="file" id="signature" name="signature" accept="image/jpeg,image/png,image/gif">
        </div>
        <button type="submit">Register Responsible Manager</button>
    </form>
</body>
</html>