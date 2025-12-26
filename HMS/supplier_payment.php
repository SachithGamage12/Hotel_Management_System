<?php
// Database configuration
$host = 'localhost';
$dbname = 'hotelgrandguardi_wedding_bliss';
$username = 'hotelgrandguardi_root';
$password = 'Sun123flower@';

$pdo = null;
$booking_data = null;
$success_message = '';
$error_message = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle form submission for saving supplier payment
if ($_POST && isset($_POST['save_payment'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO supplier_payments (
            request_date, function_date, dj_deco_band_dance_cake_car_other, 
            function_type, day_or_night, customer_name, hall_or_location, 
            supplier_name, pax, front_or_back_officer_name, officer_signature,
            start_time, end_time, hall_supervisor_name, hall_supervisor_signature,
            hall_supervisor_sign_time, banquet_manager_signature, banquet_manager_sign_time,
            sales_or_senior_manager_name, sales_signature, sales_sign_time,
            amount, sales_seal, booking_code
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $_POST['request_date'],
            $_POST['function_date'],
            $_POST['dj_deco_other'],
            $_POST['function_type'],
            $_POST['day_or_night'],
            $_POST['customer_name'],
            $_POST['hall_location'],
            $_POST['supplier_name'],
            $_POST['pax'],
            $_POST['officer_name'],
            $_POST['officer_signature'],
            $_POST['start_time'],
            $_POST['end_time'],
            $_POST['hall_supervisor_name'],
            $_POST['hall_supervisor_signature'],
            $_POST['hall_supervisor_sign_time'],
            $_POST['banquet_manager_signature'],
            $_POST['banquet_manager_sign_time'],
            $_POST['sales_manager_name'],
            $_POST['sales_signature'],
            $_POST['sales_sign_time'],
            $_POST['amount'],
            $_POST['sales_seal'],
            $_POST['booking_code']
        ]);
        
        $success_message = "Supplier payment record saved successfully!";
    } catch(PDOException $e) {
        $error_message = "Error saving record: " . $e->getMessage();
    }
}

// Handle booking code lookup
if ($_POST && isset($_POST['booking_code']) && !empty($_POST['booking_code']) && !isset($_POST['save_payment'])) {
    $booking_code = $_POST['booking_code'];
    
    // Query to fetch the latest booking details from either wedding_bookings or wedding_bookings_history
    $stmt = $pdo->prepare("
        SELECT 
            booking_date, 
            full_name, 
            no_of_pax, 
            day_or_night, 
            v.name AS venue_name, 
            ft.name AS function_type_name,
            source_table,
            record_timestamp
        FROM (
            SELECT 
                booking_date, 
                full_name, 
                no_of_pax, 
                day_or_night, 
                venue_id, 
                function_type_id, 
                created_at AS record_timestamp,
                'wedding_bookings' AS source_table
            FROM wedding_bookings 
            WHERE booking_reference = ?
            UNION
            SELECT 
                booking_date, 
                full_name, 
                no_of_pax, 
                day_or_night, 
                venue_id, 
                function_type_id, 
                updated_at AS record_timestamp,
                'wedding_bookings_history' AS source_table
            FROM wedding_bookings_history 
            WHERE booking_reference = ?
        ) AS combined
        LEFT JOIN venues v ON combined.venue_id = v.id
        LEFT JOIN function_types ft ON combined.function_type_id = ft.id
        ORDER BY record_timestamp DESC
        LIMIT 1
    ");
    
    $stmt->execute([$booking_code, $booking_code]);
    $booking_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking_data) {
        $error_message = "No booking found with code: " . $booking_code;
    }
}

