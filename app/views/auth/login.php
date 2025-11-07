<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediTrack+ | Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
</head>
<body class="login-page">

  <div class="login-card">
  <h3 class="mb-2 text-center text-accent">MediTrack+ Login</h3>
  <?php if(!empty($reg_success)): ?>
    <div class="alert alert-success text-center"><?= htmlspecialchars($reg_success) ?></div>
  <?php endif; ?>

    <?php if(isset($error)): ?>
      <div class="alert alert-danger text-center"><?= $error ?></div>
    <?php endif; ?>

    <form action="<?= site_url('auth/login'); ?>" method="post">
      <div class="mb-3">
        <label for="email" class="form-label fw-semibold">Email</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label fw-semibold">Password</label>
        <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
      </div>
      <button type="submit" class="btn btn-login w-100 mt-3">Login</button>
    </form>

    <div class="text-center mt-3">
      <a href="<?= site_url('auth/register'); ?>" class="text-decoration-none">Need an account? Register</a>
    </div>

    <p class="text-center mt-4 small text-muted">© <?= date('Y'); ?> MediTrack+. All rights reserved.</p>
  </div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= base_url().'public/assets/ui.js'; ?>"></script>
</body>
</html>
