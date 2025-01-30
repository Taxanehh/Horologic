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

?>
<main style="padding: 1rem;">
  <!-- Top status boxes (Nieuw, In bewerking, Inspectie, etc.) -->
  <div class="status-boxes" style="display: flex; flex-wrap: wrap; gap: 0 2rem; margin-bottom: 1rem;">
  
    <!-- Nieuw -->
    <div class="status-box nieuw-box">
      <div class="status-title">Nieuw</div>
      <div class="status-count"><?php echo getStatusCount('Nieuw'); ?></div>
    </div>

    <!-- In bewerking -->
    <div class="status-box nieuw-box bewerk-box">
      <div class="status-title">In bewerking</div>
      <div class="status-count"><?php echo getStatusCount('In bewerking'); ?></div>
    </div>

    <!-- Inspectie -->
    <div class="status-box nieuw-box inspec-box">
      <div class="status-title">Inspectie</div>
      <div class="status-count"><?php echo getStatusCount('Inspectie'); ?></div>
    </div>

    <!-- Toestemming -->
    <div class="status-box nieuw-box toest-box">
      <div class="status-title">Toestemming</div>
      <div class="status-count"><?php echo getStatusCount('Toestemming'); ?></div>
    </div>

    <!-- Kosten akkoord -->
    <div class="status-box nieuw-box kosten-box">
      <div class="status-title">Kosten akkoord</div>
      <div class="status-count"><?php echo getStatusCount('Kosten akkoord'); ?></div>
    </div>

    <!-- Beoordelen -->
    <div class="status-box nieuw-box beoordeel-box">
      <div class="status-title">Beoordelen</div>
      <div class="status-count"><?php echo getStatusCount('Beoordelen'); ?></div>
    </div>

    <!-- In de wacht -->
    <div class="status-box nieuw-box wacht-box">
      <div class="status-title">In de wacht</div>
      <div class="status-count"><?php echo getStatusCount('In de wacht'); ?></div>
    </div>

    <!-- Leverancier -->
    <div class="status-box nieuw-box leverancier-box">
      <div class="status-title">Leverancier</div>
      <div class="status-count"><?php echo getStatusCount('Leverancier'); ?></div>
    </div>

    <!-- Geannuleerd -->
    <div class="status-box nieuw-box annu-box">
      <div class="status-title">Geannuleerd</div>
      <div class="status-count"><?php echo getStatusCount('Geannuleerd'); ?></div>
    </div>

    <!-- Reparatie klaar -->
    <div class="status-box nieuw-box repa-box">
      <div class="status-title">Reparatie klaar</div>
      <div class="status-count"><?php echo getStatusCount('Reparatie klaar'); ?></div>
    </div>

    <!-- Teruggestuurd -->
    <div class="status-box nieuw-box terug-box">
      <div class="status-title">Teruggestuurd</div>
      <div class="status-count"><?php echo getStatusCount('Teruggestuurd'); ?></div>
    </div>

    <!-- Alle statussen -->
    <div class="status-box nieuw-box alle-box">
      <div class="status-title">Alle statussen</div>
      <div class="status-count"><?php echo getStatusCount('Alle statussen'); ?></div>
    </div>


  <!-- Row with "Toevoegen" button, search bar, filter buttons -->
  <div class="actions" style="
    display: flex; 
    gap: 0.5rem;
    margin-bottom: 1rem;
    padding-left: 1rem;  /* or margin-left: 1rem; */
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
        style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; width: 200px;"
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
    <div class="filter-item">
      <input type="text" placeholder="Reparatienummer..." />
      <i class="fa fa-arrows-alt-v"></i>
    </div>
    <div class="filter-item">
      <input type="text" placeholder="Horlogemaker..." />
      <i class="fa fa-arrows-alt-v"></i>
    </div>
  </div>

  <?php
  // Fetch data from the database
  $conn = getDbConnection();
  $stmt = $conn->prepare("SELECT * FROM horloges");
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
          <div class="horloge-item <?= $tagClasses[$tag] ?? '' ?>">
              <div class="horloge-left">
                  <span class="horloge-number">#<?= htmlspecialchars($horloge['ReparatieNummer']) ?></span>
                  <span class="horloge-brand"><?= htmlspecialchars($horloge['Merk']) ?></span>
              </div>
              <div class="horloge-details">
                  <span class="horloge-model"><?= htmlspecialchars($horloge['Model']) ?></span>
                  <span class="horloge-serial">SN: <?= htmlspecialchars($horloge['Serienummer']) ?></span>
                  <span class="horloge-company"><?= htmlspecialchars($horloge['Bedrijfsnaam']) ?></span>
              </div>
              <div class="horloge-tag">
                  <span class="tag-label"><?= htmlspecialchars($tag) ?></span>
              </div>
          </div>
      <?php endforeach; ?>
  </div>
</main>

</body>
</html>