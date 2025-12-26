<?php
// Database configuration
$host = 'localhost';
$dbname = 'hotelgrandguardi_wedding_bliss';
$username = 'hotelgrandguardi_root';
$password = 'Sun123flower@';

$pdo = null;
$reports_data = [];
$total_amount = 0;
$search_params = [];
$error_message = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle search and filtering
$where_conditions = [];
$params = [];

if ($_POST || $_GET) {
    // Booking Code Search
    if (!empty($_REQUEST['booking_code'])) {
        $where_conditions[] = "booking_code LIKE ?";
        $params[] = "%" . $_REQUEST['booking_code'] . "%";
        $search_params['booking_code'] = $_REQUEST['booking_code'];
    }
    
    // Date Range Filters
    if (!empty($_REQUEST['date_from'])) {
        $where_conditions[] = "request_date >= ?";
        $params[] = $_REQUEST['date_from'];
        $search_params['date_from'] = $_REQUEST['date_from'];
    }
    
    if (!empty($_REQUEST['date_to'])) {
        $where_conditions[] = "request_date <= ?";
        $params[] = $_REQUEST['date_to'];
        $search_params['date_to'] = $_REQUEST['date_to'];
    }
    
    // Daily Filter
    if (!empty($_REQUEST['daily_date'])) {
        $where_conditions[] = "DATE(request_date) = ?";
        $params[] = $_REQUEST['daily_date'];
        $search_params['daily_date'] = $_REQUEST['daily_date'];
    }
    
    // Monthly Filter
    if (!empty($_REQUEST['monthly_filter'])) {
        $month_year = explode('-', $_REQUEST['monthly_filter']);
        $where_conditions[] = "YEAR(request_date) = ? AND MONTH(request_date) = ?";
        $params[] = $month_year[0];
        $params[] = $month_year[1];
        $search_params['monthly_filter'] = $_REQUEST['monthly_filter'];
    }
    
    // Yearly Filter
    if (!empty($_REQUEST['yearly_filter'])) {
        $where_conditions[] = "YEAR(request_date) = ?";
        $params[] = $_REQUEST['yearly_filter'];
        $search_params['yearly_filter'] = $_REQUEST['yearly_filter'];
    }
    
    // Supplier Name Filter
    if (!empty($_REQUEST['supplier_name'])) {
        $where_conditions[] = "supplier_name LIKE ?";
        $params[] = "%" . $_REQUEST['supplier_name'] . "%";
        $search_params['supplier_name'] = $_REQUEST['supplier_name'];
    }
    
    // Customer Name Filter
    if (!empty($_REQUEST['customer_name'])) {
        $where_conditions[] = "customer_name LIKE ?";
        $params[] = "%" . $_REQUEST['customer_name'] . "%";
        $search_params['customer_name'] = $_REQUEST['customer_name'];
    }
}

// Build the main query
$sql = "SELECT * FROM supplier_payments";
if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}
$sql .= " ORDER BY request_date DESC, created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reports_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate total amount
    foreach ($reports_data as $row) {
        $total_amount += floatval($row['amount']);
    }
} catch(PDOException $e) {
    $error_message = "Error fetching data: " . $e->getMessage();
}

// Function to format Sri Lankan Rupees
function formatLKR($amount) {
    return "Rs. " . number_format($amount, 2);
}

