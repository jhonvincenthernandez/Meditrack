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
        <form method="post" action="<?= base_url(); ?>/doctors/save">
          <div class="mb-3">
            <label class="form-label">Link to User Account</label>
            <select id="user_id" name="user_id" class="form-select" required>
              <option value="">-- Select User or Add Another User --</option>
              <?php foreach($users as $u): ?>
                <option value="<?= $u['id'] ?>" data-name="<?= htmlspecialchars($u['name']) ?>"><?= htmlspecialchars($u['name']) ?> (<?= $u['email'] ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Doctor Name</label>
            <input type="text" id="doctor_name_display" class="form-control" placeholder="Select a user to populate" disabled>
            <input type="hidden" id="doctor_name" name="name" value="">
          </div>

          <div class="mb-3">
            <label class="form-label">Specialty</label>
            <input type="text" name="specialty" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label">Contact</label>
            <input type="text" maxlength="11" name="contact" class="form-control">
          </div>

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
        var nameInput = document.getElementById('doctor_name');
        var nameDisplay = document.getElementById('doctor_name_display');
        function syncName(){
          var opt = userSel.options[userSel.selectedIndex];
          var n = opt && opt.getAttribute('data-name') ? opt.getAttribute('data-name') : '';
          nameInput.value = n;
          nameDisplay.value = n;
        }
        userSel && userSel.addEventListener('change', syncName);
        // initialize on load
        syncName();
      })();
      </script>
    </div>
  </div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= base_url().'public/assets/ui.js'; ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
