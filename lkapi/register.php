<?php
// lkapi/register.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$servername = "localhost"; // Replace with your DB server
$username = "hotelgrandguardi_root"; // Replace
$password = "Sun123flower@"; // Replace
$dbname = "hotelgrandguardi_lakway_delivery"; // Replace

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

$data = json_decode(file_get_contents('php://input'), true);
$username_val = $data['username'];
$email = $data['email'];
$pass = password_hash($data['password'], PASSWORD_DEFAULT);

// Check if username or email exists
$check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$check_stmt->bind_param("ss", $username_val, $email);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode(['error' => 'Username or email already exists']);
    $check_stmt->close();
    $conn->close();
    exit();
}

$check_stmt->close();

$stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username_val, $email, $pass);

if ($stmt->execute()) {
    echo json_encode(['message' => 'Account created successfully']);
    http_response_code(201);
} else {
    echo json_encode(['error' => 'Registration failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>