<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Add User — MediTrack+</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
</head>
<body class="bg-light">
  <div class="container py-5 container-narrow">
    <h3 class="mb-4">Add New User</h3>
    <form method="post" action="<?= base_url(); ?>/users/save">
      <div class="mb-3">
        <label class="form-label">Name</label>
        <input name="name" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input name="email" type="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input name="password" type="password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Role</label>
        <select name="role" class="form-select">
          <option value="doctor">Doctor</option>
          <option value="staff">Staff</option>
        </select>
      </div>
      <button class="btn btn-primary w-100">Save User</button>
    </form>
  </div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= base_url().'public/assets/ui.js'; ?>"></script>
</body>
</html>
