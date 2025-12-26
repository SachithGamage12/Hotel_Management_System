<?php
// lkapi/get_all_stores.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

// Get stores that have approved registrations
$stmt = $conn->prepare("
    SELECT s.id, s.store_name as name, s.email, usr.address, usr.contact, usr.open_time, usr.close_time, usr.profile_pic 
    FROM stores s
    INNER JOIN uber_store_registrations usr ON s.id = usr.user_id
    WHERE usr.status = 'approved'
    ORDER BY s.store_name ASC
");
$stmt->execute();
$result = $stmt->get_result();

$stores = [];
while ($row = $result->fetch_assoc()) {
    $row['id'] = (string)$row['id']; // Ensure ID is string for consistency
    if (!empty($row['profile_pic'])) {
        $row['profile_pic_url'] = "https://hotelgrandguardian.org/uploads/uber_stores/" . $row['profile_pic'];
    }
    $stores[] = $row;
}

echo json_encode([
    'success' => true,
    'stores' => $stores,
    'count' => count($stores)
]);

$stmt->close();
$conn->close();
?>