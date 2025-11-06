<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Doctors — MediTrack+</title>
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
    <a class="nav-link active" href="<?= base_url(); ?>/doctors">🩺 Doctors</a>
    <a class="nav-link" href="<?= base_url(); ?>/appointments">📅 Appointments</a>
    <a class="nav-link" href="<?= base_url(); ?>/schedules">📆 Schedules</a>
    <a href="<?= site_url('auth/logout'); ?>" class="btn btn-danger mt-3">Logout</a>
  </nav>
</aside>

<!-- Main Content -->
<div class="main">
  <div class="topbar">
    <h4>Doctors</h4>
    <div>
      <a href="<?= base_url(); ?>/doctors/add" class="btn btn-primary btn-sm">+ Add Doctor</a>
    </div>
  </div>

  <div class="card-soft table-responsive">
    <!-- Search -->
    <form class="d-flex mb-3" method="get" action="<?= base_url(); ?>/doctors">
      <input name="q" class="form-control form-control-sm me-2" placeholder="Search by name,specialty or contact" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
      <button class="btn btn-outline-secondary btn-sm">Search</button>
    </form>
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Name</th>
          <th>Specialty</th>
          <th>Contact</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if(!empty($all)): foreach($all as $d): ?>
        <tr>
          <td><?= htmlspecialchars($d['name']) ?></td>
          <td><?= htmlspecialchars($d['specialty']) ?></td>
          <td><?= htmlspecialchars($d['contact']) ?></td>
          <td>
            <a href="<?= base_url(); ?>/doctors/edit/<?= $d['id'] ?>" class="btn btn-sm btn-outline-warning">Edit</a>
            <a href="<?= base_url(); ?>/doctors/delete/<?= $d['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this doctor?')">Delete</a>
          </td>
        </tr>
        <?php endforeach; else: ?>
        <tr>
          <td colspan="5" class="text-center text-muted">No doctors found.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
</div>
  <?= $page; ?>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= base_url().'public/assets/ui.js'; ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
