<?php require_once __DIR__ . '/../layout/header.php';

require_once __DIR__ . '/../../controllers/AuthController.php';
AuthController::checkAuth();

// Check if the user is logged in
if (!($_SESSION['logged_in'])) {
    // Redirect to login page if not logged in
    header("Location: /login");
    exit;
}

require_once 'db.php';

function getStatusCount($tag) {
    $conn = getDbConnection();
    
    if ($tag === 'Alle statussen') {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM horloges");
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM horloges WHERE Tag = :tag");
        $stmt->bindParam(':tag', $tag, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    return $stmt->fetchColumn();
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
?>
<main style="padding: 1rem;">
  <!-- Top status boxes (Nieuw, In bewerking, Inspectie, etc.) -->
  <div class="status-boxes" style="display: flex; flex-wrap: wrap; gap: 0 2rem; margin-bottom: 1rem;">
  
    <?php foreach ($statusTags as $status => $class): ?>
        <div class="status-box <?= $class ?>" onclick="filterWatches('<?= $class ?>')">
            <div class="status-title"><?= htmlspecialchars($status) ?></div>
            <div class="status-count"><?= getStatusCount($status) ?></div>
        </div>
    <?php endforeach; ?>


  <!-- Row with "Toevoegen" button, search bar, filter buttons -->
  <div class="actions" style="
    display: flex; 
    gap: 0.5rem;
    margin-bottom: 1rem;
    margin-top: 0.5rem;
    padding-left: 0rem;  /* or margin-left: 1rem; */
  ">
    <!-- Left side: Toevoegen button -->
    <button 
      style="background-color: #28b97b; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 4px;"
    >
      <i class="fa fa-check-circle"></i> Toevoegen
    </button>

    <!-- Right side: search + filter buttons -->
    <div style="display: flex; align-items: center; gap: 0.5rem; margin-left: 11.5rem;">
      <input 
        type="text" 
        placeholder="Zoek naar..." 
        style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; width: 450px;"
      />
      <button 
        style="background-color: #153c63; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 4px; font-weight: 300;"
      >
        <i class="fa fa-search-plus"></i> Filter
      </button>
      <button 
        style="background-color: #153c63; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 4px; font-weight: 300;"
      >
        Filter verwijderen
      </button>
    </div>
  </div>

  <!-- Table area (you said to ignore actual data, so just show headers) -->
  <div class="filter-box">
    <!-- Each "filter-item" is one column: input + sort icon -->
    <div class="filter-item">
      <input type="text" placeholder="Reparatienummer..." />
      <i class="fa fa-arrows-alt-v"></i>
    </div>
    <div class="filter-item">
      <input type="text" placeholder="Juwelier..." />
      <i class="fa fa-arrows-alt-v"></i>
    </div>
    <div class="filter-item">
      <input type="text" placeholder="Plaats..." />
      <i class="fa fa-arrows-alt-v"></i>
    </div>
    <div class="filter-item">
      <input type="text" placeholder="Merk..." />
      <i class="fa fa-arrows-alt-v"></i>
    </div>
    <div class="filter-item">
      <input type="text" placeholder="Model..." />
      <i class="fa fa-arrows-alt-v"></i>
    </div>
    <div class="filter-item">
      <input type="text" placeholder="Serienummer..." />
      <i class="fa fa-arrows-alt-v"></i>
    </div>
  </div>

  <?php
  // Fetch data from the database
  $conn = getDbConnection();
  $stmt = $conn->prepare("SELECT * FROM horloges ORDER BY ReparatieNummer DESC");
  $stmt->execute();
  $horloges = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Map tags to their CSS classes
  $tagClasses = [
      'Nieuw' => 'nieuw-box',
      'Alle' => 'alle-box',
      'Teruggestuurd' => 'terug-box',
      'Reparatie klaar' => 'repa-box',
      'Geannuleerd' => 'annu-box',
      'Leverancier' => 'leverancier-box',
      'In de wacht' => 'wacht-box',
      'Beoordelen' => 'beoordeel-box',
      'Kosten akkoord' => 'kosten-box',
      'Toestemming' => 'toest-box',
      'Inspectie' => 'inspec-box',
      'In bewerking' => 'bewerk-box'
  ];
  ?>

  <!-- Horloge List -->
  <div class="horloge-list">
      <?php foreach ($horloges as $horloge): ?>
          <?php $tag = $horloge['Tag'] ?? ''; ?>
          <div class="horloge-item <?= $tagClasses[$tag] ?? '' ?>" onclick="location.href='/edit/<?= $horloge['ReparatieNummer'] ?>'">
              
              <!-- ReparatieNummer + created_at -->
              <div class="horloge-repair">
                  <span class="horloge-number" style="font-weight: bold;">#<?= htmlspecialchars($horloge['ReparatieNummer']) ?></span>
                  <span class="horloge-date" style="font-weight: lighter; color: #808080;"><?= htmlspecialchars($horloge['created_at'] ?? 'Onbekend') ?></span>
              </div>

              <!-- Bedrijfsnaam + Adres -->
              <div class="horloge-company">
                  <span><?= htmlspecialchars($horloge['Bedrijfsnaam']) ?></span>
                  <span><?= htmlspecialchars($horloge['Adres']) ?></span>
              </div>

              <!-- Merk -->
              <div class="horloge-brand">
                  <span><?= htmlspecialchars($horloge['Merk']) ?></span>
              </div>

              <!-- Model -->
              <div class="horloge-model">
                  <span><?= htmlspecialchars($horloge['Model']) ?></span>
              </div>

              <!-- Serienummer -->
              <div class="horloge-serial">
                  <span>SN: <?= htmlspecialchars($horloge['Serienummer']) ?></span>
              </div>

              <!-- Tag (Color matches predefined class) -->
              <div class="horloge-tag">
                  <span class="tag-label <?= $tagClasses[$tag] ?? '' ?>"><?= htmlspecialchars($tag) ?></span>
              </div>

          </div>
      <?php endforeach; ?>
  </div>


  <!-- JavaScript for Filtering -->
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
</main>

</body>
</html>