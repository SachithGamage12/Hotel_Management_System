<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $guest_data = json_decode($_POST['guest_data'], true);
    
    $stmt = $conn->prepare("INSERT INTO guests (
        grc_number, guest_name, contact_number, email, address, id_type, id_number,
        other_guest_name_1, other_guest_nic_1, other_guest_name_2, other_guest_nic_2,
        other_guest_name_3, other_guest_nic_3, check_in_date, check_in_time, check_in_time_am_pm,
        check_out_date, check_out_time, check_out_time_am_pm, rooms, meal_plan_id, number_of_pax, remarks
    ) VALUES (
        :grc_number, :guest_name, :contact_number, :email, :address, :id_type, :id_number,
        :other_guest_name_1, :other_guest_nic_1, :other_guest_name_2, :other_guest_nic_2,
        :other_guest_name_3, :other_guest_nic_3, :check_in_date, :check_in_time, :check_in_time_am_pm,
        :check_out_date, :check_out_time, :check_out_time_am_pm, :rooms, :meal_plan_id, :number_of_pax, :remarks
    )");
    
    $stmt->execute([
        ':grc_number' => $guest_data['grc_number'],
        ':guest_name' => $guest_data['guest_name'],
        ':contact_number' => $guest_data['contact_number'],
        ':email' => $guest_data['email'] ?? null,
        ':address' => $guest_data['address'] ?? null,
        ':id_type' => $guest_data['id_type'],
        ':id_number' => $guest_data['id_number'],
        ':other_guest_name_1' => $guest_data['other_guest_name_1'] ?? null,
        ':other_guest_nic_1' => $guest_data['other_guest_nic_1'] ?? null,
        ':other_guest_name_2' => $guest_data['other_guest_name_2'] ?? null,
        ':other_guest_nic_2' => $guest_data['other_guest_nic_2'] ?? null,
        ':other_guest_name_3' => $guest_data['other_guest_name_3'] ?? null,
        ':other_guest_nic_3' => $guest_data['other_guest_nic_3'] ?? null,
        ':check_in_date' => $guest_data['check_in_date'],
        ':check_in_time' => $guest_data['check_in_time'],
        ':check_in_time_am_pm' => $guest_data['check_in_time_am_pm'],
        ':check_out_date' => $guest_data['check_out_date'],
        ':check_out_time' => $guest_data['check_out_time'],
        ':check_out_time_am_pm' => $guest_data['check_out_time_am_pm'],
        ':rooms' => json_encode($guest_data['rooms']),
        ':meal_plan_id' => $guest_data['meal_plan_id'] ?? null,
        ':number_of_pax' => $guest_data['number_of_pax'] ?? null,
        ':remarks' => $guest_data['remarks'] ?? null
    ]);
    
    $guest_id = $conn->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'guest_reference' => $guest_id,
        'grc_number' => $guest_data['grc_number']
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>