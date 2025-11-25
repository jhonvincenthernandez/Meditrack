<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Patients — MediTrack+</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
</head>
<body>

<?php include APP_DIR . 'views/_sidebar.php'; ?>

<!-- Main Content -->
<div class="main">
  <div class="topbar">
    <h4>Patients</h4>
    <div>
      <a href="<?= base_url(); ?>/patients/add" class="btn btn-primary btn-sm">+ Add Patient</a>
    </div>
  </div>

  <div class="card-soft">
    <?php if (!empty($flash_success)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($flash_success) ?></div>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($flash_error) ?></div>
    <?php endif; ?>
    <!-- Search -->
    <form class="d-flex mb-3" method="get" action="<?= base_url(); ?>/patients">
      <input name="q" class="form-control form-control-sm me-2" placeholder="Search by name,age,gender or contact" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
      <button class="btn btn-outline-secondary btn-sm">Search</button>
    </form>

    <!-- Patients Table -->
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Name</th>
            <th>Age</th>
            <th>Gender</th>
            <th>Contact</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if(!empty($patients)): foreach($patients as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= htmlspecialchars($p['age']) ?></td>
            <td><?= htmlspecialchars($p['gender']) ?></td>
            <td><?= htmlspecialchars($p['contact']) ?></td>
            <td>
              <a href="<?= base_url(); ?>/patients/edit/<?= $p['id'] ?>" class="btn btn-sm btn-outline-warning">Edit</a>
              <a href="<?= base_url(); ?>/patients/delete/<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this patient?')">Delete</a>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr>
            <td colspan="6" class="text-center text-muted">No patients found.</td>
          </tr>
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
