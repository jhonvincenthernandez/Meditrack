<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Add Appointment — MediTrack+</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
</head>
<body>

<!-- Main Content -->
<div class="main">
  <div class="topbar">
    <h4>Add Appointment</h4>
    <div></div>
  </div>

  <div class="card-soft">
    <form method="post" action="<?= site_url('appointments/save_admin'); ?>">
      <div class="row g-3">
        <div class="col-md-6">
          <label for="patient_id" class="form-label">Patient</label>
          <select id="patient_id" name="patient_id" class="form-select" required>
            <option value="" disabled selected>Choose patient...</option>
            <?php if (!empty($patients) && is_array($patients)): ?>
              <?php foreach ($patients as $p): ?>
                <option value="<?= htmlspecialchars($p['id']); ?>">
                  <?= htmlspecialchars($p['name'] ?? ($p['first_name'].' '.$p['last_name'] ?? 'Patient #'.$p['id'])); ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label for="doctor_id" class="form-label">Doctor</label>
          <select id="doctor_id" name="doctor_id" class="form-select" required>
            <option value="" disabled selected>Choose doctor...</option>
            <?php if (!empty($doctors) && is_array($doctors)): ?>
              <?php foreach ($doctors as $d): ?>
                <option value="<?= htmlspecialchars($d['id']); ?>">
                  <?= htmlspecialchars($d['name'] ?? ($d['first_name'].' '.$d['last_name'] ?? 'Doctor #'.$d['id'])); ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
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
          <label for="notes" class="form-label">Notes (optional)</label>
          <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Reason / symptoms / remarks"></textarea>
        </div>
      </div>

      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary">Save Appointment</button>
        <a href="<?= base_url(); ?>/appointments" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script>
const doctorEl = document.getElementById('doctor_id');
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>