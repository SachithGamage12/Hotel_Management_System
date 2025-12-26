<?php
// Database connection - UPDATE THESE CREDENTIALS FOR YOUR DATABASE
$host = 'localhost';          // Your database host
$dbname = 'hotelgrandguardi_wedding_bliss'; // Your database name
$username = 'hotelgrandguardi_root';   // Your database username
$password = 'Sun123flower@';   // Your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    // Build query with filters for export
    $whereConditions = [];
    $params = [];
    
    if (!empty($_GET['search'])) {
        $searchTerm = '%' . $_GET['search'] . '%';
        $whereConditions[] = "(booking_reference LIKE :search OR invoice_number LIKE :search OR issued_by LIKE :search OR nic LIKE :search OR contact_no LIKE :search)";
        $params[':search'] = $searchTerm;
    }
    
    if (!empty($_GET['ac_type'])) {
        $whereConditions[] = "ac_type = :ac_type";
        $params[':ac_type'] = $_GET['ac_type'];
    }
    
    if (!empty($_GET['date_from'])) {
        $whereConditions[] = "DATE(payment_date) >= :date_from";
        $params[':date_from'] = $_GET['date_from'];
    }
    
    if (!empty($_GET['date_to'])) {
        $whereConditions[] = "DATE(payment_date) <= :date_to";
        $params[':date_to'] = $_GET['date_to'];
    }
    
    if (!empty($_GET['payment_status'])) {
        switch ($_GET['payment_status']) {
            case 'paid':
                $whereConditions[] = "(SUM(COALESCE(pending_amount, 0)) <= 0)";
                break;
            case 'pending':
                $whereConditions[] = "(SUM(COALESCE(pending_amount, 0)) >= SUM(COALESCE(total_amount, 0)) OR SUM(COALESCE(advance_payment, 0)) = 0)";
                break;
            case 'partial':
                $whereConditions[] = "(SUM(COALESCE(pending_amount, 0)) > 0 AND SUM(COALESCE(pending_amount, 0)) < SUM(COALESCE(total_amount, 0)) AND SUM(COALESCE(advance_payment, 0)) > 0)";
                break;
        }
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    $exportQuery = "
        SELECT 
            booking_reference,
            GROUP_CONCAT(DISTINCT invoice_number SEPARATOR ', ') as invoice_numbers,
            ac_type,
            meal_plan,
            SUM(COALESCE(total_amount, 0)) as total_amount,
            SUM(COALESCE(advance_payment, 0)) as advance_payment,
            (SUM(COALESCE(total_amount, 0)) - SUM(COALESCE(advance_payment, 0))) as pending_amount,
            GROUP_CONCAT(DISTINCT issued_by SEPARATOR ', ') as issued_by,
            GROUP_CONCAT(DISTINCT contact_no SEPARATOR ', ') as contact_no,
            MAX(payment_date) as payment_date,
            GROUP_CONCAT(DISTINCT remarks SEPARATOR '; ') as remarks
        FROM room_payments 
        $whereClause 
        GROUP BY booking_reference, ac_type, meal_plan
        ORDER BY payment_date DESC
    ";
    
    $stmt = $pdo->prepare($exportQuery);
    $stmt->execute($params);
    $exportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Output CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="room_payments_report_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // CSV Headers
    if (!empty($exportData)) {
        fputcsv($output, [
            'Booking Reference',
            'Invoice Numbers',
            'AC Type',
            'Meal Plan',
            'Total Amount',
            'Advance Payment',
            'Pending Amount',
            'Issued By',
            'Contact No',
            'Payment Date',
            'Remarks'
        ]);
        foreach ($exportData as $row) {
            fputcsv($output, [
                $row['booking_reference'],
                $row['invoice_numbers'],
                $row['ac_type'],
                $row['meal_plan'],
                $row['total_amount'],
                $row['advance_payment'],
                $row['pending_amount'],
                $row['issued_by'],
                $row['contact_no'],
                $row['payment_date'],
                $row['remarks']
            ]);
        }
    }
    
    fclose($output);
    exit();
}

