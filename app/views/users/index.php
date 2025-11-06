<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Users — MediTrack+</title>
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
    <a class="nav-link active" href="<?= base_url(); ?>/users">👥 Users</a>
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
    <h4>Manage Users</h4>
    <div>
      <a href="<?= base_url(); ?>/users/add_form" class="btn btn-primary btn-sm">+ Add User</a>
    </div>
  </div>

  <div class="card-soft table-responsive">
    <!-- Search -->
    <form class="d-flex mb-3" method="get" action="<?= base_url(); ?>/users">
      <input name="q" class="form-control form-control-sm me-2" placeholder="Search by name or email" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
      <button class="btn btn-outline-secondary btn-sm">Search</button>
    </form>
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if(!empty($users)): foreach($users as $u): ?>
        <tr>
          <td><?= htmlspecialchars($u['name']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td>
            <?php
              $role_class = match($u['role']) {
                'admin' => 'role-admin',
                'doctor' => 'role-doctor',
                'staff' => 'role-staff',
                default => 'badge-secondary',
              };
            ?>
            <span class="badge badge-role <?= $role_class ?>"><?= htmlspecialchars($u['role']) ?></span>
          </td>
          <td class="action-buttons">
            <?php if($u['role'] !== 'admin'): ?>
              <a href="<?= base_url(); ?>/users/edit_form/<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
              <a href="<?= base_url(); ?>/users/delete/<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this user?')">Delete</a>
            <?php else: ?>
              <span class="text-muted">Protected</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; else: ?>
        <tr>
          <td colspan="5" class="text-center text-muted">No users found.</td>
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
