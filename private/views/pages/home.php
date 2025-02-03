<?php
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
AuthController::checkAuth();

// Check if the user is logged in
if (!($_SESSION['logged_in'])) {
    // Redirect to login page if not logged in
    header("Location: /login");
    exit;
}

require_once 'db.php';

/**
 * Returns the number of items that match a given (Dutch) tag from the DB.
 * 
 * @param string $tag The Dutch tag value as stored in the 'horloges' table (e.g. 'Nieuw').
 * @return int        The count of records matching that tag.
 */
function getStatusCount($tag) {
    $conn = getDbConnection();
    
    // If the tag is "Alle statussen" (All statuses), select all
    if ($tag === 'Alle statussen') {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM horloges");
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM horloges WHERE Tag = :tag");
        $stmt->bindParam(':tag', $tag, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    return $stmt->fetchColumn();
}

/**
 * Array that maps the Dutch tag (used in the DB) to:
 * - 'label': The English text we display in the UI
 * - 'class': The CSS class name
 */
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Check if this is the "Add New" form
  if (isset($_POST['add_new'])) {
      // Collect form fields
      $bedrijfsnaam   = $_POST['Bedrijfsnaam']       ?? '';
      $adres          = $_POST['Adres']             ?? '';
      $address         = $_POST['Address']             ?? '';
      $merk           = $_POST['Merk']              ?? '';
      $model          = $_POST['Model']             ?? '';
      $serienummer    = $_POST['Serienummer']       ?? '';
      $debit          = $_POST['Debit']             ?? '';
      $repairCustomer = $_POST['RepairCustomer']    ?? '';
      $maxCosts       = $_POST['MaxCosts']          ?? 0;
      $warrenty       = $_POST['Warrenty']          ?? null;
      $warrentyBool   = isset($_POST['WarrentyBool']) ? 1 : 0;
      $emailSent      = isset($_POST['EmailSent'])    ? 1 : 0;
      $emailSentOn    = !empty($_POST['EmailSentOn']) ? $_POST['EmailSentOn'] : null;
      $klacht         = $_POST['Klacht']            ?? '';
      $opmerkingen    = $_POST['Opmerkingen']       ?? '';

      // Optionally set a default status/tag for new entries:
      $defaultTag = 'Nieuw'; // Or something else, e.g. 'In bewerking'

      // Prepare INSERT statement
      $insertStmt = $conn->prepare("
          INSERT INTO horloges
              (Bedrijfsnaam, Adres, Address, Merk, Model, Serienummer,
               Debit, RepairCustomer, MaxCosts, WarrentyBool,
               Warrenty, EmailSent, EmailSentOn, Klacht,
               Opmerkingen, Tag, created_at)
          VALUES
              (:bedrijfsnaam, :adres, :address,  :merk, :model, :serienummer,
               :debit, :repairCustomer, :maxCosts, :warrentyBool,
               :warrenty, :emailSent, :emailSentOn, :klacht,
               :opmerkingen, :tag, NOW())
      ");

      // Bind parameters
      $insertSuccess = $insertStmt->execute([
          ':bedrijfsnaam'   => $bedrijfsnaam,
          ':adres'          => $adres,
          ':address'          => $address,
          ':merk'           => $merk,
          ':model'          => $model,
          ':serienummer'    => $serienummer,
          ':debit'          => $debit,
          ':repairCustomer' => $repairCustomer,
          ':maxCosts'       => $maxCosts,
          ':warrentyBool'   => $warrentyBool,
          ':warrenty'       => $warrenty,
          ':emailSent'      => $emailSent,
          ':emailSentOn'    => $emailSentOn,
          ':klacht'         => $klacht,
          ':opmerkingen'    => $opmerkingen,
          ':tag'            => $defaultTag
      ]);

      // Check if the INSERT was successful
      if ($insertSuccess) {
          $_SESSION['success'] = "New repair entry added successfully.";
      } else {
          $_SESSION['error'] = "Error adding new entry.";
      }

      // Redirect to whichever page shows your list of entries:
      header("Location: /home");
      exit;
  }
}
?>

<style>
  /* Add Form Backdrop */
.add-form-backdrop {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
}

/* Main Add Form Container */
#addFormContainer {
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

/* Form Elements */
#addFormContainer h2 {
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
.edit-form-col input[type="email"],
.edit-form-col input[type="date"],
.edit-form-col input[type="number"],
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

/* Buttons */
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

/* Toggle Switch */
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

/* Close Button */
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

</style>

<div id="addFormContainer" style="display: none;">
    <button type="button" class="close-edit-button" onclick="toggleAddForm()">&times;</button>
    <h2>Add New Repair</h2>
    
    <form method="POST" action="">
        <!-- Hidden input to distinguish between add vs. edit submissions -->
        <input type="hidden" name="add_new" value="1">
        
        <div class="edit-form-row">
            <div class="edit-form-col">
                <label for="Bedrijfsnaam_add">Jeweler Name</label>
                <input type="text" name="Bedrijfsnaam" id="Bedrijfsnaam_add" value="">
            </div>
            <div class="edit-form-col">
                <label for="Adres_add">Email Address</label>
                <input type="email" name="Adres" id="Adres_add" value="">
            </div>
        </div>

        <div class="edit-form-row">
            <div class="edit-form-col">
                <label for="Merk_add">Brand</label>
                <input type="text" name="Merk" id="Merk_add" value="">
            </div>
            <div class="edit-form-col">
                <label for="Model_add">Model</label>
                <input type="text" name="Model" id="Model_add" value="">
            </div>
        </div>

        <div class="edit-form-row">
            <div class="edit-form-col">
                <label for="Serienummer_add">Serial Number</label>
                <input type="text" name="Serienummer" id="Serienummer_add" value="">
            </div>
            <div class="edit-form-col">
                <label for="Warrenty_add">Warranty Date</label>
                <input type="date" name="Warrenty" id="Warrenty_add" value="">
            </div>
        </div>

        <div class="edit-form-row">
            <div class="edit-form-col">
                <label for="Klacht_add">Customer Complaint</label>
                <textarea name="Klacht" id="Klacht_add" rows="4"></textarea>
            </div>
            <div class="edit-form-col">
                <label for="Opmerkingen_add">Internal Notes</label>
                <textarea name="Opmerkingen" id="Opmerkingen_add" rows="4"></textarea>
            </div>
        </div>

        <div class="edit-form-row">
            <div class="edit-form-col">
                <label for="Debit_add">Debit Number</label>
                <input type="text" name="Debit" id="Debit_add" value="">
            </div>
            <div class="edit-form-col">
                <label for="RepairCustomer_add">Repair Customer Number</label>
                <input type="text" name="RepairCustomer" id="RepairCustomer_add" value="">
            </div>
        </div>

        <div class="edit-form-row">
            <div class="edit-form-col">
                <label for="MaxCosts_add">Max Costs (€)</label>
                <input type="number" name="MaxCosts" id="MaxCosts_add" step="0.01" value="0.00">
            </div>
            <div class="edit-form-col">
                <label>Under Warranty?</label>
                <div class="toggle-switch">
                    <input type="checkbox" name="WarrentyBool" id="WarrentyBool_add">
                    <label for="WarrentyBool_add"></label>
                </div>
            </div>
        </div>

        <div class="edit-form-row">
            <div class="edit-form-col">
                <label>Email Sent</label>
                <div class="toggle-switch">
                    <input type="checkbox" name="EmailSent" id="EmailSent_add" onchange="toggleEmailDateAdd(this)">
                    <label for="EmailSent_add"></label>
                </div>
            </div>
            <div class="edit-form-col" id="emailDateContainerAdd" style="display: none;">
                <label for="EmailSentOn_add">Email Sent Date</label>
                <input type="date" name="EmailSentOn" id="EmailSentOn_add" value="">
            </div>
            <div class="edit-form-row">
                <div class="edit-form-col">
                    <label for="Address_add">Address (Street, Postal Code, City)</label>
                    <textarea name="Address" id="Address_add" rows="2" placeholder="Enter full address..."></textarea>
                </div>
            </div>
        </div>

        <div class="edit-form-buttons">
            <button type="button" class="cancel-edit-button" onclick="toggleAddForm()">Cancel</button>
            <button type="submit" class="save-changes-button">Save New Entry</button>
        </div>
    </form>
</div>

<main style="padding: 1rem;">

  <!-- Top status boxes -->
  <div class="status-boxes" style="display: flex; flex-wrap: wrap; gap: 0 2rem; margin-bottom: 1rem;">
    <?php foreach ($statusTags as $dutchTag => $info): ?>
      <?php 
        // We'll display English but pass the Dutch tag to getStatusCount
        $englishLabel = $info['label']; 
        $cssClass     = $info['class']; 
      ?>
      <div class="status-box <?= $cssClass ?>" onclick="filterWatches('<?= $cssClass ?>')">
        <!-- Show the English label -->
        <div class="status-title"><?= htmlspecialchars($englishLabel) ?></div>
        <!-- getStatusCount still expects the Dutch tag from the DB -->
        <div class="status-count"><?= getStatusCount($dutchTag) ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Row with Add button, search bar, filter buttons -->
  <div class="actions" style="
    display: flex; 
    gap: 0.5rem;
    margin-bottom: 1rem;
    margin-top: 0.5rem;
    padding-left: 0rem;
  ">
    <!-- Left side: Add button -->
    <button 
      style="background-color: #28b97b; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 4px;"
      onclick="toggleAddForm()"
    >
      <i class="fa fa-check-circle"></i> Add
    </button>

    <!-- Right side: search + filter buttons -->
    <div style="display: flex; align-items: center; gap: 0.5rem; margin-left: 11.5rem;">
      <input 
        type="text" 
        placeholder="Search for..." 
        id="search-input"
        style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; width: 450px;"
      />
      <button 
        style="background-color: #153c63; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 4px; font-weight: 300;"
        id="filter-button"
      >
        <i class="fa fa-search-plus"></i> Filter
      </button>
      <button 
        style="background-color: #153c63; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 4px; font-weight: 300;"
        id="remove-filter-button"
      >
        Remove filter
      </button>
    </div>
  </div>

  <!-- Table-like filter row (just placeholders, no actual filter code) -->
  <div class="filter-box">
    <div class="filter-item">
      <input type="text" id="repair-filter" placeholder="Repair number..." />
      <i class="fa fa-arrows-alt-v"></i>
    </div>
    <div class="filter-item">
      <input type="text" id="jeweler-filter" placeholder="Jeweler..." />
      <i class="fa fa-arrows-alt-v"></i>
    </div>
    <div class="filter-item">
      <input type="text" id="city-filter" placeholder="City..." />
      <i class="fa fa-arrows-alt-v"></i>
    </div>
    <div class="filter-item">
      <input type="text" id="brand-filter" placeholder="Brand..." />
      <i class="fa fa-arrows-alt-v"></i>
    </div>
    <div class="filter-item">
      <input type="text" id="model-filter" placeholder="Model..." />
      <i class="fa fa-arrows-alt-v"></i>
    </div>
    <div class="filter-item">
      <input type="text" id="serial-filter" placeholder="Serial number..." />
      <i class="fa fa-arrows-alt-v"></i>
    </div>
  </div>


  <?php
  // STILL use 'horloges' and 'ReparatieNummer' for the actual DB query
  $conn = getDbConnection();
  $stmt = $conn->prepare("SELECT * FROM horloges ORDER BY ReparatieNummer DESC");
  $stmt->execute();
  $horloges = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // We must still map Dutch DB tags to the correct CSS class.
  // That is the same array we used above, but we only need
  // the 'class' part now. 
  // Note: we won't try to re-translate them here because
  // we store the raw DB value in $horloge['Tag'].
  $tagClasses = [
      'Nieuw'            => 'nieuw-box',
      'Alle'             => 'alle-box', // not usually used in DB
      'Teruggestuurd'    => 'terug-box',
      'Reparatie klaar'  => 'repa-box',
      'Geannuleerd'      => 'annu-box',
      'Leverancier'      => 'leverancier-box',
      'In de wacht'      => 'wacht-box',
      'Beoordelen'       => 'beoordeel-box',
      'Kosten akkoord'   => 'kosten-box',
      'Toestemming'      => 'toest-box',
      'Inspectie'        => 'inspec-box',
      'In bewerking'     => 'bewerk-box'
  ];
  ?>

  <!-- Watch List (Dutch DB fields, English display text) -->
  <div class="horloge-list">
      <?php foreach ($horloges as $horloge): ?>
          <?php 
              // The raw Dutch tag from DB
              $tag = $horloge['Tag'] ?? ''; 
              // The CSS class for the item (or blank if not found)
              $itemClass = $tagClasses[$tag] ?? ''; 
          ?>
          <div 
            class="horloge-item <?= $itemClass ?>" 
            onclick="location.href='/edit/<?= $horloge['ReparatieNummer'] ?>'"
            style="cursor: pointer;"
          >
              <!-- Repair number + created_at (Dutch field name, English label) -->
              <div class="horloge-repair">
                  <span class="horloge-number" style="font-weight: bold;">
                      #<?= htmlspecialchars($horloge['ReparatieNummer']) ?>
                  </span>
                  <span class="horloge-date" style="font-weight: lighter; color: #808080;">
                      <!-- Use English 'Unknown' if not set -->
                      <?= htmlspecialchars($horloge['created_at'] ?? 'Unknown') ?>
                  </span>
              </div>

              <!-- Company name + Address (DB fields in Dutch, display in English) -->
              <div class="horloge-company">
                  <span><?= htmlspecialchars($horloge['Bedrijfsnaam']) ?></span>
                  <span><?= htmlspecialchars($horloge['Adres']) ?></span>
                  <span><?= htmlspecialchars($horloge['Address']) ?></span>
              </div>

              <!-- Brand -->
              <div class="horloge-brand">
                  <span><?= htmlspecialchars($horloge['Merk']) ?></span>
              </div>

              <!-- Model -->
              <div class="horloge-model">
                  <span><?= htmlspecialchars($horloge['Model']) ?></span>
              </div>

              <!-- Serial number -->
              <div class="horloge-serial">
                  <span>SN: <?= htmlspecialchars($horloge['Serienummer']) ?></span>
              </div>

              <!-- Tag (we show the raw Dutch from DB or we can do a quick translation here as well) -->
              <div class="horloge-tag">
                  <?php 
                    // Quick translation: if we want to display English instead
                    // we can re-use our $statusTags array above
                    $englishText = $statusTags[$tag]['label'] ?? $tag; 
                  ?>
                  <span class="tag-label <?= $itemClass ?>">
                      <?= htmlspecialchars($englishText) ?>
                  </span>
              </div>
          </div>
      <?php endforeach; ?>
  </div>

  <!-- JavaScript for filtering by CSS class -->
  <script>
    function filterWatches(tagClass) {
      let watches = document.querySelectorAll('.horloge-item');

      watches.forEach(watch => {
        if (tagClass === 'alle-box') {
          watch.style.display = 'flex'; // Show all items
        } else if (watch.classList.contains(tagClass)) {
          watch.style.display = 'flex'; // Show matching items
        } else {
          watch.style.display = 'none'; // Hide non-matching items
        }
      });
    }
  </script>

<script>
  // Function to apply the search filter
  function applySearchFilter() {
    const searchValue = document.getElementById("search-input").value.toLowerCase();
    const watches = document.querySelectorAll(".horloge-item");

    watches.forEach(watch => {
      // Get the text content of the watch item, convert to lowercase
      const watchText = watch.textContent.toLowerCase();

      // If the watch text contains the search value, show it; otherwise, hide it.
      if (watchText.includes(searchValue)) {
        watch.style.display = "flex";
      } else {
        watch.style.display = "none";
      }
    });
  }

  // Function to remove the filter (show everything again)
  function removeSearchFilter() {
    // Clear the search input
    document.getElementById("search-input").value = "";
    
    // Show all watch items
    const watches = document.querySelectorAll(".horloge-item");
    watches.forEach(watch => {
      watch.style.display = "flex";
    });
  }

  // Attach the functions to the buttons
  document.getElementById("filter-button").addEventListener("click", applySearchFilter);
  document.getElementById("remove-filter-button").addEventListener("click", removeSearchFilter);

  // (Optional) If you'd like to filter as the user types:
  // document.getElementById("search-input").addEventListener("keyup", applySearchFilter);
</script>

<script>
  function applyFilters() {
    // 1. Get the global "Search for..." value (lowercase)
    const globalSearch = document.getElementById("search-input").value.toLowerCase().trim();

    // 2. Get each column filter’s value (lowercase)
    const repairVal  = document.getElementById("repair-filter").value.toLowerCase().trim();
    const jewelerVal = document.getElementById("jeweler-filter").value.toLowerCase().trim();
    const cityVal    = document.getElementById("city-filter").value.toLowerCase().trim();
    const brandVal   = document.getElementById("brand-filter").value.toLowerCase().trim();
    const modelVal   = document.getElementById("model-filter").value.toLowerCase().trim();
    const serialVal  = document.getElementById("serial-filter").value.toLowerCase().trim();

    // 3. Loop over each watch item
    const watches = document.querySelectorAll(".horloge-item");
    watches.forEach(watch => {
      // Grab the relevant text from the watch item, in lowercase
      // a) For the global search, we can use the entire watch text:
      const watchText = watch.textContent.toLowerCase();

      // b) For column-specific filters, we can be more targeted:
      //    1) Repair number is inside ".horloge-repair .horloge-number"
      const repairNumberEl = watch.querySelector(".horloge-repair .horloge-number");
      const repairText     = repairNumberEl ? repairNumberEl.textContent.toLowerCase() : "";

      //    2) Jeweler (Bedrijfsnaam) is the first <span> under ".horloge-company"
      //       City (Adres) is the second <span> under ".horloge-company"
      const companySpans = watch.querySelectorAll(".horloge-company span");
      const jewelerText  = companySpans.length > 0 ? companySpans[0].textContent.toLowerCase() : "";
      const cityText     = companySpans.length > 2 ? companySpans[2].textContent.toLowerCase() : "";

      //    3) Brand
      const brandEl  = watch.querySelector(".horloge-brand");
      const brandTxt = brandEl ? brandEl.textContent.toLowerCase() : "";

      //    4) Model
      const modelEl  = watch.querySelector(".horloge-model");
      const modelTxt = modelEl ? modelEl.textContent.toLowerCase() : "";

      //    5) Serial Number
      const serialEl  = watch.querySelector(".horloge-serial");
      const serialTxt = serialEl ? serialEl.textContent.toLowerCase() : "";

      // Start by assuming this watch matches
      let isMatch = true;

      // 4. Check the global search filter (if not empty)
      if (globalSearch && !watchText.includes(globalSearch)) {
        isMatch = false;
      }

      // 5. Check each column filter if not empty
      if (isMatch && repairVal && !repairText.includes(repairVal)) {
        isMatch = false;
      }
      if (isMatch && jewelerVal && !jewelerText.includes(jewelerVal)) {
        isMatch = false;
      }
      if (isMatch && cityVal && !cityText.includes(cityVal)) {
        isMatch = false;
      }
      if (isMatch && brandVal && !brandTxt.includes(brandVal)) {
        isMatch = false;
      }
      if (isMatch && modelVal && !modelTxt.includes(modelVal)) {
        isMatch = false;
      }
      if (isMatch && serialVal && !serialTxt.includes(serialVal)) {
        isMatch = false;
      }

      // 6. Finally, show/hide based on `isMatch`
      watch.style.display = isMatch ? "flex" : "none";
    });
  }

  // Clears all filters (global and column-based) and shows everything
  function removeAllFilters() {
    document.getElementById("search-input").value    = "";
    document.getElementById("repair-filter").value   = "";
    document.getElementById("jeweler-filter").value  = "";
    document.getElementById("city-filter").value     = "";
    document.getElementById("brand-filter").value    = "";
    document.getElementById("model-filter").value    = "";
    document.getElementById("serial-filter").value   = "";

    // Show all items
    const watches = document.querySelectorAll(".horloge-item");
    watches.forEach(watch => {
      watch.style.display = "flex";
    });
  }

  // Attach your existing "Filter" / "Remove filter" buttons
  document.getElementById("filter-button").addEventListener("click", applyFilters);
  document.getElementById("remove-filter-button").addEventListener("click", removeAllFilters);

  // For the column filters, we only filter WHEN the user presses Enter
  const columnInputs = [
    "repair-filter", "jeweler-filter", "city-filter",
    "brand-filter", "model-filter", "serial-filter"
  ];

  columnInputs.forEach(id => {
    const inputEl = document.getElementById(id);
    inputEl.addEventListener("keypress", event => {
      if (event.key === "Enter") {
        applyFilters();
      }
    });
  });

  // (Optional) If you’d also like "Search for..." to filter immediately on Enter:
  // document.getElementById("search-input").addEventListener("keypress", event => {
  //   if (event.key === "Enter") {
  //     applyFilters();
  //   }
  // });
</script>

<script>
  function toggleAddForm() {
    const addFormContainer = document.getElementById("addFormContainer");
    if (addFormContainer.style.display === "none" || addFormContainer.style.display === "") {
      addFormContainer.style.display = "block";
    } else {
      addFormContainer.style.display = "none";
    }
  }

  // If you want an "Add" button to show this form:
  // <button onclick="toggleAddForm()">Add</button>

  function toggleEmailDateAdd(checkbox) {
    const container = document.getElementById("emailDateContainerAdd");
    container.style.display = checkbox.checked ? "block" : "none";
  }
</script>

</main>
</body>
</html>