// Build WHERE clause for main query
$whereConditions = [];
$params = [];

if (!empty($_GET['search'])) {
    $searchTerm = '%' . $_GET['search'] . '%';
    $whereConditions[] = "(booking_reference LIKE :search OR invoice_number LIKE :search OR issued_by LIKE :search OR nic LIKE :search OR contact_no LIKE :search OR remarks LIKE :search)";
    $params[':search'] = $searchTerm;
}

if (!empty($_GET['ac_type'])) {
    $whereConditions[] = "ac_type = :ac_type";
    $params[':ac_type'] = $_GET['ac_type'];
}

if (!empty($_GET['date_from'])) {
    $whereConditions[] = "DATE(payment_date) >= :date_from";
    $params[':date_from'] = $_GET['date_from'];
}

if (!empty($_GET['date_to'])) {
    $whereConditions[] = "DATE(payment_date) <= :date_to";
    $params[':date_to'] = $_GET['date_to'];
}

if (!empty($_GET['payment_status'])) {
    switch ($_GET['payment_status']) {
        case 'paid':
            $whereConditions[] = "(SUM(COALESCE(pending_amount, 0)) <= 0)";
            break;
        case 'pending':
            $whereConditions[] = "(SUM(COALESCE(pending_amount, 0)) >= SUM(COALESCE(total_amount, 0)) OR SUM(COALESCE(advance_payment, 0)) = 0)";
            break;
        case 'partial':
            $whereConditions[] = "(SUM(COALESCE(pending_amount, 0)) > 0 AND SUM(COALESCE(pending_amount, 0)) < SUM(COALESCE(total_amount, 0)) AND SUM(COALESCE(advance_payment, 0)) > 0)";
            break;
    }
}

// Sorting
$sortField = isset($_GET['sort']) ? $_GET['sort'] : 'payment_date';
$sortDirection = isset($_GET['direction']) && $_GET['direction'] === 'asc' ? 'ASC' : 'DESC';

// Validate sort field to prevent SQL injection
$allowedSortFields = ['booking_reference', 'ac_type', 'total_amount', 'advance_payment', 'pending_amount', 'issued_by', 'payment_date'];
if (!in_array($sortField, $allowedSortFields)) {
    $sortField = 'payment_date';
}

// Adjust sort field for aggregated query
$sortFieldMap = [
    'total_amount' => 'total_amount',
    'advance_payment' => 'advance_payment',
    'pending_amount' => 'pending_amount',
    'booking_reference' => 'booking_reference',
    'ac_type' => 'ac_type',
    'issued_by' => 'issued_by',
    'payment_date' => 'payment_date'
];
$sortField = $sortFieldMap[$sortField] ?? 'payment_date';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 15;
$offset = ($page - 1) * $recordsPerPage;

// Build the main query
$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Get total count for pagination
$countQuery = "
    SELECT COUNT(DISTINCT booking_reference) 
    FROM room_payments 
    $whereClause
";
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $recordsPerPage);

// Main query with aggregation and pagination
$query = "
    SELECT 
        booking_reference,
        GROUP_CONCAT(DISTINCT invoice_number SEPARATOR ', ') as invoice_numbers,
        ac_type,
        meal_plan,
        SUM(COALESCE(total_amount, 0)) as total_amount,
        SUM(COALESCE(advance_payment, 0)) as advance_payment,
        (SUM(COALESCE(total_amount, 0)) - SUM(COALESCE(advance_payment, 0))) as pending_amount,
        GROUP_CONCAT(DISTINCT issued_by SEPARATOR ', ') as issued_by,
        GROUP_CONCAT(DISTINCT contact_no SEPARATOR ', ') as contact_no,
        MAX(payment_date) as payment_date,
        GROUP_CONCAT(DISTINCT remarks SEPARATOR '; ') as remarks
    FROM room_payments 
    $whereClause 
    GROUP BY booking_reference, ac_type, meal_plan
    ORDER BY $sortField $sortDirection 
    LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($query);

