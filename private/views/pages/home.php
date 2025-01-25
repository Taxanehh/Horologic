<?php include __DIR__ . '/../layout/header.php';?>

<main style="padding: 1rem;">
  <!-- Top status boxes (Nieuw, In bewerking, Inspectie, etc.) -->
  <div class="status-boxes" style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
    <!-- Example single box; repeat for each status -->
    <div style="
      flex: 0 1 200px; 
      background-color: #ffffff; 
      border: 1px solid #ddd; 
      border-radius: 4px; 
      padding: 1rem; 
      text-align: center;
    ">
      <div style="font-weight: bold;">Nieuw</div>
      <div style="font-size: 1.4rem; color: #666;">161</div>
    </div>

    <div style="
      flex: 0 1 200px;
      background-color: #ffffff;
      border: 1px solid #ddd;
      border-radius: 4px;
      padding: 1rem;
      text-align: center;
    ">
      <div style="font-weight: bold;">In bewerking</div>
      <div style="font-size: 1.4rem; color: #666;">133</div>
    </div>

    <div style="
      flex: 0 1 200px;
      background-color: #ffffff;
      border: 1px solid #ddd;
      border-radius: 4px;
      padding: 1rem;
      text-align: center;
    ">
      <div style="font-weight: bold;">Inspectie</div>
      <div style="font-size: 1.4rem; color: #666;">59</div>
    </div>

    <!-- Repeat for all other boxes: Toestemming, Kosten akkoord, etc. -->
    <!-- ... -->
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