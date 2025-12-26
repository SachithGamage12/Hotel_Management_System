<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Database configuration
$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@"; // Replace with actual password
$dbname = "hotelgrandguardi_wedding_bliss";

$response = ['success' => false, 'message' => ''];

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON input');
    }

    // Log received data for debugging
    error_log("Update Guest Data: " . print_r($data, true));

    // Validate required fields
    if (!isset($data['guest_id']) || empty($data['guest_id'])) {
        throw new Exception('Guest ID is required');
    }

    // Database connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare SQL query - removed updated_at column
    $sql = "UPDATE guests SET 
            guest_name = :guest_name,
            contact_number = :contact_number,
            email = :email,
            address = :address,
            id_type = :id_type,
            id_number = :id_number,
            check_in_date = :check_in_date,
            check_in_time = :check_in_time,
            check_in_time_am_pm = :check_in_time_am_pm,
            check_out_date = :check_out_date,
            check_out_time = :check_out_time,
            check_out_time_am_pm = :check_out_time_am_pm,
            rooms = :rooms,
            meal_plan_id = :meal_plan_id,
            number_of_pax = :number_of_pax,
            remarks = :remarks,
            other_guest_name_1 = :other_guest_name_1,
            other_guest_nic_1 = :other_guest_nic_1,
            other_guest_name_2 = :other_guest_name_2,
            other_guest_nic_2 = :other_guest_nic_2,
            other_guest_name_3 = :other_guest_name_3,
            other_guest_nic_3 = :other_guest_nic_3
            WHERE id = :guest_id";

    $stmt = $conn->prepare($sql);

    // Handle rooms JSON - ensure proper formatting
    $rooms_data = isset($data['rooms']) ? $data['rooms'] : [];
    $rooms_json = json_encode($rooms_data, JSON_UNESCAPED_UNICODE);

    // Debug rooms data
    error_log("Rooms data: " . print_r($rooms_data, true));
    error_log("Rooms JSON: " . $rooms_json);

    // Bind parameters with proper null handling
    $stmt->bindValue(':guest_id', $data['guest_id'], PDO::PARAM_INT);
    $stmt->bindValue(':guest_name', $data['guest_name'] ?? '', PDO::PARAM_STR);
    $stmt->bindValue(':contact_number', $data['contact_number'] ?? '', PDO::PARAM_STR);
    $stmt->bindValue(':email', $data['email'] ?? '', PDO::PARAM_STR);
    $stmt->bindValue(':address', $data['address'] ?? '', PDO::PARAM_STR);
    $stmt->bindValue(':id_type', $data['id_type'] ?? 'NIC', PDO::PARAM_STR);
    $stmt->bindValue(':id_number', $data['id_number'] ?? '', PDO::PARAM_STR);
    $stmt->bindValue(':check_in_date', $data['check_in_date'] ?? '', PDO::PARAM_STR);
    $stmt->bindValue(':check_in_time', $data['check_in_time'] ?? '', PDO::PARAM_STR);
    $stmt->bindValue(':check_in_time_am_pm', $data['check_in_time_am_pm'] ?? 'AM', PDO::PARAM_STR);
    $stmt->bindValue(':check_out_date', $data['check_out_date'] ?? '', PDO::PARAM_STR);
    $stmt->bindValue(':check_out_time', $data['check_out_time'] ?? '', PDO::PARAM_STR);
    $stmt->bindValue(':check_out_time_am_pm', $data['check_out_time_am_pm'] ?? 'AM', PDO::PARAM_STR);
    $stmt->bindValue(':rooms', $rooms_json, PDO::PARAM_STR);
    $stmt->bindValue(':remarks', $data['remarks'] ?? '', PDO::PARAM_STR);
    $stmt->bindValue(':other_guest_name_1', $data['other_guest_name_1'] ?? '', PDO::PARAM_STR);
    $stmt->bindValue(':other_guest_nic_1', $data['other_guest_nic_1'] ?? '', PDO::PARAM_STR);
    $stmt->bindValue(':other_guest_name_2', $data['other_guest_name_2'] ?? '', PDO::PARAM_STR);
    $stmt->bindValue(':other_guest_nic_2', $data['other_guest_nic_2'] ?? '', PDO::PARAM_STR);
    $stmt->bindValue(':other_guest_name_3', $data['other_guest_name_3'] ?? '', PDO::PARAM_STR);
    $stmt->bindValue(':other_guest_nic_3', $data['other_guest_nic_3'] ?? '', PDO::PARAM_STR);

    // Handle nullable integer fields
    $meal_plan_id = !empty($data['meal_plan_id']) ? $data['meal_plan_id'] : null;
    $number_of_pax = !empty($data['number_of_pax']) ? $data['number_of_pax'] : null;
    
    $stmt->bindValue(':meal_plan_id', $meal_plan_id, $meal_plan_id ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':number_of_pax', $number_of_pax, $number_of_pax ? PDO::PARAM_INT : PDO::PARAM_NULL);

    // Execute update
    if ($stmt->execute()) {
        $rowCount = $stmt->rowCount();
        if ($rowCount > 0) {
            $response['success'] = true;
            $response['message'] = 'Guest updated successfully';
            $response['affected_rows'] = $rowCount;
            
            // Log success
            error_log("Guest updated successfully. ID: " . $data['guest_id'] . ", Affected rows: " . $rowCount);
        } else {
            $response['message'] = 'No changes made or guest not found';
            error_log("No changes made for guest ID: " . $data['guest_id']);
        }
    } else {
        $errorInfo = $stmt->errorInfo();
        throw new Exception('Failed to execute update query: ' . $errorInfo[2]);
    }

} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    error_log("Database Error: " . $e->getMessage());
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log("General Error: " . $e->getMessage());
}

echo json_encode($response);
?>