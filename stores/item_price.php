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
// EXPORT TO EXCEL FUNCTIONALITY
// ================================================
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    try {
        // Get all items with their latest prices using ID to find latest
        $exportQuery = "
            SELECT 
                gi.item_name,
                gi.unit,
                gi.unit_price as current_unit_price
            FROM grn_items gi
            WHERE gi.id IN (
                SELECT MAX(gi2.id) 
                FROM grn_items gi2 
                WHERE gi2.item_name IS NOT NULL AND gi2.item_name != ''
                GROUP BY gi2.item_name
            )
            AND gi.item_name IS NOT NULL AND gi.item_name != ''
            ORDER BY gi.item_name
        ";
        
        $stmt = $pdo->prepare($exportQuery);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Set headers for Excel download
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="item_prices_' . date('Y-m-d') . '.xls"');
        header('Cache-Control: max-age=0');
        
        // Excel content
        echo "Item Name\tUnit\tCurrent Price\n";
        
        foreach ($items as $item) {
            echo htmlspecialchars($item['item_name']) . "\t";
            echo htmlspecialchars($item['unit']) . "\t";
            echo 'Rs. ' . number_format(floatval($item['current_unit_price'] ?? 0), 2) . "\n";
        }
        
        exit;
        
    } catch (Exception $e) {
        $error = "Export error: " . $e->getMessage();
    }
}

// ================================================
// SEARCH ITEMS WITH LATEST PRICE
// ================================================
$searchTerm = $_GET['search'] ?? '';
$itemDetails = null;
$priceHistory = [];

