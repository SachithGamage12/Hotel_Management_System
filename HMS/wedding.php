<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Wedding Bookings Form</title>
  <link rel="icon" type="image/avif" href="images/logo.avif">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #f5f7ff 0%, #ffe4f3 100%);
      font-family: 'Inter', sans-serif;
      overflow-x: hidden;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    .form-container {
      background: linear-gradient(145deg, rgba(255, 255, 255, 0.9), rgba(245, 243, 255, 0.9));
      background-image: url('data:image/svg+xml,%3Csvg width="30" height="30" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="%23c4b1ff" fill-opacity="0.15" fill-rule="evenodd"%3E%3Ccircle cx="5" cy="5" r="5"/%3E%3Ccircle cx="25" cy="25" r="5"%3E%3C/g%3E%3C/svg%3E');
      border-radius: 1.5rem;
      box-shadow: 0 10px 40px rgba(124, 58, 237, 0.15), 0 6px 20px rgba(0, 0, 0, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.3);
      backdrop-filter: blur(20px);
      padding: 3rem;
      position: relative;
      overflow: hidden;
      transition: transform 0.4s ease, box-shadow 0.4s ease;
    }
    .form-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: radial-gradient(circle at 10% 145deg, rgba(124, 58, 237, 0.15), transparent 60%);
      opacity: 0.6;
      z-index: -1;
    }
    .form-container:hover {
      transform: translateY(-8px);
      box-shadow: 0 14px 48px rgba(124, 58, 237, 0.25), 0 8px 24px rgba(0, 0, 0, 0.15);
    }
    input, select, textarea {
      font-size: 1rem;
      padding: 0.875rem;
      height: 3rem;
      border: 1px solid #d4b8ff;
      border-radius: 1rem;
      background: rgba(255, 255, 255, 0.9);
      transition: all 0.3s ease;
    }
    textarea {
      height: auto;
      min-height: 6rem;
    }
    input:focus, select:focus, textarea:focus {
      outline: none;
      border-color: #7c3aed;
      box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.25);
      transform: scale(1.02);
    }
    button {
      font-size: 1rem;
      padding: 0.75rem 1.5rem;
      border-radius: 1rem;
      transition: all 0.3s ease;
      font-weight: 500;
    }
    button:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    }
    .header {
      background: linear-gradient(to right, #5b21b6, #db2777);
      padding: 2rem 3rem;
      border-bottom: 2px solid rgba(255, 255, 255, 0.4);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(25px); }
      to { opacity: 1; transform: translateY(0); }
    }
    @keyframes pulseGlow {
      0% { transform: scale(1); box-shadow: 0 0 0 rgba(124, 58, 237, 0); }
      50% { transform: scale(1.05); box-shadow: 0 0 12px rgba(124, 58, 237, 0.4); }
      100% { transform: scale(1); box-shadow: 0 0 0 rgba(124, 58, 237, 0); }
    }
    .animate-fadeInUp {
      animation: fadeInUp 0.8s ease-out forwards;
    }
    .animate-pulseGlow {
      animation: pulseGlow 2s infinite ease-in-out;
    }
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      align-items: center;
      justify-content: center;
      z-index: 1000;
    }
    .modal-content {
      background: linear-gradient(145deg, #ffffff, #faf8ff);
      padding: 2rem;
      border-radius: 1.25rem;
      width: 90%;
      max-width: 450px;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
      transform: scale(0.7);
      opacity: 0;
      transition: transform 0.5s ease, opacity 0.5s ease;
    }
    .modal.active .modal-content {
      transform: scale(1);
      opacity: 1;
    }
    .modal-content input {
      margin-bottom: 1rem;
      font-size: 1rem;
      padding: 0.875rem;
      height: 3rem;
      border-radius: 1rem;
    }
    .close-btn {
      position: absolute;
      top: 1rem;
      right: 1rem;
      cursor: pointer;
      font-size: 1.5rem;
      color: #7c3aed;
      transition: color 0.2s ease-in-out, transform 0.2s ease-in-out;
    }
    .close-btn:hover {
      color: #db2777;
      transform: scale(1.3);
    }
    .form-group {
      opacity: 0;
      transform: translateY(20px);
    }
    .form-group.animate {
      animation: fadeInUp 0.7s ease-out forwards;
      animation-delay: calc(0.15s * var(--index));
    }
    h1, h2, h3 {
      font-family: 'Playfair Display', serif;
      font-weight: 700;
    }
    h1 {
      font-size: 2.5rem !important;
    }
    h2 {
      font-size: 2rem !important;
    }
    h3 {
      font-size: 1.5rem !important;
    }
    .time-group, .time-range-group {
      display: flex;
      gap: 0.5rem;
      align-items: center;
    }
    .time-group select, .time-range-group select {
      width: 5rem;
      height: 3rem;
      border-radius: 0.5rem;
    }
    .time-range-group {
      display: flex;
      gap: 1rem;
      align-items: center;
    }
    .time-range-group .time-subgroup {
      display: flex;
      gap: 0.5rem;
      align-items: center;
    }
    @media (max-width: 1280px) {
      .form-container {
        max-width: 90%;
      }
      .header h1 {
        font-size: 2rem !important;
      }
      h2 {
        font-size: 1.75rem !important;
      }
    }
    @media (max-width: 1024px) {
      .form-container {
        max-width: 95%;
        padding: 2rem;
      }
      .grid-cols-2 {
        grid-template-columns: 1fr;
      }
      button[type="submit"], button[type="button"] {
        padding: 0.875rem;
      }
      .time-range-group {
        flex-direction: column;
        align-items: flex-start;
      }
    }
    .sidebar {
      position: fixed;
      top: 0;
      left: -300px;
      width: 300px;
      height: 100%;
      background: linear-gradient(145deg, #ffffff, #f0eaff);
      box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
      transition: left 0.3s ease;
      z-index: 2000;
      padding: 2rem;
      overflow-y: auto;
    }
    .sidebar.active {
      left: 0;
    }
    .sidebar-toggle {
      position: fixed;
      top: 20px;
      left: 20px;
      background: #7c3aed;
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      cursor: pointer;
      z-index: 2100;
      transition: all 0.3s ease;
    }
    .sidebar-toggle:hover {
      background: #db2777;
      transform: translateY(-2px);
    }
    .sidebar button {
      width: 100%;
      margin-bottom: 0.5rem;
      background: #7c3aed;
      color: white;
      text-align: left;
      padding: 0.75rem;
      border-radius: 0.5rem;
      transition: all 0.3s ease;
    }
    .sidebar button:hover {
      background: #db2777;
      transform: translateX(5px);
    }
  </style>
</head>
<body class="min-h-screen flex flex-col">
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <h3 class="text-purple-900 mb-6 text-xl font-bold">Add New Items</h3>
    <button onclick="openAddModal('venues', 'Add New Venue')">Add Venue</button>
    <button onclick="openAddModal('menus', 'Add New Menu')">Add Menu</button>
    <button onclick="openAddModal('function_types', 'Add New Function')">Add Function</button>
    <button onclick="openAddModal('music_types', 'Add New Music Type')">Add Music Type</button>
    <button onclick="openAddModal('wedding_cars', 'Add New Wedding Car')">Add Wedding Car</button>
    <button onclick="openAddModal('jayamangala_gathas', 'Add New Jayamangala Gatha')">Add Jayamangala Gatha</button>
    <button onclick="openAddModal('wes_dances', 'Add New Wes Dance')">Add Wes Dance</button>
    <button onclick="openAddModal('ashtakas', 'Add New Ashtaka')">Add Ashtaka</button>
    <button onclick="openAddModal('welcome_songs', 'Add New Welcome Song')">Add Welcome Song</button>
    <button onclick="openAddModal('indian_dhols', 'Add New Indian Dhol')">Add Indian Dhol</button>
    <button onclick="openAddModal('floor_dances', 'Add New Floor Dance')">Add Floor Dance</button>
  </div>
  <div class="sidebar-toggle" id="sidebar-toggle">☰ Menu</div>

  <!-- Modal for Adding New Items -->
  <div id="add-field-modal" class="modal">
    <div class="modal-content relative">
      <span class="close-btn" onclick="closeAddModal()">×</span>
      <h3 id="add-field-title" class="text-purple-900 mb-4"></h3>
      <form id="add-field-form">
        <input type="text" id="new_field_value" name="new_value" placeholder="Enter New Value" class="w-full mb-4">
        <input type="hidden" id="field-type" name="field_type">
        <button type="submit" class="w-full bg-gradient-to-r from-purple-700 to-pink-700 text-white rounded-lg p-3 hover:from-purple-800 hover:to-pink-800 font-semibold">Add Item</button>
      </form>
    </div>
  </div>

  <div class="w-full min-h-screen flex flex-col">
    <!-- Header with Title and Logo -->
    <div class="header flex justify-between items-center">
      <img src="images/logo.avif" alt="Hotel Logo" class="h-14 rounded-full animate-pulseGlow">
      <h1 class="text-white tracking-wide">Wedding Bliss Bookings</h1>
        <div class="w-14">
        <a href="Backoffice.php">
          <button style="background-color:rgb(255, 247, 0); color: #000000; padding: 6px 10px; border-radius: 8px; font-weight: 500; transition: background-color 0.3s ease; cursor: pointer;" onmouseover="this.style.backgroundColor='#e0e0e0'" onmouseout="this.style.backgroundColor='#ffffff'">Home</button>
        </a>
      </div>
    </div>
    <!-- Main Content: Form Container -->
    <div class="flex flex-1 items-center justify-center p-8">
      <div class="form-container w-full max-w-5xl p-10 flex flex-col gap-10 animate-fadeInUp">
        <!-- First Page -->
        <div id="page1" class="form-page">
          <h2 class="text-center text-transparent bg-clip-text bg-gradient-to-r from-purple-700 to-pink-700">Plan Your Dream Wedding</h2>
          <form id="booking-form" class="grid grid-cols-2 gap-8">
            <div class="form-group" style="--index: 1">
              <label for="name" class="text-sm font-medium text-purple-900">Full Name</label>
              <input type="text" id="name" name="full_name" placeholder="Enter Full Name" class="w-full">
            </div>
            <div class="form-group" style="--index: 2">
              <label class="text-sm font-medium text-purple-900">Contact Numbers</label>
              <div class="flex gap-4">
                <input type="tel" id="contact_no1" name="contact_no1" placeholder="Primary Contact Number" class="w-full">
                <input type="tel" id="contact_no2" name="contact_no2" placeholder="Secondary Contact Number" class="w-full">
              </div>
            </div>
            <div class="form-group" style="--index: 3">
              <label for="booking_date" class="text-sm font-medium text-purple-900">Booking Date</label>
              <input type="date" id="booking_date" name="booking_date" class="w-full">
            </div>
            <div class="form-group" style="--index: 4">
              <label class="text-sm font-medium text-purple-900">Time Slot</label>
              <div class="flex gap-4">
                <div class="time-group">
                  <input type="time" id="time_from" name="time_from" min="01:00" max="12:59" step="60" class="w-full">
                  <select name="time_from_am_pm" class="border rounded-lg">
                    <option value="AM">AM</option>
                    <option value="PM">PM</option>
                  </select>
                </div>
                <div class="time-group">
                  <input type="time" id="time_to" name="time_to" min="01:00" max="12:59" step="60" class="w-full">
                  <select name="time_to_am_pm" class="border rounded-lg">
                    <option value="AM">AM</option>
                    <option value="PM">PM</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="form-group" style="--index: 5">
              <label for="couple_name" class="text-sm font-medium text-purple-900">Couple Name</label>
              <input type="text" id="couple_name" name="couple_name" placeholder="Enter Couple Name" class="w-full">
            </div>
            <div class="form-group col-span-2" style="--index: 6">
              <label class="text-sm font-medium text-purple-900">Addresses</label>
              <div class="flex gap-4">
                <input type="text" id="groom_address" name="groom_address" placeholder="Groom's Address" class="w-full">
                <input type="text" id="bride_address" name="bride_address" placeholder="Bride's Address" class="w-full">
              </div>
            </div>
            <div class="form-group" style="--index: 7">
              <label for="venue" class="text-sm font-medium text-purple-900">Venue</label>
              <select id="venue" name="venue_id" class="w-full">
                <option value="" disabled selected>Select Venue</option>
              </select>
            </div>
            <div class="form-group" style="--index: 8">
              <label for="menu" class="text-sm font-medium text-purple-900">Menu Selection</label>
              <select id="menu" name="menu_id" class="w-full">
                <option value="" disabled selected>Select Menu</option>
              </select>
            </div>
            <div class="form-group col-span-2" style="--index: 9">
              <label for="function_type" class="text-sm font-medium text-purple-900">Function Type</label>
              <select id="function_type" name="function_type_id" class="w-full">
                <option value="" disabled selected>Select Function Type</option>
              </select>
            </div>
            <div class="form-group" style="--index: 10">
              <label for="day_or_night" class="text-sm font-medium text-purple-900">Day or Night</label>
              <select id="day_or_night" name="day_or_night" class="w-full">
                <option value="" disabled selected>Select Day or Night</option>
                <option value="day">Day</option>
                <option value="night">Night</option>
              </select>
            </div>
            <div class="form-group" style="--index: 11">
              <label for="no_of_pax" class="text-sm font-medium text-purple-900">Number of Pax</label>
              <input type="number" id="no_of_pax" name="no_of_pax" placeholder="Enter Number of Pax" class="w-full">
            </div>
            <div class="form-group col-span-2" style="--index: 12">
              <label class="text-sm font-medium text-purple-900">Coordinators</label>
              <div class="flex gap-4">
                <input type="text" id="floor_coordinator" name="floor_coordinator" placeholder="Floor Coordinator" class="w-full">
                <input type="text" id="drinks_coordinator" name="drinks_coordinator" placeholder="Drinks Coordinator" class="w-full">
              </div>
            </div>
            <div class="flex gap-4 col-span-2">
              <button type="button" onclick="showPage2()" class="flex-1 p-4 bg-gradient-to-r from-purple-700 to-pink-700 text-white rounded-lg hover:from-purple-800 hover:to-pink-800 font-semibold text-lg animate-pulseGlow">Proceed to Next Step</button>
            </div>
          </form>
        </div>
        <!-- Second Page -->
        <div id="page2" class="form-page hidden">
          <h2 class="text-center text-transparent bg-clip-text bg-gradient-to-r from-purple-700 to-pink-700">Additional Wedding Details</h2>
          <form id="additional-form" class="grid grid-cols-2 gap-8">
            <div class="form-group col-span-2" style="--index: 1">
              <label class="text-sm font-medium text-purple-900">Dressing</label>
              <div class="flex gap-4">
                <input type="text" id="bride_dressing" name="bride_dressing" placeholder="Bride's Dressing" class="w-full">
                <input type="text" id="groom_dressing" name="groom_dressing" placeholder="Groom's Dressing" class="w-full">
              </div>
            </div>
            <div class="form-group" style="--index: 2">
              <label for="bride_arrival_time" class="text-sm font-medium text-purple-900">Bride Arrival Time</label>
              <div class="time-group">
                <input type="time" id="bride_arrival_time" name="bride_arrival_time" min="01:00" max="12:59" step="60" class="w-full">
                <select name="bride_arrival_time_am_pm" class="border rounded-lg">
                  <option value="AM">AM</option>
                  <option value="PM">PM</option>
                </select>
              </div>
            </div>
            <div class="form-group" style="--index: 3">
              <label for="groom_arrival_time" class="text-sm font-medium text-purple-900">Groom Arrival Time</label>
              <div class="time-group">
                <input type="time" id="groom_arrival_time" name="groom_arrival_time" min="01:00" max="12:59" step="60" class="w-full">
                <select name="groom_arrival_time_am_pm" class="border rounded-lg">
                  <option value="AM">AM</option>
                  <option value="PM">PM</option>
                </select>
              </div>
            </div>
            <div class="form-group col-span-2" style="--index: 4">
              <label class="text-sm font-medium text-purple-900">Morning Tea Table Time</label>
              <div class="time-range-group">
                <div class="time-subgroup">
                  <input type="time" id="morning_tea_time_from" name="morning_tea_time_from" min="01:00" max="12:59" step="60" class="w-full">
                  <select name="morning_tea_time_from_am_pm" class="border rounded-lg">
                    <option value="AM">AM</option>
                    <option value="PM">PM</option>
                  </select>
                </div>
                <span>-</span>
                <div class="time-subgroup">
                  <input type="time" id="morning_tea_time_to" name="morning_tea_time_to" min="01:00" max="12:59" step="60" class="w-full">
                  <select name="morning_tea_time_to_am_pm" class="border rounded-lg">
                    <option value="AM">AM</option>
                    <option value="PM">PM</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="form-group" style="--index: 5">
              <label for="tea_pax" class="text-sm font-medium text-purple-900">Tea/Plain Tea (Pax)</label>
              <input type="number" id="tea_pax" name="tea_pax" placeholder="Enter Number of Pax" class="w-full">
            </div>
            <div class="form-group" style="--index: 6">
              <label for="kiribath" class="text-sm font-medium text-purple-900">Kiribath</label>
              <input type="text" id="kiribath" name="kiribath" placeholder="Enter Kiribath Details" class="w-full">
            </div>
            <div class="form-group col-span-2" style="--index: 7">
              <label class="text-sm font-medium text-purple-900">Poruwa Time and Direction</label>
              <div class="flex gap-4">
                <div class="time-range-group">
                  <div class="time-subgroup">
                    <input type="time" id="poruwa_time_from" name="poruwa_time_from" min="01:00" max="12:59" step="60" class="w-full">
                    <select name="poruwa_time_from_am_pm" class="border rounded-lg">
                      <option value="AM">AM</option>
                      <option value="PM">PM</option>
                    </select>
                  </div>
                  <span>-</span>
                  <div class="time-subgroup">
                    <input type="time" id="poruwa_time_to" name="poruwa_time_to" min="01:00" max="12:59" step="60" class="w-full">
                    <select name="poruwa_time_to_am_pm" class="border rounded-lg">
                      <option value="AM">AM</option>
                      <option value="PM">PM</option>
                    </select>
                  </div>
                </div>
                <select id="poruwa_direction" name="poruwa_direction" class="w-full">
                  <option value="" disabled selected>Select Direction</option>
                  <option value="north">North</option>
                  <option value="east">East</option>
                  <option value="south">South</option>
                  <option value="west">West</option>
                </select>
              </div>
            </div>
            <div class="form-group col-span-2" style="--index: 8">
              <label class="text-sm font-medium text-purple-900">Registration Time and Direction</label>
              <div class="flex gap-4">
                <div class="time-range-group">
                  <div class="time-subgroup">
                    <input type="time" id="registration_time_from" name="registration_time_from" min="01:00" max="12:59" step="60" class="w-full">
                    <select name="registration_time_from_am_pm" class="border rounded-lg">
                      <option value="AM">AM</option>
                      <option value="PM">PM</option>
                    </select>
                  </div>
                  <span>-</span>
                  <div class="time-subgroup">
                    <input type="time" id="registration_time_to" name="registration_time_to" min="01:00" max="12:59" step="60" class="w-full">
                    <select name="registration_time_to_am_pm" class="border rounded-lg">
                      <option value="AM">AM</option>
                      <option value="PM">PM</option>
                    </select>
                  </div>
                </div>
                <select id="registration_direction" name="registration_direction" class="w-full">
                  <option value="" disabled selected>Select Direction</option>
                  <option value="north">North</option>
                  <option value="east">East</option>
                  <option value="south">South</option>
                  <option value="west">West</option>
                </select>
              </div>
            </div>
            <div class="form-group" style="--index: 9">
              <label for="welcome_drink_time" class="text-sm font-medium text-purple-900">Welcome Drink Time</label>
              <div class="time-group">
                <input type="time" id="welcome_drink_time" name="welcome_drink_time" min="01:00" max="12:59" step="60" class="w-full">
                <select name="welcome_drink_time_am_pm" class="border rounded-lg">
                  <option value="AM">AM</option>
                  <option value="PM">PM</option>
                </select>
              </div>
            </div>
            <div class="form-group col-span-2" style="--index: 10">
              <label for="floor_table_arrangement" class="text-sm font-medium text-purple-900">Floor Table Arrangement</label>
              <textarea id="floor_table_arrangement" name="floor_table_arrangement" placeholder="Describe Floor Table Arrangement" class="w-full"></textarea>
            </div>
            <div class="flex gap-4 col-span-2">
              <button type="button" onclick="showPage1()" class="flex-1 p-4 bg-gray-500 text-white rounded-lg hover:bg-gray-600 font-semibold text-lg animate-pulseGlow">Back</button>
              <button type="button" onclick="showPage3()" class="flex-1 p-4 bg-gradient-to-r from-purple-700 to-pink-700 text-white rounded-lg hover:from-purple-800 hover:to-pink-800 font-semibold text-lg animate-pulseGlow">Proceed to Next Step</button>
            </div>
          </form>
        </div>
        <!-- Third Page -->
        <div id="page3" class="form-page hidden">
          <h2 class="text-center text-transparent bg-clip-text bg-gradient-to-r from-purple-700 to-pink-700">Event Logistics</h2>
          <form id="logistics-form" class="grid grid-cols-2 gap-8">
            <div class="form-group" style="--index: 1">
              <label for="drinks_time" class="text-sm font-medium text-purple-900">Drinks on</label>
              <div class="time-group">
                <input type="time" id="drinks_time" name="drinks_time" min="01:00" max="12:59" step="60" class="w-full">
                <select name="drinks_time_am_pm" class="border rounded-lg">
                  <option value="AM">AM</option>
                  <option value="PM">PM</option>
                </select>
              </div>
            </div>
            <div class="form-group" style="--index: 2">
              <label for="drinks_pax" class="text-sm font-medium text-purple-900">Pax</label>
              <input type="number" id="drinks_pax" name="drinks_pax" placeholder="Enter Number of Pax" class="w-full">
            </div>
            <div class="form-group" style="--index: 3">
              <label for="drink_serving" class="text-sm font-medium text-purple-900">Drink Serving</label>
              <select id="drink_serving" name="drink_serving" class="w-full">
                <option value="" disabled selected>Select Serving Type</option>
                <option value="shot">Shot</option>
                <option value="bottle">Bottle</option>
              </select>
            </div>
            <div class="form-group" style="--index: 4">
              <label for="bites_source" class="text-sm font-medium text-purple-900">Bites</label>
              <select id="bites_source" name="bites_source" class="w-full">
                <option value="" disabled selected>Select Source</option>
                <option value="other">H.G.G</option>
                <option value="customer">Customer</option>
              </select>
            </div>
            <div class="form-group col-span-2" style="--index: 5">
              <label for="bite_items" class="text-sm font-medium text-purple-900">Bite Items for Drinks</label>
              <textarea id="bite_items" name="bite_items" placeholder="Describe Bite Items" class="w-full"></textarea>
            </div>
            <div class="form-group" style="--index: 6">
              <label for="buffet_open" class="text-sm font-medium text-purple-900">Buffet Open</label>
              <div class="time-group">
                <input type="time" id="buffet_open" name="buffet_open" min="01:00" max="12:59" step="60" class="w-full">
                <select name="buffet_open_am_pm" class="border rounded-lg">
                  <option value="AM">AM</option>
                  <option value="PM">PM</option>
                </select>
              </div>
            </div>
            <div class="form-group" style="--index: 7">
              <label for="buffet_close" class="text-sm font-medium text-purple-900">Buffet Close</label>
              <div class="time-group">
                <input type="time" id="buffet_close" name="buffet_close" min="01:00" max="12:59" step="60" class="w-full">
                <select name="buffet_close_am_pm" class="border rounded-lg">
                  <option value="AM">AM</option>
                  <option value="PM">PM</option>
                </select>
              </div>
            </div>
            <div class="form-group" style="--index: 8">
              <label for="buffet_type" class="text-sm font-medium text-purple-900">Buffet</label>
              <select id="buffet_type" name="buffet_type" class="w-full">
                <option value="" disabled selected>Select Buffet Type</option>
                <option value="one_way">One Way</option>
                <option value="two_way">Two Way</option>
              </select>
            </div>
            <div class="form-group" style="--index: 9">
              <label for="ice_coffee_time" class="text-sm font-medium text-purple-900">Ice Coffee</label>
              <div class="time-group">
                <input type="time" id="ice_coffee_time" name="ice_coffee_time" min="01:00" max="12:59" step="60" class="w-full">
                <select name="ice_coffee_time_am_pm" class="border rounded-lg">
                  <option value="AM">AM</option>
                  <option value="PM">PM</option>
                </select>
              </div>
            </div>
            <div class="form-group" style="--index: 10">
              <label for="music_close_time" class="text-sm font-medium text-purple-900">Music to be Closed at</label>
              <div class="time-group">
                <input type="time" id="music_close_time" name="music_close_time" min="01:00" max="12:59" step="60" class="w-full">
                <select name="music_close_time_am_pm" class="border rounded-lg">
                  <option value="AM">AM</option>
                  <option value="PM">PM</option>
                </select>
              </div>
            </div>
            <div class="form-group" style="--index: 11">
              <label for="departure_time" class="text-sm font-medium text-purple-900">Departure Time</label>
              <div class="time-group">
                <input type="time" id="departure_time" name="departure_time" min="01:00" max="12:59" step="60" class="w-full">
                <select name="departure_time_am_pm" class="border rounded-lg">
                  <option value="AM">AM</option>
                  <option value="PM">PM</option>
                </select>
              </div>
            </div>
            <div class="form-group col-span-2" style="--index: 12">
              <label for="etc_description" class="text-sm font-medium text-purple-900">Additional Notes</label>
              <textarea id="etc_description" name="etc_description" placeholder="Additional Description" class="w-full"></textarea>
            </div>
            <div class="flex gap-4 col-span-2">
              <button type="button" onclick="showPage2()" class="flex-1 p-4 bg-gray-500 text-white rounded-lg hover:bg-gray-600 font-semibold text-lg animate-pulseGlow">Back</button>
              <button type="button" onclick="showPage4()" class="flex-1 p-4 bg-gradient-to-r from-purple-700 to-pink-700 text-white rounded-lg hover:from-purple-800 hover:to-pink-800 font-semibold text-lg animate-pulseGlow">Proceed to Next Step</button>
            </div>
          </form>
        </div>
        <!-- Fourth Page -->
        <div id="page4" class="form-page hidden">
          <h2 class="text-center text-transparent bg-clip-text bg-gradient-to-r from-purple-700 to-pink-700">Cultural and Decor Details</h2>
          <form id="cultural-decor-form" action="save_booking.php" method="POST" class="grid grid-cols-2 gap-8">
            <div class="form-group" style="--index: 1">
              <label for="music_type" class="text-sm font-medium text-purple-900">Music Type</label>
              <select id="music_type" name="music_type_id" class="w-full">
                <option value="" disabled selected>Select Music Type</option>
              </select>
            </div>
            <div class="form-group" style="--index: 2">
              <label for="wedding_car" class="text-sm font-medium text-purple-900">Wedding Car</label>
              <select id="wedding_car" name="wedding_car_id" class="w-full">
                <option value="" disabled selected>Select Wedding Car</option>
              </select>
            </div>
            <div class="form-group" style="--index: 3">
              <label for="jayamangala_gatha" class="text-sm font-medium text-purple-900">Jayamangala Gatha</label>
              <select id="jayamangala_gatha" name="jayamangala_gatha_id" class="w-full">
                <option value="" disabled selected>Select Jayamangala Gatha</option>
              </select>
            </div>
            <div class="form-group" style="--index: 4">
              <label for="wes_dance" class="text-sm font-medium text-purple-900">Wes Dance</label>
              <select id="wes_dance" name="wes_dance_id" class="w-full">
                <option value="" disabled selected>Select Wes Dance</option>
              </select>
            </div>
            <div class="form-group" style="--index: 5">
              <label for="ashtaka" class="text-sm font-medium text-purple-900">Ashtaka</label>
              <select id="ashtaka" name="ashtaka_id" class="w-full">
                <option value="" disabled selected>Select Ashtaka</option>
              </select>
            </div>
            <div class="form-group" style="--index: 6">
              <label for="welcome_song" class="text-sm font-medium text-purple-900">Welcome Song</label>
              <select id="welcome_song" name="welcome_song_id" class="w-full">
                <option value="" disabled selected>Select Welcome Song</option>
              </select>
            </div>
            <div class="form-group" style="--index: 7">
              <label for="indian_dhol" class="text-sm font-medium text-purple-900">Indian Dhol</label>
              <select id="indian_dhol" name="indian_dhol_id" class="w-full">
                <option value="" disabled selected>Select Indian Dhol</option>
              </select>
            </div>
            <div class="form-group" style="--index: 8">
              <label for="floor_dance" class="text-sm font-medium text-purple-900">Floor Dance</label>
              <select id="floor_dance" name="floor_dance_id" class="w-full">
                <option value="" disabled selected>Select Floor Dance</option>
              </select>
            </div>
            <div class="form-group col-span-2" style="--index: 9">
              <label for="head_table" class="text-sm font-medium text-purple-900">Head Table</label>
              <textarea id="head_table" name="head_table" placeholder="Describe Head Table" class="w-full"></textarea>
            </div>
            <div class="form-group" style="--index: 10">
              <label for="chair_cover" class="text-sm font-medium text-purple-900">Chair Cover</label>
              <input type="text" id="chair_cover" name="chair_cover" placeholder="Describe Chair Cover" class="w-full">
            </div>
            <div class="form-group" style="--index: 11">
              <label for="table_cloth" class="text-sm font-medium text-purple-900">Table Cloth</label>
              <input type="text" id="table_cloth" name="table_cloth" placeholder="Describe Table Cloth" class="w-full">
            </div>
            <div class="form-group" style="--index: 12">
              <label for="top_cloth" class="text-sm font-medium text-purple-900">Top Cloth</label>
              <input type="text" id="top_cloth" name="top_cloth" placeholder="Describe Top Cloth" class="w-full">
            </div>
            <div class="form-group" style="--index: 13">
              <label for="bow" class="text-sm font-medium text-purple-900">Bow</label>
              <input type="text" id="bow" name="bow" placeholder="Describe Bow" class="w-full">
            </div>
            <div class="form-group" style="--index: 14">
              <label for="napkin" class="text-sm font-medium text-purple-900">Napkin</label>
              <input type="text" id="napkin" name="napkin" placeholder="Describe Napkin" class="w-full">
            </div>
            <div class="form-group" style="--index: 15">
              <label for="vip" class="text-sm font-medium text-purple-900">VIP</label>
              <input type="text" id="vip" name="vip" placeholder="Describe VIP Arrangements" class="w-full">
            </div>
            <div class="form-group" style="--index: 16">
              <label for="changing_room_date" class="text-sm font-medium text-purple-900">Changing Room Date</label>
              <input type="date" id="changing_room_date" name="changing_room_date" class="w-full">
            </div>
            <div class="form-group" style="--index: 17">
              <label for="changing_room_number" class="text-sm font-medium text-purple-900">Changing Room Number</label>
              <input type="text" id="changing_room_number" name="changing_room_number" placeholder="Room Number" class="w-full">
            </div>
            <div class="form-group" style="--index: 18">
              <label for="honeymoon_room_date" class="text-sm font-medium text-purple-900">Honeymoon Room Date</label>
              <input type="date" id="honeymoon_room_date" name="honeymoon_room_date" class="w-full">
            </div>
            <div class="form-group" style="--index: 19">
              <label for="honeymoon_room_number" class="text-sm font-medium text-purple-900">Honeymoon Room Number</label>
              <input type="text" id="honeymoon_room_number" name="honeymoon_room_number" placeholder="Room Number" class="w-full">
            </div>
            <div class="form-group" style="--index: 20">
              <label for="dressing_room_date" class="text-sm font-medium text-purple-900">Dressing Room Date</label>
              <input type="date" id="dressing_room_date" name="dressing_room_date" class="w-full">
            </div>
            <div class="form-group" style="--index: 21">
              <label for="dressing_room_number" class="text-sm font-medium text-purple-900">Dressing Room Number</label>
              <input type="text" id="dressing_room_number" name="dressing_room_number" placeholder="Room Number" class="w-full">
            </div>
            <div class="form-group col-span-2" style="--index: 22">
              <label for="theme_color" class="text-sm font-medium text-purple-900">Theme Color</label>
              <textarea id="theme_color" name="theme_color" placeholder="Describe Theme Color" class="w-full"></textarea>
            </div>
            <div class="form-group col-span-2" style="--index: 23">
              <label for="flower_decor" class="text-sm font-medium text-purple-900">Flower Decor</label>
              <textarea id="flower_decor" name="flower_decor" placeholder="Describe Flower Decor" class="w-full"></textarea>
            </div>
            <div class="form-group col-span-2" style="--index: 24">
              <label for="car_decoration" class="text-sm font-medium text-purple-900">Car Decoration</label>
              <textarea id="car_decoration" name="car_decoration" placeholder="Describe Car Decoration" class="w-full"></textarea>
            </div>
            <div class="form-group" style="--index: 25">
              <label for="milk_fountain" class="text-sm font-medium text-purple-900">Milk Fountain</label>
              <input type="text" id="milk_fountain" name="milk_fountain" placeholder="Describe Milk Fountain" class="w-full">
            </div>
            <div class="form-group" style="--index: 26">
              <label for="champaign" class="text-sm font-medium text-purple-900">Champaign</label>
              <input type="text" id="champaign" name="champaign" placeholder="Describe Champaign" class="w-full">
            </div>
            <div class="form-group col-span-2" style="--index: 27">
              <label for="cultural_table" class="text-sm font-medium text-purple-900">Cultural Table</label>
              <textarea id="cultural_table" name="cultural_table" placeholder="Describe Cultural Table" class="w-full"></textarea>
            </div>
            <div class="form-group col-span-2" style="--index: 28">
              <label for="kiribath_structure" class="text-sm font-medium text-purple-900">Kiribath Structure</label>
              <textarea id="kiribath_structure" name="kiribath_structure" placeholder="Describe Kiribath Structure" class="w-full"></textarea>
            </div>
            <div class="form-group col-span-2" style="--index: 29">
              <label for="cake_structure" class="text-sm font-medium text-purple-900">Cake Structure</label>
              <textarea id="cake_structure" name="cake_structure" placeholder="Describe Cake Structure" class="w-full"></textarea>
            </div>
            <div class="form-group col-span-2" style="--index: 30">
              <label for="projector_screen" class="text-sm font-medium text-purple-900">Projector Screen</label>
              <textarea id="projector_screen" name="projector_screen" placeholder="Describe Projector Screen" class="w-full"></textarea>
            </div>
            <div class="form-group" style="--index: 31">
              <label for="gsky_arrival_time" class="text-sm font-medium text-purple-900">GSKY Arrival Time</label>
              <div class="time-group">
                <input type="time" id="gsky_arrival_time" name="gsky_arrival_time" min="01:00" max="12:59" step="60" class="w-full">
                <select name="gsky_arrival_time_am_pm" class="border rounded-lg">
                  <option value="AM">AM</option>
                  <option value="PM">PM</option>
                </select>
              </div>
            </div>
            <div class="form-group" style="--index: 32">
              <label for="photo_team_count" class="text-sm font-medium text-purple-900">Photo Team Count</label>
              <input type="number" id="photo_team_count" name="photo_team_count" placeholder="Enter Photo Team Count" class="w-full">
            </div>
            <div class="form-group" style="--index: 33">
              <label for="bridal_team_count" class="text-sm font-medium text-purple-900">Bridal Team Count</label>
              <input type="number" id="bridal_team_count" name="bridal_team_count" placeholder="Enter Bridal Team Count" class="w-full">
            </div>
            <div class="flex gap-4 col-span-2">
              <button type="button" onclick="showPage3()" class="flex-1 p-4 bg-gray-500 text-white rounded-lg hover:bg-gray-600 font-semibold text-lg animate-pulseGlow">Back</button>
              <button type="submit" name="submit_cultural_decor" class="flex-1 p-4 bg-gradient-to-r from-purple-700 to-pink-700 text-white rounded-lg hover:from-purple-800 hover:to-pink-800 font-semibold text-lg animate-pulseGlow">Submit Booking</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Sidebar Toggle
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('active');
    });

    // Enforce 12-hour format for all time inputs
    document.addEventListener('DOMContentLoaded', () => {
      const timeInputs = document.querySelectorAll('input[type="time"]');
      timeInputs.forEach(input => {
        input.addEventListener('change', function() {
          let [hours, minutes] = this.value.split(':').map(Number);
          if (hours > 12 || hours === 0) {
            hours = hours % 12 || 12;
            this.value = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
          }
          const amPmSelect = this.parentElement.querySelector('select');
          if (amPmSelect) {
            amPmSelect.value = hours >= 12 ? 'PM' : 'AM';
          }
        });
      });

      // Animate form groups on page load
      document.querySelectorAll('#page1 .form-group').forEach(group => {
        group.classList.add('animate');
      });

      // Initialize select options
      populateSelect('venue', 'venues');
      populateSelect('menu', 'menus');
      populateSelect('function_type', 'function_types');
      populateSelect('music_type', 'music_types');
      populateSelect('wedding_car', 'wedding_cars');
      populateSelect('jayamangala_gatha', 'jayamangala_gathas');
      populateSelect('wes_dance', 'wes_dances');
      populateSelect('ashtaka', 'ashtakas');
      populateSelect('welcome_song', 'welcome_songs');
      populateSelect('indian_dhol', 'indian_dhols');
      populateSelect('floor_dance', 'floor_dances');
    });

    // Show first page
    function showPage1() {
      document.getElementById('page2').classList.add('hidden');
      document.getElementById('page3').classList.add('hidden');
      document.getElementById('page4').classList.add('hidden');
      document.getElementById('page1').classList.remove('hidden');
      document.querySelectorAll('#page1 .form-group').forEach(group => {
        group.classList.add('animate');
      });
    }

    // Show second page
    function showPage2() {
      document.getElementById('page1').classList.add('hidden');
      document.getElementById('page3').classList.add('hidden');
      document.getElementById('page4').classList.add('hidden');
      document.getElementById('page2').classList.remove('hidden');
      document.querySelectorAll('#page2 .form-group').forEach(group => {
        group.classList.add('animate');
      });
    }

    // Show third page
    function showPage3() {
      document.getElementById('page1').classList.add('hidden');
      document.getElementById('page2').classList.add('hidden');
      document.getElementById('page4').classList.add('hidden');
      document.getElementById('page3').classList.remove('hidden');
      document.querySelectorAll('#page3 .form-group').forEach(group => {
        group.classList.add('animate');
      });
    }

    // Show fourth page
    function showPage4() {
      document.getElementById('page1').classList.add('hidden');
      document.getElementById('page2').classList.add('hidden');
      document.getElementById('page3').classList.add('hidden');
      document.getElementById('page4').classList.remove('hidden');
      document.querySelectorAll('#page4 .form-group').forEach(group => {
        group.classList.add('animate');
      });
    }

    // Open modal for adding new item
    function openAddModal(fieldType, title) {
      const modal = document.getElementById('add-field-modal');
      const modalTitle = document.getElementById('add-field-title');
      const fieldTypeInput = document.getElementById('field-type');
      const newItemInput = document.getElementById('new_field_value');
      modalTitle.textContent = title;
      fieldTypeInput.value = fieldType;
      newItemInput.value = '';
      newItemInput.setAttribute('placeholder', `Enter New ${title.replace('Add New ', '')}`);
      modal.style.display = 'flex';
      setTimeout(() => modal.classList.add('active'), 10);
    }

    // Close modal
    function closeAddModal() {
      const modal = document.getElementById('add-field-modal');
      modal.classList.remove('active');
      setTimeout(() => {
        modal.style.display = 'none';
        const newItemInput = document.getElementById('new_field_value');
        newItemInput.value = '';
        newItemInput.setAttribute('placeholder', 'Enter New Value');
      }, 500);
    }

    // Populate select options from database
    async function populateSelect(selectId, fieldType) {
      try {
        const response = await fetch(`fetch_options.php?field_type=${fieldType}`);
        if (!response.ok) throw new Error('Network response was not ok');
        const items = await response.json();
        const select = document.getElementById(selectId);
        select.innerHTML = '<option value="" disabled selected>Select Option</option>';
        items.forEach(item => {
          const option = document.createElement('option');
          option.value = item.id;
          option.textContent = item.name;
          select.appendChild(option);
        });
      } catch (error) {
        console.error(`Error populating ${selectId}:`, error);
      }
    }

    // Add item form submission
    document.getElementById('add-field-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(e.target);
      try {
        const response = await fetch('create_option.php', {
          method: 'POST',
          body: formData
        });
        const result = await response.json();
        if (result.success) {
          const fieldType = formData.get('field_type');
          const selectId = fieldType.replace(/s$/, ''); // e.g., 'venues' -> 'venue'
          const select = document.getElementById(selectId);
          const option = document.createElement('option');
          option.value = result.id;
          option.textContent = result.name;
          select.appendChild(option);
          select.value = result.id;
          closeAddModal();
          alert('Item added successfully!');
        } else {
          alert('Error adding item: ' + result.message);
        }
      } catch (error) {
        console.error('Error adding item:', error);
        alert('An error occurred while adding the item.');
      }
    });

    // Save form data
    function saveFormData() {
      const form1 = document.getElementById('booking-form');
      const form2 = document.getElementById('additional-form');
      const form3 = document.getElementById('logistics-form');
      const form4 = document.getElementById('cultural-decor-form');
      const formData = {};
      new FormData(form1).forEach((value, key) => formData[key] = value);
      new FormData(form2).forEach((value, key) => formData[key] = value);
      new FormData(form3).forEach((value, key) => formData[key] = value);
      new FormData(form4).forEach((value, key) => formData[key] = value);
      return formData;
    }

    // Handle form submission
    // Handle form submission
