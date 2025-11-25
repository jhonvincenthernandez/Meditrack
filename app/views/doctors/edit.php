<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit Doctor — MediTrack+</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
</head>
<body>
  

  <div class="topbar">
      <h4 class="ms-3">Edit Doctor</h4>
    </div>

    <div class="p-4">
  <div class="card-soft container-narrow">
        <form method="post" action="<?= base_url(); ?>/doctors/update/<?= $doctor['id'] ?>" class="needs-validation" novalidate>
          <?php if(!empty($flash_success)): ?><div class="alert alert-success"><?= htmlspecialchars($flash_success) ?></div><?php endif; ?>
          <?php if(!empty($flash_error)): ?><div class="alert alert-danger"><?= htmlspecialchars($flash_error) ?></div><?php endif; ?>
          <?php if(!empty($errors) && is_array($errors)): ?>
            <div class="alert alert-danger">
              <?php foreach($errors as $err): ?>
                <div><?= htmlspecialchars($err) ?></div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <div class="mb-3">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($doctor['name'] ?? '') ?>" required>
            
          </div>
          <div class="mb-3">
            <label class="form-label">Specialty <span class="text-danger">*</span></label>
            <select name="specialty" class="form-select" required>
              <option value="">-- Select specialty --</option>
              <?php foreach($specialties as $s): ?>
                <option value="<?= htmlspecialchars($s) ?>" <?= (isset($old['specialty']) && $old['specialty'] === $s) || ($doctor['specialty'] === $s && empty($old['specialty'])) ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
              <?php endforeach; ?>
            </select>
            
          </div>
          <div class="mb-3">
            <label class="form-label">Contact <span class="text-danger">*</span></label>
            <input type="text" name="contact"  maxlength="11" pattern="^09[0-9]{9}$" class="form-control" value="<?= htmlspecialchars($old['contact'] ?? $doctor['contact'] ?? '') ?>" required>
            <div class="form-text">Format: 09xxxxxxxxx</div>
            
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-primary">Update</button>
            <a class="btn btn-outline-secondary" href="<?= base_url(); ?>/doctors">Cancel</a>
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
