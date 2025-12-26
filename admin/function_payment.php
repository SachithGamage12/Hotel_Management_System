<?php
// Set timezone to Sri Jayawardenepura (Asia/Colombo)
date_default_timezone_set('Asia/Colombo');

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
$report_type = $_GET['report_type'] ?? 'monthly';
$search_type = $_GET['search_type'] ?? '';
$search_value = $_GET['search_value'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$specific_date = $_GET['specific_date'] ?? '';
$month_year = $_GET['month_year'] ?? '';
$year = $_GET['year'] ?? '';

// Build query based on filters
$sql = "SELECT * FROM payments WHERE 1=1";
$params = [];

// Search filters
if (!empty($search_value)) {
    if ($search_type == 'invoice') {
        $sql .= " AND invoice_number LIKE ?";
        $params[] = "%$search_value%";
    } elseif ($search_type == 'booking') {
        $sql .= " AND booking_reference LIKE ?";
        $params[] = "%$search_value%";
    }
}

// Date filters
if ($report_type == 'daily' && !empty($specific_date)) {
    $sql .= " AND DATE(payment_date) = ?";
    $params[] = $specific_date;
} elseif ($report_type == 'monthly' && !empty($month_year)) {
    $sql .= " AND DATE_FORMAT(payment_date, '%Y-%m') = ?";
    $params[] = $month_year;
} elseif ($report_type == 'yearly' && !empty($year)) {
    $sql .= " AND YEAR(payment_date) = ?";
    $params[] = $year;
} elseif ($report_type == 'custom' && !empty($date_from) && !empty($date_to)) {
    $sql .= " AND DATE(payment_date) BETWEEN ? AND ?";
    $params[] = $date_from;
    $params[] = $date_to;
}

$sql .= " ORDER BY payment_date DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Query failed: " . $e->getMessage();
    $payments = [];
}

// Calculate totals
$total_amount = 0;
$total_payment = 0;
$total_pending = 0;
$total_records = count($payments);

