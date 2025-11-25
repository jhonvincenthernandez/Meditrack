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
<?php include APP_DIR . 'views/_sidebar.php'; ?>

<!-- Main Content -->
<div class="main">
  <?php $topbar_title = 'Add Schedule'; include APP_DIR . 'views/_topbar.php'; ?>

  <?php if (!empty($_SESSION['slot_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['slot_success']; unset($_SESSION['slot_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['slot_warning'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <?= $_SESSION['slot_warning']; unset($_SESSION['slot_warning']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

  <div class="card-soft">
  <form method="post" action="<?= site_url('schedules/save'); ?>">
      <div class="row g-3">
        <div class="col-md-6">
          <label for="doctor_id" class="form-label">Doctor</label>
          <select id="doctor_id" name="doctor_id" class="form-select" required>
            <option value="" disabled <?php if (empty($old['doctor_id'])) echo 'selected'; ?>>Choose doctor...</option>
            <?php if (!empty($doctors) && is_array($doctors)): ?>
              <?php foreach ($doctors as $d): ?>
                <?php $did = htmlspecialchars($d['id']); $dname = htmlspecialchars($d['name'] ?? (($d['first_name'] ?? '') . ' ' . ($d['last_name'] ?? '') ?: 'Doctor #'.$d['id'])); ?>
                <option value="<?= $did; ?>" <?php if (!empty($old['doctor_id']) && (string)$old['doctor_id'] === (string)$d['id']) echo 'selected'; ?>>
                  <?= $dname; ?>
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
      </div>

      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary">Save Schedule</button>
        <a href="<?= base_url(); ?>/schedules" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Select2 for searchable dropdowns -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="<?= base_url().'public/assets/ui.js'; ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  $(document).ready(function() {
    // Initialize Select2 on doctor select for searching/filtering
    $('#doctor_id').select2({
      placeholder: 'Choose doctor...',
      allowClear: true,
      width: '100%'
    });

    // If there is an old selected value, ensure Select2 shows it (handled by option[selected])
  });
</script>
</body>
</html>