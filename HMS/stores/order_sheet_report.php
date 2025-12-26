<?php
// order_sheet_report.php
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

// Fetch distinct order sheet numbers
$stmt = $pdo->prepare("SELECT DISTINCT order_sheet_no FROM order_sheet ORDER BY order_sheet_no");
$stmt->execute();
$order_sheets = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch distinct function dates
$stmt = $pdo->prepare("SELECT DISTINCT function_date FROM order_sheet WHERE function_date IS NOT NULL ORDER BY function_date");
$stmt->execute();
$function_dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Sheet Report Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .report-container {
            max-width: 600px;
            margin: 30px auto;
            padding: 30px;
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }
        .report-container h2 {
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
        .form-select, .btn {
            border-radius: 8px;
            padding: 10px;
            font-size: 1rem;
        }
        .form-select {
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
            padding: 12px 24px;
            font-weight: 500;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        .btn-primary:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }
        .form-group {
            margin-bottom: 20px;
        }
        @media (max-width: 576px) {
            .report-container {
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
    <div class="container report-container">
            <div style="position: absolute; top: 20px; left: 20px;">
    <button onclick="window.location.href='../stores.php'" 
        style="background-color: #f09424ff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
        Back
    </button>
</div>
        <h2>Order Sheet Report Generator</h2>
        
        <form action="generate_order_report.php" method="post" target="_blank">
            <div class="form-group">
                <label for="statusFilter">Filter by Status</label>
                <select class="form-select" id="statusFilter" name="statusFilter" required>
                    <option value="all">All Orders</option>
                    <option value="pending">Pending Orders</option>
                    <option value="issued">Issued Orders</option>
                </select>
            </div>

            <div class="form-group">
                <label for="orderSheetNo">Order Sheet Number (Optional)</label>
                <select class="form-select" id="orderSheetNo" name="orderSheetNo">
                    <option value="">All Order Sheets</option>
                    <?php foreach ($order_sheets as $sheet_no): ?>
                        <option value="<?php echo $sheet_no; ?>"><?php echo htmlspecialchars($sheet_no); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="functionDate">Function Date (Optional)</label>
                <select class="form-select" id="functionDate" name="functionDate">
                    <option value="">All Function Dates</option>
                    <?php foreach ($function_dates as $date): ?>
                        <option value="<?php echo $date; ?>"><?php echo date('F j, Y', strtotime($date)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="text-center">
                <button type="submit" class="btn btn-primary">Generate PDF Report</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>