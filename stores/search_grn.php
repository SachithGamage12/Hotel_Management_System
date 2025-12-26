<?php
// Start session
session_start();

// Set timezone to Indian Standard Time (IST)
date_default_timezone_set('Asia/Kolkata');

// Prevent browser caching
header("Cache-Control: no-cache, must-revalidate");

// Database connection settings
$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed. Please check server logs.");
}

// Get logged in username from session
$logged_in_user = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// Initialize variables for GRN data
$grn_data = null;
$grn_items = [];
$error_message = '';
$success_message = '';

// Handle GRN search
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_grn'])) {
    $grn_number = trim($_POST['grn_number']);
    
    // Validate GRN number
    if (empty($grn_number)) {
        $error_message = "Please enter a GRN number.";
    } else {
        // Fetch GRN details
        $stmt = $conn->prepare("SELECT grn_number, date, location, received_by, checked_by FROM grn_records WHERE grn_number = ?");
        if ($stmt === false) {
            error_log("Prepare failed for GRN search query: " . $conn->error);
            $error_message = "Database error: Unable to prepare GRN search query.";
        } else {
            $stmt->bind_param("s", $grn_number);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $grn_data = $result->fetch_assoc();
                
                // Fetch GRN items
                $item_stmt = $conn->prepare("SELECT item_name, quantity, unit FROM grn_items WHERE grn_id = (SELECT id FROM grn_records WHERE grn_number = ?)");
                if ($item_stmt === false) {
                    error_log("Prepare failed for GRN items query: " . $conn->error);
                    $error_message = "Database error: Unable to fetch GRN items.";
                } else {
                    $item_stmt->bind_param("s", $grn_number);
                    $item_stmt->execute();
                    $item_result = $item_stmt->get_result();
                    
                    while ($row = $item_result->fetch_assoc()) {
                        $grn_items[] = [
                            'item_name' => $row['item_name'],
                            'quantity' => $row['quantity'],
                            'unit' => $row['unit']
                        ];
                    }
                    $item_stmt->close();
                    
                    if (empty($grn_items)) {
                        $error_message = "No items found for GRN $grn_number.";
                    } else {
                        $success_message = "GRN $grn_number found successfully!";
                    }
                }
            } else {
                $error_message = "GRN number not found.";
            }
            $stmt->close();
        }
    }
}

