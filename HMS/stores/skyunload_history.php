<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection for history database
$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$history_dbname = "hotelgrandguardi_wedding_bliss";

try {
    $conn_history = new PDO("mysql:host=$servername;dbname=$history_dbname", $username, $password);
    $conn_history->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get selected date
    $today = date('Y-m-d');
    $selected_date = $_GET['date'] ?? $today;

    // Get unloads for the selected date
    $unload_stmt = $conn_history->prepare("
        SELECT 
            fuh.unload_date,
            fuh.order_sheet_no,
            fuh.item_id,
            fuh.issued_qty,
            fuh.remaining_qty,
            fuh.usage_qty,
            fuh.function_type,
            r.name AS created_by
        FROM skyfunction_unload_history fuh
        LEFT JOIN wedding_bliss.storeresponsible r ON fuh.created_by = r.id
        WHERE fuh.unload_date = ?
        ORDER BY fuh.item_id
    ");
    $unload_stmt->execute([$selected_date]);
    $unloads = $unload_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get item names from the main database (wedding_bliss.inventory)
    $conn_main = new PDO("mysql:host=$servername;dbname=wedding_bliss", $username, $password);
    $conn_main->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $item_stmt = $conn_main->prepare("SELECT id, item_name FROM inventory");
    $item_stmt->execute();
    $items = $item_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

} catch(PDOException $e) {
    $error_message = "Database connection failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unload History</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
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
                <i class="fas fa-history"></i> Unload History
            </h1>
        </header>
        
        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 text-red-800 p-4 rounded-lg mb-6 text-center flex items-center justify-center gap-2">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
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
                    <i class="fas fa-search mr-2"></i> View History
                </button>
            </form>
        </div>
        
        <div class="card">
            <h2 class="text-2xl font-semibold text-teal-700 mb-4 flex items-center gap-2"><i class="fas fa-list"></i> Unloads for <?php echo htmlspecialchars($selected_date); ?></h2>
            
            <div class="shadow-md rounded-lg overflow-hidden">
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
                        <?php if (empty($unloads)): ?>
                            <tr>
                                <td colspan="8" class="py-4 text-center text-gray-600 flex items-center justify-center gap-2"><i class="fas fa-info-circle"></i> No unload records found for this date</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($unloads as $unload): ?>
                                <tr class="hover:bg-teal-50 transition">
                                    <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($unload['unload_date']); ?></td>
                                    <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($unload['order_sheet_no']); ?></td>
                                    <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($items[$unload['item_id']] ?? 'Unknown Item'); ?></td>
                                    <td class="py-3 px-4 border-b text-sm"><?php echo number_format($unload['issued_qty'], 2); ?></td>
                                    <td class="py-3 px-4 border-b text-sm"><?php echo number_format($unload['remaining_qty'], 2); ?></td>
                                    <td class="py-3 px-4 border-b text-sm"><?php echo number_format($unload['usage_qty'], 2); ?></td>
                                    <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($unload['function_type']); ?></td>
                                    <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($unload['created_by'] ?? 'Unknown'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>