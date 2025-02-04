<?php 
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
AuthController::checkAuth();

if (!($_SESSION['logged_in'])) {
    header("Location: /login");
    exit;
}

require_once 'db.php';

function getStatusCount($tag) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) FROM horloges WHERE Tag = :tag");
    $stmt->bindParam(':tag', $tag, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchColumn();
}

$statusTags = [
    'In bewerking' => ['label' => 'In progress', 'class' => 'bewerk-box']
];

// Get only "In bewerking" items
$conn = getDbConnection();
$stmt = $conn->prepare("SELECT * FROM horloges WHERE Tag = 'In bewerking' ORDER BY ReparatieNummer DESC");
$stmt->execute();
$horloges = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote Management</title>
    <style>
        /* Add modal styles */
        #quoteModal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            z-index: 1000;
            width: 90%;
            max-width: 600px;
        }

        #modalOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-secondary {
            background: #ccc;
            color: #333;
        }

        .btn-primary {
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <main style="padding: 1rem;">
        <!-- Status boxes -->
        <div class="status-boxes" style="display: flex; flex-wrap: wrap; gap: 0 2rem; margin-bottom: 1rem;">
            <?php foreach ($statusTags as $dutchTag => $info): ?>
            <div class="status-box <?= $info['class'] ?>">
                <div class="status-title"><?= htmlspecialchars($info['label']) ?></div>
                <div class="status-count"><?= getStatusCount($dutchTag) ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin: 2rem 0; padding: 1rem; background-color: #f0f8ff; border-radius: 8px; border: 2px solid #153c63;">
            <h2 style="margin: 0; color: #153c63; font-size: 1.5rem;">
                🢂 Select a watch to create a quote 🢀
            </h2>
            <p style="margin: 0.5rem 0 0 0; color: #2a6496; font-size: 1rem;">
                Click on any item below to view details and generate a quotation
            </p>
            <p style="margin: 0.5rem 0 0 0; color: #2a6496; font-size: 1rem;">
                Only watches labeled "In progress" are eligable for quotation
            </p>
        </div>

        <!-- Filter controls -->
        <div class="actions" style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; width: 100%;">
                <input 
                    type="text" 
                    placeholder="Search for..." 
                    id="search-input"
                    style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; width: 100%;"
                />
                <button 
                    style="background-color: #153c63; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 4px;"
                    id="filter-button"
                >
                    <i class="fa fa-search-plus"></i> Filter
                </button>
                <button 
                    style="background-color: #153c63; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 4px;"
                    id="remove-filter-button"
                >
                    Remove filter
                </button>
            </div>
        </div>

        <!-- Items list -->
        <div class="horloge-list">
            <?php foreach ($horloges as $horloge): ?>
            <div class="horloge-item bewerk-box" 
                 data-repair-id="<?= $horloge['ReparatieNummer'] ?>"
                 style="cursor: pointer;">
                <div class="horloge-repair">
                    <span class="horloge-number" style="font-weight: bold;">
                        #<?= htmlspecialchars($horloge['ReparatieNummer']) ?>
                    </span>
                    <span class="horloge-date">
                        <?= htmlspecialchars($horloge['created_at'] ?? 'Unknown') ?>
                    </span>
                </div>
                <div class="horloge-company">
                    <span><?= htmlspecialchars($horloge['Bedrijfsnaam']) ?></span>
                    <span><?= htmlspecialchars($horloge['Adres']) ?></span>
                    <span><?= htmlspecialchars($horloge['Address']) ?></span>
                </div>
                <div class="horloge-brand">
                    <span><?= htmlspecialchars($horloge['Merk']) ?></span>
                </div>
                <div class="horloge-model">
                    <span><?= htmlspecialchars($horloge['Model']) ?></span>
                </div>
                <div class="horloge-serial">
                    <span>SN: <?= htmlspecialchars($horloge['Serienummer']) ?></span>
                </div>
                <div class="horloge-tag">
                    <span class="tag-label bewerk-box">In Progress</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Quote Creation Modal -->
        <div id="quoteModal">
            <div class="modal-header">
                <h2>Create Quote</h2>
                <button class="modal-close" onclick="closeQuoteModal()">&times;</button>
            </div>
            
            <form id="quoteForm" method="post" action="/generate-quote">
                <input type="hidden" name="repair_id" id="repairIdInput">
                
                <div class="form-group">
                    <label for="valid_until">Valid Until</label>
                    <input type="date" 
                           name="valid_until" 
                           id="valid_until" 
                           class="form-control"
                           required
                           min="<?= date('Y-m-d') ?>"
                           value="<?= date('Y-m-d', strtotime('+14 days')) ?>">
                </div>
                
                <div class="form-group">
                    <label for="comments">Additional Comments</label>
                    <textarea name="comments" 
                              id="comments" 
                              rows="4"
                              class="form-control"
                              placeholder="Enter any additional comments..."></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeQuoteModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate PDF</button>
                </div>
            </form>
        </div>
        <div id="modalOverlay"></div>

        <script>
            // Quote Modal Functions
            function showQuoteModal(repairId) {
                document.getElementById('repairIdInput').value = repairId;
                document.getElementById('modalOverlay').style.display = 'block';
                document.getElementById('quoteModal').style.display = 'block';
            }

            function closeQuoteModal() {
                document.getElementById('modalOverlay').style.display = 'none';
                document.getElementById('quoteModal').style.display = 'none';
            }

            // Update click handlers for horloge items
            document.querySelectorAll('.horloge-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const repairId = this.dataset.repairId;
                    showQuoteModal(repairId);
                });
            });

            // Filter functions
            function applyFilters() {
                const globalSearch = document.getElementById("search-input").value.toLowerCase().trim();
                const repairVal = document.getElementById("repair-filter").value.toLowerCase().trim();
                const jewelerVal = document.getElementById("jeweler-filter").value.toLowerCase().trim();
                const cityVal = document.getElementById("city-filter").value.toLowerCase().trim();
                const brandVal = document.getElementById("brand-filter").value.toLowerCase().trim();
                const modelVal = document.getElementById("model-filter").value.toLowerCase().trim();
                const serialVal = document.getElementById("serial-filter").value.toLowerCase().trim();

                document.querySelectorAll('.horloge-item').forEach(watch => {
                    const watchText = watch.textContent.toLowerCase();
                    const repairNumber = watch.querySelector('.horloge-number')?.textContent.toLowerCase() || '';
                    const jeweler = watch.querySelector('.horloge-company span:nth-child(1)')?.textContent.toLowerCase() || '';
                    const address = watch.querySelector('.horloge-company span:nth-child(3)')?.textContent.toLowerCase() || '';
                    const brand = watch.querySelector('.horloge-brand')?.textContent.toLowerCase() || '';
                    const model = watch.querySelector('.horloge-model')?.textContent.toLowerCase() || '';
                    const serial = watch.querySelector('.horloge-serial')?.textContent.toLowerCase() || '';

                    const matchesGlobal = globalSearch ? watchText.includes(globalSearch) : true;
                    const matchesRepair = repairVal ? repairNumber.includes(repairVal) : true;
                    const matchesJeweler = jewelerVal ? jeweler.includes(jewelerVal) : true;
                    const matchesCity = cityVal ? address.includes(cityVal) : true;
                    const matchesBrand = brandVal ? brand.includes(brandVal) : true;
                    const matchesModel = modelVal ? model.includes(modelVal) : true;
                    const matchesSerial = serialVal ? serial.includes(serialVal) : true;

                    watch.style.display = (matchesGlobal && matchesRepair && matchesJeweler && 
                                        matchesCity && matchesBrand && matchesModel && matchesSerial) 
                                        ? 'flex' : 'none';
                });
            }

            function removeAllFilters() {
                document.getElementById("search-input").value = "";
                document.getElementById("repair-filter").value = "";
                document.getElementById("jeweler-filter").value = "";
                document.getElementById("city-filter").value = "";
                document.getElementById("brand-filter").value = "";
                document.getElementById("model-filter").value = "";
                document.getElementById("serial-filter").value = "";
                document.querySelectorAll('.horloge-item').forEach(watch => {
                    watch.style.display = 'flex';
                });
            }

            // Event listeners
            document.getElementById("filter-button").addEventListener("click", applyFilters);
            document.getElementById("remove-filter-button").addEventListener("click", removeAllFilters);

            const columnInputs = ["repair-filter", "jeweler-filter", "city-filter", 
                                "brand-filter", "model-filter", "serial-filter"];
            columnInputs.forEach(id => {
                document.getElementById(id).addEventListener("keypress", e => {
                    if (e.key === "Enter") applyFilters();
                });
            });
        </script>
    </main>
</body>
</html>