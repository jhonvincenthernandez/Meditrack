<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
<?php /* Removed deprecated APPPATH include. Theme should be provided by the parent layout or global head. */ ?>
<?php $a = $appointment ?? null; ?>
<div class="card-soft">
    <form method="post" action="<?= base_url(); ?>/appointments/update/<?= $a['id'] ?>">
        <div class="row g-3">
            <div class="col-md-6">
                <label>Patient</label>
                <select name="patient_id" class="form-select" required>
                    <?php foreach($patients as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $a['patient_id']==$p['id']?'selected':'' ?>><?= htmlspecialchars($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label>Doctor</label>
                <select name="doctor_id" id="doctor_id" class="form-select" required>
                    <?php foreach($doctors as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= $a['doctor_id']==$d['id']?'selected':'' ?>><?= htmlspecialchars($d['name']) ?> — <?= htmlspecialchars($d['specialty']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label>Date</label>
                <select id="date_select" class="form-select" required>
                    <?php if(!empty($a['slot_date'])): ?>
                        <option value="<?= htmlspecialchars($a['slot_date']) ?>" selected><?= htmlspecialchars($a['slot_date']) ?></option>
                    <?php else: ?>
                        <option value="">-- Choose Date --</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label>Available Slot</label>
                <select name="slot_id" id="slot_id" class="form-select" required>
                    <?php if(!empty($a['slot_id'])): ?>
                        <option value="<?= $a['slot_id'] ?>" selected><?= htmlspecialchars(($a['start_time'] ?? '') . ' - ' . ($a['end_time'] ?? '')) ?></option>
                    <?php else: ?>
                        <option value="">-- Choose Available Slot --</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-12">
                <label>Notes</label>
                <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($a['notes']) ?></textarea>
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-primary">Update</button>
                <a href="<?= base_url(); ?>/appointments" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

<script>
(function(){
  const doctorEl = document.getElementById('doctor_id');
  const dateEl = document.getElementById('date_select');
  const slotEl = document.getElementById('slot_id');
  const currentDate = '<?= isset($a['slot_date']) ? addslashes($a['slot_date']) : '' ?>';
  const currentSlotId = '<?= isset($a['slot_id']) ? (int)$a['slot_id'] : '' ?>';

  async function loadDates() {
    const doctorId = doctorEl.value;
    if (!doctorId) return;
    const res = await fetch('<?= base_url(); ?>/appointments/getDoctorDates/' + doctorId);
    const dates = await res.json();
    // Preserve current date if present
    dateEl.innerHTML = '<option value="">-- Choose Date --</option>';
    if (currentDate) {
      const opt = document.createElement('option');
      opt.value = currentDate;
      opt.textContent = currentDate + ' (current)';
      opt.selected = true;
      dateEl.appendChild(opt);
    }
    if (dates && dates.length) {
      dates.forEach(d => {
        // avoid duplicate of current date
        if (d.date !== currentDate) {
          const opt = document.createElement('option');
          opt.value = d.date;
          opt.textContent = d.date;
          dateEl.appendChild(opt);
        }
      });
    }
  }

  async function loadSlots() {
    const doctorId = doctorEl.value;
    const date = dateEl.value || currentDate;
    if (!doctorId || !date) return;
    const res = await fetch(`<?= base_url(); ?>/appointments/getAvailableSlots/${doctorId}/${date}`);
    const slots = await res.json();
    // Preserve current slot if present
    slotEl.innerHTML = '<option value="">-- Choose Available Slot --</option>';
    if (currentSlotId) {
      const opt = document.createElement('option');
      opt.value = currentSlotId;
      opt.textContent = '<?= isset($a['start_time']) ? addslashes($a['start_time']) : '' ?> - <?= isset($a['end_time']) ? addslashes($a['end_time']) : '' ?> (current)';
      opt.selected = true;
      slotEl.appendChild(opt);
    }
    if (slots && slots.length) {
      slots.forEach(s => {
        if (parseInt(s.id) !== parseInt(currentSlotId)) {
          const opt = document.createElement('option');
          opt.value = s.id;
          opt.textContent = `${s.start_time} - ${s.end_time}`;
          slotEl.appendChild(opt);
        }
      });
    }
  }

  doctorEl.addEventListener('change', () => { loadDates(); slotEl.innerHTML = '<option value="">-- Choose Available Slot --</option>'; });
  dateEl.addEventListener('change', () => { loadSlots(); });

  // Initialize
  loadDates().then(loadSlots);
})();
</script>
