<?php
// Database connection
$host = 'localhost';
$dbname = 'hotelgrandguardi_wedding_bliss'; // Replace with your actual database name
$username = 'hotelgrandguardi_root';
$password = 'Sun123flower@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// Fetch min and max years for input validation
$min_date_stmt = $pdo->query("SELECT MIN(date) FROM logistics_grn");
$min_date = $min_date_stmt->fetchColumn();
$min_year = $min_date ? date('Y', strtotime($min_date)) : (date('Y') - 5);

$max_date_stmt = $pdo->query("SELECT MAX(date) FROM logistics_grn");
$max_date = $max_date_stmt->fetchColumn();
$max_year = $max_date ? date('Y', strtotime($max_date)) : date('Y');

// Generate list of available years
$available_years = [];
if ($min_date && $max_date) {
    for ($y = $max_year; $y >= $min_year; $y--) {
        $available_years[] = $y;
    }
} else {
    // Fallback range if no data
    for ($y = date('Y'); $y >= date('Y') - 5; $y--) {
        $available_years[] = $y;
    }
}

// Determine report type and parameters
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'daily';
$selected_date = isset($_GET['selected_date']) ? $_GET['selected_date'] : date('Y-m-d');
$selected_month = isset($_GET['selected_month']) ? $_GET['selected_month'] : date('Y-m');
$selected_year = isset($_GET['selected_year']) ? $_GET['selected_year'] : date('Y');
$grn_number = isset($_GET['grn_number']) ? trim($_GET['grn_number']) : '';

// Initialize variables
$report_data = [];
$total_quantity = 0;
$no_grn_message = '';

