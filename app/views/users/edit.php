<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit User — MediTrack+</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
</head>
<body class="bg-light">
  <div class="container py-5 container-narrow">
    <h3 class="mb-4">Edit User</h3>

    <form method="post" action="<?= base_url(); ?>/users/update/<?= (int)$user['id'] ?>">
      <div class="mb-3">
        <label class="form-label">Name</label>
        <input name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Email</label>
        <input name="email" type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Password <small class="text-muted">(leave blank to keep current)</small></label>
        <input name="password" type="password" class="form-control" placeholder="••••••">
      </div>

      <div class="mb-3">
        <label class="form-label">Role</label>
        <select name="role" class="form-select">
          <option value="doctor" <?= $user['role']==='doctor' ? 'selected' : '' ?>>Doctor</option>
          <option value="staff" <?= $user['role']==='staff' ? 'selected' : '' ?>>Staff</option>
        </select>
      </div>

      <div class="d-flex gap-2">
        <a href="<?= base_url(); ?>/users" class="btn btn-outline-secondary w-50">Cancel</a>
        <button class="btn btn-primary w-50">Save Changes</button>
      </div>
    </form>
  </div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= base_url().'public/assets/ui.js'; ?>"></script>
</body>
</html>
