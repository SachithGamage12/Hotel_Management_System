<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection
$host = 'localhost';
$dbname = 'hotelgrandguardi_lakway_delivery';
$username = 'hotelgrandguardi_root';
$password = 'Sun123flower@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Validate required fields
$required_fields = ['user_id', 'type', 'name', 'contact', 'address', 'open_time', 'close_time', 'latitude', 'longitude'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        http_response_code(400);
        echo json_encode(['error' => ucfirst($field) . ' is required']);
        exit;
    }
}

// Validate latitude and longitude within Ratnapura-Kuruwita
$latitude = trim($_POST['latitude']);
$longitude = trim($_POST['longitude']);
$minLat = 6.6;
$maxLat = 6.85;
$minLng = 80.3;
$maxLng = 80.5;
if (!is_numeric($latitude) || !is_numeric($longitude) || 
    $latitude < $minLat || $latitude > $maxLat || 
    $longitude < $minLng || $longitude > $maxLng) {
    http_response_code(400);
    echo json_encode(['error' => 'Location must be within Ratnapura-Kuruwita area']);
    exit;
}

// Validate required files
if (!isset($_FILES['profile_pic']) || $_FILES['profile_pic']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Please upload a profile picture']);
    exit;
}

// For individual type, NIC images are required
if ($_POST['type'] === 'individual') {
    if (!isset($_FILES['nic_front']) || $_FILES['nic_front']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'NIC front image is required for individual type']);
        exit;
    }
    if (!isset($_FILES['nic_back']) || $_FILES['nic_back']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'NIC back image is required for individual type']);
        exit;
    }
}

// Check if store already has a registration
$check_stmt = $pdo->prepare("SELECT id FROM uber_store_registrations WHERE user_id = ?");
$check_stmt->execute([$_POST['user_id']]);
if ($check_stmt->fetch()) {
    http_response_code(400);
    echo json_encode(['error' => 'Store already has a registration. Please contact support to update.']);
    exit;
}

// Upload directory
$upload_dir = '../uploads/uber_stores/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Function to upload file
function uploadFile($file, $prefix, $user_id) {
    global $upload_dir;
    
    $max_size = 5 * 1024 * 1024; // 5MB
    
    // Validate that it's an image using getimagesize (accepts any image format)
    $image_info = getimagesize($file['tmp_name']);
    if ($image_info === false) {
        throw new Exception('Invalid image file. Please upload a valid image.');
    }
    
    if ($file['size'] > $max_size) {
        throw new Exception('File size exceeds 5MB limit.');
    }
    
    // Get proper extension from image type
    $extension = image_type_to_extension($image_info[2], false);
    if (!$extension) {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $extension = strtolower($extension);
    } else {
        $extension = strtolower($extension);
    }
    
    $filename = $prefix . '_' . $user_id . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to upload file.');
    }
    
    // Set proper permissions
    chmod($filepath, 0644);
    
    return $filename;
}

try {
    // Upload files
    $profile_pic = uploadFile($_FILES['profile_pic'], 'profile', $_POST['user_id']);
    
    $food_licence = null;
    if (isset($_FILES['food_licence']) && $_FILES['food_licence']['error'] === UPLOAD_ERR_OK) {
        $food_licence = uploadFile($_FILES['food_licence'], 'license', $_POST['user_id']);
    }
    
    $nic_front = null;
    $nic_back = null;
    if ($_POST['type'] === 'individual') {
        $nic_front = uploadFile($_FILES['nic_front'], 'nic_front', $_POST['user_id']);
        $nic_back = uploadFile($_FILES['nic_back'], 'nic_back', $_POST['user_id']);
    }
    
    // Insert into database
    $sql = "INSERT INTO uber_store_registrations 
            (user_id, type, name, contact, address, latitude, longitude, business_reg, open_time, close_time, 
             profile_pic, food_licence, nic_front, nic_back, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['user_id'],
        $_POST['type'],
        $_POST['name'],
        $_POST['contact'],
        $_POST['address'],
        $latitude,
        $longitude,
        $_POST['business_reg'] ?? null,
        $_POST['open_time'],
        $_POST['close_time'],
        $profile_pic,
        $food_licence,
        $nic_front,
        $nic_back
    ]);
    
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Store registration submitted successfully for ලක්Way Delivery',
        'registration_id' => $pdo->lastInsertId()
    ]);
    
} catch (Exception $e) {
    // Log error for debugging
    error_log('Registration error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>