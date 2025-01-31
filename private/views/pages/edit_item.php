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
    <style>
        .edit-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-section {
            margin-bottom: 2rem;
            padding: 1rem;
            border: 1px solid #eee;
            border-radius: 4px;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        input, textarea, select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .button-group {
            margin-top: 2rem;
            text-align: right;
        }

        button {
            padding: 0.75rem 1.5rem;
            margin-left: 1rem;
        }
    </style>
</head>
<body>

    <div class="edit-container">
        <?php if(isset($success)): ?>
            <div class="success-message"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>

        <h1>Reparatie #<?= htmlspecialchars($item['ReparatieNummer']) ?></h1>

        <form method="POST">
            <div class="form-section">
                <h2>Productinformatie</h2>
                <div class="form-group">
                    <label>Bedrijfsnaam:</label>
                    <input type="text" name="Bedrijfsnaam" value="<?= htmlspecialchars($item['Bedrijfsnaam']) ?>">
                </div>
                
                <div class="form-group">
                    <label>Merk:</label>
                    <input type="text" name="Merk" value="<?= htmlspecialchars($item['Merk']) ?>">
                </div>

                <div class="form-group">
                    <label>Model:</label>
                    <input type="text" name="Model" value="<?= htmlspecialchars($item['Model']) ?>">
                </div>
            </div>

            <div class="form-section">
                <h2>Reparatie-informatie</h2>
                <div class="form-group">
                    <label>Status:</label>
                    <select name="Tag">
                        <?php foreach ($tagClasses as $tag => $class): ?>
                            <option value="<?= $tag ?>" <?= $item['Tag'] === $tag ? 'selected' : '' ?>>
                                <?= $tag ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Klachtomschrijving:</label>
                    <textarea name="Klacht"><?= htmlspecialchars($item['Klacht']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Interne opmerkingen:</label>
                    <textarea name="Opmerkingen"><?= htmlspecialchars($item['Opmerkingen']) ?></textarea>
                </div>
            </div>

            <div class="button-group">
                <button type="button" onclick="window.location.href='/home'">Annuleren</button>
                <button type="submit" class="primary">Opslaan</button>
            </div>
        </form>
    </div>
</body>
</html>