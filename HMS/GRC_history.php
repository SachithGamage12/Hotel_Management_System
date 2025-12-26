<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Guest Registration Card</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    body {
      background: #f5f7fa;
      font-family: 'Roboto', sans-serif;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 1rem;
    }
    .card {
      background: #ffffff;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      width: 190mm;
      min-height: 277mm;
      padding: 1.5rem;
      border: 1px solid #e0e0e0;
      display: flex;
      flex-direction: column;
      display: none; /* Initially hidden */
    }
    .header {
      text-align: center;
      padding-bottom: 1rem;
      border-bottom: 3px solid #4a90e2;
      margin-bottom: 2rem;
      background: linear-gradient(to right, #4a90e2, #9013fe);
      padding: 0.5rem;
      border-radius: 4px 4px 0 0;
    }
    h1 {
      font-size: 1.5rem;
      font-weight: 700;
      color: #ffffff;
      margin: 0;
    }
    .subheader {
      font-size: 0.9rem;
      color: #ffffff;
      margin-top: 0.25rem;
    }
    .logo {
      width: 50px;
      height: auto;
      margin-bottom: 0.5rem;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }
    tr {
      height: 2rem;
    }
    td {
      border: 1px solid #e0e0e0;
      padding: 0.75rem;
      font-size: 0.85rem;
      color: #333;
    }
    td:first-child {
      font-weight: 500;
      color: #4a90e2;
      width: 30%;
    }
    td span {
      font-size: 0.85rem;
    }
    .checkin-checkout {
      display: flex;
      justify-content: space-between;
      padding: 0.5rem;
      background: #f8f9fa;
      border: 1px solid #e0e0e0;
      margin-top: 2rem;
    }
    .checkin-checkout div {
      font-size: 0.85rem;
      color: #666;
    }
    .signatures {
      display: flex;
      justify-content: space-between;
      margin-top: 3rem;
      margin-bottom: 3rem;
    }
    .signature-box {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 45%;
    }
    .signature-line {
      border-top: 1px dotted #666;
      width: 100%;
      margin-bottom: 0.25rem;
    }
    .signature-text {
      font-size: 0.85rem;
      color: #666;
    }
    .print-button {
      background: #4a90e2;
      color: #ffffff;
      padding: 0.5rem 2rem;
      border-radius: 6px;
      font-weight: 500;
      font-size: 0.85rem;
      cursor: pointer;
      transition: background 0.3s ease;
      align-self: center;
      margin-top: 1rem;
    }
    .print-button:hover {
      background: #357abd;
    }
    .search-container {
      display: flex;
      justify-content: center;
      margin-bottom: 1rem;
      gap: 0.5rem;
    }
    .search-container input {
      padding: 0.5rem;
      border: 1px solid #e0e0e0;
      border-radius: 4px;
      font-size: 0.85rem;
      width: 200px;
    }
    .search-container button {
      background: #4a90e2;
      color: #ffffff;
      padding: 0.5rem 1rem;
      border-radius: 4px;
      font-size: 0.85rem;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    .search-container button:hover {
      background: #357abd;
    }
    .error-message {
      color: #dc2626;
      font-size: 0.85rem;
      text-align: center;
      margin-top: 0.5rem;
      display: none; /* Initially hidden */
    }
    @media print {
      @page {
        size: A4;
        margin: 10mm;
      }
      body {
        background: #ffffff;
        padding: 0;
        margin: 0;
      }
      .card {
        box-shadow: none;
        border: none;
        padding: 10mm;
        width: 190mm;
        min-height: 277mm;
        margin: 0;
        display: block; /* Ensure card is visible during print */
      }
      .header {
        border-bottom: 3px solid #000;
        background: none;
        margin-bottom: 2rem;
      }
      h1, .subheader {
        color: #000;
      }
      table td {
        border: 1px solid #000;
        color: #000;
        font-size: 10pt;
        padding: 0.75rem;
      }
      tr {
        height: 2rem;
      }
      td:first-child {
        color: #000;
      }
      .checkin-checkout {
        border: 1px solid #000;
        background: #fff;
        margin-top: 2rem;
      }
      .checkin-checkout div {
        color: #000;
      }
      .signature-line {
        border-top: 1px dotted #000;
      }
      .signature-text {
        color: #000;
      }
      .signatures {
        margin-top: 3rem;
        margin-bottom: 3rem;
      }
      .print-button, .search-container, .error-message {
        display: none; /* Hide during print */
      }
    }
    @media (max-width: 640px) {
      .card {
        width: 100%;
        min-height: auto;
        padding: 1rem;
      }
      h1 {
        font-size: 1.25rem;
      }
      .subheader {
        font-size: 0.8rem;
      }
      table td {
        font-size: 0.75rem;
        padding: 0.5rem;
      }
      tr {
        height: 1.5rem;
      }
      .checkin-checkout {
        margin-top: 1.5rem;
      }
      .signature-box {
        width: 48%;
      }
      .signature-text {
        font-size: 0.75rem;
      }
      .signatures {
        margin-top: 2rem;
        margin-bottom: 2rem;
      }
      .header {
        margin-bottom: 1.5rem;
      }
      .search-container input {
        width: 150px;
      }
    }
  </style>
</head>
<body>
  <div class="search-container" id="search-container">
    <input type="text" id="grc-search" placeholder="Enter GRC Number">
    <button onclick="searchGuestByGRC()">Search</button>
  </div>
  <div class="error-message" id="error-message"></div>
  <div class="card" id="grc-card">
    <div class="header">
      <img src="https://via.placeholder.com/50" alt="Logo" class="logo">
      <h1>Guest Registration</h1>
      
    </div>
    <table>
      <tr><td>GRC Number</td><td><span id="grc-number">-</span></td></tr>
      <tr><td>Name</td><td><span id="guest-name">-</span></td></tr>
      <tr><td>Contact Number</td><td><span id="contact-number">-</span></td></tr>
      <tr><td>Email</td><td><span id="email">-</span></td></tr>
      <tr><td>Address</td><td><span id="address">-</span></td></tr>
      <tr><td>ID Type</td><td><span id="id-type">-</span></td></tr>
      <tr><td>ID Number</td><td><span id="id-number">-</span></td></tr>
      <tr><td>Check-In</td><td><span id="check-in">-</span></td></tr>
      <tr><td>Check-Out</td><td><span id="check-out">-</span></td></tr>
      <tr><td>Rooms</td><td><span id="rooms">-</span></td></tr>
      <tr><td>Meal Plan</td><td><span id="meal-plan">-</span></td></tr>
      <tr><td>Number of Pax</td><td><span id="number-of-pax">-</span></td></tr>
      <tr><td>Remarks</td><td><span id="remarks">-</span></td></tr>
    </table>
    <div class="checkin-checkout">
      <div>CHECK IN TIME IS 2:00 PM</div>
      <div>CHECK OUT TIME IS 12:00 PM</div>
    </div>
    <div class="signatures">
      <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-text">Receptionist Signature</div>
      </div>
      <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-text">Guest Signature</div>
      </div>
    </div>
    <button class="print-button" onclick="window.print()">Print GRC</button>
  </div>
  <script>
    // Clear localStorage on page load to prevent auto-loading previous data
    localStorage.removeItem('guestData');

    async function fetchOptionName(fieldType, id) {
      if (!id) return '-';
      try {
        const response = await fetch(`api/fetch_options.php?field_type=${fieldType}`);
        if (!response.ok) throw new Error(`Network response was not ok: ${response.status}`);
        const items = await response.json();
        const item = items.find(item => item.id == id);
        return item ? item.name : '-';
      } catch (error) {
        console.error(`Error fetching ${fieldType}:`, error);
        return '-';
      }
    }

    async function displayGuestDetails(guestData) {
      const fields = {
        'grc-number': guestData.grc_number || '-',
        'guest-name': guestData.guest_name || '-',
        'contact-number': guestData.contact_number || '-',
        'email': guestData.email || '-',
        'address': guestData.address || '-',
        'id-type': guestData.id_type || '-',
        'id-number': guestData.id_number || '-',
        'check-in': guestData.check_in_date ? `${guestData.check_in_date} ${guestData.check_in_time} ${guestData.check_in_time_am_pm}` : '-',
        'check-out': guestData.check_out_date ? `${guestData.check_out_date} ${guestData.check_out_time} ${guestData.check_out_time_am_pm}` : '-',
        'number-of-pax': guestData.number_of_pax || '-',
        'remarks': guestData.remarks || '-'
      };

      // Handle multiple rooms
      let roomsDisplay = '-';
      if (guestData.rooms && Array.isArray(guestData.rooms) && guestData.rooms.length > 0) {
        const roomPromises = guestData.rooms.map(async (room) => {
          const roomTypeName = await fetchOptionName('room_types', room.room_type);
          return `Room No. ${room.room_number}, Rate: ${room.room_rate}`;
        });
        roomsDisplay = (await Promise.all(roomPromises)).join('<br>');
      }
      fields['rooms'] = roomsDisplay;

      // Fetch meal plan name
      fields['meal-plan'] = await fetchOptionName('meal_plans', guestData.meal_plan_id);

      // Update DOM with field values
      for (const [id, value] of Object.entries(fields)) {
        document.getElementById(id).innerHTML = value;
      }
    }

    async function searchGuestByGRC() {
      const grcNumber = document.getElementById('grc-search').value.trim();
      const errorMessage = document.getElementById('error-message');
      const searchContainer = document.getElementById('search-container');
      const grcCard = document.getElementById('grc-card');
      
      // Reset visibility
      errorMessage.style.display = 'none';
      errorMessage.textContent = '';
      grcCard.style.display = 'none';
      searchContainer.style.display = 'flex';

      if (!grcNumber) {
        errorMessage.textContent = 'Please enter a GRC number';
        errorMessage.style.display = 'block';
        return;
      }

      try {
        const response = await fetch(`api/fetch_guest_by_grc.php?grc_number=${encodeURIComponent(grcNumber)}`);
        if (!response.ok) throw new Error(`Network response was not ok: ${response.status}`);
        const result = await response.json();

        if (result.success) {
          await displayGuestDetails(result.data);
          searchContainer.style.display = 'none'; // Hide search
          errorMessage.style.display = 'none'; // Hide error
          grcCard.style.display = 'flex'; // Show GRC card
        } else {
          errorMessage.textContent = result.message || 'Guest not found';
          errorMessage.style.display = 'block';
          searchContainer.style.display = 'flex'; // Keep search visible
          // Clear fields
          const fields = {
            'grc-number': '-',
            'guest-name': '-',
            'contact-number': '-',
            'email': '-',
            'address': '-',
            'id-type': '-',
            'id-number': '-',
            'check-in': '-',
            'check-out': '-',
            'rooms': '-',
            'meal-plan': '-',
            'number-of-pax': '-',
            'remarks': '-'
          };
          for (const [id, value] of Object.entries(fields)) {
            document.getElementById(id).innerHTML = value;
          }
        }
      } catch (error) {
        console.error('Error fetching guest data:', error);
        errorMessage.textContent = 'An error occurred while fetching guest data';
        errorMessage.style.display = 'block';
        searchContainer.style.display = 'flex'; // Keep search visible
      }
    }
  </script>
</body>
</html>