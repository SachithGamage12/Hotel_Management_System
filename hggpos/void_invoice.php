<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['invoice_number']) || !isset($data['void_reason']) || !isset($data['cashier'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid or missing input data']);
        exit;
    }

    $invoice_number = trim($data['invoice_number']);
    $void_reason = trim($data['void_reason']);
    $cashier = trim($data['cashier']);

    // Validate inputs
    if (empty($invoice_number)) {
        echo json_encode(['success' => false, 'error' => 'Invoice number is required']);
        exit;
    }
    if (empty($void_reason) || strlen($void_reason) > 1000) {
        echo json_encode(['success' => false, 'error' => 'Void reason is required and must be less than 1000 characters']);
        exit;
    }
    if ($cashier !== $_SESSION['username']) {
        echo json_encode(['success' => false, 'error' => 'Cashier does not match logged-in user']);
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    // Check if invoice exists and is not already canceled
    $stmt = $conn->prepare("SELECT id, status FROM invoices WHERE invoice_number = ?");
    $stmt->bind_param('s', $invoice_number);
    $stmt->execute();
    $result = $stmt->get_result();
    $invoice = $result->fetch_assoc();

    if (!$invoice) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => 'Invoice not found']);
        exit;
    }

    if ($invoice['status'] === 'canceled') {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => 'Invoice is already canceled']);
        exit;
    }

    // Check if void_reason column exists
    $result = $conn->query("SHOW COLUMNS FROM invoices LIKE 'void_reason'");
    $has_void_reason = $result->num_rows > 0;

    // Update invoice status and void reason (if column exists)
    if ($has_void_reason) {
        $stmt = $conn->prepare("
            UPDATE invoices 
            SET status = 'canceled', void_reason = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE invoice_number = ?
        ");
        $stmt->bind_param('ss', $void_reason, $invoice_number);
    } else {
        $stmt = $conn->prepare("
            UPDATE invoices 
            SET status = 'canceled', updated_at = CURRENT_TIMESTAMP 
            WHERE invoice_number = ?
        ");
        $stmt->bind_param('s', $invoice_number);
    }
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => 'Failed to update invoice status']);
        exit;
    }

    // Log void action (optional, only if invoice_void_log table exists)
    $result = $conn->query("SHOW TABLES LIKE 'invoice_void_log'");
    $has_void_log = $result->num_rows > 0;

    if ($has_void_log) {
        $stmt = $conn->prepare("
            INSERT INTO invoice_void_log (invoice_id, invoice_number, void_reason, voided_by)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param('isss', $invoice['id'], $invoice_number, $void_reason, $cashier);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            $conn->rollback();
            echo json_encode(['success' => false, 'error' => 'Failed to log void action']);
            exit;
        }
    }

    // Commit transaction
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Invoice voided successfully']);

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>