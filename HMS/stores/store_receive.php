<?php
// DB connection
$conn = new mysqli("localhost", "hotelgrandguardi_root", "Sun123flower@", "hotelgrandguardi_wedding_bliss");
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['refillItems'])) {
    $refillItems = json_decode($_POST['refillItems'], true);
    
    // Insert into refill_requests table
    $stmt = $conn->prepare("INSERT INTO refill_requests (buffer_id, item_name, requested_qty, unit, status, request_date) VALUES (?, ?, ?, ?, 'pending', NOW())");
    
    foreach ($refillItems as $item) {
        if ($item['refill'] > 0) {
            $stmt->bind_param("isds", $item['id'], $item['item_name'], $item['refill'], $item['unit']);
            $stmt->execute();
        }
    }
    $stmt->close();
}

// Fetch pending requests
$sql = "SELECT id, buffer_id, item_name, requested_qty, unit, status, request_date 
        FROM refill_requests 
        WHERE status = 'pending' 
        ORDER BY request_date DESC";
$result = $conn->query($sql);
$requests = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store - Refill Requests</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4 sm:p-6">
    <div class="w-full max-w-4xl bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-6">Store - Refill Requests</h1>
        
        <div class="overflow-x-auto">
            <table class="w-full border-collapse bg-white rounded-lg shadow">
                <thead>
                    <tr class="bg-gray-200 text-gray-700">
                        <th class="px-4 py-3 text-left font-semibold">Item Name</th>
                        <th class="px-4 py-3 text-center font-semibold">Requested Qty</th>
                        <th class="px-4 py-3 text-center font-semibold">Unit</th>
                        <th class="px-4 py-3 text-center font-semibold">Request Date</th>
                        <th class="px-4 py-3 text-center font-semibold">Status</th>
                        <th class="px-4 py-3 text-center font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-800"><?= htmlspecialchars($request['item_name']) ?></td>
                            <td class="px-4 py-3 text-center text-gray-600"><?= $request['requested_qty'] ?></td>
                            <td class="px-4 py-3 text-center text-gray-600"><?= htmlspecialchars($request['unit']) ?></td>
                            <td class="px-4 py-3 text-center text-gray-600"><?= $request['request_date'] ?></td>
                            <td class="px-4 py-3 text-center text-gray-600"><?= htmlspecialchars($request['status']) ?></td>
                            <td class="px-4 py-3 text-center">
                                <form method="POST" action="store_receive.php">
                                    <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                    <button type="submit" name="approve" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 transition mr-2">Approve</button>
                                    <button type="submit" name="reject" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php
        // Handle approve/reject actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'])) {
            $request_id = $_POST['request_id'];
            $status = isset($_POST['approve']) ? 'approved' : 'rejected';
            
            $stmt = $conn->prepare("UPDATE refill_requests SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $request_id);
            $stmt->execute();
            $stmt->close();
            
            // Refresh page
            header("Location: store_receive.php");
            exit;
        }
        ?>
    </div>
</body>
</html>