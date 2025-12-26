<?php
// Database configuration
$host = 'localhost';
$dbname = 'hotelgrandguardi_wedding_bliss';
$username = 'hotelgrandguardi_root';
$password = 'Sun123flower@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get filter parameters
$report_type = $_GET['report_type'] ?? 'daily';
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$status_filter = $_GET['status'] ?? 'all';
$function_type = $_GET['function_type'] ?? 'all';
$order_sheet_no = $_GET['order_sheet_no'] ?? '';
$export = $_GET['export'] ?? '';

// Set date ranges based on report type
switch($report_type) {
    case 'daily':
        if (!$start_date) $start_date = date('Y-m-d');
        $end_date = $start_date;
        break;
    case 'monthly':
        if (!$start_date) $start_date = date('Y-m-01');
        if (!$end_date) $end_date = date('Y-m-t');
        break;
    case 'yearly':
        if (!$start_date) $start_date = date('Y-01-01');
        if (!$end_date) $end_date = date('Y-12-31');
        break;
}

// Build query
$where_conditions = ["DATE(os.request_date) BETWEEN :start_date AND :end_date"];
$params = ['start_date' => $start_date, 'end_date' => $end_date];

if ($status_filter !== 'all') {
    $where_conditions[] = "os.status = :status";
    $params['status'] = $status_filter;
}

if ($function_type !== 'all') {
    $where_conditions[] = "os.function_type = :function_type";
    $params['function_type'] = $function_type;
}

if (!empty($order_sheet_no)) {
    $where_conditions[] = "os.order_sheet_no LIKE :order_sheet_no";
    $params['order_sheet_no'] = '%' . $order_sheet_no . '%';
}

$where_clause = implode(' AND ', $where_conditions);

// Main query for order details
$query = "
    SELECT 
        os.*,
        i.item_name,
        i.unit,
        r.name as responsible_name,
        CASE 
            WHEN os.issued_qty IS NULL THEN 0 
            ELSE os.issued_qty 
        END as actual_issued_qty
    FROM hggorder_sheet os
    LEFT JOIN inventory i ON os.item_id = i.id
    LEFT JOIN responsible r ON os.responsible_id = r.id
    WHERE $where_clause
    ORDER BY os.request_date DESC, os.order_sheet_no DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Summary statistics query
$summary_query = "
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'issued' THEN 1 ELSE 0 END) as issued_orders,
        SUM(requested_qty) as total_requested_qty,
        SUM(CASE WHEN issued_qty IS NOT NULL THEN issued_qty ELSE 0 END) as total_issued_qty,
        COUNT(DISTINCT order_sheet_no) as unique_order_sheets,
        COUNT(DISTINCT item_id) as unique_items,
        COUNT(DISTINCT function_type) as function_types_count
    FROM hggorder_sheet os
    WHERE $where_clause
";

$summary_stmt = $pdo->prepare($summary_query);
$summary_stmt->execute($params);
$summary = $summary_stmt->fetch(PDO::FETCH_ASSOC);

// Function type breakdown
$function_breakdown_query = "
    SELECT 
        function_type,
        COUNT(*) as order_count,
        SUM(requested_qty) as total_requested,
        SUM(CASE WHEN issued_qty IS NOT NULL THEN issued_qty ELSE 0 END) as total_issued
    FROM hggorder_sheet os
    WHERE $where_clause
    GROUP BY function_type
    ORDER BY order_count DESC
";

$function_stmt = $pdo->prepare($function_breakdown_query);
$function_stmt->execute($params);
$function_breakdown = $function_stmt->fetchAll(PDO::FETCH_ASSOC);

