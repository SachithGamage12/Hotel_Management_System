<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKY Kitchen Buffer Stock Management</title>
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
        <h1 class="text-5xl font-bold text-gray-900">Kitchen Buffer Stock</h1>
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

   
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_buffer'])) {
        $item_id = $_POST['item_id'];
        $quantity = $_POST['quantity'];

        if (!is_numeric($item_id) || !is_numeric($quantity) || $quantity < 0) {
            echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg fade-in'>Invalid input provided.</div>";
        } else {
            $check_sql = "SELECT * FROM skykitchen_buffer WHERE item_id = ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param("i", $item_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $update_sql = "UPDATE skykitchen_buffer SET quantity = ?, last_updated = CURRENT_TIMESTAMP WHERE item_id = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("ii", $quantity, $item_id);
            } else {
                $insert_sql = "INSERT INTO skykitchen_buffer (item_id, quantity) VALUES (?, ?)";
                $stmt = $conn->prepare($insert_sql);
                $stmt->bind_param("ii", $item_id, $quantity);
            }

            if ($stmt->execute()) {
                echo "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg fade-in'>Buffer stock updated successfully!</div>";
            } else {
                echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg fade-in'>Error updating buffer stock: " . $conn->error . "</div>";
            }
            $stmt->close();
        }
    }

    // Load items into JS variable
    $sql = "SELECT id, item_name, unit FROM inventory";
    $result = $conn->query($sql);
    $items = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $items[] = [
                'id' => $row['id'],
                'item_name' => htmlspecialchars($row['item_name'], ENT_QUOTES, 'UTF-8'),
                'unit' => htmlspecialchars($row['unit'], ENT_QUOTES, 'UTF-8')
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
        items: window.inventoryItems || [],
        showSuggestions: false,
        init() {
            $watch('search', () => {
                this.showSuggestions = this.search.length > 0;
                if (!this.search) {
                    this.selectedItemId = null;
                    this.selectedItemText = '';
                }
            });
        },
        selectItem(item) {
            this.search = `${item.item_name} (${item.unit})`;
            this.selectedItemId = item.id;
            this.selectedItemText = `${item.item_name} (${item.unit})`;
            this.showSuggestions = false;
        },
        clearSelection() {
            this.search = '';
            this.selectedItemId = null;
            this.selectedItemText = '';
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
                                x-text="`${item.item_name} (${item.unit})`"
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
                <label for="quantity" class="block text-sm font-medium text-gray-700">Buffer Stock Quantity</label>
                <input
                    type="number"
                    name="quantity"
                    id="quantity"
                    min="0"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3"
                    required
                >
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
    <div class="bg-white rounded-2xl shadow-lg p-8 fade-in">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Current Buffer Stock</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg">
                <thead class="bg-indigo-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Item Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Unit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Buffer Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Last Updated</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <?php
                $sql = "SELECT kb.buffer_id, kb.quantity, kb.last_updated, i.item_name, i.unit 
                        FROM skykitchen_buffer kb 
                        JOIN inventory i ON kb.item_id = i.id";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr class='hover:bg-gray-50 transition duration-150'>
                                <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . htmlspecialchars($row['item_name'], ENT_QUOTES, 'UTF-8') . "</td>
                                <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . htmlspecialchars($row['unit'], ENT_QUOTES, 'UTF-8') . "</td>
                                <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>{$row['quantity']}</td>
                                <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>{$row['last_updated']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='px-6 py-4 text-center text-sm text-gray-500'>No buffer stock entries found</td></tr>";
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
