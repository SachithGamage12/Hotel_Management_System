<?php
header('Content-Type: application/json');

// Database configuration
$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_lakway_delivery";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Function to generate unique filename
function generateFilename($originalName) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid() . '_' . time() . '.' . $extension;
}

// Function to delete old image file
function deleteImageFile($imagePath) {
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
}

$response = ['success' => false, 'message' => ''];

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Validate input
$item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
$store_id = isset($_POST['store_id']) ? trim($_POST['store_id']) : '';
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

if ($item_id <= 0 || empty($store_id) || $quantity < 0) {
    $response['message'] = 'Invalid input data';
    echo json_encode($response);
    exit;
}

// Fetch current item details
$sql = "SELECT id, image FROM uber_food_items WHERE id = ? AND store_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $item_id, $store_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response['message'] = 'Item not found or access denied';
    echo json_encode($response);
    exit;
}

$current_item = $result->fetch_assoc();
$old_image_path = $current_item['image'];

// Update quantity
$update_sql = "UPDATE uber_food_items SET quantity = ? WHERE id = ? AND store_id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("iis", $quantity, $item_id, $store_id);

if (!$update_stmt->execute()) {
    $response['message'] = 'Failed to update quantity: ' . $conn->error;
    echo json_encode($response);
    exit;
}

// Handle optional image upload
$image_updated = false;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB limit

    $file_tmp = $_FILES['image']['tmp_name'];
    $file_size = $_FILES['image']['size'];
    $file_type = $_FILES['image']['type'];
    $file_name = $_FILES['image']['name'];

    // Validate file
    if (!in_array($file_type, $allowed_types)) {
        $response['message'] = 'Invalid file type. Only JPG, PNG, GIF allowed.';
        echo json_encode($response);
        exit;
    }

    if ($file_size > $max_size) {
        $response['message'] = 'File size too large. Max 5MB allowed.';
        echo json_encode($response);
        exit;
    }

    // Generate unique file name
    $new_filename = generateFilename($file_name);
    $upload_dir = 'uploads/food_images/';
    $new_image_path = $upload_dir . $new_filename;
    $full_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $new_image_path;

    // Ensure directory exists
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Move file
    if (move_uploaded_file($file_tmp, $full_path)) {
        // Delete old image
        if (!empty($old_image_path)) {
            $old_full_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $old_image_path;
            deleteImageFile($old_full_path);
        }

        // Update image path in DB
        $img_sql = "UPDATE uber_food_items SET image = ? WHERE id = ? AND store_id = ?";
        $img_stmt = $conn->prepare($img_sql);
        $img_stmt->bind_param("sis", $new_image_path, $item_id, $store_id);

        if ($img_stmt->execute()) {
            $image_updated = true;
        } else {
            deleteImageFile($full_path); // rollback
            $response['message'] = 'Failed to update image: ' . $conn->error;
            echo json_encode($response);
            exit;
        }
    } else {
        $response['message'] = 'Failed to upload image';
        echo json_encode($response);
        exit;
    }
}

// Final success response
$response['success'] = true;
$response['message'] = 'Item updated successfully';
if ($image_updated) {
    $response['message'] .= ' Image updated.';
}

echo json_encode($response);
$conn->close();
?>