// Bind parameters
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$statsQuery = "
    SELECT 
        COUNT(DISTINCT booking_reference) as total_records,
        SUM(COALESCE(total_amount, 0)) as total_amount,
        SUM(COALESCE(advance_payment, 0)) as total_advance,
        SUM(COALESCE(total_amount, 0)) - SUM(COALESCE(advance_payment, 0)) as total_pending
    FROM room_payments 
    $whereClause
";
$statsStmt = $pdo->prepare($statsQuery);
$statsStmt->execute($params);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Get unique AC types for filter dropdown
$acTypesQuery = "SELECT DISTINCT ac_type FROM room_payments WHERE ac_type IS NOT NULL AND ac_type != '' ORDER BY ac_type";
$acTypesStmt = $pdo->prepare($acTypesQuery);
$acTypesStmt->execute();
$acTypes = $acTypesStmt->fetchAll(PDO::FETCH_COLUMN);

// Helper functions
function getPaymentStatus($record) {
    $pending = floatval($record['pending_amount'] ?? 0);
    $total = floatval($record['total_amount'] ?? 0);
    
    if ($pending <= 0) return 'paid';
    if ($pending >= $total) return 'pending';
    return 'partial';
}

function formatCurrency($amount) {
    return 'Rs. ' . number_format(floatval($amount ?? 0), 2);
}

