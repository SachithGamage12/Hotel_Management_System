<?php
// Database connection
$host = 'localhost';
$dbname = 'hotelgrandguardi_wedding_bliss';
$username = 'hotelgrandguardi_root';
$password = 'Sun123flower@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// Determine report type
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'daily';
$selected_date = isset($_GET['selected_date']) ? $_GET['selected_date'] : date('Y-m-d');
$selected_month = isset($_GET['selected_month']) ? $_GET['selected_month'] : date('Y-m');
$selected_year = isset($_GET['selected_year']) ? $_GET['selected_year'] : date('Y');

// Fetch distinct years for dropdown
$stmt_years = $pdo->query("SELECT DISTINCT YEAR(purchased_date) AS year FROM purchases_backup ORDER BY year DESC");
$years = $stmt_years->fetchAll(PDO::FETCH_COLUMN);

// Initialize variables
$report_data = [];
$total_amount = 0;

// Fetch data based on report type with join to items table
try {
    switch($report_type) {
        case 'daily':
            $stmt = $pdo->prepare("
                SELECT p.id, p.item_id, i.name AS item_name, p.quantity, p.unit, p.unit_price, p.total_price, p.expiry_date, p.purchased_date 
                FROM purchases_backup p
                LEFT JOIN items i ON p.item_id = i.id
                WHERE p.purchased_date = :selected_date 
                ORDER BY p.purchased_date DESC
            ");
            $stmt->execute(['selected_date' => $selected_date]);
            break;
            
        case 'monthly':
            $start_date = date('Y-m-01', strtotime($selected_month));
            $end_date = date('Y-m-t', strtotime($selected_month));
            
            $stmt = $pdo->prepare("
                SELECT 
                    DATE(p.purchased_date) as purchase_day,
                    p.item_id,
                    i.name AS item_name,
                    p.unit,
                    p.unit_price,
                    MAX(p.purchased_date) as purchased_date,
                    MAX(p.expiry_date) as expiry_date,
                    SUM(p.quantity) as total_quantity,
                    SUM(p.total_price) as daily_total
                FROM purchases_backup p
                LEFT JOIN items i ON p.item_id = i.id
                WHERE p.purchased_date BETWEEN :start_date AND :end_date 
                GROUP BY DATE(p.purchased_date), p.item_id, p.unit, p.unit_price
                ORDER BY purchase_day DESC, p.item_id
            ");
            $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
            break;
            
        case 'yearly':
            $start_date = date('Y-01-01', strtotime($selected_year . '-01-01'));
            $end_date = date('Y-12-31', strtotime($selected_year . '-01-01'));
            
            $stmt = $pdo->prepare("
                SELECT 
                    MONTH(p.purchased_date) as month,
                    p.item_id,
                    i.name AS item_name,
                    p.unit,
                    MAX(p.purchased_date) as purchased_date,
                    MAX(p.expiry_date) as expiry_date,
                    SUM(p.quantity) as total_quantity,
                    SUM(p.total_price) as total_amount,
                    COUNT(p.id) as purchase_count
                FROM purchases_backup p
                LEFT JOIN items i ON p.item_id = i.id
                WHERE p.purchased_date BETWEEN :start_date AND :end_date 
                GROUP BY MONTH(p.purchased_date), p.item_id, p.unit
                ORDER BY month DESC, p.item_id
            ");
            $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
            break;
    }
    
    $report_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate total amount
    foreach($report_data as $row) {
        if($report_type == 'daily') {
            $total_amount += $row['total_price'];
        } elseif($report_type == 'monthly') {
            $total_amount += $row['daily_total'];
        } elseif($report_type == 'yearly') {
            $total_amount += $row['total_amount'];
        }
    }
    
} catch(PDOException $e) {
    die("ERROR: Could not execute query. " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchased Items Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f7fa;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        select, input, button {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        button {
            background: #3498db;
            color: white;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
            align-self: flex-end;
        }
        button:hover {
            background: #2980b9;
        }
        .summary {
            background: #e8f4fc;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .summary h2 {
            margin: 0;
            color: #2c3e50;
        }
        .total-amount {
            font-size: 24px;
            font-weight: bold;
            color: #27ae60;
        }
        .table {
            width: 100%;
            margin-top: 20px;
        }
        .table thead th {
            background-color: #3498db;
            color: white;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0,123,255,0.05);
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
            font-style: italic;
        }
        .print-btn {
            background: #27ae60;
            margin-top: 20px;
            padding: 10px 20px;
        }
        .print-btn:hover {
            background: #219653;
        }
        @media (max-width: 768px) {
            .filters {
                flex-direction: column;
            }
            .table-responsive {
                overflow-x: auto;
            }
        }
        @media print {
            body {
                padding: 0;
                background-color: white;
            }
            .container {
                box-shadow: none;
                padding: 0;
            }
            .filters, .print-btn {
                display: none;
            }
            .summary {
                border: 1px solid #ddd;
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
        <h1>Purchased Items Report</h1>
        
        <!-- Filters -->
        <form method="GET" action="">
            <div class="filters">
                <div class="filter-group">
                    <label for="report_type">Report Type:</label>
                    <select name="report_type" id="report_type" onchange="this.form.submit()">
                        <option value="daily" <?= $report_type == 'daily' ? 'selected' : '' ?>>Daily</option>
                        <option value="monthly" <?= $report_type == 'monthly' ? 'selected' : '' ?>>Monthly</option>
                        <option value="yearly" <?= $report_type == 'yearly' ? 'selected' : '' ?>>Yearly</option>
                    </select>
                </div>
                
                <?php if($report_type == 'daily'): ?>
                <div class="filter-group">
                    <label for="selected_date">Date:</label>
                    <input type="date" name="selected_date" value="<?= htmlspecialchars($selected_date) ?>" onchange="this.form.submit()">
                </div>
                <?php elseif($report_type == 'monthly'): ?>
                <div class="filter-group">
                    <label for="selected_month">Month:</label>
                    <input type="month" name="selected_month" value="<?= htmlspecialchars($selected_month) ?>" onchange="this.form.submit()">
                </div>
                <?php else: ?>
                <div class="filter-group">
                    <label for="selected_year">Year:</label>
                    <select name="selected_year" onchange="this.form.submit()">
                        <option value="">All Years</option>
                        <?php foreach($years as $y): ?>
                        <option value="<?= $y ?>" <?= $selected_year == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="filter-group">
                    <label>&nbsp;</label>
                    <button type="submit">Generate Report</button>
                </div>
            </div>
        </form>
        
        <!-- Summary -->
        <div class="summary">
            <h2>
                <?php 
                if($report_type == 'daily') {
                    echo "Daily Report for " . date('F j, Y', strtotime($selected_date));
                } elseif($report_type == 'monthly') {
                    echo "Monthly Report for " . date('F Y', strtotime($selected_month));
                } else {
                    echo "Yearly Report for " . ($selected_year ?: 'All Years');
                }
                ?>
            </h2>
            <div class="total-amount">Total: Rs.<?= number_format($total_amount, 2) ?></div>
        </div>
        
        <!-- Report Data -->
        <?php if(count($report_data) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <?php if($report_type == 'daily'): ?>
                                <th>ID</th>
                                <th>Item Name</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Unit Price (Rs.)</th>
                                <th>Total Price (Rs.)</th>
                                <th>Expiry Date</th>
                                <th>Purchased Date</th>
                            <?php elseif($report_type == 'monthly'): ?>
                                <th>Date</th>
                                <th>Item Name</th>
                                <th>Unit</th>
                                <th>Total Quantity</th>
                                <th>Unit Price (Rs.)</th>
                                <th>Purchased Date</th>
                                <th>Expiry Date</th>
                                <th>Daily Total (Rs.)</th>
                            <?php else: ?>
                                <th>Month</th>
                                <th>Item Name</th>
                                <th>Unit</th>
                                <th>Total Quantity</th>
                                <th>Purchased Date</th>
                                <th>Expiry Date</th>
                                <th>Total Amount (Rs.)</th>
                                <th>Purchase Count</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($report_data as $row): ?>
                        <tr>
                            <?php if($report_type == 'daily'): ?>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['item_name'] ?? 'Unknown Item') ?></td>
                                <td><?= $row['quantity'] ?></td>
                                <td><?= htmlspecialchars($row['unit']) ?></td>
                                <td>Rs.<?= number_format($row['unit_price'], 2) ?></td>
                                <td>Rs.<?= number_format($row['total_price'], 2) ?></td>
                                <td><?= $row['expiry_date'] ?? 'N/A' ?></td>
                                <td><?= $row['purchased_date'] ?></td>
                            <?php elseif($report_type == 'monthly'): ?>
                                <td><?= date('M j, Y', strtotime($row['purchase_day'])) ?></td>
                                <td><?= htmlspecialchars($row['item_name'] ?? 'Unknown Item') ?></td>
                                <td><?= htmlspecialchars($row['unit']) ?></td>
                                <td><?= $row['total_quantity'] ?></td>
                                <td>Rs.<?= number_format($row['unit_price'], 2) ?></td>
                                <td><?= $row['purchased_date'] ?? 'N/A' ?></td>
                                <td><?= $row['expiry_date'] ?? 'N/A' ?></td>
                                <td>Rs.<?= number_format($row['daily_total'], 2) ?></td>
                            <?php else: ?>
                                <td><?= date('F', mktime(0, 0, 0, $row['month'], 1)) ?></td>
                                <td><?= htmlspecialchars($row['item_name'] ?? 'Unknown Item') ?></td>
                                <td><?= htmlspecialchars($row['unit']) ?></td>
                                <td><?= $row['total_quantity'] ?></td>
                                <td><?= $row['purchased_date'] ?? 'N/A' ?></td>
                                <td><?= $row['expiry_date'] ?? 'N/A' ?></td>
                                <td>Rs.<?= number_format($row['total_amount'], 2) ?></td>
                                <td><?= $row['purchase_count'] ?></td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <button class="btn print-btn" onclick="window.print()">Print Report</button>
            
        <?php else: ?>
            <div class="no-data">
                No purchased items found for the selected criteria.
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>