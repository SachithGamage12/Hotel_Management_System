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
// ADD CANCEL ENUM VALUE TO PAYMENT_STATUS COLUMN
// ================================================
try {
    $pdo->exec("ALTER TABLE payments MODIFY payment_status ENUM('Pending','Paid','Partial','Credit','Refunded','Cancel') DEFAULT 'Pending'");
} catch (Exception $e) {
    // Continue even if alter fails (might already have the value)
}

// ================================================
// CANCEL BILL FUNCTIONALITY
// ================================================
if (isset($_POST['cancel_bill']) && !empty($_POST['invoice_id'])) {
    header('Content-Type: application/json');
    
    try {
        $invoice_id = (int)$_POST['invoice_id'];
        
        // Update the invoice status to Cancel
        $updateStmt = $pdo->prepare("
            UPDATE payments 
            SET payment_status = 'Cancel'
            WHERE id = ?
        ");
        $updateStmt->execute([$invoice_id]);

        echo json_encode(['success' => true, 'message' => 'Bill cancelled successfully']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// ================================================
// MAIN PAGE - DISPLAY INVOICES
// ================================================
$searchTerm = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Get invoices (exclude already cancelled ones)
$whereConditions = ["payment_status != 'Cancel'"];
$params = [];

if (!empty($searchTerm)) {
    $searchTermLike = '%' . $searchTerm . '%';
    $whereConditions[] = "(invoice_number LIKE :search OR booking_reference LIKE :search OR issued_by LIKE :search OR contact_no LIKE :search OR email LIKE :search)";
    $params[':search'] = $searchTermLike;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM payments $whereClause");
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Get invoices
$query = "
    SELECT id, invoice_number, booking_reference, value_type, 
           total_amount, payment_amount, pending_amount,
           payment_status, issued_by, contact_no, email, payment_date, remarks
    FROM payments 
    $whereClause 
    ORDER BY payment_date DESC 
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($query);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helpers
function fmt($v) { return 'Rs. ' . number_format(floatval($v ?? 0), 2); }
function fdate($d) { return $d ? date('Y-m-d H:i', strtotime($d)) : '-'; }
function getStatusBadge($status) {
    $classes = [
        'Pending' => 'status-pending',
        'Paid' => 'status-paid',
        'Partial' => 'status-partial',
        'Credit' => 'status-credit',
        'Refunded' => 'status-refunded',
        'Cancel' => 'status-cancel'
    ];
    $class = $classes[$status] ?? 'status-pending';
    return "<span class='status-badge $class'>$status</span>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Cancellation System</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Segoe UI,Tahoma,sans-serif; background:#667eea; padding:20px; }
        .container { max-width:1600px; margin:auto; background:white; border-radius:10px; box-shadow:0 5px 15px rgba(0,0,0,.1); overflow:hidden; }
        .header { background:#dc3545; color:white; padding:20px; text-align:center; }
        .header h1 { font-size:2em; margin-bottom:10px; }
        .controls { display:flex; gap:10px; margin:20px; }
        .search-box { flex:1; }
        .search-box input { width:100%; padding:8px 12px; border:1px solid #ddd; border-radius:5px; }
        .btn { padding:8px 16px; border:none; border-radius:5px; color:white; cursor:pointer; text-decoration:none; display:inline-block; }
        .btn-danger { background:#dc3545; }
        .btn-secondary { background:#6c757d; }
        .btn-info { background:#17a2b8; }
        .table { width:100%; border-collapse:collapse; background:white; }
        .table th { background:#495057; color:white; padding:10px; text-align:left; }
        .table td { padding:8px 10px; border-bottom:1px solid #eee; }
        .table tbody tr:hover { background:#f5f5f5; }
        .amount { text-align:right; }
        .pagination { display:flex; justify-content:center; gap:5px; margin:20px; }
        .pagination a, .pagination span { padding:5px 10px; border:1px solid #ddd; border-radius:3px; text-decoration:none; color:#333; }
        .pagination .current { background:#667eea; color:white; }
        .no-data { text-align:center; padding:40px 20px; color:#666; }
        .status-badge { padding:3px 10px; border-radius:15px; font-size:0.75em; font-weight:600; text-transform:uppercase; }
        .status-pending { background:#f8d7da; color:#721c24; }
        .status-paid { background:#d4edda; color:#155724; }
        .status-partial { background:#fff3cd; color:#856404; }
        .status-credit { background:#d1ecf1; color:#0c5460; }
        .status-refunded { background:#e2e3e5; color:#383d41; }
        .status-cancel { background:#6c757d; color:white; }
    </style>
</head>
<body>
    <div style="position:absolute;top:20px;left:20px;">
        <button onclick="window.location.href='../accounts.php'" 
                style="background:#f09424;color:white;padding:10px 20px;border:none;border-radius:5px;cursor:pointer;">
            Back to Accounts
        </button>
    </div>

    <div class="container">
        <div class="header">
            <h1>Bill Cancellation System</h1>
            <p>Cancel invoices that should no longer be active</p>
        </div>

        <div class="controls">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search by invoice number, booking reference, name, contact, email..." 
                       value="<?php echo htmlspecialchars($searchTerm); ?>">
            </div>
            <button class="btn btn-info" onclick="performSearch()">Search</button>
            <a href="?" class="btn btn-secondary">Reset</a>
        </div>

        <?php if (empty($invoices)): ?>
            <div class="no-data">
                <h3>No Active Invoices Found</h3>
                <p>All invoices might be cancelled or no invoices match your search</p>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice No</th>
                        <th>Booking Ref</th>
                        <th>Type</th>
                        <th>Total Amount</th>
                        <th>Paid Amount</th>
                        <th>Pending Amount</th>
                        <th>Status</th>
                        <th>Issued By</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($inv['invoice_number']); ?></strong></td>
                            <td><?php echo htmlspecialchars($inv['booking_reference']); ?></td>
                            <td><?php echo htmlspecialchars($inv['value_type'] ?: '-'); ?></td>
                            <td class="amount"><?php echo fmt($inv['total_amount']); ?></td>
                            <td class="amount"><?php echo fmt($inv['payment_amount']); ?></td>
                            <td class="amount" style="<?php echo $inv['pending_amount'] > 0 ? 'color:#dc3545;' : ''; ?>">
                                <?php echo fmt($inv['pending_amount']); ?>
                            </td>
                            <td><?php echo getStatusBadge($inv['payment_status']); ?></td>
                            <td><?php echo htmlspecialchars($inv['issued_by'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($inv['contact_no'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($inv['email'] ?: '-'); ?></td>
                            <td><?php echo fdate($inv['payment_date']); ?></td>
                            <td>
                                <button class="btn btn-danger" onclick="cancelBill(<?php echo $inv['id']; ?>, '<?php echo htmlspecialchars($inv['invoice_number']); ?>')">
                                    Cancel Bill
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Prev</a>
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

    <script>
        function performSearch() {
            const searchTerm = document.getElementById('searchInput').value;
            const url = new URL(window.location.href);
            if (searchTerm) {
                url.searchParams.set('search', searchTerm);
            } else {
                url.searchParams.delete('search');
            }
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }

        function cancelBill(invoiceId, invoiceNumber) {
            if (!confirm(`Are you sure you want to cancel invoice ${invoiceNumber}?\n\nThis action cannot be undone.`)) {
                return;
            }
            
            const formData = new FormData();
            formData.append('invoice_id', invoiceId);
            formData.append('cancel_bill', '1');
            
            fetch('<?php echo basename($_SERVER['PHP_SELF']); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Invoice ${invoiceNumber} cancelled successfully!`);
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }

        // Enter key for search
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    </script>
</body>
</html>