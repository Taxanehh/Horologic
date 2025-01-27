<?php 

if (!($_SESSION['logged_in'])) {
  // Redirect to login page if not logged in
  header("Location: /private/views/pages/login.php");
  exit;
}

include __DIR__ . '/../layout/header.php';?>

<main style="padding: 1rem;">
  <!-- Top status boxes (Nieuw, In bewerking, Inspectie, etc.) -->
  <div class="status-boxes" style="display: flex; flex-wrap: wrap; gap: 0 2rem; margin-bottom: 1rem;">
  
    <!-- Nieuw -->
    <div class="status-box nieuw-box">
      <div class="status-title">Nieuw</div>
      <div class="status-count">leeg</div>
    </div>

    
    <!-- In bewerking -->
    <div class="status-box nieuw-box bewerk-box">
      <div class="status-title">In bewerking</div>
      <div class="status-count">leeg</div>
    </div>

    <div class="status-box nieuw-box inspec-box">
      <div class="status-title">Inspectie</div>
      <div class="status-count">leeg</div>
    </div>

    <!-- Toestemming -->
    <div class="status-box nieuw-box toest-box">
      <div class="status-title">Toestemming</div>
      <div class="status-count">leeg</div>
    </div>

    <!-- Kosten akkoord -->
    <div class="status-box nieuw-box kosten-box">
      <div class="status-title">Kosten akkoord</div>
      <div class="status-count">leeg</div>
    </div>

    <!-- Beoordelen -->
    <div class="status-box nieuw-box beoordeel-box">
      <div class="status-title">Beoordelen</div>
      <div class="status-count">leeg</div>
    </div>

    <!-- In de wacht -->
    <div class="status-box nieuw-box wacht-box">
      <div class="status-title">In de wacht</div>
      <div class="status-count">leeg</div>
    </div>

    <!-- Leverancier -->
    <div class="status-box nieuw-box leverancier-box">
      <div class="status-title">Leverancier</div>
      <div class="status-count">leeg</div>
    </div>

    <!-- Geannuleerd -->
    <div class="status-box nieuw-box annu-box">
      <div class="status-title">Geannuleerd</div>
      <div class="status-count">leeg</div>
    </div>

    <!-- Alle statussen -->
    <div class="status-box nieuw-box alle-box">
      <div class="status-title">Alle statussen</div>
      <div class="status-count">leeg</div>
    </div>
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
</main>

</body>
</html>