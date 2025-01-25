<?php include __DIR__ . '/../layout/header.php';?>

<main style="padding: 1rem;">
  <!-- Top status boxes (Nieuw, In bewerking, Inspectie, etc.) -->
  <div class="status-boxes" style="display: flex; flex-wrap: wrap; gap: 0 2rem; margin-bottom: 1rem;">
  
    <!-- Nieuw -->
    <div class="status-box nieuw-box">
      <div class="status-title">Nieuw</div>
      <div class="status-count">161</div>
    </div>

    
    <!-- In bewerking -->
    <div class="status-box nieuw-box bewerk-box">
      <div class="status-title">In bewerking</div>
      <div class="status-count">133</div>
    </div>

    <div class="status-box nieuw-box inspec-box">
      <div class="status-title">Inspectie</div>
      <div class="status-count">59</div>
    </div>

    <!-- Toestemming -->
    <div class="status-box nieuw-box toest-box">
      <div class="status-title">Toestemming</div>
      <div class="status-count">48</div>
    </div>

    <!-- Kosten akkoord -->
    <div class="status-box nieuw-box kosten-box">
      <div class="status-title">Kosten akkoord</div>
      <div class="status-count">???</div>
    </div>

    <!-- Beoordelen -->
    <div class="status-box nieuw-box beoordeel-box">
      <div class="status-title">Beoordelen</div>
      <div class="status-count">???</div>
    </div>

    <!-- In de wacht -->
    <div class="status-box nieuw-box wacht-box">
      <div class="status-title">In de wacht</div>
      <div class="status-count">67</div>
    </div>

    <!-- Leverancier -->
    <div class="status-box nieuw-box leverancier-box">
      <div class="status-title">Leverancier</div>
      <div class="status-count">2</div>
    </div>

    <!-- Geannuleerd -->
    <div class="status-box nieuw-box annu-box">
      <div class="status-title">Geannuleerd</div>
      <div class="status-count">115</div>
    </div>

    <!-- Reparatie klaar -->
    <div class="status-box nieuw-box repa-box">
      <div class="status-title">Reparatie klaar</div>
      <div class="status-count">799</div>
    </div>

    <!-- Teruggestuurd -->
    <div class="status-box nieuw-box terug-box">
      <div class="status-title">Teruggestuurd</div>
      <div class="status-count">8166</div>
    </div>

    <!-- Alle statussen -->
    <div class="status-box nieuw-box alle-box">
      <div class="status-title">Alle statussen</div>
      <div class="status-count">9733</div>
    </div>
  </div>


  <!-- Row with "Toevoegen" button, search bar, filter buttons -->
  <div class="actions" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
    <button style="background-color: #28a745; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 4px;">
      Toevoegen
    </button>
    <input 
      type="text" 
      placeholder="Zoek naar..." 
      style="flex: 1; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;"
    />
    <button style="background-color: #007bff; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 4px;">
      Filter
    </button>
    <button style="background-color: #dc3545; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 4px;">
      Filter verwijderen
    </button>
  </div>

  <!-- Table area (you said to ignore actual data, so just show headers) -->
  <div class="table-container" style="background-color: #fff; border: 1px solid #ddd; border-radius: 4px; overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse;">
      <thead>
        <tr style="background-color: #f1f1f1; text-align: left;">
          <th style="padding: 0.75rem; border-bottom: 1px solid #ddd;">Reparatienummer</th>
          <th style="padding: 0.75rem; border-bottom: 1px solid #ddd;">Juwelier</th>
          <th style="padding: 0.75rem; border-bottom: 1px solid #ddd;">Plaats</th>
          <th style="padding: 0.75rem; border-bottom: 1px solid #ddd;">Merk</th>
          <th style="padding: 0.75rem; border-bottom: 1px solid #ddd;">Model</th>
          <th style="padding: 0.75rem; border-bottom: 1px solid #ddd;">Serienummer</th>
          <th style="padding: 0.75rem; border-bottom: 1px solid #ddd;">Reparatienummer</th>
          <th style="padding: 0.75rem; border-bottom: 1px solid #ddd;">Horlogemaker</th>
          <th style="padding: 0.75rem; border-bottom: 1px solid #ddd;">Status</th>
        </tr>
      </thead>
      <tbody>
        <!-- Rows would go here, but you said to ignore the data for now -->
      </tbody>
    </table>
  </div>
</main>

</body>
</html>