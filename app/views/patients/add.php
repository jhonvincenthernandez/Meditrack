<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Add Patient — MediTrack+</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
</head>
<body>
  

  <?php
    $topbar_title = 'Add Patient';
    $topbar_class = 'd-flex justify-content-center align-items-center';
    include APP_DIR . 'views/_topbar.php';
  ?>

    <div class="p-4">
  <div class="card-soft container-narrow">
        <form method="post" action="<?= base_url(); ?>/patients/save" class="needs-validation" novalidate>
          <?php if(!empty($flash_success)): ?><div class="alert alert-success"><?= htmlspecialchars($flash_success) ?></div><?php endif; ?>
          <?php if(!empty($flash_error)): ?><div class="alert alert-danger"><?= htmlspecialchars($flash_error) ?></div><?php endif; ?>
          <?php if(!empty($errors) && is_array($errors)): ?>
            <div class="alert alert-danger">
              <?php foreach($errors as $err): ?><div><?= htmlspecialchars($err) ?></div><?php endforeach; ?>
            </div>
          <?php endif; ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Full name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($old['name'] ?? '') ?>">
            </div>
            <div class="col-md-2">
              <label class="form-label">Age <span class="text-danger">*</span></label>
              <input type="number" name="age" class="form-control" min="0" value="<?= htmlspecialchars($old['age'] ?? '') ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Gender</label>
              <select name="gender" class="form-select" required>
                <option value="">Select</option>
                <option value="Male" <?= (isset($old['gender']) && $old['gender'] === 'Male') ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= (isset($old['gender']) && $old['gender'] === 'Female') ? 'selected' : '' ?>>Female</option>
                <option value="Other" <?= (isset($old['gender']) && $old['gender'] === 'Other') ? 'selected' : '' ?>>Other</option>
              </select>
            </div>

            <div class="col-12">
              <label class="form-label">Contact <span class="text-danger">*</span></label>
              <input type="text" maxlength="11" name="contact" class="form-control" pattern="^09[0-9]{9}$" required value="<?= htmlspecialchars($old['contact'] ?? '') ?>">
              <div class="form-text">Format: 09xxxxxxxxx</div>
            </div>

            

            <div class="col-12">
              <label class="form-label">Medical history</label>
              <textarea name="medical_history" rows="4" class="form-control"><?= htmlspecialchars($old['medical_history'] ?? '') ?></textarea>
            </div>

            <div class="col-12 d-flex gap-2">
              <button class="btn btn-primary">Save</button>
              <a class="btn btn-outline-secondary" href="<?= base_url(); ?>/patients">Cancel</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="<?= base_url().'public/assets/ui.js'; ?>"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function () {
      'use strict'
      var forms = document.querySelectorAll('.needs-validation')
      Array.prototype.slice.call(forms)
        .forEach(function (form) {
          form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
              event.preventDefault();
              event.stopPropagation();
            }
            form.classList.add('was-validated')
          }, false)
        })
    })();
  </script>
</body>
</html>
