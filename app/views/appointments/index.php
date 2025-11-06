<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Appointments — MediTrack+</title>
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
    <a class="nav-link" href="<?= base_url(); ?>/dashboard_admin">🏠 Dashboard</a>
    <a class="nav-link" href="<?= base_url(); ?>/users">👥 Users</a>
    <a class="nav-link" href="<?= base_url(); ?>/patients">🧾 Patients</a>
    <a class="nav-link" href="<?= base_url(); ?>/doctors">🩺 Doctors</a>
    <a class="nav-link active" href="<?= base_url(); ?>/appointments">📅 Appointments</a>
    <a class="nav-link" href="<?= base_url(); ?>/schedules">📆 Schedules</a>
    <a href="<?= site_url('auth/logout'); ?>" class="btn btn-danger mt-3">Logout</a>
  </nav>
</aside>

<!-- Main Content -->
<div class="main">
  <div class="topbar">
    <h4 class="mb-0">Appointments</h4>
    <?php if($_SESSION['role'] === 'admin'): ?>
      <a href="<?= base_url(); ?>/appointments/add" class="btn btn-primary btn-sm">+ Add Appointment</a>
    <?php endif; ?>
  </div>

  <div class="card-soft table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Date</th>
          <th>Patient</th>
          <th>Doctor</th>
          <th>Notes</th>
          <th>Status</th>
          <?php if($_SESSION['role'] === 'admin'): ?><th>Actions</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php if(!empty($appointments)): foreach($appointments as $a): ?>
        <tr>
          <td>
            <?php if(!empty($a['slot_date'])): ?>
              <?= htmlspecialchars($a['slot_date']) ?>
              <?php if(!empty($a['start_time']) && !empty($a['end_time'])): ?>
                (<?= htmlspecialchars($a['start_time']) ?> - <?= htmlspecialchars($a['end_time']) ?>)
              <?php endif; ?>
            <?php else: ?>
              N/A
            <?php endif; ?>
          </td>
          <td><span class="badge-patient"><?= htmlspecialchars($a['patient_name'] ?? 'N/A') ?></span></td>
          <td><span class="badge-doctor"><?= htmlspecialchars($a['doctor_name'] ?? 'N/A') ?></span></td>
          <td><?= htmlspecialchars($a['notes']) ?></td>
          <?php
            $status = $a['status'] ?? 'scheduled';
            $badgeClass = 'text-bg-warning text-dark';
            if ($status === 'completed') $badgeClass = 'text-bg-success';
            if ($status === 'cancelled') $badgeClass = 'text-bg-secondary';
          ?>
          <td><span class="badge <?= $badgeClass ?>" style="text-transform:capitalize;"><?= htmlspecialchars($status) ?></span></td>
          <?php if($_SESSION['role'] === 'admin'): ?>
          <td class="text-nowrap">
            <?php if ($status === 'scheduled'): ?>
              <a href="<?= base_url(); ?>/appointments/<?= $a['id'] ?>/complete" class="btn btn-sm btn-success me-1">Mark Done</a>
              <a href="<?= base_url(); ?>/appointments/<?= $a['id'] ?>/cancel" class="btn btn-sm btn-outline-danger me-1" onclick="return confirm('Cancel this appointment?')">Cancel</a>
            <?php else: ?>
              <button class="btn btn-sm btn-light me-1" disabled>—</button>
            <?php endif; ?>
            <a href="<?= base_url(); ?>/appointments/edit/<?= $a['id'] ?>" class="btn btn-sm btn-outline-warning me-1">✏️ Edit</a>
            <a href="<?= base_url(); ?>/appointments/delete/<?= $a['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this appointment?')">🗑 Delete</a>
          </td>
          <?php endif; ?>
        </tr>
        <?php endforeach; else: ?>
        <tr>
          <td colspan="<?= $_SESSION['role'] === 'admin' ? 7 : 6 ?>" class="text-center text-muted py-4">No appointments scheduled.</td>
        </tr>
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
