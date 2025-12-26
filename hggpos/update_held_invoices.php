
<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['heldInvoices'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid input data']);
    exit;
}

$_SESSION['held_invoices'] = $input['heldInvoices'];
echo json_encode(['success' => true]);
