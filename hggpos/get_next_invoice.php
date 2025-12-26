<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

require_once 'db_connect.php';

try {
    // Fetch the maximum invoice number from database
    $query = "SELECT MAX(CAST(invoice_number AS UNSIGNED)) as max_num FROM invoices";
    $result = $conn->query($query);
    
    if ($result) {
        $row = $result->fetch_assoc();
        $maxNum = $row['max_num'];
        
        // Calculate next invoice number
        if ($maxNum) {
            $nextInvoiceNumber = intval($maxNum) + 1;
        } else {
            // If no invoices exist, start from 1400
            $nextInvoiceNumber = 1400;
        }
        
        echo json_encode([
            'success' => true,
            'nextInvoiceNumber' => $nextInvoiceNumber,
            'currentMax' => $maxNum
        ]);
        
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Database query failed: ' . $conn->error
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Exception: ' . $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>