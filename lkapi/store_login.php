<?php
// Set headers for JSON response and CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Connect to the database (update with your credentials)
$conn = new mysqli('localhost', 'hotelgrandguardi_root', 'Sun123flower@', 'hotelgrandguardi_lakway_delivery');

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Read and decode JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Validate input fields
if (!isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

if (empty(trim($data['email'])) || empty(trim($data['password']))) {
    http_response_code(400);
    echo json_encode(['error' => 'Fields cannot be empty']);
    exit;
}

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}

// Sanitize inputs
$email = $conn->real_escape_string(trim($data['email']));

// Retrieve store data
$stmt = $conn->prepare("SELECT id, store_name, password FROM stores WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email or password']);
    $stmt->close();
    $conn->close();
    exit;
}

$row = $result->fetch_assoc();
if (password_verify(trim($data['password']), $row['password'])) {
    http_response_code(200);
    echo json_encode([
        'message' => 'Login successful',
        'store' => [
            'id' => $row['id'],
            'store_name' => $row['store_name']
        ]
    ]);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email or password']);
}

$stmt->close();
$conn->close();
?>