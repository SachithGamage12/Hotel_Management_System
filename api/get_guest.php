<?php
header('Content-Type: application/json');
require 'db_connect.php';   // <-- your DB file

$grc = $_GET['grc'] ?? '';
if (!$grc || !is_numeric($grc)) {
    echo json_encode(['success'=>false, 'message'=>'Invalid GRC']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM guests WHERE grc_number = ?");
$stmt->execute([$grc]);
$guest = $stmt->fetch(PDO::FETCH_ASSOC);

if ($guest) {
    echo json_encode(['success'=>true, 'guest'=>$guest]);
} else {
    echo json_encode(['success'=>false, 'message'=>'Guest not found']);
}
?>