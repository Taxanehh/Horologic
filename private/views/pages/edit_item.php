<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ... Existing headers to prevent caching ...
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

// Get item ID from URL
if (!isset($_GET['url'])) {
    die("Item ID ontbreekt");
}
$pathParts = explode('/', $_GET['url']);
$reparatieNummer = end($pathParts);
if (!is_numeric($reparatieNummer)) {
    die("Ongeldig reparatienummer");
}

// Fetch item data
$conn = getDbConnection();
$stmt = $conn->prepare("SELECT * FROM horloges WHERE ReparatieNummer = ?");
$stmt->execute([$reparatieNummer]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$item) {
    die("Item niet gevonden");
}

// Prepare success/error messages
$success = $_SESSION['success'] ?? '';
$error   = $_SESSION['error']   ?? '';
unset($_SESSION['success'], $_SESSION['error']);
session_write_close();

// Status tags mapping
$statusTags = [
    'Nieuw'            => ['label' => 'New',             'class' => 'nieuw-box'],
    'In bewerking'     => ['label' => 'In progress',     'class' => 'bewerk-box'],
    'Inspectie'        => ['label' => 'Inspection',      'class' => 'inspec-box'],
    'Toestemming'      => ['label' => 'Permission',      'class' => 'toest-box'],
    'Kosten akkoord'   => ['label' => 'Cost approved',   'class' => 'kosten-box'],
    'Beoordelen'       => ['label' => 'Review',          'class' => 'beoordeel-box'],
    'In de wacht'      => ['label' => 'On hold',         'class' => 'wacht-box'],
    'Leverancier'      => ['label' => 'Supplier',        'class' => 'leverancier-box'],
    'Geannuleerd'      => ['label' => 'Cancelled',       'class' => 'annu-box'],
    'Reparatie klaar'  => ['label' => 'Repair complete', 'class' => 'repa-box'],
    'Teruggestuurd'    => ['label' => 'Returned',        'class' => 'terug-box'],
    'Alle statussen'   => ['label' => 'All statuses',    'class' => 'alle-box']
];

$currentTag  = $item['Tag'] ?? '';
$statusInfo  = $statusTags[$currentTag] ?? ['label' => $currentTag, 'class' => 'default-box'];
$statusLabel = $statusInfo['label'];
$statusClass = $statusInfo['class'];

// Handle POST actions (status update OR edit-form update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Status update
    if (isset($_POST['new_status'])) {
        $newStatus = $_POST['new_status'];
        $updateStmt = $conn->prepare("UPDATE horloges SET Tag = ? WHERE ReparatieNummer = ?");
        if ($updateStmt->execute([$newStatus, $reparatieNummer])) {
            $_SESSION['success'] = "Status updated to $newStatus";
        } else {
            $_SESSION['error'] = "Error updating status";
        }
        header("Location: /edit/" . $reparatieNummer);
        exit;
    }
    // 2) Edit all fields
    if (isset($_POST['edit_all'])) {
        $bedrijfsnaam = $_POST['Bedrijfsnaam'] ?? '';
        $adres = $_POST['Adres'] ?? '';
        $merk = $_POST['Merk'] ?? '';
        $model = $_POST['Model'] ?? '';
        $serienummer = $_POST['Serienummer'] ?? '';
        $debit = $_POST['Debit'] ?? '';
        $repairCustomer = $_POST['RepairCustomer'] ?? '';
        $maxCosts = $_POST['MaxCosts'] ?? 0;
        $warrenty = $_POST['Warrenty'] ?? null;
        $warrentyBool = isset($_POST['WarrentyBool']) ? 1 : 0;
        $emailSent = isset($_POST['EmailSent']) ? 1 : 0;
        $emailSentOn = !empty($_POST['EmailSentOn']) ? $_POST['EmailSentOn'] : null;
        $klacht = $_POST['Klacht'] ?? '';
        $opmerkingen = $_POST['Opmerkingen'] ?? '';
    
        $updateAll = $conn->prepare("
            UPDATE horloges 
            SET Bedrijfsnaam = ?, 
                Adres = ?,
                Merk = ?,
                Model = ?,
                Serienummer = ?,
                Debit = ?,
                RepairCustomer = ?,
                MaxCosts = ?,
                WarrentyBool   = ?,
                Warrenty = ?,
                EmailSent = ?,
                EmailSentOn = ?,
                Klacht = ?,
                Opmerkingen = ?
            WHERE ReparatieNummer = ?
        ");
        $successUpdate = $updateAll->execute([
            $bedrijfsnaam,
            $adres,
            $merk,
            $model,
            $serienummer,
            $debit,
            $repairCustomer,
            $maxCosts,
            $warrentyBool,
            $warrenty,
            $emailSent,
            $emailSentOn,
            $klacht,
            $opmerkingen,
            $reparatieNummer
        ]);

        if ($successUpdate) {
            $_SESSION['success'] = "Item updated successfully.";
        } else {
            $_SESSION['error'] = "Error updating item.";
        }
        header("Location: /edit/" . $reparatieNummer);
        exit;
    }
}

