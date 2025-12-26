<?php
// ================================================
// DATABASE CONNECTION
// ================================================
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

// ================================================
// MARK PAID / CREDIT / REFUNDED (UPDATE SINGLE ROW)
// ================================================
if (isset($_GET['action']) && in_array($_GET['action'], ['mark_paid', 'mark_credit', 'mark_refunded']) && !empty($_GET['id'])) {
    header('Content-Type: application/json');
    
    try {
        $id = (int)$_GET['id'];
        $action = $_GET['action'];
        $statusMap = [
            'mark_paid' => 'Paid',
            'mark_credit' => 'Credit',
            'mark_refunded' => 'Refunded'
        ];
        $newStatus = $statusMap[$action];

        // Get current row
        $stmt = $pdo->prepare("SELECT total_amount, advance_payment, payment_status FROM room_payments WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'Record not found']);
            exit;
        }

        $pending = ($row['total_amount'] ?? 0) - ($row['advance_payment'] ?? 0);

        // If marking Paid and pending > 0 → add advance
        if ($newStatus === 'Paid' && $pending > 0) {
            $update = $pdo->prepare("UPDATE room_payments SET advance_payment = advance_payment + ?, payment_status = 'Paid' WHERE id = ?");
            $update->execute([$pending, $id]);
            $newPending = 0;
            $newAdvance = $row['advance_payment'] + $pending;
        } else {
            // Just update status
            $update = $pdo->prepare("UPDATE room_payments SET payment_status = ? WHERE id = ?");
            $update->execute([$newStatus, $id]);
            $newPending = $pending;
            $newAdvance = $row['advance_payment'];
        }

        echo json_encode([
            'success' => true,
            'new_status' => $newStatus,
            'new_pending' => $newPending,
            'new_advance' => $newAdvance
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ================================================
// CSV EXPORT (Per Invoice)
// ================================================
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
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
        $statusMap = ['paid' => 'Paid', 'pending' => 'Pending', 'credit' => 'Credit', 'refunded' => 'Refunded', 'partial' => 'Partial', 'cancel' => 'Cancel'];
        if (isset($statusMap[$_GET['payment_status']])) {
            $whereConditions[] = "payment_status = :pstatus";
            $params[':pstatus'] = $statusMap[$_GET['payment_status']];
        }
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    $exportQuery = "
        SELECT 
            id, booking_reference, invoice_number, ac_type, meal_plan,
            total_amount, advance_payment, (total_amount - advance_payment) as pending_amount,
            issued_by, contact_no, payment_date, remarks, payment_status
        FROM room_payments 
        $whereClause 
        ORDER BY payment_date DESC
    ";
    
    $stmt = $pdo->prepare($exportQuery);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="room_payments_' . date('Y-m-d') . '.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID','Ref','Invoice','AC','Meal','Total','Advance','Pending','Issued','Contact','Date','Remarks','Status']);
    foreach ($data as $row) {
        fputcsv($output, [
            $row['id'],
            $row['booking_reference'],
            $row['invoice_number'],
            $row['ac_type'],
            $row['meal_plan'],
            $row['total_amount'],
            $row['advance_payment'],
            $row['pending_amount'],
            $row['issued_by'],
            $row['contact_no'],
            $row['payment_date'],
            $row['remarks'],
            $row['payment_status']
        ]);
    }
    fclose($output);
    exit;
}

// ================================================
// MAIN REPORT (Per Invoice, No Grouping)
// ================================================
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
    $statusMap = ['paid' => 'Paid', 'pending' => 'Pending', 'credit' => 'Credit', 'refunded' => 'Refunded', 'partial' => 'Partial', 'cancel' => 'Cancel'];
    if (isset($statusMap[$_GET['payment_status']])) {
        $whereConditions[] = "payment_status = :pstatus";
        $params[':pstatus'] = $statusMap[$_GET['payment_status']];
    }
}

$sortField = $_GET['sort'] ?? 'payment_date';
$sortDirection = ($_GET['direction'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

$allowedSort = ['id','booking_reference','invoice_number','ac_type','total_amount','advance_payment','payment_date','payment_status'];
$sortField = in_array($sortField, $allowedSort) ? $sortField : 'payment_date';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM room_payments $whereClause");
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Data - PER INVOICE
$query = "
    SELECT 
        id, booking_reference, invoice_number, ac_type, meal_plan,
        total_amount, advance_payment, (total_amount - advance_payment) as pending_amount,
        issued_by, contact_no, payment_date, remarks, payment_status
    FROM room_payments 
    $whereClause 
    ORDER BY $sortField $sortDirection 
    LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($query);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// COMPREHENSIVE STATISTICS - EXCLUDE CANCELLED INVOICES
$statsWhereConditions = array_merge($whereConditions, ["payment_status != 'Cancel'"]);
$statsWhereClause = !empty($statsWhereConditions) ? 'WHERE ' . implode(' AND ', $statsWhereConditions) : '';

$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_records,
        
        -- Invoice Counts by Payment Status (EXCLUDE CANCELLED)
        COUNT(CASE WHEN payment_status = 'Paid' THEN 1 END) as paid_count,
        COUNT(CASE WHEN payment_status = 'Pending' THEN 1 END) as pending_count,
        COUNT(CASE WHEN payment_status = 'Partial' THEN 1 END) as partial_count,
        COUNT(CASE WHEN payment_status = 'Credit' THEN 1 END) as credit_count,
        COUNT(CASE WHEN payment_status = 'Refunded' THEN 1 END) as refunded_count,
        COUNT(CASE WHEN payment_status = 'Cancel' THEN 1 END) as cancel_count,
        
        -- Total Amounts by Payment Status (EXCLUDE CANCELLED)
        SUM(CASE WHEN payment_status = 'Paid' THEN COALESCE(total_amount, 0) ELSE 0 END) as total_paid_amount,
        SUM(CASE WHEN payment_status = 'Pending' THEN COALESCE(total_amount, 0) ELSE 0 END) as total_pending_amount,
        SUM(CASE WHEN payment_status = 'Partial' THEN COALESCE(total_amount, 0) ELSE 0 END) as total_partial_amount,
        SUM(CASE WHEN payment_status = 'Credit' THEN COALESCE(total_amount, 0) ELSE 0 END) as total_credit_amount,
        SUM(CASE WHEN payment_status = 'Refunded' THEN COALESCE(total_amount, 0) ELSE 0 END) as total_refunded_amount,
        
        -- Advance Amounts by Payment Status (EXCLUDE CANCELLED)
        SUM(CASE WHEN payment_status = 'Paid' THEN COALESCE(advance_payment, 0) ELSE 0 END) as advance_paid,
        SUM(CASE WHEN payment_status = 'Pending' THEN COALESCE(advance_payment, 0) ELSE 0 END) as advance_pending,
        SUM(CASE WHEN payment_status = 'Partial' THEN COALESCE(advance_payment, 0) ELSE 0 END) as advance_partial,
        SUM(CASE WHEN payment_status = 'Credit' THEN COALESCE(advance_payment, 0) ELSE 0 END) as advance_credit,
        SUM(CASE WHEN payment_status = 'Refunded' THEN COALESCE(advance_payment, 0) ELSE 0 END) as advance_refunded,
        
        -- Pending Balance ONLY from Pending & Partial status (EXCLUDE CANCELLED)
        SUM(CASE 
            WHEN payment_status IN ('Pending', 'Partial') 
            THEN GREATEST(COALESCE(total_amount - advance_payment, 0), 0)
            ELSE 0 
        END) as actual_pending_balance,
        
        -- Total amounts for reference (EXCLUDE CANCELLED)
        SUM(COALESCE(total_amount, 0)) as total_invoice_value,
        SUM(COALESCE(advance_payment, 0)) as total_advance_collected
        
    FROM room_payments 
    $statsWhereClause
");
$statsStmt->execute($params);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Calculate net collection based ONLY on payment status (EXCLUDE CANCELLED)
$netCollection = ($stats['advance_paid'] ?? 0) + ($stats['advance_partial'] ?? 0) - ($stats['advance_refunded'] ?? 0);

// Get cancelled count separately
$cancelStmt = $pdo->prepare("SELECT COUNT(*) as cancel_count FROM room_payments WHERE payment_status = 'Cancel'");
$cancelStmt->execute();
$cancelCount = $cancelStmt->fetchColumn();

// AC Types
$acTypes = $pdo->query("SELECT DISTINCT ac_type FROM room_payments WHERE ac_type IS NOT NULL AND ac_type != '' ORDER BY ac_type")->fetchAll(PDO::FETCH_COLUMN);

// Helpers
function fmt($v) { return 'Rs. ' . number_format(floatval($v ?? 0), 2); }
function fdate($d) { return $d ? date('Y-m-d H:i', strtotime($d)) : '-'; }
function getDisplayStatus($row) {
    return $row['payment_status'] ?? 'Pending';
}
function getStatusClass($status) {
    return 'status-' . strtolower($status);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Payments Report - Payment Status Based</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Segoe UI,Tahoma,sans-serif; background:#667eea; padding:20px; }
        .container { max-width:1600px; margin:auto; background:rgba(255,255,255,.95); border-radius:20px; box-shadow:0 20px 40px rgba(0,0,0,.1); overflow:hidden; }
        .header { background:#2c3e50; color:white; padding:30px; text-align:center; }
        .header h1 { font-size:2.5em; margin-bottom:10px; }
        .controls { padding:30px; background:#f8f9fa; }
        .controls-row { display:flex; flex-wrap:wrap; gap:20px; margin-bottom:20px; }
        .control-group { flex:1; min-width:150px; }
        .control-group label { display:block; font-weight:600; color:#495057; margin-bottom:5px; }
        .control-group input, .control-group select { width:100%; padding:10px 15px; border:2px solid #e9ecef; border-radius:8px; }
        .btn { padding:8px 16px; border:none; border-radius:6px; color:white; cursor:pointer; font-size:0.8em; margin:2px; }
        .btn-success { background:#28a745; }
        .btn-info { background:#17a2b8; }
        .btn-warning { background:#ffc107; color:#212529; }
        .btn-secondary { background:#6c757d; }
        .stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:15px; margin:20px 0; }
        .stat-card { background:white; padding:15px; border-radius:10px; text-align:center; box-shadow:0 3px 10px rgba(0,0,0,.1); border-left:4px solid; }
        .stat-card.paid { border-left-color:#28a745; }
        .stat-card.pending { border-left-color:#dc3545; }
        .stat-card.partial { border-left-color:#fd7e14; }
        .stat-card.credit { border-left-color:#6f42c1; }
        .stat-card.refunded { border-left-color:#e83e8c; }
        .stat-card.cancel { border-left-color:#6c757d; background:#f8f9fa; }
        .stat-card.net { border-left-color:#20c997; background:linear-gradient(135deg,#20c997,#007bff); color:white; }
        .stat-card.total { border-left-color:#007bff; }
        .stat-number { font-size:1.5em; font-weight:bold; }
        .stat-label { font-size:0.85em; opacity:0.9; margin-top:5px; }
        .table-container { padding:30px; overflow-x:auto; }
        .table { width:100%; border-collapse:collapse; background:white; border-radius:12px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,.1); }
        .table th { background:#495057; color:white; padding:12px 10px; text-align:left; font-weight:600; font-size:0.8em; }
        .table th a { color:white; text-decoration:none; }
        .table td { padding:10px; border-bottom:1px solid #e9ecef; font-size:0.9em; }
        .table tbody tr:nth-child(even) { background:#f8f9fa; }
        .table tbody tr:hover { background:#e3f2fd; }
        .status-badge { padding:3px 10px; border-radius:15px; font-size:0.75em; font-weight:600; text-transform:uppercase; }
        .status-pending { background:#f8d7da; color:#721c24; }
        .status-paid { background:#d4edda; color:#155724; }
        .status-partial { background:#fff3cd; color:#856404; }
        .status-credit { background:#d1ecf1; color:#0c5460; }
        .status-refunded { background:#e2e3e5; color:#383d41; }
        .status-cancel { background:#6c757d; color:white; }
        .amount { text-align:right; font-weight:600; }
        .amount.negative { color:#dc3545; }
        .pagination { display:flex; justify-content:center; gap:8px; margin-top:25px; }
        .pagination a, .pagination span { padding:8px 14px; border:1px solid #dee2e6; border-radius:6px; text-decoration:none; color:#495057; font-size:0.9em; }
        .pagination .current { background:#667eea; color:white; }
        .no-data { text-align:center; padding:50px 20px; color:#6c757d; }
        .summary-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:15px; margin:20px 0; }
        .summary-card { background:white; padding:15px; border-radius:10px; box-shadow:0 3px 10px rgba(0,0,0,.1); }
        .summary-card h3 { margin-bottom:10px; color:#495057; font-size:1em; border-bottom:1px solid #e9ecef; padding-bottom:5px; }
        .summary-item { display:flex; justify-content:space-between; padding:5px 0; border-bottom:1px dashed #f8f9fa; }
        .summary-item.total { font-weight:bold; border-top:2px solid #e9ecef; margin-top:5px; padding-top:8px; }
        .status-breakdown { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:10px; margin-top:10px; }
        .status-item { display:flex; justify-content:space-between; padding:5px; background:#f8f9fa; border-radius:5px; }
        @media (max-width:768px) { .controls-row { flex-direction:column; } .table { font-size:0.8em; } }
        @media print { .controls, .pagination, .no-print { display:none !important; } }
    </style>
</head>
<body>
    <div style="position:absolute;top:20px;left:20px;">
        <button onclick="window.location.href='Backoffice.php'" class="no-print"
                style="background:#f09424;color:white;padding:10px 20px;border:none;border-radius:5px;cursor:pointer;">
            Back
        </button>
    </div>

    <div class="container">
        <div class="header">
            <h1>Room Payments Report - Payment Status Based</h1>
            <p>Accurate Financial Overview Based on Actual Payment Status</p>
        </div>

        <div class="controls">
            <form method="GET">
                <div class="controls-row">
                    <div class="control-group">
                        <label>Search</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Ref, invoice, name...">
                    </div>
                    <div class="control-group">
                        <label>AC Type</label>
                        <select name="ac_type">
                            <option value="">All</option>
                            <?php foreach ($acTypes as $t): ?>
                                <option value="<?php echo htmlspecialchars($t); ?>" <?php echo ($_GET['ac_type'] ?? '') == $t ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($t); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="control-group">
                        <label>Status</label>
                        <select name="payment_status">
                            <option value="">All</option>
                            <option value="paid" <?php echo ($_GET['payment_status'] ?? '') == 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="pending" <?php echo ($_GET['payment_status'] ?? '') == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="partial" <?php echo ($_GET['payment_status'] ?? '') == 'partial' ? 'selected' : ''; ?>>Partial</option>
                            <option value="credit" <?php echo ($_GET['payment_status'] ?? '') == 'credit' ? 'selected' : ''; ?>>Credit</option>
                            <option value="refunded" <?php echo ($_GET['payment_status'] ?? '') == 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                            <option value="cancel" <?php echo ($_GET['payment_status'] ?? '') == 'cancel' ? 'selected' : ''; ?>>Cancel</option>
                        </select>
                    </div>
                    <div class="control-group">
                        <label>From</label>
                        <input type="date" name="date_from" value="<?php echo $_GET['date_from'] ?? ''; ?>">
                    </div>
                    <div class="control-group">
                        <label>To</label>
                        <input type="date" name="date_to" value="<?php echo $_GET['date_to'] ?? ''; ?>">
                    </div>
                </div>
                <div class="controls-row">
                    <button type="submit" class="btn btn-success">Filter</button>
                    <a href="?" class="btn btn-secondary">Reset</a>
                    <a href="?export=csv&<?php echo http_build_query(array_filter($_GET)); ?>" class="btn btn-info">Export CSV</a>
                    <button type="button" class="btn btn-warning" onclick="window.print()">Print</button>
                </div>
            </form>

            <!-- Payment Status Based Statistics - EXCLUDE CANCELLED -->
            <div class="stats">
                <div class="stat-card paid">
                    <div class="stat-number"><?php echo fmt($stats['total_paid_amount']); ?></div>
                    <div class="stat-label">Paid (<?php echo $stats['paid_count'] ?? 0; ?> invoices)</div>
                </div>
                <div class="stat-card pending">
                    <div class="stat-number"><?php echo fmt($stats['total_pending_amount']); ?></div>
                    <div class="stat-label">Pending (<?php echo $stats['pending_count'] ?? 0; ?> invoices)</div>
                </div>
                <div class="stat-card partial">
                    <div class="stat-number"><?php echo fmt($stats['total_partial_amount']); ?></div>
                    <div class="stat-label">Partial (<?php echo $stats['partial_count'] ?? 0; ?> invoices)</div>
                </div>
                <div class="stat-card credit">
                    <div class="stat-number"><?php echo fmt($stats['total_credit_amount']); ?></div>
                    <div class="stat-label">Credit (<?php echo $stats['credit_count'] ?? 0; ?> invoices)</div>
                </div>
                <div class="stat-card refunded">
                    <div class="stat-number"><?php echo fmt($stats['total_refunded_amount']); ?></div>
                    <div class="stat-label">Refunded (<?php echo $stats['refunded_count'] ?? 0; ?> invoices)</div>
                </div>
                <div class="stat-card cancel">
                    <div class="stat-number"><?php echo $cancelCount; ?></div>
                    <div class="stat-label">Cancelled Invoices</div>
                </div>
                <div class="stat-card net">
                    <div class="stat-number"><?php echo fmt($netCollection); ?></div>
                    <div class="stat-label">Net Collection</div>
                </div>
                <div class="stat-card total">
                    <div class="stat-number"><?php echo $stats['total_records']; ?></div>
                    <div class="stat-label">Active Invoices</div>
                </div>
            </div>

            <!-- Detailed Summary - EXCLUDE CANCELLED -->
            <div class="summary-grid">
                <div class="summary-card">
                    <h3>Payment Status Breakdown (Active)</h3>
                    <div class="status-breakdown">
                        <div class="status-item">
                            <span>Paid:</span>
                            <span><?php echo $stats['paid_count'] ?? 0; ?> invoices</span>
                        </div>
                        <div class="status-item">
                            <span>Pending:</span>
                            <span><?php echo $stats['pending_count'] ?? 0; ?> invoices</span>
                        </div>
                        <div class="status-item">
                            <span>Partial:</span>
                            <span><?php echo $stats['partial_count'] ?? 0; ?> invoices</span>
                        </div>
                        <div class="status-item">
                            <span>Credit:</span>
                            <span><?php echo $stats['credit_count'] ?? 0; ?> invoices</span>
                        </div>
                        <div class="status-item">
                            <span>Refunded:</span>
                            <span><?php echo $stats['refunded_count'] ?? 0; ?> invoices</span>
                        </div>
                        <div class="status-item" style="background:#e9ecef;">
                            <span><strong>Cancelled:</strong></span>
                            <span><strong><?php echo $cancelCount; ?> invoices</strong></span>
                        </div>
                    </div>
                </div>

                <div class="summary-card">
                    <h3>Advance Collection by Status (Active)</h3>
                    <div class="summary-item">
                        <span>Paid Invoices:</span>
                        <span><?php echo fmt($stats['advance_paid'] ?? 0); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Pending Invoices:</span>
                        <span><?php echo fmt($stats['advance_pending'] ?? 0); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Partial Invoices:</span>
                        <span><?php echo fmt($stats['advance_partial'] ?? 0); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Credit Invoices:</span>
                        <span><?php echo fmt($stats['advance_credit'] ?? 0); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Refunded Invoices:</span>
                        <span><?php echo fmt($stats['advance_refunded'] ?? 0); ?></span>
                    </div>
                    <div class="summary-item total">
                        <span>Total Advance (Active):</span>
                        <span><?php echo fmt($stats['total_advance_collected'] ?? 0); ?></span>
                    </div>
                </div>

                <div class="summary-card">
                    <h3>Financial Summary (Active Invoices)</h3>
                    <div class="summary-item">
                        <span>Total Invoice Value:</span>
                        <span><?php echo fmt($stats['total_invoice_value'] ?? 0); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Total Advance Collected:</span>
                        <span><?php echo fmt($stats['total_advance_collected'] ?? 0); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Actual Pending Balance:</span>
                        <span><?php echo fmt($stats['actual_pending_balance'] ?? 0); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Total Refunded:</span>
                        <span>-<?php echo fmt($stats['total_refunded_amount'] ?? 0); ?></span>
                    </div>
                    <div class="summary-item total">
                        <span>Net Collection:</span>
                        <span><?php echo fmt($netCollection); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-container">
            <?php if (empty($payments)): ?>
                <div class="no-data">
                    <h3>No Invoices Found</h3>
                    <p>Try adjusting filters</p>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ref</th>
                            <th>Invoice</th>
                            <th>AC</th>
                            <th>Meal</th>
                            <th>Total</th>
                            <th>Advance</th>
                            <th>Pending</th>
                            <th>Status</th>
                            <th>Issued</th>
                            <th>Contact</th>
                            <th>Date</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $p): 
                            $pending = floatval($p['pending_amount'] ?? 0);
                            $displayStatus = getDisplayStatus($p);
                            $showPaidBtn = $displayStatus !== 'Paid' && $displayStatus !== 'Partial' && $displayStatus !== 'Cancel';
                            $showCreditBtn = $displayStatus !== 'Credit' && $displayStatus !== 'Cancel';
                            $showRefundBtn = $displayStatus !== 'Refunded' && $displayStatus !== 'Cancel';
                        ?>
                            <tr data-id="<?php echo $p['id']; ?>">
                                <td><?php echo $p['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($p['booking_reference']); ?></strong></td>
                                <td><?php echo htmlspecialchars($p['invoice_number']); ?></td>
                                <td><?php echo htmlspecialchars($p['ac_type'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($p['meal_plan'] ?: '-'); ?></td>
                                <td class="amount"><?php echo fmt($p['total_amount']); ?></td>
                                <td class="amount advance" data-advance="<?php echo $p['advance_payment']; ?>">
                                    <?php echo fmt($p['advance_payment']); ?>
                                </td>
                                <td class="amount pending <?php echo $pending > 0 ? 'negative' : ''; ?>" data-pending="<?php echo $pending; ?>">
                                    <?php echo fmt($pending); ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo getStatusClass($displayStatus); ?>">
                                        <?php echo $displayStatus; ?>
                                    </span>
                                    <div style="margin-top:4px;">
                                        <!-- Show buttons only if NOT cancelled -->
                                        <?php if ($displayStatus !== 'Cancel'): ?>
                                            <?php if ($showPaidBtn): ?>
                                                <button class="btn btn-success mark-paid" data-id="<?php echo $p['id']; ?>">Mark Paid</button>
                                            <?php endif; ?>
                                            <?php if ($showCreditBtn): ?>
                                                <button class="btn btn-info mark-credit" data-id="<?php echo $p['id']; ?>">Credit</button>
                                            <?php endif; ?>
                                            <?php if ($showRefundBtn): ?>
                                                <button class="btn btn-warning mark-refunded" data-id="<?php echo $p['id']; ?>">Refund</button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color:#6c757d; font-size:0.8em;">Cancelled</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($p['issued_by'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($p['contact_no'] ?: '-'); ?></td>
                                <td><?php echo fdate($p['payment_date']); ?></td>
                                <td><?php echo htmlspecialchars(strlen($p['remarks']) > 40 ? substr($p['remarks'],0,40).'...' : ($p['remarks'] ?: '-')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Prev</a>
                        <?php endif; ?>
                        <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
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

    <script>
        function fmt(v) { return 'Rs. ' + Number(v).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }

        document.querySelectorAll('.mark-paid, .mark-credit, .mark-refunded').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const row = this.closest('tr');
                const pendingCell = row.querySelector('.pending');
                const advanceCell = row.querySelector('.advance');
                const pending = parseFloat(pendingCell.dataset.pending) || 0;
                const action = this.classList.contains('mark-paid') ? 'mark_paid' :
                              this.classList.contains('mark-credit') ? 'mark_credit' : 'mark_refunded';
                const label = this.textContent;

                if (!confirm(`Mark invoice #${id} as ${label.toUpperCase()}?`)) return;

                this.disabled = true;
                this.textContent = 'Saving...';

                fetch(`?action=${action}&id=${id}&t=${Date.now()}`)
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            // Reload page to update all statistics
                            window.location.reload();
                        } else {
                            alert(res.message);
                            this.disabled = false;
                            this.textContent = label;
                        }
                    })
                    .catch(err => {
                        alert('Error: ' + err.message);
                        this.disabled = false;
                        this.textContent = label;
                    });
            });
        });
    </script>
</body>
</html>