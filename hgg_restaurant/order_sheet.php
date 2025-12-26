<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HGG/R Order Sheet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .search-results {
            max-height: 250px;
            overflow-y: auto;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            background: white;
            position: absolute;
            width: 100%;
            max-width: 400px;
            z-index: 30;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .search-results div {
            padding: 0.75rem;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.2s;
        }
        .search-results div:hover {
            background-color: #d1fae5;
            transform: translateX(5px);
        }
        .item-row {
            display: flex;
            gap: 1rem;
            align-items: center;
            padding: 0.75rem;
            background: #f1f5f9;
            border-radius: 0.5rem;
            margin-bottom: 0.75rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .item-row:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .table-container {
            max-height: 500px;
            overflow-y: auto;
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
            max-width: 500px;
            width: 100%;
            animation: slideIn 0.3s ease-out;
        }
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @media print {
            body, .container, .modal, .table-container, form, h2, h3:not(.print-title) {
                display: none !important;
            }
            #print-view {
                display: block !important;
                width: 300px;
                font-family: 'Courier New', Courier, monospace;
                font-size: 12px;
                line-height: 1.2;
                color: #000;
                padding: 0;
                margin: 0;
            }
            #print-view table {
                width: 100%;
                border-collapse: collapse;
            }
            #print-view th, #print-view td {
                border: none;
                padding: 2px 0;
                text-align: left;
            }
            #print-view .header {
                text-align: center;
                font-weight: bold;
            }
            #print-view .divider {
                border-top: 1px dashed #000;
                margin: 5px 0;
            }
        }
        .unit-display {
            display: block;
            width: 100%;
            padding: 0.75rem;
            background-color: #f9fafb;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            color: #374151;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-teal-100 to-blue-200 min-h-screen p-6">
    <button onclick="window.location.href='../hgg_restaurant.php'" style="background-color: #d815f6ff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
        Back
    </button>
    <div class="container mx-auto max-w-6xl bg-white p-10 rounded-3xl shadow-2xl">
        <h2 class="text-4xl font-bold text-teal-900 mb-10 text-center">Create HGG/R Order Sheet</h2>
        
        <?php
        // Enable error reporting and logging
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        ini_set('log_errors', 1);
        ini_set('error_log', 'F:/xampp/htdocs/HMS/stores/php_errors.log');

        // Database connection
        $servername = "localhost";
        $username = "hotelgrandguardi_root";
        $password = "Sun123flower@";
        $dbname = "hotelgrandguardi_wedding_bliss";

        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            error_log("Database connection successful at " . date('Y-m-d H:i:s'));
            
            // Handle form submission
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_request'])) {
                error_log("Form submission started: " . json_encode($_POST));
                
                $items = json_decode($_POST['items_json'] ?? '', true);
                $responsible_id = $_POST['responsible_id'] ?? '';
                $password = $_POST['password'] ?? '';
                $function_type = $_POST['function_type'] ?? '';
                $function_date = isset($_POST['function_date']) ? trim($_POST['function_date']) : '';
                $day_night = $_POST['day_night'] ?? '';
                
                try {
                    // Validate inputs
                    if (!is_array($items) || empty($items)) {
                        throw new Exception("No items provided.");
                    }
                    foreach ($items as $item) {
                        if (!isset($item['item_id']) || !is_numeric($item['item_id']) || 
                            !isset($item['requested_qty']) || !is_numeric($item['requested_qty']) || 
                            $item['requested_qty'] <= 0 ||
                            !isset($item['unit'])) {
                            throw new Exception("Invalid item data or missing unit type.");
                        }
                    }
                    if (empty($responsible_id) || !is_numeric($responsible_id)) {
                        throw new Exception("Please select a responsible person.");
                    }
                    if (empty($password)) {
                        throw new Exception("Password is required.");
                    }
                    if (empty($function_type)) {
                        throw new Exception("Please select a function location.");
                    }
                    // Validate function_date
                    if ($function_date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $function_date)) {
                        throw new Exception("Please select a valid function date.");
                    }
                    $selected_date = new DateTime($function_date);
                    $today = new DateTime('today');
                    if ($selected_date < $today) {
                        throw new Exception("Function date cannot be in the past.");
                    }
                    error_log("Input validation passed: items=" . count($items) . ", responsible_id=$responsible_id, function_type=$function_type, function_date=$function_date, day_night=$day_night");

                    // Verify responsible person
                    $stmt = $conn->prepare("SELECT id, name, password FROM responsible WHERE id = ?");
                    $stmt->execute([$responsible_id]);
                    $responsible = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$responsible) {
                        throw new Exception("Responsible person not found.");
                    }
                    if (!password_verify($password, $responsible['password'])) {
                        throw new Exception("Incorrect password.");
                    }
                    error_log("Responsible person verified: " . $responsible['name']);

                    // Begin transaction
                    $conn->beginTransaction();
                    try {
                        // Get and increment order sheet number
                        $stmt = $conn->prepare("SELECT last_order_sheet_no FROM hggorder_sheet_counter WHERE id = 1 FOR UPDATE");
                        $stmt->execute();
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        if (!$result) {
                            // Initialize counter if not exists
                            $stmt = $conn->prepare("INSERT INTO hggorder_sheet_counter (id, last_order_sheet_no) VALUES (1, 1099)");
                            $stmt->execute();
                            $order_sheet_no = 1100;
                        } else {
                            $order_sheet_no = $result['last_order_sheet_no'] + 1;
                        }
                        error_log("Order sheet number: $order_sheet_no");

                        // Update counter
                        $stmt = $conn->prepare("INSERT INTO hggorder_sheet_counter (id, last_order_sheet_no) 
                                              VALUES (1, ?) 
                                              ON DUPLICATE KEY UPDATE last_order_sheet_no = ?");
                        $stmt->execute([$order_sheet_no, $order_sheet_no]);
                        
                        // Prepare print data with ORIGINAL quantities and units
                        $print_items = [];
                        foreach ($items as $item) {
                            // Fetch item name from inventory
                            $stmt = $conn->prepare("SELECT item_name FROM inventory WHERE id = ?");
                            $stmt->execute([$item['item_id']]);
                            $item_data = $stmt->fetch(PDO::FETCH_ASSOC);
                            $print_items[] = [
                                'item_name' => substr($item_data['item_name'], 0, 20),
                                'requested_qty' => $item['requested_qty'],
                                'unit' => $item['unit']
                            ];
                        }

                        // Insert items into order_sheet with CONVERSION to base unit
                        $stmt = $conn->prepare("INSERT INTO hggorder_sheet (item_id, requested_qty, requested_unit, status, request_date, order_sheet_no, responsible_id, function_type, function_date, day_night) 
                                              VALUES (?, ?, ?, 'pending', NOW(), ?, ?, ?, ?, ?)");
                        foreach ($items as $item) {
                            // Verify item exists and get base unit
                            $check_stmt = $conn->prepare("SELECT id, unit FROM inventory WHERE id = ?");
                            $check_stmt->execute([$item['item_id']]);
                            $inventory_item = $check_stmt->fetch(PDO::FETCH_ASSOC);
                            if (!$inventory_item) {
                                throw new Exception("Item ID {$item['item_id']} not found in inventory.");
                            }
                            $base_unit = $inventory_item['unit'] ?? 'Unit';
                            $requested_unit = $item['unit'];
                            $requested_qty = $item['requested_qty'];

                            // Convert to base unit if necessary
                            $converted_qty = $requested_qty;
                            $converted_unit = $base_unit;
                            if ($base_unit === 'kg' && $requested_unit === 'g') {
                                $converted_qty = $requested_qty / 1000;
                                $converted_unit = 'kg';
                            } elseif ($base_unit === 'l' && $requested_unit === 'ml') {
                                $converted_qty = $requested_qty / 1000;
                                $converted_unit = 'l';
                            }
                            // No conversion needed for other cases (kg->kg, l->l, or fixed units)

                            // Validate unit type
                            $allowed_units = ['kg', 'g', 'l', 'ml'];
                            if (in_array($base_unit, ['kg', 'l']) && !in_array($requested_unit, $allowed_units)) {
                                throw new Exception("Invalid unit '{$requested_unit}' for item {$item['item_name']}.");
                            }

                            // Use NULL for day_night if empty
                            $day_night_value = empty($day_night) ? null : $day_night;
                            $stmt->execute([$item['item_id'], $converted_qty, $converted_unit, $order_sheet_no, $responsible_id, $function_type, $function_date, $day_night_value]);
                            error_log("Inserted item: ID={$item['item_id']}, Original Qty={$item['requested_qty']} {$requested_unit}, Converted Qty={$converted_qty} {$converted_unit}, Order=$order_sheet_no, Function=$function_type, Date=$function_date, Day/Night=$day_night_value");
                        }
                        
                        // Update remaining_quantity to 0 for SKY Buffer Stock Refill items
                        if ($function_type === 'HGG/R Buffer Stock Refill') {
                            $update_stmt = $conn->prepare("UPDATE hggkitchen_buffer SET remaining_quantity = 0 WHERE item_id = ?");
                            foreach ($items as $item) {
                                $update_stmt->execute([$item['item_id']]);
                                error_log("Updated remaining_quantity to 0 for item_id: {$item['item_id']}");
                            }
                        }
                        
                        // Prepare print view data
                        $print_data = [
                            'order_sheet_no' => $order_sheet_no,
                            'date' => date('Y-m-d H:i:s'),
                            'responsible_name' => $responsible['name'],
                            'function_type' => $function_type,
                            'function_date' => $function_date,
                            'day_night' => $day_night ?: 'N/A',
                            'items' => $print_items
                        ];
                        
                        $conn->commit();
                        error_log("Transaction committed for order_sheet_no: $order_sheet_no");
                        echo "<div class='bg-teal-100 text-teal-800 p-4 rounded-lg mb-6 text-center'>Order submitted successfully. Order Sheet No: $order_sheet_no. Printing...</div>";
                        echo "<script>printReceipt(" . json_encode($print_data) . ");</script>";
                    } catch (Exception $e) {
                        $conn->rollBack();
                        error_log("Transaction rolled back: " . $e->getMessage());
                        throw $e;
                    }
                } catch (Exception $e) {
                    error_log("Submission error: " . $e->getMessage());
                    echo "<div class='bg-red-100 text-red-800 p-4 rounded-lg mb-6 text-center'>" . htmlspecialchars($e->getMessage()) . "</div>";
                }
            }
            
            // Fetch available items with units
            $stmt = $conn->prepare("
                SELECT i.id AS item_id, i.item_name, i.unit
                FROM inventory i
                ORDER BY i.item_name
            ");
            $stmt->execute();
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Fetched " . count($items) . " unique available items");
            
            // Fetch buffer stock items for SKY Buffer Stock Refill
            $buffer_items = [];
            $stmt = $conn->prepare("
                SELECT b.item_id, i.item_name, i.unit, b.quantity, b.remaining_quantity, b.usage
                FROM hggkitchen_buffer b
                JOIN inventory i ON b.item_id = i.id
                WHERE b.remaining_quantity < b.quantity AND b.remaining_quantity > 0
            ");
            $stmt->execute();
            $buffer_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Fetched " . count($buffer_items) . " buffer stock items with remaining quantity > 0");
            
            // Fetch responsible persons
            $stmt = $conn->prepare("SELECT id, name FROM responsible");
            $stmt->execute();
            $responsibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Fetched " . count($responsibles) . " responsible persons");
            
            // Fetch pending requests for display
            $stmt = $conn->prepare("
                SELECT os.order_sheet_no, os.item_id, i.item_name, 
                       os.requested_qty, os.requested_unit, 
                       os.request_date, os.status, 
                       r.name AS responsible_name,
                       os.function_type, os.function_date, os.day_night
                FROM hggorder_sheet os
                JOIN inventory i ON os.item_id = i.id
                LEFT JOIN responsible r ON os.responsible_id = r.id
                ORDER BY os.order_sheet_no DESC, os.item_id
            ");
            $stmt->execute();
            $pending_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Aggregate items by order_sheet_no
            $grouped_requests = [];
            foreach ($pending_requests as $request) {
                $order_sheet_no = $request['order_sheet_no'];
                if (!isset($grouped_requests[$order_sheet_no])) {
                    $grouped_requests[$order_sheet_no] = [
                        'order_sheet_no' => $order_sheet_no,
                        'items' => [],
                        'request_date' => $request['request_date'],
                        'status' => $request['status'],
                        'responsible_name' => $request['responsible_name'],
                        'function_type' => $request['function_type'],
                        'function_date' => $request['function_date'],
                        'day_night' => $request['day_night']
                    ];
                }
                $grouped_requests[$order_sheet_no]['items'][] = [
                    'item_id' => $request['item_id'],
                    'item_name' => $request['item_name'],
                    'requested_qty' => $request['requested_qty'],
                    'unit' => $request['requested_unit'] ?? 'Unit'
                ];
            }
            error_log("Fetched and grouped " . count($grouped_requests) . " requests");
            
        } catch(PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            echo "<div class='bg-red-100 text-red-800 p-4 rounded-lg mb-6 text-center'>Database connection failed.</div>";
        }
        ?>

        <!-- Print view (hidden by default) -->
        <div id="print-view" style="display: none;">
            <div class="header">Kitchen Order Sheet</div>
            <div>Order No: <span id="print-order-no"></span></div>
            <div>Function: <span id="print-function-type"></span></div>
            <div>Date: <span id="print-date"></span></div>
            <div>Function Date: <span id="print-function-date"></span></div>
            <div>Time: <span id="print-day-night"></span></div>
            <div class="divider"></div>
            <table>
                <tr>
                    <th style="width: 60%;">Item Name</th>
                    <th style="width: 20%;">Qty</th>
                    <th style="width: 20%;">Unit</th>
                </tr>
                <tbody id="print-items"></tbody>
            </table>
            <div class="divider"></div>
            <div>Responsible: <span id="print-responsible"></span></div>
        </div>

        <div class="bg-white p-6 rounded-xl mb-8 shadow-md">
            <h3 class="text-xl font-semibold text-teal-900 mb-4">Select Function Details</h3>
            <div class="space-y-4">
                <div>
                    <label for="function_type" class="block text-sm font-medium text-gray-700 mb-2">Function Type</label>
                    <select id="function_type" name="function_type" required class="block w-full max-w-md p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 sm:text-sm transition duration-200">
                        <option value="">Select Function Type</option>
                        <option value="HGG Restaurant">HGG Restaurant</option>
                        <option value="HGG/R Night Function">HGG/R Night Function</option>
                        <option value="HGG/R Birthday Function">HGG/R Birthday Function</option>
                        <option value="HGG/R Buffer Stock Refill">HGG/R Buffer Stock Refill</option>
                    </select>
                </div>
                <div>
                    <label for="function_date" class="block text-sm font-medium text-gray-700 mb-2">Select Date</label>
                    <input type="date" id="function_date" name="function_date" required class="block w-full max-w-md p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 sm:text-sm transition duration-200">
                </div>
                <div>
                    <label for="day_night" class="block text-sm font-medium text-gray-700 mb-2">Function Time (Optional)</label>
                    <select id="day_night" name="day_night" class="block w-full max-w-md p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 sm:text-sm transition duration-200">
                        <option value="">Select Day or Night</option>
                        <option value="Day">Day Function</option>
                        <option value="Night">Night Function</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl mb-8 shadow-md">
            <h3 class="text-xl font-semibold text-teal-900 mb-4">Add Items to Order</h3>
            <div class="space-y-4">
                <div class="relative">
                    <label for="item_search" class="block text-sm font-medium text-gray-700 mb-2">Search Item</label>
                    <input type="text" id="item_search" class="block w-full max-w-md p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 sm:text-sm transition duration-200" placeholder="Search for an item..." autocomplete="off">
                    <input type="hidden" id="item_id">
                    <div id="search_results" class="search-results hidden"></div>
                </div>
                <div class="flex space-x-4">
                    <div class="flex-1">
                        <label for="item_qty" class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                        <input type="number" id="item_qty" min="0.001" step="0.001" class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 sm:text-sm transition duration-200" placeholder="Enter quantity">
                    </div>
                    <div class="flex-1">
                        <label for="unit_type" class="block text-sm font-medium text-gray-700 mb-2">Unit Type</label>
                        <div id="unit_display" class="unit-display hidden">-</div>
                        <select id="unit_type" class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 sm:text-sm transition duration-200 hidden">
                            <option value="">Select Unit</option>
                            <option value="kg">kg</option>
                            <option value="g">g</option>
                            <option value="l">l</option>
                            <option value="ml">ml</option>
                        </select>
                    </div>
                </div>
                <button type="button" id="add_item" class="bg-teal-600 text-white p-3 rounded-lg hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 transition duration-200">Add Item</button>
            </div>
            <div id="item_list" class="mt-4">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Selected Items</h4>
                <div id="items_container" class="space-y-2"></div>
            </div>
        </div>

        <form method="post" action="" id="order_form" class="space-y-6">
            <input type="hidden" name="items_json" id="items_json">
            <input type="hidden" name="submit_request" value="1">
            <input type="hidden" name="function_type" id="function_type_hidden">
            <input type="hidden" name="function_date" id="function_date_hidden">
            <input type="hidden" name="day_night" id="day_night_hidden">
            <div>
                <label for="responsible_id" class="block text-sm font-medium text-gray-700 mb-2">Responsible Person</label>
                <select name="responsible_id" id="responsible_id" required class="block w-full max-w-md p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 sm:text-sm transition duration-200">
                    <option value="">Select Responsible Person</option>
                    <?php foreach ($responsibles as $responsible): ?>
                        <option value="<?php echo htmlspecialchars($responsible['id']); ?>">
                            <?php echo htmlspecialchars($responsible['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input type="password" name="password" id="password" required class="block w-full max-w-md p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 sm:text-sm transition duration-200" placeholder="Enter password">
            </div>
            <div>
                <button type="button" id="submit_order" class="w-full max-w-md bg-teal-600 text-white p-3 rounded-lg hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 transition duration-200">Submit and Print Order</button>
            </div>
        </form>

        <div id="confirm_modal" class="modal">
            <div class="modal-content">
                <h3 class="text-lg font-semibold text-teal-900 mb-4">Confirm Order Submission</h3>
                <p class="text-gray-600 mb-6">I have read the order sheet and confirm all items are needed. Proceed with submission and printing?</p>
                <div class="flex justify-end gap-4">
                    <button id="cancel_submit" class="bg-gray-300 text-gray-800 p-2 rounded-lg hover:bg-gray-400 transition duration-200">Cancel</button>
                    <button id="confirm_submit" class="bg-teal-600 text-white p-2 rounded-lg hover:bg-teal-700 transition duration-200">Confirm</button>
                </div>
            </div>
        </div>

        <h3 class="text-xl font-semibold text-teal-900 mt-8 mb-4">Pending Orders</h3>
        <div id="pending_orders_container">
            <?php if (empty($grouped_requests)): ?>
                <p class="text-gray-600 text-center">No pending orders.</p>
            <?php else: ?>
                <div class="table-container">
                    <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                        <thead class="bg-teal-100">
                            <tr>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Order Sheet No</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Function Location</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Function Date</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Function Time</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Items</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Quantities</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Units</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Request Date</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Responsible Person</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Status</th>
                            </tr>
                        </thead>
                        <tbody id="pending_orders_table">
                            <?php foreach ($grouped_requests as $index => $request): ?>
                                <?php
                                $item_names = array_column($request['items'], 'item_name');
                                $quantities = array_column($request['items'], 'requested_qty');
                                $units = array_column($request['items'], 'unit');
                                ?>
                                <tr class="<?php echo $index % 2 ? 'bg-gray-50' : 'bg-white'; ?> hover:bg-teal-50 transition duration-200">
                                    <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($request['order_sheet_no']); ?></td>
                                    <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($request['function_type'] ?? 'N/A'); ?></td>
                                    <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($request['function_date'] ?? 'N/A'); ?></td>
                                    <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($request['day_night'] ?? 'N/A'); ?></td>
                                    <td class="py-3 px-4 border-b text-sm" title="<?php echo htmlspecialchars(implode(', ', $item_names)); ?>">
                                        <?php echo htmlspecialchars(implode(', ', $item_names)); ?>
                                    </td>
                                    <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars(implode(', ', $quantities)); ?></td>
                                    <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars(implode(', ', $units)); ?></td>
                                    <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($request['request_date']); ?></td>
                                    <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($request['responsible_name'] ?? 'N/A'); ?></td>
                                    <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($request['status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const items = <?php echo json_encode($items); ?>;
        const bufferItems = <?php echo json_encode($buffer_items); ?>;
        let groupedRequests = <?php echo json_encode($grouped_requests); ?>;
        const searchInput = document.getElementById('item_search');
        const itemIdInput = document.getElementById('item_id');
        const itemQtyInput = document.getElementById('item_qty');
        const unitTypeSelect = document.getElementById('unit_type');
        const unitDisplay = document.getElementById('unit_display');
        const addItemButton = document.getElementById('add_item');
        const itemsContainer = document.getElementById('items_container');
        const itemsJsonInput = document.getElementById('items_json');
        const searchResults = document.getElementById('search_results');
        const submitOrderButton = document.getElementById('submit_order');
        const confirmModal = document.getElementById('confirm_modal');
        const cancelSubmit = document.getElementById('cancel_submit');
        const confirmSubmit = document.getElementById('confirm_submit');
        const pendingOrdersContainer = document.getElementById('pending_orders_container');
        const pendingOrdersTable = document.getElementById('pending_orders_table');
        const functionTypeSelect = document.getElementById('function_type');
        const functionTypeHidden = document.getElementById('function_type_hidden');
        const functionDateInput = document.getElementById('function_date');
        const functionDateHidden = document.getElementById('function_date_hidden');
        const dayNightSelect = document.getElementById('day_night');
        const dayNightHidden = document.getElementById('day_night_hidden');
        let selectedItems = [];
        
        // Units that allow selection
        const selectableUnits = ['kg', 'l'];
        let currentItemUnit = '';

        // Set minimum date for function_date to today
        const today = new Date();
        const todayFormatted = today.toISOString().split('T')[0];
        functionDateInput.setAttribute('min', todayFormatted);
        console.log('Set function_date min to:', todayFormatted);

        // Validate function_date on change
        functionDateInput.addEventListener('change', function() {
            if (this.value) {
                const selectedDate = new Date(this.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                if (selectedDate < today) {
                    alert('Function date cannot be in the past. Please select today or a future date.');
                    this.value = todayFormatted;
                }
            }
            functionDateHidden.value = this.value;
            console.log('Function date updated:', this.value);
        });

        // Handle function type change to populate buffer stock items
        functionTypeSelect.addEventListener('change', function() {
            console.log('Function type changed:', this.value);
            selectedItems = [];
            updateItemList();
            searchInput.value = '';
            itemIdInput.value = '';
            itemQtyInput.value = '';
            resetUnitDisplay();
            searchResults.classList.add('hidden');

            if (this.value === 'HGG/R Buffer Stock Refill') {
                console.log('Populating buffer stock items:', bufferItems);
                itemQtyInput.disabled = true;
                itemQtyInput.classList.add('bg-gray-200');
                searchInput.disabled = true;
                addItemButton.disabled = true;
                bufferItems.forEach(item => {
                    const usage = item.quantity - item.remaining_quantity;
                    if (usage > 0) {
                        selectedItems.push({
                            item_id: item.item_id,
                            item_name: item.item_name,
                            requested_qty: usage,
                            unit: item.unit || 'Unit',
                            isBufferItem: true
                        });
                    }
                });
                updateItemList();
            } else {
                itemQtyInput.disabled = false;
                itemQtyInput.classList.remove('bg-gray-200');
                searchInput.disabled = false;
                addItemButton.disabled = false;
            }
        });

        function resetUnitDisplay() {
            unitDisplay.classList.add('hidden');
            unitTypeSelect.classList.add('hidden');
            unitDisplay.textContent = '-';
            unitTypeSelect.value = '';
            currentItemUnit = '';
        }

        function setupUnitSelection(baseUnit) {
            console.log('Setting up unit selection for:', baseUnit);
            resetUnitDisplay();
            
            if (!baseUnit) return;
            
            const normalizedUnit = baseUnit.toLowerCase().trim();
            currentItemUnit = normalizedUnit;
            
            const isSelectable = selectableUnits.includes(normalizedUnit);
            
            if (isSelectable) {
                unitTypeSelect.classList.remove('hidden');
                
                if (normalizedUnit === 'kg') {
                    unitTypeSelect.innerHTML = `
                        <option value="kg">kg</option>
                        <option value="g">g</option>
                    `;
                    unitTypeSelect.value = 'kg';
                } else if (normalizedUnit === 'l') {
                    unitTypeSelect.innerHTML = `
                        <option value="l">l</option>
                        <option value="ml">ml</option>
                    `;
                    unitTypeSelect.value = 'l';
                }
            } else {
                unitDisplay.classList.remove('hidden');
                unitDisplay.textContent = baseUnit;
            }
        }

        function printReceipt(data) {
            console.log('Printing receipt:', JSON.stringify(data));
            document.getElementById('print-order-no').textContent = data.order_sheet_no;
            document.getElementById('print-function-type').textContent = data.function_type;
            document.getElementById('print-date').textContent = data.date;
            document.getElementById('print-function-date').textContent = data.function_date;
            document.getElementById('print-day-night').textContent = data.day_night || 'N/A';
            document.getElementById('print-responsible').textContent = data.responsible_name;
            const itemsBody = document.getElementById('print-items');
            itemsBody.innerHTML = '';
            data.items.forEach(item => {
                console.log('Adding item to print:', item);
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td style="width: 60%;">${item.item_name}</td>
                    <td style="width: 20%;">${item.requested_qty}</td>
                    <td style="width: 20%;">${item.unit}</td>
                `;
                itemsBody.appendChild(row);
            });
            window.print();
        }

        function updatePendingOrdersTable(orders) {
            groupedRequests = orders;
            if (Object.keys(orders).length === 0) {
                pendingOrdersContainer.innerHTML = '<p class="text-gray-600 text-center">No pending orders.</p>';
                return;
            }

            let tableHTML = `
                <div class="table-container">
                    <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                        <thead class="bg-teal-100">
                            <tr>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Order Sheet No</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Function Location</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Function Date</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Function Time</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Items</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Quantities</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Units</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Request Date</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Responsible Person</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Status</th>
                            </tr>
                        </thead>
                        <tbody id="pending_orders_table">
            `;
            Object.values(orders).forEach((request, index) => {
                const itemNames = request.items.map(item => item.item_name);
                const quantities = request.items.map(item => item.requested_qty);
                const units = request.items.map(item => item.unit);
                tableHTML += `
                    <tr class="${index % 2 ? 'bg-gray-50' : 'bg-white'} hover:bg-teal-50 transition duration-200">
                        <td class="py-3 px-4 border-b text-sm">${request.order_sheet_no}</td>
                        <td class="py-3 px-4 border-b text-sm">${request.function_type || 'N/A'}</td>
                        <td class="py-3 px-4 border-b text-sm">${request.function_date || 'N/A'}</td>
                        <td class="py-3 px-4 border-b text-sm">${request.day_night || 'N/A'}</td>
                        <td class="py-3 px-4 border-b text-sm" title="${itemNames.join(', ')}">${itemNames.join(', ')}</td>
                        <td class="py-3 px-4 border-b text-sm">${quantities.join(', ')}</td>
                        <td class="py-3 px-4 border-b text-sm">${units.join(', ')}</td>
                        <td class="py-3 px-4 border-b text-sm">${request.request_date}</td>
                        <td class="py-3 px-4 border-b text-sm">${request.responsible_name || 'N/A'}</td>
                        <td class="py-3 px-4 border-b text-sm">${request.status}</td>
                    </tr>
                `;
            });
            tableHTML += `
                        </tbody>
                    </table>
                </div>
            `;
            pendingOrdersContainer.innerHTML = tableHTML;
        }

        function fetchPendingOrders() {
            fetch('fetch_pending_hggkitchen_orders.php')
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    console.log('Fetched pending orders:', data);
                    updatePendingOrdersTable(data);
                })
                .catch(error => {
                    console.error('Error fetching pending orders:', error);
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'bg-red-100 text-red-800 p-4 rounded-lg mb-6 text-center';
                    errorDiv.textContent = 'Error loading pending orders.';
                    pendingOrdersContainer.prepend(errorDiv);
                });
        }

        setInterval(fetchPendingOrders, 10000);
        fetchPendingOrders();

        searchInput.addEventListener('input', function() {
            console.log('Search input:', this.value);
            if (functionTypeSelect.value === 'HGG/R Buffer Stock Refill') return;
            const query = this.value.toLowerCase();
            searchResults.innerHTML = '';
            resetUnitDisplay();
            if (query) {
                const filteredItems = items.filter(item => item.item_name.toLowerCase().includes(query));
                console.log('Filtered items:', filteredItems.length);
                if (filteredItems.length > 0) {
                    searchResults.classList.remove('hidden');
                    filteredItems.forEach(item => {
                        const div = document.createElement('div');
                        div.textContent = `${item.item_name} (${item.unit || 'Unit'})`;
                        div.dataset.itemId = item.item_id;
                        div.dataset.unit = item.unit || 'Unit';
                        div.addEventListener('click', function() {
                            console.log('Selected item:', item.item_name, item.item_id, item.unit);
                            searchInput.value = item.item_name;
                            itemIdInput.value = item.item_id;
                            setupUnitSelection(item.unit || 'Unit');
                            itemQtyInput.value = '';
                            itemQtyInput.focus();
                            searchResults.classList.add('hidden');
                        });
                        searchResults.appendChild(div);
                    });
                } else {
                    searchResults.classList.add('hidden');
                }
            } else {
                searchResults.classList.add('hidden');
            }
        });

        document.addEventListener('click', function(e) {
            if (!searchResults.contains(e.target) && e.target !== searchInput) {
                searchResults.classList.add('hidden');
            }
        });

        addItemButton.addEventListener('click', function() {
            console.log('Add item clicked');
            if (functionTypeSelect.value === 'HGG/R Buffer Stock Refill') {
                alert('Items for SKY Buffer Stock Refill are automatically populated and cannot be manually added.');
                return;
            }
            if (!functionTypeSelect.value || !functionDateInput.value) {
                console.error('Missing required fields: type=', functionTypeSelect.value, 'date=', functionDateInput.value);
                alert('Please select a function type and date before adding items.');
                return;
            }
            if (!itemIdInput.value || !itemQtyInput.value || itemQtyInput.value <= 0) {
                console.error('Invalid input: item_id=', itemIdInput.value, 'qty=', itemQtyInput.value);
                alert('Please select an item and enter a valid quantity.');
                return;
            }

            const item = items.find(i => i.item_id == itemIdInput.value);
            if (!item) {
                console.error('Item not found: item_id=', itemIdInput.value);
                alert('Selected item not found.');
                return;
            }

            const requestedQty = parseFloat(itemQtyInput.value);
            let selectedUnit;
            if (selectableUnits.includes(currentItemUnit)) {
                selectedUnit = unitTypeSelect.value;
            } else {
                selectedUnit = currentItemUnit;
            }
            if (!selectedUnit) {
                console.error('No unit selected for item:', item.item_name);
                alert('Please select a valid unit type.');
                return;
            }
            
            const existingItemIndex = selectedItems.findIndex(i => i.item_id == item.item_id && i.unit === selectedUnit);
            
            if (existingItemIndex >= 0) {
                selectedItems[existingItemIndex].requested_qty += requestedQty;
                console.log('Updated item quantity:', item.item_name, selectedItems[existingItemIndex].requested_qty, selectedUnit);
            } else {
                selectedItems.push({
                    item_id: item.item_id,
                    item_name: item.item_name,
                    requested_qty: requestedQty,
                    unit: selectedUnit,
                    isBufferItem: false
                });
                console.log('Added new item:', item.item_name, requestedQty, selectedUnit);
            }

            updateItemList();
            searchInput.value = '';
            itemIdInput.value = '';
            itemQtyInput.value = '';
            resetUnitDisplay();
        });

        function updateItemList() {
            console.log('Updating item list:', selectedItems);
            itemsContainer.innerHTML = '';
            selectedItems.forEach((item, index) => {
                const row = document.createElement('div');
                row.className = 'item-row';
                row.innerHTML = `
                    <span class="flex-1 text-sm font-medium">${item.item_name}</span>
                    <span class="w-24 text-sm">${item.requested_qty} ${item.unit}</span>
                    <button type="button" class="text-red-600 hover:text-red-800 text-sm font-medium" onclick="removeItem(${index})" ${item.isBufferItem ? 'disabled' : ''}>Remove</button>
                `;
                itemsContainer.appendChild(row);
            });
            itemsJsonInput.value = JSON.stringify(selectedItems);
            functionTypeHidden.value = functionTypeSelect.value;
            functionDateHidden.value = functionDateInput.value;
            dayNightHidden.value = dayNightSelect.value;
            console.log('Items JSON updated:', itemsJsonInput.value);
            console.log('Function type updated:', functionTypeHidden.value);
            console.log('Function date updated:', functionDateHidden.value);
            console.log('Day/Night updated:', dayNightHidden.value);
        }

        function removeItem(index) {
            console.log('Removing item at index:', index);
            if (selectedItems[index].isBufferItem) {
                alert('Buffer stock items cannot be removed.');
                return;
            }
            selectedItems.splice(index, 1);
            updateItemList();
        }

        submitOrderButton.addEventListener('click', function() {
            console.log('Submit button clicked, selectedItems:', selectedItems);
            if (!functionTypeSelect.value || !functionDateInput.value) {
                console.error('Missing required fields: type=', functionTypeSelect.value, 'date=', functionDateInput.value);
                alert('Please select a function type and date.');
                return;
            }
            if (selectedItems.length === 0) {
                console.error('No items selected');
                alert('Please add at least one item to the order.');
                return;
            }
            if (!document.getElementById('responsible_id').value || !document.getElementById('password').value) {
                console.error('Missing responsible_id or password');
                alert('Please select a responsible person and enter a password.');
                return;
            }
            functionDateHidden.value = functionDateInput.value;
            console.log('Submitting form with function_date_hidden:', functionDateHidden.value);
            submitOrderButton.classList.add('loading');
            submitOrderButton.textContent = 'Processing...';
            confirmModal.style.display = 'flex';
        });

        cancelSubmit.addEventListener('click', function() {
            console.log('Cancel submit clicked');
            confirmModal.style.display = 'none';
            submitOrderButton.classList.remove('loading');
            submitOrderButton.textContent = 'Submit and Print Order';
        });

        confirmSubmit.addEventListener('click', function() {
            console.log('Confirm submit clicked');
            confirmModal.style.display = 'none';
            console.log('Submitting form with items_json:', itemsJsonInput.value);
            console.log('Submitting form with function_type:', functionTypeHidden.value);
            console.log('Submitting form with function_date:', functionDateHidden.value);
            console.log('Submitting form with day_night:', dayNightHidden.value);
            document.getElementById('order_form').submit();
        });
    </script>
</body>
</html>