if (!empty($searchTerm)) {
    try {
        $searchTermLike = '%' . $searchTerm . '%';
        
        // Get specific item details with latest price
        $itemQuery = "
            SELECT 
                item_name,
                unit,
                (SELECT unit_price 
                 FROM grn_items gi2 
                 WHERE gi2.item_name = gi.item_name 
                 ORDER BY gi2.id DESC 
                 LIMIT 1) as current_unit_price
            FROM grn_items gi
            WHERE gi.item_name LIKE :search
            GROUP BY gi.item_name, gi.unit
            ORDER BY gi.item_name
            LIMIT 1
        ";
        
        $stmt = $pdo->prepare($itemQuery);
        $stmt->execute([':search' => $searchTermLike]);
        $itemDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If item found, get price history
        if ($itemDetails) {
            $historyQuery = "
                SELECT 
                    unit_price,
                    quantity
                FROM grn_items 
                WHERE item_name = :item_name
                ORDER BY id DESC
            ";
            
            $historyStmt = $pdo->prepare($historyQuery);
            $historyStmt->execute([':item_name' => $itemDetails['item_name']]);
            $priceHistory = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $success = "Item '{$itemDetails['item_name']}' found with " . count($priceHistory) . " price records";
        } else {
            $error = "No item found matching '{$searchTerm}'";
        }
        
    } catch (Exception $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// ================================================
// GET ALL UNIQUE ITEMS FOR DROPDOWN (OPTIONAL)
// ================================================
try {
    $allItemsQuery = "
        SELECT DISTINCT item_name 
        FROM grn_items 
        WHERE item_name IS NOT NULL AND item_name != '' 
        ORDER BY item_name
    ";
    $allItems = $pdo->query($allItemsQuery)->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $error = "Error loading item list: " . $e->getMessage();
    $allItems = [];
}

function fmt($v) { return 'Rs. ' . number_format(floatval($v ?? 0), 2); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Price Search</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Segoe UI,Tahoma,sans-serif; background:#667eea; padding:20px; }
        .container { max-width:1400px; margin:auto; background:white; border-radius:10px; box-shadow:0 5px 15px rgba(0,0,0,.1); overflow:hidden; }
        .header { background:#2c3e50; color:white; padding:20px; text-align:center; }
        
        /* Alert Messages */
        .alert { padding:15px; margin:20px; border-radius:5px; font-weight:500; }
        .alert-error { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
        .alert-success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
        .alert-info { background:#d1ecf1; color:#0c5460; border:1px solid #bee5eb; }
        
        .controls { padding:20px; background:#f8f9fa; }
        .search-form { display:flex; gap:10px; margin-bottom:15px; flex-wrap: wrap; }
        .search-container { position:relative; flex: 1 1 300px; }
        .search-box input { width:100%; padding:10px 15px; border:2px solid #ddd; border-radius:5px; font-size:16px; }
        .btn { padding:10px 20px; border:none; border-radius:5px; color:white; cursor:pointer; text-decoration:none; display:inline-block; font-size:14px; }
        .btn-primary { background:#007bff; }
        .btn-secondary { background:#6c757d; }
        .btn-success { background:#28a745; }
        .btn + .btn { margin-left: 8px; }
        .items-list { margin:20px; }
        .item-card { background:white; border:1px solid #e9ecef; border-radius:8px; padding:20px; margin-bottom:20px; box-shadow:0 2px 4px rgba(0,0,0,.1); }
        .item-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap: wrap; gap: 10px; }
        .item-name { font-size:1.4em; font-weight:bold; color:#2c3e50; }
        .item-unit { background:#e9ecef; padding:4px 12px; border-radius:15px; font-size:1em; color:#495057; }
        .item-details { display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:15px; margin-bottom:20px; }
        .detail-item { display:flex; flex-direction:column; }
        .detail-label { font-size:0.9em; color:#6c757d; margin-bottom:5px; }
        .detail-value { font-weight:600; }
        .price-current { color:#28a745; font-size:1.3em; }
        .no-data { text-align:center; padding:40px 20px; color:#666; }
        .suggestions { background:white; border:1px solid #ddd; border-radius:5px; max-height:200px; overflow-y:auto; position:absolute; z-index:1000; width:100%; top: 100%; left: 0; }
        .suggestion-item { padding:8px 15px; cursor:pointer; border-bottom:1px solid #f8f9fa; }
        .suggestion-item:hover { background:#f8f9fa; }
        .price-history { margin-top:25px; }
        .history-table { width:100%; border-collapse:collapse; margin-top:15px; }
        .history-table th { background:#495057; color:white; padding:12px; text-align:left; }
        .history-table td { padding:10px 12px; border-bottom:1px solid #eee; }
        .history-table tbody tr:hover { background:#f5f5f5; }
        .amount { text-align:right; }
        .total-items { color:#495057; font-size:0.9em; margin-top:10px; padding:10px; background:#e9ecef; border-radius:5px; }
        .export-section { margin-top: 15px; padding: 15px; background: #e7f3ff; border-radius: 5px; border-left: 4px solid #007bff; }

        @media (max-width: 600px) {
            .search-form { flex-direction: column; }
            .btn { width: 100%; text-align: center; }
            .btn + .btn { margin-left: 0; margin-top: 8px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Item Price Search</h1>
            <p>Find items with current price and purchase history</p>
        </div>

        <!-- Error Messages -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Success Messages -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <strong>Success:</strong> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="controls">
            <form method="GET" class="search-form">
                <div class="search-container">
                    <div class="search-box">
                        <input type="text" id="searchInput" name="search" 
                               value="<?php echo htmlspecialchars($searchTerm); ?>" 
                               placeholder="Enter item name..." 
                               autocomplete="off">
                    </div>
                    <div id="suggestions" class="suggestions" style="display:none;"></div>
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="?" class="btn btn-secondary">Clear</a>
                <a href="../stores.php" class="btn btn-secondary">Back to Stores</a>
            </form>
            
            <?php if (!empty($allItems)): ?>
                <div class="export-section">
                    <strong>Export All Items:</strong> Download complete list with latest prices
                    <div style="margin-top: 10px;">
                        <a href="?export=excel" class="btn btn-success">Export to Excel</a>
                        <span style="margin-left: 10px; color: #666; font-size: 0.9em;">
                            Includes <?php echo count($allItems); ?> items with current prices
                        </span>
                    </div>
                </div>
                
                <div class="total-items">
                    <strong>Available items:</strong> <?php echo count($allItems); ?> items in database
                </div>
            <?php endif; ?>
        </div>

        <div class="items-list">
            <?php if (empty($searchTerm)): ?>
                <div class="no-data">
                    <h3>Search for an Item</h3>
                    <p>Enter an item name to see its current price and purchase history</p>
                    <?php if (!empty($allItems)): ?>
                        <p style="margin-top: 15px;">
                            <strong>Or</strong> download the complete item list using the Export to Excel button above.
                        </p>
                    <?php endif; ?>
                </div>
            <?php elseif (!empty($itemDetails)): ?>
                <div class="item-card">
                    <div class="item-header">
                        <div class="item-name"><?php echo htmlspecialchars($itemDetails['item_name']); ?></div>
                        <div class="item-unit"><?php echo htmlspecialchars($itemDetails['unit']); ?></div>
                    </div>
                    <div class="item-details">
                        <div class="detail-item">
                            <span class="detail-label">Current Unit Price</span>
                            <span class="detail-value price-current"><?php echo fmt($itemDetails['current_unit_price']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Unit</span>
                            <span class="detail-value"><?php echo htmlspecialchars($itemDetails['unit']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Price Records</span>
                            <span class="detail-value"><?php echo count($priceHistory); ?> entries</span>
                        </div>
                    </div>
                    
                    <?php if (!empty($priceHistory)): ?>
                        <div class="price-history">
                            <h3>Price History (Latest First)</h3>
                            <table class="history-table">
                                <thead>
                                    <tr>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Total Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($priceHistory as $history): ?>
                                        <tr>
                                            <td class="amount"><?php echo number_format($history['quantity'], 3); ?></td>
                                            <td class="amount"><?php echo fmt($history['unit_price']); ?></td>
                                            <td class="amount"><?php echo fmt($history['quantity'] * $history['unit_price']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-suggest functionality
        const searchInput = document.getElementById('searchInput');
        const suggestionsDiv = document.getElementById('suggestions');
        const allItems = <?php echo json_encode($allItems); ?>;

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            suggestionsDiv.innerHTML = '';
            
            if (searchTerm.length < 2) {
                suggestionsDiv.style.display = 'none';
                return;
            }
            
            const filteredItems = allItems.filter(item => 
                item.toLowerCase().includes(searchTerm)
            ).slice(0, 10);
            
            if (filteredItems.length > 0) {
                filteredItems.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'suggestion-item';
                    div.textContent = item;
                    div.addEventListener('click', function() {
                        searchInput.value = item;
                        suggestionsDiv.style.display = 'none';
                        document.forms[0].submit();
                    });
                    suggestionsDiv.appendChild(div);
                });
                suggestionsDiv.style.display = 'block';
            } else {
                suggestionsDiv.style.display = 'none';
            }
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
                suggestionsDiv.style.display = 'none';
            }
        });

        // Auto-focus on search input
        window.addEventListener('load', function() {
            searchInput.focus();
        });
    </script>
</body>
</html>