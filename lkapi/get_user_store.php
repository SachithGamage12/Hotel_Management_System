<?php
// lkapi/get_user_store.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing user_id']);
    exit();
}

$user_id = $data['user_id'];

// Get store registration data
$stmt = $conn->prepare("
    SELECT sr.*, s.name as store_name 
    FROM uber_store_registrations sr 
    LEFT JOIN stores s ON sr.user_id = s.id 
    WHERE sr.user_id = ? AND sr.status = 'approved'
    ORDER BY sr.created_at DESC 
    LIMIT 1
");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $store = $result->fetch_assoc();
    // Add profile pic URL
    if (!empty($store['profile_pic'])) {
        $store['profile_pic_url'] = "https://hotelgrandguardian.org/uploads/" . $store['profile_pic'];
    }
    echo json_encode(['success' => true, 'store' => $store]);
} else {
    echo json_encode(['success' => false, 'error' => 'No approved store found']);
}

$stmt->close();
$conn->close();
?>