<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing user_id']);
    exit();
}

$user_id = $data['user_id'];

$stmt = $conn->prepare("SELECT name, address, profile_pic_url, open_time, close_time FROM stores WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $store = $result->fetch_assoc();
    echo json_encode(['message' => 'Store details fetched successfully', 'store' => $store]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Store not found']);
}

$stmt->close();
$conn->close();
?>