// Create supplier_payments table if it doesn't exist
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS supplier_payments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        request_date DATE,
        function_date DATE,
        dj_deco_band_dance_cake_car_other TEXT,
        function_type VARCHAR(255),
        day_or_night VARCHAR(10),
        customer_name VARCHAR(255),
        hall_or_location VARCHAR(255),
        supplier_name VARCHAR(255),
        pax INT,
        front_or_back_officer_name VARCHAR(255),
        officer_signature TEXT,
        start_time TIME,
        end_time TIME,
        hall_supervisor_name VARCHAR(255),
        hall_supervisor_signature TEXT,
        hall_supervisor_sign_time TIME,
        banquet_manager_signature TEXT,
        banquet_manager_sign_time TIME,
        sales_or_senior_manager_name VARCHAR(255),
        sales_signature TEXT,
        sales_sign_time TIME,
        amount DECIMAL(10,2),
        sales_seal TEXT,
        booking_code VARCHAR(10),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch(PDOException $e) {
    // Table might already exist, continue
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Payments Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-header {
            text-align: center;
            background-color: #d4d4d4;
            padding: 15px;
            margin: -20px -20px 20px -20px;
            border-radius: 8px 8px 0 0;
            font-weight: bold;
            font-size: 18px;
        }
        
        .lookup-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        
        .form-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
        }
        
        .form-table td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: middle;
        }
        
        .label-cell {
            background-color: #e8e8e8;
            font-weight: bold;
            width: 200px;
        }
        
        .input-cell {
            background-color: white;
        }
        
        .input-cell input, .input-cell textarea, .input-cell select {
            width: 100%;
            border: none;
            padding: 5px;
            font-size: 14px;
            background: transparent;
        }
        
        .input-cell input[readonly], .input-cell select[readonly] {
            background-color: #f0f0f0;
            cursor: not-allowed;
        }
        
        .input-cell textarea {
            resize: vertical;
            min-height: 40px;
        }
        
        .row-span-2 {
            border-bottom: none;
        }
        
        .signature-cell {
            height: 60px;
            vertical-align: top;
        }
        
        .button-group {
            text-align: center;
            margin-top: 20px;
            padding: 20px 0;
        }
        
        .btn {
            padding: 10px 30px;
            margin: 0 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0056b3;
        }
        
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #1e7e34;
        }
        
        .alert {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        
        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        @media print {
            .button-group, .lookup-section {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div style="margin-bottom: 1rem;">
  <a
    href="Backoffice.php"
    role="button"
    style="
      display: inline-block;
      background-color: #f59e0b;
      color: #ffffff;
      padding: 0.5rem 0.9rem;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
      box-shadow: 0 2px 6px rgba(0,0,0,0.12);
    "
    onmouseover="this.style.backgroundColor='#d97706';"
    onmouseout="this.style.backgroundColor='#f59e0b';"
    onbeforeprint="this.style.display='none';"
    onafterprint="this.style.display='inline-block';"
  >
    Back
  </a>
</div>
    
    <div class="container">
        <div class="form-header">
            Supplier Payments
        </div>
        
        <!-- Booking Code Lookup Section -->
        <div class="lookup-section">
            <h3>Enter Booking Code to Auto-Fill Details</h3>
            <form method="POST">
                <input type="text" name="booking_code" placeholder="Enter booking code..." 
                       value="<?php echo isset($_POST['booking_code']) ? htmlspecialchars($_POST['booking_code']) : ''; ?>" 
                       style="padding: 10px; width: 200px; margin-right: 10px;">
                <button type="submit" class="btn btn-primary">Lookup</button>
            </form>
        </div>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <!-- Main Form -->
        <form id="paymentForm" method="POST">
            <table class="form-table">
                <tr>
                    <td class="label-cell">Request Date :</td>
                    <td class="input-cell">
                        <input type="date" name="request_date" value="<?php echo date('Y-m-d'); ?>">
                    </td>
                    <td class="label-cell">Function date :</td>
                    <td class="input-cell">
                        <input type="date" name="function_date" 
                               value="<?php echo $booking_data ? htmlspecialchars($booking_data['booking_date']) : ''; ?>" 
                               <?php echo $booking_data ? 'readonly' : ''; ?>>
                    </td>
                </tr>
                
                <tr>
                    <td class="label-cell">DJ /Deco / Band / Dance/Cake / Car or Other:</td>
                    <td class="input-cell" colspan="3">
                        <textarea name="dj_deco_other" placeholder="Enter details..."></textarea>
                    </td>
                </tr>
                
                <tr>
                    <td class="label-cell">Function Type:</td>
                    <td class="input-cell">
                        <input type="text" name="function_type" 
                               value="<?php echo $booking_data ? htmlspecialchars($booking_data['function_type_name'] ?? 'Wedding') : ''; ?>" 
                               <?php echo $booking_data ? 'readonly' : ''; ?>>
                    </td>
                    <td class="label-cell">Day or Night:</td>
                    <td class="input-cell">
                        <select name="day_or_night" <?php echo $booking_data ? 'readonly' : ''; ?>>
                            <option value="">Select...</option>
                            <option value="Day" <?php echo ($booking_data && strtolower($booking_data['day_or_night']) == 'day') ? 'selected' : ''; ?>>Day</option>
                            <option value="Night" <?php echo ($booking_data && strtolower($booking_data['day_or_night']) == 'night') ? 'selected' : ''; ?>>Night</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <td class="label-cell">Customer name :</td>
                    <td class="input-cell">
                        <input type="text" name="customer_name" 
                               value="<?php echo $booking_data ? htmlspecialchars($booking_data['full_name']) : ''; ?>" 
                               <?php echo $booking_data ? 'readonly' : ''; ?>>
                    </td>
                    <td class="label-cell">Hall Or Location :</td>
                    <td class="input-cell">
                        <input type="text" name="hall_location" 
                               value="<?php echo $booking_data ? htmlspecialchars($booking_data['venue_name'] ?? 'Red Hall') : ''; ?>" 
                               <?php echo $booking_data ? 'readonly' : ''; ?>>
                    </td>
                </tr>
                
                <tr>
                    <td class="label-cell">Supplier Name :</td>
                    <td class="input-cell">
                        <input type="text" name="supplier_name">
                    </td>
                    <td class="label-cell">Pax :</td>
                    <td class="input-cell">
                        <input type="number" name="pax" 
                               value="<?php echo $booking_data ? htmlspecialchars($booking_data['no_of_pax']) : ''; ?>" 
                               <?php echo $booking_data ? 'readonly' : ''; ?>>
                    </td>
                </tr>
                
                <tr>
                    <td class="label-cell">Front or Back Officer Name:</td>
                    <td class="input-cell">
                        <input type="text" name="officer_name">
                    </td>
                    <td class="label-cell">Officer Signature :</td>
                    <td class="input-cell signature-cell">
                        <textarea name="officer_signature" placeholder="Signature..."></textarea>
                    </td>
                </tr>
                
                <tr>
                    <td class="label-cell">Start Time:</td>
                    <td class="input-cell">
                        <input type="time" name="start_time">
                    </td>
                    <td class="label-cell">End Time:</td>
                    <td class="input-cell">
                        <input type="time" name="end_time">
                    </td>
                </tr>
                
                <tr>
                    <td class="label-cell">Hall Supervisor Name :</td>
                    <td class="input-cell" colspan="3">
                        <input type="text" name="hall_supervisor_name">
                    </td>
                </tr>
                
                <tr>
                    <td class="label-cell">Hall Supervisor Signature :</td>
                    <td class="input-cell signature-cell">
                        <textarea name="hall_supervisor_signature" placeholder="Signature..."></textarea>
                    </td>
                    <td class="label-cell">Sign Time:</td>
                    <td class="input-cell">
                        <input type="time" name="hall_supervisor_sign_time">
                    </td>
                </tr>
                
                <tr>
                    <td class="label-cell">Banquet Manager Signature :</td>
                    <td class="input-cell signature-cell">
                        <textarea name="banquet_manager_signature" placeholder="Signature..."></textarea>
                    </td>
                    <td class="label-cell">Sign Time:</td>
                    <td class="input-cell">
                        <input type="time" name="banquet_manager_sign_time">
                    </td>
                </tr>
                
                <tr>
                    <td class="label-cell">Sales Or Senior Manager Name :</td>
                    <td class="input-cell" colspan="3">
                        <input type="text" name="sales_manager_name">
                    </td>
                </tr>
                
                <tr>
                    <td class="label-cell">Signature :</td>
                    <td class="input-cell signature-cell">
                        <textarea name="sales_signature" placeholder="Signature..."></textarea>
                    </td>
                    <td class="label-cell">Sign Time:</td>
                    <td class="input-cell">
                        <input type="time" name="sales_sign_time">
                    </td>
                </tr>
                
                <tr>
                    <td class="label-cell">Amount :</td>
                    <td class="input-cell" colspan="3">
                        <input type="number" step="0.01" name="amount" placeholder="0.00">
                    </td>
                </tr>
                
                <tr>
                    <td class="label-cell">Sales Seal :</td>
                    <td class="input-cell" colspan="3">
                        <textarea name="sales_seal" placeholder="Sales seal details..."></textarea>
                    </td>
                </tr>
            </table>
            
            <!-- Hidden field for booking code -->
            <input type="hidden" name="booking_code" value="<?php echo isset($_POST['booking_code']) ? htmlspecialchars($_POST['booking_code']) : ''; ?>">
            <input type="hidden" name="save_payment" value="1">
            
            <div class="button-group">
                <button type="button" onclick="triggerPrintAndSave()" class="btn btn-success">Save Payment Record</button>
            </div>
        </form>
    </div>
    
    <script>
        // Auto-fill current date and time for certain fields
        document.addEventListener('DOMContentLoaded', function() {
            const now = new Date();
            const currentTime = now.toTimeString().slice(0, 5);
            
            // Set current time as default for sign times if empty
            const timeInputs = document.querySelectorAll('input[type="time"]');
            timeInputs.forEach(function(input) {
                if (input.name.includes('sign_time') && !input.value) {
                    input.value = currentTime;
                }
            });
        });
        
        // Function to trigger print and then save
        function triggerPrintAndSave() {
            window.print();
            // Submit the form after print dialog is shown
            document.getElementById('paymentForm').submit();
        }
        
        // Clear form function
        function clearForm() {
            if (confirm('Are you sure you want to clear all form data?')) {
                document.querySelector('form').reset();
            }
        }
    </script>
</body>
</html>