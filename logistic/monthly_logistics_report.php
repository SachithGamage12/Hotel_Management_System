<?php
// Start session
session_start();

// Set timezone to Asia/Colombo
date_default_timezone_set('Asia/Colombo');

// Database Connection
$conn = new mysqli('localhost', 'hotelgrandguardi_root', 'Sun123flower@', 'hotelgrandguardi_wedding_bliss');
if ($conn->connect_error) {
    die('<div class="alert alert-danger">Connection failed: ' . htmlspecialchars($conn->connect_error) . '</div>');
}
$conn->set_charset("utf8mb4");

// Function to format currency in LKR
function formatCurrency($amount) {
    return 'Rs. ' . number_format($amount, 2);
}

// Function to convert quantity between units
function convertQuantity($quantity, $fromUnit, $toUnit) {
    if ($fromUnit === $toUnit) {
        return $quantity;
    }

    $quantity = floatval($quantity);
    $conversionTable = [
        'mg' => ['base' => 'g', 'factor' => 0.001],
        'g' => ['base' => 'g', 'factor' => 1],
        'kg' => ['base' => 'g', 'factor' => 1000],
        't' => ['base' => 'g', 'factor' => 1000000],
        'mL' => ['base' => 'L', 'factor' => 0.001],
        'L' => ['base' => 'L', 'factor' => 1],
        'm³' => ['base' => 'L', 'factor' => 1000],
        'mm' => ['base' => 'm', 'factor' => 0.001],
        'cm' => ['base' => 'm', 'factor' => 0.01],
        'm' => ['base' => 'm', 'factor' => 1],
        'km' => ['base' => 'm', 'factor' => 1000],
        'pcs' => ['base' => 'pcs', 'factor' => 1],
        'doz' => ['base' => 'pcs', 'factor' => 12],
        'unit' => ['base' => 'pcs', 'factor' => 1]
    ];

    if (!isset($conversionTable[$fromUnit]) || !isset($conversionTable[$toUnit])) {
        error_log("Invalid unit conversion: from $fromUnit to $toUnit");
        return $quantity;
    }

    if ($conversionTable[$fromUnit]['base'] !== $conversionTable[$toUnit]['base']) {
        error_log("Incompatible unit types: $fromUnit and $toUnit");
        return $quantity;
    }

    $baseQuantity = $quantity * $conversionTable[$fromUnit]['factor'];
    $result = $baseQuantity / $conversionTable[$toUnit]['factor'];
    return $result;
}

// Function to convert unit price between units
function convertUnitPrice($price, $fromUnit, $toUnit) {
    if ($fromUnit === $toUnit) {
        return $price;
    }

    $price = floatval($price);
    $conversionTable = [
        'mg' => ['base' => 'g', 'factor' => 0.001],
        'g' => ['base' => 'g', 'factor' => 1],
        'kg' => ['base' => 'g', 'factor' => 1000],
        't' => ['base' => 'g', 'factor' => 1000000],
        'mL' => ['base' => 'L', 'factor' => 0.001],
        'L' => ['base' => 'L', 'factor' => 1],
        'm³' => ['base' => 'L', 'factor' => 1000],
        'mm' => ['base' => 'm', 'factor' => 0.001],
        'cm' => ['base' => 'm', 'factor' => 0.01],
        'm' => ['base' => 'm', 'factor' => 1],
        'km' => ['base' => 'm', 'factor' => 1000],
        'pcs' => ['base' => 'pcs', 'factor' => 1],
        'doz' => ['base' => 'pcs', 'factor' => 12],
        'unit' => ['base' => 'pcs', 'factor' => 1]
    ];

    if (!isset($conversionTable[$fromUnit]) || !isset($conversionTable[$toUnit])) {
        error_log("Invalid unit price conversion: from $fromUnit to $toUnit");
        return $price;
    }

    if ($conversionTable[$fromUnit]['base'] !== $conversionTable[$toUnit]['base']) {
        error_log("Incompatible unit types for price: $fromUnit and $toUnit");
        return $price;
    }

    $factor_from = $conversionTable[$fromUnit]['factor'];
    $factor_to = $conversionTable[$toUnit]['factor'];
    return $price * ($factor_to / $factor_from);
}

