<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['logged_in'])) {
    header("Location: /login");
    exit;
}

while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

require_once 'db.php';
require 'fpdf/fpdf.php';

/**
 * Converts a UTF‑8 string to CP1252.
 * Make sure that your TTF font and its generated definition files were created using CP1252.
 */
function conv($str) {
    return iconv('UTF-8', 'CP1252//TRANSLIT', $str);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['repair_id'])) {
    header("Location: /home");
    exit;
}

$repairId = (int)$_POST['repair_id'];
$conn = getDbConnection();

try {
    $stmt = $conn->prepare("
        SELECT h.*, rp.product_name, rp.amount, rp.price 
        FROM horloges h
        LEFT JOIN repair_products rp ON h.ReparatieNummer = rp.reparatie_nummer
        WHERE h.ReparatieNummer = ?
    ");
    $stmt->execute([$repairId]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    header("Location: /home");
    exit;
}

if (empty($result)) {
    header("Location: /home");
    exit;
}

$repairData = $result[0];
$products = array_filter($result, function($row) {
    return !empty($row['product_name']);
});

class PDF extends FPDF {
    private function initFonts() {
        // Add all font variants.
        // IMPORTANT: Ensure these font files were generated using CP1252.
        $this->AddFont('DejaVuSans', '', 'DejaVuSans.php');
        $this->AddFont('DejaVuSans', 'B', 'DejaVuSans-Bold.php');
        $this->AddFont('DejaVuSans', 'I', 'DejaVuSans-Oblique.php');
        $this->AddFont('DejaVuSans', 'BI', 'DejaVuSans-BoldOblique.php');
    }

    function __construct() {
        parent::__construct();
        $this->initFonts();
    }

    function Header() {
        $this->SetFont('DejaVuSans', '', 10);
        @$this->Image(__DIR__ . '/../../../public/img/horologic.png', 10, 6, 30);
        $this->Cell(80);
        $this->MultiCell(0, 5, "Horologic BV\nSample Street 123\n1234 AB Amsterdam\nThe Netherlands");
        $this->Ln(20);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('DejaVuSans', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
        @$this->Image(__DIR__ . '/../../../public/img/horologic.png', 160, $this->GetY()-20, 40);
    }
}

try {
    $pdf = new PDF();
    $pdf->AddPage();

    // Quote header
    $pdf->SetFont('DejaVuSans', 'B', 14);
    $pdf->Cell(0, 10, conv('QUOTATION'), 0, 1, 'C');
    $pdf->Ln(10);

    // Client info
    $pdf->SetFont('DejaVuSans', '', 12);
    $pdf->Cell(40, 10, conv('To:'));
    $pdf->MultiCell(0, 6, conv($repairData['Bedrijfsnaam'] . "\n" . $repairData['Address']));
    $pdf->Ln(10);

    // Quote details
    $pdf->SetFont('DejaVuSans', '', 12);
    $pdf->Cell(40, 10, conv('Quote Number: ' . $repairData['ReparatieNummer']));
    $pdf->Cell(40, 10, conv('Date: ' . date('d-m-Y')));
    $validUntil = isset($_POST['valid_until']) ? $_POST['valid_until'] : date('d-m-Y', strtotime('+14 days'));
    $pdf->Cell(40, 10, conv('Valid Until: ' . $validUntil), 0, 1);
    $pdf->Ln(10);

    // Repair Products Section
    $pdf->SetFont('DejaVuSans', 'B', 12);
    $pdf->Cell(0, 10, conv('Repair Products'), 0, 1);
    $pdf->Ln(4);

    if (!empty($products)) {
        // Table Header
        $pdf->SetFont('DejaVuSans', 'B', 10);
        $pdf->Cell(80, 7, conv('Product'), 1);
        $pdf->Cell(25, 7, conv('Amount'), 1, 0, 'C');
        $pdf->Cell(25, 7, conv('Price'), 1, 0, 'R');
        $pdf->Cell(30, 7, conv('Subtotal'), 1, 0, 'R');
        $pdf->Ln();

        // Table Data
        $pdf->SetFont('DejaVuSans', '', 10);
        $total = 0;
        
        foreach ($products as $product) {
            $subtotal = $product['amount'] * $product['price'];
            $total += $subtotal;
            
            $priceText = conv('€ ' . number_format($product['price'], 2));
            $subtotalText = conv('€ ' . number_format($subtotal, 2));
            
            $pdf->Cell(80, 7, conv($product['product_name']), 1);
            $pdf->Cell(25, 7, conv($product['amount']), 1, 0, 'C');
            $pdf->Cell(25, 7, $priceText, 1, 0, 'R');
            $pdf->Cell(30, 7, $subtotalText, 1, 0, 'R');
            $pdf->Ln();
        }

        // Total Row
        $pdf->SetFont('DejaVuSans', 'B', 10);
        $pdf->Cell(130, 7, conv('Total'), 1);
        $pdf->Cell(30, 7, conv('€ ' . number_format($total, 2)), 1, 0, 'R');
        $pdf->Ln();
    } else {
        $pdf->SetFont('DejaVuSans', 'I', 10);
        $pdf->Cell(0, 10, conv('No repair products added'), 0, 1);
        $pdf->Ln();
    }

    // Comments
    if (!empty($_POST['comments'])) {
        $pdf->SetFont('DejaVuSans', '', 12);
        $pdf->MultiCell(0, 10, conv('Additional Comments: ' . $_POST['comments']));
    }

    ob_end_clean();
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="quote-' . $repairId . '.pdf"');
    $pdf->Output('D', 'quote-' . $repairId . '.pdf');
    exit;

} catch (Exception $e) {
    error_log('PDF generation failed: ' . $e->getMessage());
    header("Location: /home");
    exit;
}
