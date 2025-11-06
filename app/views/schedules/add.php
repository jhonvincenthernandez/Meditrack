<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Add Schedule — MediTrack+</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
</head>
<body>


<!-- Main Content -->
<div class="main">
  <div class="topbar">
    <h4>Add Schedule</h4>
    <div></div>
  </div>

  <div class="card-soft">
  <form method="post" action="<?= site_url('schedules/save'); ?>">
      <div class="row g-3">
        <div class="col-md-6">
          <label for="doctor_id" class="form-label">Doctor</label>
          <select id="doctor_id" name="doctor_id" class="form-select" required>
            <option value="" disabled selected>Choose doctor...</option>
            <?php if (!empty($doctors) && is_array($doctors)): ?>
              <?php forEach ($doctors as $d): ?>
                <option value="<?= htmlspecialchars($d['id']); ?>">
                  <?= htmlspecialchars($d['name'] ?? ($d['first_name'].' '.$d['last_name'] ?? 'Doctor #'.$d['id'])); ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>

        <div class="col-md-4">
          <label for="date" class="form-label">Date</label>
          <input type="date" id="date" name="date" class="form-control" min="<?= date('Y-m-d'); ?>" required>
        </div>
        <div class="col-md-4">
          <label for="start_time" class="form-label">Start Time</label>
          <input type="time" id="start_time" name="start_time" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label for="end_time" class="form-label">End Time</label>
          <input type="time" id="end_time" name="end_time" class="form-control" required>
        </div>

        <div class="col-12">
          <label for="notes" class="form-label">Notes (optional)</label>
          <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Availability details or remarks"></textarea>
        </div>
      </div>

      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary">Save Schedule</button>
        <a href="<?= base_url(); ?>/schedules" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= base_url().'public/assets/ui.js'; ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>