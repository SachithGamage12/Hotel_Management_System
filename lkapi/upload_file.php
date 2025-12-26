<?php
header('Content-Type: application/json');

if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$upload_dir = 'uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$file = $_FILES['file'];
$filename = uniqid() . '_' . basename($file['name']);
$upload_path = $upload_dir . $filename;

if (move_uploaded_file($file['tmp_name'], $upload_path)) {
    $url = 'https://hotelgrandguardian.org/lkapi/uploads/' . $filename;
    http_response_code(200);
    echo json_encode(['url' => $url]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'File upload failed']);
}
?>