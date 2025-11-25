<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Patient Directory — Staff · MediTrack+</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar p-3">
  <a href="<?= base_url(); ?>/" class="d-flex align-items-center mb-4 text-decoration-none">
    <div class="brand-logo"></div>
    <div class="ms-2">
      <div class="brand-title">MediTrack+</div>
      <small class="text-muted">Clinic Staff</small>
    </div>
  </a>
  <nav class="nav flex-column">
    <a class="nav-link" href="<?= base_url(); ?>/dashboard_staff">🏠 Dashboard</a>
    <a class="nav-link" href="<?= base_url(); ?>/appointments/staff_add">➕ Add Appointment</a>
    <a class="nav-link active" href="<?= base_url(); ?>/patients">👥 Patient Directory</a>
    <a class="nav-link" href="<?= base_url(); ?>/payments/records">💰 Payment Records</a>
    <a href="<?= site_url('auth/logout'); ?>" class="btn btn-danger mt-3">Logout</a>
  </nav>
</aside>

<div class="main">
  <?php
    $directory = $patients ?? [];
    $query = $query ?? '';
    $summaryDefaults = ['total' => 0, 'upcoming' => 0, 'pending' => 0, 'pending_amount' => 0];
    $summary = array_merge($summaryDefaults, $summary ?? []);

    $statusBadgeMap = [
      'scheduled' => 'text-bg-warning text-dark',
      'confirmed' => 'text-bg-info text-dark',
      'completed' => 'text-bg-success',
      'cancelled' => 'text-bg-secondary',
    ];
  ?>

  <div class="topbar flex-wrap gap-2">
    <div>
      <h4 class="mb-0">Patient Directory</h4>
      <small class="text-muted">Centralized contact list with visit history & upcoming schedules.</small>
    </div>
    <form class="d-flex" method="get" action="<?= base_url(); ?>/patients">
      <input name="q" class="form-control form-control-sm me-2" placeholder="Search by name, contact, or gender" value="<?= htmlspecialchars($query); ?>">
      <button class="btn btn-outline-secondary btn-sm">Search</button>
    </form>
  </div>

  <div class="row g-3 my-4">
    <div class="col-md-4">
      <div class="card-soft border h-100 border-primary-subtle">
        <small class="text-muted text-uppercase">Patients on file</small>
        <h3 class="mt-2 mb-0"><?= (int)$summary['total']; ?></h3>
        <p class="text-muted small mb-0">Across all doctors.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card-soft border h-100 border-success-subtle">
        <small class="text-muted text-uppercase">With upcoming visits</small>
        <h3 class="mt-2 mb-0"><?= (int)$summary['upcoming']; ?></h3>
        <p class="text-muted small mb-0">Next 7-day outlook.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card-soft border h-100 border-warning-subtle">
        <small class="text-muted text-uppercase">Pending collections</small>
        <h3 class="mt-2 mb-0"><?= (int)$summary['pending']; ?> patients</h3>
        <p class="text-muted small mb-0">₱ <?= number_format((float)$summary['pending_amount'], 2); ?> outstanding.</p>
      </div>
    </div>
  </div>

  <div class="card-soft">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
      <div>
        <h5 class="mb-0">Directory</h5>
        <small class="text-muted">Sorted alphabetically for quick lookup.</small>
      </div>
      <div class="d-flex gap-2">
        <a href="<?= base_url(); ?>/appointments/staff_add" class="btn btn-primary btn-sm">+ Book Appointment</a>
        <a href="<?= base_url(); ?>/patients/add" class="btn btn-outline-secondary btn-sm">+ Add Patient</a>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Patient</th>
            <th>Contact</th>
            <th>Next Visit</th>
            <th>Last Visit</th>
            <th>Outstanding</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($directory)): ?>
            <?php foreach ($directory as $patient): ?>
              <?php
                $nextStatus = strtolower($patient['next_status'] ?? '');
                $lastStatus = strtolower($patient['last_status'] ?? '');
                $nextBadge = $statusBadgeMap[$nextStatus] ?? 'text-bg-secondary';
                $lastBadge = $statusBadgeMap[$lastStatus] ?? 'text-bg-secondary';
                $contact = $patient['contact'] ?? '';
                $telLink = preg_replace('/[^0-9+]/', '', $contact);
                $hasContactLink = !empty($telLink);
                $outstanding = (float)($patient['outstanding'] ?? 0);
                $outstandingClass = $outstanding > 0 ? 'text-danger' : 'text-muted';
              ?>
              <tr>
                <td>
                  <div class="fw-semibold"><?= htmlspecialchars($patient['name'] ?? 'Patient'); ?></div>
                  <small class="text-muted">Age <?= htmlspecialchars($patient['age'] ?? '—'); ?> · <?= htmlspecialchars($patient['gender'] ?? '—'); ?></small><br>
                  <small class="text-muted">Total visits: <?= (int)($patient['total_visits'] ?? 0); ?></small>
                </td>
                <td>
                  <?php if ($contact): ?>
                    <div class="fw-semibold"><?= htmlspecialchars($contact); ?></div>
                    <?php if ($hasContactLink): ?>
                      <a class="text-decoration-none small" href="tel:<?= htmlspecialchars($telLink); ?>">Call / SMS</a>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($patient['next_visit'])): ?>
                    <div class="fw-semibold"><?= htmlspecialchars($patient['next_visit']); ?></div>
                    <small class="text-muted">with <?= htmlspecialchars($patient['next_doctor'] ?? 'Unassigned'); ?></small><br>
                    <span class="badge <?= $nextBadge; ?>" style="text-transform:capitalize;"><?= htmlspecialchars($nextStatus ?: 'scheduled'); ?></span>
                  <?php else: ?>
                    <span class="text-muted">No upcoming appointments</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($patient['last_visit'])): ?>
                    <div class="fw-semibold"><?= htmlspecialchars($patient['last_visit']); ?></div>
                    <small class="text-muted">with <?= htmlspecialchars($patient['last_doctor'] ?? 'Unassigned'); ?></small><br>
                    <?php if (!empty($lastStatus)): ?>
                      <span class="badge <?= $lastBadge; ?>" style="text-transform:capitalize;"><?= htmlspecialchars($lastStatus); ?></span>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="text-muted">No visits logged yet</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="fw-semibold <?= $outstandingClass; ?>">₱ <?= number_format($outstanding, 2); ?></div>
                  <small class="text-muted">Due across pending invoices</small>
                </td>
                
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center text-muted py-4">No patients match your search.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= base_url().'public/assets/ui.js'; ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
