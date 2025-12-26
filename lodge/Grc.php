<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Guest Registration Card</title>
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
    input[readonly] {
      background: rgba(235, 235, 235, 0.9);
      cursor: not-allowed;
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
    .time-group {
      display: flex;
      gap: 0.5rem;
      align-items: center;
    }
    .time-group select {
      width: 5rem;
      height: 3rem;
      border-radius: 0.5rem;
    }
    .room-entry {
      display: flex;
      gap: 0.75rem;
      align-items: center;
      margin-bottom: 0.75rem;
      background: rgba(255, 255, 255, 0.95);
      padding: 0.5rem;
      border-radius: 0.75rem;
      box-shadow:0 2px 8px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .room-entry:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2);
    }
    .room-entry select, .room-entry input {
      flex: 1;
      min-width: 0;
    }
    .room-entry input[readonly] {
      flex: 1.5;
    }
    .room-entry button {
      background: #dc2626;
      color: white;
      padding: 0.5rem;
      border-radius: 0.5rem;
      width: 4rem;
      text-align: center;
    }
    .room-entry button:hover {
      background: #b91c1c;
    }
    .add-room-btn {
      background: #7c3aed;
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      transition: all 0.3s ease;
    }
    .add-room-btn:hover {
      background: #6d28d9;
      transform: translateY(-2px);
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
      .room-entry {
        flex-wrap: wrap;
      }
      .room-entry select, .room-entry input, .room-entry button {
        width: 100%;
        margin-bottom: 0.5rem;
      }
      .room-entry input[readonly] {
        width: 100%;
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
    <button onclick="openAddModal('room_types', 'Add New Room Type')">Add Room Type</button>
    <button onclick="openAddModal('meal_plans', 'Add New Meal Plan')">Add Meal Plan</button>
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
    <!-- Header -->
    <div class="header flex justify-between items-center">
      <img src="../image/Grand View lodge logo png.png" alt="Hotel Logo" class="h-14 rounded-full animate-pulseGlow">
      <h1 class="text-white tracking-wide">Guest Registration Card</h1>
      <div class="w-14">
        <a href="frontoffice.php">
          <button style="background-color: #fff700; color: #000000; padding: 6px 10px; border-radius: 8px; font-weight: 500; transition: background-color 0.3s ease; cursor: pointer;" onmouseover="this.style.backgroundColor='#e0e0e0'" onmouseout="this.style.backgroundColor='#fff700'">Back</button>
        </a>
      </div>
    </div>

    <!-- Main Content: Form Container -->
    <div class="flex flex-1 items-center justify-center p-8">
      <div class="form-container w-full max-w-5xl p-10 flex flex-col gap-10 animate-fadeInUp">
        <!-- First Page: Guest Information -->
        <div id="page1" class="form-page">
          <h2 class="text-center text-transparent bg-clip-text bg-gradient-to-r from-purple-700 to-pink-700">Guest Information</h2>
          <form id="guest-form" class="grid grid-cols-2 gap-8">
            <input type="hidden" id="grc_number" name="grc_number">
            <div class="form-group" style="--index: 1">
              <label for="guest_name" class="text-sm font-medium text-purple-900">Full Name</label>
              <input type="text" id="guest_name" name="guest_name" placeholder="Enter Full Name" class="w-full" required>
            </div>
            <div class="form-group" style="--index: 2">
              <label for="contact_number" class="text-sm font-medium text-purple-900">Contact Number</label>
              <input type="tel" id="contact_number" name="contact_number" placeholder="Enter Contact Number" class="w-full" required>
            </div>
            <div class="form-group" style="--index: 3">
              <label for="email" class="text-sm font-medium text-purple-900">Email Address</label>
              <input type="email" id="email" name="email" placeholder="Enter Email Address" class="w-full">
            </div>
            <div class="form-group" style="--index: 4">
              <label for="address" class="text-sm font-medium text-purple-900">Address</label>
              <input type="text" id="address" name="address" placeholder="Enter Address" class="w-full">
            </div>
            <div class="form-group" style="--index: 5">
              <label for="id_type" class="text-sm font-medium text-purple-900">ID Type</label>
              <select id="id_type" name="id_type" class="w-full" required>
                <option value="" disabled selected>Select ID Type</option>
                <option value="NIC">NIC</option>
                <option value="Passport">Passport</option>
              </select>
            </div>
            <div class="form-group" style="--index: 6">
              <label for="id_number" class="text-sm font-medium text-purple-900">ID Number</label>
              <input type="text" id="id_number" name="id_number" placeholder="Enter NIC or Passport Number" class="w-full" required>
            </div>
            <!-- Additional Guest Details -->
            <div class="form-group" style="--index: 7">
              <label for="other_guest_name_1" class="text-sm font-medium text-purple-900">Other Guest 1 Name</label>
              <input type="text" id="other_guest_name_1" name="other_guest_name_1" placeholder="Enter Guest 1 Name" class="w-full">
            </div>
            <div class="form-group" style="--index: 8">
              <label for="other_guest_nic_1" class="text-sm font-medium text-purple-900">Other Guest 1 NIC</label>
              <input type="text" id="other_guest_nic_1" name="other_guest_nic_1" placeholder="Enter Guest 1 NIC" class="w-full">
            </div>
            <div class="form-group" style="--index: 9">
              <label for="other_guest_name_2" class="text-sm font-medium text-purple-900">Other Guest 2 Name</label>
              <input type="text" id="other_guest_name_2" name="other_guest_name_2" placeholder="Enter Guest 2 Name" class="w-full">
            </div>
            <div class="form-group" style="--index: 10">
              <label for="other_guest_nic_2" class="text-sm font-medium text-purple-900">Other Guest 2 NIC</label>
              <input type="text" id="other_guest_nic_2" name="other_guest_nic_2" placeholder="Enter Guest 2 NIC" class="w-full">
            </div>
            <div class="form-group" style="--index: 11">
              <label for="other_guest_name_3" class="text-sm font-medium text-purple-900">Other Guest 3 Name</label>
              <input type="text" id="other_guest_name_3" name="other_guest_name_3" placeholder="Enter Guest 3 Name" class="w-full">
            </div>
            <div class="form-group" style="--index: 12">
              <label for="other_guest_nic_3" class="text-sm font-medium text-purple-900">Other Guest 3 NIC</label>
              <input type="text" id="other_guest_nic_3" name="other_guest_nic_3" placeholder="Enter Guest 3 NIC" class="w-full">
            </div>
            <div class="form-group" style="--index: 13">
              <label for="check_in_date" class="text-sm font-medium text-purple-900">Check-In Date</label>
              <input type="date" id="check_in_date" name="check_in_date" class="w-full" required>
            </div>
            <div class="form-group" style="--index: 14">
              <label for="check_in_time" class="text-sm font-medium text-purple-900">Check-In Time</label>
              <div class="time-group">
                <input type="time" id="check_in_time" name="check_in_time" min="01:00" max="12:59" step="60" class="w-full" required>
                <select name="check_in_time_am_pm" class="border rounded-lg">
                  <option value="AM">AM</option>
                  <option value="PM">PM</option>
                </select>
              </div>
            </div>
            <div class="form-group" style="--index: 15">
              <label for="check_out_date" class="text-sm font-medium text-purple-900">Check-Out Date</label>
              <input type="date" id="check_out_date" name="check_out_date" class="w-full" required>
            </div>
            <div class="form-group" style="--index: 16">
              <label for="check_out_time" class="text-sm font-medium text-purple-900">Check-Out Time</label>
              <div class="time-group">
                <input type="time" id="check_out_time" name="check_out_time" min="01:00" max="12:59" step="60" class="w-full" required>
                <select name="check_out_time_am_pm" class="border rounded-lg">
                  <option value="AM">AM</option>
                  <option value="PM">PM</option>
                </select>
              </div>
            </div>
            <div class="flex gap-4 col-span-2">
              <button type="button" onclick="showPage2()" class="flex-1 p-4 bg-gradient-to-r from-purple-700 to-pink-700 text-white rounded-lg hover:from-purple-800 hover:to-pink-800 font-semibold text-lg animate-pulseGlow">Proceed to Next Step</button>
            </div>
          </form>
        </div>

        <!-- Second Page: Room Details and Preferences -->
        <div id="page2" class="form-page hidden">
          <h2 class="text-center text-transparent bg-clip-text bg-gradient-to-r from-purple-700 to-pink-700">Room Details and Preferences</h2>
          <form id="services-form" action="../lapi/save_guest.php" method="POST" class="grid grid-cols-2 gap-8">
            <div class="form-group col-span-2" style="--index: 1">
              <label class="text-sm font-medium text-purple-900">Room Details</label>
              <div id="rooms-container">
                <div class="room-entry">
                  <select name="room_type[]" class="w-full" required>
                    <option value="" disabled selected>Select Room Type</option>
                  </select>
                  <input type="text" name="room_number[]" placeholder="Room Number (e.g., 201)" class="w-full" required>
                  <select name="ac_type[]" class="w-full" required>
                    <option value="" disabled selected>A/C Type</option>
                    <option value="AC">A/C</option>
                    <option value="Non-AC">Non-A/C</option>
                  </select>
                  <input type="text" name="room_rate[]" class="w-full" readonly placeholder="Rate (Rs.)">
                  <button type="button" onclick="removeRoom(this)" class="hidden">Remove</button>
                </div>
              </div>
              <button type="button" onclick="addRoom()" class="mt-2 add-room-btn">Add Another Room</button>
            </div>
            <div class="form-group" style="--index: 2">
              <label for="total_room_rate" class="text-sm font-medium text-purple-900">Total Room Rate (Rs.)</label>
              <input type="text" id="total_room_rate" name="total_room_rate" class="w-full" readonly>
            </div>
            <div class="form-group" style="--index: 3">
              <label for="meal_plan" class="text-sm font-medium text-purple-900">Meal Plan</label>
              <select id="meal_plan" name="meal_plan_id" class="w-full">
                <option value="" disabled selected>Select Meal Plan</option>
              </select>
            </div>
            <div class="form-group" style="--index: 4">
              <label for="number_of_pax" class="text-sm font-medium text-purple-900">Number of Pax</label>
              <input type="number" id="number_of_pax" name="number_of_pax" placeholder="Enter Number of Pax" class="w-full" min="1">
            </div>
            <div class="form-group col-span-2" style="--index: 5">
              <label for="remarks" class="text-sm font-medium text-purple-900">Remarks</label>
              <textarea id="remarks" name="remarks" placeholder="Describe Any Remarks" class="w-full"></textarea>
            </div>
            <div class="flex gap-4 col-span-2">
              <button type="button" onclick="showPage1()" class="flex-1 p-4 bg-gray-500 text-white rounded-lg hover:bg-gray-600 font-semibold text-lg animate-pulseGlow">Back</button>
              <button type="submit" name="submit_guest" class="flex-1 p-4 bg-gradient-to-r from-purple-700 to-pink-700 text-white rounded-lg hover:from-purple-800 hover:to-pink-800 font-semibold text-lg animate-pulseGlow">Submit Registration</button>
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

    // Set minimum date for check-in and check-out
    function setMinDates() {
      const today = new Date().toISOString().split('T')[0];
      const checkInDateInput = document.getElementById('check_in_date');
      const checkOutDateInput = document.getElementById('check_out_date');

      // Set min date for check-in to today
      checkInDateInput.setAttribute('min', today);

      // Update check-out min date based on check-in date
      checkInDateInput.addEventListener('change', () => {
        const checkInDate = checkInDateInput.value;
        if (checkInDate) {
          checkOutDateInput.setAttribute('min', checkInDate);
          // Reset check-out date if it's before the new check-in date
          if (checkOutDateInput.value && checkOutDateInput.value < checkInDate) {
            checkOutDateInput.value = '';
            alert('Check-out date cannot be earlier than check-in date.');
          }
        }
      });

      // Validate check-out date on change
      checkOutDateInput.addEventListener('change', () => {
        const checkInDate = checkInDateInput.value;
        const checkOutDate = checkOutDateInput.value;
        if (checkInDate && checkOutDate && checkOutDate < checkInDate) {
          checkOutDateInput.value = '';
          alert('Check-out date cannot be earlier than check-in date.');
        }
      });
    }

    // Fetch next GRC number
    async function fetchNextGRCNumber() {
      try {
        const response = await fetch('../lapi/fetch_next_grc.php');
        if (!response.ok) throw new Error(`Network response was not ok: ${response.status}`);
        const result = await response.json();
        if (result.success) {
          document.getElementById('grc_number').value = result.grc_number;
        } else {
          console.error('Error fetching GRC number:', result.message);
          document.getElementById('grc_number').value = 'Error';
        }
      } catch (error) {
        console.error('Error fetching GRC number:', error);
        document.getElementById('grc_number').value = 'Fetch error';
      }
    }

    // Enforce 12-hour format for time inputs
    document.addEventListener('DOMContentLoaded', () => {
      fetchNextGRCNumber();
      setMinDates();
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

      // Populate room type and meal plan options
      populateRoomTypes();
      populateSelect('meal_plan', 'meal_plans');

      // Add event listeners for room inputs
      updateRoomListeners();
    });

    // Add a new room entry
    function addRoom() {
      const container = document.getElementById('rooms-container');
      const entry = document.createElement('div');
      entry.className = 'room-entry';
      entry.innerHTML = `
        <select name="room_type[]" class="w-full" required>
          <option value="" disabled selected>Select Room Type</option>
        </select>
        <input type="text" name="room_number[]" placeholder="Room Number (e.g., 201)" class="w-full" required>
        <select name="ac_type[]" class="w-full" required>
          <option value="" disabled selected>A/C Type</option>
          <option value="AC">A/C</option>
          <option value="Non-AC">Non-A/C</option>
        </select>
        <input type="text" name="room_rate[]" class="w-full" readonly placeholder="Rate (Rs.)">
        <button type="button" onclick="removeRoom(this)" class="text-white">Remove</button>
      `;
      container.appendChild(entry);
      populateRoomTypes([entry.querySelector('select[name="room_type[]"]')]);
      updateRoomListeners();
      fetchRoomRates();
    }

    // Remove a room entry
    function removeRoom(button) {
      if (document.querySelectorAll('.room-entry').length > 1) {
        button.parentElement.remove();
        updateRoomListeners();
        fetchRoomRates();
      }
    }

    // Update event listeners for room inputs
    function updateRoomListeners() {
      const roomTypeInputs = document.querySelectorAll('select[name="room_type[]"]');
      const roomNumberInputs = document.querySelectorAll('input[name="room_number[]"]');
      const acTypeInputs = document.querySelectorAll('select[name="ac_type[]"]');
      const removeButtons = document.querySelectorAll('.room-entry button');
      
      roomTypeInputs.forEach(input => {
        input.removeEventListener('change', fetchRoomRates);
        input.addEventListener('change', fetchRoomRates);
      });
      roomNumberInputs.forEach(input => {
        input.removeEventListener('change', fetchRoomRates);
        input.addEventListener('change', fetchRoomRates);
      });
      acTypeInputs.forEach(input => {
        input.removeEventListener('change', fetchRoomRates);
        input.addEventListener('change', fetchRoomRates);
      });
      
      // Show/hide Remove buttons
      document.querySelectorAll('.room-entry').forEach((entry, index) => {
        const removeBtn = entry.querySelector('button');
        removeBtn.classList.toggle('hidden', index === 0);
      });
    }

    // Validate room number
    function validateRoomNumber(roomNumber) {
      return /^[0-9]+$/.test(roomNumber);
    }

    // Fetch room rates
    async function fetchRoomRates() {
      const roomTypeInputs = document.querySelectorAll('select[name="room_type[]"]');
      const roomNumberInputs = document.querySelectorAll('input[name="room_number[]"]');
      const acTypeInputs = document.querySelectorAll('select[name="ac_type[]"]');
      const roomRateInputs = document.querySelectorAll('input[name="room_rate[]"]');
      const totalRoomRateInput = document.getElementById('total_room_rate');

      const roomData = Array.from(roomNumberInputs).map((input, index) => ({
        room_type: roomTypeInputs[index]?.value || '',
        room_number: input.value,
        ac_type: acTypeInputs[index]?.value || ''
      })).filter(data => data.room_type && data.room_number && data.ac_type);

      // Clear all rate inputs
      roomRateInputs.forEach(input => input.value = '');
      totalRoomRateInput.value = '';

      if (roomData.length === 0) {
        console.log('No valid room data to fetch rates.');
        totalRoomRateInput.value = 'Please select all fields';
        return;
      }

      for (const [index, data] of roomData.entries()) {
        if (!validateRoomNumber(data.room_number)) {
          roomRateInputs[index].value = 'Invalid room number';
          totalRoomRateInput.value = 'Invalid input';
          console.log(`Invalid room number: ${data.room_number}`);
          return;
        }
        if (!['AC', 'Non-AC'].includes(data.ac_type)) {
          roomRateInputs[index].value = 'Invalid A/C type';
          totalRoomRateInput.value = 'Invalid input';
          console.log(`Invalid A/C type: ${data.ac_type}`);
          return;
        }
        if (!data.room_type) {
          roomRateInputs[index].value = 'Invalid room type';
          totalRoomRateInput.value = 'Invalid input';
          console.log(`Invalid room type: ${data.room_type}`);
          return;
        }
      }

      const roomQuery = roomData.map(data => `${data.room_type}:${data.room_number}:${data.ac_type}`).join('/');
      console.log('Sending room query:', roomQuery);

      try {
        const response = await fetch(`../lapi/fetch_room_rate.php?room_data=${encodeURIComponent(roomQuery)}`, {
          method: 'GET',
          headers: { 'Accept': 'application/json' }
        });
        console.log('Response status:', response.status);
        if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`);
        }
        const result = await response.json();
        console.log('API response:', result);

        if (result.success) {
          result.individual_rates.forEach((rate, index) => {
            roomRateInputs[index].value = rate || 'N/A';
          });
          totalRoomRateInput.value = result.total_rate || 'N/A';
        } else {
          roomRateInputs.forEach(input => input.value = result.message || 'Error fetching rate');
          totalRoomRateInput.value = result.message || 'Error fetching rate';
          console.error('API error:', result.message);
        }
      } catch (error) {
        roomRateInputs.forEach(input => input.value = 'Fetch error');
        totalRoomRateInput.value = 'Fetch error';
        console.error('Fetch error:', error.message);
      }
    }

    // Show first page
    function showPage1() {
      document.getElementById('page2').classList.add('hidden');
      document.getElementById('page1').classList.remove('hidden');
      document.querySelectorAll('#page1 .form-group').forEach(group => {
        group.classList.add('animate');
      });
    }

    // Show second page
    function showPage2() {
      document.getElementById('page1').classList.add('hidden');
      document.getElementById('page2').classList.remove('hidden');
      document.querySelectorAll('#page2 .form-group').forEach(group => {
        group.classList.add('animate');
      });
      fetchRoomRates();
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

    // Populate room type options
    async function populateRoomTypes(targetSelects = null) {
      try {
        const response = await fetch('../lapi/fetch_options.php?field_type=room_types');
        if (!response.ok) throw new Error(`Network response was not ok: ${response.status}`);
        const items = await response.json();
        console.log('Room types fetched:', items);
        const selects = targetSelects || document.querySelectorAll('select[name="room_type[]"]');
        selects.forEach(select => {
          const currentValue = select.value;
          select.innerHTML = '<option value="" disabled selected>Select Room Type</option>';
          items.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;
            select.appendChild(option);
          });
          if (currentValue) select.value = currentValue;
        });
      } catch (error) {
        console.error('Error populating room types:', error);
      }
    }

    // Populate select options (for meal plans)
    async function populateSelect(selectId, fieldType) {
      try {
        const response = await fetch(`../lapi/fetch_options.php?field_type=${fieldType}`);
        if (!response.ok) throw new Error(`Network response was not ok: ${response.status}`);
        const items = await response.json();
        console.log(`Fetched ${fieldType}:`, items);
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
        const response = await fetch('../lapi/create_option.php', {
          method: 'POST',
          body: formData
        });
        const result = await response.json();
        if (result.success) {
          const fieldType = formData.get('field_type');
          if (fieldType === 'room_types') {
            await populateRoomTypes();
          } else {
            const selectId = fieldType.replace(/s$/, '');
            await populateSelect(selectId, fieldType);
          }
          const select = document.getElementById(fieldType.replace(/s$/, ''));
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
      const form1 = document.getElementById('guest-form');
      const form2 = document.getElementById('services-form');
      const formData = {};
      new FormData(form1).forEach((value, key) => formData[key] = value);

      // Handle multiple room entries
      const roomTypes = Array.from(document.querySelectorAll('select[name="room_type[]"]')).map(select => select.value);
      const roomNumbers = Array.from(document.querySelectorAll('input[name="room_number[]"]')).map(input => input.value);
      const acTypes = Array.from(document.querySelectorAll('select[name="ac_type[]"]')).map(select => select.value);
      const roomRates = Array.from(document.querySelectorAll('input[name="room_rate[]"]')).map(input => input.value);
      formData['rooms'] = roomTypes.map((type, index) => ({
        room_type: type,
        room_number: roomNumbers[index],
        ac_type: acTypes[index],
        room_rate: roomRates[index]
      }));

      new FormData(form2).forEach((value, key) => {
        if (!['room_type[]', 'room_number[]', 'ac_type[]', 'room_rate[]'].includes(key)) {
          formData[key] = value;
        }
      });

      return formData;
    }

    // Handle form submission
    document.getElementById('services-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const checkInDate = document.getElementById('check_in_date').value;
      const checkOutDate = document.getElementById('check_out_date').value;
      if (checkOutDate < checkInDate) {
        alert('Check-out date cannot be earlier than check-in date.');
        return;
      }
      const formData = new FormData();
      const guestData = saveFormData();
      formData.append('guest_data', JSON.stringify(guestData));
      try {
        const response = await fetch('../lapi/save_guest.php', {
          method: 'POST',
          body: formData
        });
        const result = await response.json();
        if (result.success) {
          localStorage.setItem('guestData', JSON.stringify({
            ...guestData,
            guest_reference: result.guest_reference,
            grc_number: guestData.grc_number
          }));
          const newWindow = window.open('guest_details.html', '_blank');
          if (!newWindow) {
            alert('Please allow pop-ups to view the guest details.');
            return;
          }
          document.getElementById('guest-form').reset();
          document.getElementById('services-form').reset();
          document.getElementById('total_room_rate').value = '';
          document.getElementById('rooms-container').innerHTML = `
            <div class="room-entry">
              <select name="room_type[]" class="w-full" required>
                <option value="" disabled selected>Select Room Type</option>
              </select>
              <input type="text" name="room_number[]" placeholder="Room Number (e.g., 201)" class="w-full" required>
              <select name="ac_type[]" class="w-full" required>
                <option value="" disabled selected>A/C Type</option>
                <option value="AC">A/C</option>
                <option value="Non-AC">Non-A/C</option>
              </select>
              <input type="text" name="room_rate[]" class="w-full" readonly placeholder="Rate (Rs.)">
              <button type="button" onclick="removeRoom(this)" class="hidden">Remove</button>
            </div>
          `;
          populateRoomTypes();
          populateSelect('meal_plan', 'meal_plans');
          fetchNextGRCNumber();
          showPage1();
          alert('Guest registration submitted successfully!');
        } else {
          alert('Error submitting registration: ' + result.message);
        }
      } catch (error) {
        console.error('Error submitting registration:', error);
        alert('An error occurred while submitting the registration.');
      }
    });
  </script>
</body>
</html>