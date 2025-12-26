<?php
include 'config.php';

// Check if either print_po or po_data is set to allow page refresh
if (!isset($_SESSION['print_po']) && !isset($_SESSION['po_data'])) {
    $_SESSION['error'] = "No purchase order data available.";
    header("Location: purchase_order.php");
    exit();
}

try {
    // If print_po is set, fetch new data
    if (isset($_SESSION['print_po'])) {
        $po_id = $_SESSION['print_po'];

        // Fetch PO header
        $stmt = $conn->prepare("SELECT po.*, s.name as supplier_name, s.contact_number as supplier_contact, 
                              s.address as supplier_address, r.name as confirmed_by_name 
                              FROM purchase_orders po
                              JOIN suppliers s ON po.supplier_id = s.id
                              JOIN responsibilities r ON po.confirmed_by = r.id
                              WHERE po.id = :id");
        $stmt->bindParam(':id', $po_id);
        $stmt->execute();
        $po = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$po) {
            throw new Exception("Purchase Order not found");
        }
        
        // Fetch PO items with item names from inventory
        $stmt = $conn->prepare("SELECT pi.*, i.item_name 
                               FROM po_items pi
                               LEFT JOIN inventory i ON pi.item_id = i.id
                               WHERE pi.po_id = :po_id");
        $stmt->bindParam(':po_id', $po_id);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Check if items are fetched
        if (empty($items)) {
            throw new Exception("No items found for this purchase order.");
        }
        
        // Track invalid items for warning
        $invalid_items = [];
        foreach ($items as $index => $item) {
            if (!isset($item['item_name']) || $item['item_name'] === null) {
                error_log("Missing item_name for item_id: " . $item['item_id'] . " in po_id: $po_id");
                $items[$index]['item_name'] = "Invalid Item (ID: {$item['item_id']})";
                $invalid_items[] = $item['item_id'];
            }
        }
        
        // Set warning if there are invalid items
        if (!empty($invalid_items)) {
            $_SESSION['warning'] = "Some items (IDs: " . implode(', ', $invalid_items) . ") could not be found in inventory. Please verify and update.";
        }
        
        // Store data in session for refresh
        $_SESSION['po_data'] = [
            'po' => $po,
            'items' => $items
        ];
        
        // Clear print_po to prevent reuse
        unset($_SESSION['print_po']);
    } else {
        // Use cached data from session if refreshed
        $po = $_SESSION['po_data']['po'];
        $items = $_SESSION['po_data']['items'];
        
        // Validate session data
        if (empty($items)) {
            throw new Exception("No items found in session data.");
        }
        $invalid_items = [];
        foreach ($items as $index => $item) {
            if (!isset($item['item_name']) || $item['item_name'] === null) {
                error_log("Missing item_name in session data for item_id: " . $item['item_id']);
                $items[$index]['item_name'] = "Invalid Item (ID: {$item['item_id']})";
                $invalid_items[] = $item['item_id'];
            }
        }
        
        // Update session data with any fixes
        if (!empty($invalid_items)) {
            $_SESSION['warning'] = "Some items (IDs: " . implode(', ', $invalid_items) . ") could not be found in inventory. Please verify and update.";
            $_SESSION['po_data']['items'] = $items;
        }
    }
    
} catch (Exception $e) {
    error_log("Error in print_po.php: " . $e->getMessage());
    $_SESSION['error'] = "Error: " . $e->getMessage();
    unset($_SESSION['po_data']);
    header("Location: purchase_order.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=80mm, initial-scale=1.0">
    <title>Print Purchase Order</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        body {
            font-size: 10pt;
            line-height: 1.5;
            width: 100%;
            margin: 0;
            padding: 0;
            color: #000;
            background: #fff;
            min-height: 150mm;
            display: flex;
            justify-content: center;
        }
        .wrapper {
            width: 80mm;
            margin: 0 auto;
        }
        .po-container {
            width: 80mm;
            margin: 0;
            padding: 3mm;
            text-align: left;
        }
        .header {
            text-align: center;
            margin: 0 0 5mm 0;
            padding: 0 0 3mm 0;
            border-bottom: 2px solid #000;
        }
        .header h2 {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 2mm;
            text-transform: uppercase;
        }
        .header div {
            font-size: 9pt;
            font-weight: bold;
        }
        .supplier-info {
            margin: 0 0 5mm 0;
            padding: 0 0 3mm 0;
            font-size: 9pt;
            text-align: left;
        }
        .supplier-info div {
            margin-bottom: 2mm;
            word-break: break-word;
            font-weight: bold;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 5mm 0;
            font-size: 9pt;
        }
        .table th {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 2mm 1mm;
            text-align: left;
            font-weight: bold;
        }
        .table td {
            padding: 2mm 1mm;
            border-bottom: 1px solid #000;
            vertical-align: top;
            font-weight: bold;
        }
        .table td:first-child {
            width: 50%;
            word-wrap: break-word;
            white-space: normal;
        }
        .table td:nth-child(2) {
            width: 20%;
            text-align: center;
            white-space: nowrap;
        }
        .table td:nth-child(3) {
            width: 30%;
            text-align: center;
            white-space: nowrap;
        }
        .table tr:last-child td {
            border-bottom: 2px solid #000;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin: 0 0 5mm 0;
            font-size: 9pt;
            text-align: left;
        }
        .signature-box {
            width: 48%;
            min-width: 35mm;
            text-align: center;
            padding: 3mm;
            overflow: visible;
        }
        .signature-box div {
            font-weight: bold;
            font-size: 10pt;
            white-space: nowrap;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 8mm;
            padding-top: 1mm;
        }
        .controls {
            text-align: center;
            margin: 0 0 5mm 0;
            padding-top: 3mm;
            border-top: 2px dashed #000;
            width: 80mm;
        }
        .controls button {
            padding: 2mm 4mm;
            margin: 0 2mm;
            font-size: 9pt;
            cursor: pointer;
            background: #f0f0f0;
            border: 1px solid #000;
            font-weight: bold;
        }
        .warning {
            background: #f2dede;
            color: #a94442;
            padding: 2mm;
            margin: 0 0 4mm 0;
            font-size: 9pt;
            border: 1px solid #a94442;
            font-weight: bold;
            text-align: left;
        }
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        @page {
            size: 80mm auto;
            margin: 0;
        }
        @media print {
            .back-button, .controls, .controls *, .warning {
                display: none !important;
            }
            body {
                padding: 0;
                margin: 0;
                width: 80mm;
                font-size: 10pt;
                color: #000 !important;
                min-height: 0;
                display: block;
            }
            .wrapper {
                width: 80mm;
                margin: 0 auto;
            }
            .po-container {
                width: 80mm;
                margin: 0;
                padding: 0;
            }
            .header {
                margin: 0 0 5mm 0;
                padding: 0 0 3mm 0;
            }
            .supplier-info, .table {
                margin: 0 0 5mm 0;
                padding: 0;
            }
            .header div, .header h2, .supplier-info div, .table td, .table th, .signatures div, .signature-box div {
                color: #000 !important;
                font-weight: bold !important;
            }
            .signature-box {
                padding: 3mm;
                min-width: 35mm;
                overflow: visible;
                font-size: 10pt;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>
    <button onclick="window.location.href='purchase_order.php'" class="back-button no-print">
        Back
    </button>

    <div class="wrapper">
        <?php if (isset($_SESSION['warning'])): ?>
            <div class="warning no-print"><?= htmlspecialchars($_SESSION['warning']); unset($_SESSION['warning']); ?></div>
        <?php endif; ?>
        
        <div class="po-container">
            <div class="header">
                <h2>PURCHASE ORDER</h2>
                <div><?= htmlspecialchars($po['po_number']) ?></div>
                <div>Date: <?= date('d-M-Y H:i', strtotime($po['po_date'])) ?></div>
            </div>
            
            <div class="supplier-info">
                <div><strong>Supplier:</strong> <?= htmlspecialchars($po['supplier_name']) ?></div>
                <div><strong>Contact:</strong> <?= htmlspecialchars($po['supplier_contact']) ?></div>
                <div><strong>Address:</strong> <?= htmlspecialchars($po['supplier_address']) ?></div>
            </div>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Unit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['item_name']) ?></td>
                        <td><?= (int)$item['quantity'] ?></td>
                        <td><?= strtoupper(htmlspecialchars($item['mass_unit'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="signatures">
                <div class="signature-box">
                    <div>Requested By:</div>
                    <div><?= htmlspecialchars($po['received_by']) ?></div>
                    <div class="signature-line"></div>
                </div>
                <div class="signature-box">
                    <div>Confirmed By:</div>
                    <div><?= htmlspecialchars($po['confirmed_by_name']) ?></div>
                    <div class="signature-line"></div>
                </div>
            </div>
        </div>
        
        <div class="controls no-print">
            <button onclick="window.print()">Print PO</button>
            <button onclick="window.location.href='purchase_order.php'">Back to Create PO</button>
        </div>
    </div>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            // Small delay to ensure page is fully rendered
            setTimeout(function() {
                window.print();
            }, 500);
        };

        // Handle after print event
        window.onafterprint = function() {
            console.log("Print completed or cancelled");
            // You can add any post-print actions here if needed
        };
    </script>
</body>
</html>