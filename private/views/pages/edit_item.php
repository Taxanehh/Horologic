<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . '/../layout/header.php';
require_once 'db.php';

// Check authentication
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: /login");
    exit;
}

// Get item ID from URL path
if (!isset($_GET['url'])) {
    die("Item ID ontbreekt");
}

$pathParts = explode('/', $_GET['url']);
$reparatieNummer = end($pathParts);

if (!is_numeric($reparatieNummer)) {
    die("Ongeldig reparatienummer");
}

$statusTags = [
    "Nieuw" => "nieuw-box",
    "In bewerking" => "bewerk-box",
    "Inspectie" => "inspec-box",
    "Toestemming" => "toest-box",
    "Kosten akkoord" => "kosten-box",
    "Beoordelen" => "beoordeel-box",
    "In de wacht" => "wacht-box",
    "Leverancier" => "leverancier-box",
    "Geannuleerd" => "annu-box",
    "Reparatie klaar" => "repa-box",
    "Teruggestuurd" => "terug-box",
    "Alle statussen" => "alle-box"
  ];

// Fetch item data
$conn = getDbConnection();
$stmt = $conn->prepare("SELECT * FROM horloges WHERE ReparatieNummer = ?");
$stmt->execute([$reparatieNummer]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    die("Item niet gevonden");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $updateData = [
        'Bedrijfsnaam' => $_POST['Bedrijfsnaam'] ?? '',
        'Adres' => $_POST['Adres'] ?? '',
        'Merk' => $_POST['Merk'] ?? '',
        'Model' => $_POST['Model'] ?? '',
        'Serienummer' => $_POST['Serienummer'] ?? '',
        'Tag' => $_POST['Tag'] ?? '',
        'Klacht' => $_POST['Klacht'] ?? '',
        'Opmerkingen' => $_POST['Opmerkingen'] ?? '',
        'ReparatieNummer' => $reparatieNummer
    ];

    // NEW: Prepare the UPDATE statement separately
    $updateStmt = $conn->prepare("
        UPDATE horloges SET
            Bedrijfsnaam = :Bedrijfsnaam,
            Adres = :Adres,
            Merk = :Merk,
            Model = :Model,
            Serienummer = :Serienummer,
            Tag = :Tag,
            Klacht = :Klacht,
            Opmerkingen = :Opmerkingen
        WHERE ReparatieNummer = :ReparatieNummer
    ");

    if ($updateStmt->execute($updateData)) {
        $_SESSION['success'] = "Wijzigingen opgeslagen!";
    } else {
        $errorInfo = $updateStmt->errorInfo();
        error_log("Database error: " . print_r($errorInfo, true)); // Log detailed error
        $_SESSION['error'] = "Fout bij opslaan: " . $errorInfo[2]; // Human-readable error
    }
    header("Location: /edit/" . $reparatieNummer);
    exit;

}

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
session_write_close(); // Add this line
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Reparatie #<?= htmlspecialchars($item['ReparatieNummer']) ?> Bewerken</title>
    <link rel="stylesheet" href="/css/globals.css">

    <!-- Example styling to mimic the screenshot layout -->
    <style>
        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
        }
        
        /* Top bar (if you want to replicate the screenshot’s nav) */
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #ddd;
        }
        .topbar nav a {
            margin-right: 1rem;
        }

        /* Main heading */
        .main-heading {
            margin-top: 1rem;
            font-size: 1.5rem;
            font-weight: bold;
        }

        /* Two-column "Product" / "Reparatie" info row */
        .info-grid {
            display: grid;
            grid-template-columns: 2fr 2fr;
            gap: 2rem;
            margin-top: 1rem;
        }
        .info-box {
            background: #f8f8f8;
            padding: 1rem;
            border-radius: 4px;
        }
        .info-box h2 {
            margin-top: 0;
        }
        .info-line {
            margin: 0.5rem 0;
        }
        .info-line span {
            font-weight: bold;
        }

        /* Complaint / extra notes area */
        .complaint-section {
            margin-top: 1rem;
            display: grid;
            grid-template-columns: 2fr 2fr;
            gap: 2rem;
        }
        .complaint-box {
            background: #fff;
            padding: 1rem;
            border: 1px solid #eee;
        }
        .complaint-box h3 {
            margin-top: 0;
        }

        /* Reparatie regels table */
        .reparatie-regels {
            margin-top: 2rem;
            background: #fff;
            border: 1px solid #eee;
            padding: 1rem;
        }
        .reparatie-regels table {
            width: 100%;
            border-collapse: collapse;
        }
        .reparatie-regels th, .reparatie-regels td {
            padding: 0.5rem;
            border-bottom: 1px solid #ddd;
        }

        /* Activity log and images area */
        .bottom-sections {
            display: grid;
            grid-template-columns: 2fr 2fr;
            gap: 2rem;
            margin-top: 2rem;
        }
        .activity-log, .images-box {
            background: #fff;
            border: 1px solid #eee;
            padding: 1rem;
        }

        /* Right-side action buttons (the big colored buttons) */
        .right-actions {
            position: absolute; /* Or flex it on the right if you prefer. */
            top: 120px;
            right: 50px;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .right-actions button,
        .right-actions a.button-like {
            display: block;
            background: #eee;
            border: none;
            padding: 0.5rem 1rem;
            text-align: left;
            cursor: pointer;
            border-radius: 4px;
            text-decoration: none;
        }
        .right-actions button:hover,
        .right-actions a.button-like:hover {
            background: #ddd;
        }

        /* Example success/error messages */
        .success-message {
            color: green;
            margin-bottom: 1rem;
        }
        .error-message {
            color: red;
            margin-bottom: 1rem;
        }

        /* Form styling (if you still want the editable fields visible here) */
        form {
            margin-top: 2rem;
            background: #fafafa;
            border: 1px solid #eee;
            padding: 1rem;
        }
        label {
            font-weight: bold;
        }
        .form-group {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

<div class="page-container">

    <!-- Main heading -->
    <div class="main-heading">
        Reparatie no. <?= htmlspecialchars($item['ReparatieNummer']) ?>
    </div>

    <!-- Example success/error messages -->
    <?php if($success): ?>
        <div class="success-message"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- The two-column info section -->
    <div class="info-grid">
        <!-- Left: Product info -->
        <div class="info-box">
            <h2>Product</h2>
            <div class="info-line"><span>Merk:</span> <?= htmlspecialchars($item['Merk']) ?></div>
            <div class="info-line"><span>Model:</span> <?= htmlspecialchars($item['Model']) ?></div>
            <div class="info-line"><span>Serienummer:</span> <?= htmlspecialchars($item['Serienummer']) ?></div>
            <!-- etc. -->
        </div>

        <!-- Right: Reparatie info -->
        <div class="info-box">
            <h2>Reparatie</h2>
            <div class="info-line"><span>Garantietype:</span> (bijv. Ja/Nee)</div>
            <div class="info-line"><span>Status:</span> <?= htmlspecialchars($item['Tag']) ?></div>
            <div class="info-line"><span>Max. kosten:</span> € 45,00 (voorbeeld)</div>
            <!-- etc. -->
        </div>
    </div>

    <!-- Complaint + extra explanation, internal remarks, etc. -->
    <div class="complaint-section">
        <div class="complaint-box">
            <h3>Klachtomschrijving</h3>
            <p><?= nl2br(htmlspecialchars($item['Klacht'])) ?></p>
        </div>
        <div class="complaint-box">
            <h3>Interne opmerkingen</h3>
            <p><?= nl2br(htmlspecialchars($item['Opmerkingen'])) ?></p>
        </div>
    </div>

    <!-- Reparatie regels (table of lines) -->
    <div class="reparatie-regels">
        <h3>Reparatie regels</h3>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Aantal</th>
                    <th>Prijs</th>
                    <th>Subtotaal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Nog geen reparatie regels</td>
                    <td>-</td>
                    <td>-</td>
                    <td>€ 0,00</td>
                </tr>
            </tbody>
        </table>
        <div style="text-align:right; margin-top: 1rem;">
            <strong>Totaal €0,00</strong>
        </div>
    </div>

    <!-- Activity log & images -->
    <div class="bottom-sections">
        <div class="activity-log">
            <h3>Activiteiten log</h3>
            <p>Nog geen activiteiten aanwezig (voorbeeld)</p>
        </div>
        <div class="images-box">
            <h3>Afbeeldingen</h3>
            <p>Nog geen afbeeldingen aanwezig (voorbeeld)</p>
        </div>
    </div>

    <!-- OPTIONAL: Editing form at bottom (or in a modal, etc.) -->
    <!-- If you want inline editing right on this page: -->
    <form method="POST">
        <h2>Bewerken</h2>
        <div class="form-group">
            <label for="Bedrijfsnaam">Bedrijfsnaam</label>
            <input type="text" name="Bedrijfsnaam" 
                   value="<?= htmlspecialchars($item['Bedrijfsnaam']) ?>">
        </div>
        <!-- Add all other fields similarly -->
        
        <div class="form-group">
            <button type="submit">Opslaan</button>
            <button type="button" onclick="location.href='/home'">Annuleren</button>
        </div>
    </form>

</div><!-- end .page-container -->

<!-- Right-side big buttons -->
<div class="right-actions">
    <button>Begin reparatie</button>
    <a href="#" class="button-like">Bewerken</a>
    <a href="#" class="button-like">E-mailadres toevoegen</a>
    <a href="#" class="button-like">Afbeeldingen toevoegen</a>
    <a href="#" class="button-like">Label printen</a>
    <a href="#" class="button-like">Bevestiging e-mail versturen</a>
    <button style="background:#f66;color:#fff;">Verwijderen</button>
</div>

</body>
</html>