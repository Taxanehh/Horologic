<?php
ini_set('SMTP', 'localhost'); ini_set('smtp_port', 1025);
ini_set('sendmail_from', 'paul.stokreef@gmail.com');

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
 * Convert UTF-8 to CP1252 for FPDF (depending on your font setup).
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
    // Fetch repair data plus any associated products
    $stmt = $conn->prepare("
        SELECT h.*, rp.product_name, rp.amount, rp.price 
        FROM horloges h
        LEFT JOIN repair_products rp 
               ON h.ReparatieNummer = rp.reparatie_nummer
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
$products   = array_filter($result, fn($row) => !empty($row['product_name']));

// Optional: decide how many days until the quote is valid
$validUntil = isset($_POST['valid_until']) 
    ? $_POST['valid_until'] 
    : date('d-m-Y', strtotime('+14 days'));

class PDF extends FPDF {
    private function initFonts() {
        // Ensure these fonts exist in your /fpdf/font/ folder
        $this->AddFont('DejaVuSans', '', 'DejaVuSans.php');
        $this->AddFont('DejaVuSans', 'B', 'DejaVuSans-Bold.php');
        $this->AddFont('DejaVuSans', 'I', 'DejaVuSans-Oblique.php');
        $this->AddFont('DejaVuSans', 'BI', 'DejaVuSans-BoldOblique.php');
    }

    public function __construct() {
        parent::__construct();
        $this->initFonts();
    }

    // Custom Header
    function Header() {
        // Logo (top-left)
        // Adjust x,y,width as needed (x=10, y=10, width=30)
        $this->Image(__DIR__ . '/../../../public/img/horologic.png', 10, 27, 50);

        // Company info on the right
        $this->SetXY(130, 10);
        $this->SetFont('DejaVuSans', '', 10);
        $this->MultiCell(
            0, 5,
            conv("Horologic BV\nSample Street 123\n1234 AB Amsterdam\nThe Netherlands\nTel: +31 00 000 000\nEmail: info@horologic.example.com"),
            0, 'R'
        );

        // Some extra vertical space
        $this->Ln(20);

        // Draw a horizontal line
        $this->SetDrawColor(200, 200, 200);
        $this->SetLineWidth(0.5);
        $this->Line(10, 40, 200, 40);
        $this->Ln(10);

        // Title: QUOTATION
        $this->SetFont('DejaVuSans', 'B', 16);
        $this->Cell(0, -20, conv('QUOTATION'), 0, 1, 'C');
        $this->Ln(2);
    }

    // Custom Footer
    function Footer() {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        $this->SetFont('DejaVuSans', 'I', 8);
        // Page number
        $this->Cell(0, 10, conv('Page ') . $this->PageNo() . ' / {nb}', 0, 0, 'C');
    }
}

try {
    // Create PDF
    $pdf = new PDF();
    $pdf->AliasNbPages(); // Needed for the {nb} in the footer
    $pdf->AddPage();
    $pdf->SetY(70);

    // ---- CLIENT / QUOTE DETAILS ----
    $pdf->SetFont('DejaVuSans', '', 12);

    // "Bill To" / "To"
    $pdf->SetFont('DejaVuSans', 'B', 12);
    $pdf->Cell(0, 8, conv('Bill To:'), 0, 1);
    $pdf->SetFont('DejaVuSans', '', 12);
    
    // Customer name + (optional) address from DB
    $pdf->MultiCell(0, 6, conv($repairData['Bedrijfsnaam'] ?? 'Unknown'));
    // If you have a separate "Address" or "City" column:
    if (!empty($repairData['Address'])) {
        $pdf->MultiCell(0, 6, conv($repairData['Address']));
    }
    $pdf->Ln(8);

    // Some quote info on a new line
    $pdf->SetFont('DejaVuSans', '', 12);
    $quoteNumber = 'Quote Number: ' . $repairData['ReparatieNummer'];
    $quoteDate   = 'Date: ' . date('d-m-Y');
    $quoteValid  = 'Valid Until: ' . $validUntil;

    // You can place them side by side using Cells:
    $pdf->Cell(60, 8, conv($quoteNumber), 0, 0);
    $pdf->Cell(50, 8, conv($quoteDate), 0, 0);
    $pdf->Cell(60, 8, conv($quoteValid), 0, 1);
    $pdf->Ln(6);

    // ---- REPAIR PRODUCTS TABLE ----
    $pdf->SetFont('DejaVuSans', 'B', 12);
    $pdf->Cell(0, 10, conv('Repair Products'), 0, 1, 'L');
    $pdf->Ln(2);

    if (!empty($products)) {
        // Table header
        $pdf->SetFont('DejaVuSans', 'B', 10);
        $pdf->Cell(80, 7, conv('Product'), 1, 0, 'C');
        $pdf->Cell(25, 7, conv('Amount'), 1, 0, 'C');
        $pdf->Cell(25, 7, conv('Price'), 1, 0, 'R');
        $pdf->Cell(30, 7, conv('Subtotal'), 1, 0, 'R');
        $pdf->Ln();

        // Table rows
        $pdf->SetFont('DejaVuSans', '', 10);
        $total = 0;
        foreach ($products as $product) {
            $subtotal = $product['amount'] * $product['price'];
            $total += $subtotal;
            $pdf->Cell(80, 7, conv($product['product_name']), 1);
            $pdf->Cell(25, 7, conv($product['amount']), 1, 0, 'C');
            $pdf->Cell(25, 7, conv('€ ' . number_format($product['price'], 2)), 1, 0, 'R');
            $pdf->Cell(30, 7, conv('€ ' . number_format($subtotal, 2)), 1, 0, 'R');
            $pdf->Ln();
        }

        // Total row
        $pdf->SetFont('DejaVuSans', 'B', 10);
        $pdf->Cell(130, 7, conv('Total'), 1);
        $pdf->Cell(30, 7, conv('€ ' . number_format($total, 2)), 1, 0, 'R');
        $pdf->Ln(10);

    } else {
        // No products
        $pdf->SetFont('DejaVuSans', 'I', 10);
        $pdf->Cell(0, 10, conv('No repair products added.'), 0, 1);
        $pdf->Ln(5);
    }

    // ---- ADDITIONAL COMMENTS ----
    if (!empty($_POST['comments'])) {
        $pdf->SetFont('DejaVuSans', 'B', 12);
        $pdf->Cell(0, 10, conv('Additional Comments'), 0, 1);
        $pdf->SetFont('DejaVuSans', '', 10);
        $pdf->MultiCell(0, 6, conv($_POST['comments']));
        $pdf->Ln(5);
    }

    // --- SIGNATURE TITLE & IMAGE ---
    $pdf->SetFont('DejaVuSans', 'B', 12);

    // Show the signature image. Adjust X/Y/width as needed:
    $pdf->Image(__DIR__ . '/../../../public/img/signature.png', $pdf->GetX(), $pdf->GetY(), 30);
    $pdf->Ln(0); // extra spacing after the image

    // ---- SIGNATURE (OPTIONAL) ----
    // If you want an actual signature image or a line for a signature:
    /*
    $pdf->Ln(15);
    $pdf->Cell(0, 10, '__________________________', 0, 1, 'L');
    $pdf->Cell(0, 5, conv('Authorized Signature'), 0, 1, 'L');
    */

    $token = bin2hex(random_bytes(16));

    // Update the current repair record with the token and set the quotation status to 'pending'
    $updateStmt = $conn->prepare("UPDATE horloges SET quote_token = ?, quote_status = 'pending' WHERE ReparatieNummer = ?");
    $updateStmt->execute([$token, $repairId]);

    // Construct the acceptance URL (adjust yourdomain.com to your actual domain)
    $acceptUrl = "http://localhost:3000/accept_quote?quote_id=20&token=9f0f1f78bead7eff737f0a2f6d4220e2";

    // Prepare the email message
    $subject = "Your Quotation from Horologic";
    $message = "Dear Customer,\n\n"
            . "Please review your quotation. To accept the quotation, please click the link below:\n\n"
            . $acceptUrl . "\n\n"
            . "If you do not wish to accept, you can decline the quotation by visiting the same link and leaving a comment.\n\n"
            . "Thank you,\nHorologic Team";

    // Send the email (you might want to add additional headers for HTML or proper From: information)
    mail($repairData['Adres'], $subject, $message);

    // Output to browser
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
