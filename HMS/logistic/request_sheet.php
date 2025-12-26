<?php
// Start session (optional, included for consistency)
session_start();

// Set timezone to Asia/Colombo (Sri Jayawardenepura)
date_default_timezone_set('Asia/Colombo');

// Database Connection
$conn = new mysqli('localhost', 'hotelgrandguardi_root', 'Sun123flower@', 'hotelgrandguardi_wedding_bliss');
if ($conn->connect_error) {
    die('<div class="alert alert-danger">Connection failed: ' . htmlspecialchars($conn->connect_error) . '</div>');
}
$conn->set_charset("utf8mb4");

// Get request_id from URL
$request_id = isset($_GET['request_id']) ? (int)$_GET['request_id'] : 0;
$error = '';
$request_data = null;
$items = [];
$manager_name = 'Unknown';

// Fetch request details
if ($request_id > 0) {
    $stmt = $conn->prepare("SELECT request_date, requester_name, section, reason, last_request_date, manager_id FROM item_requests WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $request_data = $result->fetch_assoc();
            // Fetch manager name
            $stmt_manager = $conn->prepare("SELECT username FROM managers WHERE id = ?");
            if ($stmt_manager) {
                $stmt_manager->bind_param("i", $request_data['manager_id']);
                $stmt_manager->execute();
                $manager_result = $stmt_manager->get_result();
                if ($manager_result->num_rows === 1) {
                    $manager_name = $manager_result->fetch_assoc()['username'];
                }
                $stmt_manager->close();
                $manager_result->free();
            } else {
                $error = "Error fetching manager: " . htmlspecialchars($conn->error);
                error_log("Prepare failed for manager username query: " . $conn->error);
            }
        } else {
            $error = "Request ID not found.";
        }
        $stmt->close();
        $result->free();
    } else {
        $error = "Error fetching request: " . htmlspecialchars($conn->error);
        error_log("Prepare failed for item_requests query: " . $conn->error);
    }

    // Fetch requested items
    if (!$error) {
        $stmt = $conn->prepare("SELECT i.name, ri.quantity, ri.unit_type FROM request_items ri JOIN items i ON ri.item_id = i.id WHERE ri.request_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $request_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
            $stmt->close();
            $result->free();
        } else {
            $error = "Error fetching items: " . htmlspecialchars($conn->error);
            error_log("Prepare failed for request_items query: " . $conn->error);
        }
    }
} else {
    $error = "Invalid or missing Request ID.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Request Sheet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .request-sheet {
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 10px;
            padding: 20px;
            width: 500px; /* Fixed width for square-like appearance */
            max-width: 90%;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .request-sheet h4 {
            border-bottom: 1px solid #ced4da;
            padding-bottom: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .request-sheet p {
            margin: 10px 0;
            font-size: 1rem;
        }
        .request-sheet .table {
            margin-top: 20px;
        }
        .alert {
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .print-button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            border-radius: 5px;
        }
        @media (max-width: 576px) {
            .request-sheet {
                padding: 15px;
                width: 100%;
            }
        }
        @media print {
            body {
                background-color: #fff;
                display: block;
            }
            .request-sheet {
                box-shadow: none;
                border: none;
                width: 100%;
                max-width: 500px;
                margin: 0 auto;
            }
            .print-button {
                display: none; /* Hide print button when printing */
            }
            .alert {
                display: none; /* Hide alerts when printing */
            }
        }
    </style>
</head>
<body>
    <div class="request-sheet">
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif ($request_data): ?>
            <h4>Item Request Sheet</h4>
            <p><strong>Request ID:</strong> <?php echo htmlspecialchars($request_id); ?></p>
            <p><strong>Request Date:</strong> <?php echo htmlspecialchars($request_data['request_date']); ?></p>
            <p><strong>Requester Name:</strong> <?php echo htmlspecialchars($request_data['requester_name']); ?></p>
            <p><strong>Section:</strong> <?php echo htmlspecialchars($request_data['section']); ?></p>
            <p><strong>Reason:</strong> <?php echo htmlspecialchars($request_data['reason']); ?></p>
            <p><strong>Last Request Date:</strong> <?php echo htmlspecialchars($request_data['last_request_date'] ?: 'N/A'); ?></p>
            <p><strong>Confirmed by Manager:</strong> <?php echo htmlspecialchars($manager_name); ?></p>
            <h5 class="mt-3">Items Requested</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Unit Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr><td colspan="3">No items found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($item['unit_type']); ?></td>
                            </tr>
                            <?php endforeach;?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <button onclick="window.print()" class="btn btn-primary print-button">Print Request Sheet</button>
            <?php endif; ?>
        </div>
    
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    
    <?php
    $conn->close();
    ?>