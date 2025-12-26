<?php
// Database configuration
$host = 'localhost';
$dbname = 'hotelgrandguardi_wedding_bliss';
$username = 'hotelgrandguardi_root';
$password = 'Sun123flower@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle form submissions
$filters = [];
$action = $_GET['action'] ?? 'view';

if ($_POST) {
    $filters['date_from'] = $_POST['date_from'] ?? '';
    $filters['date_to'] = $_POST['date_to'] ?? '';
    $filters['payment_type'] = $_POST['payment_type'] ?? '';
    $filters['payment_status'] = $_POST['payment_status'] ?? '';
    $filters['booking_reference'] = $_POST['booking_reference'] ?? '';
    $filters['invoice_number'] = $_POST['invoice_number'] ?? '';
    $filters['issued_by'] = $_POST['issued_by'] ?? '';
}

// Function to get payment data with filters
function getPaymentReport($pdo, $filters = []) {
    // Step 1: Fetch all payment records
    $sql = "SELECT 
                p.booking_reference,
                p.invoice_number,
                p.contact_no,
                p.whatsapp_no,
                p.email,
                p.rate_per_plate,
                p.additional_plate_rate,
                p.remarks,
                p.value_type,
                p.total_amount,
                p.payment_type,
                p.payment_amount,
                p.pending_amount,
                p.no_of_pax,
                p.issued_by,
                p.payment_date
            FROM payments p";
    
    $whereConditions = [];
    $params = [];
    
    if (!empty($filters['date_from'])) {
        $whereConditions[] = "DATE(p.payment_date) >= :date_from";
        $params['date_from'] = $filters['date_from'];
    }
    
    if (!empty($filters['date_to'])) {
        $whereConditions[] = "DATE(p.payment_date) <= :date_to";
        $params['date_to'] = $filters['date_to'];
    }
    
    if (!empty($filters['payment_type'])) {
        $whereConditions[] = "p.payment_type = :payment_type";
        $params['payment_type'] = $filters['payment_type'];
    }
    
    if (!empty($filters['payment_status'])) {
        switch($filters['payment_status']) {
            case 'paid':
                $whereConditions[] = "p.pending_amount = 0";
                break;
            case 'unpaid':
                $whereConditions[] = "p.payment_amount = 0";
                break;
            case 'partial':
                $whereConditions[] = "p.pending_amount > 0 AND p.payment_amount > 0";
                break;
        }
    }
    
    if (!empty($filters['booking_reference'])) {
        $whereConditions[] = "p.booking_reference LIKE :booking_ref";
        $params['booking_ref'] = '%' . $filters['booking_reference'] . '%';
    }
    
    if (!empty($filters['invoice_number'])) {
        $whereConditions[] = "p.invoice_number LIKE :invoice_num";
        $params['invoice_num'] = '%' . $filters['invoice_number'] . '%';
    }
    
    if (!empty($filters['issued_by'])) {
        $whereConditions[] = "p.issued_by LIKE :issued_by";
        $params['issued_by'] = '%' . $filters['issued_by'] . '%';
    }
    
    if (!empty($whereConditions)) {
        $sql .= " WHERE " . implode(" AND ", $whereConditions);
    }
    
    $sql .= " ORDER BY p.payment_date DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Step 2: Group by booking_reference and calculate totals
    $groupedPayments = [];
    foreach ($payments as $payment) {
        $bookingRef = $payment['booking_reference'];
        if (!isset($groupedPayments[$bookingRef])) {
            $groupedPayments[$bookingRef] = [
                'total_amount' => 0,
                'payment_amount' => 0,
                'records' => []
            ];
        }
        $groupedPayments[$bookingRef]['total_amount'] += $payment['total_amount'];
        $groupedPayments[$bookingRef]['payment_amount'] += $payment['payment_amount'];
        $groupedPayments[$bookingRef]['records'][] = $payment;
    }
    
    // Step 3: Adjust pending_amount and payment_status
    $result = [];
    foreach ($groupedPayments as $bookingRef => $group) {
        $totalAmount = $group['total_amount'];
        $totalPayments = $group['payment_amount'];
        $totalPending = max(0, $totalAmount - $totalPayments);
        
        foreach ($group['records'] as $payment) {
            $adjustedPayment = $payment;
            
            // If total payments cover total amount for this booking reference
            if ($totalPayments >= $totalAmount) {
                $adjustedPayment['pending_amount'] = 0;
                $adjustedPayment['payment_status'] = 'Fully Paid';
                $adjustedPayment['payment_percentage'] = 100;
            } else {
                // Distribute pending amount proportionally or keep original if single invoice
                if (count($group['records']) > 1 && $totalAmount > 0) {
                    $proportion = $payment['total_amount'] / $totalAmount;
                    $adjustedPayment['pending_amount'] = round($totalPending * $proportion, 2);
                } else {
                    $adjustedPayment['pending_amount'] = $payment['total_amount'] - $payment['payment_amount'];
                }
                
                if ($adjustedPayment['pending_amount'] == 0) {
                    $adjustedPayment['payment_status'] = 'Fully Paid';
                    $adjustedPayment['payment_percentage'] = 100;
                } elseif ($adjustedPayment['payment_amount'] == 0) {
                    $adjustedPayment['payment_status'] = 'Unpaid';
                    $adjustedPayment['payment_percentage'] = 0;
                } else {
                    $adjustedPayment['payment_status'] = 'Partially Paid';
                    $adjustedPayment['payment_percentage'] = round(($payment['payment_amount'] / $payment['total_amount']) * 100, 2);
                }
            }
            
            $result[] = $adjustedPayment;
        }
    }
    
    return $result;
}

