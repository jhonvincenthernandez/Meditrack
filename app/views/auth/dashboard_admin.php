<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Dashboard — MediTrack+</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
</head>
<body>
  <div class="main py-4">
    <div class="topbar"><h4 class="m-0">Admin Dashboard</h4><span class="text-muted small">Today: <?= date('M d, Y'); ?></span></div>
    <div class="card-soft">
    <h3>Welcome, Admin <?= $this->session->userdata('name'); ?> 👋</h3>
    <p class="text-muted">Manage doctors and staff here.</p>
    <a href="<?= base_url(); ?>/users" class="btn btn-primary">Manage Users</a>
    <a href="<?= base_url(); ?>/auth/logout" class="btn btn-outline-danger float-end">Logout</a>
    </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="<?= base_url().'public/assets/ui.js'; ?>"></script>
  </body>
</html>
