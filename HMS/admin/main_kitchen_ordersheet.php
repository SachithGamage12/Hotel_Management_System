<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Order Sheet Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <style>
        .table-container { max-height: 500px; overflow-y: auto; }
        .error-message { display: none; color: red; }
        .loader { display: none; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Order Sheet Report</h1>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <form id="reportForm" method="GET" action="" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="report_type" class="block text-sm font-medium text-gray-700">Report Type</label>
                        <select name="report_type" id="report_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">Select...</option>
                            <option value="daily">Daily</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div>
                        <label for="date_input" class="block text-sm font-medium text-gray-700">Date Input</label>
                        <input type="text" name="date_input" id="date_input" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Select date..." required>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">Generate Report</button>
                    </div>
                </div>
                <p id="errorMessage" class="error-message text-sm">Please enter a valid date format.</p>
            </form>
            
            <div id="loader" class="loader flex justify-center items-center my-4">
                <svg class="animate-spin h-8 w-8 text-indigo-600" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            
            <div id="reportResult" class="mt-6">
                <?php
                // Database connection details
                $host = 'localhost';
                $username = 'hotelgrandguardi_root';
                $password = 'Sun123flower@';
                $dbname = 'hotelgrandguardi_wedding_bliss';
                
                try {
                    $conn = new mysqli($host, $username, $password, $dbname);
                    if ($conn->connect_error) {
                        throw new Exception("Connection failed: " . $conn->connect_error);
                    }
                    
                    if (isset($_GET['report_type']) && isset($_GET['date_input'])) {
                        $report_type = $_GET['report_type'];
                        $date_input = $_GET['date_input'];
                        
                        // Validate date input
                        $date_valid = false;
                        if ($report_type === 'daily' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_input)) {
                            $date_valid = true;
                        } elseif ($report_type === 'monthly' && preg_match('/^\d{4}-\d{2}$/', $date_input)) {
                            $date_valid = true;
                        } elseif ($report_type === 'yearly' && preg_match('/^\d{4}$/', $date_input)) {
                            $date_valid = true;
                        }
                        
                        if (!$date_valid) {
                            echo "<p class='text-red-500'>Invalid date format. Use YYYY-MM-DD for daily, YYYY-MM for monthly, or YYYY for yearly.</p>";
                        } else {
                            // Base query
                            $sql = "
                                SELECT 
                                    i.item_name,
                                    i.category,
                                    i.unit,
                                    SUM(os.requested_qty) AS total_requested,
                                    SUM(os.issued_qty) AS total_issued,
                                    os.status,
                                    os.day_night,
                                    os.function_type
                                FROM order_sheet os
                                JOIN inventory i ON os.item_id = i.id
                            ";
                            
                            // Add WHERE clause based on report type
                            $date_group = "";
                            if ($report_type === 'daily') {
                                $sql .= " WHERE DATE(os.request_date) = ?";
                                $date_group = "Date: $date_input";
                            } elseif ($report_type === 'monthly') {
                                $sql .= " WHERE DATE_FORMAT(os.request_date, '%Y-%m') = ?";
                                $date_group = "Month: $date_input";
                            } elseif ($report_type === 'yearly') {
                                $sql .= " WHERE YEAR(os.request_date) = ?";
                                $date_group = "Year: $date_input";
                            }
                            
                            $sql .= " GROUP BY i.id, os.status, os.day_night, os.function_type";
                            
                            $stmt = $conn->prepare($sql);
                            if (!$stmt) {
                                throw new Exception("Query preparation failed: " . $conn->error);
                            }
                            $stmt->bind_param("s", $date_input);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0) {
                                echo "<div class='flex justify-between items-center mb-4'>";
                                echo "<h2 class='text-xl font-semibold'>Report for $report_type - $date_group</h2>";
                                echo "<button id='exportBtn' class='bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition'>Export to Excel</button>";
                                echo "</div>";
                                echo "<div class='table-container'>";
                                echo "<table id='reportTable' class='w-full text-sm text-left text-gray-500'>";
                                echo "<thead class='text-xs text-gray-700 uppercase bg-gray-50'>";
                                echo "<tr><th class='px-6 py-3'>Item Name</th><th class='px-6 py-3'>Category</th><th class='px-6 py-3'>Unit</th><th class='px-6 py-3'>Total Requested</th><th class='px-6 py-3'>Total Issued</th><th class='px-6 py-3'>Status</th><th class='px-6 py-3'>Day/Night</th><th class='px-6 py-3'>Function Type</th></tr>";
                                echo "</thead><tbody>";
                                
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr class='bg-white border-b hover:bg-gray-50'>";
                                    echo "<td class='px-6 py-4'>" . htmlspecialchars($row['item_name']) . "</td>";
                                    echo "<td class='px-6 py-4'>" . htmlspecialchars($row['category']) . "</td>";
                                    echo "<td class='px-6 py-4'>" . htmlspecialchars($row['unit']) . "</td>";
                                    echo "<td class='px-6 py-4'>" . number_format($row['total_requested'] ?? 0, 2) . "</td>";
                                    echo "<td class='px-6 py-4'>" . number_format($row['total_issued'] ?? 0, 2) . "</td>";
                                    echo "<td class='px-6 py-4'>" . htmlspecialchars($row['status']) . "</td>";
                                    echo "<td class='px-6 py-4'>" . htmlspecialchars($row['day_night'] ?? '-') . "</td>";
                                    echo "<td class='px-6 py-4'>" . htmlspecialchars($row['function_type'] ?? '-') . "</td>";
                                    echo "</tr>";
                                }
                                
                                echo "</tbody></table>";
                                echo "</div>";
                            } else {
                                echo "<p class='text-gray-500'>No data found for the selected period.</p>";
                            }
                            $stmt->close();
                        }
                    }
                    $conn->close();
                } catch (Exception $e) {
                    echo "<p class='text-red-500'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Initialize datepicker based on report type
            $('#report_type').change(function() {
                const reportType = $(this).val();
                $('#date_input').val('');
                
                if (reportType === 'daily') {
                    $('#date_input').datepicker({
                        dateFormat: 'yy-mm-dd',
                        changeMonth: true,
                        changeYear: true
                    });
                } else if (reportType === 'monthly') {
                    $('#date_input').datepicker({
                        dateFormat: 'yy-mm',
                        changeMonth: true,
                        changeYear: true,
                        showButtonPanel: true,
                        onClose: function(dateText, inst) {
                            $(this).val($.datepicker.formatDate('yy-mm', new Date(inst.selectedYear, inst.selectedMonth, 1)));
                        }
                    });
                } else if (reportType === 'yearly') {
                    $('#date_input').datepicker({
                        dateFormat: 'yy',
                        changeYear: true,
                        showButtonPanel: true,
                        onClose: function(dateText, inst) {
                            $(this).val(inst.selectedYear);
                        }
                    });
                } else {
                    $('#date_input').datepicker('destroy');
                    $('#date_input').attr('type', 'text');
                }
            });

            // Form validation
            $('#reportForm').submit(function(e) {
                const reportType = $('#report_type').val();
                const dateInput = $('#date_input').val();
                let isValid = true;

                if (reportType === 'daily' && !/^\d{4}-\d{2}-\d{2}$/.test(dateInput)) {
                    isValid = false;
                } else if (reportType === 'monthly' && !/^\d{4}-\d{2}$/.test(dateInput)) {
                    isValid = false;
                } else if (reportType === 'yearly' && !/^\d{4}$/.test(dateInput)) {
                    isValid = false;
                }

                if (!isValid) {
                    $('#errorMessage').show();
                    e.preventDefault();
                    setTimeout(() => $('#errorMessage').fadeOut(), 3000);
                } else {
                    $('#loader').show();
                    $('#reportResult').hide();
                }
            });

            // Export to Excel
            $('#exportBtn').click(function() {
                const table = document.getElementById('reportTable');
                const wb = XLSX.utils.table_to_book(table, {sheet: "Order Sheet Report"});
                XLSX.write_file(wb, `OrderSheetReport_${$('#report_type').val()}_${$('#date_input').val()}.xlsx`);
            });
        });
    </script>
</body>
</html>