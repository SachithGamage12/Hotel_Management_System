<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Guest Registration</title>
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
      background: linear-gradient(145deg, rgba(255,255,255,0.9), rgba(245,243,255,0.9));
      background-image: url('data:image/svg+xml,%3Csvg width="30" height="30" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="%23c4b1ff" fill-opacity="0.15"%3E%3Ccircle cx="5" cy="5" r="5"/%3E%3Ccircle cx="25" cy="25" r="5"%3E%3C/g%3E%3C/svg%3E');
      border-radius: 1.5rem;
      box-shadow: 0 10px 40px rgba(124,58,237,0.15), 0 6px 20px rgba(0,0,0,0.1);
      border: 1px solid rgba(255,255,255,0.3);
      backdrop-filter: blur(20px);
      padding: 3rem;
      position: relative;
      overflow: hidden;
      transition: transform 0.4s ease, box-shadow 0.4s ease;
    }
    .form-container:hover { transform: translateY(-8px); box-shadow: 0 14px 48px rgba(124,58,237,0.25); }
    input, select, textarea {
      font-size: 1rem; padding: 0.875rem; height: 3rem; border: 1px solid #d4b8ff;
      border-radius: 1rem; background: rgba(255,255,255,0.9); transition: all 0.3s ease;
    }
    input:focus, select:focus, textarea:focus {
      outline: none; border-color: #7c3aed; box-shadow: 0 0 0 4px rgba(124,58,237,0.25); transform: scale(1.02);
    }
    button {
      font-size: 1rem; padding: 0.75rem 1.5rem; border-radius: 1rem; transition: all 0.3s ease; font-weight: 500;
    }
    button:hover { transform: translateY(-3px); box-shadow: 0 6px 16px rgba(0,0,0,0.2); }
    .header { background: linear-gradient(to right, #5b21b6, #db2777); padding: 2rem 3rem; border-bottom: 2px solid rgba(255,255,255,0.4); }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(25px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fadeInUp { animation: fadeInUp 0.8s ease-out forwards; }
    .form-group { opacity: 0; transform: translateY(20px); }
    .form-group.animate { animation: fadeInUp 0.7s ease-out forwards; animation-delay: calc(0.15s * var(--index)); }
    h1, h2 { font-family: 'Playfair Display', serif; font-weight: 700; }
    .time-group { display: flex; gap: 0.5rem; align-items: center; }
    .time-group select { width: 5rem; height: 3rem; border-radius: 0.5rem; }
    .room-entry { display: flex; gap: 0.75rem; align-items: center; margin-bottom: 0.75rem; background: rgba(255,255,255,0.95); padding: 0.5rem; border-radius: 0.75rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .add-room-btn { background: #7c3aed; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; }
    .search-box { max-width: 500px; margin: 2rem auto; }
  </style>
</head>
<body class="min-h-screen flex flex-col">
  <div class="header flex justify-between items-center">
    <img src="images/logo.avif" alt="Hotel Logo" class="h-14 rounded-full">
    <h1 class="text-white tracking-wide">Edit Guest Registration</h1>
    <a href="Frontoffice.php"><button style="background:#fff700;color:#000;padding:6px 12px;border-radius:8px;">Back</button></a>
  </div>

  <div class="flex-1 flex items-start justify-center p-8">
    <div class="form-container w-full max-w-5xl p-10 flex flex-col gap-10 animate-fadeInUp">

      <!-- Search GRC Number -->
      <div id="search-section" class="text-center">
        <h2 class="text-2xl mb-6 text-purple-900">Enter GRC Number to Edit</h2>
        <div class="search-box">
          <input type="text" id="search_grc" placeholder="e.g. 1001" class="w-full text-center text-lg">
          <button onclick="loadGuest()" class="mt-4 w-full bg-gradient-to-r from-purple-700 to-pink-700 text-white py-3">Load Guest</button>
        </div>
      </div>

      <!-- Edit Form (hidden until loaded) -->
      <div id="edit-form" class="hidden">
        <h2 class="text-center text-transparent bg-clip-text bg-gradient-to-r from-purple-700 to-pink-700">Edit Guest Details</h2>

        <form id="guest-edit-form" class="grid grid-cols-2 gap-8">
          <input type="hidden" id="guest_id" name="guest_id">

          <!-- Page 1 -->
          <div id="page1-edit">
            <div class="form-group" style="--index: 1"><label>Full Name</label><input type="text" name="guest_name" required></div>
            <div class="form-group" style="--index: 2"><label>Contact Number</label><input type="tel" name="contact_number" required></div>
            <div class="form-group" style="--index: 3"><label>Email</label><input type="email" name="email"></div>
            <div class="form-group" style="--index: 4"><label>Address</label><input type="text" name="address"></div>
            <div class="form-group" style="--index: 5"><label>ID Type</label>
              <select name="id_type" required>
                <option value="NIC">NIC</option>
                <option value="Passport">Passport</option>
              </select>
            </div>
            <div class="form-group" style="--index: 6"><label>ID Number</label><input type="text" name="id_number" required></div>

            <div class="form-group" style="--index: 7"><label>Other Guest 1 Name</label><input type="text" name="other_guest_name_1"></div>
            <div class="form-group" style="--index: 8"><label>Other Guest 1 NIC</label><input type="text" name="other_guest_nic_1"></div>
            <div class="form-group" style="--index: 9"><label>Other Guest 2 Name</label><input type="text" name="other_guest_name_2"></div>
            <div class="form-group" style="--index: 10"><label>Other Guest 2 NIC</label><input type="text" name="other_guest_nic_2"></div>
            <div class="form-group" style="--index: 11"><label>Other Guest 3 Name</label><input type="text" name="other_guest_name_3"></div>
            <div class="form-group" style="--index: 12"><label>Other Guest 3 NIC</label><input type="text" name="other_guest_nic_3"></div>

            <div class="form-group" style="--index: 13"><label>Check-In Date</label><input type="date" name="check_in_date" required></div>
            <div class="form-group" style="--index: 14"><label>Check-In Time</label>
              <div class="time-group">
                <input type="time" name="check_in_time" required>
                <select name="check_in_time_am_pm"><option>AM</option><option>PM</option></select>
              </div>
            </div>
            <div class="form-group" style="--index: 15"><label>Check-Out Date</label><input type="date" name="check_out_date" required></div>
            <div class="form-group" style="--index: 16"><label>Check-Out Time</label>
              <div class="time-group">
                <input type="time" name="check_out_time" required>
                <select name="check_out_time_am_pm"><option>AM</option><option>PM</option></select>
              </div>
            </div>

            <div class="col-span-2 text-right">
              <button type="button" onclick="showPage2Edit()" class="bg-gradient-to-r from-purple-700 to-pink-700 text-white px-8 py-4 rounded-lg">Next</button>
            </div>
          </div>

          <!-- Page 2 -->
          <div id="page2-edit" class="hidden">
            <div class="form-group col-span-2">
              <label>Rooms</label>
              <div id="rooms-container-edit"></div>
              <button type="button" onclick="addRoomEdit()" class="mt-2 add-room-btn">Add Room</button>
            </div>

            <div class="form-group"><label>Total Room Rate (Rs.)</label><input type="text" id="total_room_rate_edit" readonly></div>
            <div class="form-group"><label>Meal Plan</label>
              <select id="meal_plan_edit" name="meal_plan_id"></select>
            </div>
            <div class="form-group"><label>Number of Pax</label><input type="number" name="number_of_pax" min="1"></div>
            <div class="form-group col-span-2"><label>Remarks</label><textarea name="remarks"></textarea></div>

            <div class="col-span-2 flex gap-4">
              <button type="button" onclick="showPage1Edit()" class="flex-1 bg-gray-600 text-white py-4 rounded-lg">Back</button>
              <button type="submit" class="flex-1 bg-gradient-to-r from-purple-700 to-pink-700 text-white py-4 rounded-lg">Update Guest</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // === Load Guest by GRC ===
    async function loadGuest() {
      const grc = document.getElementById('search_grc').value.trim();
      if (!grc) return alert('Please enter GRC number');

      try {
        const res = await fetch(`api/fetch_guest.php?grc=${grc}`);
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Guest not found');

        const g = data.guest;
        document.getElementById('guest_id').value = g.id;
        document.getElementById('search-section').classList.add('hidden');
        document.getElementById('edit-form').classList.remove('hidden');

        // Fill Page 1
        document.querySelector('[name="guest_name"]').value = g.guest_name;
        document.querySelector('[name="contact_number"]').value = g.contact_number;
        document.querySelector('[name="email"]').value = g.email || '';
        document.querySelector('[name="address"]').value = g.address || '';
        document.querySelector('[name="id_type"]').value = g.id_type;
        document.querySelector('[name="id_number"]').value = g.id_number;
        document.querySelector('[name="other_guest_name_1"]').value = g.other_guest_name_1 || '';
        document.querySelector('[name="other_guest_nic_1"]').value = g.other_guest_nic_1 || '';
        document.querySelector('[name="other_guest_name_2"]').value = g.other_guest_name_2 || '';
        document.querySelector('[name="other_guest_nic_2"]').value = g.other_guest_nic_2 || '';
        document.querySelector('[name="other_guest_name_3"]').value = g.other_guest_name_3 || '';
        document.querySelector('[name="other_guest_nic_3"]').value = g.other_guest_nic_3 || '';

        document.querySelector('[name="check_in_date"]').value = g.check_in_date;
        document.querySelector('[name="check_in_time"]').value = g.check_in_time.slice(0,5);
        document.querySelector('[name="check_in_time_am_pm"]').value = g.check_in_time_am_pm;
        document.querySelector('[name="check_out_date"]').value = g.check_out_date;
        document.querySelector('[name="check_out_time"]').value = g.check_out_time.slice(0,5);
        document.querySelector('[name="check_out_time_am_pm"]').value = g.check_out_time_am_pm;

        // Fill Page 2
        document.querySelector('[name="number_of_pax"]').value = g.number_of_pax || '';
        document.querySelector('[name="remarks"]').value = g.remarks || '';

        // Populate dropdowns
        await populateRoomTypes();
        await populateSelect('meal_plan_edit', 'meal_plans');
        document.getElementById('meal_plan_edit').value = g.meal_plan_id || '';

        // Load rooms
        const roomsContainer = document.getElementById('rooms-container-edit');
        roomsContainer.innerHTML = '';
        const rooms = JSON.parse(g.rooms);
        rooms.forEach((room, i) => {
          const entry = document.createElement('div');
          entry.className = 'room-entry';
          entry.innerHTML = `
            <select name="room_type[]" required></select>
            <input type="text" name="room_number[]" value="${room.room_number}" required>
            <select name="ac_type[]" required>
              <option value="AC">A/C</option>
              <option value="Non-AC">Non-A/C</option>
            </select>
            <input type="text" name="room_rate[]" readonly placeholder="Rate">
            <button type="button" onclick="this.parentElement.remove();fetchRoomRatesEdit();" class="bg-red-600 text-white px-3">Remove</button>
          `;
          roomsContainer.appendChild(entry);
          const selectType = entry.querySelector('select[name="room_type[]"]');
          populateRoomTypes([selectType]).then(() => {
            selectType.value = room.room_type;
            entry.querySelector('select[name="ac_type[]"]').value = room.ac_type;
            entry.querySelector('input[name="room_rate[]"]').value = room.room_rate;
            if (i === rooms.length - 1) fetchRoomRatesEdit();
          });
        });

        // Animate fields
        document.querySelectorAll('#page1-edit .form-group').forEach(g => g.classList.add('animate'));
      } catch (err) {
        alert(err.message);
      }
    }

    // === Navigation ===
    function showPage1Edit() { document.getElementById('page2-edit').classList.add('hidden'); document.getElementById('page1-edit').classList.remove('hidden'); }
    function showPage2Edit() { document.getElementById('page1-edit').classList.add('hidden'); document.getElementById('page2-edit').classList.remove('hidden'); fetchRoomRatesEdit(); }

    // === Room Management (Edit) ===
    function addRoomEdit() {
      const container = document.getElementById('rooms-container-edit');
      const entry = document.createElement('div');
      entry.className = 'room-entry';
      entry.innerHTML = `
        <select name="room_type[]" required><option value="" disabled selected>Select Room Type</option></select>
        <input type="text" name="room_number[]" placeholder="Room Number" required>
        <select name="ac_type[]" required><option value="" disabled selected>A/C Type</option><option>AC</option><option>Non-AC</option></select>
        <input type="text" name="room_rate[]" readonly placeholder="Rate">
        <button type="button" onclick="this.parentElement.remove();fetchRoomRatesEdit();" class="bg-red-600 text-white px-3">Remove</button>
      `;
      container.appendChild(entry);
      populateRoomTypes([entry.querySelector('select[name="room_type[]"]')]);
      updateRoomListenersEdit();
    }

    function updateRoomListenersEdit() {
      document.querySelectorAll('select[name="room_type[]"], input[name="room_number[]"], select[name="ac_type[]"]')
        .forEach(el => el.onchange = fetchRoomRatesEdit);
    }

    async function fetchRoomRatesEdit() {
      const roomData = Array.from(document.querySelectorAll('#rooms-container-edit .room-entry')).map(entry => {
        const type = entry.querySelector('select[name="room_type[]"]').value;
        const num = entry.querySelector('input[name="room_number[]"]').value;
        const ac = entry.querySelector('select[name="ac_type[]"]').value;
        return `${type}:${num}:${ac}`;
      }).filter(Boolean).join('/');

      if (!roomData) {
        document.getElementById('total_room_rate_edit').value = '';
        return;
      }

      const res = await fetch(`api/fetch_room_rate.php?room_data=${encodeURIComponent(roomData)}`);
      const json = await res.json();
      if (json.success) {
        document.querySelectorAll('#rooms-container-edit input[name="room_rate[]"]').forEach((inp, i) => {
          inp.value = json.individual_rates[i] || '';
        });
        document.getElementById('total_room_rate_edit').value = json.total_rate;
      }
    }

    // === Populate Selects ===
    async function populateRoomTypes(targets = null) {
      const res = await fetch('api/fetch_options.php?field_type=room_types');
      const items = await res.json();
      const selects = targets || document.querySelectorAll('select[name="room_type[]"]');
      selects.forEach(sel => {
        const val = sel.value;
        sel.innerHTML = '<option value="" disabled selected>Select Room Type</option>';
        items.forEach(it => {
          const opt = document.createElement('option');
          opt.value = it.id; opt.textContent = it.name;
          sel.appendChild(opt);
        });
        if (val) sel.value = val;
      });
    }

    async function populateSelect(id, type) {
      const res = await fetch(`api/fetch_options.php?field_type=${type}`);
      const items = await res.json();
      const sel = document.getElementById(id);
      sel.innerHTML = '<option value="" disabled selected>Select</option>';
      items.forEach(it => {
        const opt = document.createElement('option');
        opt.value = it.id; opt.textContent = it.name;
        sel.appendChild(opt);
      });
    }

    // === Submit Update ===
    document.getElementById('guest-edit-form').onsubmit = async function(e) {
      e.preventDefault();
      const formData = new FormData();
      const data = {};
      new FormData(this).forEach((v, k) => data[k] = v);

      // Collect rooms
      const rooms = [];
      document.querySelectorAll('#rooms-container-edit .room-entry').forEach(entry => {
        rooms.push({
          room_type: entry.querySelector('select[name="room_type[]"]').value,
          room_number: entry.querySelector('input[name="room_number[]"]').value,
          ac_type: entry.querySelector('select[name="ac_type[]"]').value,
          room_rate: entry.querySelector('input[name="room_rate[]"]').value
        });
      });
      data.rooms = JSON.stringify(rooms);

      formData.append('guest_data', JSON.stringify(data));

      try {
        const res = await fetch('api/update_guest.php', { method: 'POST', body: formData });
        const json = await res.json();
        if (json.success) {
          alert('Guest updated successfully!');
          location.reload();
        } else {
          alert('Error: ' + json.message);
        }
      } catch (err) {
        alert('Network error');
      }
    };

    // Initial setup
    document.querySelectorAll('#page1-edit .form-group').forEach((g, i) => g.style.setProperty('--index', i+1));
  </script>
</body>
</html>