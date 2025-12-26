<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sky Restaurant Buffer Stock Report</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .stock-low {
            background-color: #fee2e2;
        }
        .stock-medium {
            background-color: #fef3c7;
        }
        .stock-good {
            background-color: #dcfce7;
        }
        .table-header {
            background-color: #4f46e5;
            color: white;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="../accounts.php" class="inline-block bg-yellow-500 text-white py-2 px-4 rounded-md hover:bg-yellow-600">Back</a>
        </div>

        <h1 class="text-3xl font-bold mb-6 text-center text-indigo-800">Sky Restaurant Buffer Stock Report</h1>

        <!-- Filter Form -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-semibold mb-4 text-indigo-700">Filter Report</h2>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="filter_type" class="block text-sm font-medium text-gray-700">Filter Type</label>
                    <select name="filter_type" id="filter_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" onchange="toggleFilterInputs()">
                        <option value="all" <?php echo (!isset($_GET['filter_type']) || $_GET['filter_type'] == 'all') ? 'selected' : ''; ?>>All</option>
                        <option value="daily" <?php echo (isset($_GET['filter_type']) && $_GET['filter_type'] == 'daily') ? 'selected' : ''; ?>>Daily</option>
                        <option value="monthly" <?php echo (isset($_GET['filter_type']) && $_GET['filter_type'] == 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                        <option value="yearly" <?php echo (isset($_GET['filter_type']) && $_GET['filter_type'] == 'yearly') ? 'selected' : ''; ?>>Yearly</option>
                        <option value="specific_date" <?php echo (isset($_GET['filter_type']) && $_GET['filter_type'] == 'specific_date') ? 'selected' : ''; ?>>Specific Date</option>
                    </select>
                </div>
                <div id="date_input" class="hidden">
                    <label for="specific_date" class="block text-sm font-medium text-gray-700">Select Date</label>
                    <input type="date" name="specific_date" id="specific_date" value="<?php echo isset($_GET['specific_date']) ? htmlspecialchars($_GET['specific_date']) : ''; ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div id="month_input" class="hidden">
                    <label for="month_year" class="block text-sm font-medium text-gray-700">Select Month</label>
                    <input type="month" name="month_year" id="month_year" value="<?php echo isset($_GET['month_year']) ? htmlspecialchars($_GET['month_year']) : ''; ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div id="year_input" class="hidden">
                    <label for="year" class="block text-sm font-medium text-gray-700">Select Year</label>
                    <input type="number" name="year" id="year" value="<?php echo isset($_GET['year']) ? htmlspecialchars($_GET['year']) : date('Y'); ?>" min="2000" max="<?php echo date('Y'); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700">Apply Filter</button>
                </div>
            </form>
        </div>

        <!-- Stock Summary -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-semibold mb-4 text-indigo-700">Stock Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-100 p-4 rounded-lg">
                    <h3 class="font-medium text-blue-800">Total Items</h3>
                    <p class="text-2xl font-bold text-blue-600" id="total-items">0</p>
                </div>
                <div class="bg-yellow-100 p-4 rounded-lg">
                    <h3 class="font-medium text-yellow-800">Low Stock Items</h3>
                    <p class="text-2xl font-bold text-yellow-600" id="low-stock">0</p>
                </div>
                <div class="bg-green-100 p-4 rounded-lg">
                    <h3 class="font-medium text-green-800">Items Need Reordering</h3>
                    <p class="text-2xl font-bold text-green-600" id="reorder-items">0</p>
                </div>
            </div>
        </div>

        <!-- Print Button and Table -->
        <div class="mb-4">
            <button onclick="printTable()" class="bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700">Print Table</button>
        </div>

        <?php
        // Database connection
        $servername = "localhost";
        $username = "hotelgrandguardi_root";
        $password = "Sun123flower@";
        $dbname = "hotelgrandguardi_wedding_bliss";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("<div class='text-red-500 text-center p-4 bg-red-100 rounded-lg'>Connection failed: " . $conn->connect_error . "</div>");
        }

        // Initialize filter conditions
        $filter_condition = "";
        $filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : 'all';

        if ($filter_type === 'daily') {
            $filter_condition = "WHERE DATE(h.update_timestamp) = CURDATE()";
        } elseif ($filter_type === 'monthly' && !empty($_GET['month_year'])) {
            $month_year = $conn->real_escape_string($_GET['month_year']);
            $filter_condition = "WHERE DATE_FORMAT(h.update_timestamp, '%Y-%m') = '$month_year'";
        } elseif ($filter_type === 'yearly' && !empty($_GET['year'])) {
            $year = $conn->real_escape_string($_GET['year']);
            $filter_condition = "WHERE YEAR(h.update_timestamp) = '$year'";
        } elseif ($filter_type === 'specific_date' && !empty($_GET['specific_date'])) {
            $specific_date = $conn->real_escape_string($_GET['specific_date']);
            $filter_condition = "WHERE DATE(h.update_timestamp) = '$specific_date'";
        }

        // SQL query to fetch buffer stock report
        $sql = "SELECT 
                    i.id AS item_id,
                    i.item_name,
                    b.quantity AS buffer_quantity,
                    b.remaining_quantity AS buffer_remaining_quantity,
                    b.usage,
                    b.last_updated AS buffer_last_updated,
                    h.quantity_updated AS history_quantity_updated,
                    h.update_timestamp AS history_update_timestamp
                FROM 
                    inventory i
                    LEFT JOIN skykitchen_buffer b ON i.id = b.item_id
                    LEFT JOIN skykitchen_buffer_history h ON i.id = h.item_id
                $filter_condition
                ORDER BY 
                    i.id, h.update_timestamp DESC";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Initialize counters and tracking for unique items
            $unique_items = [];
            $low_stock_items = 0;
            $reorder_items = 0;
            
            // Start table
            echo '<div id="stock-table" class="overflow-x-auto bg-white rounded-lg shadow">';
            echo '<table class="min-w-full">';
            echo '<thead>';
            echo '<tr class="table-header">';
            echo '<th class="py-3 px-4 text-left">Item ID</th>';
            echo '<th class="py-3 px-4 text-left">Item Name</th>';
            echo '<th class="py-3 px-4 text-center">Buffer Quantity</th>';
            echo '<th class="py-3 px-4 text-center">Remaining Quantity</th>';
            echo '<th class="py-3 px-4 text-center">Usage</th>';
            echo '<th class="py-3 px-4 text-center">Last Updated</th>';
            echo '<th class="py-3 px-4 text-center">Status</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            // Fetch and display rows
            while ($row = $result->fetch_assoc()) {
                // Track unique items
                $item_id = $row['item_id'];
                if (!in_array($item_id, $unique_items)) {
                    $unique_items[] = $item_id;
                }
                
                // Get buffer quantity
                $buffer_quantity = $row['buffer_quantity'] ?? 0;
                
                // Use history_quantity_updated directly as remaining_quantity
                $remaining_quantity = $row['history_quantity_updated'] ?? $row['buffer_remaining_quantity'] ?? 0;
                
                // Calculate usage: buffer_quantity - remaining_quantity
                $usage = $buffer_quantity - $remaining_quantity;
                
                // Use the history update timestamp as last updated
                $last_updated = $row['history_update_timestamp'] ?? $row['buffer_last_updated'] ?? 'N/A';
                
                // Determine stock status
                $status_class = '';
                $status_text = '';
                $percentage_remaining = $buffer_quantity > 0 ? ($remaining_quantity / $buffer_quantity) * 100 : 0;
                
                if ($percentage_remaining < 20) {
                    $status_class = 'stock-low';
                    $status_text = 'Low Stock';
                    $low_stock_items++;
                    $reorder_items++;
                } else if ($percentage_remaining < 50) {
                    $status_class = 'stock-medium';
                    $status_text = 'Medium Stock';
                } else {
                    $status_class = 'stock-good';
                    $status_text = 'Good Stock';
                }

                echo '<tr class="hover:bg-gray-50 ' . $status_class . '">';
                echo '<td class="py-3 px-4 border-b">' . htmlspecialchars($row['item_id']) . '</td>';
                echo '<td class="py-3 px-4 border-b font-medium">' . htmlspecialchars($row['item_name'] ?? 'N/A') . '</td>';
                echo '<td class="py-3 px-4 border-b text-center">' . htmlspecialchars($buffer_quantity) . '</td>';
                echo '<td class="py-3 px-4 border-b text-center">' . htmlspecialchars($remaining_quantity) . '</td>';
                echo '<td class="py-3 px-4 border-b text-center">' . htmlspecialchars($usage) . '</td>';
                echo '<td class="py-3 px-4 border-b text-center">' . htmlspecialchars($last_updated) . '</td>';
                echo '<td class="py-3 px-4 border-b text-center">' . $status_text . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</div>';

            // Calculate total unique items
            $total_items = count($unique_items);
            
            // Update summary with JavaScript
            echo '<script>
                document.getElementById("total-items").textContent = "' . $total_items . '";
                document.getElementById("low-stock").textContent = "' . $low_stock_items . '";
                document.getElementById("reorder-items").textContent = "' . $reorder_items . '";
            </script>';
        } else {
            echo '<div id="stock-table" class="text-center text-gray-500 mt-4 p-6 bg-white rounded-lg shadow">No records found.</div>';
        }

        // Close connection
        $conn->close();
        ?>


    <!-- JavaScript for filter inputs and print functionality -->
    <script>
        function toggleFilterInputs() {
            const filterType = document.getElementById('filter_type').value;
            document.getElementById('date_input').classList.add('hidden');
            document.getElementById('month_input').classList.add('hidden');
            document.getElementById('year_input').classList.add('hidden');
            
            if (filterType === 'specific_date') {
                document.getElementById('date_input').classList.remove('hidden');
            } else if (filterType === 'monthly') {
                document.getElementById('month_input').classList.remove('hidden');
            } else if (filterType === 'yearly') {
                document.getElementById('year_input').classList.remove('hidden');
            }
        }
        // Run on page load to set initial state
        toggleFilterInputs();

        function printTable() {
            const table = document.getElementById('stock-table').outerHTML;
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Sky Restaurant Buffer Stock Report - Print</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
                        th { background-color: #4f46e5; color: white; }
                        .stock-low { background-color: #fee2e2; }
                        .stock-medium { background-color: #fef3c7; }
                        .stock-good { background-color: #dcfce7; }
                        .hover\\:bg-gray-50:hover { background-color: #f9fafb; }
                        td:first-child, th:first-child { text-align: left; }
                        td:nth-child(2), th:nth-child(2) { text-align: left; }
                    </style>
                </head>
                <body>
                    <h1 style="text-align: center; color: #4b0082;">Sky Restaurant Buffer Stock Report</h1>
                    ${table}
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }
    </script>
</body>
</html>