document.getElementById('cultural-decor-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData();
    const bookingData = saveFormData();
    formData.append('booking_data', JSON.stringify(bookingData));
    try {
        const response = await fetch('save_booking.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            // Save form data and booking reference to localStorage for booking details page
            localStorage.setItem('bookingData', JSON.stringify({
                ...bookingData,
                booking_reference: result.booking_reference
            }));
            // Open new window with booking details
            const newWindow = window.open('booking_details.html', '_blank');
            if (!newWindow) {
                alert('Please allow pop-ups to view the booking details.');
                return;
            }
            // Reset forms and return to page 1
            document.getElementById('booking-form').reset();
            document.getElementById('additional-form').reset();
            document.getElementById('logistics-form').reset();
            document.getElementById('cultural-decor-form').reset();
            // Repopulate select options
            populateSelect('venue', 'venues');
            populateSelect('menu', 'menus');
            populateSelect('function_type', 'function_types');
            populateSelect('music_type', 'music_types');
            populateSelect('wedding_car', 'wedding_cars');
            populateSelect('jayamangala_gatha', 'jayamangala_gathas');
            populateSelect('wes_dance', 'wes_dances');
            populateSelect('ashtaka', 'ashtakas');
            populateSelect('welcome_song', 'welcome_songs');
            populateSelect('indian_dhol', 'indian_dhols');
            populateSelect('floor_dance', 'floor_dances');
            showPage1();
            alert('Booking submitted successfully!');
        } else {
            alert('Error submitting booking: ' + result.message);
        }
    } catch (error) {
        console.error('Error submitting booking:', error);
        alert('An error occurred while submitting the booking.');
    }
});
  </script>
</body>
</html>