$date = isset($grn_data['date']) ? $grn_data['date'] : date("Y-m-d H:i:s");
$grn_number = isset($grn_data['grn_number']) ? $grn_data['grn_number'] : 'Not Found';
$location = isset($grn_data['location']) ? $grn_data['location'] : 'N/A';
$received_by = isset($grn_data['received_by']) ? $grn_data['received_by'] : $logged_in_user;
$checked_by = isset($grn_data['checked_by']) ? $grn_data['checked_by'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=80mm, initial-scale=1.0">
    <title>Search Goods Received Note</title>
    <link rel="icon" type="image/avif" href="../images/logo.avif">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        body {
            font-size: 8pt;
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
        .grn-container {
            width: 80mm;
            padding: 3mm;
            text-align: left;
        }
        .grn-header {
            margin: 0 0 5mm 0;
            padding: 0 0 3mm 0;
        }
        .grn-header h2 {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 4mm;
            text-transform: uppercase;
            text-align: center;
            border-bottom: 1.5px solid #000;
        }
        .grn-header div {
            font-size: 8pt;
            font-weight: bold;
            display: flex;
            align-items: center;
            margin-bottom: 3mm;
        }
        .grn-header div strong {
            display: inline-block;
            min-width: 20mm;
            text-align: left;
        }
        .grn-header div .colon {
            display: inline-block;
            width: 2mm;
            text-align: center;
        }
        .grn-header div span {
            display: inline-block;
            margin-left: 1mm;
        }
        .grn-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 5mm 0;
            font-size: 8pt;
            text-align: left;
        }
        .grn-table th {
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            padding: 2mm;
            text-align: left;
            font-weight: bold;
        }
        .grn-table th:nth-child(2) {
            width: 25%;
            text-align: center;
        }
        .grn-table th:nth-child(3) {
            width: 25%;
            text-align: center;
        }
        .grn-table td {
            padding: 2mm;
            border-bottom: 0.5px solid #000;
            vertical-align: top;
            font-weight: bold;
        }
        .grn-table td:first-child {
            width: 50%;
            word-wrap: break-word;
            white-space: normal;
        }
        .grn-table td:nth-child(2) {
            width: 25%;
            text-align: center;
            white-space: nowrap;
        }
        .grn-table td:nth-child(3) {
            width: 25%;
            text-align: center;
            white-space: nowrap;
        }
        .grn-table tr:last-child td {
            border-bottom: 1.5px solid #000;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin: 0 0 5mm 0;
            font-size: 8pt;
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
            font-size: 8pt;
            white-space: nowrap;
            margin-bottom: 3mm;
        }
        .signature-line {
            border-top: 1.5px solid #000;
            margin-top: 6mm;
            padding-top: 2mm;
        }
        .grn-footer {
            text-align: center;
            font-size: 8pt;
            font-weight: bold;
            margin-top: 5mm;
            padding-top: 3mm;
            border-top: 1px dashed #000;
        }
        .controls, .form-container, .user-info, .alert {
            text-align: center;
            margin: 0 0 5mm 0;
            padding-top: 3mm;
            border-top: 2px dashed #000;
            width: 80mm;
        }
        .controls button, .form-container button {
            padding: 2mm 4mm;
            margin: 0 2mm;
            font-size: 8pt;
            cursor: pointer;
            background: #f0f0f0;
            border: 1px solid #000;
            font-weight: bold;
        }
        .controls button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .form-container {
            background: white;
            padding: 3mm;
            margin-bottom: 5mm;
        }
        .form-group {
            margin-bottom: 3mm;
        }
        label {
            display: block;
            margin-bottom: 1mm;
            font-weight: bold;
            font-size: 8pt;
        }
        input[type="text"] {
            width: 100%;
            padding: 2mm;
            border: 1px solid #000;
            font-size: 8pt;
            box-sizing: border-box;
        }
        input[type="text"]:focus {
            border-color: #000;
            outline: none;
        }
        .alert {
            background: #f2dede;
            color: #a94442;
            padding: 2mm;
            margin: 0 0 4mm 0;
            font-size: 8pt;
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
            body {
                width: 80mm;
                margin: 0;
                padding: 0;
                font-size: 9pt;
                line-height: 1.6;
                color: #000 !important;
                background: #fff !important;
                min-height: 0;
                display: block;
            }
            .wrapper {
                width: 80mm;
                margin: 0 auto;
            }
            .grn-container, .print-content {
                width: 80mm;
                margin: 0 auto;
                padding: 0;
                text-align: left;
            }
            .grn-header {
                margin: 0 0 5mm 0;
                padding: 0 0 3mm 0;
                text-align: left;
            }
            .grn-header h2 {
                font-size: 12pt;
                margin-bottom: 4mm;
                border-bottom: 1.5px solid #000;
                text-align: center;
            }
            .grn-header div {
                font-size: 9pt;
                margin-bottom: 3mm;
            }
            .grn-table {
                width: 80mm;
                border-collapse: collapse;
                margin: 0;
                padding: 0;
                font-size: 9pt;
                table-layout: fixed;
                text-align: left;
            }
            .grn-table tr {
                margin-bottom: 3mm;
            }
            .grn-table th {
                padding: 2mm;
                border-top: 1.5px solid #000;
                border-bottom: 1.5px solid #000;
                text-align: left;
                font-weight: bold;
            }
            .grn-table td {
                padding: 2mm;
                border-bottom: 0.5px solid #000;
                text-align: left;
                font-weight: bold;
            }
            .grn-table th:first-child, .grn-table td:first-child {
                width: 50%;
                word-wrap: break-word;
                white-space: normal;
            }
            .grn-table th:nth-child(2), .grn-table td:nth-child(2) {
                width: 25%;
                text-align: center;
            }
            .grn-table th:nth-child(3), .grn-table td:nth-child(3) {
                width: 25%;
                text-align: center;
            }
            .grn-table tr:last-child td {
                border-bottom: 1.5px solid #000;
            }
            .signatures {
                margin: 0 0 5mm 0;
                text-align: left;
            }
            .signature-box {
                padding: 3mm;
                font-size: 9pt;
            }
            .signature-box div {
                margin-bottom: 3mm;
            }
            .signature-line {
                margin-top: 6mm;
                padding-top: 2mm;
            }
            .grn-footer {
                margin: 0 0 5mm 0;
                padding: 3mm 0 0 0;
                font-size: 9pt;
                text-align: center;
            }
            .controls, .form-container, .user-info, .alert, .no-print {
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
            .print-content {
                display: block !important;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div style="position: absolute; top: 20px; left: 20px;">
            <button onclick="window.location.href='../stores.php'" 
                style="background-color: #f09424ff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                Back
            </button>
        </div>

        <div class="no-print">
            <?php if (!empty($logged_in_user)): ?>
                <div class="user-info">Logged in as: <?php echo htmlspecialchars($logged_in_user); ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="grn_number">Search GRN Number</label>
                        <input type="text" id="grn_number" name="grn_number" placeholder="Enter GRN number (e.g., GRN-1500)" required>
                    </div>
                    <button type="submit" name="search_grn" class="btn-primary">Search GRN</button>
                </form>
            </div>

            <?php if (isset($success_message) && !empty($success_message)): ?>
                <div class="alert"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            <?php if (isset($error_message) && !empty($error_message)): ?>
                <div class="alert"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
        </div>

        <?php if (!empty($grn_data) && !empty($grn_items)): ?>
            <div class="grn-container">
                <div class="grn-header">
                    <h2>Goods Received Note</h2>
                    <div><strong>GRN No</strong><span class="colon">: </span><span id="print_grn_no"><?php echo htmlspecialchars($grn_number); ?></span></div>
                    <div><strong>Date</strong><span class="colon">: </span><span id="print_date"><?php echo date('d-M-Y H:i', strtotime($date)); ?></span></div>
                    <div><strong>Location</strong><span class="colon">: </span><span id="print_location"><?php echo htmlspecialchars($location); ?></span></div>
                </div>

                <table class="grn-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Unit</th>
                        </tr>
                    </thead>
                    <tbody id="print_items">
                        <?php foreach ($grn_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                <td><?php echo (int)$item['quantity']; ?></td>
                                <td><?php echo strtoupper(htmlspecialchars($item['unit'])); ?></td>
                            </tr>
                            <?php error_log("Rendering item: item_name={$item['item_name']}, quantity={$item['quantity']}, unit={$item['unit']}"); ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="signatures">
                    <div class="signature-box">
                        <div>Received By:</div>
                        <div><?php echo htmlspecialchars($received_by); ?></div>
                        <div class="signature-line"></div>
                    </div>
                    <div class="signature-box">
                        <div>Checked By:</div>
                        <div><?php echo htmlspecialchars($checked_by); ?></div>
                        <div class="signature-line"></div>
                    </div>
                </div>

                <div class="grn-footer">System Generated!</div>
            </div>

            <div id="print-content" class="print-content"></div>

            <div class="no-print controls">
                <button type="button" onclick="printGRN()" class="btn-warning" id="print-btn">Print GRN</button>
            </div>

            <div id="print-error" class="no-print alert" style="display: none;">Print failed. Please check printer settings.</div>
        <?php endif; ?>
    </div>

    <script src="../assets/js/qz-tray.js"></script>
    <script>
        // Debugging: Log GRN data
        console.log("GRN Data:", <?php echo json_encode($grn_data); ?>);
        console.log("GRN Items:", <?php echo json_encode($grn_items); ?>);

        // Ensure QZ Tray certificate is loaded (if required)
        if (typeof qz !== 'undefined') {
            qz.security.setCertificatePromise(function(resolve, reject) {
                console.log("Setting QZ Tray certificate...");
                resolve();
            });
        } else {
            console.error("QZ Tray not loaded.");
        }

        function printGRN() {
            console.log("Starting printGRN...");
            const printError = document.getElementById('print-error');
            const printContentDiv = document.getElementById('print-content');
            
            // Verify print data
            const grnNo = document.getElementById('print_grn_no').textContent;
            const date = document.getElementById('print_date').textContent;
            const location = document.getElementById('print_location').textContent;
            const checkedBy = '<?php echo addslashes(htmlspecialchars($checked_by)); ?>';
            const receivedBy = '<?php echo addslashes(htmlspecialchars($received_by)); ?>';
            
            // Clone the table
            const tableClone = document.getElementById('print_items').cloneNode(true);
            const itemsHtml = tableClone.innerHTML;
            
            console.log("Print data:", { grnNo, date, location, checkedBy, itemsHtml, receivedBy });

            if (!grnNo || grnNo === 'Not Found') {
                console.error("Invalid GRN number.");
                alert("Error: Invalid GRN number.");
                return;
            }

            if (!itemsHtml) {
                console.error("No items to print.");
                alert("Error: No items found for GRN.");
                return;
            }

            const printContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <style>
                        * {
                            margin: 0;
                            padding: 0;
                            box-sizing: border-box;
                            font-family: 'Arial', sans-serif;
                        }
                        body {
                            font-size: 9pt;
                            line-height: 1.6;
                            color: #000;
                            background: #fff;
                            width: 80mm;
                            margin: 0;
                            padding: 0;
                        }
                        .grn-container {
                            width: 80mm;
                            margin: 0 auto;
                            padding: 0;
                            text-align: left;
                        }
                        .grn-header {
                            margin: 0 0 5mm 0;
                            padding: 0 0 3mm 0;
                            text-align: left;
                        }
                        .grn-header h2 {
                            font-size: 12pt;
                            font-weight: bold;
                            margin-bottom: 4mm;
                            text-transform: uppercase;
                            text-align: center;
                            border-bottom: 1.5px solid #000;
                        }
                        .grn-header div {
                            font-size: 9pt;
                            font-weight: bold;
                            display: flex;
                            align-items: center;
                            margin-bottom: 3mm;
                        }
                        .grn-header div strong {
                            display: inline-block;
                            min-width: 20mm;
                            text-align: left;
                        }
                        .grn-header div .colon {
                            display: inline-block;
                            width: 2mm;
                            text-align: center;
                        }
                        .grn-header div span {
                            display: inline-block;
                            margin-left: 1mm;
                        }
                        .grn-table {
                            width: 80mm;
                            border-collapse: collapse;
                            margin: 0;
                            padding: 0;
                            font-size: 9pt;
                            table-layout: fixed;
                            text-align: left;
                        }
                        .grn-table tr {
                            margin-bottom: 3mm;
                        }
                        .grn-table th {
                            border-top: 1.5px solid #000;
                            border-bottom: 1.5px solid #000;
                            padding: 2mm;
                            text-align: left;
                            font-weight: bold;
                        }
                        .grn-table td {
                            padding: 2mm;
                            border-bottom: 0.5px solid #000;
                            text-align: left;
                            font-weight: bold;
                        }
                        .grn-table th:first-child, .grn-table td:first-child {
                            width: 50%;
                            word-wrap: break-word;
                            white-space: normal;
                        }
                        .grn-table th:nth-child(2), .grn-table td:nth-child(2) {
                            width: 25%;
                            text-align: center;
                        }
                        .grn-table th:nth-child(3), .grn-table td:nth-child(3) {
                            width: 25%;
                            text-align: center;
                        }
                        .grn-table tr:last-child td {
                            border-bottom: 1.5px solid #000;
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
                            font-size: 9pt;
                            white-space: nowrap;
                            margin-bottom: 3mm;
                        }
                        .signature-line {
                            border-top: 1.5px solid #000;
                            margin-top: 6mm;
                            padding-top: 2mm;
                        }
                        .grn-footer {
                            margin: 0 0 5mm 0;
                            padding: 3mm 0 0 0;
                            border-top: 1px dashed #000;
                            font-size: 9pt;
                            text-align: center;
                            font-weight: bold;
                        }
                    </style>
                </head>
                <body>
                    <div class='grn-container'>
                        <div class='grn-header'>
                            <h2>Goods Received Note</h2>
                            <div><strong>GRN No</strong><span class='colon'>: </span><span>${grnNo}</span></div>
                            <div><strong>Date</strong><span class='colon'>: </span><span>${date}</span></div>
                            <div><strong>Location</strong><span class='colon'>: </span><span>${location}</span></div>
                        </div>
                        <table class='grn-table'>
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Unit</th>
                                </tr>
                            </thead>
                            <tbody>${itemsHtml}</tbody>
                        </table>
                        <div class='signatures'>
                            <div class='signature-box'>
                                <div>Received By:</div>
                                <div>${receivedBy}</div>
                                <div class='signature-line'></div>
                            </div>
                            <div class='signature-box'>
                                <div>Checked By:</div>
                                <div>${checkedBy}</div>
                                <div class='signature-line'></div>
                            </div>
                        </div>
                        <div class='grn-footer'>System Generated!</div>
                    </div>
                </body>
                </html>
            `;
            
            console.log("Print content prepared:", printContent.substring(0, 200) + "...");
            printContentDiv.innerHTML = printContent;

            setTimeout(function () {
                if (typeof qz === 'undefined') {
                    console.error("❌ QZ Tray JS library not loaded. Falling back to window.print().");
                    printError.style.display = 'block';
                    window.print();
                    setTimeout(() => {
                        console.log("Hiding print error after 5 seconds.");
                        printError.style.display = 'none';
                    }, 5000);
                    return;
                }
                console.log("Attempting QZ Tray connection...");
                qz.websocket.connect().then(function () {
                    console.log("✅ QZ Tray connected");
                    return qz.printers.find("POSPrinter POS-80C").catch(function(err) {
                        console.error("Printer not found: ", err);
                        printError.style.display = 'block';
                        alert("Printer not found. Check QZ Tray settings.");
                        setTimeout(() => {
                            console.log("Hiding print error after 5 seconds.");
                            printError.style.display = 'none';
                        }, 5000);
                        throw err;
                    });
                }).then(function (printer) {
                    console.log("🖨️ Printer found: " + printer);
                    const config = qz.configs.create(printer, {
                        margins: { top: 0, right: 0, bottom: 0, left: 0 },
                        size: { width: 80, height: 'auto' },
                        units: 'mm'
                    });
                    console.log("Print config created:", config);

                    const data = [{
                        type: 'html',
                        format: 'plain',
                        data: printContent
                    }];

                    console.log("Sending print job...");
                    return qz.print(config, data);
                }).then(function () {
                    console.log("✅ Print job sent");
                    return qz.websocket.disconnect();
                }).catch(function (err) {
                    console.error("❌ QZ Tray error: ", err);
                    printError.style.display = 'block';
                    alert("Print failed: " + err.message);
                    setTimeout(() => {
                        console.log("Hiding print error after 5 seconds.");
                        printError.style.display = 'none';
                    }, 5000);
                });
            }, 500);
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>