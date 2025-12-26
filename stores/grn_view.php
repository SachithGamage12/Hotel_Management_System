<?php
// Start session
session_start();
// Set timezone to Indian Standard Time (IST)
date_default_timezone_set('Asia/Kolkata');
// Prevent browser caching
header("Cache-Control: no-cache, must-revalidate");

// Database connection
$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed. Please check server logs.");
}

$logged_in_user = $_SESSION['username'] ?? '';

// Initialize display
$display_items = [];
$display_grn_number = 'Pending';
$display_datetime = date("Y-m-d H:i:s");
$display_location = '';
$display_checked_by = '';
$display_received_by = $logged_in_user;
$search_error = '';

// Handle GRN search
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_grn'])) {
    $search_grn_number = trim($_POST['search_grn_number']);
    
    if (!is_numeric($search_grn_number) || $search_grn_number < 1500) {
        $search_error = "Please enter a valid GRN number (1500 or higher).";
    } else {
        $full_grn_number = "GRN-" . $search_grn_number;

        $stmt = $conn->prepare("SELECT grn_number, date, location, received_by, checked_by FROM grn_records WHERE grn_number = ?");
        if (!$stmt) {
            $search_error = "Database error.";
            error_log("Prepare failed: " . $conn->error);
        } else {
            $stmt->bind_param("s", $full_grn_number);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $grn = $result->fetch_assoc();

                // Fetch items with unit_price
                $item_stmt = $conn->prepare("
                    SELECT item_name, quantity, unit, unit_price 
                    FROM grn_items 
                    WHERE grn_id = (SELECT id FROM grn_records WHERE grn_number = ?)
                ");
                if ($item_stmt) {
                    $item_stmt->bind_param("s", $full_grn_number);
                    $item_stmt->execute();
                    $items_result = $item_stmt->get_result();

                    $display_items = [];
                    while ($item = $items_result->fetch_assoc()) {
                        $qty = floatval($item['quantity']);
                        $price = floatval($item['unit_price']);
                        $display_items[] = [
                            'item_name' => $item['item_name'],
                            'quantity' => $qty,
                            'unit' => $item['unit'],
                            'unit_price' => $price,
                            'total' => $qty * $price
                        ];
                    }
                    $item_stmt->close();
                }

                // Set display values
                $display_grn_number = $grn['grn_number'];
                $display_datetime = $grn['date'];
                $display_location = $grn['location'];
                $display_received_by = $grn['received_by'];
                $display_checked_by = $grn['checked_by'];
            } else {
                $search_error = "GRN $full_grn_number not found.";
            }
            $stmt->close();
        }
    }
}

