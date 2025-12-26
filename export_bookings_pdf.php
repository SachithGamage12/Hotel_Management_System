<?php
// Start output buffering to capture any unintended output
ob_start();

// Disable error output and enable logging
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/errors.log');

// Include mPDF library
require_once __DIR__ . '/vendor/autoload.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set up database connection (modify with your database credentials)
$dbHost = 'localhost';
$dbUser = 'hotelgrandguardi_root';
$dbPass = 'Sun123flower@';
$dbName = 'hotelgrandguardi_wedding_bliss';

try {
    $conn = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Connection failed: " . $e->getMessage(), 3, __DIR__ . '/errors.log');
    ob_end_clean();
    die("Error: Unable to connect to the database. Please check errors.log.");
}

// Fetch year and month from query parameters
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m') - 1;
$start = sprintf("%d-%02d-01", $year, $month + 1);
$end = sprintf("%d-%02d-01", $year, $month + 2);

// Fetch booking data from room_bookings table, grouping by guest details
try {
    $stmt = $conn->prepare("
        SELECT 
            guest_name, 
            telephone, 
            check_in, 
            check_out, 
            GROUP_CONCAT(room_number ORDER BY room_number) AS room_numbers, 
            pax, 
            remarks, 
            function_type
        FROM room_bookings 
        WHERE check_in <= :end_date AND check_out >= :start_date
        GROUP BY guest_name, telephone, check_in, check_out, pax, remarks, function_type
        ORDER BY check_in, guest_name
    ");
    $stmt->execute(['start_date' => $start, 'end_date' => $end]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Query failed: " . $e->getMessage(), 3, __DIR__ . '/errors.log');
    ob_end_clean();
    die("Error: Unable to fetch booking data. Please check errors.log.");
}

// Create new mPDF instance with modern configuration
$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 25,
    'margin_bottom' => 20,
    'margin_header' => 10,
    'margin_footer' => 10,
    'default_font' => 'dejavusans',
    'default_font_size' => 10
]);

// Set document metadata
$mpdf->SetCreator('Grand Guardian Hotel');
$mpdf->SetAuthor('Grand Guardian Hotel');
$mpdf->SetTitle('Room Bookings Report');
$mpdf->SetSubject('Booking Details for ' . date('F Y', strtotime($start)));
$mpdf->SetKeywords('Bookings, Hotel, Report, Multi-Room');

// Set header and footer
$monthName = date('F', mktime(0, 0, 0, $month + 1, 1, $year));
$header = '
<div style="text-align: center; border-bottom: 2px solid #1a73e8; padding-bottom: 10px;">
    <h1 style="font-size: 18pt; color: #1a73e8; margin: 0;">Grand Guardian Hotel</h1>
    <h2 style="font-size: 14pt; color: #333; margin: 5px 0;">Room Bookings Report</h2>
    <p style="font-size: 10pt; color: #666; margin: 0;">' . $monthName . ' ' . $year . '</p>
</div>';

$footer = '
<div style="text-align: center; font-size: 8pt; color: #666;">
    Page {PAGENO} of {NB} | Generated on ' . date('d M Y') . ' | Grand Guardian Hotel
</div>';

$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLFooter($footer);

// Create the HTML content with modern styling
$html = '
<style>
    body {
        font-family: dejavusans, sans-serif;
        color: #333;
    }
    .report-container {
        margin: 10px 0;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9pt;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    th, td {
        border: 1px solid #e0e0e0;
        padding: 8px 6px;
        text-align: left;
        vertical-align: top;
    }
    th {
        background-color: #1a73e8;
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .telephone-column {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    tr:nth-child(even) {
        background-color: #f8fafc;
    }
    tr:hover {
        background-color: #e8f0fe;
    }
    .no-data {
        text-align: center;
        font-style: italic;
        color: #666;
        padding: 20px;
    }
    .table-header {
        background-color: #f1f3f4;
        font-weight: bold;
    }
</style>
<div class="report-container">
    <table>
        <tr>
            <th style="width: 22%;">Guest Name</th>
            <th style="width: 15%;">Telephone</th>
            <th style="width: 12%;">Check-in</th>
            <th style="width: 12%;">Check-out</th>
            <th style="width: 18%;">Room Numbers</th>
            <th style="width: 8%;">Pax</th>
            <th style="width: 15%;">Remarks</th>
            <th style="width: 10%;">Function Type</th>
        </tr>';

if (empty($bookings)) {
    $html .= '<tr><td colspan="8" class="no-data">No bookings found for this period.</td></tr>';
} else {
    foreach ($bookings as $booking) {
        $html .= '
        <tr>
            <td><div style="word-wrap: break-word;">' . htmlspecialchars($booking['guest_name'] ?? 'N/A') . '</div></td>
            <td class="telephone-column"><div style="white-space: nowrap;">' . htmlspecialchars($booking['telephone'] ?? 'N/A') . '</div></td>
            <td><div style="white-space: nowrap;">' . htmlspecialchars($booking['check_in'] ?? 'N/A') . '</div></td>
            <td><div style="white-space: nowrap;">' . htmlspecialchars($booking['check_out'] ?? 'N/A') . '</div></td>
            <td><div style="word-wrap: break-word;">' . htmlspecialchars($booking['room_numbers'] ?? 'N/A') . '</div></td>
            <td><div style="white-space: nowrap;">' . htmlspecialchars($booking['pax'] ?? '0') . '</div></td>
            <td><div style="word-wrap: break-word;">' . htmlspecialchars($booking['remarks'] ?? 'N/A') . '</div></td>
            <td><div style="word-wrap: break-word;">' . htmlspecialchars($booking['function_type'] ?? 'N/A') . '</div></td>
        </tr>';
    }
}

$html .= '</table></div>';

// Write the HTML content to the PDF
$mpdf->WriteHTML($html);

// Clean the output buffer
ob_end_clean();

// Output the PDF
$filename = 'Bookings_' . $monthName . '_' . $year . '.pdf';
$mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
?>