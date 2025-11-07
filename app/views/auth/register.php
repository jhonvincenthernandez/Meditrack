<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediTrack+ | Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
</head>
<body class="login-page">

  <div class="login-card">
    <h3 class="mb-2 text-center text-accent">Create Account</h3>
    <p class="text-center text-muted mb-4">Register as Doctor or Staff</p>

    <?php if(!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('auth/register'); ?>">
      <div class="mb-3">
        <label for="name" class="form-label fw-semibold">Username</label>
        <input type="text" class="form-control" id="name" name="name" placeholder="Username" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label fw-semibold">Email</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label fw-semibold">Password</label>
        <input type="password" class="form-control" id="password" name="password" minlength="6" required>
      </div>
      <div class="mb-3">
        <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6" required>
      </div>
      <div class="mb-3">
        <label for="role" class="form-label fw-semibold">Role</label>
        <select class="form-select" id="role" name="role" required>
          <option value="" disabled <?= empty($_POST['role']) ? 'selected' : '' ?>>Select role</option>
          <option value="doctor" <?= (($_POST['role'] ?? '') === 'doctor') ? 'selected' : '' ?>>Doctor</option>
          <option value="staff" <?= (($_POST['role'] ?? '') === 'staff') ? 'selected' : '' ?>>Staff</option>
        </select>
      </div>
      <button type="submit" class="btn btn-login w-100 mt-2">Register</button>
    </form>

    <div class="text-center mt-3">
      <a href="<?= site_url('auth/login'); ?>" class="text-decoration-none">Already have an account? Login</a>
    </div>

    <p class="text-center mt-4 small text-muted">© <?= date('Y'); ?> MediTrack+. All rights reserved.</p>
  </div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= base_url().'public/assets/ui.js'; ?>"></script>
</body>
</html>
