<?php
require_once '../config/db_connection.php';
session_start();

// Set timezone to Sri Lanka Standard Time
date_default_timezone_set('Asia/Colombo');

// Prevent browser caching
header("Cache-Control: no-cache, must-revalidate");

// Debug session
error_log("Session data in print_po.php: " . json_encode($_SESSION));

// Check if either print_po or po_data is set
if (!isset($_SESSION['print_po']) && !isset($_SESSION['po_data'])) {
    $_SESSION['error'] = "No purchase order data available.";
    error_log("Redirecting to create_po.php: No PO data");
    header("Location: create_po.php");
    exit();
}

try {
    $conn = getDBConnection();
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // If print_po is set, fetch new data
    if (isset($_SESSION['print_po'])) {
        $po_id = (int)$_SESSION['print_po'];
        error_log("Fetching PO with id: $po_id");

        if ($po_id <= 0) {
            throw new Exception("Invalid PO ID: $po_id");
        }

        // Fetch PO header
        $stmt = $conn->prepare("
            SELECT po.po_number, po.created_at, po.supplier_id, po.confirmed_by, po.requested_by,
                   s.name AS supplier_name, s.contact_number AS supplier_contact, s.address AS supplier_address,
                   u1.username AS confirmed_by_name, u2.username AS requested_by_name
            FROM logistic_purchase_orders po
            LEFT JOIN suppliers s ON po.supplier_id = s.id
            LEFT JOIN logestic_users u1 ON po.confirmed_by = u1.id
            LEFT JOIN logi_users u2 ON po.requested_by = u2.id
            WHERE po.id = ?
        ");
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $po_id);
        $stmt->execute();
        $po = $stmt->get_result()->fetch_assoc();

        if (!$po) {
            throw new Exception("Purchase Order not found for id: $po_id");
        }

        // Debug: Log the PO data
        error_log("PO data: " . json_encode($po));

        // Fetch PO items
        $stmt = $conn->prepare("
            SELECT pi.item_id, pi.quantity, pi.unit, i.name AS item_name
            FROM logistic_po_items pi
            LEFT JOIN items i ON pi.item_id = i.id
            WHERE pi.po_id = ?
        ");
        if ($stmt === false) {
            throw new Exception("Prepare failed for items: " . $conn->error);
        }
        $stmt->bind_param("i", $po_id);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (empty($items)) {
            throw new Exception("No items found for this purchase order.");
        }

        // Track invalid items
        $invalid_items = [];
        foreach ($items as $index => $item) {
            if (!isset($item['item_name']) || $item['item_name'] === null) {
                error_log("Missing item_name for item_id: " . $item['item_id'] . " in po_id: $po_id");
                $items[$index]['item_name'] = "Invalid Item (ID: {$item['item_id']})";
                $invalid_items[] = $item['item_id'];
            }
        }

        if (!empty($invalid_items)) {
            $_SESSION['warning'] = "Some items (IDs: " . implode(', ', $invalid_items) . ") could not be found in items table.";
        }

        // Store data in session for refresh
        $_SESSION['po_data'] = [
            'po' => $po,
            'items' => $items
        ];

        // Clear print_po
        unset($_SESSION['print_po']);
    } else {
        // Use cached data
        $po = $_SESSION['po_data']['po'];
        $items = $_SESSION['po_data']['items'];

        // Debug: Log the cached PO data
        error_log("Cached PO data: " . json_encode($po));

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

        if (!empty($invalid_items)) {
            $_SESSION['warning'] = "Some items (IDs: " . implode(', ', $invalid_items) . ") could not be found in items table.";
            $_SESSION['po_data']['items'] = $items;
        }
    }

} catch (Exception $e) {
    error_log("Error in print_po.php: " . $e->getMessage());
    $_SESSION['error'] = "Error: " . $e->getMessage();
    unset($_SESSION['po_data']);
    header("Location: create_po.php");
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
            width: 80mm;
            margin: 0;
            padding: 0;
            color: #000;
            background: #fff;
            min-height: 150mm;
        }
        .po-container {
            width: 80mm;
            margin: 0;
            padding: 0;
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
            width: 80mm;
            border-collapse: collapse;
            margin: 0 0 5mm 0;
            font-size: 9pt;
            table-layout: fixed;
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
            word-wrap: break-word;
            white-space: normal;
        }
        .table td:not(:first-child) {
            white-space: nowrap;
            text-align: center;
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
        .print-content {
            display: none;
        }
        @page {
            size: 80mm auto;
            margin: 0;
        }
        @media print {
            .controls, .controls *, .warning, .no-print {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                width: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
                position: absolute !important;
                left: -9999px !important;
            }
            body {
                padding: 0;
                margin: 0;
                width: 80mm;
                font-size: 10pt;
                color: #000 !important;
                min-height: 150mm;
            }
            .po-container {
                width: 80mm;
                margin: 0;
                padding: 0;
                margin-bottom: 5mm;
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
            .print-content {
                display: block !important;
            }
        }
    </style>
</head>
<body>
    <div class="po-container">
        <?php if (isset($_SESSION['warning'])): ?>
            <div class="warning"><?= htmlspecialchars($_SESSION['warning']); unset($_SESSION['warning']); ?></div>
        <?php endif; ?>
        
        <div class="header">
            <h2>LOGISTIC<br>PURCHASE ORDER</h2>
            <div><?= htmlspecialchars($po['po_number']) ?></div>
            <div>Date: <?= date('d-M-Y H:i', strtotime($po['created_at'])) ?></div>
        </div>
        
        <div class="supplier-info">
            <div><strong>Supplier:</strong> <?= htmlspecialchars($po['supplier_name'] ?? 'N/A') ?></div>
            <div><strong>Contact:</strong> <?= htmlspecialchars($po['supplier_contact'] ?? 'N/A') ?></div>
            <div><strong>Address:</strong> <?= htmlspecialchars($po['supplier_address'] ?? 'N/A') ?></div>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50%;">Item</th>
                    <th style="width: 20%;">Qty</th>
                    <th style="width: 30%;">Unit</th>
                </tr>
            </thead>
            <tbody id="print_items">
                <?php foreach($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['item_name']) ?></td>
                    <td><?= number_format($item['quantity'], 2) ?></td>
                    <td><?= strtoupper(htmlspecialchars($item['unit'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="signatures">
            <div class="signature-box">
                <div>Requested By:</div>
                <div><?= htmlspecialchars($po['requested_by_name'] ?? 'N/A') ?></div>
                <div class="signature-line"></div>
            </div>
            <div class="signature-box">
                <div>Confirmed By:</div>
                <div><?= htmlspecialchars($po['confirmed_by_name'] ?? 'N/A') ?></div>
                <div class="signature-line"></div>
            </div>
        </div>
    </div>
    
    <div id="print-content" class="print-content"></div>
    
    <div class="no-print controls">
        <button onclick="window.location.href='create_po.php'">Back</button>
        <button onclick="printPO()">Print</button>
    </div>
    
    <div id="print-error" class="no-print warning" style="display: none;">Print failed. Please check printer settings.</div>

    <script>
        // Auto-print on page load if po_data exists
        <?php if (!empty($_SESSION['po_data'])): ?>
        window.onload = function() {
            printPO();
        };
        <?php endif; ?>

        function printPO() {
            console.log("Starting printPO...");
            const printError = document.getElementById('print-error');
            const poNumber = '<?php echo addslashes(htmlspecialchars($po['po_number'])); ?>';
            const itemsHtml = document.getElementById('print_items').innerHTML;

            // Validate data
            if (!poNumber) {
                console.error("Invalid PO number.");
                alert("Error: Invalid Purchase Order number.");
                printError.style.display = 'block';
                setTimeout(() => {
                    printError.style.display = 'none';
                    window.location.href = 'create_po.php';
                }, 3000);
                return;
            }

            if (!itemsHtml || itemsHtml.includes("No items found")) {
                console.error("No items to print.");
                alert("Error: No items in Purchase Order.");
                printError.style.display = 'block';
                setTimeout(() => {
                    printError.style.display = 'none';
                    window.location.href = 'create_po.php';
                }, 3000);
                return;
            }

            // Trigger browser print dialog
            console.log("Opening print dialog...");
            window.print();

            // Redirect after a short delay to allow print dialog to open
            setTimeout(() => {
                console.log("Redirecting to create_po.php after print.");
                window.location.href = 'create_po.php';
            }, 1000);
        }
    </script>
</body>
</html>
<?php 
unset($_SESSION['po_data']); // Clear session data after use
$conn->close(); 
?>