// Daily trend (for monthly/yearly reports)
$daily_trend = [];
if ($report_type !== 'daily') {
    $trend_query = "
        SELECT 
            DATE(request_date) as order_date,
            COUNT(*) as daily_orders,
            SUM(requested_qty) as daily_requested,
            SUM(CASE WHEN issued_qty IS NOT NULL THEN issued_qty ELSE 0 END) as daily_issued
        FROM hggorder_sheet os
        WHERE $where_clause
        GROUP BY DATE(request_date)
        ORDER BY order_date
    ";
    
    $trend_stmt = $pdo->prepare($trend_query);
    $trend_stmt->execute($params);
    $daily_trend = $trend_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get function types for filter
$function_types_query = "SELECT DISTINCT function_type FROM hggorder_sheet WHERE function_type != '' ORDER BY function_type";
$function_types_stmt = $pdo->query($function_types_query);
$function_types = $function_types_stmt->fetchAll(PDO::FETCH_COLUMN);

// Export functionality
if ($export === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="hgg_kitchen_order_report_' . $start_date . '_to_' . $end_date . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // CSV Headers
    fputcsv($output, [
        'Order ID', 'Order Sheet No', 'Item Name', 'Requested Qty', 'Issued Qty', 
        'Unit', 'Status', 'Function Type', 'Function Date', 'Day/Night', 
        'Request Date', 'Issued Date', 'Responsible Person'
    ]);
    
    // CSV Data
    foreach ($orders as $order) {
        fputcsv($output, [
            $order['order_id'],
            $order['order_sheet_no'],
            $order['item_name'],
            $order['requested_qty'],
            $order['actual_issued_qty'],
            $order['unit'],
            ucfirst($order['status']),
            $order['function_type'],
            $order['function_date'],
            $order['day_night'],
            $order['request_date'],
            $order['issued_date'],
            $order['responsible_name']
        ]);
    }
    
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HGG Restaurant Kitchen Order Sheet Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: end;
            margin-bottom: 15px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            font-weight: bold;
            color: #555;
        }

        .filter-group select,
        .filter-group input {
            padding: 8px 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5a67d8;
        }

        .btn-success {
            background: #48bb78;
            color: white;
        }

        .btn-success:hover {
            background: #38a169;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .summary-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .summary-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .summary-card .value {
            font-size: 2em;
            font-weight: bold;
            color: #333;
        }

        .summary-card.pending .value {
            color: #ed8936;
        }

        .summary-card.issued .value {
            color: #48bb78;
        }

        .summary-card.total .value {
            color: #667eea;
        }

        .chart-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .chart-section h2 {
            margin-bottom: 15px;
            color: #333;
        }

        .function-breakdown {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .function-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .function-card h4 {
            color: #667eea;
            margin-bottom: 10px;
        }

        .function-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .orders-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .table-header {
            background: #667eea;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h2 {
            margin: 0;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            font-weight: bold;
            color: #555;
            position: sticky;
            top: 0;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fed7aa;
            color: #c2410c;
        }

        .status-issued {
            background: #bbf7d0;
            color: #166534;
        }

        .trend-chart {
            margin-top: 20px;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .filter-row {
                flex-direction: column;
            }
            
            .filter-group {
                width: 100%;
            }
            
            .summary-cards {
                grid-template-columns: 1fr;
            }
            
            .function-breakdown {
                grid-template-columns: 1fr;
            }
            
            table {
                font-size: 14px;
            }
            
            th, td {
                padding: 8px;
            }
        }

        .print-hide {
            display: block;
        }

        @media print {
            .print-hide {
                display: none !important;
            }
            
            body {
                background: white;
            }
            
            .container {
                max-width: none;
                padding: 0;
            }
            
            .summary-cards {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div style="margin-bottom: 1rem;">
  <a
    href="../admin.php"
    role="button"
    style="
      display: inline-block;
      background-color: #f59e0b;
      color: #ffffff;
      padding: 0.5rem 0.9rem;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
      box-shadow: 0 2px 6px rgba(0,0,0,0.12);
    "
    onmouseover="this.style.backgroundColor='#d97706';"
    onmouseout="this.style.backgroundColor='#f59e0b';"
    onbeforeprint="this.style.display='none';"
    onafterprint="this.style.display='inline-block';"
  >
    Back
  </a>
</div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>HGG Restaurant Kitchen Order Sheet Report</h1>
            <p><?= ucfirst($report_type) ?> Report: <?= $start_date ?> <?= $end_date !== $start_date ? 'to ' . $end_date : '' ?></p>
        </div>

        <!-- Filters -->
        <div class="filters print-hide">
            <form method="GET" action="">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Report Type:</label>
                        <select name="report_type" onchange="updateDateFields()">
                            <option value="daily" <?= $report_type === 'daily' ? 'selected' : '' ?>>Daily</option>
                            <option value="monthly" <?= $report_type === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                            <option value="yearly" <?= $report_type === 'yearly' ? 'selected' : '' ?>>Yearly</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Start Date:</label>
                        <input type="date" name="start_date" value="<?= $start_date ?>">
                    </div>
                    
                    <div class="filter-group" id="end-date-group">
                        <label>End Date:</label>
                        <input type="date" name="end_date" value="<?= $end_date ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label>Status:</label>
                        <select name="status">
                            <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Status</option>
                            <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="issued" <?= $status_filter === 'issued' ? 'selected' : '' ?>>Issued</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Function Type:</label>
                        <select name="function_type">
                            <option value="all">All Functions</option>
                            <?php foreach ($function_types as $type): ?>
                                <option value="<?= htmlspecialchars($type) ?>" 
                                        <?= $function_type === $type ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Order Sheet No:</label>
                        <input type="text" name="order_sheet_no" value="<?= htmlspecialchars($order_sheet_no) ?>" placeholder="Enter sheet number">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                    <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'csv'])) ?>" class="btn btn-success">Export CSV</a>
                </div>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card total">
                <h3>Total Orders</h3>
                <div class="value"><?= number_format($summary['total_orders']) ?></div>
            </div>
            <div class="summary-card pending">
                <h3>Pending Orders</h3>
                <div class="value"><?= number_format($summary['pending_orders']) ?></div>
            </div>
            <div class="summary-card issued">
                <h3>Issued Orders</h3>
                <div class="value"><?= number_format($summary['issued_orders']) ?></div>
            </div>
            <div class="summary-card">
                <h3>Unique Order Sheets</h3>
                <div class="value"><?= number_format($summary['unique_order_sheets']) ?></div>
            </div>
            <div class="summary-card">
                <h3>Unique Items</h3>
                <div class="value"><?= number_format($summary['unique_items']) ?></div>
            </div>
            <div class="summary-card">
                <h3>Total Requested Qty</h3>
                <div class="value"><?= number_format($summary['total_requested_qty'], 2) ?></div>
            </div>
            <div class="summary-card">
                <h3>Total Issued Qty</h3>
                <div class="value"><?= number_format($summary['total_issued_qty'], 2) ?></div>
            </div>
            <div class="summary-card">
                <h3>Fulfillment Rate</h3>
                <div class="value">
                    <?= $summary['total_requested_qty'] > 0 
                        ? number_format(($summary['total_issued_qty'] / $summary['total_requested_qty']) * 100, 1) . '%' 
                        : '0%' ?>
                </div>
            </div>
        </div>

        <!-- Function Type Breakdown -->
        <?php if (!empty($function_breakdown)): ?>
        <div class="chart-section">
            <h2>Function Type Breakdown</h2>
            <div class="function-breakdown">
                <?php foreach ($function_breakdown as $func): ?>
                <div class="function-card">
                    <h4><?= htmlspecialchars($func['function_type']) ?: 'Not Specified' ?></h4>
                    <div class="function-stats">
                        <span>Orders:</span>
                        <strong><?= number_format($func['order_count']) ?></strong>
                    </div>
                    <div class="function-stats">
                        <span>Requested:</span>
                        <strong><?= number_format($func['total_requested'], 2) ?></strong>
                    </div>
                    <div class="function-stats">
                        <span>Issued:</span>
                        <strong><?= number_format($func['total_issued'], 2) ?></strong>
                    </div>
                    <div class="function-stats">
                        <span>Rate:</span>
                        <strong>
                            <?= $func['total_requested'] > 0 
                                ? number_format(($func['total_issued'] / $func['total_requested']) * 100, 1) . '%' 
                                : '0%' ?>
                        </strong>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Daily Trend Chart -->
        <?php if (!empty($daily_trend) && $report_type !== 'daily'): ?>
        <div class="chart-section">
            <h2>Daily Trend</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Orders</th>
                            <th>Requested Qty</th>
                            <th>Issued Qty</th>
                            <th>Fulfillment Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daily_trend as $day): ?>
                        <tr>
                            <td><?= date('M d, Y', strtotime($day['order_date'])) ?></td>
                            <td><?= number_format($day['daily_orders']) ?></td>
                            <td><?= number_format($day['daily_requested'], 2) ?></td>
                            <td><?= number_format($day['daily_issued'], 2) ?></td>
                            <td>
                                <?= $day['daily_requested'] > 0 
                                    ? number_format(($day['daily_issued'] / $day['daily_requested']) * 100, 1) . '%' 
                                    : '0%' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Orders Table -->
        <div class="orders-table">
            <div class="table-header">
                <h2>Detailed Order List</h2>
                <span><?= count($orders) ?> orders found</span>
            </div>
            
            <?php if (!empty($orders)): ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Sheet No</th>
                            <th>Item</th>
                            <th>Requested</th>
                            <th>Issued</th>
                            <th>Unit</th>
                            <th>Status</th>
                            <th>Function</th>
                            <th>Function Date</th>
                            <th>Day/Night</th>
                            <th>Responsible</th>
                            <th>Request Date</th>
                            <th>Issued Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['order_id'] ?></td>
                            <td><?= $order['order_sheet_no'] ?></td>
                            <td><?= htmlspecialchars($order['item_name']) ?></td>
                            <td><?= number_format($order['requested_qty'], 2) ?></td>
                            <td><?= number_format($order['actual_issued_qty'], 2) ?></td>
                            <td><?= htmlspecialchars($order['unit']) ?></td>
                            <td>
                                <span class="status-badge status-<?= $order['status'] ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($order['function_type']) ?></td>
                            <td><?= $order['function_date'] ? date('M d, Y', strtotime($order['function_date'])) : '-' ?></td>
                            <td><?= $order['day_night'] ?: '-' ?></td>
                            <td><?= htmlspecialchars($order['responsible_name']) ?: '-' ?></td>
                            <td><?= date('M d, Y H:i', strtotime($order['request_date'])) ?></td>
                            <td><?= $order['issued_date'] ? date('M d, Y H:i', strtotime($order['issued_date'])) : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="no-data">
                <p>No orders found for the selected criteria.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function updateDateFields() {
            const reportType = document.querySelector('select[name="report_type"]').value;
            const startDateInput = document.querySelector('input[name="start_date"]');
            const endDateGroup = document.getElementById('end-date-group');
            
            const today = new Date();
            
            if (reportType === 'daily') {
                startDateInput.value = today.toISOString().split('T')[0];
                endDateGroup.style.display = 'none';
            } else {
                endDateGroup.style.display = 'flex';
                
                if (reportType === 'monthly') {
                    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                    startDateInput.value = firstDay.toISOString().split('T')[0];
                    document.querySelector('input[name="end_date"]').value = lastDay.toISOString().split('T')[0];
                } else if (reportType === 'yearly') {
                    const firstDay = new Date(today.getFullYear(), 0, 1);
                    const lastDay = new Date(today.getFullYear(), 11, 31);
                    startDateInput.value = firstDay.toISOString().split('T')[0];
                    document.querySelector('input[name="end_date"]').value = lastDay.toISOString().split('T')[0];
                }
            }
        }

        // Initialize date fields on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateDateFields();
        });

        // Print functionality
        function printReport() {
            window.print();
        }
        
        // Add print button
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.querySelector('.table-header');
            const printBtn = document.createElement('button');
            printBtn.textContent = 'Print Report';
            printBtn.className = 'btn btn-primary print-hide';
            printBtn.onclick = printReport;
            header.appendChild(printBtn);
        });
    </script>
</body>
</html>