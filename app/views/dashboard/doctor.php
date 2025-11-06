<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Doctor Schedule — MediTrack+</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar p-3">
  <a href="<?= base_url(); ?>/" class="d-flex align-items-center mb-4 text-decoration-none">
    <div class="brand-logo"></div>
    <div class="ms-2"><div class="brand-title">MediTrack+</div><small class="text-muted">Clinic Doctor</small></div>
  </a>
  <nav class="nav flex-column">
    <a href="<?= site_url('auth/logout'); ?>" class="btn btn-danger mt-3">Logout</a>
  </nav>
</aside>

<div class="main">
  <div class="topbar d-flex justify-content-between align-items-center">
    <h4 class="ms-3">My Schedule</h4>
    <div class="d-flex gap-2 me-3">
      <a href="<?= base_url(); ?>/schedules/manage" class="btn btn-outline-secondary">Manage Slots</a>
      <a href="<?= base_url(); ?>/appointments/doc_add" class="btn btn-primary">Add Appointment</a>
    </div>
  </div>

  <div class="p-4 card-soft table-responsive">
    <h5 class="mb-3">Upcoming Appointments</h5>
    <table class="table table-hover align-middle mb-4">
      <thead>
        <tr>
          <th>Date</th>
          <th>Patient</th>
          <th>Notes</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if(!empty($upcoming)): foreach($upcoming as $a): ?>
          <tr>
            <?php
              $slotDate  = $a['slot_date']  ?? null;
              $startTime = $a['start_time'] ?? null;
              $endTime   = $a['end_time']   ?? null;
              if (!empty($slotDate)) {
                $startStr = !empty($startTime) ? $startTime : '00:00:00';
                $ts = strtotime($slotDate . ' ' . $startStr);
                // Prepare date text with optional end time range
                if ($ts !== false) {
                  $datePart = date('M d, Y', $ts);
                  $startPart = date('H:i', $ts);
                  $range = $startPart;
                  if (!empty($endTime)) {
                    $te = strtotime($slotDate . ' ' . $endTime);
                    if ($te !== false) {
                      $range .= ' - ' . date('H:i', $te);
                    }
                  }
                  $dateText = $datePart . ' ' . $range;
                } else {
                  $dateText = '-';
                }
              } else {
                $ts = false;
                $dateText = '-';
              }
            ?>
            <td><?= htmlspecialchars($dateText) ?></td>
            <td><?= htmlspecialchars($a['patient_name'] ?? $a['patient_id']) ?></td>
            <td><?= htmlspecialchars(substr($a['notes'] ?? '',0,80)) ?></td>
            <td>
              <?php
                if ($ts === false) {
                  $status = 'Unknown';
                } else {
                  $status = $ts >= time() ? 'Upcoming' : 'Past';
                }
                $color = $status === 'Upcoming' ? 'text-success' : ($status === 'Past' ? 'text-muted' : 'text-secondary');
              ?>
              <span class="<?= $color ?>"><?= $status ?></span>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="4" class="text-muted text-center">No scheduled appointments.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <h5 class="mb-3">Past Appointments</h5>
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Date</th>
          <th>Patient</th>
          <th>Notes</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if(!empty($past)): foreach($past as $a): ?>
          <tr>
            <?php
              $slotDate  = $a['slot_date']  ?? null;
              $startTime = $a['start_time'] ?? null;
              $endTime   = $a['end_time']   ?? null;
              if (!empty($slotDate)) {
                $startStr = !empty($startTime) ? $startTime : '00:00:00';
                $ts = strtotime($slotDate . ' ' . $startStr);
                if ($ts !== false) {
                  $datePart = date('M d, Y', $ts);
                  $startPart = date('H:i', $ts);
                  $range = $startPart;
                  if (!empty($endTime)) {
                    $te = strtotime($slotDate . ' ' . $endTime);
                    if ($te !== false) {
                      $range .= ' - ' . date('H:i', $te);
                    }
                  }
                  $dateText = $datePart . ' ' . $range;
                } else {
                  $dateText = '-';
                }
              } else {
                $ts = false;
                $dateText = '-';
              }
            ?>
            <td><?= htmlspecialchars($dateText) ?></td>
            <td><?= htmlspecialchars($a['patient_name'] ?? $a['patient_id']) ?></td>
            <td><?= htmlspecialchars(substr($a['notes'] ?? '',0,80)) ?></td>
            <td>
              <?php
                if ($ts === false) {
                  $status = 'Unknown';
                } else {
                  $status = $ts >= time() ? 'Upcoming' : 'Past';
                }
                $color = $status === 'Upcoming' ? 'text-success' : ($status === 'Past' ? 'text-muted' : 'text-secondary');
              ?>
              <span class="<?= $color ?>"><?= $status ?></span>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="4" class="text-muted text-center">No past appointments.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= base_url().'public/assets/ui.js'; ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
