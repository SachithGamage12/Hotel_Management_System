<?php
// Start session
session_start();

// Set timezone to Asia/Colombo
date_default_timezone_set('Asia/Colombo');

// Database connection parameters
$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die('<div class="alert alert-danger">Connection failed: ' . htmlspecialchars($conn->connect_error) . '</div>');
}
$conn->set_charset("utf8mb4");

// Function to format currency in LKR
function formatCurrency($amount) {
    return 'Rs. ' . number_format(floatval($amount), 2);
}

// Get current year and month for default values
$currentYear = date('Y');
$currentMonth = date('m');

// Initialize variables
$selectedMonth = isset($_POST['month']) ? (int)$_POST['month'] : $currentMonth;
$selectedYear = isset($_POST['year']) ? (int)$_POST['year'] : $currentYear;
$reportData = [];
$totals = [];
$error = '';
$success_message = '';

// Fetch report data if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "SELECT * FROM monthly_reports WHERE report_month = ? AND report_year = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $error = 'Error preparing query: ' . htmlspecialchars($conn->error);
    } else {
        $stmt->bind_param("ii", $selectedMonth, $selectedYear);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $report = $result->fetch_assoc();
            $reportData = json_decode($report['report_data'], true);
            $totals = [
                'total_items' => $report['total_items'],
                'total_stock_value' => $report['total_stock_value'],
                'total_shortage_value' => $report['total_shortage_value'],
                'shortage_items' => $report['shortage_items']
            ];
            $success_message = 'Report loaded for ' . date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) . '.';
        } else {
            $error = 'No reports found for the selected month and year.';
        }
        $stmt->close();
    }
}

$conn->close();

// Define headers for the report_data table in the specified order
$reportDataHeaders = [
    'item_name' => ['label' => 'Items', 'width' => '15%'],
    'unit_type' => ['label' => 'Unit', 'width' => '6%'],
    'unit_price' => ['label' => 'Price (LKR)', 'width' => '8%'],
    'last_audit_date' => ['label' => 'Last Audit', 'width' => '8%'],
    'last_audit_qty' => ['label' => 'Last Qty', 'width' => '6%'],
    'new_received' => ['label' => 'Received', 'width' => '6%'],
    'total_issue' => ['label' => 'Issued', 'width' => '6%'],
    'balance' => ['label' => 'Balance', 'width' => '6%'],
    'present_audit_date' => ['label' => 'Current Audit', 'width' => '8%'],
    'present_audit_qty' => ['label' => 'Current Qty', 'width' => '6%'],
    'short' => ['label' => 'Short', 'width' => '6%'],
    'shorted_price' => ['label' => 'Shortage (LKR)', 'width' => '8%']
];

