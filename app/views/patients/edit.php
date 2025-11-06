<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit Patient — MediTrack+</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
</head>
<body>
  

  <div class="topbar d-flex justify-content-between align-items-center">
      <h4>Edit Patient</h4>
    </div>

    <div class="p-4">
  <div class="card-soft container-narrow">
        <form method="post" action="<?= base_url(); ?>/patients/update/<?= $patient['id'] ?>">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Full name</label>
              <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($patient['name'] ?? '') ?>" required>
            </div>
            <div class="col-md-2">
              <label class="form-label">Age</label>
              <input type="number" name="age" class="form-control" value="<?= htmlspecialchars($patient['age'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Gender</label>
              <select name="gender" class="form-select">
                <option value="">Select</option>
                <option <?= ($patient['gender']=='Male')?'selected':'' ?>>Male</option>
                <option <?= ($patient['gender']=='Female')?'selected':'' ?>>Female</option>
                <option <?= ($patient['gender']=='Other')?'selected':'' ?>>Other</option>
              </select>
            </div>

            <div class="col-12">
              <label class="form-label">Contact</label>
              <input type="text" maxlength="11" name="contact" class="form-control" value="<?= htmlspecialchars($patient['contact'] ?? '') ?>">
            </div>

           

            <div class="col-12">
              <label class="form-label">Medical history</label>
              <textarea name="medical_history" rows="4" class="form-control"><?= htmlspecialchars($patient['medical_history'] ?? '') ?></textarea>
            </div>

            <div class="col-12 d-flex gap-2">
              <button class="btn btn-primary">Update</button>
              <a class="btn btn-outline-secondary" href="<?= base_url(); ?>/patients">Cancel</a>
            </div>
          </div>
        </form>
      </div>
    </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="<?= base_url().'public/assets/ui.js'; ?>"></script>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