// Function to get summary statistics
function getSummaryStats($pdo, $where_conditions = [], $params = []) {
    $sql = "SELECT 
                COUNT(*) as total_records,
                SUM(amount) as total_amount,
                AVG(amount) as average_amount,
                MIN(amount) as min_amount,
                MAX(amount) as max_amount
            FROM supplier_payments";
    
    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(" AND ", $where_conditions);
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$summary_stats = getSummaryStats($pdo, $where_conditions, $params);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Payments Report</title>
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
            background: linear-gradient(135deg, #2c3e50 0%, #4a6741 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .filters-section {
            padding: 30px;
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .filter-group {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #007bff;
        }
        
        .filter-group h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        
        .filter-group input, .filter-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e9ecef;
            border-radius: 5px;
            font-size: 14px;
            margin-bottom: 10px;
            transition: border-color 0.3s;
        }
        
        .filter-group input:focus, .filter-group select:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            margin: 5px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745, #1e7e34);
            color: white;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ffc107, #e0a800);
            color: #212529;
        }
        
        .btn-info {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
        }
        
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 30px;
        }
        
        .summary-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-top: 4px solid;
        }
        
        .summary-card.total { border-top-color: #28a745; }
        .summary-card.records { border-top-color: #007bff; }
        .summary-card.average { border-top-color: #ffc107; }
        .summary-card.range { border-top-color: #6f42c1; }
        
        .summary-card h3 {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .summary-card .value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .table-container {
            margin: 30px;
            overflow-x: auto;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            font-size: 14px;
        }
        
        .data-table th {
            background: linear-gradient(135deg, #2c3e50, #4a6741);
            color: white;
            padding: 15px 10px;
            text-align: left;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .data-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: top;
        }
        
        .data-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .amount-cell {
            font-weight: bold;
            color: #28a745;
            text-align: right;
        }
        
        .date-cell {
            color: #6c757d;
            font-size: 13px;
        }
        
        .booking-code {
            background: #007bff;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .no-data {
            text-align: center;
            padding: 60px;
            color: #6c757d;
            font-size: 1.2rem;
        }
        
        .alert {
            padding: 15px;
            margin: 20px 30px;
            border-radius: 8px;
            border-left: 4px solid;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        
        .export-section {
            text-align: center;
            padding: 30px;
            background: #f8f9fa;
        }
        
        @media print {
            body { background: white; }
            .filters-section, .export-section { display: none; }
            .container { box-shadow: none; }
        }
        
        @media (max-width: 768px) {
            .filters-grid {
                grid-template-columns: 1fr;
            }
            
            .summary-cards {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .header h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div style="margin-bottom: 1rem;">
  <a
    href="../audit.php"
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
            <h1>📊 Supplier Payments Report</h1>
            <p>Comprehensive reporting system with advanced filtering options</p>
        </div>
        
        <!-- Filters Section -->
        <div class="filters-section">
            <form method="POST" action="">
                <div class="filters-grid">
                    <!-- Booking Code Search -->
                    <div class="filter-group">
                        <h3>🔍 Booking Code Search</h3>
                        <input type="text" name="booking_code" placeholder="Enter booking code..." 
                               value="<?php echo $search_params['booking_code'] ?? ''; ?>">
                    </div>
                    
                    <!-- Date Range -->
                    <div class="filter-group">
                        <h3>📅 Date Range</h3>
                        <input type="date" name="date_from" placeholder="From Date" 
                               value="<?php echo $search_params['date_from'] ?? ''; ?>">
                        <input type="date" name="date_to" placeholder="To Date" 
                               value="<?php echo $search_params['date_to'] ?? ''; ?>">
                    </div>
                    
                    <!-- Daily Filter -->
                    <div class="filter-group">
                        <h3>📆 Daily Report</h3>
                        <input type="date" name="daily_date" 
                               value="<?php echo $search_params['daily_date'] ?? ''; ?>">
                    </div>
                    
                    <!-- Monthly Filter -->
                    <div class="filter-group">
                        <h3>📅 Monthly Report</h3>
                        <input type="month" name="monthly_filter" 
                               value="<?php echo $search_params['monthly_filter'] ?? ''; ?>">
                    </div>
                    
                    <!-- Yearly Filter -->
                    <div class="filter-group">
                        <h3>📊 Yearly Report</h3>
                        <select name="yearly_filter">
                            <option value="">Select Year</option>
                            <?php for($year = date('Y'); $year >= 2020; $year--): ?>
                                <option value="<?php echo $year; ?>" 
                                    <?php echo (isset($search_params['yearly_filter']) && $search_params['yearly_filter'] == $year) ? 'selected' : ''; ?>>
                                    <?php echo $year; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <!-- Supplier Search -->
                    <div class="filter-group">
                        <h3>🏢 Supplier Search</h3>
                        <input type="text" name="supplier_name" placeholder="Supplier name..." 
                               value="<?php echo $search_params['supplier_name'] ?? ''; ?>">
                    </div>
                    
                    <!-- Customer Search -->
                    <div class="filter-group">
                        <h3>👤 Customer Search</h3>
                        <input type="text" name="customer_name" placeholder="Customer name..." 
                               value="<?php echo $search_params['customer_name'] ?? ''; ?>">
                    </div>
                </div>
                
                <div style="text-align: center;">
                    <button type="submit" class="btn btn-primary">🔍 Generate Report</button>
                    <button type="button" onclick="clearFilters()" class="btn btn-warning">🗑️ Clear Filters</button>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-info">🔄 Show All</a>
                    <button type="button" onclick="window.print()" class="btn btn-success">🖨️ Print Report</button>
                </div>
            </form>
        </div>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <strong>Error:</strong> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card total">
                <h3>Total Amount</h3>
                <div class="value"><?php echo formatLKR($summary_stats['total_amount'] ?? 0); ?></div>
            </div>
            
            <div class="summary-card records">
                <h3>Total Records</h3>
                <div class="value"><?php echo number_format($summary_stats['total_records'] ?? 0); ?></div>
            </div>
            
            <div class="summary-card average">
                <h3>Average Amount</h3>
                <div class="value"><?php echo formatLKR($summary_stats['average_amount'] ?? 0); ?></div>
            </div>
            
            <div class="summary-card range">
                <h3>Amount Range</h3>
                <div class="value" style="font-size: 1.2rem;">
                    <?php echo formatLKR($summary_stats['min_amount'] ?? 0); ?> - 
                    <?php echo formatLKR($summary_stats['max_amount'] ?? 0); ?>
                </div>
            </div>
        </div>
        
        <!-- Data Table -->
        <div class="table-container">
            <?php if (empty($reports_data)): ?>
                <div class="no-data">
                    <h3>📋 No Records Found</h3>
                    <p>Try adjusting your search criteria or clear filters to see all records.</p>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Request Date</th>
                            <th>Function Date</th>
                            <th>Booking Code</th>
                            <th>Customer Name</th>
                            <th>Supplier Name</th>
                            <th>Hall/Location</th>
                            <th>Function Type</th>
                            <th>Day/Night</th>
                            <th>Pax</th>
                            <th>Amount (LKR)</th>
                            <th>Officer</th>
                            <th>Time Range</th>
                            <th>Services</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports_data as $index => $row): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td class="date-cell"><?php echo date('Y-m-d', strtotime($row['request_date'])); ?></td>
                            <td class="date-cell"><?php echo date('Y-m-d', strtotime($row['function_date'])); ?></td>
                            <td>
                                <?php if ($row['booking_code']): ?>
                                    <span class="booking-code"><?php echo htmlspecialchars($row['booking_code']); ?></span>
                                <?php else: ?>
                                    <span style="color: #6c757d;">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['customer_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row['supplier_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row['hall_or_location'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row['function_type'] ?? 'N/A'); ?></td>
                            <td><?php echo ucfirst($row['day_or_night'] ?? 'N/A'); ?></td>
                            <td><?php echo number_format($row['pax'] ?? 0); ?></td>
                            <td class="amount-cell"><?php echo formatLKR($row['amount'] ?? 0); ?></td>
                            <td><?php echo htmlspecialchars($row['front_or_back_officer_name'] ?? 'N/A'); ?></td>
                            <td>
                                <?php if ($row['start_time'] || $row['end_time']): ?>
                                    <?php echo ($row['start_time'] ? date('H:i', strtotime($row['start_time'])) : '??') . ' - ' . ($row['end_time'] ? date('H:i', strtotime($row['end_time'])) : '??'); ?>
                                <?php else: ?>
                                    <span style="color: #6c757d;">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small><?php echo htmlspecialchars(substr($row['dj_deco_band_dance_cake_car_other'] ?? 'N/A', 0, 50)); ?><?php echo strlen($row['dj_deco_band_dance_cake_car_other'] ?? '') > 50 ? '...' : ''; ?></small>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Export Section -->
        <div class="export-section">
            <h3>📤 Export Options</h3>
            <button type="button" onclick="exportToCSV()" class="btn btn-success">📊 Export to CSV</button>
            <button type="button" onclick="window.print()" class="btn btn-primary">🖨️ Print Report</button>
        </div>
    </div>
    
    <script>
        // Clear all filters
        function clearFilters() {
            document.querySelectorAll('input, select').forEach(function(element) {
                if (element.type !== 'submit' && element.type !== 'button') {
                    element.value = '';
                }
            });
        }
        
        // Export to CSV
        function exportToCSV() {
            const table = document.querySelector('.data-table');
            if (!table) {
                alert('No data to export!');
                return;
            }
            
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const cols = row.querySelectorAll('td, th');
                let csvRow = [];
                
                for (let j = 0; j < cols.length; j++) {
                    let cellText = cols[j].textContent.trim();
                    // Escape quotes in CSV
                    cellText = cellText.replace(/"/g, '""');
                    csvRow.push('"' + cellText + '"');
                }
                
                csv.push(csvRow.join(','));
            }
            
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            
            if (link.download !== undefined) {
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', 'supplier_payments_report_' + new Date().toISOString().slice(0,10) + '.csv');
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }
        
        // Auto-submit form on filter change for better UX
        document.addEventListener('DOMContentLoaded', function() {
            const filters = document.querySelectorAll('input[type="date"], input[type="month"], select');
            filters.forEach(function(filter) {
                filter.addEventListener('change', function() {
                    // Optional: Auto-submit on change (remove if not desired)
                    // this.form.submit();
                });
            });
        });
    </script>
</body>
</html>