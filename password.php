<?php
// Database connection
$host = "localhost";
$user = "hotelgrandguardi_root";
$pass = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// If form submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'] ?? '';
    $password = $_POST['password'] ?? '';

    // Get hash from database for this user
    $stmt = $conn->prepare("SELECT password FROM responsible WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt->bind_result($hash);
    
    if ($stmt->fetch()) {
        if (password_verify($password, $hash)) {
            echo "<p style='color:green;'>✅ Password is correct!</p>";
        } else {
            echo "<p style='color:red;'>❌ Invalid password.</p>";
        }
    } else {
        echo "<p style='color:red;'>❌ User not found.</p>";
    }
    $stmt->close();
}

$conn->close();
?>

<!-- Simple HTML form -->
<form method="POST">
    <label>Username:</label><br>
    <input type="text" name="name" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Check Password</button>
</form>
