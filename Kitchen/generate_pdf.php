<?php
// Include Composer autoloader
try {
    require_once '../vendor/autoload.php';
} catch (Exception $e) {
    die("Failed to load Composer autoloader: " . $e->getMessage());
}

// Use TCPDF namespace
use TCPDF as GlobalTCPDF;
use Tecnickcom\TCPDF\TCPDF;

// Set timezone to Indian Standard Time
date_default_timezone_set('Asia/Kolkata');

// DB connection (for reference, if needed to fetch additional data)
$conn = new mysqli("localhost", "hotelgrandguardi_root", "Sun123flower@", "hotelgrandguardi_wedding_bliss");
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// Get data from POST request
$addedItems = json_decode($_POST['addedItems'] ?? '[]', true);
if (empty($addedItems)) {
    error_log("No items provided in POST data: " . print_r($_POST, true));
    die("No items provided for PDF generation. Please ensure items are added in the form before generating the PDF.");
}

// Initialize TCPDF
try {
    $pdf = new GlobalTCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
} catch (Exception $e) {
    die("Failed to initialize TCPDF: " . $e->getMessage());
}

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Wedding Bliss');
$pdf->SetTitle('Kitchen Usage & Refilling Sheet');
$pdf->SetSubject('Kitchen Inventory Report');

// Set default header data with IST time
$pdf->SetHeaderData('', 0, 'Kitchen Usage & Refilling Sheet', 'Generated on ' . date('Y-m-d H:i:s'), [0, 64, 255], [0, 64, 128]);
$pdf->setFooterData([0, 64, 0], [0, 64, 128]);

// Rest of your PDF generation code remains the same...
// Set header and footer fonts
$pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
$pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

// Set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Set font
$pdf->SetFont('helvetica', '', 10);

// Add a page
$pdf->AddPage();

// Generate HTML content for the table
$html = '
<style>
    h1 {
        color: #1a56db;
        text-align: center;
        font-size: 16pt;
        margin-bottom: 20px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    th {
        background-color: #e2e8f0;
        color: #1a202c;
        font-weight: bold;
        padding: 8px;
        border: 1px solid #cbd5e0;
        text-align: center;
    }
    td {
        padding: 8px;
        border: 1px solid #cbd5e0;
        text-align: center;
        color: #2d3748;
    }
    tr:nth-child(even) {
        background-color: #f7fafc;
    }
</style>
<h1>Kitchen Usage & Refilling Sheet</h1>
<table>
    <thead>
        <tr>
            <th>Item Name</th>
            <th>Buffer Stock</th>
            <th>Remaining Stock</th>
            <th>Usage Qty</th>
            <th>Refill Qty</th>
        </tr>
    </thead>
    <tbody>';

foreach ($addedItems as $item) {
    $html .= '
        <tr>
            <td>' . htmlspecialchars($item['item_name'] ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($item['buffer_qty'] ?? 0) . ' ' . htmlspecialchars($item['unit'] ?? '') . '</td>
            <td>' . htmlspecialchars($item['usage_qty'] ?? 0) . ' ' . htmlspecialchars($item['unit'] ?? '') . '</td>
            <td>' . htmlspecialchars($item['unload'] ?? 0) . ' ' . htmlspecialchars($item['unit'] ?? '') . '</td>
            <td>' . htmlspecialchars($item['refill'] ?? 0) . ' ' . htmlspecialchars($item['unit'] ?? '') . '</td>
        </tr>';
}

$html .= '
    </tbody>
</table>';

// Write HTML to PDF
try {
    $pdf->writeHTML($html, true, false, true, false, '');
} catch (Exception $e) {
    die("Failed to write HTML to PDF: " . $e->getMessage());
}

// Output the PDF
try {
    $pdf->Output('kitchen_usage_refilling_sheet.pdf', 'I');
} catch (Exception $e) {
    die("Failed to output PDF: " . $e->getMessage());
}
?>