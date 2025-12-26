
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
if (!isset($data['store_name']) || !isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

if (empty(trim($data['store_name'])) || empty(trim($data['email'])) || empty(trim($data['password']))) {
    http_response_code(400);
    echo json_encode(['error' => 'Fields cannot be empty']);
    exit;
}

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}

if (strlen($data['password']) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'Password must be at least 6 characters']);
    exit;
}

if (strlen($data['store_name']) < 3) {
    http_response_code(400);
    echo json_encode(['error' => 'Store name must be at least 3 characters']);
    exit;
}

// Sanitize inputs
$store_name = $conn->real_escape_string(trim($data['store_name']));
$email = $conn->real_escape_string(trim($data['email']));
$password = password_hash(trim($data['password']), PASSWORD_BCRYPT);

// Check for duplicate email
$stmt = $conn->prepare("SELECT id FROM stores WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Email already exists']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Insert new store
$stmt = $conn->prepare("INSERT INTO stores (store_name, email, password, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("sss", $store_name, $email, $password);
if ($stmt->execute()) {
    $store_id = $conn->insert_id;
    http_response_code(201);
    echo json_encode([
        'message' => 'Store created successfully',
        'store' => [
            'id' => $store_id,
            'store_name' => $store_name
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create store']);
}

$stmt->close();
$conn->close();
?>