// Fetch data based on report type
try {
    $stmt = null; // Initialize $stmt to prevent undefined variable
    switch ($report_type) {
        case 'daily':
            $stmt = $pdo->prepare("
                SELECT r.id AS grn_id, r.grn_number, r.date, r.location, r.received_by, r.checked_by,
                       d.id AS item_id, d.item_name, d.quantity, d.unit
                FROM logistics_grn r
                LEFT JOIN logistics_grn_details d ON r.id = d.grn_id
                WHERE DATE(r.date) = :selected_date
                ORDER BY r.date DESC, r.id, d.id
            ");
            $stmt->execute(['selected_date' => $selected_date]);
            break;

        case 'monthly':
            $start_date = date('Y-m-01', strtotime($selected_month));
            $end_date = date('Y-m-t', strtotime($selected_month));
            $stmt = $pdo->prepare("
                SELECT 
                    DATE(r.date) AS report_date,
                    r.id AS grn_id, r.grn_number, r.date, r.location, r.received_by, r.checked_by,
                    d.id AS item_id, d.item_name, d.quantity, d.unit,
                    SUM(d.quantity) AS total_quantity
                FROM logistics_grn r
                LEFT JOIN logistics_grn_details d ON r.id = d.grn_id
                WHERE r.date BETWEEN :start_date AND :end_date
                GROUP BY DATE(r.date), r.id, d.id
                ORDER BY report_date DESC, r.id, d.id
            ");
            $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
            break;

        case 'yearly':
            $start_date = date('Y-01-01', strtotime($selected_year . '-01-01'));
            $end_date = date('Y-12-31', strtotime($selected_year . '-01-01'));
            $stmt = $pdo->prepare("
                SELECT 
                    MONTH(r.date) AS month,
                    r.id AS grn_id, r.grn_number, r.date, r.location, r.received_by, r.checked_by,
                    d.id AS item_id, d.item_name, d.quantity, d.unit,
                    SUM(d.quantity) AS total_quantity
                FROM logistics_grn r
                LEFT JOIN logistics_grn_details d ON r.id = d.grn_id
                WHERE r.date BETWEEN :start_date AND :end_date
                GROUP BY MONTH(r.date), r.id, d.id
                ORDER BY month DESC, r.id, d.id
            ");
            $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
            break;

        case 'grn_number':
            if (empty($grn_number)) {
                $no_grn_message = "Please enter a GRN number.";
                break;
            }
            $stmt = $pdo->prepare("
                SELECT r.id AS grn_id, r.grn_number, r.date, r.location, r.received_by, r.checked_by,
                       d.id AS item_id, d.item_name, d.quantity, d.unit
                FROM logistics_grn r
                LEFT JOIN logistics_grn_details d ON r.id = d.grn_id
                WHERE r.grn_number = :grn_number
                ORDER BY r.date DESC, r.id, d.id
            ");
            $stmt->execute(['grn_number' => $grn_number]);
            break;
    }

    // Fetch data only if $stmt is set
    if ($stmt) {
        $report_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate total quantity
        foreach ($report_data as $row) {
            if (isset($row['quantity'])) {
                $total_quantity += $row['quantity'];
            } elseif (isset($row['total_quantity'])) {
                $total_quantity += $row['total_quantity'];
            }
        }

        // Set message if no data found for GRN number
        if ($report_type == 'grn_number' && empty($report_data) && !empty($grn_number)) {
            $no_grn_message = "No records found for GRN number: " . htmlspecialchars($grn_number);
        }
    }

} catch (PDOException $e) {
    die("ERROR: Could not execute query. " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GRN Detailed Report</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
        .total-quantity {
            font-size: 24px;
            font-weight: bold;
            color: #27ae60;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
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
        .grn-header {
            background-color: #ecf0f1;
            padding: 10px;
            margin-top: 20px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
        }
        @media (max-width: 768px) {
            .filters {
                flex-direction: column;
            }
            table {
                display: block;
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
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
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
        <h1>GRN Detailed Report</h1>

        <!-- Filters -->
        <form method="GET" action="">
            <div class="filters">
                <div class="filter-group">
                    <label for="report_type">Report Type:</label>
                    <select name="report_type" id="report_type" onchange="this.form.submit()">
                        <option value="daily" <?= $report_type == 'daily' ? 'selected' : '' ?>>Daily</option>
                        <option value="monthly" <?= $report_type == 'monthly' ? 'selected' : '' ?>>Monthly</option>
                        <option value="yearly" <?= $report_type == 'yearly' ? 'selected' : '' ?>>Yearly</option>
                        <option value="grn_number" <?= $report_type == 'grn_number' ? 'selected' : '' ?>>GRN Number</option>
                    </select>
                </div>

                <?php if ($report_type == 'daily'): ?>
                    <div class="filter-group">
                        <label for="selected_date">Date:</label>
                        <input type="date" name="selected_date" value="<?= htmlspecialchars($selected_date) ?>" min="<?= htmlspecialchars($min_date ?: date('Y-m-d')) ?>" max="<?= htmlspecialchars($max_date ?: date('Y-m-d')) ?>" onchange="this.form.submit()">
                    </div>
                <?php elseif ($report_type == 'monthly'): ?>
                    <div class="filter-group">
                        <label for="selected_month">Month:</label>
                        <input type="month" name="selected_month" value="<?= htmlspecialchars($selected_month) ?>" onchange="this.form.submit()">
                    </div>
                <?php elseif ($report_type == 'yearly'): ?>
                    <div class="filter-group">
                        <label for="selected_year">Year:</label>
                        <select name="selected_year" onchange="this.form.submit()">
                            <?php foreach ($available_years as $y): ?>
                                <option value="<?= $y ?>" <?= $selected_year == $y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php else: // grn_number ?>
                    <div class="filter-group">
                        <label for="grn_number">GRN Number:</label>
                        <input type="text" name="grn_number" value="<?= htmlspecialchars($grn_number) ?>" placeholder="Enter GRN Number" onchange="this.form.submit()">
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
                if ($report_type == 'daily') {
                    echo "Daily Report for " . date('F j, Y', strtotime($selected_date));
                } elseif ($report_type == 'monthly') {
                    echo "Monthly Report for " . date('F Y', strtotime($selected_month));
                } elseif ($report_type == 'yearly') {
                    echo "Yearly Report for " . $selected_year;
                } else {
                    echo "Report for GRN Number: " . ($grn_number ? htmlspecialchars($grn_number) : 'N/A');
                }
                ?>
            </h2>
            <div class="total-quantity">Total Quantity: <?= number_format($total_quantity) ?></div>
        </div>

        <!-- Report Data -->
        <?php if ($no_grn_message): ?>
            <div class="no-data">
                <?= $no_grn_message ?>
            </div>
        <?php elseif (count($report_data) > 0): ?>
            <?php
            $current_grn = null;
            $current_date = null;
            $current_month = null;
            ?>
            <?php foreach ($report_data as $row): ?>
                <?php
                // Handle grouping for monthly and yearly reports
                $group_key = $report_type == 'monthly' ? $row['report_date'] : ($report_type == 'yearly' ? $row['month'] : null);
                if ($group_key && $group_key != ($report_type == 'monthly' ? $current_date : $current_month)) {
                    if ($current_grn !== null) {
                        echo "</tbody></table>";
                    }
                    $current_grn = null;
                    echo "<div class='grn-header'>";
                    if ($report_type == 'monthly') {
                        echo "<h4>" . date('F j, Y', strtotime($row['report_date'])) . "</h4>";
                        $current_date = $row['report_date'];
                    } elseif ($report_type == 'yearly') {
                        echo "<h4>" . date('F', mktime(0, 0, 0, $row['month'], 1)) . "</h4>";
                        $current_month = $row['month'];
                    }
                    echo "</div>";
                }

                // Handle GRN grouping
                if ($row['grn_id'] != $current_grn) {
                    if ($current_grn !== null) {
                        echo "</tbody></table>";
                    }
                    $current_grn = $row['grn_id'];
                    echo "<div class='grn-header'>";
                    echo "<h4>GRN Number: " . htmlspecialchars($row['grn_number']) . " | Date: " . date('F j, Y H:i', strtotime($row['date'])) . " | Location: " . htmlspecialchars($row['location']) . "</h4>";
                    echo "<p>Received By: " . htmlspecialchars($row['received_by'] ?? 'N/A') . " | Checked By: " . htmlspecialchars($row['checked_by'] ?? 'N/A') . "</p>";
                    echo "</div>";
                    echo "<table class='table table-bordered'>";
                    echo "<thead><tr><th>Item Name</th><th>Quantity</th><th>Unit</th></tr></thead>";
                    echo "<tbody>";
                }
                ?>
                <?php if ($row['item_name']): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['item_name']) ?></td>
                        <td><?= number_format($row['quantity']) ?></td>
                        <td><?= htmlspecialchars($row['unit']) ?></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if ($current_grn !== null): ?>
                </tbody></table>
            <?php endif; ?>

            <button class="print-btn" onclick="window.print()">Print Report</button>

        <?php else: ?>
            <div class="no-data">
                No GRN records found for the selected criteria.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>