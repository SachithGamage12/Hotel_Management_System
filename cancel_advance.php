<?php
// ================================================
// DATABASE CONNECTION
// ================================================
$host     = 'localhost';
$dbname   = 'hotelgrandguardi_wedding_bliss';
$username = 'hotelgrandguardi_root';
$password = 'Sun123flower@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Database connection failed. Please try again later.");
}

// ================================================
// SAFELY ADD is_cancelled COLUMN (works on very old MySQL too)
// ================================================
try {
    $pdo->query("ALTER TABLE advance_payments ADD COLUMN is_cancelled TINYINT(1) DEFAULT 0");
} catch (Exception $e) {
    // Column probably already exists → we simply ignore the error
}

// ================================================
// CANCEL BILL (AJAX)
// ================================================
if (isset($_POST['cancel_bill'])) {
    header('Content-Type: application/json');
    $id = (int)$_POST['invoice_id'];

    try {
        $stmt = $pdo->prepare("UPDATE advance_payments SET is_cancelled = 1 WHERE id = ? AND is_cancelled = 0");
        $stmt->execute([$id]);

        $success = $stmt->rowCount() > 0;
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Bill cancelled successfully' : 'Already cancelled or not found'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    exit;
}

// ================================================
// MAIN LIST
// ================================================
$search = trim($_GET['search'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 15;
$offset = ($page - 1) * $limit;

$sqlWhere = "WHERE is_cancelled = 0";
$bind = [];

if ($search !== '') {
    $like = "%$search%";
    $sqlWhere .= " AND (invoice_number LIKE ? OR guest_name LIKE ? OR email LIKE ? OR issued_by LIKE ?)";
    $bind = [$like, $like, $like, $like];
}

// Total rows
$count = $pdo->prepare("SELECT COUNT(*) FROM advance_payments $sqlWhere");
$count->execute($bind);
$totalRows = $count->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Data
$sql = "SELECT id, invoice_number, guest_name, email, total_amount, advance_payment, issued_by, payment_date
        FROM advance_payments $sqlWhere
        ORDER BY payment_date DESC
        LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($bind);
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

function money($n) { return 'Rs. ' . number_format((float)$n, 2); }
function niceDate($d) { return $d ? date('d-m-Y H:i', strtotime($d)) : '—'; }
?>

<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8">
<title>Cancel Advance Bills</title>
<style>
    body {font-family:Arial,sans-serif; background:#f4f6f9; margin:0; padding:20px;}
    .box {max-width:1350px; margin:auto; background:#fff; border-radius:10px; box-shadow:0 5px 20px rgba(0,0,0,0.1); overflow:hidden;}
    header {background:#dc3545; color:#fff; padding:20px; text-align:center;}
    .controls {padding:20px; display:flex; gap:10px; flex-wrap:wrap;}
    input {flex:1; padding:10px; border:1px solid #ddd; border-radius:5px; min-width:220px;}
    .btn {padding:10px 20px; border:none; border-radius:5px; color:#fff; cursor:pointer;}
    .btn-info {background:#17a2b8;}
    .btn-gray {background:#6c757d;}
    .btn-red {background:#dc3545;}
    table {width:100%; border-collapse:collapse;}
    th {background:#343a40; color:#fff; padding:12px; text-align:left;}
    td {padding:10px 12px; border-bottom:1px solid #eee;}
    .amt {text-align:right;}
    .pagination {padding:20px; text-align:center;}
    .pagination a, .pagination span {padding:8px 14px; margin:0 4px; border:1px solid #ddd; border-radius:4px; text-decoration:none;}
    .current {background:#dc3545; color:#fff;}
</style>
</head>
<body>
<div class="box">
    <header><h1>Cancel Advance Payment Bills</h1></header>
 <div style="position:absolute;top:20px;left:20px;">
        <button onclick="window.location.href='Frontoffice.php'" 
                style="background:#f09424;color:white;padding:10px 20px;border:none;border-radius:5px;cursor:pointer;">
            Back 
        </button>
    </div>
    <div class="controls">
        <input type="text" id="s" placeholder="Search invoice / name / email..." value="<?=htmlspecialchars($search)?>">
        <button class="btn btn-info" onclick="go()">Search</button>
        <a href="cancel_bill.php" class="btn btn-gray">Reset</a>
    </div>

    <?php if (!$invoices): ?>
        <p style="text-align:center; padding:50px; color:#666;">No active bills found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Invoice</th><th>Guest Name</th><th>Email</th>
                    <th>Total</th><th>Advance</th><th>Issued By</th><th>Date</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $r): ?>
                <tr>
                    <td><strong><?=htmlspecialchars($r['invoice_number'])?></strong></td>
                    <td><?=htmlspecialchars($r['guest_name'])?></td>
                    <td><?=htmlspecialchars($r['email'] ?: '-')?></td>
                    <td class="amt"><?=money($r['total_amount'])?></td>
                    <td class="amt"><?=money($r['advance_payment'])?></td>
                    <td><?=htmlspecialchars($r['issued_by'])?></td>
                    <td><?=niceDate($r['payment_date'])?></td>
                    <td><button class="btn btn-red" onclick="cancelBill(<?=$r['id']?>)">Cancel</button></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?=$page-1?>&search=<?=urlencode($search)?>">Previous</a>
            <?php endif; ?>

            <?php for ($i = max(1,$page-3); $i <= min($totalPages,$page+3); $i++): ?>
                <?php if ($i==$page): ?>
                    <span class="current"><?=$i?></span>
                <?php else: ?>
                    <a href="?page=<?=$i?>&search=<?=urlencode($search)?>"><?=$i?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?=$page+1?>&search=<?=urlencode($search)?>">Next</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
function go() {
    const term = document.getElementById('s').value.trim();
    location = term ? '?search=' + encodeURIComponent(term) : '?';
}
function cancelBill(id) {
    if (!confirm('Cancel this bill permanently?')) return;
    const d = new FormData();
    d.append('cancel_bill', '1');
    d.append('invoice_id', id);
    fetch('', {method:'POST', body:d})
        .then(r => r.json())
        .then(j => {
            alert(j.success ? 'Cancelled!' : j.message);
            if (j.success) location.reload();
        });
}
</script>
</body>
</html>