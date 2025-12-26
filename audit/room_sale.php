<?php
// Database connection
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

// ================ CSV EXPORT ================
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

    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

    $exportQuery = "
        SELECT
            booking_reference,
            GROUP_CONCAT(DISTINCT invoice_number SEPARATOR ', ') AS invoice_numbers,
            ac_type,
            meal_plan,
            SUM(COALESCE(total_amount, 0)) AS total_amount,
            SUM(COALESCE(advance_payment, 0)) AS advance_payment,
            (SUM(COALESCE(total_amount, 0)) - SUM(COALESCE(advance_payment, 0))) AS pending_amount,
            GROUP_CONCAT(DISTINCT issued_by SEPARATOR ', ') AS issued_by,
            GROUP_CONCAT(DISTINCT contact_no SEPARATOR ', ') AS contact_no,
            MAX(payment_date) AS payment_date,
            GROUP_CONCAT(DISTINCT remarks SEPARATOR '; ') AS remarks
        FROM room_payments
        $whereClause
        GROUP BY booking_reference, ac_type, meal_plan
        ORDER BY payment_date DESC
    ";

    $stmt = $pdo->prepare($exportQuery);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="room_payments_report_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Booking Reference','Invoice Numbers','AC Type','Meal Plan','Total Amount','Advance Payment','Pending Amount','Issued By','Contact No','Payment Date','Remarks']);

    foreach ($data as $row) {
        $status = ($row['pending_amount'] <= 0) ? 'Paid' : ((floatval($row['advance_payment']) > 0) ? 'Partial' : 'Pending');
        fputcsv($output, [
            $row['booking_reference'],
            $row['invoice_numbers'],
            $row['ac_type'] ?? '',
            $row['meal_plan'] ?? '',
            number_format($row['total_amount'], 2),
            number_format($row['advance_payment'], 2),
            number_format($row['pending_amount'], 2),
            $row['issued_by'],
            $row['contact_no'],
            date('Y-m-d H:i', strtotime($row['payment_date'])),
            $row['remarks']
        ]);
    }
    fclose($output);
    exit();
}

// ================ MAIN FILTERS & QUERY ================
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

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Payment status filter (applied after aggregation)
$statusFilter = $_GET['payment_status'] ?? '';