// Function to get summary statistics
function getPaymentSummary($pdo, $filters = []) {
    // Get the adjusted payment report
    $payments = getPaymentReport($pdo, $filters);
    
    $summary = [
        'total_records' => count($payments),
        'total_invoice_amount' => 0,
        'total_payments_received' => 0,
        'total_pending_amount' => 0,
        'average_invoice_amount' => 0,
        'fully_paid_count' => 0,
        'unpaid_count' => 0,
        'partially_paid_count' => 0,
        'total_pax' => 0
    ];
    
    foreach ($payments as $payment) {
        $summary['total_invoice_amount'] += $payment['total_amount'];
        $summary['total_payments_received'] += $payment['payment_amount'];
        $summary['total_pending_amount'] += $payment['pending_amount'];
        $summary['total_pax'] += $payment['no_of_pax'];
        
        if ($payment['payment_status'] === 'Fully Paid') {
            $summary['fully_paid_count']++;
        } elseif ($payment['payment_status'] === 'Unpaid') {
            $summary['unpaid_count']++;
        } elseif ($payment['payment_status'] === 'Partially Paid') {
            $summary['partially_paid_count']++;
        }
    }
    
    if ($summary['total_records'] > 0) {
        $summary['average_invoice_amount'] = $summary['total_invoice_amount'] / $summary['total_records'];
    }
    
    return $summary;
}