function formatDate($dateString) {
    if (!$dateString) return '-';
    return date('Y-m-d H:i', strtotime($dateString));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Payments Report</title>
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
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .header p {
            opacity: 0.9;
            font-size: 1.1em;
        }

        .controls {
            padding: 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }

        .controls-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: center;
            margin-bottom: 20px;
        }

        .control-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .control-group label {
            font-weight: 600;
            color: #495057;
            font-size: 0.9em;
        }

        .control-group input, 
        .control-group select {
            padding: 10px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            min-width: 150px;
        }

        .control-group input:focus, 
        .control-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: white;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-success {
            background: #28a745;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            border-left: 4px solid;
        }

        .stat-card.total { border-left-color: #007bff; }
        .stat-card.advance { border-left-color: #28a745; }
        .stat-card.pending { border-left-color: #dc3545; }
        .stat-card.count { border-left-color: #17a2b8; }

        .stat-number {
            font-size: 1.8em;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9em;
        }

        .table-container {
            padding: 30px;
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .table th {
            background: linear-gradient(135deg, #495057 0%, #6c757d 100%);
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85em;
            letter-spacing: 0.5px;
            position: relative;
        }

        .table th a {
            color: white;
            text-decoration: none;
            display: block;
        }

        .table th a:hover {
            color: #f8f9fa;
        }

        .table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .table tbody tr:hover {
            background: #e3f2fd;
            transform: scale(1.01);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-paid {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #f8d7da;
            color: #721c24;
        }

        .status-partial {
            background: #fff3cd;
            color: #856404;
        }

        .amount {
            font-weight: 600;
            text-align: right;
        }

        .amount.positive {
            color: #28a745;
        }

        .amount.negative {
            color: #dc3545;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
            padding: 20px;
        }

        .pagination a, .pagination span {
            padding: 8px 16px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            text-decoration: none;
            color: #495057;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }

        .pagination .current {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        @media (max-width: 768px) {
            .controls-row {
                flex-direction: column;
                align-items: stretch;
            }

            .control-group input,
            .control-group select {
                min-width: auto;
                width: 100%;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .table-container {
                padding: 15px;
            }

            .table {
                font-size: 0.85em;
            }

            .table th,
            .table td {
                padding: 8px 6px;
            }
        }

        @media print {
            .controls, .pagination { display: none !important; }
            .container { box-shadow: none; }
            body { background: white !important; }
        }
    </style>
</head>
<body>
 <div class="wrapper">
        <div style="position: absolute; top: 20px; left: 20px;">
           <style>
@media print {
  .no-print {
    display: none !important;
  }
}
</style>

<button onclick="window.location.href='../audit.php'" 
        class="no-print"
        style="background-color: #f09424ff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
    Back
</button>
    <div class="container">
        
        <div class="header">
            
            <h1>🏨 Room Payments Report</h1>
            <p>Comprehensive booking and payment management system</p>
        </div>

        <div class="controls">
            <form method="GET" action="">
                <div class="controls-row">
                    <div class="control-group">
                        <label for="search">Search</label>
                        <input type="text" id="search" name="search" placeholder="Booking ref, invoice, NIC..." 
                               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    </div>
                    
                    <div class="control-group">
                        <label for="ac_type">AC Type</label>
                        <select id="ac_type" name="ac_type">
                            <option value="">All Types</option>
                            <?php foreach ($acTypes as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>" 
                                        <?php echo (isset($_GET['ac_type']) && $_GET['ac_type'] == $type) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="control-group">
                        <label for="payment_status">Payment Status</label>
                        <select id="payment_status" name="payment_status">
                            <option value="">All Status</option>
                            <option value="paid" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] == 'paid') ? 'selected' : ''; ?>>Fully Paid</option>
                            <option value="pending" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="partial" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] == 'partial') ? 'selected' : ''; ?>>Partial</option>
                        </select>
                    </div>

                    <div class="control-group">
                        <label for="date_from">Date From</label>
                        <input type="date" id="date_from" name="date_from" 
                               value="<?php echo htmlspecialchars($_GET['date_from'] ?? ''); ?>">
                    </div>

                    <div class="control-group">
                        <label for="date_to">Date To</label>
                        <input type="date" id="date_to" name="date_to" 
                               value="<?php echo htmlspecialchars($_GET['date_to'] ?? ''); ?>">
                    </div>
                </div>

                <div class="controls-row">
                    <button type="submit" class="btn btn-primary">🔍 Filter</button>
                    <a href="?" class="btn btn-secondary">🔄 Reset</a>
                    <a href="?export=csv&<?php echo http_build_query(array_filter($_GET)); ?>" class="btn btn-success">📊 Export CSV</a>
                    <button type="button" class="btn btn-secondary" onclick="window.print()">🖨️ Print</button>
                </div>
            </form>

            <div class="stats">
                <div class="stat-card total">
                    <div class="stat-number"><?php echo formatCurrency($stats['total_amount']); ?></div>
                    <div class="stat-label">Total Amount</div>
                </div>
                <div class="stat-card advance">
                    <div class="stat-number"><?php echo formatCurrency($stats['total_advance']); ?></div>
                    <div class="stat-label">Advance Paid</div>
                </div>
                <div class="stat-card pending">
                    <div class="stat-number"><?php echo formatCurrency($stats['total_pending']); ?></div>
                    <div class="stat-label">Pending Amount</div>
                </div>
                <div class="stat-card count">
                    <div class="stat-number"><?php echo $stats['total_records']; ?></div>
                    <div class="stat-label">Total Records</div>
                </div>
            </div>
        </div>

        <div class="table-container">
            <?php if (empty($payments)): ?>
                <div class="no-data">
                    <div style="font-size: 4em; margin-bottom: 20px; opacity: 0.3;">📋</div>
                    <h3>No Records Found</h3>
                    <p>Try adjusting your filters or search criteria</p>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'booking_reference', 'direction' => ($sortField == 'booking_reference' && $sortDirection == 'ASC') ? 'desc' : 'asc'])); ?>">Booking Ref</a></th>
                            <th>Invoice(s)</th>
                            <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'ac_type', 'direction' => ($sortField == 'ac_type' && $sortDirection == 'ASC') ? 'desc' : 'asc'])); ?>">AC Type</a></th>
                            <th>Meal Plan</th>
                            <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'total_amount', 'direction' => ($sortField == 'total_amount' && $sortDirection == 'ASC') ? 'desc' : 'asc'])); ?>">Total Amount</a></th>
                            <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'advance_payment', 'direction' => ($sortField == 'advance_payment' && $sortDirection == 'ASC') ? 'desc' : 'asc'])); ?>">Advance</a></th>
                            <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'pending_amount', 'direction' => ($sortField == 'pending_amount' && $sortDirection == 'ASC') ? 'desc' : 'asc'])); ?>">Pending</a></th>
                            <th>Status</th>
                            <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'issued_by', 'direction' => ($sortField == 'issued_by' && $sortDirection == 'ASC') ? 'desc' : 'asc'])); ?>">Issued By</a></th>
                            <th>Contact</th>
                            <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'payment_date', 'direction' => ($sortField == 'payment_date' && $sortDirection == 'ASC') ? 'desc' : 'asc'])); ?>">Payment Date</a></th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <?php $status = getPaymentStatus($payment); ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($payment['booking_reference']); ?></strong></td>
                                <td><?php echo htmlspecialchars($payment['invoice_numbers'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($payment['ac_type'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($payment['meal_plan'] ?: '-'); ?></td>
                                <td class="amount positive"><?php echo formatCurrency($payment['total_amount']); ?></td>
                                <td class="amount"><?php echo formatCurrency($payment['advance_payment']); ?></td>
                                <td class="amount <?php echo floatval($payment['pending_amount']) > 0 ? 'negative' : ''; ?>"><?php echo formatCurrency($payment['pending_amount']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $status; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($payment['issued_by'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($payment['contact_no'] ?: '-'); ?></td>
                                <td><?php echo formatDate($payment['payment_date']); ?></td>
                                <td><?php echo htmlspecialchars($payment['remarks'] ? (strlen($payment['remarks']) > 50 ? substr($payment['remarks'], 0, 50) . '...' : $payment['remarks']) : '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Previous</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Room Details Modal -->
    

    <script>
        function showRoomDetails(bookingRef) {
            const modal = document.getElementById('roomModal');
            const modalBody = document.getElementById('modalBody');
            
            // Show modal with loading
            modal.style.display = 'block';
            modalBody.innerHTML = `
                <div class="loading-modal">
                    <div class="loading-spinner"></div>
                    <p>Loading details...</p>
                </div>
            `;
            
            // Fetch room details via AJAX
            fetch(`?action=get_room_details&booking_ref=${encodeURIComponent(bookingRef)}`)
                .then(response => response.json())
                .then(data => {
                    displayRoomDetails(data, bookingRef);
                })
                .catch(error => {
                    console.error('Error fetching room details:', error);
                    modalBody.innerHTML = `
                        <div class="no-details">
                            <div class="icon">❌</div>
                            <h3>Error Loading Details</h3>
                            <p>Unable to fetch room details. Please try again.</p>
                        </div>
                    `;
                });
        }

        function displayRoomDetails(data, bookingRef) {
            const modalBody = document.getElementById('modalBody');
            
            if (!data.guest_data && !data.room_booking_data) {
                modalBody.innerHTML = `
                    <div class="no-details">
                        <div class="icon">📋</div>
                        <h3>No Details Found</h3>
                        <p>No room or guest details found for booking reference: <strong>${bookingRef}</strong></p>
                    </div>
                `;
                return;
            }

            let html = '';

            if (data.guest_data) {
                const guest = data.guest_data;
                const rooms = guest.rooms ? JSON.parse(guest.rooms) : [];
                
                html += `
                    <div class="detail-section">
                        <h3>👤 Guest Information</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <div class="detail-label">GRC Number</div>
                                <div class="detail-value highlight">${guest.grc_number || '-'}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Guest Name</div>
                                <div class="detail-value highlight">${guest.guest_name || '-'}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Contact Number</div>
                                <div class="detail-value">${guest.contact_number || '-'}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Email</div>
                                <div class="detail-value">${guest.email || '-'}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">ID Type</div>
                                <div class="detail-value">${guest.id_type || '-'}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">ID Number</div>
                                <div class="detail-value">${guest.id_number || '-'}</div>
                            </div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3>🏠 Accommodation Details</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <div class="detail-label">Check-in Date</div>
                                <div class="detail-value highlight">${guest.check_in_date || '-'}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Check-in Time</div>
                                <div class="detail-value">${guest.check_in_time || '-'} ${guest.check_in_time_am_pm || ''}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Check-out Date</div>
                                <div class="detail-value highlight">${guest.check_out_date || '-'}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Check-out Time</div>
                                <div class="detail-value">${guest.check_out_time || '-'} ${guest.check_out_time_am_pm || ''}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Number of Pax</div>
                                <div class="detail-value">${guest.number_of_pax || '-'}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Meal Plan</div>
                                <div class="detail-value">${guest.meal_plan_name || '-'}</div>
                            </div>
                        </div>
                        ${rooms.length > 0 ? `
                            <div style="margin-top: 15px;">
                                <div class="detail-label">Rooms Assigned</div>
                                <div class="rooms-list">
                                    ${rooms.map(room => `<span class="room-tag">Room ${room}</span>`).join('')}
                                </div>
                            </div>
                        ` : ''}
                    </div>

                    ${guest.address ? `
                        <div class="detail-section">
                            <h3>📍 Address</h3>
                            <div class="detail-item">
                                <div class="detail-value">${guest.address}</div>
                            </div>
                        </div>
                    ` : ''}

                    ${guest.remarks ? `
                        <div class="detail-section">
                            <h3>📝 Remarks</h3>
                            <div class="detail-item">
                                <div class="detail-value">${guest.remarks}</div>
                            </div>
                        </div>
                    ` : ''}
                `;
            } else if (data.room_booking_data) {
                const booking = data.room_booking_data;
                
                html += `
                    <div class="detail-section">
                        <h3>👤 Guest Information</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <div class="detail-label">Booking ID</div>
                                <div class="detail-value highlight">${booking.id || '-'}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Guest Name</div>
                                <div class="detail-value highlight">${booking.guest_name || '-'}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Telephone</div>
                                <div class="detail-value">${booking.telephone || '-'}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Number of Pax</div>
                                <div class="detail-value">${booking.pax || '-'}</div>
                            </div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3>🏠 Room & Booking Details</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <div class="detail-label">Room Number</div>
                                <div class="detail-value highlight">${booking.room_number || '-'}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Room Type</div>
                                <div class="detail-value">${booking.room_type || '-'}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">AC Type</div>
                                <div class="detail-value">${booking.ac_type || '-'}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Check-in Date</div>
                                <div class="detail-value highlight">${booking.check_in || '-'}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Check-out Date</div>
                                <div class="detail-value highlight">${booking.check_out || '-'}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Booking Created</div>
                                <div class="detail-value">${booking.created_at || '-'}</div>
                            </div>
                        </div>
                    </div>

                    ${booking.function_type ? `
                        <div class="detail-section">
                            <h3>🎉 Function Details</h3>
                            <div class="detail-item">
                                <div class="detail-label">Function Type</div>
                                <div class="detail-value highlight">${booking.function_type}</div>
                            </div>
                        </div>
                    ` : ''}

                    ${booking.remarks ? `
                        <div class="detail-section">
                            <h3>📝 Remarks</h3>
                            <div class="detail-item">
                                <div class="detail-value">${booking.remarks}</div>
                            </div>
                        </div>
                    ` : ''}
                `;
            }

            modalBody.innerHTML = html;
        }

        function closeModal() {
            document.getElementById('roomModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('roomModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>