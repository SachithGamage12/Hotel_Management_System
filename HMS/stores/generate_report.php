<?php
// generate_order_report.php
require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';

class OrderSheetReport extends TCPDF {
    private $db;
    
    public function __construct($db) {
        // Use landscape orientation for A4
        parent::__construct('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
        $this->db = $db;
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor('Your Company');
        $this->SetTitle('Order Sheet Report');
        $this->SetSubject('Order Sheet Details');
        $this->SetKeywords('PDF, Order Sheet, Report');
        $this->SetMargins(10, 20, 10);
        $this->SetAutoPageBreak(true, 15);
    }
    
    public function Header() {
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 15, 'Order Sheet Report', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
    }
    
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
    
    private function fetchData($statusFilter = 'all', $orderSheetNo = null, $functionDate = null) {
        $query = "SELECT 
                    os.item_id, os.requested_qty, os.issued_qty, os.status, 
                    os.request_date, os.order_sheet_no, os.responsible_id, 
                    os.function_type, os.function_date, os.day_night,
                    i.item_name, i.unit,
                    COALESCE(r.name, 'N/A') as responsible_name
                  FROM order_sheet os
                  JOIN inventory i ON os.item_id = i.id
                  LEFT JOIN responsible r ON os.responsible_id = r.id";
        
        $where = [];
        $params = [];
        
        if ($statusFilter !== 'all') {
            $where[] = "os.status = ?";
            $params[] = $statusFilter;
        }
        
        if ($orderSheetNo !== null) {
            $where[] = "os.order_sheet_no = ?";
            $params[] = $orderSheetNo;
        }
        
        if ($functionDate !== null) {
            $where[] = "os.function_date = ?";
            $params[] = $functionDate;
        }
        
        if (!empty($where)) {
            $query .= " WHERE " . implode(' AND ', $where);
        }
        
        $query .= " ORDER BY os.order_sheet_no, os.function_date DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function generateSummaryTable($data) {
        // Set table header
        $this->SetFont('helvetica', 'B', 6);
        $this->Cell(35, 6, 'Item Name', 1, 0, 'C');
        $this->Cell(25, 6, 'Requested Qty', 1, 0, 'C');
        $this->Cell(25, 6, 'Issued Qty', 1, 0, 'C');
        $this->Cell(15, 6, 'Status', 1, 0, 'C');
        $this->Cell(25, 6, 'Request Date', 1, 0, 'C');
        $this->Cell(20, 6, 'Order Sheet No', 1, 0, 'C');
        $this->Cell(35, 6, 'Responsible', 1, 0, 'C');
        $this->Cell(25, 6, 'Function Type', 1, 0, 'C');
        $this->Cell(20, 6, 'Function Date', 1, 0, 'C');
        $this->Cell(15, 6, 'Day/Night', 1, 1, 'C');
        
        // Set table data
        $this->SetFont('helvetica', '', 5);
        foreach ($data as $row) {
            // Combine quantities with unit
            $req_qty = $row['requested_qty'] !== null ? number_format($row['requested_qty'], 2) . ' ' . ($row['unit'] ?? 'N/A') : 'N/A';
            $iss_qty = $row['issued_qty'] !== null ? number_format($row['issued_qty'], 2) . ' ' . ($row['unit'] ?? 'N/A') : 'N/A';
            
            // Use MultiCell for text wrapping
            $this->MultiCell(35, 5, $row['item_name'], 1, 'L', false, 0);
            $this->MultiCell(25, 5, $req_qty, 1, 'R', false, 0);
            $this->MultiCell(25, 5, $iss_qty, 1, 'R', false, 0);
            $this->MultiCell(15, 5, ucfirst($row['status']), 1, 'C', false, 0);
            $this->MultiCell(25, 5, $row['request_date'], 1, 'C', false, 0);
            $this->MultiCell(20, 5, $row['order_sheet_no'], 1, 'C', false, 0);
            $this->MultiCell(35, 5, $row['responsible_name'], 1, 'L', false, 0);
            $this->MultiCell(25, 5, $row['function_type'], 1, 'L', false, 0);
            $this->MultiCell(20, 5, $row['function_date'] ?? 'N/A', 1, 'C', false, 0);
            $this->MultiCell(15, 5, $row['day_night'] ?? 'N/A', 1, 'C', false, 1);
        }
    }
    
    private function generateTotals($data) {
        $totalRequested = array_sum(array_column($data, 'requested_qty'));
        $totalIssued = array_sum(array_column($data, 'issued_qty'));
        
        $this->Ln(5);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(150, 6, 'Total Requested Quantity:', 0, 0, 'R');
        $this->Cell(30, 6, number_format($totalRequested, 2), 0, 1, 'R');
        
        $this->Cell(150, 6, 'Total Issued Quantity:', 0, 0, 'R');
        $this->Cell(30, 6, number_format($totalIssued, 2), 0, 1, 'R');
    }
    
    public function generateReport($statusFilter = 'all', $orderSheetNo = null, $functionDate = null) {
        $data = $this->fetchData($statusFilter, $orderSheetNo, $functionDate);
        
        $this->AddPage();
        
        // Report title based on filters
        $title = 'All Order Sheets';
        if ($statusFilter === 'pending') {
            $title = 'Pending Order Sheets';
        } elseif ($statusFilter === 'issued') {
            $title = 'Issued Order Sheets';
        }
        if ($orderSheetNo !== null) {
            $title .= ' - Order Sheet No: ' . $orderSheetNo;
        }
        if ($functionDate !== null) {
            $title .= ' - Function Date: ' . date('F j, Y', strtotime($functionDate));
        }
        
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 10, $title, 0, 1, 'C');
        $this->Ln(5);
        
        if (empty($data)) {
            $this->SetFont('helvetica', '', 12);
            $this->Cell(0, 10, 'No data found for the selected filters.', 0, 1, 'C');
            return;
        }
        
        $this->generateSummaryTable($data);
        $this->generateTotals($data);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Database connection
        $db = new PDO('mysql:host=localhost;dbname=hotelgrandguardi_wedding_bliss', 'hotelgrandguardi_root', 'Sun123flower@');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $report = new OrderSheetReport($db);
        
        $statusFilter = $_POST['statusFilter'] ?? 'all';
        $orderSheetNo = !empty($_POST['orderSheetNo']) ? $_POST['orderSheetNo'] : null;
        $functionDate = !empty($_POST['functionDate']) ? $_POST['functionDate'] : null;
        
        $report->generateReport($statusFilter, $orderSheetNo, $functionDate);
        
        $report->Output('order_sheet_report.pdf', 'I');
    } catch (Exception $e) {
        die('Error generating report: ' . $e->getMessage());
    }
}
?>