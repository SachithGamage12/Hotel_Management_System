<?php
// purchased_history.php
// Database connection for HTML interface
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

// Fetch items for select
$stmt = $pdo->prepare("SELECT id, item_name FROM inventory ORDER BY item_name ASC");
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchased Items Report Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .report-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .form-group {
            margin-bottom: 20px;
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
        <h2 class="text-center mb-4">Purchased Items Report</h2>
        
        <form action="generate_report.php" method="post" target="_blank">
            <div class="form-group">
                <label for="reportType" class="form-label">Report Type</label>
                <select class="form-select" id="reportType" name="reportType" required>
                    <option value="all">All Purchases</option>
                    <option value="yearly">Yearly Report</option>
                    <option value="monthly">Monthly Report</option>
                    <option value="daily">Daily Report</option>
                </select>
            </div>

            <div class="form-group">
                <label for="item_id" class="form-label">Select Item (Optional)</label>
                <select class="form-select" id="item_id" name="item_id">
                    <option value="">All Items</option>
                    <?php foreach ($items as $item): ?>
                        <option value="<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['item_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group" id="yearSelection" style="display: none;">
                <label for="year" class="form-label">Select Year</label>
                <select class="form-select" id="year" name="year">
                    <?php
                    $currentYear = date('Y');
                    for ($i = $currentYear; $i >= $currentYear - 5; $i--) {
                        echo "<option value=\"$i\">$i</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group" id="monthSelection" style="display: none;">
                <label for="month" class="form-label">Select Month</label>
                <select class="form-select" id="month" name="month">
                    <?php
                    for ($i = 1; $i <= 12; $i++) {
                        $monthName = date('F', mktime(0, 0, 0, $i, 1));
                        echo "<option value=\"$i\">$monthName</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group" id="dateSelection" style="display: none;">
                <label for="date" class="form-label">Select Date</label>
                <input type="date" class="form-control" id="date" name="date" value="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="text-center">
                <button type="submit" class="btn btn-primary btn-lg">Generate PDF Report</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('reportType').addEventListener('change', function() {
            const reportType = this.value;
            document.getElementById('yearSelection').style.display = 'none';
            document.getElementById('monthSelection').style.display = 'none';
            document.getElementById('dateSelection').style.display = 'none';
            
            if (reportType === 'yearly') {
                document.getElementById('yearSelection').style.display = 'block';
            } else if (reportType === 'monthly') {
                document.getElementById('yearSelection').style.display = 'block';
                document.getElementById('monthSelection').style.display = 'block';
            } else if (reportType === 'daily') {
                document.getElementById('dateSelection').style.display = 'block';
            }
        });
    </script>
</body>
</html>