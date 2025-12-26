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
if (!isset($data['user_id']) || !isset($data['mobile_number'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing user_id or mobile_number']);
    exit();
}

$user_id = $data['user_id'];
$mobile_number = $data['mobile_number'];

// Basic validation for mobile number (e.g., remove non-digits, check length)
$mobile_number = preg_replace('/[^0-9]/', '', $mobile_number);
if (strlen($mobile_number) < 7 || strlen($mobile_number) > 15) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid mobile number']);
    exit();
}

$stmt = $conn->prepare("UPDATE users SET mobile_number = ? WHERE id = ?");
$stmt->bind_param("si", $mobile_number, $user_id);

if ($stmt->execute()) {
    echo json_encode(['message' => 'Mobile number updated successfully']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update mobile number: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>