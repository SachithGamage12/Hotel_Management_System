<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['grc_number']) || empty($input['grc_number'])) {
        echo json_encode(['success' => false, 'error' => 'GRC number is required']);
        exit;
    }

    // Generate unique invoice number
    $stmt = $conn->query("SELECT MAX(id) AS max_id FROM room_invoices");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $invoice_number = 'INV' . str_pad(($result['max_id'] ?? 0) + 1, 6, '0', STR_PAD_LEFT);

    $stmt = $conn->prepare("
        INSERT INTO room_invoices (
            invoice_number, grc_number, guest_name, nic, contact_no, billing_date,
            rooms, ac_type, meal_plan, remarks, value_type, amount_type,
            total_amount, advance_payment, pending_amount, issued_by
        ) VALUES (
            :invoice_number, :grc_number, :guest_name, :nic, :contact_no, :billing_date,
            :rooms, :ac_type, :meal_plan, :remarks, :value_type, :amount_type,
            :total_amount, :advance_payment, :pending_amount, :issued_by
        )
    ");

    $billing_date = date('Y-m-d');
    $stmt->execute([
        ':invoice_number' => $invoice_number,
        ':grc_number' => $input['grc_number'],
        ':guest_name' => $input['guest_name'],
        ':nic' => $input['nic'] ?? null,
        ':contact_no' => $input['contact_no'] ?? null,
        ':billing_date' => $billing_date,
        ':rooms' => json_encode($input['rooms']),
        ':ac_type' => $input['ac_type'] ?? null,
        ':meal_plan' => $input['meal_plan'] ?? null,
        ':remarks' => $input['remarks'] ?? null,
        ':value_type' => $input['value_type'],
        ':amount_type' => $input['amount_type'],
        ':total_amount' => $input['amount_type'] === 'FOC' ? null : ($input['total_amount'] ?? null),
        ':advance_payment' => $input['amount_type'] === 'FOC' ? null : ($input['advance_payment'] ?? null),
        ':pending_amount' => $input['amount_type'] === 'FOC' ? null : ($input['pending_amount'] ?? null),
        ':issued_by stacks/GrC/GrC.php' => $input['issued_by'] ?? 'Admin'
    ]);

    echo json_encode([
        'success' => true,
        'invoice_number' => $invoice_number
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>