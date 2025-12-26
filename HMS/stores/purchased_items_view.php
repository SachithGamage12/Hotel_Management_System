<?php
// purchased_items_view.php
// Database connection
$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch distinct item names for filter
$stmt = $pdo->prepare("SELECT DISTINCT item_name FROM inventory WHERE id IN (SELECT item_id FROM purchased_items) ORDER BY item_name");
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch aggregated purchased items data
$query = "SELECT 
            i.item_name,
            SUM(pi.stock) as total_quantity,
            i.unit
          FROM purchased_items pi
          JOIN inventory i ON pi.item_id = i.id";
$where = [];
$params = [];

if (!empty($_GET['item_name'])) {
    $where[] = "i.item_name = ?";
    $params[] = $_GET['item_name'];
}

if (!empty($where)) {
    $query .= " WHERE " . implode(' AND ', $where);
}
$query .= " GROUP BY i.item_name, i.unit ORDER BY i.item_name";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchased Items Stock View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .table-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }
        .table-container h2 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
            font-size: 1.8rem;
        }
        .form-group label {
            color: #34495e;
            font-weight: 500;
            margin-bottom: 8px;
        }
        .form-select {
            border-radius: 8px;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ced4da;
            background: #f8f9fa;
            transition: border-color 0.3s ease;
        }
        .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.2);
        }
        .btn-primary {
            background: #007bff;
            border: none;
            padding: 10px 20px;
            font-weight: 500;
            border-radius: 8px;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        .btn-primary:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }
        .table {
            font-size: 0.9rem;
        }
        .table thead th {
            background: #2c3e50;
            color: #ffffff;
            border: none;
        }
        .table tbody tr:hover {
            background: #f8f9fa;
        }
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 6px;
        }
        .dataTables_wrapper .dataTables_length select {
            border-radius: 8px;
            border: 1px solid #ced4da;
        }
        @media (max-width: 768px) {
            .table-container {
                margin: 15px;
                padding: 20px;
            }
            .btn-primary {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container table-container">
            <div style="position: absolute; top: 20px; left: 20px;">
    <button onclick="window.location.href='../stores.php'" 
        style="background-color: #f09424ff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
        Back
    </button>
</div>
        <h2>Purchased Items Stock View</h2>
        
        <form method="get" class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="item_name">Item Name (Optional)</label>
                    <select class="form-select" id="item_name" name="item_name">
                        <option value="">All Items</option>
                        <?php foreach ($items as $item): ?>
                            <option value="<?php echo htmlspecialchars($item); ?>" <?php echo isset($_GET['item_name']) && $_GET['item_name'] === $item ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($item); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>

        <table id="purchasedItemsTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Total Quantity</th>
                    <th>Unit</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                        <td><?php echo number_format($row['total_quantity'], 2); ?></td>
                        <td><?php echo htmlspecialchars($row['unit']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#purchasedItemsTable').DataTable({
                "paging": true,
                "searching": true,
                "ordering": true,
                "pageLength": 10,
                "language": {
                    "search": "Search Table:",
                    "lengthMenu": "Show _MENU_ entries"
                }
            });
        });
    </script>
</body>
</html>