// Calculate grand total
$grand_total = array_sum(array_column($display_items, 'total'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=80mm, initial-scale=1.0">
    <title>View GRN</title>
    <link rel="icon" type="image/avif" href="../images/logo.avif">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Arial', sans-serif; }
        body { font-size: 8pt; line-height: 1.5; width: 100%; margin: 0; padding: 0; color: #000; background: #fff; min-height: 150mm; display: flex; justify-content: center; }
        .wrapper { width: 80mm; margin: 0 auto; position: relative; }
        .grn-container { width: 80mm; padding: 3mm; text-align: left; }
        .grn-header { margin: 0 0 5mm 0; padding: 0 0 3mm 0; }
        .grn-header h2 { font-size: 11pt; font-weight: bold; margin-bottom: 4mm; text-transform: uppercase; text-align: center; border-bottom: 1.5px solid #000; }
        .grn-header div { font-size: 8pt; font-weight: bold; display: flex; align-items: center; margin-bottom: 3mm; }
        .grn-header div strong { min-width: 20mm; }
        .grn-header div .colon { width: 2mm; text-align: center; }
        .grn-header div span { margin-left: 1mm; }

        .grn-table { width: 100%; border-collapse: collapse; margin: 0 0 5mm 0; font-size: 8pt; }
        .grn-table th { border-top: 1.5px solid #000; border-bottom: 1.5px solid #000; padding: 2mm; font-weight: bold; text-align: center; }
        .grn-table td { padding: 2mm; border-bottom: 0.5px solid #000; font-weight: bold; }
        .grn-table td:first-child { width: 40%; word-wrap: break-word; white-space: normal; }
        .grn-table td:nth-child(2), .grn-table td:nth-child(3), .grn-table td:nth-child(4) { width: 15%; text-align: center; }
        .grn-table td:nth-child(5) { width: 20%; text-align: right; }
        .grn-table tr:last-child td { border-bottom: 1.5px solid #000; }

        .total-row td { border-top: 1.5px solid #000; font-weight: bold; text-align: right; padding-top: 3mm; }
        .total-row td:first-child { text-align: left; }

        .signatures { display: flex; justify-content: space-between; margin: 5mm 0; font-size: 8pt; }
        .signature-box { width: 48%; text-align: center; padding: 3mm; }
        .signature-line { border-top: 1.5px solid #000; margin-top: 6mm; padding-top: 2mm; }
        .grn-footer { text-align: center; font-size: 8pt; font-weight: bold; margin-top: 5mm; padding-top: 3mm; border-top: 1px dashed #000; }

        .form-container, .alert, .controls { width: 80mm; text-align: center; margin: 0 0 5mm; padding-top: 3mm; border-top: 2px dashed #000; }
        .form-container { background: white; padding: 3mm; }
        .form-group { margin-bottom: 3mm; }
        label { display: block; margin-bottom: 1mm; font-weight: bold; font-size: 8pt; }
        input[type="text"] { width: 100%; padding: 2mm; border: 1px solid #000; font-size: 8pt; }
        button { padding: 2mm 4mm; margin: 0 2mm; font-size: 8pt; background: #f0f0f0; border: 1px solid #000; font-weight: bold; cursor: pointer; }
        button:disabled { background: #ccc; cursor: not-allowed; }
        .alert { background: #f2dede; color: #a94442; padding: 2mm; margin: 0 0 4mm; font-size: 8pt; border: 1px solid #a94442; font-weight: bold; text-align: left; }
        .back-button { position: fixed; top: 20px; left: 20px; z-index: 1000; background: #f09424ff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }

        @page { size: 80mm auto; margin: 0; }
        @media print {
            body, .wrapper, .grn-container { width: 80mm; margin: 0; padding: 0; font-size: 9pt; line-height: 1.6; }
            .grn-header h2 { font-size: 12pt; }
            .grn-header div { font-size: 9pt; }
            .grn-table { font-size: 9pt; table-layout: fixed; }
            .grn-table th, .grn-table td { padding: 2mm; }
            .grn-table th:first-child, .grn-table td:first-child { width: 38%; }
            .grn-table th:nth-child(2), .grn-table td:nth-child(2) { width: 14%; }
            .grn-table th:nth-child(3), .grn-table td:nth-child(3) { width: 14%; }
            .grn-table th:nth-child(4), .grn-table td:nth-child(4) { width: 16%; }
            .grn-table th:nth-child(5), .grn-table td:nth-child(5) { width: 18%; text-align: right; }
            .total-row td { font-size: 9pt; }
            .signatures, .signature-box, .grn-footer { font-size: 9pt; }
            .form-container, .controls, .alert, .back-button, .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <button onclick="window.location.href='../stores.php'" class="back-button no-print">Back</button>

        <div class="no-print">
            <?php if ($logged_in_user): ?>
                <div class="user-info">Logged in as: <?php echo htmlspecialchars($logged_in_user); ?></div>
            <?php endif; ?>

            <div class="form-container">
                <h3>Search GRN by Number</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="search_grn_number">GRN Number</label>
                        <input type="text" name="search_grn_number" id="search_grn_number" placeholder="e.g. 1500" pattern="[0-9]+" required>
                    </div>
                    <button type="submit" name="search_grn">Search GRN</button>
                </form>
            </div>

            <?php if ($search_error): ?>
                <div class="alert"><?php echo htmlspecialchars($search_error); ?></div>
            <?php endif; ?>
        </div>

        <div class="grn-container">
            <div class="grn-header">
                <h2>Goods Received Note</h2>
                <div><strong>GRN No</strong><span class="colon">: </span><span id="print_grn_no"><?php echo htmlspecialchars($display_grn_number); ?></span></div>
                <div><strong>Date</strong><span class="colon">: </span><span id="print_date"><?php echo (new DateTime($display_datetime))->format('d-M-Y H:i'); ?></span></div>
                <div><strong>Location</strong><span class="colon">: </span><span id="print_location"><?php echo htmlspecialchars($display_location); ?></span></div>
            </div>

            <table class="grn-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Unit Price (Rs.)</th>
                        <th>Total (Rs.)</th>
                    </tr>
                </thead>
                <tbody id="print_items">
                    <?php if (empty($display_items)): ?>
                        <tr><td colspan="5">Search for a GRN to view details.</td></tr>
                    <?php else: ?>
                        <?php foreach ($display_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                <td><?php echo (floor($item['quantity']) == $item['quantity']) ? (int)$item['quantity'] : number_format($item['quantity'], 3); ?></td>
                                <td><?php echo strtoupper(htmlspecialchars($item['unit'])); ?></td>
                                <td><?php echo number_format($item['unit_price'], 2); ?></td>
                                <td><?php echo number_format($item['total'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td colspan="4"><strong>Grand Total</strong></td>
                            <td><strong>Rs. <?php echo number_format($grand_total, 2); ?></strong></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="signatures">
                <div class="signature-box">
                    <div>Received By:</div>
                    <div><?php echo htmlspecialchars($display_received_by); ?></div>
                    <div class="signature-line"></div>
                </div>
                <div class="signature-box">
                    <div>Checked By:</div>
                    <div><?php echo htmlspecialchars($display_checked_by); ?></div>
                    <div class="signature-line"></div>
                </div>
            </div>
            <div class="grn-footer">System Generated!</div>
        </div>

        <?php if (!empty($display_items)): ?>
            <div class="no-print controls">
                <button type="button" onclick="printGRN()" id="print-btn">Print GRN</button>
            </div>
        <?php endif; ?>
    </div>

    <script>
        let isPrinting = false;
        function printGRN() {
            if (isPrinting) return;
            isPrinting = true;

            const grnNo = document.getElementById('print_grn_no').textContent;
            const date = document.getElementById('print_date').textContent;
            const location = document.getElementById('print_location').textContent;
            const itemsHtml = document.getElementById('print_items').innerHTML;
            const receivedBy = <?= json_encode(htmlspecialchars($display_received_by)); ?>;
            const checkedBy = <?= json_encode(htmlspecialchars($display_checked_by)); ?>;

            if (grnNo === 'Pending' || itemsHtml.includes('Search for')) {
                alert('Please search for a valid GRN first.');
                isPrinting = false;
                return;
            }

            const printContent = `<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
                body{font-family:Arial,sans-serif;font-size:9pt;width:80mm;margin:0;padding:0;color:#000;background:#fff}
                .grn-container{width:80mm;margin:0 auto;padding:0}
                .grn-header h2{font-size:12pt;text-align:center;border-bottom:1.5px solid #000;margin-bottom:4mm}
                .grn-header div{font-size:9pt;font-weight:bold;margin-bottom:3mm;display:flex}
                .grn-header strong{min-width:20mm}
                .grn-table{width:100%;border-collapse:collapse;font-size:9pt}
                .grn-table th,.grn-table td{padding:2mm;border-bottom:0.5px solid #000;font-weight:bold;text-align:center}
                .grn-table th:first-child,.grn-table td:first-child{width:38%;text-align:left;word-wrap:break-word}
                .grn-table th:nth-child(2),.td:nth-child(2),.grn-table th:nth-child(3),.td:nth-child(3),.grn-table th:nth-child(4),.td:nth-child(4){width:15%}
                .grn-table th:nth-child(5),.grn-table td:nth-child(5){width:17%;text-align:right}
                .grn-table tr:last-child td{border-bottom:1.5px solid #000}
                .total-row td{border-top:1.5px solid #000;font-weight:bold;text-align:right;padding-top:3mm}
                .total-row td:first-child{text-align:left}
                .signatures{display:flex;justify-content:space-between;margin:5mm 0}
                .signature-box{width:48%;text-align:center;padding:3mm}
                .signature-line{border-top:1.5px solid #000;margin-top:6mm}
                .grn-footer{text-align:center;font-weight:bold;margin-top:5mm;padding-top:3mm;border-top:1px dashed #000}
            </style></head><body>
                <div class="grn-container">
                    <div class="grn-header">
                        <h2>Goods Received Note</h2>
                        <div><strong>GRN No</strong><span>: </span><span>${grnNo}</span></div>
                        <div><strong>Date</strong><span>: </span><span>${date}</span></div>
                        <div><strong>Location</strong><span>: </span><span>${location}</span></div>
                    </div>
                    <table class="grn-table">
                        <thead><tr><th>Item</th><th>Qty</th><th>Unit</th><th>Unit Price (Rs.)</th><th>Total (Rs.)</th></tr></thead>
                        <tbody>${itemsHtml}</tbody>
                    </table>
                    <div class="signatures">
                        <div class="signature-box"><div>Received By:</div><div>${receivedBy}</div><div class="signature-line"></div></div>
                        <div class="signature-box"><div>Checked By:</div><div>${checkedBy}</div><div class="signature-line"></div></div>
                    </div>
                    <div class="grn-footer">System Generated!</div>
                </div>
            </body></html>`;

            const win = window.open('', '_blank', 'width=80mm,height=auto');
            if (!win) { alert("Popup blocked!"); isPrinting = false; return; }
            win.document.write(printContent);
            win.document.close();
            win.onload = () => win.print();
            win.onafterprint = () => { win.close(); isPrinting = false; };
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>