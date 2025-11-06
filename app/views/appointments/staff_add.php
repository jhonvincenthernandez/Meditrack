<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Staff Add Appointment — MediTrack+</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
</head>
<body>
  <div class="sidebar"><h5>MediTrack+</h5><hr></div>

  <div class="main p-4">
    <div class="card-soft container-narrow">
  <form method="post" action="<?= base_url(); ?>/appointments/save_staff_add">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Patient</label>
            <select name="patient_id" class="form-select" required>
              <option value="">Select Patient</option>
              <?php if(!empty($patients)): foreach($patients as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
              <?php endforeach; endif; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Doctor</label>
            <select name="doctor_id" class="form-select" required>
              <option value="">Select Doctor</option>
              <?php if(!empty($doctors)): foreach($doctors as $d): ?>
                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?> — <?= htmlspecialchars($d['specialty']) ?></option>
              <?php endforeach; endif; ?>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Date</label>
            <select id="date_select" class="form-select" required>
              <option value="">-- Choose Date --</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Available Slot</label>
            <select name="slot_id" id="slot_id" class="form-select" required>
              <option value="">-- Choose Available Slot --</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="3"></textarea>
          </div>

          <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary">Save</button>
            <a class="btn btn-outline-secondary" href="<?= base_url(); ?>/dashboard_staff">Cancel</a>
          </div>
        </div>
      </form>
    </div>
  </div>
<script>
const doctorEl = document.querySelector('select[name="doctor_id"]');
const dateEl = document.getElementById('date_select');
const slotEl = document.getElementById('slot_id');

doctorEl.addEventListener('change', async function() {
  const doctorId = this.value;
  dateEl.innerHTML = '<option>Loading...</option>';
  slotEl.innerHTML = '<option value="">-- Choose Available Slot --</option>';
  if (!doctorId) return;
  const res = await fetch('<?= base_url(); ?>/appointments/getDoctorDates/' + doctorId);
  const dates = await res.json();
  dateEl.innerHTML = '<option value="">-- Choose Date --</option>';
  if (!dates || dates.length === 0) {
    dateEl.innerHTML += '<option disabled>No dates available</option>';
    return;
  }
  dates.forEach(d => {
    const opt = document.createElement('option');
    opt.value = d.date;
    opt.textContent = d.date;
    dateEl.appendChild(opt);
  });
});

dateEl.addEventListener('change', async function() {
  const doctorId = doctorEl.value;
  const date = this.value;
  slotEl.innerHTML = '<option>Loading...</option>';
  if (!doctorId || !date) return;
  const res = await fetch(`<?= base_url(); ?>/appointments/getAvailableSlots/${doctorId}/${date}`);
  const slots = await res.json();
  slotEl.innerHTML = '<option value="">-- Choose Available Slot --</option>';
  if (!slots || slots.length === 0) {
    slotEl.innerHTML += '<option disabled>No slots on this date</option>';
    return;
  }
  slots.forEach(s => {
    const opt = document.createElement('option');
    opt.value = s.id;
    opt.textContent = `${s.start_time} - ${s.end_time}`;
    slotEl.appendChild(opt);
  });
});
</script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= base_url().'public/assets/ui.js'; ?>"></script>
</body>
</html>
