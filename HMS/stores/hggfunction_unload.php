<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection for main database
$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Database connection for history database
    $history_dbname = "wedding_bliss";
    $conn_history = new PDO("mysql:host=$servername;dbname=$history_dbname", $username, $password);
    $conn_history->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_unload'])) {
        $unload_date = $_POST['unload_date'];
        $created_by = $_POST['responsible_id'];
        $items = $_POST['items'];
        
        $conn->beginTransaction();
        $conn_history->beginTransaction();
        
        try {
            foreach ($items as $item_id => $item) {
                if (isset($item['remaining_qty']) && $item['remaining_qty'] !== '') {
                    // Get the first order_sheet_no for this item
                    $order_sheets_stmt = $conn->prepare("
                        SELECT order_sheet_no
                        FROM hggorder_sheet
                        WHERE item_id = ? AND function_date = ?
                        ORDER BY order_sheet_no
                        LIMIT 1
                    ");
                    $order_sheets_stmt->execute([$item_id, $unload_date]);
                    $order_sheet = $order_sheets_stmt->fetch(PDO::FETCH_ASSOC);
                    $order_sheet_no = $order_sheet ? $order_sheet['order_sheet_no'] : 0;
                    
                    // Calculate usage_qty
                    $remaining_qty = (float)$item['remaining_qty'];
                    $issued_qty = (float)$item['issued_qty'];
                    $usage_qty = $issued_qty - $remaining_qty;
                    
                    // Insert into main database (wedding_bliss.function_unload)
                    $stmt = $conn->prepare("
                        INSERT INTO hggfunction_unload 
                        (order_sheet_no, item_id, requested_qty, issued_qty, remaining_qty, usage_qty, 
                         unload_date, function_type, day_night, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $order_sheet_no,
                        $item_id,
                        (float)$item['requested_qty'],
                        $issued_qty,
                        $remaining_qty,
                        $usage_qty,
                        $unload_date,
                        $item['function_type'],
                        $item['day_night'],
                        $created_by
                    ]);
                    
                    // Insert into history database (wedding_bliss_history.function_unload_history)
                    $history_stmt = $conn_history->prepare("
                        INSERT INTO hggfunction_unload_history 
                        (order_sheet_no, item_id, requested_qty, issued_qty, remaining_qty, usage_qty, 
                         unload_date, function_type, day_night, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $history_stmt->execute([
                        $order_sheet_no,
                        $item_id,
                        (float)$item['requested_qty'],
                        $issued_qty,
                        $remaining_qty,
                        $usage_qty,
                        $unload_date,
                        $item['function_type'],
                        $item['day_night'],
                        $created_by
                    ]);
                }
            }
            
            $conn->commit();
            $conn_history->commit();
            $success_message = "Unload data saved successfully!";
            header("Location: hggunload_history.php?date=" . urlencode($unload_date));
            exit();
        } catch (Exception $e) {
            $conn->rollBack();
            $conn_history->rollBack();
            $error_message = "Error saving unload data: " . $e->getMessage();
        }
    }
    
    // Get today's date for default value
    $today = date('Y-m-d');
    $selected_date = $_GET['date'] ?? $today;
    
    // Get responsible persons
    $responsible_stmt = $conn->prepare("SELECT id, name FROM storeresponsible");
    $responsible_stmt->execute();
    $responsibles = $responsible_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all order sheets for the selected date and aggregate by item
    $orders_stmt = $conn->prepare("
        SELECT 
            os.item_id,
            i.item_name,
            i.unit,
            SUM(os.requested_qty) AS total_requested,
            SUM(os.issued_qty) AS total_issued,
            GROUP_CONCAT(DISTINCT os.function_type ORDER BY os.function_type SEPARATOR ', ') AS function_types,
            GROUP_CONCAT(DISTINCT os.day_night ORDER BY os.day_night SEPARATOR ', ') AS day_nights,
            GROUP_CONCAT(DISTINCT os.order_sheet_no ORDER BY os.order_sheet_no SEPARATOR ', ') AS order_sheet_nos,
            GROUP_CONCAT(DISTINCT r.name SEPARATOR ', ') AS responsible_names,
            COUNT(DISTINCT os.order_sheet_no) AS order_sheet_count
        FROM hggorder_sheet os
        JOIN inventory i ON os.item_id = i.id
        LEFT JOIN responsible r ON os.responsible_id = r.id
        WHERE os.function_date = ?
        GROUP BY os.item_id
        ORDER BY i.item_name
    ");
    $orders_stmt->execute([$selected_date]);
    $aggregated_items = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get function details
    $function_details = !empty($aggregated_items) ? [
        'function_type' => $aggregated_items[0]['function_types'],
        'day_night' => $aggregated_items[0]['day_nights'],
        'order_sheet_nos' => $aggregated_items[0]['order_sheet_nos'],
        'responsible_names' => $aggregated_items[0]['responsible_names'],
        'order_sheet_count' => $aggregated_items[0]['order_sheet_count']
    ] : null;
    
    // Get existing unloads for the date
    $unload_stmt = $conn->prepare("
        SELECT fu.*, i.item_name 
        FROM hggfunction_unload fu
        JOIN inventory i ON fu.item_id = i.id
        WHERE fu.unload_date = ?
        ORDER BY i.item_name
    ");
    $unload_stmt->execute([$selected_date]);
    $existing_unloads = $unload_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Prepare existing unloads for easy access (aggregate by item_id for display)
    $unloads_by_item = [];
    foreach ($existing_unloads as $unload) {
        $item_id = $unload['item_id'];
        if (!isset($unloads_by_item[$item_id])) {
            $unloads_by_item[$item_id] = [
                'item_id' => $unload['item_id'],
                'item_name' => $unload['item_name'],
                'requested_qty' => (float)$unload['requested_qty'],
                'issued_qty' => (float)$unload['issued_qty'],
                'remaining_qty' => (float)$unload['remaining_qty'],
                'usage_qty' => (float)$unload['usage_qty'],
                'function_type' => $unload['function_type'],
                'day_night' => $unload['day_night']
            ];
        } else {
            // Aggregate quantities for the same item
            $unloads_by_item[$item_id]['requested_qty'] += (float)$unload['requested_qty'];
            $unloads_by_item[$item_id]['issued_qty'] += (float)$unload['issued_qty'];
            $unloads_by_item[$item_id]['remaining_qty'] += (float)$unload['remaining_qty'];
            $unloads_by_item[$item_id]['usage_qty'] += (float)$unload['usage_qty'];
        }
    }
    
} catch(PDOException $e) {
    $error_message = "Database connection failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HGG Restaurant Unload System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .table-container {
            max-height: 500px;
            overflow-y: auto;
        }
        .highlight-row {
            background-color: #f0fdf4;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            align-items: center;
            justify-content: center;
            z-index: 50;
        }
        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 0.75rem;
            max-width: 600px;
            width: 100%;
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
        }
        .btn-primary {
            background-color: #0d9488;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            transition: background-color 0.2s;
        }
        .btn-primary:hover {
            background-color: #0f766e;
        }
        .input-field {
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            width: 100%;
        }
        input:focus {
    outline: none; /* optional */
    box-shadow: 0 0 0 2px teal;
}

    </style>
</head>
<body class="bg-gray-50 min-h-screen p-8">
    <div style="position: absolute; top: 20px; left: 20px;">
    <button onclick="window.location.href='../stores.php'" 
        style="background-color: #f09424ff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
        Back
    </button>
</div>
    <div class="container mx-auto max-w-7xl">
        <header class="mb-8">
            <h1 class="text-4xl font-bold text-teal-800 text-center flex items-center justify-center gap-2">
                <i class="fas fa-truck-loading"></i> HGG/R Unload System
            </h1>
        </header>
        
        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 text-red-800 p-4 rounded-lg mb-6 text-center flex items-center justify-center gap-2">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-6 text-center flex items-center justify-center gap-2">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <div class="card mb-8">
            <h2 class="text-2xl font-semibold text-teal-700 mb-4 flex items-center gap-2"><i class="fas fa-calendar-alt"></i> Select Date</h2>
            <form method="get" action="" class="flex flex-col md:flex-row items-center gap-4">
                <div class="flex-1 w-full">
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Function Date</label>
                    <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($selected_date); ?>" class="input-field">
                </div>
                <button type="submit" class="btn-primary mt-4 md:mt-0">
                    <i class="fas fa-search mr-2"></i> Load Orders
                </button>
            </form>
        </div>
        
        <?php if (!empty($aggregated_items) && $function_details): ?>
        <form method="post" action="" id="unload_form">
            <input type="hidden" name="submit_unload" value="1">
            <input type="hidden" name="unload_date" value="<?php echo htmlspecialchars($selected_date); ?>">
            
            <div class="card mb-8">
                <h2 class="text-2xl font-semibold text-teal-700 mb-4 flex items-center gap-2"><i class="fas fa-user-shield"></i> Responsible Person</h2>
                <div class="w-full md:w-1/2">
                    <label for="responsible_id" class="block text-sm font-medium text-gray-700 mb-2">Select Responsible</label>
                    <select id="responsible_id" name="responsible_id" required class="input-field">
                        <option value="">Select Responsible Person</option>
                        <?php foreach ($responsibles as $responsible): ?>
                            <option value="<?php echo htmlspecialchars($responsible['id']); ?>">
                                <?php echo htmlspecialchars($responsible['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="card mb-8">
                <h2 class="text-2xl font-semibold text-teal-700 mb-4 flex items-center gap-2"><i class="fas fa-boxes"></i> Issued Items for <?php echo htmlspecialchars($selected_date); ?></h2>
                
                <div class="mb-6 bg-gray-50 p-4 rounded-lg shadow-inner">
                    <h3 class="font-bold text-xl mb-3 flex items-center gap-2"><i class="fas fa-info-circle"></i>  Summary</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white p-3 rounded shadow-sm">
                            <p><span class="font-medium">Function Type(s):</span> <?php echo htmlspecialchars($function_details['function_type']); ?></p>
                            <p><span class="font-medium">Day/Night:</span> <?php echo htmlspecialchars($function_details['day_night'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="bg-white p-3 rounded shadow-sm">
                            <p><span class="font-medium">Order Sheet No:</span> <?php echo htmlspecialchars($function_details['order_sheet_nos']); ?></p>
                            <p><span class="font-medium">Responsible Persons:</span> <?php echo htmlspecialchars($function_details['responsible_names']); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="table-container shadow-md rounded-lg overflow-hidden">
                    <table class="min-w-full bg-white">
                        <thead class="bg-teal-600 text-white">
                            <tr>
                                <th class="py-3 px-4 text-left text-sm font-medium">Item Name</th>
                                <th class="py-3 px-4 text-left text-sm font-medium">Requested Qty (Total)</th>
                                <th class="py-3 px-4 text-left text-sm font-medium">Issued Qty (Total)</th>
                                <th class="py-3 px-4 text-left text-sm font-medium">Unit</th>
                                <th class="py-3 px-4 text-left text-sm font-medium">Remaining Qty</th>
                                <th class="py-3 px-4 text-left text-sm font-medium">Usage Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($aggregated_items as $item): ?>
                            <?php 
                                $existing_unload = $unloads_by_item[$item['item_id']] ?? null;
                                $row_class = $existing_unload ? 'highlight-row' : '';
                            ?>
                            <tr class="<?php echo $row_class; ?> hover:bg-teal-50 transition">
                                <td class="py-3 px-4 border-b text-sm">
                                    <?php echo htmlspecialchars($item['item_name']); ?>
                                    <input type="hidden" name="items[<?php echo $item['item_id']; ?>][item_id]" value="<?php echo $item['item_id']; ?>">
                                    <input type="hidden" name="items[<?php echo $item['item_id']; ?>][requested_qty]" value="<?php echo number_format($item['total_requested'], 2); ?>">
                                    <input type="hidden" name="items[<?php echo $item['item_id']; ?>][issued_qty]" value="<?php echo number_format($item['total_issued'], 2); ?>">
                                    <input type="hidden" name="items[<?php echo $item['item_id']; ?>][function_type]" value="<?php echo htmlspecialchars($item['function_types']); ?>">
                                    <input type="hidden" name="items[<?php echo $item['item_id']; ?>][day_night]" value="<?php echo htmlspecialchars($item['day_nights']); ?>">
                                </td>
                                <td class="py-3 px-4 border-b text-sm"><?php echo number_format($item['total_requested'], 2); ?></td>
                                <td class="py-3 px-4 border-b text-sm"><?php echo number_format($item['total_issued'], 2); ?></td>
                                <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($item['unit']); ?></td>
                                <td class="py-3 px-4 border-b text-sm">
                                    <input type="number" 
                                           name="items[<?php echo $item['item_id']; ?>][remaining_qty]"
                                           value="<?php echo $existing_unload ? number_format($existing_unload['remaining_qty'], 2) : ''; ?>"
                                           min="0" 
                                           max="<?php echo number_format($item['total_issued'], 2); ?>"
                                           step="0.01"
                                           class="w-24 input-field"
                                           onchange="calculateUsage(this, <?php echo number_format($item['total_issued'], 2); ?>)"
                                           <?php echo $item['total_issued'] == 0 ? 'disabled' : ''; ?>>
                                </td>
                                <td class="py-3 px-4 border-b text-sm usage-qty">
                                    <?php 
                                    if ($existing_unload) {
                                        echo number_format($existing_unload['usage_qty'], 2);
                                    } else {
                                        echo $item['total_issued'] > 0 ? '0.00' : 'N/A';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="flex justify-center mt-6">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save mr-2"></i> Save Unload Data
                    </button>
                </div>
            </div>
        </form>
        <?php else: ?>
            <div class="card text-center">
                <p class="text-gray-600 py-4 flex items-center justify-center gap-2"><i class="fas fa-info-circle"></i> No orders found for <?php echo htmlspecialchars($selected_date); ?></p>
            </div>
        <?php endif; ?>
        
        <div class="mt-12 card">
            <h2 class="text-2xl font-semibold text-teal-800 mb-4 flex items-center gap-2"><i class="fas fa-history"></i> Recent Unloads</h2>
            
            <div class="table-container shadow-md rounded-lg overflow-hidden">
                <table class="min-w-full bg-white">
                    <thead class="bg-teal-600 text-white">
                        <tr>
                            <th class="py-3 px-4 text-left text-sm font-medium">Date</th>
                            <th class="py-3 px-4 text-left text-sm font-medium">Order Sheet No</th>
                            <th class="py-3 px-4 text-left text-sm font-medium">Item Name</th>
                            <th class="py-3 px-4 text-left text-sm font-medium">Issued Qty</th>
                            <th class="py-3 px-4 text-left text-sm font-medium">Remaining Qty</th>
                            <th class="py-3 px-4 text-left text-sm font-medium">Usage Qty</th>
                            <th class="py-3 px-4 text-left text-sm font-medium">Function</th>
                            <th class="py-3 px-4 text-left text-sm font-medium">Recorded By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $recent_stmt = $conn->prepare("
    SELECT 
        fu.unload_date,
        fu.order_sheet_no,
        i.item_name,
        fu.issued_qty,
        fu.remaining_qty,
        fu.usage_qty,
        fu.function_type,
        r.name AS created_by
    FROM hggfunction_unload fu
    JOIN inventory i ON fu.item_id = i.id
    LEFT JOIN responsible r ON fu.created_by = r.id
    ORDER BY fu.unload_date DESC, i.item_name
    LIMIT 5
");
                        $recent_stmt->execute();
                        $recent_unloads = $recent_stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (empty($recent_unloads)): ?>
                            <tr>
                                <td colspan="8" class="py-4 text-center text-gray-600">No unload records found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_unloads as $unload): ?>
                                <tr class="hover:bg-teal-50 transition">
                                    <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($unload['unload_date']); ?></td>
                                    <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($unload['order_sheet_no']); ?></td>
                                    <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($unload['item_name']); ?></td>
                                    <td class="py-3 px-4 border-b text-sm"><?php echo number_format($unload['issued_qty'], 2); ?></td>
                                    <td class="py-3 px-4 border-b text-sm"><?php echo number_format($unload['remaining_qty'], 2); ?></td>
                                    <td class="py-3 px-4 border-b text-sm"><?php echo number_format($unload['usage_qty'], 2); ?></td>
                                    <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($unload['function_type']); ?></td>
                                    <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($unload['created_by']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function calculateUsage(input, issuedQty) {
            const row = input.closest('tr');
            const remainingQty = parseFloat(input.value);
            
            if (!isNaN(remainingQty)) {
                const usageQty = issuedQty - remainingQty;
                row.querySelector('.usage-qty').textContent = usageQty.toFixed(2);
            } else {
                row.querySelector('.usage-qty').textContent = issuedQty > 0 ? '0.00' : 'N/A';
            }
        }
        
        // Auto-calculate usage when page loads for existing values
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('input[name*="remaining_qty"]').forEach(input => {
                if (input.value && !input.disabled) {
                    const issuedQty = parseFloat(input.getAttribute('max'));
                    calculateUsage(input, issuedQty);
                }
            });
        });
    </script>
</body>
</html>