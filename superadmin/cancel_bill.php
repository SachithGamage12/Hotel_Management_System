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
    $pdo->exec("ALTER TABLE room_payments MODIFY payment_status ENUM('Pending','Paid','Partial','Credit','Refunded','Cancel') DEFAULT 'Pending'");
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
            UPDATE room_payments 
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
    $whereConditions[] = "(invoice_number LIKE :search OR booking_reference LIKE :search OR issued_by LIKE :search)";
    $params[':search'] = $searchTermLike;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM room_payments $whereClause");
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Get invoices
$query = "
    SELECT id, invoice_number, booking_reference, total_amount, advance_payment, 
           payment_status, issued_by, payment_date, remarks
    FROM room_payments 
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
        .container { max-width:1400px; margin:auto; background:white; border-radius:10px; box-shadow:0 5px 15px rgba(0,0,0,.1); overflow:hidden; }
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bill Cancellation</h1>
        </div>

        <div class="controls">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search invoices..." 
                       value="<?php echo htmlspecialchars($searchTerm); ?>">
            </div>
            <button class="btn btn-info" onclick="performSearch()">Search</button>
            <a href="cancel_bill.php" class="btn btn-secondary">Reset</a>
        </div>

        <?php if (empty($invoices)): ?>
            <div class="no-data">
                <h3>No Invoices Found</h3>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice No</th>
                        <th>Booking Ref</th>
                        <th>Total Amount</th>
                        <th>Advance Paid</th>
                        <th>Status</th>
                        <th>Issued By</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($inv['invoice_number']); ?></td>
                            <td><?php echo htmlspecialchars($inv['booking_reference']); ?></td>
                            <td class="amount"><?php echo fmt($inv['total_amount']); ?></td>
                            <td class="amount"><?php echo fmt($inv['advance_payment']); ?></td>
                            <td><?php echo htmlspecialchars($inv['payment_status']); ?></td>
                            <td><?php echo htmlspecialchars($inv['issued_by'] ?: '-'); ?></td>
                            <td><?php echo fdate($inv['payment_date']); ?></td>
                            <td>
                                <button class="btn btn-danger" onclick="cancelBill(<?php echo $inv['id']; ?>)">
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

        function cancelBill(invoiceId) {
            if (!confirm('Are you sure you want to cancel this bill?')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('invoice_id', invoiceId);
            formData.append('cancel_bill', '1');
            
            fetch('cancel_bill.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Bill cancelled successfully!');
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