$warrentyBool = isset($_POST['WarrentyBool']) ? 1 : 0;

if (isset($_POST['delete'])) {
    $deleteStmt = $conn->prepare("DELETE FROM horloges WHERE ReparatieNummer = ?");
    if ($deleteStmt->execute([$reparatieNummer])) {
        $_SESSION['success'] = "Entry deleted successfully";
        header("Location: /home");  // Redirect to overview after deletion
        exit;
    } else {
        $_SESSION['error'] = "Error deleting entry";
        header("Location: /edit/" . $reparatieNummer);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Reparatie #<?= htmlspecialchars($item['ReparatieNummer']) ?></title>
    <link rel="stylesheet" href="/css/globals.css">

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
            background: #f1f1f1;
        }

        .edit-form-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
        }

        #editFormContainer {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 2rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            width: 70%;
            max-width: 800px;
            z-index: 10000;
        }

        .close-edit-button {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }

        .main-box {
            width: 1550px;
            background: #fff;
            margin: 0 auto;
            border: 2px solid rgba(0, 0, 0, 0.125);
            box-sizing: border-box;
            position: relative;
            padding: 1rem;
            margin-right: 1500px;
            top: -10px;
            right: 30px;
        }

        .container {
            width: 100%;
            max-width: 1850px;
            margin: 1rem auto;
            padding: 1rem;
            background: #f1f1f1;
            position: relative;
        }
        /* Top big box */
        .top-box {
            background: #fff;
            padding: 1.2rem;
            border-radius: 6px;
            position: relative;
            top: -10px;
            right: 10px;
        }
        .top-box .rep-number {
            font-size: 1rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .top-box .light-line {
            color: #353535;
            margin-bottom: 0.25rem;
            font-size: 0.8rem;
        }
        .top-box .product-info {
            margin-top: 0.8rem;
            padding-top: 0.8rem;
            border-top: 1px solid #ddd;
            color: #353535;
            background: #fafafa;
            height: 210px;
            line-height: 20px;
            font-size: 0.8rem;
        }

        /* Middle row: complaint (left) and internal notes (right) */
        .middle-row {
            display: flex;
            gap: 1rem;
            font-size: 0.8rem;
        }
        .middle-column {
            flex: 1;
            background: #fff;
            padding: 1rem;
            border: 1px solid #eee;
            border-radius: 6px;
            height: 170px;
        }
        .middle-column h3 {
            margin-top: 0;
        }
        .extra-notes {
            font-size: 0.9rem;
            color: #888;
            margin-top: 0.5rem;
        }

        /* Right sidebar / buttons */
        .sidebar {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 285px;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            padding: 0.5rem;
            z-index: 100;
            position: absolute;
            right: 1rem;
            top: 1rem;
        }

        .status-box {
            width: 285px;
            height: 43px;
            padding: 0.75rem 1rem;
            font-weight: 600;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            font-size: 0.95rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .status-box:hover {
            transform: scale(0.98);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .status-box:active {
            transform: scale(0.95);
        }

        /* Color variations for status */
        .status-box.nieuw-box        { background: #fffd54; }
        .status-box.repa-box         { background: #92fa5b; }
        .status-box.annu-box         { background: #ea3223; }
        .status-box.leverancier-box  { background: #8d43f6; }
        .status-box.wacht-box        { background: #cadfb8; }
        .status-box.beoordeel-box    { background: #91fcfd; }
        .status-box.kosten-box       { background: #dfdfdf; }
        .status-box.toest-box        { background: #000;     color: #fff; }
        .status-box.inspec-box       { background: #f1a0f9; }
        .status-box.bewerk-box       { background: #f19d38; }
        .status-box.default-box      { background: #f0f0f0; }

        .sidebar button {
            width: 285px;
            height: 43px;
            padding: 0.75rem 1.5rem;
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            color: #2c3e50;
            font-size: 0.9rem;
            text-align: left;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
        }
        .sidebar button:hover {
            background: #f1f1f1;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }

        .sidebar .danger {
            background: #dc3545;
            color: white;
            border-color: #dc3545;
            margin-top: 1rem;
            justify-content: center;
            font-weight: 500;
        }
        .sidebar .danger:hover {
            background: #c82333;
            border-color: #bd2130;
        }

        /* Repair products table */
        .repair-products-box {
            margin-bottom: 1rem;
            position: relative;
            right: 28px;
        }
        .bottom-box {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 2rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .bottom-box table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }
        .bottom-box th {
            background: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid #e0e0e0;
        }
        .bottom-box td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            color: #4a4a4a;
        }
        .bottom-box tr:last-child td {
            border-bottom: none;
        }
        .total-line {
            text-align: right;
            font-weight: 700;
            color: #2c3e50;
            padding: 1rem;
            margin-top: 1rem;
            background: #f8f9fa;
            border-radius: 4px;
        }

        /* Activity Log & Pictures */
        .lowest-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin: 2rem 0;
            position: relative;
            right: 28px;
        }
        .bottom-box2, .bottom-box3 {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 1.5rem;
            min-height: 250px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .bottom-box2 h3, .bottom-box3 h3 {
            margin: 0 0 1.5rem 0;
            color: #2c3e50;
            font-size: 1.1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f0f0;
        }
        .bottom-box2 p, .bottom-box3 p {
            color: #95a5a6;
            font-style: italic;
            text-align: center;
            margin: 2rem 0;
        }

        /* Back button */
        .bottom-nav {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }
        .back-button {
            background: #f8f9fa;
            color: #2c3e50;
            border: 1px solid #e0e0e0;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .back-button:hover {
            background: #f1f1f1;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        /* Status dropdown styling */
        .status-container {
            position: relative;
            z-index: 100;
        }
        .status-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 5px);
            left: 0;
            width: 285px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            margin-top: 0.5rem;
            max-height: 400px;
            overflow-y: auto;
            padding: 8px;
            z-index: 1000;
            animation: slideIn 0.2s ease forwards;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .status-option {
            padding: 12px 16px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            border-radius: 6px;
            margin: 4px 0;
            font-size: 0.9rem;
            color: #2c3e50;
        }
        .status-option:hover {
            background: rgba(0,0,0,0.03);
            transform: translateX(4px);
        }
        .status-color-indicator {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            flex-shrink: 0;
            border: 2px solid rgba(0,0,0,0.1);
        }
        .status-option.nieuw-box .status-color-indicator        { background: #fffd54; }
        .status-option.repa-box .status-color-indicator         { background: #92fa5b; }
        .status-option.annu-box .status-color-indicator         { background: #ea3223; }
        .status-option.leverancier-box .status-color-indicator  { background: #8d43f6; }
        .status-option.wacht-box .status-color-indicator        { background: #cadfb8; }
        .status-option.beoordeel-box .status-color-indicator    { background: #91fcfd; }
        .status-option.kosten-box .status-color-indicator       { background: #dfdfdf; }
        .status-option.toest-box .status-color-indicator        { background: #000; }
        .status-option.inspec-box .status-color-indicator       { background: #f1a0f9; }
        .status-option.bewerk-box .status-color-indicator       { background: #f19d38; }
        .status-option.default-box .status-color-indicator      { background: #f0f0f0; }
        .current-status .status-label::after {
            content: "✓";
            margin-left: 8px;
            color: #2c3e50;
            font-weight: bold;
        }

        /* Success/error messages */
        .success-message {
            color: green;
            margin-bottom: 1rem;
        }
        .error-message {
            color: red;
            margin-bottom: 1rem;
        }

        /* --- EDIT FORM --- */
        #editFormContainer {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 2rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            width: 70%;
            max-width: 800px;
            z-index: 10000;
        }
        #editFormContainer h2 {
            margin-top: 0;
            margin-bottom: 1rem;
        }
        .edit-form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .edit-form-col {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .edit-form-col label {
            font-weight: bold;
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }
        .edit-form-col input[type="text"], 
        .edit-form-col textarea {
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 0.9rem;
            width: 100%;
        }
        .edit-form-col textarea {
            height: 60px;
            resize: vertical;
        }
        .edit-form-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        .save-changes-button {
            background: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 0.5rem 1rem;
            cursor: pointer;
        }
        .save-changes-button:hover {
            background: #218838;
        }
        .cancel-edit-button {
            background: #e0e0e0;
            color: #333;
            border: none;
            border-radius: 4px;
            padding: 0.5rem 1rem;
            cursor: pointer;
        }
        .cancel-edit-button:hover {
            background: #ccc;
        }

        .close-edit-button {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }

        
        .toggle-switch {
                position: relative;
                display: inline-block;
                width: 60px;
                height: 34px;
            }

            .toggle-switch input {
                opacity: 0;
                width: 0;
                height: 0;
            }

            .toggle-switch label {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ccc;
                transition: .4s;
                border-radius: 34px;
            }

            .toggle-switch label:before {
                position: absolute;
                content: "";
                height: 26px;
                width: 26px;
                left: 4px;
                bottom: 4px;
                background-color: white;
                transition: .4s;
                border-radius: 50%;
            }

            .toggle-switch input:checked + label {
                background-color: #4CAF50;
            }

            .toggle-switch input:checked + label:before {
                transform: translateX(26px);
            }
        
    </style>
</head>
<body>

<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="delete" value="1">
</form>

<div class="edit-form-backdrop" id="editFormBackdrop" onclick="toggleEditForm()"></div>

<!-- Edit Form Container -->
<div id="editFormContainer">
    <button type="button" class="close-edit-button" onclick="toggleEditForm()">&times;</button>
    <h2>Edit Repair Info</h2>
    <form method="POST" action="">
        <input type="hidden" name="edit_all" value="1">
        <div class="edit-form-row">
            <div class="edit-form-col">
                <label for="Bedrijfsnaam">Jeweler Name</label>
                <input type="text" name="Bedrijfsnaam" id="Bedrijfsnaam" 
                       value="<?= htmlspecialchars($item['Bedrijfsnaam'] ?? '') ?>">
            </div>
            <div class="edit-form-col">
                <label for="Adres">Email Address</label>
                <input type="email" name="Adres" id="Adres"
                       value="<?= htmlspecialchars($item['Adres'] ?? '') ?>">
            </div>
        </div>

        <div class="edit-form-row">
            <div class="edit-form-col">
                <label for="Merk">Brand</label>
                <input type="text" name="Merk" id="Merk" 
                       value="<?= htmlspecialchars($item['Merk'] ?? '') ?>">
            </div>
            <div class="edit-form-col">
                <label for="Model">Model</label>
                <input type="text" name="Model" id="Model"
                       value="<?= htmlspecialchars($item['Model'] ?? '') ?>">
            </div>
        </div>

        <div class="edit-form-row">
            <div class="edit-form-col">
                <label for="Serienummer">Serial Number</label>
                <input type="text" name="Serienummer" id="Serienummer"
                       value="<?= htmlspecialchars($item['Serienummer'] ?? '') ?>">
            </div>
            <div class="edit-form-col">
                <label for="Warrenty">Warranty Date</label>
                <input type="date" name="Warrenty" id="Warrenty"
                       value="<?= htmlspecialchars($item['Warrenty'] ?? '') ?>">
            </div>
        </div>

        <div class="edit-form-row">
            <div class="edit-form-col">
                <label for="Klacht">Customer Complaint</label>
                <textarea name="Klacht" id="Klacht" rows="4"><?= htmlspecialchars($item['Klacht'] ?? '') ?></textarea>
            </div>
            <div class="edit-form-col">
                <label for="Opmerkingen">Internal Notes</label>
                <textarea name="Opmerkingen" id="Opmerkingen" rows="4"><?= htmlspecialchars($item['Opmerkingen'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="edit-form-row">
            <div class="edit-form-col">
                <label for="Debit">Debit Number</label>
                <input type="text" name="Debit" id="Debit" 
                       value="<?= htmlspecialchars($item['Debit'] ?? '') ?>">
            </div>
            <div class="edit-form-col">
                <label for="RepairCustomer">Repair Customer Number</label>
                <input type="text" name="RepairCustomer" id="RepairCustomer"
                       value="<?= htmlspecialchars($item['RepairCustomer'] ?? '') ?>">
            </div>
        </div>

        <div class="edit-form-row">
            <div class="edit-form-col">
                <label for="MaxCosts">Max Costs (€)</label>
                <input type="number" name="MaxCosts" id="MaxCosts" step="0.01"
                       value="<?= htmlspecialchars($item['MaxCosts'] ?? '0.00') ?>">
            </div>
            <!-- For the yes/no toggle -->
                <div class="edit-form-col">
                <label>Under Warranty?</label>
                <div class="toggle-switch">
                    <input type="checkbox" name="WarrentyBool" id="WarrentyBool"
                        <?= !empty($item['WarrentyBool']) ? 'checked' : '' ?>>
                    <label for="WarrentyBool"></label>
                </div>
            </div>

        </div>

        <div class="edit-form-row">
            <div class="edit-form-col">
                <label>Email Sent</label>
                <div class="toggle-switch">
                    <input type="checkbox" name="EmailSent" id="EmailSent" 
                           <?= $item['EmailSent'] ? 'checked' : '' ?>
                           onchange="toggleEmailDate(this)">
                    <label for="EmailSent"></label>
                </div>
            </div>
            <div class="edit-form-col" id="emailDateContainer" 
                 style="<?= $item['EmailSent'] ? '' : 'display: none;' ?>">
                <label for="EmailSentOn">Email Sent Date</label>
                <input type="date" name="EmailSentOn" id="EmailSentOn"
                       value="<?= htmlspecialchars($item['EmailSentOn'] ?? '') ?>">
            </div>
        </div>

        <div class="edit-form-buttons">
            <button type="button" class="cancel-edit-button" onclick="toggleEditForm()">Cancel</button>
            <button type="submit" class="save-changes-button">Save Changes</button>
        </div>

    </form>
</div>

<div class="container">

    <!-- Right sidebar -->
    <div class="sidebar">
        <!-- STATUS BLOCK -->
        <div class="status-container">
            <div class="status-box <?= htmlspecialchars($statusClass) ?>" onclick="toggleStatusDropdown()">
                <?= htmlspecialchars($statusLabel) ?>
            </div>
            <div class="status-dropdown" id="statusDropdown">
                <?php foreach ($statusTags as $tag => $info): ?>
                    <div class="status-option <?= $info['class'] ?> <?= $tag === $currentTag ? 'current-status' : '' ?>" 
                         onclick="updateStatus('<?= htmlspecialchars($tag) ?>')">
                        <div class="status-color-indicator"></div>
                        <span class="status-label"><?= htmlspecialchars($info['label']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Hidden form to submit new status -->
        <form id="statusForm" method="POST" style="display:none;">
            <input type="hidden" name="new_status" id="newStatusInput">
        </form>
        <script>
          function toggleStatusDropdown() {
              const dropdown = document.getElementById('statusDropdown');
              dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
          }
          function updateStatus(newStatus) {
              document.getElementById('newStatusInput').value = newStatus;
              document.getElementById('statusForm').submit();
          }
          // Close the dropdown if user clicks outside
          document.addEventListener('click', function(e) {
              if (!e.target.closest('.status-container')) {
                  document.getElementById('statusDropdown').style.display = 'none';
              }
          });
        </script>

        <!-- EDIT button: toggles the edit form -->
        <button onclick="toggleEditForm()">Edit</button>
        <button>Add pictures</button>
        <button>Add products</button>
        <button>Print label</button>
        <button>Send confirmation E-mail</button>
        <button class="danger" onclick="if(confirmDelete()) { document.getElementById('deleteForm').submit(); }">Delete</button>
    </div>

    <!-- Display success/error messages -->
    <?php if($success): ?>
        <div class="success-message"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- MAIN BOX with item info -->
    <div class="main-box">
        <div class="top-box">
            <div class="rep-number">Repair no. <?= htmlspecialchars($item['ReparatieNummer']) ?></div>
            <div class="light-line">Juweler: <?= htmlspecialchars($item['Bedrijfsnaam']) ?></div>
            <div class="light-line">Debit no.: <?= htmlspecialchars($item['Debit']) ?></div>
            <div class="light-line">Email: <?= htmlspecialchars($item['Adres']) ?></div>
            <div class="light-line">Repair no. customer: <?= htmlspecialchars($item['RepairCustomer']) ?></div>

            <div class="product-info">
                <strong>Product:</strong><br>
                Brand: <?= htmlspecialchars($item['Merk']) ?><br>
                Model: <?= htmlspecialchars($item['Model']) ?><br>
                Serial Number: <?= htmlspecialchars($item['Serienummer']) ?><br>
                Start Warrenty (YYYY/DD/MM): <?= htmlspecialchars($item['Warrenty']) ?><br>
                Max costs: <?= htmlspecialchars($item['MaxCosts']) ?><br>
                <!-- Show “Yes” if WarrentyBool == 1, else “No” -->
                Warrenty: 
                <?= ($item['WarrentyBool'] == 1) ? 'Yes' : 'No' ?>
                <br>
                E-mail Sent: 
                <?= ($item['EmailSent'] == 1) ? 'Yes' : 'No' ?>
                <br>
                E-mail sent on: <?= htmlspecialchars($item['EmailSentOn']) ?><br>
            </div>
        </div>

        <!-- Middle row: complaint and internal notes -->
        <div class="middle-row">
            <div class="middle-column">
                <h3>Complaint description</h3>
                <p><?= nl2br(htmlspecialchars($item['Klacht'])) ?></p>
            </div>
            <div class="middle-column">
                <h3>Internal notes</h3>
                <p><?= nl2br(htmlspecialchars($item['Opmerkingen'])) ?></p>
            </div>
        </div>
    </div>

    <!-- REPAIR PRODUCTS SECTION -->
    <div class="repair-products-box">
        <h3 style="font-size: 1.1rem; color: #2c3e50; margin-bottom: 1rem; margin-top: 1rem;">Repair Products</h3>
        <div class="bottom-box">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Amount</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>No repair products added</td>
                        <td>-</td>
                        <td>-</td>
                        <td>€ 0,00</td>
                    </tr>
                </tbody>
            </table>
            <div class="total-line">Total € 0,00</div>
        </div>
    </div>

    <!-- Activity Log & Pictures -->
    <div class="lowest-box">
        <div class="bottom-box2">
            <h3>Activity Log</h3>
            <div class="activity-entry">
                <p>No recent activity</p>
            </div>
        </div>
        <div class="bottom-box3">
            <h3>Pictures</h3>
            <div class="image-gallery">
                <p>No images uploaded</p>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="bottom-nav">
        <button class="back-button" onclick="location.href='/home'">
            ← Back to Overview
        </button>
    </div>

    <!-- EDIT FORM (hidden by default) -->
    <div id="editFormContainer">
        <h2>Edit Repair Info</h2>
        <form method="POST" action="">
            <!-- Hidden field to identify this form -->
            <input type="hidden" name="edit_all" value="1">

            <!-- Example input fields (adjust to your DB columns) -->
            <div class="edit-form-row">
                <div class="edit-form-col">
                    <label for="Bedrijfsnaam">Bedrijfsnaam (Juwelier):</label>
                    <input type="text" name="Bedrijfsnaam" id="Bedrijfsnaam" 
                           value="<?= htmlspecialchars($item['Bedrijfsnaam'] ?? '') ?>">
                </div>
                <div class="edit-form-col">
                    <label for="Adres">Adres / Email:</label>
                    <input type="text" name="Adres" id="Adres"
                           value="<?= htmlspecialchars($item['Adres'] ?? '') ?>">
                </div>
            </div>

            <div class="edit-form-row">
                <div class="edit-form-col">
                    <label for="Merk">Merk:</label>
                    <input type="text" name="Merk" id="Merk" 
                           value="<?= htmlspecialchars($item['Merk'] ?? '') ?>">
                </div>
                <div class="edit-form-col">
                    <label for="Model">Model:</label>
                    <input type="text" name="Model" id="Model"
                           value="<?= htmlspecialchars($item['Model'] ?? '') ?>">
                </div>
            </div>

            <div class="edit-form-row">
                <div class="edit-form-col">
                    <label for="Serienummer">Serienummer:</label>
                    <input type="text" name="Serienummer" id="Serienummer"
                           value="<?= htmlspecialchars($item['Serienummer'] ?? '') ?>">
                </div>
                <div class="edit-form-col">
                    <label for="Warrenty">Start Warrenty:</label>
                    <input type="text" name="Warrenty" id="Warrenty"
                           value="<?= htmlspecialchars($item['Warrenty'] ?? '') ?>">
                </div>
            </div>

            <div class="edit-form-row">
                <div class="edit-form-col">
                    <label for="Klacht">Klacht (Complaint):</label>
                    <textarea name="Klacht" id="Klacht"><?= htmlspecialchars($item['Klacht'] ?? '') ?></textarea>
                </div>
                <div class="edit-form-col">
                    <label for="Opmerkingen">Opmerkingen (Internal Notes):</label>
                    <textarea name="Opmerkingen" id="Opmerkingen"><?= htmlspecialchars($item['Opmerkingen'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="edit-form-buttons">
                <button type="submit" class="save-changes-button">Save Changes</button>
                <button type="button" class="cancel-edit-button" onclick="toggleEditForm()">Cancel</button>
            </div>
        </form>
    </div>

</div><!-- /.container -->

<script>
function toggleEditForm() {
    const editForm = document.getElementById('editFormContainer');
    const backdrop = document.getElementById('editFormBackdrop');
    editForm.style.display = editForm.style.display === 'block' ? 'none' : 'block';
    backdrop.style.display = backdrop.style.display === 'block' ? 'none' : 'block';
}
</script>

<script>
    function toggleEmailDate(checkbox) {
        const emailDateContainer = document.getElementById('emailDateContainer');
        emailDateContainer.style.display = checkbox.checked ? 'block' : 'none';
        if (!checkbox.checked) {
            document.getElementById('EmailSentOn').value = '';
        }
    }

    function confirmDelete() {
        return confirm('Are you sure you want to delete this entry?\nThis action cannot be undone!');
    }
</script>

</body>
</html>
