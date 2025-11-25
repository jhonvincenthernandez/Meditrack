<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Doctors — MediTrack+</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" crossorigin="anonymous">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
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
    <a class="nav-link active" href="<?= base_url(); ?>/doctors">🩺 Doctors</a>
    <a class="nav-link" href="<?= base_url(); ?>/appointments">📅 Appointments</a>
    <a class="nav-link" href="<?= base_url(); ?>/schedules">📆 Schedules</a>
    <a class="nav-link" href="<?= base_url(); ?>/payments/records">💰 Payment Records</a>
    <a href="<?= site_url('auth/logout'); ?>" class="btn btn-danger mt-3">Logout</a>
  </nav>
</aside>

<!-- Main Content -->
<div class="main">
  <?php
    $topbar_title = 'Doctors';
    $topbar_right = '<a href="' . base_url() . '/doctors/add" class="btn btn-primary btn-sm">+ Add Doctor</a>';
    include APP_DIR . 'views/_topbar.php';
  ?>

  <div class="card-soft">
    <?php if (!empty($flash_success)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($flash_success) ?></div>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($flash_error) ?></div>
    <?php endif; ?>
    <!-- Search & Filter -->
    <form class="row g-3 mb-4" method="get" action="<?= base_url(); ?>/doctors">
      <div class="col-md-8">
        <input name="q" class="form-control" placeholder="Search by name or contact" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
      </div>
      <div class="col-md-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-grow-1">Search</button>
        <a href="<?= base_url(); ?>/doctors" class="btn btn-outline-secondary">Reset</a>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Doctor</th>
            <th>Specialty</th>
            <th>Contact</th>
            <th>Statistics</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if(!empty($all)): foreach($all as $d): ?>
          <?php
            $appointmentCount = (int)($d['appointment_count'] ?? 0);
            $availableSlots = (int)($d['available_slots'] ?? 0);
            $hasAppointments = $appointmentCount > 0;
          ?>
          <tr>
            <td>
              <div class="fw-semibold"><?= htmlspecialchars($d['name']) ?></div>
              <small class="text-muted">ID: #<?= (int)$d['id'] ?></small>
            </td>
            <td>
              <span class="badge bg-primary bg-opacity-10 text-primary"><?= htmlspecialchars($d['specialty']) ?></span>
            </td>
            <td><?= htmlspecialchars($d['contact']) ?></td>
            <td>
              <div class="small">
                <div class="text-muted">📅 <?= $appointmentCount ?> appointment<?= $appointmentCount !== 1 ? 's' : '' ?></div>
                <div class="text-muted">🕐 <?= $availableSlots ?> available slot<?= $availableSlots !== 1 ? 's' : '' ?></div>
              </div>
            </td>
            <td class="text-nowrap">
              <a href="<?= base_url(); ?>/doctors/edit/<?= $d['id'] ?>" class="btn btn-sm btn-outline-secondary">✏️ Edit</a>
              <?php if($hasAppointments): ?>
                <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Cannot delete: has active appointments">🗑 Delete</button>
              <?php else: ?>
                <a href="<?= base_url(); ?>/doctors/delete/<?= $d['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete Dr. <?= htmlspecialchars($d['name']) ?>? This action cannot be undone.')">🗑 Delete</a>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr>
            <td colspan="5" class="text-center text-muted py-4">No doctors found.</td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?= $page ?? ''; ?>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="<?= base_url().'public/assets/ui.js'; ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
