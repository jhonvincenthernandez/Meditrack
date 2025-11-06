<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Dashboard — MediTrack+</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
</head>
<body>

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
    <a class="nav-link active" href="<?= base_url(); ?>/dashboard_admin">🏠 Dashboard</a>
    <a class="nav-link" href="<?= base_url(); ?>/users">👥 Users</a>
    <a class="nav-link" href="<?= base_url(); ?>/patients">🧾 Patients</a>
    <a class="nav-link" href="<?= base_url(); ?>/doctors">🩺 Doctors</a>
  <a class="nav-link" href="<?= base_url(); ?>/appointments">📅 Appointments</a>
  <a class="nav-link" href="<?= base_url(); ?>/schedules">📆 Schedules</a>
    <a href="<?= site_url('auth/logout'); ?>" class="btn btn-danger mt-3">Logout</a>
  </nav>
</aside>

<!-- Main Content -->
<div class="main">
  <div class="topbar">
    <h4>Dashboard</h4>
    <span class="text-muted small">Today: <?= date('M d, Y'); ?></span>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card-soft d-flex justify-content-between align-items-center">
        <div>
          <small class="text-muted">Total Patients</small>
          <h3 class="mt-2"><?= $patient_count ?? 0; ?></h3>
        </div>
  <div class="fs-30 text-accent">👥</div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card-soft d-flex justify-content-between align-items-center">
        <div>
          <small class="text-muted">Total Doctors</small>
          <h3 class="mt-2"><?= $doctor_count ?? 0; ?></h3>
        </div>
  <div class="fs-30 text-green">🩺</div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card-soft d-flex justify-content-between align-items-center">
        <div>
          <small class="text-muted">Upcoming Appointments</small>
          <h3 class="mt-2"><?= $appointment_count ?? 0; ?></h3>
        </div>
  <div class="fs-30 text-amber">📅</div>
      </div>
    </div>
  </div>

  <div class="card-soft">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="m-0">Upcoming Appointments</h5>
      <small class="text-muted"><?= date('F Y'); ?></small>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Date</th>
            <th>Patient</th>
            <th>Doctor</th>
            <th>Notes</th>
          </tr>
        </thead>
        <tbody>
          <?php if(!empty($upcoming) && is_array($upcoming)): ?>
            <?php foreach(array_slice($upcoming,0,5) as $a): ?>
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
                    if ($te !== false) { $range .= ' - ' . date('H:i', $te); }
                  }
                  $dateText = $datePart . ' ' . $range;
                } else { $dateText = 'N/A'; }
              } else { $dateText = 'N/A'; }
            ?>
            <tr>
              <td><?= htmlspecialchars($dateText) ?></td>
              <td><?= htmlspecialchars($a['patient_name'] ?? 'N/A'); ?></td>
              <td><?= htmlspecialchars($a['doctor_name'] ?? 'N/A'); ?></td>
              <td><?= htmlspecialchars(substr($a['notes'] ?? '',0,80)); ?></td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="4" class="text-center text-muted">No upcoming appointments</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card-soft mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="m-0">Past Appointments</h5>
      <small class="text-muted"><?= date('F Y'); ?></small>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Date</th>
            <th>Patient</th>
            <th>Doctor</th>
            <th>Notes</th>
          </tr>
        </thead>
        <tbody>
          <?php if(!empty($past) && is_array($past)): ?>
            <?php foreach(array_slice($past,0,5) as $a): ?>
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
                    if ($te !== false) { $range .= ' - ' . date('H:i', $te); }
                  }
                  $dateText = $datePart . ' ' . $range;
                } else { $dateText = 'N/A'; }
              } else { $dateText = 'N/A'; }
            ?>
            <tr>
              <td><?= htmlspecialchars($dateText) ?></td>
              <td><?= htmlspecialchars($a['patient_name'] ?? 'N/A'); ?></td>
              <td><?= htmlspecialchars($a['doctor_name'] ?? 'N/A'); ?></td>
              <td><?= htmlspecialchars(substr($a['notes'] ?? '',0,80)); ?></td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="4" class="text-center text-muted">No past appointments</td></tr>
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
