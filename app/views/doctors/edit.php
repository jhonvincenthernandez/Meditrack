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
        <form method="post" action="<?= base_url(); ?>/doctors/update/<?= $doctor['id'] ?>">
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($doctor['name'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Specialty</label>
            <input type="text" name="specialty" class="form-control" value="<?= htmlspecialchars($doctor['specialty'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Contact</label>
            <input type="text" name="contact"  maxlength="11" class="form-control" value="<?= htmlspecialchars($doctor['contact'] ?? '') ?>">
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
</body>
</html>
