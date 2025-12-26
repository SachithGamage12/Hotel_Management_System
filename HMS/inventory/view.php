<?php
include 'db.php';

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<html>
<head>
<title>Inventory Report</title>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        background: #f5f5f5;
        padding: 20px;
    }

    .container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 30px;
        width: 100%;
        margin: 0 auto;
    }

    h1 {
        text-align: center;
        color: #333;
        margin-bottom: 30px;
        font-size: 24px;
    }

    .form-section {
        margin-bottom: 25px;
        padding: 15px;
        background: #fafafa;
        border-radius: 5px;
        border-left: 4px solid #007bff;
        display: flex;
        gap: 15px;
        align-items: center;
        flex-wrap: wrap;
    }

    label {
        font-weight: 600;
        color: #333;
    }

    select, input[type="date"] {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        background: white;
    }

    select:focus, input:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0,123,255,0.3);
    }

    .table-wrapper {
        width: 100%;
        margin-top: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
        border: 1px solid #ddd;
        table-layout: auto;
    }

    th {
        background: #007bff;
        color: white;
        padding: 10px 6px;
        text-align: center;
        font-weight: 600;
        border: 1px solid #0056b3;
        white-space: nowrap;
        font-size: 11px;
    }

    td {
        padding: 8px 6px;
        text-align: center;
        border: 1px solid #ddd;
        vertical-align: middle;
        word-wrap: break-word;
        font-size: 11px;
    }

    tr:nth-child(even) {
        background: #f9f9f9;
    }

    tr:hover {
        background: #f0f8ff;
    }

    .item-name {
        text-align: left !important;
        font-weight: 600;
        color: #333;
        max-width: 150px;
        word-wrap: break-word;
    }

    .print-btn {
        background: #17a2b8;
        color: white;
        padding: 12px 30px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        display: block;
        margin: 20px auto;
    }

    .print-btn:hover {
        background: #138496;
    }

    .success-message {
        background: #d4edda;
        color: #155724;
        padding: 15px;
        border-radius: 5px;
        border: 1px solid #c3e6cb;
        margin-bottom: 20px;
        text-align: center;
        font-weight: 600;
    }

    .error-message {
        background: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 5px;
        border: 1px solid #f5c6cb;
        margin-bottom: 20px;
        text-align: center;
        font-weight: 600;
    }

    .no-items {
        text-align: center;
        padding: 40px;
        color: #666;
        font-size: 16px;
        background: #f8f9fa;
        border-radius: 5px;
        border: 1px solid #dee2e6;
    }

    /* Print Styles */
    @media print {
        @page {
            size: landscape;
            margin: 10mm;
        }
        
        body {
            padding: 0;
            background: white;
        }
        
        .container {
            box-shadow: none;
            padding: 0;
            border-radius: 0;
        }
        
        .form-section, .print-btn {
            display: none !important;
        }
        
        h1 {
            font-size: 18pt;
            margin-bottom: 15px;
            page-break-after: avoid;
        }
        
        .table-wrapper {
            margin-top: 0;
            page-break-inside: auto;
        }
        
        table {
            width: 100%;
            font-size: 8pt;
            border: 1px solid #000;
            page-break-inside: auto;
        }
        
        th {
            background: #007bff !important;
            color: white !important;
            padding: 4px 3px;
            font-size: 8pt;
            border: 1px solid #000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        td {
            padding: 3px;
            font-size: 8pt;
            border: 1px solid #000;
            page-break-inside: avoid;
        }
        
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        
        thead {
            display: table-header-group;
        }
        
        tfoot {
            display: table-footer-group;
        }
        
        /* Ensure backgrounds print */
        tr:nth-child(even) {
            background: #f0f0f0 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }

    /* Responsive adjustments for better fit */
    @media (max-width: 1400px) {
        table {
            font-size: 11px;
        }
        th, td {
            padding: 6px 4px;
            font-size: 10px;
        }
    }

    @media (max-width: 1200px) {
        table {
            font-size: 10px;
        }
        th, td {
            padding: 5px 3px;
            font-size: 9px;
        }
    }

    @media (max-width: 768px) {
        body {
            padding: 10px;
        }
        .container {
            padding: 15px;
        }
        h1 {
            font-size: 20px;
        }
        table {
            font-size: 9px;
        }
        th, td {
            padding: 4px 2px;
            font-size: 8px;
        }
        .form-section {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>
<script>
function printReport() {
    window.print();
}

function validateForm() {
    const dateInput = document.querySelector('select[name="present_date"]');
    if (dateInput.value && !/^\d{4}-\d{2}-\d{2}$/.test(dateInput.value)) {
        alert('Please select a valid date.');
        return false;
    }
    return true;
}
</script>
</head>
<body>
<div class="container">
<h1>Inventory Report</h1>

<div class="form-section">
<form method="get" onsubmit="return validateForm()">
    <label for="location_id">Select Location:</label>
    <select name="location_id" onchange="this.form.submit()">
        <option value="">All Locations</option>
        <?php
        $sql = "SELECT * FROM inv_locations ORDER BY name";
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $selected = (isset($_GET['location_id']) && $_GET['location_id'] == $row['id']) ? 'selected' : '';
                echo "<option value='{$row['id']}' $selected>" . htmlspecialchars($row['name']) . "</option>";
            }
        } else {
            echo "<div class='error-message'>Error fetching locations: " . htmlspecialchars($conn->error) . "</div>";
        }
        ?>
    </select>

    <label for="present_date">Select Date:</label>
    <select name="present_date" onchange="this.form.submit()">
        <option value="">All Dates</option>
        <?php
        $sql_dates = "SELECT DISTINCT present_date FROM inv_history WHERE present_date IS NOT NULL ORDER BY present_date DESC";
        $result_dates = $conn->query($sql_dates);
        if ($result_dates) {
            while ($row = $result_dates->fetch_assoc()) {
                $selected = (isset($_GET['present_date']) && $_GET['present_date'] == $row['present_date']) ? 'selected' : '';
                echo "<option value='{$row['present_date']}' $selected>" . htmlspecialchars($row['present_date']) . "</option>";
            }
        } else {
            echo "<div class='error-message'>Error fetching dates: " . htmlspecialchars($conn->error) . "</div>";
        }
        ?>
    </select>
</form>
</div>

<?php
if (isset($_GET['present_date']) && $_GET['present_date'] != '') {
    $present_date = $_GET['present_date'];
    $location_id = isset($_GET['location_id']) && $_GET['location_id'] != '' ? (int)$_GET['location_id'] : null;

    $sql = "
        SELECT 
            ih.*, 
            i.name AS item_name, 
            i.remarks, 
            l1.name AS location_name,
            l2.name AS transfer_location_name,
            l3.name AS return_location_name
        FROM inv_history ih
        JOIN inv_items i ON ih.item_id = i.id
        JOIN inv_locations l1 ON ih.location_id = l1.id
        LEFT JOIN inv_locations l2 ON ih.transfer_location_id = l2.id
        LEFT JOIN inv_locations l3 ON ih.return_location_id = l3.id
        WHERE ih.present_date = ?
    ";
    if ($location_id !== null) {
        $sql .= " AND ih.location_id = ?";
    }
    $sql .= " ORDER BY i.name, ih.created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "<div class='error-message'>Error preparing statement: " . htmlspecialchars($conn->error) . "</div>";
    } else {
        if ($location_id !== null) {
            $stmt->bind_param("si", $present_date, $location_id);
        } else {
            $stmt->bind_param("s", $present_date);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<div class='table-wrapper'>
            <table>
            <thead>
            <tr>
                <th>Item Name</th>
                <th>Location</th>
                <th>Remarks</th>
                <th>Present Date</th>
                <th>Last Inventory Date</th>
                <th>Last Inventory Qty</th>
                <th>New Issue Qty</th>
                <th>Transfer Date</th>
                <th>Transfer Location</th>
                <th>Transfer Qty</th>
                <th>Return Date</th>
                <th>Return Location</th>
                <th>Return Qty</th>
                <th>Damage Qty</th>
                <th>Total Qty</th>
                <th>Present Qty</th>
                <th>Created At</th>
            </tr>
            </thead>
            <tbody>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td class='item-name'>" . htmlspecialchars($row['item_name']) . "</td>
                    <td>" . htmlspecialchars($row['location_name']) . "</td>
                    <td>" . htmlspecialchars($row['remarks'] ?: '-') . "</td>
                    <td>" . htmlspecialchars($row['present_date'] ?: '-') . "</td>
                    <td>" . htmlspecialchars($row['last_inventory_date'] ?: '-') . "</td>
                    <td>" . (int)$row['last_inventory_qty'] . "</td>
                    <td>" . (int)$row['new_issue_qty'] . "</td>
                    <td>" . htmlspecialchars($row['transfer_date'] ?: '-') . "</td>
                    <td>" . htmlspecialchars($row['transfer_location_name'] ?: '-') . "</td>
                    <td>" . (int)$row['transfer_qty'] . "</td>
                    <td>" . htmlspecialchars($row['return_date'] ?: '-') . "</td>
                    <td>" . htmlspecialchars($row['return_location_name'] ?: '-') . "</td>
                    <td>" . (int)$row['return_qty'] . "</td>
                    <td>" . (int)$row['damage_qty'] . "</td>
                    <td>" . (int)$row['total_qty'] . "</td>
                    <td>" . (int)$row['present_qty'] . "</td>
                    <td>" . htmlspecialchars($row['created_at']) . "</td>
                </tr>";
            }
            echo "</tbody>
            </table>
            </div>
            <button class='print-btn' onclick='printReport()'>Print Report</button>";
        } else {
            echo "<div class='no-items'>No inventory records found for the selected date" . ($location_id !== null ? " and location" : "") . ".</div>";
        }
        $stmt->close();
    }
}
$conn->close();
?>
</div>
</body>
</html>