<?php
// Start output buffering
ob_start();
ini_set('memory_limit', '512M');

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/errors.log');

// Set Sri Lankan timezone
date_default_timezone_set('Asia/Colombo');

// ✅ Include Composer autoloader (loads TCPDF + dependencies)
require_once __DIR__ . '/../vendor/autoload.php';

use TCPDF;

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set up database connection
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

// Fetch booking data
try {
    $stmt = $conn->prepare("
        SELECT 
            guest_name, 
            telephone, 
            check_in, 
            check_out, 
            GROUP_CONCAT(room_number ORDER BY room_number SEPARATOR ', ') AS room_numbers, 
            pax, 
            remarks, 
            function_type
        FROM sapthapadhiroom_bookings 
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

// Custom TCPDF class with professional design
class PremiumTCPDF extends TCPDF {
    public function Header() {
        // Premium header with gradient background
        $this->SetFillColor(245, 247, 250);
        $this->Rect(0, 0, $this->getPageWidth(), 45, 'F');
        
        // Elegant border
        $this->SetDrawColor(59, 130, 246);
        $this->SetLineWidth(2);
        $this->Line(0, 43, $this->getPageWidth(), 43);
        
        // Logo with premium black circle
        $logoPath = __DIR__ . '/../image/sapthapadhi logo png.png';
        if (file_exists($logoPath)) {
            // Premium black circle with subtle gradient effect
            $this->SetFillColor(20, 20, 20);
            $this->SetDrawColor(40, 40, 40);
            $this->Circle(25, 22, 16, 0, 360, 'FD');
            
            // Inner highlight for depth
            $this->SetDrawColor(60, 60, 60);
            $this->SetLineWidth(0.5);
            $this->Circle(25, 22, 14, 0, 360, 'D');
            
            // Logo placement - larger and more visible
            $this->Image($logoPath, 9, 6, 32, 32, 'PNG', '', '', true, 300, '', false, false, 0, 'CM');
        }
        
        // Premium typography
        $this->SetFont('times', 'B', 22);
        $this->SetTextColor(20, 20, 20);
        $this->SetXY(50, 10);
        $this->Cell(0, 10, 'Hotel sapthapadhi ', 0, 1, 'L', 0, '', 0);
        
        // Elegant subtitle
        $this->SetFont('times', 'I', 14);
        $this->SetTextColor(59, 130, 246);
        $this->SetXY(50, 22);
        $this->Cell(0, 8, 'Premium Room Bookings Report', 0, 1, 'L', 0, '', 0);
        
        // Report period with Sri Lankan time
        $monthName = date('F', mktime(0, 0, 0, $_GET['month'] + 1 ?? date('m'), 1, $_GET['year'] ?? date('Y')));
        $year = $_GET['year'] ?? date('Y');
        $sriLankanTime = date('d/m/Y H:i:s T');
        
        $this->SetFont('times', '', 11);
        $this->SetTextColor(75, 85, 99);
        $this->SetXY(50, 32);
        $this->Cell(100, 6, "Report Period: {$monthName} {$year}", 0, 0, 'L', 0, '', 0);
        
        // Right side - Sri Lankan time
        $this->SetXY($this->getPageWidth() - 100, 15);
        $this->SetFont('times', '', 9);
        $this->SetTextColor(107, 114, 128);
        $this->Cell(90, 5, 'Generated (Sri Lanka Time):', 0, 1, 'R', 0, '', 0);
        
        $this->SetXY($this->getPageWidth() - 100, 22);
        $this->SetFont('times', 'B', 10);
        $this->SetTextColor(20, 20, 20);
        $this->Cell(90, 5, $sriLankanTime, 0, 1, 'R', 0, '', 0);
        
        $this->SetXY($this->getPageWidth() - 100, 30);
        $this->SetFont('times', '', 8);
        $this->SetTextColor(107, 114, 128);
        $this->Cell(90, 4, 'Ratnapura, Sri Lanka', 0, 1, 'R', 0, '', 0);
        
        $this->Ln(8);
    }

    public function Footer() {
        // Premium footer
        $this->SetY(-20);
        
        // Elegant top border
        $this->SetDrawColor(59, 130, 246);
        $this->SetLineWidth(1);
        $this->Line(15, $this->getPageHeight() - 18, $this->getPageWidth() - 15, $this->getPageHeight() - 18);
        
        $this->SetFont('times', '', 9);
        $this->SetTextColor(75, 85, 99);
        
        // Three-column footer layout
        $this->SetY(-12);
        $this->Cell(80, 6, 'Hotel sapthapadhi - Confidential Report', 0, 0, 'L', 0, '', 0);
        
        $pageInfo = 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages();
        $this->Cell(0, 6, $pageInfo, 0, 0, 'C', 0, '', 0);
        
        $this->SetX($this->getPageWidth() - 70);
        $sriLankanTime = date('H:i:s T');
        $this->Cell(60, 6, "Time: {$sriLankanTime}", 0, 0, 'R', 0, '', 0);
    }
}

// Create PDF instance
$pdf = new PremiumTCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Grand Guardian Hotel Management System');
$pdf->SetAuthor('GRAND VIEW sapthapadhi');
$pdf->SetTitle('Premium Room Bookings Report - ' . date('F Y', strtotime($start)));
$pdf->SetSubject('Detailed Booking Analysis');
$pdf->SetKeywords('Hotel, Bookings, Report, Premium, Grand Guardian');

// Premium margins
$pdf->SetMargins(15, 50, 15);
$pdf->SetHeaderMargin(8);
$pdf->SetFooterMargin(15);
$pdf->SetAutoPageBreak(TRUE, 20);

// Add first page
$pdf->AddPage();

// Text truncation function
function truncateText($text, $maxLength = 30) {
    if (strlen($text) > $maxLength) {
        return substr($text, 0, $maxLength - 3) . '...';
    }
    return $text;
}

// Premium table design
$pdf->SetFont('times', 'B', 9);

// Sophisticated header styling
$pdf->SetFillColor(30, 58, 138);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetDrawColor(226, 232, 240);

// Optimized column widths for all data
$colWidths = [
    'guest_name' => 55,
    'telephone' => 32,
    'check_in' => 26,
    'check_out' => 26,
    'room_numbers' => 38,
    'pax' => 20,
    'remarks' => 48,
    'function_type' => 35
];

// Professional headers
$headers = [
    'GUEST NAME',
    'TELEPHONE',
    'CHECK IN',
    'CHECK OUT',
    'ROOM NUMBERS',
    'GUESTS',
    'REMARKS',
    'FUNCTION TYPE'
];

// Header row with premium styling
$headerHeight = 12;
foreach ($headers as $i => $header) {
    $width = array_values($colWidths)[$i];
    $pdf->Cell($width, $headerHeight, $header, 1, 0, 'C', 1);
}
$pdf->Ln();

// Data rows with premium styling
$pdf->SetTextColor(31, 41, 55);
$pdf->SetFont('times', '', 8);

if (empty($bookings)) {
    $pdf->SetFillColor(249, 250, 251);
    $pdf->SetTextColor(107, 114, 128);
    $pdf->Cell(array_sum($colWidths), 15, 'No bookings found for the specified period.', 1, 1, 'C', 1);
} else {
    $rowNum = 0;
    foreach ($bookings as $booking) {
        // Premium alternating row colors
        if ($rowNum % 2 == 0) {
            $pdf->SetFillColor(248, 250, 252);
        } else {
            $pdf->SetFillColor(255, 255, 255);
        }
        
        // Special highlighting for VIP bookings (if function_type contains "VIP" or "Wedding")
        if (stripos($booking['function_type'] ?? '', 'wedding') !== false || 
            stripos($booking['function_type'] ?? '', 'vip') !== false) {
            $pdf->SetFillColor(254, 249, 195); // Light gold
        }
        
        // Format data professionally
        $cellData = [
            truncateText(ucwords(strtolower($booking['guest_name'] ?? 'N/A')), 35),
            $booking['telephone'] ?? 'Not Provided',
            date('d-M-Y', strtotime($booking['check_in'] ?? 'now')),
            date('d-M-Y', strtotime($booking['check_out'] ?? 'now')),
            truncateText($booking['room_numbers'] ?? 'TBA', 28),
            ($booking['pax'] ?? '0') . ' Guest' . (($booking['pax'] ?? 0) != 1 ? 's' : ''),
            truncateText($booking['remarks'] ?? 'No remarks', 40),
            truncateText(ucwords(strtolower($booking['function_type'] ?? 'Standard')), 25)
        ];
        
        // Page break management
        if ($pdf->GetY() > 170) {
            $pdf->AddPage();
            
            // Reprint headers
            $pdf->SetFont('times', 'B', 9);
            $pdf->SetFillColor(30, 58, 138);
            $pdf->SetTextColor(255, 255, 255);
            foreach ($headers as $i => $header) {
                $width = array_values($colWidths)[$i];
                $pdf->Cell($width, $headerHeight, $header, 1, 0, 'C', 1);
            }
            $pdf->Ln();
            $pdf->SetTextColor(31, 41, 55);
            $pdf->SetFont('times', '', 8);
        }
        
        $rowHeight = 10;
        $startY = $pdf->GetY();
        $startX = $pdf->GetX();
        
        // Render row
        foreach ($cellData as $i => $data) {
            $width = array_values($colWidths)[$i];
            $pdf->SetXY($startX + array_sum(array_slice(array_values($colWidths), 0, $i)), $startY);
            
            // Multi-line support for remarks
            if ($i == 6 && strlen($data) > 35) {
                $pdf->MultiCell($width, $rowHeight/2, $data, 1, 'L', $rowNum % 2 == 0, 0);
            } else {
                $pdf->Cell($width, $rowHeight, $data, 1, 0, 'L', $rowNum % 2 == 0);
            }
        }
        $pdf->Ln();
        $rowNum++;
    }
}

// Premium summary section
$pdf->Ln(15);

// Summary header
$pdf->SetFillColor(30, 58, 138);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('times', 'B', 12);
$pdf->Cell(0, 10, 'EXECUTIVE SUMMARY', 0, 1, 'L', 1);
$pdf->Ln(5);

// Statistics
$totalBookings = count($bookings);
$totalPax = array_sum(array_column($bookings, 'pax'));
$avgPaxPerBooking = $totalBookings > 0 ? round($totalPax / $totalBookings, 1) : 0;
$monthName = date('F', mktime(0, 0, 0, $month + 1, 1, $year));

// Create summary table
$pdf->SetFont('times', '', 10);
$pdf->SetTextColor(31, 41, 55);
$pdf->SetFillColor(248, 250, 252);

$summaryData = [
    ['METRIC', 'VALUE', 'DETAILS'],
    ['Total Bookings', $totalBookings, 'Active reservations'],
    ['Total Guests', $totalPax, 'All confirmed attendees'],
    ['Average Group Size', $avgPaxPerBooking, 'Guests per booking'],
    ['Report Period', "{$monthName} {$year}", 'Monthly analysis'],
    ['Generated Time', date('d/m/Y H:i:s T'), 'Sri Lankan Standard Time']
];

foreach ($summaryData as $i => $row) {
    if ($i == 0) {
        $pdf->SetFont('times', 'B', 9);
        $pdf->SetFillColor(59, 130, 246);
        $pdf->SetTextColor(255, 255, 255);
    } else {
        $pdf->SetFont('times', '', 9);
        $pdf->SetFillColor($i % 2 == 0 ? 248 : 255, $i % 2 == 0 ? 250 : 255, $i % 2 == 0 ? 252 : 255);
        $pdf->SetTextColor(31, 41, 55);
    }
    
    $pdf->Cell(60, 8, $row[0], 1, 0, 'L', 1);
    $pdf->Cell(40, 8, $row[1], 1, 0, 'C', 1);
    $pdf->Cell(80, 8, $row[2], 1, 1, 'L', 1);
}

// Clean output buffer
ob_end_clean();

// Generate filename with Sri Lankan timestamp
$timestamp = date('Ymd_His');
$filename = "GrandGuardian_Bookings_{$monthName}_{$year}_{$timestamp}.pdf";

// Output PDF
$pdf->Output($filename, 'D');
?>