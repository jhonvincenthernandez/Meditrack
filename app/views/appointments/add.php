<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Add Appointment — MediTrack+</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
</head>
<body>

<!-- Main Content -->
<div class="main">
  <div class="topbar">
    <h4>Add Appointment</h4>
    <div></div>
  </div>

  <!-- Sidebar -->
<aside class="sidebar">
  <a href="<?= base_url(); ?>/" class="d-flex align-items-center mb-4 text-decoration-none">
    <div class="brand-logo"></div>
    <div class="ms-2">
      <div class="brand-title">MediTrack+</div>
      <small class="text-muted">Clinic Manager</small>
    </div>
  </a>
  <nav class="nav flex-column">
    <a class="nav-link" href="<?= base_url(); ?>/dashboard_admin">🏠 Dashboard</a>
    <a class="nav-link" href="<?= base_url(); ?>/users">👥 Users</a>
    <a class="nav-link" href="<?= base_url(); ?>/patients">🧾 Patients</a>
    <a class="nav-link" href="<?= base_url(); ?>/doctors">🩺 Doctors</a>
    <a class="nav-link active" href="<?= base_url(); ?>/appointments">📅 Appointments</a>
    <a class="nav-link" href="<?= base_url(); ?>/schedules">📆 Schedules</a>
    <a class="nav-link" href="<?= base_url(); ?>/payments/records">💰 Payment Records</a>
    <a href="<?= site_url('auth/logout'); ?>" class="btn btn-danger mt-3">Logout</a>
  </nav>
</aside>

  <div class="card-soft">
    <div class="alert alert-info small">
      Booking a slot will automatically generate a Xendit checkout invoice. Once saved, you'll be redirected to the hosted payment page and the appointment will track the live payment status.
    </div>
    <form method="post" action="<?= site_url('appointments/save_admin'); ?>">
      <div class="row g-3">
        <div class="col-md-6">
          <label for="patient_id" class="form-label">Patient <small class="text-muted">(searchable)</small></label>
          <select id="patient_id" name="patient_id" class="form-select" required>
            <option value="">Search patient...</option>
            <?php if (!empty($patients) && is_array($patients)): ?>
              <?php foreach ($patients as $p): ?>
                <option value="<?= htmlspecialchars($p['id']); ?>">
                  <?= htmlspecialchars($p['name'] ?? ($p['first_name'].' '.$p['last_name'] ?? 'Patient #'.$p['id'])); ?>
                  <?php if (!empty($p['contact'])): ?> - <?= htmlspecialchars($p['contact']); ?><?php endif; ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label for="doctor_id" class="form-label">Doctor <small class="text-muted">(searchable)</small></label>
          <select id="doctor_id" name="doctor_id" class="form-select" required>
            <option value="">Search doctor...</option>
            <?php if (!empty($doctors) && is_array($doctors)): ?>
              <?php foreach ($doctors as $d): ?>
                <option value="<?= htmlspecialchars($d['id']); ?>">
                  <?= htmlspecialchars($d['name'] ?? ($d['first_name'].' '.$d['last_name'] ?? 'Doctor #'.$d['id'])); ?>
                  <?php if (!empty($d['specialty'])): ?> - <?= htmlspecialchars($d['specialty']); ?><?php endif; ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Date <small class="text-muted" id="date_count"></small></label>
          <select id="date_select" class="form-select" required>
            <option value="">-- Choose Date --</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Available Slot <small class="text-success" id="slot_count"></small></label>
          <select name="slot_id" id="slot_id" class="form-select" required>
            <option value="">-- Choose Available Slot --</option>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Consultation Fee (PHP)</label>
          <input type="number" name="consultation_fee" step="0.01" min="0" class="form-control" value="<?= htmlspecialchars(config_item('default_consultation_fee') ?? 1500); ?>" required>
        </div>

        <div class="col-12">
          <label for="notes" class="form-label">Notes (optional)</label>
          <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Reason / symptoms / remarks"></textarea>
        </div>

        <!-- Appointment Summary -->
        <div class="col-12" id="summary_box" style="display:none;">
          <div class="alert alert-light border">
            <h6 class="mb-2">📋 Appointment Summary</h6>
            <div id="summary_content" class="small"></div>
          </div>
        </div>
      </div>

      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary" id="submit_btn">Save Appointment</button>
        <a href="<?= base_url(); ?>/appointments" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" crossorigin="anonymous"></script>
<script src="<?= base_url().'public/assets/ui.js'; ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script>
const dateEl = document.getElementById('date_select');
const slotEl = document.getElementById('slot_id');

$(document).ready(function() {
  $('#patient_id').select2({
    theme: 'bootstrap-5',
    placeholder: 'Type to search patient...',
    allowClear: true,
    width: '100%'
  });
  
  $('#doctor_id').select2({
    theme: 'bootstrap-5',
    placeholder: 'Type to search doctor...',
    allowClear: true,
    width: '100%'
  });
  
  // Select2 change event
  $('#doctor_id').on('select2:select', async function(e) {
    const doctorId = e.params.data.id;
    dateEl.innerHTML = '<option>Loading...</option>';
    slotEl.innerHTML = '<option value="">-- Choose Available Slot --</option>';
    if (!doctorId) return;
    
    try {
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
      document.getElementById('date_count').textContent = `(${dates.length} available)`;
    } catch(err) {
      console.error('Error loading dates:', err);
      dateEl.innerHTML = '<option value="">Error loading dates</option>';
    }
  });
  
  $(dateEl).on('change', async function() {
    const doctorId = $('#doctor_id').val();
    const date = this.value;
    slotEl.innerHTML = '<option>Loading...</option>';
    if (!doctorId || !date) return;
    
    try {
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
        opt.dataset.start = s.start_time;
        opt.dataset.end = s.end_time;
        slotEl.appendChild(opt);
      });
      document.getElementById('slot_count').textContent = `✓ ${slots.length} slots`;
    } catch(err) {
      console.error('Error loading slots:', err);
      slotEl.innerHTML = '<option value="">Error loading slots</option>';
    }
  });
  
  // Show summary on slot selection
  $(slotEl).on('change', function() {
    updateSummary();
  });
  
  $('#patient_id, #doctor_id').on('select2:select', function() {
    updateSummary();
  });
  
  $('input[name="consultation_fee"]').on('input', function() {
    updateSummary();
  });
});

function updateSummary() {
  const patient = $('#patient_id option:selected').text();
  const doctor = $('#doctor_id option:selected').text();
  const date = $('#date_select option:selected').text();
  const slot = $('#slot_id option:selected');
  const slotText = slot.text();
  const fee = $('input[name="consultation_fee"]').val();
  
  if (patient && doctor && date && slotText && patient !== 'Search patient...' && doctor !== 'Search doctor...') {
    const summary = `
      <strong>Patient:</strong> ${patient}<br>
      <strong>Doctor:</strong> ${doctor}<br>
      <strong>Date:</strong> ${date}<br>
      <strong>Time:</strong> ${slotText}<br>
      <strong>Fee:</strong> ₱${parseFloat(fee).toLocaleString('en-PH', {minimumFractionDigits: 2})}
    `;
    $('#summary_content').html(summary);
    $('#summary_box').slideDown();
  } else {
    $('#summary_box').slideUp();
  }
}
</script>
</body>
</html>