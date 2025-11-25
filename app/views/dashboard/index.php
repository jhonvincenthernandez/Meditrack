<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Dashboard — MediTrack+</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
</head>
<body>

<!-- Sidebar -->
<?php include APP_DIR . 'views/_sidebar.php'; ?>

<!-- Main Content -->
<div class="main">
  <?php
    $topbar_title = 'Dashboard';
    $topbar_right = '<span class="text-muted small">Today: ' . htmlspecialchars($today_label ?? date('M d, Y')) . '</span>';
    include APP_DIR . 'views/_topbar.php';
  ?>
  <?php
    $statusCounts = $status_counts ?? ['scheduled' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0];
    $statusCards = [
      ['label' => 'Scheduled', 'value' => $statusCounts['scheduled'], 'class' => 'bg-warning bg-opacity-25 border-warning-subtle text-warning', 'icon' => '📅'],
      ['label' => 'Confirmed', 'value' => $statusCounts['confirmed'], 'class' => 'bg-info bg-opacity-25 border-info-subtle text-info', 'icon' => '✅'],
      ['label' => 'Completed', 'value' => $statusCounts['completed'], 'class' => 'bg-success bg-opacity-25 border-success-subtle text-success', 'icon' => '🟢'],
      ['label' => 'Cancelled', 'value' => $statusCounts['cancelled'], 'class' => 'bg-secondary bg-opacity-25 border-secondary-subtle text-secondary', 'icon' => '❌'],
    ];
    $pendingSummary = $pending_summary ?? ['count' => 0, 'amount' => 0];
    $revenue = $revenue ?? ['today' => 0, 'week' => 0, 'month' => 0];
    $badgeMap = [
      'scheduled' => 'text-bg-warning text-dark',
      'confirmed' => 'text-bg-info text-dark',
      'completed' => 'text-bg-success',
      'cancelled' => 'text-bg-secondary',
    ];
    $paymentBadgeMap = [
      'paid' => 'text-bg-success',
      'pending' => 'text-bg-warning text-dark',
      'cancelled' => 'text-bg-secondary',
      'expired' => 'text-bg-danger',
    ];
  ?>

  <div class="row g-3 mb-4">
    <?php foreach ($statusCards as $card): ?>
      <div class="col-md-6 col-xl-3">
        <div class="card-soft border <?= $card['class']; ?>">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <small class="text-uppercase fw-semibold text-muted"><?= htmlspecialchars($card['label']); ?></small>
              <h3 class="mt-2 mb-0"><?= (int)$card['value']; ?></h3>
            </div>
            <div class="fs-30"><?= $card['icon']; ?></div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
      <div class="card-soft d-flex justify-content-between align-items-center">
        <div>
          <small class="text-muted">Total Patients</small>
          <h3 class="mt-2 mb-0"><?= (int)($patient_count ?? 0); ?></h3>
        </div>
        <div class="fs-30 text-accent">👥</div>
      </div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="card-soft d-flex justify-content-between align-items-center">
        <div>
          <small class="text-muted">Total Doctors</small>
          <h3 class="mt-2 mb-0"><?= (int)($doctor_count ?? 0); ?></h3>
        </div>
        <div class="fs-30 text-green">🩺</div>
      </div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="card-soft d-flex justify-content-between align-items-center">
        <div>
          <small class="text-muted">Today's Appointments</small>
          <h3 class="mt-2 mb-0"><?= (int)($today_count ?? 0); ?></h3>
        </div>
        <div class="fs-30 text-amber">🕒</div>
      </div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="card-soft d-flex justify-content-between align-items-center">
        <div>
          <small class="text-muted">Pending Payments</small>
          <h3 class="mt-2 mb-0"><?= (int)($pendingSummary['count'] ?? 0); ?></h3>
          <span class="text-muted">₱ <?= number_format((float)($pendingSummary['amount'] ?? 0), 2); ?></span>
        </div>
        <div class="fs-30 text-danger">💰</div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-xl-8">
      <div class="card-soft h-100">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <h5 class="mb-0">Today's Schedule</h5>
            <small class="text-muted"><?= htmlspecialchars($today_label ?? date('l, F d, Y')); ?></small>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Time</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Status</th>
                <th>Payment</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($today_schedule)): ?>
                <?php foreach ($today_schedule as $row): ?>
                  <?php
                    $statusKey = $row['status'] ?? 'scheduled';
                    $statusBadge = $badgeMap[$statusKey] ?? 'text-bg-secondary';
                    $payKey = $row['payment_status'] ?? 'pending';
                    $paymentBadge = $paymentBadgeMap[$payKey] ?? 'text-bg-secondary';
                  ?>
                  <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($row['time']); ?></td>
                    <td><?= htmlspecialchars($row['patient']); ?></td>
                    <td><?= htmlspecialchars($row['doctor']); ?></td>
                    <td><span class="badge <?= $statusBadge ?>" style="text-transform:capitalize;"><?= htmlspecialchars($statusKey); ?></span></td>
                    <td>
                      <div class="d-flex flex-column">
                        <span class="badge <?= $paymentBadge ?>" style="text-transform:capitalize;"><?= htmlspecialchars($payKey); ?></span>
                        <small class="text-muted">₱ <?= number_format((float)($row['amount'] ?? 0), 2); ?></small>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">No appointments scheduled for today.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-4">
      <div class="card-soft mb-3">
        <h6 class="text-uppercase text-muted small">Revenue Overview</h6>
        <div class="d-flex gap-3 mt-3">
          <div class="flex-fill p-3 border rounded">
            <small class="text-muted">Today</small>
            <h4 class="mb-0">₱ <?= number_format((float)$revenue['today'], 2); ?></h4>
          </div>
          <div class="flex-fill p-3 border rounded">
            <small class="text-muted">This Week</small>
            <h4 class="mb-0">₱ <?= number_format((float)$revenue['week'], 2); ?></h4>
          </div>
          <div class="flex-fill p-3 border rounded">
            <small class="text-muted">This Month</small>
            <h4 class="mb-0">₱ <?= number_format((float)$revenue['month'], 2); ?></h4>
          </div>
        </div>
      </div>

      <div class="card-soft">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="mb-0">Pending Payments</h6>
          <small class="text-muted"><?= (int)($pendingSummary['count'] ?? 0); ?> invoices</small>
        </div>
        <?php if (!empty($pending_payments)): ?>
          <div class="list-group list-group-flush">
            <?php foreach ($pending_payments as $pay): ?>
              <?php
                $dueLabel = 'soon';
                if (!empty($pay['slot_date'])) {
                  $ts = strtotime($pay['slot_date']);
                  $dueLabel = $ts ? date('M d', $ts) : $pay['slot_date'];
                }
              ?>
              <div class="list-group-item px-0">
                <div class="d-flex justify-content-between">
                  <div>
                    <div class="fw-semibold"><?= htmlspecialchars($pay['patient_name'] ?? 'Patient'); ?></div>
                    <small class="text-muted">with <?= htmlspecialchars($pay['doctor_name'] ?? 'Doctor'); ?></small>
                  </div>
                  <div class="text-end">
                    <div class="fw-semibold">₱ <?= number_format((float)($pay['amount'] ?? 0), 2); ?></div>
                    <small class="text-muted">Due <?= htmlspecialchars($dueLabel); ?></small>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-muted mb-0">All caught up! 🎉</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="card-soft mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0">Next 7 Days</h5>
  <small class="text-muted">Totals grouped by payment status</small>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Date</th>
            <th>Total</th>
            <th>Paid</th>
            <th>Pending</th>
            <th>Cancelled</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($upcoming_week)): ?>
            <?php foreach ($upcoming_week as $weekRow): ?>
              <?php
                $weekLabel = $weekRow['label'] ?? null;
                if ($weekLabel === null && !empty($weekRow['date'])) {
                  $ts = strtotime($weekRow['date']);
                  $weekLabel = $ts ? date('D, M d', $ts) : $weekRow['date'];
                }
              ?>
              <tr>
                <td><?= htmlspecialchars($weekLabel ?? 'TBD'); ?></td>
                <td class="fw-semibold"><?= (int)($weekRow['total'] ?? 0); ?></td>
                <td><span class="badge text-bg-success"><?= (int)($weekRow['paid'] ?? 0); ?></span></td>
                <td><span class="badge text-bg-warning text-dark"><?= (int)($weekRow['pending'] ?? 0); ?></span></td>
                <td><span class="badge text-bg-secondary"><?= (int)($weekRow['cancelled'] ?? 0); ?></span></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="text-center text-muted py-4">No appointments scheduled in the next week.</td>
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
