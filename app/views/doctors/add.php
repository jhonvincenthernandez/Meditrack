<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Add Doctor — MediTrack+</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
</head>
<body>
  

  <div class="topbar d-flex justify-content-center align-items-center">
      <h4 class="ms-3">Add Doctor</h4>
    </div>

    <div class="p-4">
  <div class="card-soft container-narrow">
        <form method="post" action="<?= base_url(); ?>/doctors/save" class="needs-validation" novalidate>
          <?php if(!empty($errors) && is_array($errors)): ?>
            <div class="alert alert-danger">
              <?php foreach($errors as $err): ?>
                <div><?= htmlspecialchars($err) ?></div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <div class="mb-3">
            <label class="form-label">Link to User Account <small class="text-muted">(optional)</small></label>
            <select id="user_id" name="user_id" class="form-select">
              <option value="">-- Select User or Add Another User --</option>
              <?php foreach($users as $u): ?>
                <option value="<?= $u['id'] ?>" data-name="<?= htmlspecialchars($u['name']) ?>" <?= (isset($old['user_id']) && $old['user_id'] == $u['id']) ? 'selected' : '' ?>><?= htmlspecialchars($u['name']) ?> (<?= $u['email'] ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Doctor Name <span class="text-danger">*</span></label>
            <input type="text" id="doctor_name_display" name="name" class="form-control" placeholder="Full name" required value="<?= htmlspecialchars($old['name'] ?? '') ?>">
            
          </div>

          <div class="mb-3">
            <label class="form-label">Specialty <span class="text-danger">*</span></label>
            <select name="specialty" id="specialty" class="form-select" required>
              <option value="">-- Select specialty --</option>
              <?php foreach($specialties as $s): ?>
              <option value="<?= htmlspecialchars($s) ?>" <?= (isset($old['specialty']) && $old['specialty'] === $s) ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
              <?php endforeach; ?>
            </select>
            
          </div>

          <div class="mb-3">
            <label class="form-label">Contact <span class="text-danger">*</span></label>
            <input type="text" name="contact" id="contact" pattern="^09[0-9]{9}$" maxlength="11" class="form-control" required value="<?= htmlspecialchars($old['contact'] ?? '') ?>">
            <div class="form-text">Format: 09xxxxxxxxx</div>
            
          </div>

          <?php if(!empty($flash_success)): ?><div class="alert alert-success"><?= htmlspecialchars($flash_success) ?></div><?php endif; ?>
          <?php if(!empty($flash_error)): ?><div class="alert alert-danger"><?= htmlspecialchars($flash_error) ?></div><?php endif; ?>

          <div class="d-flex gap-2">
            <button class="btn btn-primary">Save</button>
            <a class="btn btn-outline-secondary" href="<?= base_url(); ?>/doctors">Cancel</a>
          </div>
        </form>
      </div>
      <script>
      // Keep doctor name in sync with selected user
      (function(){
  var userSel = document.getElementById('user_id');
  var nameDisplay = document.getElementById('doctor_name_display');
        function syncName(){
          var opt = userSel.options[userSel.selectedIndex];
          var n = opt && opt.getAttribute('data-name') ? opt.getAttribute('data-name') : '';
          // when a user is selected, we will overwrite the name input value
          if (n !== '') {
            nameDisplay.value = n;
          }
        }
        userSel && userSel.addEventListener('change', syncName);
        // initialize on load
        syncName();
      })();

      // Bootstrap client-side validation
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
    </div>
  </div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= base_url().'public/assets/ui.js'; ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