// Sorting
$sortField = $_GET['sort'] ?? 'payment_date';
$sortDirection = ($_GET['direction'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
$allowedSort = ['booking_reference','ac_type','total_amount','advance_payment','pending_amount','issued_by','payment_date'];
$sortField = in_array($sortField, $allowedSort) ? $sortField : 'payment_date';

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Total records (distinct booking refs)
$countQuery = "SELECT COUNT(DISTINCT booking_reference, ac_type, meal_plan) FROM room_payments $whereClause";
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Main aggregated query
$mainQuery = "
    SELECT
        booking_reference,
        GROUP_CONCAT(DISTINCT invoice_number SEPARATOR ', ') AS invoice_numbers,
        ac_type,
        meal_plan,
        SUM(COALESCE(total_amount, 0)) AS total_amount,
        SUM(COALESCE(advance_payment, 0)) AS advance_payment,
        (SUM(COALESCE(total_amount, 0)) - SUM(COALESCE(advance_payment, 0))) AS pending_amount,
        GROUP_CONCAT(DISTINCT issued_by SEPARATOR ', ') AS issued_by,
        GROUP_CONCAT(DISTINCT contact_no SEPARATOR ', ') AS contact_no,
        MAX(payment_date) AS payment_date,
        GROUP_CONCAT(DISTINCT remarks SEPARATOR '; ') AS remarks
    FROM room_payments
    $whereClause
    GROUP BY booking_reference, ac_type, meal_plan
    HAVING 1=1
";

if ($statusFilter === 'paid') {
    $mainQuery .= " AND (SUM(COALESCE(total_amount, 0)) - SUM(COALESCE(advance_payment, 0))) <= 0";
} elseif ($statusFilter === 'pending') {
    $mainQuery .= " AND (SUM(COALESCE(advance_payment, 0)) = 0 OR SUM(COALESCE(total_amount, 0)) - SUM(COALESCE(advance_payment, 0))) >= SUM(COALESCE(total_amount, 0)))";
} elseif ($statusFilter === 'partial') {
    $mainQuery .= " AND SUM(COALESCE(advance_payment, 0)) > 0 AND (SUM(COALESCE(total_amount, 0)) - SUM(COALESCE(advance_payment, 0))) > 0 AND (SUM(COALESCE(total_amount, 0)) - SUM(COALESCE(advance_payment, 0))) < SUM(COALESCE(total_amount, 0)))";
}

$mainQuery .= " ORDER BY $sortField $sortDirection LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($mainQuery);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistics
$statsQuery = "
    SELECT
        COUNT(DISTINCT booking_reference, ac_type, meal_plan) AS total_records,
        SUM(COALESCE(total_amount, 0)) AS total_amount,
        SUM(COALESCE(advance_payment, 0)) AS total_advance,
        (SUM(COALESCE(total_amount, 0)) - SUM(COALESCE(advance_payment, 0))) AS total_pending
    FROM room_payments
    $whereClause
";
$statsStmt = $pdo->prepare($statsQuery);
$statsStmt->execute($params);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// AC Types for dropdown
$acTypesStmt = $pdo->query("SELECT DISTINCT ac_type FROM room_payments WHERE ac_type IS NOT NULL AND ac_type != '' ORDER BY ac_type");
$acTypes = $acTypesStmt->fetchAll(PDO::FETCH_COLUMN);

// Helpers
function formatCurrency($amount) {
    return 'Rs. ' . number_format((float)$amount, 2);
}
function formatDate($date) {
    return $date ? date('Y-m-d H:i', strtotime($date)) : '-';
}
function getStatusClass($pending, $total, $advance) {
    if ($pending <= 0) return 'paid';
    if ($advance <= 0) return 'pending';
    return 'partial';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Payments Report</title>
    <style>
        /* [Your existing beautiful CSS – unchanged] */
        /* ... paste your full CSS here ... */
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; min-height: 100vh; }
        .container { max-width: 1500px; margin: 0 auto; background: rgba(255,255,255,0.95); border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); overflow: hidden; }
        /* ... rest of your CSS ... */
        .status-badge { padding: 6px 14px; border-radius: 20px; font-weight: bold; font-size: 0.8em; text-transform: uppercase; }
        .status-paid { background: #d4edda; color: #155724; }
        .status-partial { background: #fff3cd; color: #856404; }
        .status-pending { background: #f8d7da; color: #721c24; }
        .amount { text-align: right; font-weight: 600; }
        .amount.negative { color: #dc3545; }
        .btn { padding: 12px 24px; background: #667eea; color: white; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
        .btn-success { background: #28a745; }
        .btn-secondary { background: #6c757d; }
    </style>
</head>
<body>

<button onclick="window.location.href='../audit.php'" style="position:absolute; top:20px; left:20px; padding:10px 20px; background:#f09424; color:white; border:none; border-radius:5px;">Back</button>

<div class="container">
    <div class="header" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color:white; padding:30px; text-align:center;">
        <h1>Room Payments Report</h1>
        <p>Comprehensive booking and payment overview</p>
    </div>

    <div class="controls" style="padding:30px; background:#f8f9fa;">
        <form method="GET">
            <div style="display:flex; flex-wrap:wrap; gap:15px; margin-bottom:20px;">
                <input type="text" name="search" placeholder="Search..." value="<?=htmlspecialchars($_GET['search']??'')?>" style="padding:10px; border-radius:8px; border:1px solid #ddd; min-width:200px;">
                <select name="ac_type" style="padding:10px; border-radius:8px; border:1px solid #ddd;">
                    <option value="">All AC Types</option>
                    <?php foreach($acTypes as $t): ?>
                        <option value="<?=htmlspecialchars($t)?>" <?=($_GET['ac_type']??'')==$t?'selected':''?>><?=htmlspecialchars($t)?></option>
                    <?php endforeach; ?>
                </select>
                <select name="payment_status" style="padding:10px; border-radius:8px; border:1px solid #ddd;">
                    <option value="">All Status</option>
                    <option value="paid" <?=($_GET['payment_status']??'')==='paid'?'selected':''?>>Paid</option>
                    <option value="partial" <?=($_GET['payment_status']??'')==='partial'?'selected':''?>>Partial</option>
                    <option value="pending" <?=($_GET['payment_status']??'')==='pending'?'selected':''?>>Pending</option>
                </select>
                <input type="date" name="date_from" value="<?=htmlspecialchars($_GET['date_from']??'')?>">
                <input type="date" name="date_to" value="<?=htmlspecialchars($_GET['date_to']??'')?>">
            </div>
            <div>
                <button type="submit" class="btn">Filter</button>
                <a href="?" class="btn btn-secondary">Reset</a>
                <a href="?export=csv&<?=http_build_query(array_filter($_GET))?>" class="btn btn-success">Export CSV</a>
                <button type="button" onclick="window.print()" class="btn btn-secondary">Print</button>
            </div>
        </form>

        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:20px; margin-top:20px;">
            <div style="background:white; padding:20px; border-radius:12px; text-align:center; box-shadow:0 5px 15px rgba(0,0,0,0.1);">
                <div style="font-size:1.8em; font-weight:bold; color:#007bff;"><?=formatCurrency($stats['total_amount']??0)?></div>
                <div style="color:#6c757d;">Total Amount</div>
            </div>
            <div style="background:white; padding:20px; border-radius:12px; text-align:center; box-shadow:0 5px 15px rgba(0,0,0,0.1);">
                <div style="font-size:1.8em; font-weight:bold; color:#28a745;"><?=formatCurrency($stats['total_advance']??0)?></div>
                <div style="color:#6c757d;">Advance Paid</div>
            </div>
            <div style="background:white; padding:20px; border-radius:12px; text-align:center; box-shadow:0 5px 15px rgba(0,0,0,0.1);">
                <div style="font-size:1.8em; font-weight:bold; color:#dc3545;"><?=formatCurrency($stats['total_pending']??0)?></div>
                <div style="color:#6c757d;">Pending</div>
            </div>
            <div style="background:white; padding:20px; border-radius:12px; text-align:center; box-shadow:0 5px 15px rgba(0,0,0,0.1);">
                <div style="font-size:1.8em; font-weight:bold; color:#17a2b8;"><?=$stats['total_records']??0?></div>
                <div style="color:#6c757d;">Bookings</div>
            </div>
        </div>
    </div>

    <div style="padding:30px;">
        <?php if (empty($payments)): ?>
            <div style="text-align:center; padding:80px; color:#6c757d;">
                <h3>No records found</h3>
                <p>Try adjusting your filters</p>
            </div>
        <?php else: ?>
            <table style="width:100%; border-collapse:collapse; background:white; border-radius:12px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.1);">
                <thead style="background:linear-gradient(135deg,#495057,#6c757d); color:white;">
                    <tr>
                        <th style="padding:15px; text-align:left;">Booking Ref</th>
                        <th style="padding:15px; text-align:left;">Invoice(s)</th>
                        <th style="padding:15px; text-align:left;">AC Type</th>
                        <th style="padding:15px; text-align:left;">Meal Plan</th>
                        <th style="padding:15px; text-align:right;">Total</th>
                        <th style="padding:15px; text-align:right;">Advance</th>
                        <th style="padding:15px; text-align:right;">Pending</th>
                        <th style="padding:15px; text-align:center;">Status</th>
                        <th style="padding:15px; text-align:left;">Issued By</th>
                        <th style="padding:15px; text-align:left;">Contact</th>
                        <th style="padding:15px; text-align:left;">Date</th>
                        <th style="padding:15px; text-align:left;">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $p): 
                        $status = getStatusClass($p['pending_amount'], $p['total_amount'], $p['advance_payment']);
                    ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:12px; font-weight:bold;"><?=htmlspecialchars($p['booking_reference'])?></td>
                        <td style="padding:12px;"><?=htmlspecialchars($p['invoice_numbers'] ?: '-')?></td>
                        <td style="padding:12px;"><?=htmlspecialchars($p['ac_type'] ?: '-')?></td>
                        <td style="padding:12px;"><?=htmlspecialchars($p['meal_plan'] ?: '-')?></td>
                        <td class="amount" style="color:#28a745;"><?=formatCurrency($p['total_amount'])?></td>
                        <td class="amount"><?=formatCurrency($p['advance_payment'])?></td>
                        <td class="amount <?= $p['pending_amount']>0 ? 'negative' : '' ?>"><?=formatCurrency($p['pending_amount'])?></td>
                        <td style="padding:12px; text-align:center;">
                            <span class="status-badge status-<?=$status?>">
                                <?=ucfirst($status)?>
                            </span>
                        </td>
                        <td style="padding:12px;"><?=htmlspecialchars($p['issued_by'] ?: '-')?></td>
                        <td style="padding:12px;"><?=htmlspecialchars($p['contact_no'] ?: '-')?></td>
                        <td style="padding:12px;"><?=formatDate($p['payment_date'])?></td>
                        <td style="padding:12px; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            <?=htmlspecialchars(strlen($p['remarks']) > 60 ? substr($p['remarks'],0,60).'...' : ($p['remarks'] ?: '-'))?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div style="text-align:center; margin-top:30px;">
                <?php if($page > 1): ?>
                    <a href="?<?=http_build_query(array_merge($_GET, ['page'=>$page-1]))?>" class="btn btn-secondary">Previous</a>
                <?php endif; ?>
                <?php for($i=1;$i<=$totalPages;$i++): ?>
                    <a href="?<?=http_build_query(array_merge($_GET, ['page'=>$i]))?>" class="<?= $i==$page?'btn':'' ?>" style="<?= $i==$page?'background:#667eea; color:white;':'' ?> padding:8px 12px; margin:0 4px; border:1px solid #ddd; border-radius:6px;">
                        <?=$i?>
                    </a>
                <?php endfor; ?>
                <?php if($page < $totalPages): ?>
                    <a href="?<?=http_build_query(array_merge($_GET, ['page'=>$page+1]))?>" class="btn btn-secondary">Next</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>