foreach ($payments as $payment) {
    $total_amount += $payment['total_amount'];
    $total_payment += $payment['payment_amount'];
    $total_pending += $payment['pending_amount'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Reports</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 300;
        }
        
        .filters {
            background: #f8f9fa;
            padding: 25px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
            align-items: center;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .filter-group label {
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
        }
        
        .filter-group select,
        .filter-group input {
            padding: 10px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            min-width: 150px;
        }
        
        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn {
            padding: 12px 25px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #495057);
        }
        
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 25px;
            background: #f8f9fa;
        }
        
        .summary-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .summary-card:hover {
            transform: translateY(-5px);
        }
        
        .summary-card h3 {
            color: #495057;
            margin-bottom: 10px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .summary-card .amount {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .summary-card.total { border-left: 5px solid #28a745; }
        .summary-card.paid { border-left: 5px solid #007bff; }
        .summary-card.pending { border-left: 5px solid #dc3545; }
        .summary-card.records { border-left: 5px solid #6f42c1; }
        
        .table-container {
            overflow-x: auto;
            margin: 25px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .data-table th {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .data-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: top;
        }
        
        .data-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .data-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .amount {
            font-weight: 600;
            text-align: right;
        }
        
        .amount.positive { color: #28a745; }
        .amount.negative { color: #dc3545; }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        
        .status-partial {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-pending {
            background: #f8d7da;
            color: #721c24;
        }
        
        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
            font-size: 1.1rem;
        }
        
        .export-buttons {
            padding: 20px 25px;
            text-align: right;
            background: #f8f9fa;
            border-top: 2px solid #e9ecef;
        }
        
        .export-buttons .btn {
            margin-left: 10px;
        }
        
        @media (max-width: 768px) {
            .filter-row {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group {
                width: 100%;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .data-table {
                font-size: 0.9rem;
            }
            
            .summary-cards {
                grid-template-columns: 1fr;
            }
        }
        
        .print-only {
            display: none;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .container {
                box-shadow: none;
                border-radius: 0;
            }
            
            .filters,
            .export-buttons,
            .btn {
                display: none !important;
            }
            
            .print-only {
                display: block;
            }
            
            .data-table th {
                background: #2c3e50 !important;
                color: white !important;
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
        <div class="header">
            <h1>Payment Reports Dashboard</h1>
            <p>Comprehensive payment tracking and reporting system</p>
        </div>
        
        <div class="filters">
            <form method="GET" action="">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="report_type">Report Type</label>
                        <select name="report_type" id="report_type" onchange="toggleDateInputs()">
                            <option value="daily" <?= $report_type == 'daily' ? 'selected' : '' ?>>Daily</option>
                            <option value="monthly" <?= $report_type == 'monthly' ? 'selected' : '' ?>>Monthly</option>
                            <option value="yearly" <?= $report_type == 'yearly' ? 'selected' : '' ?>>Yearly</option>
                            <option value="custom" <?= $report_type == 'custom' ? 'selected' : '' ?>>Custom Range</option>
                        </select>
                    </div>
                    
                    <div class="filter-group" id="daily_input" style="display: <?= $report_type == 'daily' ? 'flex' : 'none' ?>">
                        <label for="specific_date">Select Date</label>
                        <input type="date" name="specific_date" id="specific_date" value="<?= htmlspecialchars($specific_date) ?>">
                    </div>
                    
                    <div class="filter-group" id="monthly_input" style="display: <?= $report_type == 'monthly' ? 'flex' : 'none' ?>">
                        <label for="month_year">Select Month</label>
                        <input type="month" name="month_year" id="month_year" value="<?= htmlspecialchars($month_year) ?>">
                    </div>
                    
                    <div class="filter-group" id="yearly_input" style="display: <?= $report_type == 'yearly' ? 'flex' : 'none' ?>">
                        <label for="year">Select Year</label>
                        <select name="year" id="year">
                            <option value="">Select Year</option>
                            <?php
                            $currentYear = date('Y');
                            for ($i = $currentYear; $i >= $currentYear - 10; $i--) {
                                $selected = ($year == $i) ? 'selected' : '';
                                echo "<option value='$i' $selected>$i</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="filter-group" id="custom_from" style="display: <?= $report_type == 'custom' ? 'flex' : 'none' ?>">
                        <label for="date_from">From Date</label>
                        <input type="date" name="date_from" id="date_from" value="<?= htmlspecialchars($date_from) ?>">
                    </div>
                    
                    <div class="filter-group" id="custom_to" style="display: <?= $report_type == 'custom' ? 'flex' : 'none' ?>">
                        <label for="date_to">To Date</label>
                        <input type="date" name="date_to" id="date_to" value="<?= htmlspecialchars($date_to) ?>">
                    </div>
                </div>
                
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="search_type">Search Type</label>
                        <select name="search_type" id="search_type">
                            <option value="">No Search</option>
                            <option value="invoice" <?= $search_type == 'invoice' ? 'selected' : '' ?>>Invoice Number</option>
                            <option value="booking" <?= $search_type == 'booking' ? 'selected' : '' ?>>Booking Reference</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="search_value">Search Value</label>
                        <input type="text" name="search_value" id="search_value" value="<?= htmlspecialchars($search_value) ?>" placeholder="Enter search term...">
                    </div>
                    
                    <div class="filter-group" style="margin-top: 25px;">
                        <button type="submit" class="btn">Generate Report</button>
                        <a href="?" class="btn btn-secondary">Clear Filters</a>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="summary-cards">
            <div class="summary-card total">
                <h3>Total Amount</h3>
                <div class="amount">LKR <?= number_format($total_amount, 2) ?></div>
            </div>
            <div class="summary-card paid">
                <h3>Total Paid</h3>
                <div class="amount">LKR <?= number_format($total_payment, 2) ?></div>
            </div>
            <div class="summary-card pending">
                <h3>Total Pending</h3>
                <div class="amount">LKR <?= number_format($total_pending, 2) ?></div>
            </div>
            <div class="summary-card records">
                <h3>Total Records</h3>
                <div class="amount"><?= $total_records ?></div>
            </div>
        </div>
        
        <div class="print-only" style="padding: 20px; text-align: center; border-bottom: 2px solid #eee;">
            <h2>Payment Report</h2>
            <p><strong>Generated on:</strong> <?= date('F j, Y g:i A T') ?></p>
            <p><strong>Report Type:</strong> <?= ucfirst($report_type) ?></p>
            <?php if (!empty($search_value)): ?>
                <p><strong>Search:</strong> <?= ucfirst($search_type) ?> - "<?= htmlspecialchars($search_value) ?>"</p>
            <?php endif; ?>
        </div>
        
        <div class="table-container">
            <?php if (isset($error)): ?>
                <div class="no-data">
                    <p>Error: <?= htmlspecialchars($error) ?></p>
                </div>
            <?php elseif (empty($payments)): ?>
                <div class="no-data">
                    <p>No payment records found matching your criteria.</p>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Booking Ref</th>
                            <th>Contact Info</th>
                            <th>Payment Date</th>
                            <th>Value Type</th>
                            <th>Total Amount</th>
                            <th>Payment Type</th>
                            <th>Paid Amount</th>
                            <th>Pending Amount</th>
                            <th>Pax</th>
                            <th>Status</th>
                            <th>Issued By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?= htmlspecialchars($payment['invoice_number']) ?></td>
                                <td><?= htmlspecialchars($payment['booking_reference']) ?></td>
                                <td>
                                    <?php if (!empty($payment['contact_no'])): ?>
                                        <div>📞 <?= htmlspecialchars($payment['contact_no']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($payment['whatsapp_no'])): ?>
                                        <div>📱 <?= htmlspecialchars($payment['whatsapp_no']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($payment['email'])): ?>
                                        <div>✉️ <?= htmlspecialchars($payment['email']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M j, Y g:i A T', strtotime($payment['payment_date'])) ?></td>
                                <td><?= htmlspecialchars($payment['value_type']) ?></td>
                                <td class="amount">LKR <?= number_format($payment['total_amount'], 2) ?></td>
                                <td><?= htmlspecialchars($payment['payment_type']) ?></td>
                                <td class="amount positive">LKR <?= number_format($payment['payment_amount'], 2) ?></td>
                                <td class="amount <?= $payment['pending_amount'] > 0 ? 'negative' : '' ?>">LKR <?= number_format($payment['pending_amount'], 2) ?></td>
                                <td><?= $payment['no_of_pax'] ?? '-' ?></td>
                                <td>
                                    <?php
                                    if ($payment['pending_amount'] == 0) {
                                        echo '<span class="status-badge status-paid">Paid</span>';
                                    } elseif ($payment['payment_amount'] > 0) {
                                        echo '<span class="status-badge status-partial">Partial</span>';
                                    } else {
                                        echo '<span class="status-badge status-pending">Pending</span>';
                                    }
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($payment['issued_by'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($payments)): ?>
            <div class="export-buttons">
                <button onclick="window.print()" class="btn">Print Report</button>
                <button onclick="exportToCSV()" class="btn">Export CSV</button>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function toggleDateInputs() {
            const reportType = document.getElementById('report_type').value;
            const inputs = ['daily_input', 'monthly_input', 'yearly_input', 'custom_from', 'custom_to'];
            
            inputs.forEach(id => {
                document.getElementById(id).style.display = 'none';
            });
            
            switch(reportType) {
                case 'daily':
                    document.getElementById('daily_input').style.display = 'flex';
                    break;
                case 'monthly':
                    document.getElementById('monthly_input').style.display = 'flex';
                    break;
                case 'yearly':
                    document.getElementById('yearly_input').style.display = 'flex';
                    break;
                case 'custom':
                    document.getElementById('custom_from').style.display = 'flex';
                    document.getElementById('custom_to').style.display = 'flex';
                    break;
            }
        }
        
        function exportToCSV() {
            const table = document.querySelector('.data-table');
            if (!table) return;
            
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = [];
                const cols = rows[i].querySelectorAll('td, th');
                
                for (let j = 0; j < cols.length; j++) {
                    let text = cols[j].innerText.replace(/[\n\r]/g, ' ').trim();
                    row.push('"' + text.replace(/"/g, '""') + '"');
                }
                
                csv.push(row.join(','));
            }
            
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'payment_report_' + new Date().toISOString().split('T')[0] + '.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }
        
        // Initialize date inputs on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleDateInputs();
            
            // Set default dates if not set
            const today = new Date().toISOString().split('T')[0];
            const thisMonth = new Date().toISOString().slice(0, 7);
            const thisYear = new Date().getFullYear();
            
            if (!document.getElementById('specific_date').value) {
                document.getElementById('specific_date').value = today;
            }
            if (!document.getElementById('month_year').value) {
                document.getElementById('month_year').value = thisMonth;
            }
            if (!document.getElementById('year').value) {
                document.getElementById('year').value = thisYear;
            }
        });
    </script>
</body>
</html>