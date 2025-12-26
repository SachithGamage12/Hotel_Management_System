<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Guest Registration</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { 
      font-family: 'Inter', sans-serif; 
      background: #0a0a0a;
      color: #fff;
      min-height: 100vh;
    }
    .glass {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 24px;
    }
    .input-modern {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      padding: 14px 16px;
      color: #fff;
      transition: all 0.3s ease;
      width: 100%;
    }
    .input-modern:focus {
      outline: none;
      background: rgba(255, 255, 255, 0.08);
      border-color: #667eea;
      box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }
    .input-modern::placeholder { color: rgba(255, 255, 255, 0.4); }
    .btn-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      padding: 14px 32px;
      border-radius: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      color: #fff;
    }
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    }
    .btn-secondary {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      padding: 14px 32px;
      border-radius: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      color: #fff;
    }
    .btn-secondary:hover {
      background: rgba(255, 255, 255, 0.1);
    }
    .room-card {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 16px;
      padding: 20px;
      margin-bottom: 16px;
      transition: all 0.3s ease;
    }
    .room-card:hover {
      border-color: rgba(102, 126, 234, 0.5);
      transform: translateY(-2px);
    }
    .label {
      font-size: 14px;
      font-weight: 500;
      color: rgba(255, 255, 255, 0.7);
      margin-bottom: 8px;
      display: block;
    }
    .section-title {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 20px;
      color: #fff;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .badge {
      background: rgba(102, 126, 234, 0.2);
      color: #667eea;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
    }
    select.input-modern {
      cursor: pointer;
    }
    select.input-modern option {
      background: #1a1a1a;
      color: #fff;
    }
    .fade-in {
      animation: fadeIn 0.5s ease-in;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .header-modern {
      background: rgba(10, 10, 10, 0.8);
      backdrop-filter: blur(20px);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .search-container {
      position: relative;
    }
    .search-icon {
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: rgba(255, 255, 255, 0.4);
    }
    .input-with-icon {
      padding-left: 48px;
    }
    .remove-btn {
      background: rgba(239, 68, 68, 0.1);
      border: 1px solid rgba(239, 68, 68, 0.3);
      color: #ef4444;
      padding: 8px 16px;
      border-radius: 8px;
      font-size: 14px;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .remove-btn:hover {
      background: rgba(239, 68, 68, 0.2);
      border-color: #ef4444;
    }
    .total-rate-display {
      background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
      border: 2px solid rgba(102, 126, 234, 0.3);
      padding: 20px;
      border-radius: 16px;
      text-align: center;
    }
    .total-rate-value {
      font-size: 32px;
      font-weight: 700;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
  </style>
</head>
<body>

  <!-- Header -->
  <div class="header-modern">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
      <div class="flex items-center gap-4">
        <img src="images/logo.avif" alt="Logo" class="w-12 h-12 rounded-full" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
        <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-pink-600 rounded-full hidden items-center justify-center font-bold text-xl">G</div>
        <div>
          <h1 class="text-xl font-bold">Guest Registry</h1>
          <p class="text-sm text-gray-400">Edit Registration Card</p>
        </div>
      </div>
      <a href="Frontoffice.php">
        <button class="btn-secondary">← Back to Dashboard</button>
      </a>
    </div>
  </div>

  <div class="max-w-7xl mx-auto px-6 py-8">
    
    <!-- Search Section -->
    <div class="glass p-8 mb-8">
      <h2 class="text-2xl font-bold mb-6">Search Guest</h2>
      <div class="flex gap-4 items-end max-w-2xl">
        <div class="flex-1 search-container">
          <svg class="search-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
          <input type="text" id="search_grc" placeholder="Enter GRC Number (e.g., GRC-2025-001)" class="input-modern input-with-icon">
        </div>
        <button onclick="loadGuest()" class="btn-primary">Search</button>
      </div>
      <p class="text-sm text-gray-400 mt-4">💡 Tip: Leave blank to view recent registrations</p>
    </div>

    <!-- Edit Form -->
    <div id="edit-form-container" class="hidden fade-in">
      <form id="edit-guest-form">
        <input type="hidden" id="guest_id" name="guest_id">

        <!-- Guest Information -->
        <div class="glass p-8 mb-6">
          <div class="section-title">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Guest Information
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
              <label class="label">GRC Number <span class="badge">READ ONLY</span></label>
              <input type="text" id="grc_number" readonly class="input-modern opacity-50 cursor-not-allowed">
            </div>
            <div>
              <label class="label">Full Name <span class="text-red-400">*</span></label>
              <input type="text" name="guest_name" required class="input-modern">
            </div>
            <div>
              <label class="label">Contact Number <span class="text-red-400">*</span></label>
              <input type="tel" name="contact_number" required class="input-modern">
            </div>
            <div>
              <label class="label">Email Address</label>
              <input type="text" name="email" class="input-modern" placeholder="Enter email address">
            </div>
            <div class="md:col-span-2">
              <label class="label">Address</label>
              <input type="text" name="address" class="input-modern">
            </div>
            <div>
              <label class="label">ID Type <span class="text-red-400">*</span></label>
              <select name="id_type" required class="input-modern">
                <option value="NIC">National ID Card</option>
                <option value="Passport">Passport</option>
              </select>
            </div>
            <div>
              <label class="label">ID Number <span class="text-red-400">*</span></label>
              <input type="text" name="id_number" required class="input-modern">
            </div>
          </div>
        </div>

        <!-- Additional Guests -->
        <div class="glass p-8 mb-6">
          <div class="section-title">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            Additional Guests <span class="text-sm font-normal text-gray-400">(Optional)</span>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="label">Guest 1 Name</label>
              <input type="text" name="other_guest_name_1" class="input-modern" placeholder="Enter full name">
            </div>
            <div>
              <label class="label">Guest 1 NIC/Passport</label>
              <input type="text" name="other_guest_nic_1" class="input-modern" placeholder="Enter NIC or Passport">
            </div>
            <div>
              <label class="label">Guest 2 Name</label>
              <input type="text" name="other_guest_name_2" class="input-modern" placeholder="Enter full name">
            </div>
            <div>
              <label class="label">Guest 2 NIC/Passport</label>
              <input type="text" name="other_guest_nic_2" class="input-modern" placeholder="Enter NIC or Passport">
            </div>
            <div>
              <label class="label">Guest 3 Name</label>
              <input type="text" name="other_guest_name_3" class="input-modern" placeholder="Enter full name">
            </div>
            <div>
              <label class="label">Guest 3 NIC/Passport</label>
              <input type="text" name="other_guest_nic_3" class="input-modern" placeholder="Enter NIC or Passport">
            </div>
          </div>
        </div>

        <!-- Booking Details -->
        <div class="glass p-8 mb-6">
          <div class="section-title">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            Booking Details
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
              <label class="label">Check-In Date <span class="text-red-400">*</span></label>
              <input type="date" name="check_in_date" required class="input-modern">
            </div>
            <div>
              <label class="label">Check-In Time <span class="text-red-400">*</span></label>
              <div class="flex gap-2">
                <input type="time" name="check_in_time" required class="input-modern flex-1">
                <select name="check_in_time_am_pm" class="input-modern" style="width: 90px;">
                  <option>AM</option>
                  <option>PM</option>
                </select>
              </div>
            </div>
            <div>
              <label class="label">Check-Out Date <span class="text-red-400">*</span></label>
              <input type="date" name="check_out_date" required class="input-modern">
            </div>
            <div>
              <label class="label">Check-Out Time <span class="text-red-400">*</span></label>
              <div class="flex gap-2">
                <input type="time" name="check_out_time" required class="input-modern flex-1">
                <select name="check_out_time_am_pm" class="input-modern" style="width: 90px;">
                  <option>AM</option>
                  <option>PM</option>
                </select>
              </div>
            </div>
            <div>
              <label class="label">Meal Plan</label>
              <select id="meal_plan_select" name="meal_plan_id" class="input-modern">
                <option value="">Select Meal Plan</option>
              </select>
            </div>
            <div>
              <label class="label">Number of Guests</label>
              <input type="number" name="number_of_pax" min="1" class="input-modern" placeholder="Total guests">
            </div>
          </div>
        </div>

        <!-- Rooms -->
        <div class="glass p-8 mb-6">
          <div class="flex justify-between items-center mb-6">
            <div class="section-title mb-0">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
              </svg>
              Room Assignment
            </div>
            <button type="button" onclick="addRoomField()" class="btn-primary">+ Add Room</button>
          </div>
          
          <div id="rooms-container"></div>

          <!-- Total Rate Display -->
          <div class="total-rate-display mt-6">
            <div class="label mb-2">Total Room Rate</div>
            <div id="total_room_rate" class="total-rate-value">Rs. 0.00</div>
          </div>
        </div>

        <!-- Remarks -->
        <div class="glass p-8 mb-6">
          <div class="section-title">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
            </svg>
            Additional Notes
          </div>
          <textarea name="remarks" rows="4" class="input-modern" placeholder="Add any special requests or important information..."></textarea>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-4 justify-end">
          <button type="button" onclick="cancelEdit()" class="btn-secondary px-8">Cancel</button>
          <button type="submit" class="btn-primary px-8">
            <span class="flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              Update Registration
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    async function loadGuest() {
      const grc = document.getElementById('search_grc').value.trim();
      const url = grc ? `api/fetch_guest.php?grc=${grc}` : 'api/fetch_guest.php?recent=1';

      try {
        const res = await fetch(url);
        const data = await res.json();

        if (!data.success) {
          alert(data.message || 'Guest not found');
          return;
        }

        populateForm(data.guest);
        document.getElementById('edit-form-container').classList.remove('hidden');
        document.getElementById('edit-form-container').scrollIntoView({ behavior: 'smooth' });
      } catch (err) {
        alert('Error loading guest data');
        console.error(err);
      }
    }

    function populateForm(guest) {
      document.getElementById('guest_id').value = guest.id;
      document.getElementById('grc_number').value = guest.grc_number;

      // Basic info
      ['guest_name', 'contact_number', 'email', 'address', 'id_type', 'id_number'].forEach(field => {
        const input = document.querySelector(`[name="${field}"]`);
        if (input) input.value = guest[field] || '';
      });

      // Additional guests
      for (let i = 1; i <= 3; i++) {
        const nameInput = document.querySelector(`[name="other_guest_name_${i}"]`);
        const nicInput = document.querySelector(`[name="other_guest_nic_${i}"]`);
        if (nameInput) nameInput.value = guest[`other_guest_name_${i}`] || '';
        if (nicInput) nicInput.value = guest[`other_guest_nic_${i}`] || '';
      }

      // Dates & times
      document.querySelector('[name="check_in_date"]').value = guest.check_in_date;
      document.querySelector('[name="check_in_time"]').value = guest.check_in_time;
      document.querySelector('[name="check_in_time_am_pm"]').value = guest.check_in_time_am_pm;
      document.querySelector('[name="check_out_date"]').value = guest.check_out_date;
      document.querySelector('[name="check_out_time"]').value = guest.check_out_time;
      document.querySelector('[name="check_out_time_am_pm"]').value = guest.check_out_time_am_pm;

      document.querySelector('[name="number_of_pax"]').value = guest.number_of_pax || '';
      document.querySelector('[name="remarks"]').value = guest.remarks || '';

      // Meal Plan
      populateSelect('meal_plan_select', 'meal_plans', guest.meal_plan_id);

      // Rooms
      const container = document.getElementById('rooms-container');
      container.innerHTML = '';
      const rooms = JSON.parse(guest.rooms || '[]');
      rooms.forEach(room => addRoomField(room));
      if (rooms.length === 0) addRoomField();
    }

    async function populateSelect(selectId, table, selected = null) {
      try {
        const res = await fetch(`api/fetch_options.php?field_type=${table}`);
        const items = await res.json();
        const select = document.getElementById(selectId);
        select.innerHTML = '<option value="">Select Meal Plan</option>';
        items.forEach(item => {
          const opt = new Option(item.name, item.id, false, item.id == selected);
          select.add(opt);
        });
      } catch (err) {
        console.error('Error loading options:', err);
      }
    }

    function addRoomField(room = null) {
      const container = document.getElementById('rooms-container');
      const div = document.createElement('div');
      div.className = 'room-card';
      div.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
          <div>
            <label class="label">Room Type</label>
            <select name="room_type[]" required class="input-modern" onchange="fetchRoomRates()"></select>
          </div>
          <div>
            <label class="label">Room Number</label>
            <input type="text" name="room_number[]" value="${room?.room_number || ''}" placeholder="e.g., 101" required class="input-modern" onchange="fetchRoomRates()">
          </div>
          <div>
            <label class="label">A/C Type</label>
            <select name="ac_type[]" required class="input-modern" onchange="fetchRoomRates()">
              <option value="AC" ${room?.ac_type === 'AC' ? 'selected' : ''}>Air Conditioned</option>
              <option value="Non-AC" ${room?.ac_type === 'Non-AC' ? 'selected' : ''}>Non A/C</option>
            </select>
          </div>
          <div class="flex gap-2 items-end">
            <div class="flex-1">
              <label class="label">Rate</label>
              <input type="text" value="${room?.room_rate || ''}" readonly placeholder="Auto" class="input-modern opacity-50 room-rate-display">
            </div>
            <button type="button" onclick="this.closest('.room-card').remove(); fetchRoomRates();" class="remove-btn">Remove</button>
          </div>
        </div>
      `;
      container.appendChild(div);
      populateRoomTypes(div.querySelector('select[name="room_type[]"]'), room?.room_type);
    }

    async function populateRoomTypes(select, selectedId = null) {
      try {
        const res = await fetch('api/fetch_options.php?field_type=room_types');
        const items = await res.json();
        select.innerHTML = '<option value="">Select Room Type</option>';
        items.forEach(item => {
          const opt = new Option(item.name, item.id, false, item.id == selectedId);
          select.add(opt);
        });
      } catch (err) {
        console.error('Error loading room types:', err);
      }
    }

    async function fetchRoomRates() {
      const types = document.querySelectorAll('select[name="room_type[]"]');
      const nums = document.querySelectorAll('input[name="room_number[]"]');
      const acs = document.querySelectorAll('select[name="ac_type[]"]');
      const rates = document.querySelectorAll('.room-rate-display');
      const totalInput = document.getElementById('total_room_rate');

      const data = [];
      let valid = true;

      for (let i = 0; i < types.length; i++) {
        if (!types[i].value || !nums[i].value || !acs[i].value) {
          valid = false;
          rates[i].value = 'Complete all fields';
          continue;
        }
        data.push(`${types[i].value}:${nums[i].value.trim()}:${acs[i].value}`);
        rates[i].value = 'Fetching...';
      }

      if (!valid || data.length === 0) {
        totalInput.textContent = 'Please complete room details';
        return;
      }

      try {
        const res = await fetch(`api/fetch_room_rate.php?room_data=${data.join('/')}`);
        const json = await res.json();
        if (json.success) {
          json.individual_rates.forEach((r, i) => {
            if (rates[i]) rates[i].value = `Rs. ${r}`;
          });
          totalInput.textContent = `Rs. ${json.total_rate}`;
        } else {
          totalInput.textContent = 'Rate calculation error';
        }
      } catch (e) {
        totalInput.textContent = 'Network error';
        console.error('Rate fetch error:', e);
      }
    }

    function cancelEdit() {
      if (confirm('Are you sure? Any unsaved changes will be lost.')) {
        document.getElementById('edit-form-container').classList.add('hidden');
        document.getElementById('search_grc').value = '';
      }
    }

    // Submit Update
    document.getElementById('edit-guest-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(e.target);
      const data = {
        guest_id: document.getElementById('guest_id').value,
        rooms: []
      };

      // Collect room data
      document.querySelectorAll('.room-card').forEach(entry => {
        const rateDisplay = entry.querySelector('.room-rate-display').value;
        const rateValue = rateDisplay.replace('Rs. ', '').trim();
        data.rooms.push({
          room_type: entry.querySelector('select[name="room_type[]"]').value,
          room_number: entry.querySelector('input[name="room_number[]"]').value,
          ac_type: entry.querySelector('select[name="ac_type[]"]').value,
          room_rate: rateValue
        });
      });

      // Add other fields
      formData.forEach((value, key) => {
        if (!key.includes('[]')) data[key] = value;
      });

      try {
        const res = await fetch('api/update_guest.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.success) {
          alert('Guest updated successfully!');
          cancelEdit();
        } else {
          alert('Error: ' + (result.message || 'Update failed'));
        }
      } catch (err) {
        alert('Update failed. Please try again.');
        console.error('Update error:', err);
      }
    });

    // Allow Enter key to search
    document.getElementById('search_grc').addEventListener('keypress', e => {
      if (e.key === 'Enter') loadGuest();
    });
  </script>
</body>
</html>