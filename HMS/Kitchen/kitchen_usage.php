<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Kitchen Buffer Stock Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .fade-in { animation: fadeIn 0.5s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .suggestion-list { max-height: 200px; overflow-y: auto; z-index: 50; }
        .suggestion-item:hover { background-color: #e0e7ff; }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 to-gray-200 min-h-screen">
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <header class="text-center mb-12 fade-in">
        <h1 class="text-5xl font-bold text-gray-900">Main Kitchen Buffer Stock</h1>
        <p class="text-lg text-gray-600 mt-2">Effortlessly manage your kitchen inventory</p>
    </header>

    <?php
    $servername = "localhost";
    $username = "hotelgrandguardi_root";
    $password = "Sun123flower@";
    $dbname = "hotelgrandguardi_wedding_bliss";

    $conn = new mysqli($servername, $username, $password);
    if ($conn->connect_error) {
        die("<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 text-center rounded-lg fade-in'>Connection failed: " . $conn->connect_error . "</div>");
    }

    $sql = "CREATE DATABASE IF NOT EXISTS `$dbname`";
    if ($conn->query($sql) === TRUE) {
        $conn->select_db($dbname);
    } else {
        die("<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 text-center rounded-lg fade-in'>Error creating database: " . $conn->error . "</div>");
    }

    // Create kitchen_buffer_history table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS kitchen_buffer_history (
        history_id INT AUTO_INCREMENT PRIMARY KEY,
        item_id INT NOT NULL,
        quantity_updated INT NOT NULL,
        update_timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (item_id) REFERENCES inventory(id)
    )";
    if ($conn->query($sql) !== TRUE) {
        die("<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 text-center rounded-lg fade-in'>Error creating history table: " . $conn->error . "</div>");
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_buffer'])) {
        $item_id = $_POST['item_id'];
        $remaining_quantity = $_POST['remaining_quantity'];

        if (!is_numeric($item_id)) {
            echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg fade-in'>Please select a valid item.</div>";
        } elseif (!is_numeric($remaining_quantity) || $remaining_quantity < 0) {
            echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg fade-in'>Invalid input provided.</div>";
        } else {
            // Get current buffer quantity from database
            $check_sql = "SELECT quantity FROM kitchen_buffer WHERE item_id = ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param("i", $item_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $buffer_quantity = $row['quantity'];
                
                if ($remaining_quantity > $buffer_quantity) {
                    echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg fade-in'>Remaining quantity cannot be greater than buffer quantity.</div>";
                } else {
                    // Update buffer stock
                    $update_sql = "UPDATE kitchen_buffer SET remaining_quantity = ?, last_updated = CURRENT_TIMESTAMP WHERE item_id = ?";
                    $stmt = $conn->prepare($update_sql);
                    $stmt->bind_param("ii", $remaining_quantity, $item_id);
                    
                    if ($stmt->execute()) {
                        // Insert into history table
                        $history_sql = "INSERT INTO kitchen_buffer_history (item_id, quantity_updated) VALUES (?, ?)";
                        $history_stmt = $conn->prepare($history_sql);
                        $history_stmt->bind_param("ii", $item_id, $remaining_quantity);
                        $history_stmt->execute();
                        $history_stmt->close();
                        
                        echo "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg fade-in'>Buffer stock updated successfully!</div>";
                    } else {
                        echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg fade-in'>Error updating buffer stock: " . $conn->error . "</div>";
                    }
                }
            } else {
                echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg fade-in'>Selected item doesn't have buffer stock set up.</div>";
            }
            $stmt->close();
        }
    }

    // Fetch inventory items and their buffer quantities
    $sql = "SELECT i.id, i.item_name, i.unit, COALESCE(kb.quantity, 0) as buffer_quantity, 
            COALESCE(kb.remaining_quantity, 0) as remaining_quantity
            FROM inventory i
            LEFT JOIN kitchen_buffer kb ON i.id = kb.item_id";
    $result = $conn->query($sql);
    $items = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $items[] = [
                'id' => $row['id'],
                'item_name' => htmlspecialchars($row['item_name'], ENT_QUOTES, 'UTF-8'),
                'unit' => htmlspecialchars($row['unit'], ENT_QUOTES, 'UTF-8'),
                'buffer_quantity' => $row['buffer_quantity'],
                'remaining_quantity' => $row['remaining_quantity']
            ];
        }
    }
    ?>
    <script>
        window.inventoryItems = <?= json_encode($items); ?>;
    </script>

    <!-- Buffer Stock Update Form -->
    <div class="bg-white rounded-2xl shadow-lg p-8 mb-12 fade-in" x-data="{
        search: '',
        selectedItemId: null,
        selectedItemText: '',
        selectedItemBuffer: 0,
        selectedItemRemaining: 0,
        items: window.inventoryItems || [],
        showSuggestions: false,
        usage: 0,
        init() {
            $watch('selectedItemRemaining', (value) => {
                this.calculateUsage();
            });
        },
        calculateUsage() {
            const buffer = parseFloat(this.selectedItemBuffer) || 0;
            const remaining = parseFloat(this.selectedItemRemaining) || 0;
            this.usage = buffer - remaining;
            if (this.usage < 0) this.usage = 0;
        },
        selectItem(item) {
            this.search = `${item.item_name} (${item.unit})`;
            this.selectedItemId = item.id;
            this.selectedItemText = `${item.item_name} (${item.unit})`;
            this.selectedItemBuffer = item.buffer_quantity;
            this.selectedItemRemaining = item.remaining_quantity;
            this.showSuggestions = false;
            this.calculateUsage();
        },
        clearSelection() {
            this.search = '';
            this.selectedItemId = null;
            this.selectedItemText = '';
            this.selectedItemBuffer = 0;
            this.selectedItemRemaining = 0;
            this.showSuggestions = false;
        }
    }">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Update Buffer Stock</h2>
        <?php if (empty($items)) {
            echo "<div class='bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded-lg fade-in'>No items found in the inventory table.</div>";
        } ?>
        <form method="POST" class="space-y-6">
            <div>
                <label for="item_search" class="block text-sm font-medium text-gray-700">Search Item</label>
                <div class="relative">
                    <input
                        type="text"
                        id="item_search"
                        x-model="search"
                        @input="showSuggestions = search.length > 0"
                        @focus="showSuggestions = search.length > 0"
                        @blur="setTimeout(() => showSuggestions = false, 200)"
                        placeholder="Type to search items..."
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3 pr-10"
                        autocomplete="off"
                    >
                    <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <div
                        x-show="showSuggestions"
                        class="absolute mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg suggestion-list"
                        x-transition
                    >
                        <template x-for="item in items.filter(i => i.item_name.toLowerCase().includes(search.toLowerCase()))" :key="item.id">
                            <div
                                class="px-4 py-2 text-sm text-gray-900 cursor-pointer suggestion-item"
                                @click="selectItem(item)"
                                x-text="`${item.item_name} (${item.unit}) - Buffer: ${item.buffer_quantity}`"
                            ></div>
                        </template>
                        <div
                            x-show="search.length > 0 
                                && items.filter(i => i.item_name.toLowerCase().includes(search.toLowerCase())).length === 0 
                                && !selectedItemId"
                            class="px-4 py-2 text-sm text-gray-500"
                        >
                            No items found
                        </div>
                    </div>
                </div>
                <input type="hidden" name="item_id" x-model="selectedItemId" required>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Buffer Quantity (from database)</label>
                <div class="mt-1 block w-full bg-gray-100 rounded-md px-3 py-3 text-sm text-gray-900">
                    <span x-text="selectedItemBuffer"></span>
                </div>
            </div>
            
            <div>
                <label for="remaining_quantity" class="block text-sm font-medium text-gray-700">Remaining Stock Quantity</label>
                <input
                    type="number"
                    name="remaining_quantity"
                    id="remaining_quantity"
                    x-model="selectedItemRemaining"
                    :max="selectedItemBuffer"
                    min="0"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3"
                    required
                >
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Usage (Auto-calculated)</label>
                <div class="mt-1 block w-full bg-gray-100 rounded-md px-3 py-3 text-sm text-gray-900">
                    <span x-text="usage"></span>
                </div>
            </div>
            
            <button
                type="submit"
                name="update_buffer"
                class="w-full bg-indigo-600 text-white py-3 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200 font-medium"
                :disabled="!selectedItemId"
            >
                Update Buffer Stock
            </button>
        </form>
    </div>

    <!-- Buffer Stock Display -->
    <div class="bg-white rounded-2xl shadow-lg p-8 mb-12 fade-in">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Current Buffer Stock</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg">
                <thead class="bg-indigo-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Item Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Unit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Buffer Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Remaining Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Usage</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Last Updated</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <?php
                $sql = "SELECT kb.buffer_id, kb.quantity, kb.remaining_quantity, kb.last_updated, i.item_name, i.unit 
                        FROM kitchen_buffer kb 
                        JOIN inventory i ON kb.item_id = i.id
                        ORDER BY i.item_name";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $usage = $row['quantity'] - $row['remaining_quantity'];
                        echo "<tr class='hover:bg-gray-50 transition duration-150'>
                                <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . htmlspecialchars($row['item_name'], ENT_QUOTES, 'UTF-8') . "</td>
                                <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . htmlspecialchars($row['unit'], ENT_QUOTES, 'UTF-8') . "</td>
                                <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>{$row['quantity']}</td>
                                <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>{$row['remaining_quantity']}</td>
                                <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>$usage</td>
                                <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>{$row['last_updated']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='px-6 py-4 text-center text-sm text-gray-500'>No buffer stock entries found</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Buffer Stock History Display -->
    <div class="bg-white rounded-2xl shadow-lg p-8 fade-in">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Buffer Stock Update History</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg">
                <thead class="bg-indigo-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Item Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Unit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Quantity Updated</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Update Timestamp</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <?php
                $sql = "SELECT h.history_id, h.item_id, h.quantity_updated, h.update_timestamp, i.item_name, i.unit 
                        FROM kitchen_buffer_history h 
                        JOIN inventory i ON h.item_id = i.id
                        ORDER BY h.update_timestamp DESC";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr class='hover:bg-gray-50 transition duration-150'>
                                <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . htmlspecialchars($row['item_name'], ENT_QUOTES, 'UTF-8') . "</td>
                                <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . htmlspecialchars($row['unit'], ENT_QUOTES, 'UTF-8') . "</td>
                                <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>{$row['quantity_updated']}</td>
                                <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>{$row['update_timestamp']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='px-6 py-4 text-center text-sm text-gray-500'>No buffer stock update history found</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php $conn->close(); ?>
</div>
</body>
</html>