// Function to get payment types for dropdown
function getPaymentTypes($pdo) {
    $stmt = $pdo->query("SELECT DISTINCT payment_type FROM payments ORDER BY payment_type");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Function to get staff members for dropdown
function getStaffMembers($pdo) {
    $stmt = $pdo->query("SELECT DISTINCT issued_by FROM payments WHERE issued_by IS NOT NULL ORDER BY issued_by");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Handle CSV export
if ($action === 'export') {
    $payments = getPaymentReport($pdo, $filters);
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="payment_report_' . date('Y-m-d_H-i-s') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    if (!empty($payments)) {
        // Write headers
        $headers = [
            'Booking Reference', 'Invoice Number', 'Contact No', 'WhatsApp No', 
            'Email', 'Rate Per Plate', 'Additional Plate Rate', 'Remarks', 'Value Type',
            'Total Amount', 'Payment Type', 'Payment Amount', 'Pending Amount', 
            'No of Pax', 'Issued By', 'Payment Date', 'Payment Status', 'Payment Percentage'
        ];
        fputcsv($output, $headers);
        
        // Write data
        foreach ($payments as $row) {
            fputcsv($output, [
                $row['booking_reference'], $row['invoice_number'],
                $row['contact_no'], $row['whatsapp_no'], $row['email'],
                $row['rate_per_plate'], $row['additional_plate_rate'], $row['remarks'],
                $row['value_type'], $row['total_amount'], $row['payment_type'],
                $row['payment_amount'], $row['pending_amount'], $row['no_of_pax'],
                $row['issued_by'], $row['payment_date'], $row['payment_status'],
                $row['payment_percentage']
            ]);
        }
    }
    
    fclose($output);
    exit;
}

// Get data for display
$payments = getPaymentReport($pdo, $filters);
$summary = getPaymentSummary($pdo, $filters);
$paymentTypes = getPaymentTypes($pdo);
$staffMembers = getStaffMembers($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Function Payment Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --success: #10b981;
            --success-dark: #059669;
            --warning: #f59e0b;
            --warning-dark: #d97706;
            --danger: #ef4444;
            --danger-dark: #dc2626;
            --info: #06b6d4;
            --info-dark: #0891b2;
            --purple: #8b5cf6;
            --purple-dark: #7c3aed;
            
            --bg-primary: #f8fafc;
            --bg-secondary: #f1f5f9;
            --bg-card: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --border-light: #f1f5f9;
            
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            
            --rounded: 0.5rem;
            --rounded-lg: 0.75rem;
            --rounded-xl: 1rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            font-size: 14px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        /* Header Section */
        .header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--purple) 100%);
            color: white;
            padding: 3rem 2rem;
            border-radius: var(--rounded-xl);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(50%, -50%);
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .header p {
            font-size: 1.125rem;
            opacity: 0.9;
            font-weight: 300;
        }

        .header-icon {
            position: absolute;
            top: 2rem;
            right: 2rem;
            font-size: 3rem;
            opacity: 0.2;
        }

        /* Filters Section */
        .filters-section {
            background: var(--bg-card);
            padding: 2rem;
            border-radius: var(--rounded-xl);
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .section-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .section-header i {
            color: var(--primary);
            font-size: 1.25rem;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .form-control {
            padding: 0.75rem 1rem;
            border: 2px solid var(--border);
            border-radius: var(--rounded);
            font-size: 0.875rem;
            transition: all 0.2s ease;
            background: var(--bg-card);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-control:hover {
            border-color: var(--text-muted);
        }

        /* Buttons */
        .button-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--rounded);
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: var(--shadow-sm);
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-secondary {
            background: var(--text-muted);
            color: white;
        }

        .btn-secondary:hover {
            background: var(--text-secondary);
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            background: var(--success-dark);
        }

        .btn-info {
            background: var(--info);
            color: white;
        }

        .btn-info:hover {
            background: var(--info-dark);
        }

        /* Summary Section */
        .summary-section {
            margin-bottom: 2rem;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .summary-card {
            background: var(--bg-card);
            padding: 2rem;
            border-radius: var(--rounded-xl);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary), var(--purple));
        }

        .card-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .card-icon {
            width: 3rem;
            height: 3rem;
            border-radius: var(--rounded-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .card-icon.primary { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); }
        .card-icon.success { background: linear-gradient(135deg, var(--success), var(--success-dark)); }
        .card-icon.warning { background: linear-gradient(135deg, var(--warning), var(--warning-dark)); }
        .card-icon.danger { background: linear-gradient(135deg, var(--danger), var(--danger-dark)); }
        .card-icon.info { background: linear-gradient(135deg, var(--info), var(--info-dark)); }
        .card-icon.purple { background: linear-gradient(135deg, var(--purple), var(--purple-dark)); }

        .card-title {
            font-size: 0.875rem;
            color: var(--text-muted);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .card-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .card-change {
            font-size: 0.75rem;
            color: var(--success);
            font-weight: 500;
        }

        /* Table Section */
        .table-section {
            background: var(--bg-card);
            border-radius: var(--rounded-xl);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .table-header {
            padding: 2rem;
            border-bottom: 1px solid var(--border);
        }

        .table-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            gap: 1rem;
        }

        .search-box {
            position: relative;
            max-width: 300px;
            flex: 1;
        }

        .search-box input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 2px solid var(--border);
            border-radius: var(--rounded);
            font-size: 0.875rem;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: var(--bg-secondary);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-light);
            font-size: 0.875rem;
            vertical-align: middle;
        }

        tbody tr:hover {
            background: var(--bg-secondary);
        }

        .amount {
            text-align: right;
            font-weight: 600;
            font-family: 'Monaco', 'Menlo', monospace;
        }

        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .status-fully-paid {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-dark);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .status-unpaid {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-dark);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .status-partially-paid {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning-dark);
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .header {
                padding: 2rem 1rem;
                text-align: center;
            }

            .header h1 {
                font-size: 2rem;
            }

            .header-icon {
                display: none;
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .button-group {
                justify-content: center;
            }

            .table-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                max-width: none;
            }

            th, td {
                padding: 0.75rem 0.5rem;
                font-size: 0.75rem;
            }

            .card-value {
                font-size: 1.5rem;
            }
        }

        /* Print Styles */
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            @page {
                size: A4 landscape;
                margin: 0.5in;
            }

            body {
                background: white !important;
                color: black !important;
                font-size: 7pt !important;
                line-height: 1.2 !important;
            }

            .container {
                max-width: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }

            .header,
            .filters-section,
            .summary-section,
            .button-group,
            .table-controls,
            .search-box {
                display: none !important;
            }

            .table-section {
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                background: white !important;
                overflow: visible !important;
            }

            .table-header {
                border-bottom: 2px solid #000 !important;
                padding: 0.5rem 0 !important;
                page-break-after: avoid !important;
            }

            .section-header h2 {
                font-size: 14pt !important;
                font-weight: bold !important;
                color: #000 !important;
                text-align: center !important;
                margin: 0 !important;
            }

            .section-header i {
                display: none !important;
            }

            .table-wrapper {
                overflow: visible !important;
                width: 100% !important;
            }

            table {
                width: 100% !important;
                font-size: 6pt !important;
                border-collapse: collapse !important;
                table-layout: fixed !important;
                margin: 0 !important;
                page-break-inside: auto !important;
            }

            /* Optimized column widths for landscape A4 */
            th:nth-child(1), td:nth-child(1) { width: 8% !important; } /* Invoice # */
            th:nth-child(2), td:nth-child(2) { width: 8% !important; } /* Booking Ref */
            th:nth-child(3), td:nth-child(3) { width: 8% !important; } /* Contact */
            th:nth-child(4), td:nth-child(4) { width: 12% !important; } /* Email */
            th:nth-child(5), td:nth-child(5) { width: 7% !important; } /* Total Amount */
            th:nth-child(6), td:nth-child(6) { width: 7% !important; } /* Paid Amount */
            th:nth-child(7), td:nth-child(7) { width: 7% !important; } /* Pending */
            th:nth-child(8), td:nth-child(8) { width: 6% !important; } /* Payment Type */
            th:nth-child(9), td:nth-child(9) { width: 6% !important; } /* Status */
            th:nth-child(10), td:nth-child(10) { width: 4% !important; } /* Pax */
            th:nth-child(11), td:nth-child(11) { width: 6% !important; } /* Rate/Plate */
            th:nth-child(12), td:nth-child(12) { width: 7% !important; } /* Date */
            th:nth-child(13), td:nth-child(13) { width: 7% !important; } /* Issued By */
            th:nth-child(14), td:nth-child(14) { width: 7% !important; } /* Remarks */

            th, td {
                padding: 2px 1px !important;
                border: 0.5px solid #000 !important;
                font-size: 6pt !important;
                line-height: 1.1 !important;
                vertical-align: top !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
                white-space: normal !important;
                page-break-inside: avoid !important;
            }

            th {
                background: #e8e8e8 !important;
                font-weight: bold !important;
                text-align: center !important;
                font-size: 5.5pt !important;
                text-transform: uppercase !important;
                padding: 3px 1px !important;
            }

            th i {
                display: none !important;
            }

            td {
                text-align: left !important;
                font-size: 5.5pt !important;
            }

            .amount {
                text-align: right !important;
                font-weight: normal !important;
                font-family: Arial, sans-serif !important;
            }

            tbody tr {
                page-break-inside: avoid !important;
                background: white !important;
            }

            tbody tr:nth-child(even) {
                background: #f8f8f8 !important;
            }

            tbody tr:hover {
                background: inherit !important;
            }

            /* Status badges for print */
            .status-badge {
                display: inline-block !important;
                padding: 1px 3px !important;
                border: 0.5px solid #000 !important;
                background: transparent !important;
                color: #000 !important;
                font-size: 5pt !important;
                border-radius: 2px !important;
                font-weight: bold !important;
            }

            .status-fully-paid {
                background: #e8f5e8 !important;
            }

            .status-unpaid {
                background: #ffe8e8 !important;
            }

            .status-partially-paid {
                background: #fff8e8 !important;
            }

            /* Remove links styling for print */
            a {
                color: #000 !important;
                text-decoration: none !important;
            }

            /* Compact spans for print */
            span {
                display: inline !important;
                padding: 0 !important;
                margin: 0 !important;
                background: transparent !important;
                border: none !important;
                font-size: inherit !important;
            }

            /* Hide icons in print except status icons */
            i:not(.status-badge i) {
                display: none !important;
            }

            .empty-state {
                text-align: center !important;
                padding: 2rem !important;
                font-size: 12pt !important;
                border: 2px solid #000 !important;
            }

            /* Page breaks */
            .table-section {
                page-break-before: auto !important;
            }

            thead {
                display: table-header-group !important;
            }

            tbody {
                display: table-row-group !important;
            }

            /* Force table to fit on page */
            .table-wrapper {
                transform: scale(1) !important;
                transform-origin: top left !important;
            }

            /* Prevent widows and orphans */
            p, td, th {
                orphans: 3 !important;
                widows: 3 !important;
            }

            /* Title for print */
            .table-header::before {
                content: "Function Payment Report";
                display: block !important;
                text-align: center !important;
                font-size: 16pt !important;
                font-weight: bold !important;
                margin-bottom: 10px !important;
                border-bottom: 2px solid #000 !important;
                padding-bottom: 5px !important;
            }
        }

        /* Additional print optimization for very wide tables */
        @media print and (max-width: 11in) {
            table {
                font-size: 5pt !important;
            }
            
            th, td {
                font-size: 5pt !important;
                padding: 1px !important;
            }
            
            th {
                font-size: 4.5pt !important;
            }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Fade in animation */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
     <div class="wrapper">
        <div style="position: absolute; top: 20px; left: 20px;">
           <style>
@media print {
  .no-print {
    display: none !important;
  }
}
</style>

<button onclick="window.location.href='../accounts.php'" 
        class="no-print"
        style="background-color: #f09424ff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
    Back
</button>
    <div class="container">
        <!-- Header -->
        <div class="header fade-in">
            <div class="header-content">
                <h1><i class="fas fa-ring"></i> Function Bill Report</h1>
                
            </div>
            <i class="fas fa-heart header-icon"></i>
        </div>

        <!-- Filters Section -->
        <div class="filters-section fade-in">
            <div class="section-header">
                <i class="fas fa-filter"></i>
                <h2>Filter & Search</h2>
            </div>
            
            <form method="POST" action="">
                <div class="filters-grid">
                    <div class="form-group">
                        <label for="date_from">
                            <i class="far fa-calendar-alt"></i> From Date
                        </label>
                        <input type="date" id="date_from" name="date_from" class="form-control" 
                               value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="date_to">
                            <i class="far fa-calendar-alt"></i> To Date
                        </label>
                        <input type="date" id="date_to" name="date_to" class="form-control" 
                               value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_type">
                            <i class="fas fa-credit-card"></i> Payment Type
                        </label>
                        <select id="payment_type" name="payment_type" class="form-control">
                            <option value="">All Types</option>
                            <?php foreach ($paymentTypes as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>" <?= ($filters['payment_type'] ?? '') === $type ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_status">
                            <i class="fas fa-check-circle"></i> Payment Status
                        </label>
                        <select id="payment_status" name="payment_status" class="form-control">
                            <option value="">All Status</option>
                            <option value="paid" <?= ($filters['payment_status'] ?? '') === 'paid' ? 'selected' : '' ?>>Fully Paid</option>
                            <option value="unpaid" <?= ($filters['payment_status'] ?? '') === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                            <option value="partial" <?= ($filters['payment_status'] ?? '') === 'partial' ? 'selected' : '' ?>>Partially Paid</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="booking_reference">
                            <i class="fas fa-bookmark"></i> Booking Reference
                        </label>
                        <input type="text" id="booking_reference" name="booking_reference" class="form-control"
                               value="<?= htmlspecialchars($filters['booking_reference'] ?? '') ?>" 
                               placeholder="Search booking...">
                    </div>
                    
                    <div class="form-group">
                        <label for="invoice_number">
                            <i class="fas fa-file-invoice"></i> Invoice Number
                        </label>
                        <input type="text" id="invoice_number" name="invoice_number" class="form-control"
                               value="<?= htmlspecialchars($filters['invoice_number'] ?? '') ?>" 
                               placeholder="Search invoice...">
                    </div>
                    
                    <div class="form-group">
                        <label for="issued_by">
                            <i class="fas fa-user-tie"></i> Issued By
                        </label>
                        <select id="issued_by" name="issued_by" class="form-control">
                            <option value="">All Staff</option>
                            <?php foreach ($staffMembers as $staff): ?>
                            <option value="<?= htmlspecialchars($staff) ?>" <?= ($filters['issued_by'] ?? '') === $staff ? 'selected' : '' ?>>
                                <?= htmlspecialchars($staff) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                    <a href="?" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear Filters
                    </a>
                    <a href="?action=export<?= $_POST ? '&' . http_build_query($_POST) : '' ?>" class="btn btn-success">
                        <i class="fas fa-download"></i> Export CSV
                    </a>
                    <button type="button" class="btn btn-info" onclick="printReport()">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                </div>
            </form>
        </div>

        <!-- Summary Section -->
        <div class="summary-section fade-in">
            <div class="section-header">
                <i class="fas fa-chart-bar"></i>
                <h2>Summary Statistics</h2>
            </div>
            
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="card-icon primary">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="card-title">Total Records</div>
                    <div class="card-value"><?= number_format($summary['total_records']) ?></div>
                    <div class="card-change">
                        <i class="fas fa-arrow-up"></i> Active bookings
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="card-icon success">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="card-title">Total Invoice Amount</div>
                    <div class="card-value">Rs.<?= number_format($summary['total_invoice_amount'], 2) ?></div>
                    <div class="card-change">
                        <i class="fas fa-chart-line"></i> Total business value
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="card-icon info">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div class="card-title">Payments Received</div>
                    <div class="card-value">Rs.<?= number_format($summary['total_payments_received'], 2) ?></div>
                    <div class="card-change">
                        <i class="fas fa-percentage"></i> 
                        <?= $summary['total_invoice_amount'] > 0 ? round(($summary['total_payments_received'] / $summary['total_invoice_amount']) * 100, 1) : 0 ?>% collected
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="card-icon danger">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="card-title">Pending Amount</div>
                    <div class="card-value">Rs.<?= number_format($summary['total_pending_amount'], 2) ?></div>
                    <div class="card-change">
                        <i class="fas fa-clock"></i> Outstanding payments
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="card-icon success">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div class="card-title">Fully Paid</div>
                    <div class="card-value"><?= number_format($summary['fully_paid_count']) ?></div>
                    <div class="card-change">
                        <i class="fas fa-smile"></i> Completed payments
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="card-icon danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="card-title">Unpaid</div>
                    <div class="card-value"><?= number_format($summary['unpaid_count']) ?></div>
                    <div class="card-change">
                        <i class="fas fa-frown"></i> Requires follow-up
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="card-icon warning">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="card-title">Partially Paid</div>
                    <div class="card-value"><?= number_format($summary['partially_paid_count']) ?></div>
                    <div class="card-change">
                        <i class="fas fa-balance-scale"></i> In progress
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="card-icon purple">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-title">Total Guests</div>
                    <div class="card-value"><?= number_format($summary['total_pax']) ?></div>
                    <div class="card-change">
                        <i class="fas fa-utensils"></i> Total pax served
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table Section -->
        <div class="table-section fade-in">
            <div class="table-header">
                <div class="section-header">
                    <i class="fas fa-table"></i>
                    <h2>Payment Details (<?= number_format(count($payments)) ?> records)</h2>
                </div>
                
                <div class="table-controls">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="tableSearch" placeholder="Search in table..." onkeyup="searchTable()">
                    </div>
                </div>
            </div>
            
            <?php if (empty($payments)): ?>
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h3>No Records Found</h3>
                    <p>No payment records match your current filter criteria.</p>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table id="paymentsTable">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag"></i> Invoice #</th>
                                <th><i class="fas fa-bookmark"></i> Booking Ref</th>
                                <th><i class="fas fa-phone"></i> Contact</th>
                                <th><i class="fas fa-envelope"></i> Email</th>
                                <th><i class="fas fa-money-bill"></i> Total Amount</th>
                                <th><i class="fas fa-hand-holding-usd"></i> Paid Amount</th>
                                <th><i class="fas fa-clock"></i> Pending</th>
                                <th><i class="fas fa-credit-card"></i> Payment Type</th>
                                <th><i class="fas fa-info-circle"></i> Status</th>
                                <th><i class="fas fa-users"></i> Pax</th>
                                <th><i class="fas fa-calculator"></i> Rate/Plate</th>
                                <th><i class="fas fa-calendar"></i> Date</th>
                                <th><i class="fas fa-user"></i> Issued By</th>
                                <th><i class="fas fa-sticky-note"></i> Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($payment['invoice_number']) ?></strong></td>
                                <td>
                                    <span style="background: rgba(59, 130, 246, 0.1); color: var(--primary-dark); padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem;">
                                        <?= htmlspecialchars($payment['booking_reference']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($payment['whatsapp_no']): ?>
                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $payment['whatsapp_no']) ?>" 
                                           target="_blank" style="color: var(--success); text-decoration: none;">
                                            <i class="fab fa-whatsapp"></i> <?= htmlspecialchars($payment['contact_no']) ?>
                                        </a>
                                    <?php else: ?>
                                        <?= htmlspecialchars($payment['contact_no']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($payment['email']): ?>
                                        <a href="mailto:<?= htmlspecialchars($payment['email']) ?>" 
                                           style="color: var(--primary); text-decoration: none;">
                                            <?= htmlspecialchars($payment['email']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="amount">Rs.<?= number_format($payment['total_amount'], 2) ?></td>
                                <td class="amount" style="color: var(--success);">Rs.<?= number_format($payment['payment_amount'], 2) ?></td>
                                <td class="amount" style="color: var(--danger);">Rs.<?= number_format($payment['pending_amount'], 2) ?></td>
                                <td>
                                    <span style="background: rgba(139, 92, 246, 0.1); color: var(--purple-dark); padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem;">
                                        <?= htmlspecialchars($payment['payment_type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $payment['payment_status'])) ?>">
                                        <?php if($payment['payment_status'] === 'Fully Paid'): ?>
                                            <i class="fas fa-check"></i>
                                        <?php elseif($payment['payment_status'] === 'Unpaid'): ?>
                                            <i class="fas fa-times"></i>
                                        <?php else: ?>
                                            <i class="fas fa-clock"></i>
                                        <?php endif; ?>
                                        <?= $payment['payment_status'] ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <span style="background: var(--bg-secondary); padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-weight: 600;">
                                        <?= $payment['no_of_pax'] ?>
                                    </span>
                                </td>
                                <td class="amount">Rs.<?= number_format($payment['rate_per_plate'], 2) ?></td>
                                <td><?= date('M d, Y', strtotime($payment['payment_date'])) ?></td>
                                <td>
                                    <span style="color: var(--info-dark);">
                                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($payment['issued_by']) ?>
                                    </span>
                                </td>
                                <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                    <?= htmlspecialchars($payment['remarks']) ?: '—' ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function printReport() {
            window.print();
        }

        function searchTable() {
            const input = document.getElementById('tableSearch');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('paymentsTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < cells.length; j++) {
                    if (cells[j].textContent.toLowerCase().includes(filter)) {
                        found = true;
                        break;
                    }
                }
                
                rows[i].style.display = found ? '' : 'none';
            }
        }

        // Add loading state to buttons on form submit
        document.querySelector('form').addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="loading"></span> Processing...';
            submitBtn.disabled = true;
            
            // Re-enable after 3 seconds (fallback)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 3000);
        });

        // Add fade-in animation to elements as they come into view
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.summary-card, .table-section').forEach(el => {
            observer.observe(el);
        });

        // Add hover effects to summary cards
        document.querySelectorAll('.summary-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-4px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Auto-refresh functionality (optional)
        let autoRefresh = false;
        function toggleAutoRefresh() {
            autoRefresh = !autoRefresh;
            if (autoRefresh) {
                setInterval(() => {
                    if (autoRefresh) {
                        location.reload();
                    }
                }, 60000); // Refresh every minute
            }
        }
    </script>
</body>
</html>