// Fetch the most recent audit date to set default month and year
$latest_date_query = $conn->query("SELECT MAX(audit_date) as latest_date FROM inventory_audits");
$latest_date_row = $latest_date_query->fetch_assoc();
$latest_date = $latest_date_row['latest_date'] ?? date('Y-m-d');
$selected_month = isset($_POST['month']) ? (int)$_POST['month'] : (int)date('n', strtotime($latest_date));
$selected_year = isset($_POST['year']) ? (int)$_POST['year'] : (int)date('Y', strtotime($latest_date));
$latest_date_query->free();

// Handle form submission
$report_data = [];
$totals = [];
$error = '';
$success_message = '';
$start_date = "$selected_year-$selected_month-01";
$end_date = date('Y-m-t', strtotime($start_date));

// Check if report exists in the database
$stmt_check = $conn->prepare("SELECT * FROM monthly_reports WHERE report_month = ? AND report_year = ?");
$stmt_check->bind_param("ii", $selected_month, $selected_year);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Fetch existing report
    $report = $result_check->fetch_assoc();
    $report_data = json_decode($report['report_data'], true);
    $totals = [
        'total_items' => $report['total_items'],
        'total_stock_value' => $report['total_stock_value'],
        'total_shortage_value' => $report['total_shortage_value'],
        'shortage_items' => $report['shortage_items']
    ];
    $success_message = 'Loaded existing report for ' . date('F Y', mktime(0, 0, 0, $selected_month, 1, $selected_year)) . '.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['generate_report']) || isset($_POST['print_report']))) {
    // Generate new report
    if ($selected_month < 1 || $selected_month > 12 || $selected_year < 2000 || $selected_year > 2100) {
        $error = 'Invalid month or year selected.';
    } else {
        // Fetch all items
        $stmt_items = $conn->prepare("SELECT id, name, unit_type FROM items ORDER BY name");
        if (!$stmt_items) {
            $error = 'Error preparing items query: ' . htmlspecialchars($conn->error);
        } else {
            $stmt_items->execute();
            $items_result = $stmt_items->get_result();
            while ($item = $items_result->fetch_assoc()) {
                $item_id = $item['id'];
                $base_unit = $item['unit_type'];

                $data = [
                    'item_name' => $item['name'],
                    'unit_type' => $item['unit_type'],
                    'unit_price' => 0,
                    'last_audit_date' => 'N/A',
                    'last_audit_qty' => 0,
                    'new_received' => 0,
                    'total_issue' => 0,
                    'balance' => 0,
                    'present_audit_date' => 'N/A',
                    'present_audit_qty' => 0,
                    'short' => 0,
                    'shorted_price' => 0
                ];

                // Fetch latest unit price
                $stmt_price = $conn->prepare("
                    SELECT unit_price, unit
                    FROM purchases
                    WHERE item_id = ?
                    ORDER BY purchased_date DESC
                    LIMIT 1
                ");
                if ($stmt_price) {
                    $stmt_price->bind_param("i", $item_id);
                    $stmt_price->execute();
                    $price_result = $stmt_price->get_result();
                    if ($price_row = $price_result->fetch_assoc()) {
                        $data['unit_price'] = convertUnitPrice($price_row['unit_price'], $price_row['unit'], $base_unit);
                    }
                    $stmt_price->close();
                    $price_result->free();
                }

                // Fetch last and present audit dates
                $stmt_audits = $conn->prepare("
                    SELECT audit_date, quantity_at_audit
                    FROM inventory_audits
                    WHERE item_id = ?
                    ORDER BY audit_date DESC
                    LIMIT 2
                ");
                if ($stmt_audits) {
                    $stmt_audits->bind_param("i", $item_id);
                    $stmt_audits->execute();
                    $audits_result = $stmt_audits->get_result();
                    $audits = $audits_result->fetch_all(MYSQLI_ASSOC);
                    if (count($audits) >= 1) {
                        $data['present_audit_date'] = $audits[0]['audit_date'];
                        $data['present_audit_qty'] = $audits[0]['quantity_at_audit'];
                        if (count($audits) == 2) {
                            $data['last_audit_date'] = $audits[1]['audit_date'];
                            $data['last_audit_qty'] = $audits[1]['quantity_at_audit'];
                        }
                    }
                    $stmt_audits->close();
                    $audits_result->free();
                }

                // Define date filters
                $last_date = $data['last_audit_date'] !== 'N/A' ? $data['last_audit_date'] : '0000-01-01';
                $present_date = $data['present_audit_date'] !== 'N/A' ? $data['present_audit_date'] : '9999-12-31';
                $month_start = $start_date;
                $month_end = $end_date;

                $last_end = $last_date . ' 23:59:59';
                $present_start = $present_date . ' 00:00:00';
                $month_start_time = $month_start . ' 00:00:00';
                $month_end_time = $month_end . ' 23:59:59';

                // Fetch new received from stock
                $stmt_stock = $conn->prepare("
                    SELECT SUM(quantity) as total_received, unit as unit_type
                    FROM stock
                    WHERE item_id = ?
                    AND `date` > ?
                    AND `date` < ?
                    AND `date` >= ?
                    AND `date` <= ?
                    GROUP BY unit
                ");
                if ($stmt_stock) {
                    $stmt_stock->bind_param("issss", $item_id, $last_date, $present_date, $month_start, $month_end);
                    $stmt_stock->execute();
                    $stock_result = $stmt_stock->get_result();
                    while ($stock_row = $stock_result->fetch_assoc()) {
                        $data['new_received'] += convertQuantity($stock_row['total_received'], $stock_row['unit_type'], $base_unit);
                    }
                    $stmt_stock->close();
                    $stock_result->free();
                }

                // Fetch new received from stock_additions
                $stmt_additions = $conn->prepare("
                    SELECT SUM(quantity) as total_received, unit as unit_type
                    FROM stock_additions
                    WHERE item_id = ?
                    AND added_date > ?
                    AND added_date < ?
                    AND added_date >= ?
                    AND added_date <= ?
                    GROUP BY unit
                ");
                if ($stmt_additions) {
                    $stmt_additions->bind_param("issss", $item_id, $last_end, $present_start, $month_start_time, $month_end_time);
                    $stmt_additions->execute();
                    $additions_result = $stmt_additions->get_result();
                    while ($additions_row = $additions_result->fetch_assoc()) {
                        $data['new_received'] += convertQuantity($additions_row['total_received'], $additions_row['unit_type'], $base_unit);
                    }
                    $stmt_additions->close();
                    $additions_result->free();
                }

                // Fetch total issued
                $stmt_issued = $conn->prepare("
                    SELECT SUM(ri.issued_quantity) as total_issued, ri.unit_type
                    FROM request_items ri
                    JOIN item_requests ir ON ri.request_id = ir.id
                    WHERE ri.item_id = ?
                    AND ir.issued_date > ?
                    AND ir.issued_date < ?
                    AND ir.issued_date >= ?
                    AND ir.issued_date <= ?
                    GROUP BY ri.unit_type
                ");
                if ($stmt_issued) {
                    $stmt_issued->bind_param("issss", $item_id, $last_end, $present_start, $month_start_time, $month_end_time);
                    $stmt_issued->execute();
                    $issued_result = $stmt_issued->get_result();
                    while ($issued_row = $issued_result->fetch_assoc()) {
                        $data['total_issue'] += convertQuantity($issued_row['total_issued'], $issued_row['unit_type'], $base_unit);
                    }
                    $stmt_issued->close();
                    $issued_result->free();
                }

                // Calculate balance and short
                $data['balance'] = $data['last_audit_qty'] + $data['new_received'] - $data['total_issue'];
                $data['short'] = $data['balance'] - $data['present_audit_qty'];
                $data['shorted_price'] = $data['unit_price'] * $data['short'];

                $report_data[] = $data;
            }
            $stmt_items->close();
            $items_result->free();

            // Calculate totals
            $total_items = count($report_data);
            $total_shortage_value = 0;
            $total_stock_value = 0;
            $shortage_items = 0;
            foreach ($report_data as $data) {
                $total_stock_value += $data['unit_price'] * $data['present_audit_qty'];
                if ($data['short'] < 0) {
                    $shortage_items++;
                    $total_shortage_value += -$data['shorted_price'];
                }
            }

            // Save to monthly_reports
            $generated_at = date('Y-m-d H:i:s');
            $report_json = json_encode($report_data);
            $stmt = $conn->prepare("
                INSERT INTO monthly_reports (
                    generated_at, report_month, report_year, total_items, 
                    total_stock_value, total_shortage_value, shortage_items, report_data
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if ($stmt) {
                $stmt->bind_param(
                    "siiiddis",
                    $generated_at,
                    $selected_month,
                    $selected_year,
                    $total_items,
                    $total_stock_value,
                    $total_shortage_value,
                    $shortage_items,
                    $report_json
                );
                if ($stmt->execute()) {
                    $success_message = 'Report generated and saved successfully for ' . date('F Y', mktime(0, 0, 0, $selected_month, 1, $selected_year)) . '.';
                    $totals = [
                        'total_items' => $total_items,
                        'total_stock_value' => $total_stock_value,
                        'total_shortage_value' => $total_shortage_value,
                        'shortage_items' => $shortage_items
                    ];
                } else {
                    $error = 'Error saving report: ' . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            } else {
                $error = 'Error preparing insert statement: ' . htmlspecialchars($conn->error);
            }
        }
    }
}
$stmt_check->close();
$result_check->free();

// Generate year options
$year_options = [];
$current_year = date('Y');
for ($y = $current_year - 10; $y <= $current_year + 10; $y++) {
    $year_options[] = $y;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Logistics Stock Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 1400px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .currency-lkr {
            color: #059669;
            font-weight: 600;
        }
        .shortage-negative {
            color: #dc2626;
            font-weight: bold;
        }
        .shortage-positive {
            color: #059669;
            font-weight: bold;
        }
        .table-container {
            overflow-x: auto;
        }
        .table {
            margin-top: 20px;
        }
        .totals {
            margin-top: 20px;
            font-weight: bold;
        }
        .print-btn {
            margin-top: 20px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .container {
                box-shadow: none;
                padding: 0;
            }
            .table {
                font-size: 10px;
                width: 100%;
                break-inside: avoid;
            }
            th, td {
                border: 1px solid #000;
                padding: 4px;
            }
            .currency-lkr, .shortage-negative, .shortage-positive {
                -webkit-print-color-adjust: exact;
            }
            .report-footer {
                margin-top: 10px;
                font-size: 8pt;
                text-align: center;
            }
        }
        @media screen and (max-width: 768px) {
            .table thead {
                display: none;
            }
            .table tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #ddd;
                padding: 0.5rem;
                background-color: #fff;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .table td {
                display: flex;
                justify-content: space-between;
                text-align: right;
                border: none;
                border-bottom: 1px solid #eee;
                padding: 0.5rem 1rem;
            }
            .table td::before {
                content: attr(data-label);
                font-weight: bold;
                text-transform: uppercase;
                color: #666;
                flex: 1;
                text-align: left;
            }
            .table td:last-child {
                border-bottom: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <style>
@media print {
  .no-print {
    display: none !important;
  }
}
</style>

<button onclick="window.location.href='../logistic.php'" 
        class="no-print"
        style="background-color: #f09424ff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
    Back
</button>

        <div class="gradient-bg text-white rounded-lg shadow-lg p-6 mb-6 no-print">
            <h1 class="text-center text-3xl font-bold"><i class="fas fa-warehouse mr-2"></i>Monthly Stock Report</h1>
        </div>

        <!-- Alerts -->
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show no-print" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i><?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show no-print" role="alert">
                <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" class="no-print mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="month" class="form-label"><i class="fas fa-calendar mr-1"></i>Month</label>
                    <select name="month" id="month" class="form-select">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>" <?php echo $m == $selected_month ? 'selected' : ''; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="year" class="form-label"><i class="fas fa-calendar-year mr-1"></i>Year</label>
                    <select name="year" id="year" class="form-select">
                        <?php foreach ($year_options as $year): ?>
                            <option value="<?php echo $year; ?>" <?php echo $year == $selected_year ? 'selected' : ''; ?>>
                                <?php echo $year; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" name="generate_report" class="btn btn-primary w-100">
                        <i class="fas fa-chart-line mr-2"></i>Generate Report
                    </button>
                </div>
            </div>
        </form>

        <?php if (!empty($report_data)): ?>
            <!-- Totals -->
            <div class="totals card p-4 mb-4">
                <div class="row">
                    <div class="col-md-3"><strong>Total Items:</strong> <?php echo $totals['total_items']; ?></div>
                    <div class="col-md-3"><strong>Total Stock Value:</strong> <?php echo formatCurrency($totals['total_stock_value']); ?></div>
                    <div class="col-md-3"><strong>Total Shortage Value:</strong> <span class="shortage-negative"><?php echo formatCurrency($totals['total_shortage_value']); ?></span></div>
                    <div class="col-md-3"><strong>Shortage Items:</strong> <?php echo $totals['shortage_items']; ?></div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-container">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Items</th>
                            <th>Unit</th>
                            <th>Price (LKR)</th>
                            <th>Last Audit</th>
                            <th>Last Qty</th>
                            <th>Received</th>
                            <th>Issued</th>
                            <th>Balance</th>
                            <th>Current Audit</th>
                            <th>Current Qty</th>
                            <th>Short</th>
                            <th>Shortage (LKR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report_data as $index => $data): ?>
                            <tr class="<?php echo $index % 2 == 0 ? 'bg-light' : ''; ?>">
                                <td data-label="Items"><?php echo htmlspecialchars($data['item_name']); ?></td>
                                <td data-label="Unit"><?php echo htmlspecialchars($data['unit_type']); ?></td>
                                <td data-label="Price (LKR)" class="currency-lkr"><?php echo formatCurrency($data['unit_price']); ?></td>
                                <td data-label="Last Audit"><?php echo htmlspecialchars($data['last_audit_date']); ?></td>
                                <td data-label="Last Qty" class="text-end"><?php echo number_format($data['last_audit_qty'], 2); ?></td>
                                <td data-label="Received" class="text-end text-success"><?php echo number_format($data['new_received'], 2); ?></td>
                                <td data-label="Issued" class="text-end text-danger"><?php echo number_format($data['total_issue'], 2); ?></td>
                                <td data-label="Balance" class="text-end text-primary"><?php echo number_format($data['balance'], 2); ?></td>
                                <td data-label="Current Audit"><?php echo htmlspecialchars($data['present_audit_date']); ?></td>
                                <td data-label="Current Qty" class="text-end"><?php echo number_format($data['present_audit_qty'], 2); ?></td>
                                <td data-label="Short" class="text-end <?php echo $data['short'] < 0 ? 'shortage-negative' : 'shortage-positive'; ?>">
                                    <?php echo number_format($data['short'], 2); ?>
                                </td>
                                <td data-label="Shortage (LKR)" class="text-end <?php echo $data['shorted_price'] < 0 ? 'shortage-negative' : 'shortage-positive'; ?>">
                                    <?php echo formatCurrency($data['shorted_price']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <td colspan="10" class="text-end font-bold">TOTAL SHORTAGE VALUE:</td>
                            <td class="text-end font-bold shortage-negative"><?php echo $totals['shortage_items']; ?> items</td>
                            <td class="text-end font-bold shortage-negative"><?php echo formatCurrency($totals['total_shortage_value']); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Print Button -->
            <form method="POST" class="no-print">
                <input type="hidden" name="month" value="<?php echo $selected_month; ?>">
                <input type="hidden" name="year" value="<?php echo $selected_year; ?>">
                <input type="hidden" name="print_report" value="1">
                <button type="submit" name="print_report" class="btn btn-success print-btn">
                    <i class="fas fa-print mr-2"></i>Print Report
                </button>
            </form>

            <!-- Footer -->
            <div class="report-footer">
                <p>Generated on <?php echo date('d/m/Y H:i:s'); ?> Logistics Management System</p>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center no-print">
                <i class="fas fa-info-circle mr-2"></i>Please select a month and year, then click "Generate Report" to view the logistics data.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (isset($_POST['print_report'])): ?>
        <script>window.print();</script>
    <?php endif; ?>
</body>
</html>