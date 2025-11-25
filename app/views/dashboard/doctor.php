<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Doctor Dashboard — MediTrack+</title>
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
      <small class="text-muted">Doctor Portal</small>
    </div>
  </a>
  <nav class="nav flex-column">
    <a class="nav-link active" href="<?= base_url(); ?>/dashboard_doctor">🏠 Dashboard</a>
    <a class="nav-link" href="<?= base_url(); ?>/appointments/doc_add">➕ Add Appointment</a>
    <a class="nav-link" href="<?= base_url(); ?>/schedules/manage">📆 Manage Slots</a>
    <a class="nav-link" href="<?= base_url(); ?>/payments/records">💰 Payment Records</a>
    <a href="<?= site_url('auth/logout'); ?>" class="btn btn-danger mt-3">Logout</a>
  </nav>
</aside>

<div class="main">
  <?php
    $doctorName = $doctor['name'] ?? 'Doctor';
    $statsDefaults = [
      'today_total' => 0,
      'confirmed_today' => 0,
      'pending_payments' => 0,
      'week_completed' => 0,
    ];
    $stats = array_merge($statsDefaults, $stats ?? []);

    $revenueDefaults = ['today' => 0, 'week' => 0, 'month' => 0];
    $revenue = array_merge($revenueDefaults, $revenue ?? []);

    $todaySchedule = $today_schedule ?? [];
    $pendingPayments = $pending_payments ?? [];
    $upcomingWeek = $upcoming_week ?? [];
    $recentCompleted = $recent_completed ?? [];
    $totalPatients = (int)($total_patients ?? 0);
    $appointmentTotal = (int)($appointment_total ?? 0);

    $badgeMap = [
      'scheduled' => 'text-bg-warning text-dark',
      'confirmed' => 'text-bg-info text-dark',
      'completed' => 'text-bg-success',
      'cancelled' => 'text-bg-secondary',
    ];
    $paymentBadgeMap = [
      'paid' => 'text-bg-success',
      'pending' => 'text-bg-warning text-dark',
      'expired' => 'text-bg-danger',
      'cancelled' => 'text-bg-secondary',
      'void' => 'text-bg-secondary',
      'refunded' => 'text-bg-secondary',
    ];
  ?>

  <?php
    $topbar_title = 'Hello, Dr. ' . ($doctorName);
    $topbar_right = '<span class="text-muted small">Today: ' . htmlspecialchars($today_label ?? date('l, F d, Y')) . '</span>';
    $topbar_class = 'd-flex justify-content-between align-items-center flex-wrap';
    include APP_DIR . 'views/_topbar.php';
  ?>

  <div class="row g-3 my-4">
    <div class="col-md-6 col-xl-3">
      <div class="card-soft border bg-opacity-25 border-primary-subtle">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <small class="text-muted text-uppercase fw-semibold">Today's Appointments</small>
            <h3 class="mt-2 mb-0"><?= (int)$stats['today_total']; ?></h3>
          </div>
          <div class="fs-30">🕒</div>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="card-soft border bg-opacity-25 border-info-subtle">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <small class="text-muted text-uppercase fw-semibold">Confirmed Today</small>
            <h3 class="mt-2 mb-0"><?= (int)$stats['confirmed_today']; ?></h3>
          </div>
          <div class="fs-30">✅</div>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="card-soft border bg-opacity-25 border-warning-subtle">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <small class="text-muted text-uppercase fw-semibold">Pending Payments</small>
            <h3 class="mt-2 mb-0"><?= (int)$stats['pending_payments']; ?></h3>
          </div>
          <div class="fs-30">💰</div>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="card-soft border bg-opacity-25 border-success-subtle">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <small class="text-muted text-uppercase fw-semibold">Completed This Week</small>
            <h3 class="mt-2 mb-0"><?= (int)$stats['week_completed']; ?></h3>
          </div>
          <div class="fs-30">📈</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-6">
      <div class="card-soft h-100">
        <small class="text-muted text-uppercase">Unique Patients Seen</small>
        <h3 class="mt-2 mb-0"><?= $totalPatients; ?></h3>
        <p class="text-muted small mb-0">Across all recorded appointments.</p>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card-soft h-100">
        <small class="text-muted text-uppercase">Total Appointments</small>
        <h3 class="mt-2 mb-0"><?= $appointmentTotal; ?></h3>
        <p class="text-muted small mb-0">Lifetime count for your profile.</p>
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
                <th>Payment</th>
                <th>Status</th>
                <th>Notes</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($todaySchedule)): ?>
                <?php foreach ($todaySchedule as $visit): ?>
                  <?php
                    $statusKey = $visit['status'] ?? 'scheduled';
                    $statusBadge = $badgeMap[$statusKey] ?? 'text-bg-secondary';
                    $payKey = $visit['payment_status'] ?? 'pending';
                    $paymentBadge = $paymentBadgeMap[$payKey] ?? 'text-bg-secondary';
                    $modalLabel = ($visit['patient'] ?? 'Patient') . ' • ' . ($visit['time'] ?? 'Schedule');
                  ?>
                  <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($visit['time']); ?></td>
                    <td><?= htmlspecialchars($visit['patient']); ?></td>
                    <td>
                      <div class="fw-semibold">₱ <?= number_format((float)($visit['amount'] ?? 0), 2); ?></div>
                      <span class="badge <?= $paymentBadge; ?>" style="text-transform:capitalize;"><?= htmlspecialchars($payKey); ?></span>
                    </td>
                    <td><span class="badge <?= $statusBadge; ?>" style="text-transform:capitalize;"><?= htmlspecialchars($statusKey); ?></span></td>
                    <td><?= htmlspecialchars($visit['notes'] ?? ''); ?></td>
                    <td class="text-end">
                      <div class="d-flex justify-content-end gap-2">
                        <?php if (($visit['status'] ?? '') !== 'completed'): ?>
                          <a href="<?= base_url(); ?>/appointments/<?= (int)($visit['appointment_id'] ?? 0); ?>/complete" class="btn btn-sm btn-success">Mark Done</a>
                          <button type="button"
                                  class="btn btn-sm btn-outline-danger"
                                  data-bs-toggle="modal"
                                  data-bs-target="#cancelModal"
                                  data-id="<?= (int)($visit['appointment_id'] ?? 0); ?>"
                                  data-label="<?= htmlspecialchars($modalLabel, ENT_QUOTES, 'UTF-8'); ?>">
                            Cancel
                          </button>
                        <?php else: ?>
                          <span class="badge text-bg-success">Completed</span>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-center text-muted py-4">No appointments scheduled for today.</td>
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

      <div class="card-soft mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="mb-0">Pending Payments</h6>
          <small class="text-muted">Top <?= count($pendingPayments); ?></small>
        </div>
        <?php if (!empty($pendingPayments)): ?>
          <div class="list-group list-group-flush">
            <?php foreach ($pendingPayments as $pay): ?>
              <?php
                $badge = $paymentBadgeMap[$pay['payment_status'] ?? 'pending'] ?? 'text-bg-warning text-dark';
                $amount = number_format((float)($pay['amount'] ?? 0), 2);
              ?>
              <div class="list-group-item px-0">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <div class="fw-semibold"><?= htmlspecialchars($pay['patient'] ?? 'Patient'); ?></div>
                    <small class="text-muted"><?= htmlspecialchars($pay['slot_label'] ?? 'Schedule TBD'); ?></small>
                  </div>
                  <div class="text-end">
                    <div class="fw-semibold">₱ <?= $amount; ?></div>
                    <span class="badge <?= $badge; ?>" style="text-transform:capitalize;"><?= htmlspecialchars($pay['payment_status'] ?? 'pending'); ?></span>
                  </div>
                </div>
                <?php if (!empty($pay['invoice_url'])): ?>
                  <div class="mt-2 text-end">
                    <a href="<?= htmlspecialchars($pay['invoice_url']); ?>" target="_blank" rel="noopener" class="btn btn-link btn-sm p-0">Open invoice</a>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-muted mb-0">All caught up! 🎉</p>
        <?php endif; ?>
      </div>

      <div class="card-soft">
        <h6 class="text-uppercase text-muted small">Quick Actions</h6>
        <div class="d-grid gap-2 mt-3">
          <a href="<?= base_url(); ?>/appointments/doc_add" class="btn btn-primary">Create Appointment</a>
          <a href="<?= base_url(); ?>/schedules/manage" class="btn btn-outline-secondary">Manage Slots</a>
          <a href="<?= base_url(); ?>/payments/records" class="btn btn-outline-primary">Review Payments</a>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mt-1">
    <div class="col-12 col-xl-7">
      <div class="card-soft h-100">
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
              <?php if (!empty($upcomingWeek)): ?>
                <?php foreach ($upcomingWeek as $weekRow): ?>
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

    <div class="col-12 col-xl-5">
      <div class="card-soft h-100">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">Recent Completions</h5>
          <small class="text-muted">Latest 5 consultations</small>
        </div>
        <?php if (!empty($recentCompleted)): ?>
          <div class="list-group list-group-flush">
            <?php foreach ($recentCompleted as $completed): ?>
              <div class="list-group-item px-0">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <div class="fw-semibold"><?= htmlspecialchars($completed['patient'] ?? 'Patient'); ?></div>
                    <small class="text-muted"><?= htmlspecialchars($completed['slot_label'] ?? '—'); ?></small>
                  </div>
                  <div class="text-end">
                    <div class="fw-semibold">₱ <?= number_format((float)($completed['amount'] ?? 0), 2); ?></div>
                    <?php if (!empty($completed['completed_at'])): ?>
                      <small class="text-muted"><?= htmlspecialchars(date('M d, H:i', strtotime($completed['completed_at']))); ?></small>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-muted mb-0">No completed appointments yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cancelModalLabel">Cancel Appointment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" id="cancelForm">
        <div class="modal-body">
          <p class="mb-3 text-muted small">You're cancelling the appointment for <strong class="appointment-label"></strong>.</p>
          <div class="mb-3">
            <label for="cancelReason" class="form-label">Reason for cancellation <span class="text-danger">*</span></label>
            <textarea name="reason" id="cancelReason" class="form-control" rows="3" placeholder="Describe why this appointment is being cancelled" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Keep Appointment</button>
          <button type="submit" class="btn btn-danger">Confirm Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= base_url().'public/assets/ui.js'; ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  (function(){
    const cancelModalEl = document.getElementById('cancelModal');
    if (!cancelModalEl) {
      return;
    }

    const cancelForm = document.getElementById('cancelForm');
    const labelEl = cancelModalEl.querySelector('.appointment-label');
    const reasonEl = document.getElementById('cancelReason');
    const cancelBase = '<?= rtrim(base_url(), '/'); ?>/appointments';

    cancelModalEl.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      if (!button) { return; }
      const appointmentId = button.getAttribute('data-id');
      const appointmentLabel = button.getAttribute('data-label') || '';

      cancelForm.action = cancelBase + '/' + appointmentId + '/cancel';
      labelEl.textContent = appointmentLabel;
      reasonEl.value = '';
    });

    cancelModalEl.addEventListener('hidden.bs.modal', function () {
      reasonEl.value = '';
      cancelForm.action = '';
      labelEl.textContent = '';
    });
  })();
</script>
</body>
</html>
