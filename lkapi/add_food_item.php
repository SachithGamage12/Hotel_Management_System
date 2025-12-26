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
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Validate required fields
$required_fields = ['store_id', 'name', 'price', 'quantity'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        http_response_code(400);
        echo json_encode(['error' => ucfirst($field) . ' is required']);
        exit;
    }
}

// Validate image file
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Please upload an item image']);
    exit;
}

// Upload directory
$upload_dir = '../uploads/uber_foods/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Function to upload file (accepts any image format)
function uploadFile($file, $prefix, $store_id) {
    global $upload_dir;
    
    $max_size = 5 * 1024 * 1024; // 5MB
    
    $image_info = getimagesize($file['tmp_name']);
    if ($image_info === false) {
        throw new Exception('Invalid image file. Please upload a valid image.');
    }
    
    if ($file['size'] > $max_size) {
        throw new Exception('File size exceeds 5MB limit.');
    }
    
    $extension = image_type_to_extension($image_info[2], false);
    if (!$extension) {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $extension = strtolower($extension);
    } else {
        $extension = strtolower($extension);
    }
    
    $filename = $prefix . '_' . $store_id . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to upload file.');
    }
    
    chmod($filepath, 0644);
    
    return $filename;
}

try {
    // Upload image
    $image = uploadFile($_FILES['image'], 'food', $_POST['store_id']);
    
    // Insert into database
    $sql = "INSERT INTO uber_food_items 
            (store_id, name, description, price, quantity, image, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['store_id'],
        $_POST['name'],
        $_POST['description'] ?? null,
        $_POST['price'],
        $_POST['quantity'],
        $image
    ]);
    
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Food item added successfully',
        'item_id' => $pdo->lastInsertId()
    ]);
    
} catch (Exception $e) {
    error_log('Add food item error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>