// Quantity fields to format without decimals if whole numbers
$quantityFields = ['last_audit_qty', 'new_received', 'total_issue', 'balance', 'present_audit_qty', 'short'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Logistics Stock Report</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .container-fluid {
            max-width: 100%;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin: 0 auto;
        }

        .form-container {
            padding: 20px 30px;
            background: #2c3e50;
            color: white;
            border-bottom: 4px solid #3498db;
        }

        .form-container h2 {
            margin: 0 0 20px 0;
            font-size: 24px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            min-width: 140px;
        }

        .form-group label {
            margin-bottom: 5px;
            font-weight: 500;
            font-size: 14px;
        }

        select, button {
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        select {
            background: #ecf0f1;
            color: #2c3e50;
        }

        select:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.3);
        }

        button {
            background: #3498db;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            white-space: nowrap;
        }

        button:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .print-button {
            background: #2ecc71 !important;
        }

        .print-button:hover {
            background: #27ae60 !important;
        }

        .totals {
            padding: 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
        }

        .totals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .total-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            background: white;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }

        .currency-lkr, .shortage-negative, .shortage-positive {
            font-weight: 600;
            white-space: nowrap;
        }

        .currency-lkr { color: #059669; }
        .shortage-negative { color: #dc2626; }
        .shortage-positive { color: #059669; }
        .received { color: #059669; font-weight: 600; }
        .issued { color: #dc2626; font-weight: 600; }
        .balance { color: #3498db; font-weight: 600; }

        /* Table Container for Horizontal Scrolling */
        .table-container {
            width: 100%;
            overflow-x: auto;
            margin: 0;
            padding: 0;
        }

        .report-table {
            width: 100%;
            min-width: 1200px; /* Minimum width to prevent crushing */
            border-collapse: collapse;
            background: white;
            table-layout: fixed;
            margin: 0;
        }

        .report-table th,
        .report-table td {
            padding: 8px 6px;
            text-align: left;
            border: 1px solid #e0e0e0;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
            font-size: 12px;
            line-height: 1.4;
        }

        .report-table th {
            background: #6b7280;
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.05em;
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
        }

        .report-table tbody tr:hover {
            background: #f8f9fa;
        }

        .report-table td {
            color: #2c3e50;
        }

        /* Column Specific Styling */
        .report-table th:nth-child(1), .report-table td:nth-child(1) { width: 15%; }
        .report-table th:nth-child(2), .report-table td:nth-child(2) { width: 6%; }
        .report-table th:nth-child(3), .report-table td:nth-child(3) { width: 8%; }
        .report-table th:nth-child(4), .report-table td:nth-child(4) { width: 8%; }
        .report-table th:nth-child(5), .report-table td:nth-child(5) { width: 6%; }
        .report-table th:nth-child(6), .report-table td:nth-child(6) { width: 6%; }
        .report-table th:nth-child(7), .report-table td:nth-child(7) { width: 6%; }
        .report-table th:nth-child(8), .report-table td:nth-child(8) { width: 6%; }
        .report-table th:nth-child(9), .report-table td:nth-child(9) { width: 8%; }
        .report-table th:nth-child(10), .report-table td:nth-child(10) { width: 6%; }
        .report-table th:nth-child(11), .report-table td:nth-child(11) { width: 6%; }
        .report-table th:nth-child(12), .report-table td:nth-child(12) { width: 8%; }

        /* Numeric columns right align */
        .report-table th:nth-child(n+3),
        .report-table td:nth-child(n+3) {
            text-align: right;
        }

        /* Item name and unit left align */
        .report-table th:nth-child(-n+2),
        .report-table td:nth-child(-n+2) {
            text-align: left;
        }

        .report-table tfoot td {
            font-weight: bold;
            background: #f8f9fa;
            border-top: 2px solid #6b7280;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #e74c3c;
            font-size: 18px;
            font-weight: 600;
        }

        .report-footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #6b7280;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body { padding: 10px; }
            
            .form-container {
                padding: 15px 20px;
            }
            
            .form-container h2 {
                font-size: 20px;
                margin-bottom: 15px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 10px;
            }
            
            .form-group {
                min-width: 100%;
            }
            
            select, button {
                width: 100%;
                padding: 12px;
                font-size: 16px; /* Prevent zoom on iOS */
            }
            
            .totals { padding: 15px; }
            
            .totals-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .total-item {
                padding: 8px 12px;
                font-size: 14px;
            }
        }

        @media (max-width: 576px) {
            .report-table th,
            .report-table td {
                padding: 6px 4px;
                font-size: 10px;
            }
            
            .report-table th {
                font-size: 9px;
            }
        }

        /* Print Styles */
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            body {
                background: white !important;
                padding: 5mm !important;
                font-size: 8pt !important;
                margin: 0 !important;
            }

            .container-fluid {
                box-shadow: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }

            .form-container,
            .no-print,
            .alert {
                display: none !important;
            }

            .totals {
                padding: 3mm !important;
                font-size: 7pt !important;
                border: 1px solid #000 !important;
                margin-bottom: 2mm !important;
                background: #f5f5f5 !important;
            }

            .totals-grid {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 2mm !important;
            }

            .total-item {
                padding: 1mm !important;
                border: 1px solid #ccc !important;
                border-radius: 0 !important;
                background: white !important;
                border-left: 2pt solid #000 !important;
            }

            .table-container {
                overflow: visible !important;
            }

            .report-table {
                width: 100% !important;
                min-width: 100% !important;
                margin: 0 !important;
                page-break-inside: avoid !important;
                border-collapse: collapse !important;
            }

            .report-table th,
            .report-table td {
                border: 0.5pt solid #000 !important;
                font-size: 6pt !important;
                padding: 1mm !important;
                line-height: 1.2 !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
                hyphens: auto !important;
            }

            .report-table th {
                background: #d0d0d0 !important;
                color: #000 !important;
                font-weight: bold !important;
                font-size: 5pt !important;
                position: static !important;
            }

            .report-table tbody tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .report-table tfoot td {
                background: #e0e0e0 !important;
                border-top: 1pt solid #000 !important;
                font-weight: bold !important;
            }

            .currency-lkr,
            .shortage-negative,
            .shortage-positive,
            .received,
            .issued,
            .balance {
                color: inherit !important;
                font-weight: bold !important;
            }

            .report-footer {
                font-size: 6pt !important;
                margin-top: 3mm !important;
                page-break-inside: avoid !important;
            }

            /* Ensure table doesn't break across pages */
            .report-table {
                page-break-before: auto !important;
                page-break-after: auto !important;
                page-break-inside: avoid !important;
            }
            
            /* Prevent breaking in the middle of rows */
            .report-table tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }

        /* Large screen optimization */
        @media (min-width: 1400px) {
            .container-fluid {
                max-width: 1350px;
            }
            
            .report-table th,
            .report-table td {
                font-size: 13px;
                padding: 10px 8px;
            }
            
            .report-table th {
                font-size: 11px;
            }
        }
    </style>
    <script>
        function printReport() {
            // Hide any open dropdowns or modals before printing
            var dropdowns = document.querySelectorAll('.dropdown-menu.show');
            dropdowns.forEach(function(dropdown) {
                dropdown.classList.remove('show');
            });
            
            setTimeout(function() {
                window.print();
            }, 100);
        }
        
        // Ensure responsive behavior on window resize
        window.addEventListener('resize', function() {
            // Force table redraw on mobile devices
            var table = document.querySelector('.report-table');
            if (table && window.innerWidth <= 768) {
                table.style.minWidth = '1200px';
            }
        });
    </script>
</head>
<body>
    
    <div class="container-fluid">
        <style>
@media print {
  .no-print {
    display: none !important;
  }
}
</style>

<button onclick="window.location.href='../accounts.php'" 
        class="no-print"
        style="background-color: #f09424ff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
    Back
</button>

        <div class="form-container no-print">
            
            <h2><i class="fas fa-warehouse"></i> Monthly Stock Report</h2>
            <form id="reportForm" method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="month">Select Month:</label>
                        <select name="month" id="month">
                            <?php
                            $months = [
                                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                            ];
                            foreach ($months as $num => $name) {
                                $selected = ($num == $selectedMonth) ? 'selected' : '';
                                echo "<option value='$num' $selected>$name</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="year">Select Year:</label>
                        <select name="year" id="year">
                            <?php
                            for ($year = $currentYear - 5; $year <= $currentYear + 5; $year++) {
                                $selected = ($year == $selectedYear) ? 'selected' : '';
                                echo "<option value='$year' $selected>$year</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <button type="submit">Generate Report</button>
                    </div>
                    
                    <?php if (!empty($reportData)): ?>
                    <div class="form-group">
                        <button type="button" class="print-button" onclick="printReport()">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Alerts -->
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show no-print" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show no-print" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($reportData)): ?>
            <!-- Totals -->
            <div class="totals">
                <div class="totals-grid">
                    <div class="total-item">
                        <span><strong>Total Items:</strong></span>
                        <span><?php echo htmlspecialchars($totals['total_items']); ?></span>
                    </div>
                    <div class="total-item">
                        <span><strong>Total Stock Value:</strong></span>
                        <span class="currency-lkr"><?php echo formatCurrency($totals['total_stock_value']); ?></span>
                    </div>
                    <div class="total-item">
                        <span><strong>Total Shortage Value:</strong></span>
                        <span class="shortage-negative"><?php echo formatCurrency($totals['total_shortage_value']); ?></span>
                    </div>
                    <div class="total-item">
                        <span><strong>Shortage Items:</strong></span>
                        <span><?php echo htmlspecialchars($totals['shortage_items']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Report Table -->
            <div class="table-container">
                <table class="report-table">
                    <thead>
                        <tr>
                            <?php foreach ($reportDataHeaders as $key => $header): ?>
                                <th><?php echo htmlspecialchars($header['label']); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData as $item): ?>
                            <tr>
                                <?php foreach ($reportDataHeaders as $key => $header): ?>
                                    <td>
                                        <?php
                                        $value = $item[$key] ?? 'N/A';
                                        if ($key === 'unit_price' || $key === 'shorted_price') {
                                            echo '<span class="' . ($key === 'shorted_price' && $value < 0 ? 'shortage-negative' : 'currency-lkr') . '">' . formatCurrency($value) . '</span>';
                                        } elseif ($key === 'short') {
                                            echo '<span class="' . ($value < 0 ? 'shortage-negative' : 'shortage-positive') . '">' . (in_array($key, $quantityFields) && is_numeric($value) && floor($value) == $value ? (int)$value : number_format(floatval($value), 2)) . '</span>';
                                        } elseif ($key === 'new_received') {
                                            echo '<span class="received">' . (in_array($key, $quantityFields) && is_numeric($value) && floor($value) == $value ? (int)$value : number_format(floatval($value), 2)) . '</span>';
                                        } elseif ($key === 'total_issue') {
                                            echo '<span class="issued">' . (in_array($key, $quantityFields) && is_numeric($value) && floor($value) == $value ? (int)$value : number_format(floatval($value), 2)) . '</span>';
                                        } elseif ($key === 'balance') {
                                            echo '<span class="balance">' . (in_array($key, $quantityFields) && is_numeric($value) && floor($value) == $value ? (int)$value : number_format(floatval($value), 2)) . '</span>';
                                        } elseif (in_array($key, $quantityFields) && is_numeric($value)) {
                                            echo htmlspecialchars(floor($value) == $value ? (int)$value : number_format($value, 2));
                                        } else {
                                            echo htmlspecialchars($value);
                                        }
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="10" style="text-align: right; font-weight: bold;">TOTAL SHORTAGE:</td>
                            <td style="text-align: right;" class="shortage-negative"><?php echo htmlspecialchars($totals['shortage_items']); ?> items</td>
                            <td style="text-align: right;" class="shortage-negative"><?php echo formatCurrency($totals['total_shortage_value']); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Footer -->
            <div class="report-footer">
                <p><strong>Generated on <?php echo date('d/m/Y H:i:s'); ?> Logistics Management System</strong></p>
                <p>Report for <?php echo date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)); ?></p>
            </div>
        <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
            <p class="no-data"><i class="fas fa-exclamation-circle"></i> No reports found for the selected month and year.</p>
        <?php else: ?>
            <p class="no-data"><i class="fas fa-info-circle"></i> Please select a month and year to generate a report.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>