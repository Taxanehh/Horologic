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
?>
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
      <input type="text" placeholder="Repair number..." />
      <i class="fa fa-arrows-alt-v"></i>
    </div>
    <div class="filter-item">
      <input type="text" placeholder="Jeweler..." />
      <i class="fa fa-arrows-alt-v"></i>
    </div>
    <div class="filter-item">
      <input type="text" placeholder="City..." />
      <i class="fa fa-arrows-alt-v"></i>
    </div>
    <div class="filter-item">
      <input type="text" placeholder="Brand..." />
      <i class="fa fa-arrows-alt-v"></i>
    </div>
    <div class="filter-item">
      <input type="text" placeholder="Model..." />
      <i class="fa fa-arrows-alt-v"></i>
    </div>
    <div class="filter-item">
      <input type="text" placeholder="Serial number..." />
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

</main>
</body>
</html>
