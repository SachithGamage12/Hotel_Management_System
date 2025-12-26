
<?php
// lkapi/login.php
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
$identifier = $data['identifier'];
$pass = $data['password'];

$stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $identifier, $identifier);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($pass, $user['password'])) {
        unset($user['password']);
        echo json_encode(['message' => 'Login successful', 'user' => $user]);
    } else {
        echo json_encode(['error' => 'Invalid password']);
    }
} else {
    echo json_encode(['error' => 'User not found']);
}

$stmt->close();
$conn->close();
?>