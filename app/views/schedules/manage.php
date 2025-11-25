<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>My Schedule Slots — MediTrack+</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
</head>
<body>

<!-- Sidebar -->
<?php include APP_DIR . 'views/_sidebar.php'; ?>

<div class="main">
  <?php
    $topbar_title = 'My Schedule Slots';
    $topbar_right = '<a href="' . base_url() . '/dashboard_doctor" class="btn btn-outline-secondary">Back to Dashboard</a>';
    $topbar_class = 'd-flex justify-content-between align-items-center';
    include APP_DIR . 'views/_topbar.php';
  ?>

  <div class="p-4">
    <?php if (!empty($flash_warning)): ?>
      <div class="alert alert-warning"><?= htmlspecialchars($flash_warning) ?></div>
    <?php endif; ?>
    <?php if (!empty($flash_success)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($flash_success) ?></div>
    <?php endif; ?>

    <div class="card-soft mb-4">
      <h5 class="mb-3">Add a Slot</h5>
      <form method="post" action="<?= site_url('schedules/doctor_save'); ?>" class="row g-3">
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
          <button type="submit" class="btn btn-primary">Add Slot</button>
        </div>
      </form>
    </div>

    <div class="card-soft table-responsive">
      <h5 class="mb-3">Your Slots</h5>
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($slots)): foreach ($slots as $s): ?>
            <?php
              $dateText = htmlspecialchars(date('M d, Y', strtotime($s['date'])));
              $timeText = htmlspecialchars(substr($s['start_time'],0,5) . ' - ' . substr($s['end_time'],0,5));
              $isBooked = (int)($s['is_booked'] ?? 0) === 1;
              $slotStartTs = strtotime(($s['date'] ?? '') . ' ' . ($s['start_time'] ?? '00:00:00'));
              $isPast = $slotStartTs !== false && $slotStartTs <= time();
            ?>
            <tr>
              <td><?= $dateText ?></td>
              <td><?= $timeText ?></td>
              <td>
                <?php $apptStatus = $s['appt_status'] ?? null; ?>
                <?php if ($apptStatus === 'completed'): ?>
                  <span class="badge text-bg-success">Done</span>
                <?php elseif ($apptStatus === 'cancelled'): ?>
                  <span class="badge text-bg-secondary">Cancelled</span>
                <?php elseif ($isPast): ?>
                  <span class="badge bg-secondary">Past</span>
                <?php elseif ($isBooked): ?>
                  <span class="badge bg-secondary">Booked</span>
                <?php else: ?>
                  <span class="badge bg-success">Open</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if (!$isBooked): ?>
                  <a href="<?= base_url(); ?>/schedules/doctor_delete/<?= (int)$s['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this slot?');">Delete</a>
                <?php else: ?>
                  <button class="btn btn-sm btn-outline-secondary" disabled>Delete</button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="4" class="text-center text-muted">No slots yet. Add one above.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= base_url().'public/assets/ui.js'; ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
