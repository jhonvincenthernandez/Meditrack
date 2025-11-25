<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Staff Add Appointment — MediTrack+</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar p-3">
  <a href="<?= base_url(); ?>/" class="d-flex align-items-center mb-4 text-decoration-none">
    <div class="brand-logo"></div>
    <div class="ms-2">
      <div class="brand-title">MediTrack+</div>
      <small class="text-muted">Clinic Staff</small>
    </div>
  </a>
  <nav class="nav flex-column">
    <a class="nav-link active" href="<?= base_url(); ?>/dashboard_staff">🏠 Dashboard</a>
    <a class="nav-link" href="<?= base_url(); ?>/appointments/staff_add">➕ Add Appointment</a>
    <a class="nav-link" href="<?= base_url(); ?>/patients">👥 Patient Directory</a>
    <a class="nav-link" href="<?= base_url(); ?>/payments/records">💰 Payment Records</a>
    <a href="<?= site_url('auth/logout'); ?>" class="btn btn-danger mt-3">Logout</a>
  </nav>
</aside>

  <div class="main p-4">
    <div class="card-soft container-narrow">
      <div class="alert alert-info small">
        Saving this form issues a Xendit invoice in test mode and redirects you to the checkout page to complete payment.
      </div>
  <form method="post" action="<?= base_url(); ?>/appointments/save_staff_add">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Patient <small class="text-muted">(searchable)</small></label>
            <select name="patient_id" id="patient_select" class="form-select" required>
              <option value="">Search patient...</option>
              <?php if(!empty($patients)): foreach($patients as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?><?php if(!empty($p['contact'])): ?> - <?= htmlspecialchars($p['contact']); ?><?php endif; ?></option>
              <?php endforeach; endif; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Doctor <small class="text-muted">(searchable)</small></label>
            <select name="doctor_id" id="doctor_select" class="form-select" required>
              <option value="">Search doctor...</option>
              <?php if(!empty($doctors)): foreach($doctors as $d): ?>
                <option value="<?= $d['id'] ?>" data-specialty="<?= htmlspecialchars($d['specialty'] ?? ''); ?>"><?= htmlspecialchars($d['name']) ?> — <?= htmlspecialchars($d['specialty']) ?></option>
              <?php endforeach; endif; ?>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Date</label>
            <select id="date_select" class="form-select" required>
              <option value="">-- Choose Date --</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Available Slot</label>
            <select name="slot_id" id="slot_id" class="form-select" required>
              <option value="">-- Choose Available Slot --</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Consultation Fee (PHP)</label>
            <input type="number" name="consultation_fee" class="form-control" step="0.01" min="0" value="<?= htmlspecialchars(config_item('default_consultation_fee') ?? 1500); ?>" required>
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
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="<?= base_url().'public/assets/ui.js'; ?>"></script>
<script>
$(document).ready(function() {
  $('#patient_select').select2({
    theme: 'bootstrap-5',
    placeholder: 'Type to search patient...',
    allowClear: true,
    width: '100%'
  });
  
  $('#doctor_select').select2({
    theme: 'bootstrap-5',
    placeholder: 'Type to search doctor...',
    allowClear: true,
    width: '100%'
  });
});

const doctorEl = document.getElementById('doctor_select');
const dateEl = document.getElementById('date_select');
const slotEl = document.getElementById('slot_id');

$('#doctor_select').on('change', async function() {
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
  const doctorId = $('#doctor_select').val();
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
</body>
</html>
