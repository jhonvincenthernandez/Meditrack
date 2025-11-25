<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Appointments — MediTrack+</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
</head>
<body>

<?php include APP_DIR . 'views/_sidebar.php'; ?>

<!-- Main Content -->
<div class="main">
  <?php $role = $_SESSION['role'] ?? ''; ?>
  <?php
    $topbar_title = 'Appointments';
    $topbar_right = ($role === 'admin') ? '<a href="' . base_url() . '/appointments/add" class="btn btn-primary btn-sm">+ Add Appointment</a>' : '';
    include APP_DIR . 'views/_topbar.php';
  ?>

  <div class="card-soft">
    <?php
      $counts = $appointment_counts ?? ['scheduled' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0];
      $grouped = $appointments ?? ['scheduled' => [], 'confirmed' => [], 'completed' => [], 'cancelled' => []];
      $statusClasses = [
        'scheduled' => 'text-bg-warning text-dark',
        'confirmed' => 'text-bg-info text-dark',
        'completed' => 'text-bg-success',
        'cancelled' => 'text-bg-secondary',
      ];
    ?>

    <div class="row g-3 mb-3">
      <div class="col-md-3">
        <div class="status-pill bg-warning bg-opacity-25 border border-warning-subtle rounded p-3">
          <div class="fw-semibold text-warning text-uppercase small">Scheduled</div>
          <div class="display-6 mb-0 text-warning"><?= (int)$counts['scheduled']; ?></div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="status-pill bg-info bg-opacity-25 border border-info-subtle rounded p-3">
          <div class="fw-semibold text-info text-uppercase small">Confirmed</div>
          <div class="display-6 mb-0 text-info"><?= (int)$counts['confirmed']; ?></div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="status-pill bg-success bg-opacity-25 border border-success-subtle rounded p-3">
          <div class="fw-semibold text-success text-uppercase small">Completed</div>
          <div class="display-6 mb-0 text-success"><?= (int)$counts['completed']; ?></div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="status-pill bg-secondary bg-opacity-25 border border-secondary-subtle rounded p-3">
          <div class="fw-semibold text-secondary text-uppercase small">Cancelled</div>
          <div class="display-6 mb-0 text-secondary"><?= (int)$counts['cancelled']; ?></div>
        </div>
      </div>
    </div>

    <ul class="nav nav-pills mb-3" id="appointmentTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="scheduled-tab" data-bs-toggle="pill" data-bs-target="#scheduled-pane" type="button" role="tab" aria-controls="scheduled-pane" aria-selected="true">Scheduled</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="confirmed-tab" data-bs-toggle="pill" data-bs-target="#confirmed-pane" type="button" role="tab" aria-controls="confirmed-pane" aria-selected="false">Confirmed</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="completed-tab" data-bs-toggle="pill" data-bs-target="#completed-pane" type="button" role="tab" aria-controls="completed-pane" aria-selected="false">Completed</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="cancelled-tab" data-bs-toggle="pill" data-bs-target="#cancelled-pane" type="button" role="tab" aria-controls="cancelled-pane" aria-selected="false">Cancelled</button>
      </li>
    </ul>

    <div class="tab-content" id="appointmentTabContent">
      <div class="tab-pane fade show active" id="scheduled-pane" role="tabpanel" aria-labelledby="scheduled-tab" tabindex="0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Date</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Notes</th>
                <th>Payment</th>
                <th>Status</th>
                <?php if($role === 'admin'): ?><th>Actions</th><?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php if(!empty($grouped['scheduled'])): foreach($grouped['scheduled'] as $a): ?>
              <?php
                $status = strtolower($a['status'] ?? 'scheduled');
                $badgeClass = $statusClasses[$status] ?? 'text-bg-secondary';
                $paymentStatus = strtolower($a['payment_status'] ?? 'pending');
                $paymentBadge = 'text-bg-secondary';
                if ($paymentStatus === 'paid') {
                  $paymentBadge = 'text-bg-success';
                } elseif (in_array($paymentStatus, ['expired','failed','cancelled'], true)) {
                  $paymentBadge = 'text-bg-danger';
                } elseif ($paymentStatus === 'pending') {
                  $paymentBadge = 'text-bg-warning text-dark';
                }
                $amount = number_format((float)($a['amount'] ?? 0), 2);
                $checkoutLink = base_url() . '/appointments/admin/checkout/' . (int)$a['id'];
              ?>
              <tr>
                <td>
                  <?php if(!empty($a['slot_date'])): ?>
                    <?= htmlspecialchars($a['slot_date']) ?>
                    <?php if(!empty($a['start_time']) && !empty($a['end_time'])): ?>
                      (<?= htmlspecialchars($a['start_time']) ?> - <?= htmlspecialchars($a['end_time']) ?>)
                    <?php endif; ?>
                  <?php else: ?>
                    N/A
                  <?php endif; ?>
                </td>
                <td><span class="badge-patient"><?= htmlspecialchars($a['patient_name'] ?? 'N/A') ?></span></td>
                <td><span class="badge-doctor"><?= htmlspecialchars($a['doctor_name'] ?? 'N/A') ?></span></td>
                <td><?= htmlspecialchars($a['notes']) ?></td>
                <td>
                  <div class="fw-semibold">₱ <?= $amount; ?></div>
                  <span class="badge <?= $paymentBadge ?>" style="text-transform:capitalize;"><?= htmlspecialchars($paymentStatus); ?></span>
                  <?php if(!empty($a['invoice_url'])): ?>
                    <div class="mt-1">
                      <a href="<?= $checkoutLink; ?>" class="btn btn-sm btn-link p-0">Open invoice</a>
                    </div>
                  <?php endif; ?>
                </td>
                <td><span class="badge <?= $badgeClass ?>" style="text-transform:capitalize;"><?= htmlspecialchars($status) ?></span></td>
                <?php if($role === 'admin'): ?>
                <td class="text-nowrap">
                  <button type="button"
                          class="btn btn-sm btn-info me-1 btn-mark-complete"
                          data-id="<?= (int)$a['id']; ?>"
                          data-payment-status="<?= htmlspecialchars($paymentStatus, ENT_QUOTES, 'UTF-8'); ?>"
                          data-amount="<?= (float)($a['amount'] ?? 0); ?>"
                          data-patient="<?= htmlspecialchars($a['patient_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>"
                          data-doctor="<?= htmlspecialchars($a['doctor_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>"
                          data-slot="<?= htmlspecialchars(($a['slot_date'] ?? '') . (!empty($a['start_time']) ? ' • '.$a['start_time'].'-'.$a['end_time'] : ''), ENT_QUOTES, 'UTF-8'); ?>">
                    ✓ Confirm
                  </button>
      <button type="button"
        class="btn btn-sm btn-outline-danger me-1"
                          data-bs-toggle="modal"
                          data-bs-target="#cancelModal"
                          data-id="<?= (int)$a['id']; ?>"
                          data-label="<?= htmlspecialchars(($a['patient_name'] ?? 'N/A').' • '.($a['slot_date'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?>">
                    Cancel
                  </button>
      <a href="<?= base_url(); ?>/appointments/edit/<?= (int)$a['id']; ?>" class="btn btn-sm btn-outline-warning me-1">✏️ Edit</a>
      <a href="<?= base_url(); ?>/appointments/delete/<?= (int)$a['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this appointment?');">🗑 Delete</a>
                </td>
                <?php endif; ?>
              </tr>
              <?php endforeach; else: ?>
              <tr>
                <td colspan="<?= $role === 'admin' ? 7 : 6 ?>" class="text-center text-muted py-4">No scheduled appointments found.</td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="tab-pane fade" id="confirmed-pane" role="tabpanel" aria-labelledby="confirmed-tab" tabindex="0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Date</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Notes</th>
                <th>Payment</th>
                <th>Status</th>
                <?php if($role === 'admin'): ?><th>Actions</th><?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php if(!empty($grouped['confirmed'])): foreach($grouped['confirmed'] as $a): ?>
              <?php
                $status = strtolower($a['status'] ?? 'confirmed');
                $badgeClass = $statusClasses[$status] ?? 'text-bg-secondary';
                $paymentStatus = strtolower($a['payment_status'] ?? 'pending');
                $paymentBadge = 'text-bg-secondary';
                if ($paymentStatus === 'paid') {
                  $paymentBadge = 'text-bg-success';
                }
                $amount = number_format((float)($a['amount'] ?? 0), 2);
              ?>
              <tr>
                <td>
                  <?php if(!empty($a['slot_date'])): ?>
                    <?= htmlspecialchars($a['slot_date']) ?>
                    <?php if(!empty($a['start_time']) && !empty($a['end_time'])): ?>
                      (<?= htmlspecialchars($a['start_time']) ?> - <?= htmlspecialchars($a['end_time']) ?>)
                    <?php endif; ?>
                  <?php else: ?>
                    N/A
                  <?php endif; ?>
                </td>
                <td><span class="badge-patient"><?= htmlspecialchars($a['patient_name'] ?? 'N/A') ?></span></td>
                <td><span class="badge-doctor"><?= htmlspecialchars($a['doctor_name'] ?? 'N/A') ?></span></td>
                <td><?= htmlspecialchars($a['notes']) ?></td>
                <td>
                  <div class="fw-semibold">₱ <?= $amount; ?></div>
                  <span class="badge <?= $paymentBadge ?>" style="text-transform:capitalize;"><?= htmlspecialchars($paymentStatus); ?></span>
                </td>
                <td><span class="badge <?= $badgeClass ?>" style="text-transform:capitalize;"><?= htmlspecialchars($status) ?></span></td>
                <?php if($role === 'admin'): ?>
                <td class="text-nowrap">
                  <button type="button"
                          class="btn btn-sm btn-success me-1 btn-mark-complete"
                          data-id="<?= (int)$a['id']; ?>"
                          data-status="confirmed"
                          data-payment-status="<?= htmlspecialchars($paymentStatus, ENT_QUOTES, 'UTF-8'); ?>"
                          data-amount="<?= (float)($a['amount'] ?? 0); ?>"
                          data-patient="<?= htmlspecialchars($a['patient_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>"
                          data-doctor="<?= htmlspecialchars($a['doctor_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>"
                          data-slot="<?= htmlspecialchars(($a['slot_date'] ?? '') . (!empty($a['start_time']) ? ' • '.$a['start_time'].'-'.$a['end_time'] : ''), ENT_QUOTES, 'UTF-8'); ?>">
                    ✓ Mark Done
                  </button>
                </td>
                <?php endif; ?>
              </tr>
              <?php endforeach; else: ?>
              <tr>
                <td colspan="<?= $role === 'admin' ? 7 : 6 ?>" class="text-center text-muted py-4">No confirmed appointments yet.</td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="tab-pane fade" id="completed-pane" role="tabpanel" aria-labelledby="completed-tab" tabindex="0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Date</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Notes</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Completed At</th>
              </tr>
            </thead>
            <tbody>
              <?php if(!empty($grouped['completed'])): foreach($grouped['completed'] as $a): ?>
              <?php
                $paymentStatus = strtolower($a['payment_status'] ?? 'pending');
                $paymentBadge = 'text-bg-secondary';
                if ($paymentStatus === 'paid') {
                  $paymentBadge = 'text-bg-success';
                }
                $amount = number_format((float)($a['amount'] ?? 0), 2);
              ?>
              <tr>
                <td>
                  <?php if(!empty($a['slot_date'])): ?>
                    <?= htmlspecialchars($a['slot_date']) ?>
                    <?php if(!empty($a['start_time']) && !empty($a['end_time'])): ?>
                      (<?= htmlspecialchars($a['start_time']) ?> - <?= htmlspecialchars($a['end_time']) ?>)
                    <?php endif; ?>
                  <?php else: ?>
                    N/A
                  <?php endif; ?>
                </td>
                <td><span class="badge-patient"><?= htmlspecialchars($a['patient_name'] ?? 'N/A') ?></span></td>
                <td><span class="badge-doctor"><?= htmlspecialchars($a['doctor_name'] ?? 'N/A') ?></span></td>
                <td><?= htmlspecialchars($a['notes']) ?></td>
                <td>
                  <div class="fw-semibold">₱ <?= $amount; ?></div>
                  <span class="badge <?= $paymentBadge ?>" style="text-transform:capitalize;"><?= htmlspecialchars($paymentStatus); ?></span>
                </td>
                <td><span class="badge text-bg-success" style="text-transform:capitalize;">completed</span></td>
                <td><?= !empty($a['completed_at']) ? htmlspecialchars($a['completed_at']) : '—'; ?></td>
              </tr>
              <?php endforeach; else: ?>
              <tr>
                <td colspan="7" class="text-center text-muted py-4">No completed appointments yet.</td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="tab-pane fade" id="cancelled-pane" role="tabpanel" aria-labelledby="cancelled-tab" tabindex="0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Date</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Reason</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Cancelled At</th>
              </tr>
            </thead>
            <tbody>
              <?php if(!empty($grouped['cancelled'])): foreach($grouped['cancelled'] as $a): ?>
              <?php
                $paymentStatus = strtolower($a['payment_status'] ?? 'pending');
                $paymentBadge = 'text-bg-secondary';
                if ($paymentStatus === 'paid') {
                  $paymentBadge = 'text-bg-success';
                } elseif (in_array($paymentStatus, ['expired','failed','cancelled'], true)) {
                  $paymentBadge = 'text-bg-danger';
                }
                $amount = number_format((float)($a['amount'] ?? 0), 2);
              ?>
              <tr>
                <td>
                  <?php if(!empty($a['slot_date'])): ?>
                    <?= htmlspecialchars($a['slot_date']) ?>
                    <?php if(!empty($a['start_time']) && !empty($a['end_time'])): ?>
                      (<?= htmlspecialchars($a['start_time']) ?> - <?= htmlspecialchars($a['end_time']) ?>)
                    <?php endif; ?>
                  <?php else: ?>
                    N/A
                  <?php endif; ?>
                </td>
                <td><span class="badge-patient"><?= htmlspecialchars($a['patient_name'] ?? 'N/A') ?></span></td>
                <td><span class="badge-doctor"><?= htmlspecialchars($a['doctor_name'] ?? 'N/A') ?></span></td>
                <td><?= htmlspecialchars($a['cancellation_reason'] ?? '—') ?></td>
                <td>
                  <div class="fw-semibold">₱ <?= $amount; ?></div>
                  <span class="badge <?= $paymentBadge ?>" style="text-transform:capitalize;"><?= htmlspecialchars($paymentStatus); ?></span>
                </td>
                <td><span class="badge text-bg-secondary" style="text-transform:capitalize;">cancelled</span></td>
                <td><?= !empty($a['cancelled_at']) ? htmlspecialchars($a['cancelled_at']) : '—'; ?></td>
              </tr>
              <?php endforeach; else: ?>
              <tr>
                <td colspan="7" class="text-center text-muted py-4">No cancelled appointments yet.</td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
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

<!-- Manual Confirmation Modal -->
<div class="modal fade" id="manualCompleteModal" tabindex="-1" aria-labelledby="manualCompleteLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="manualCompleteLabel">Manual Payment Confirmation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" id="manualCompleteForm">
        <div class="modal-body">
          <div class="alert alert-warning" role="alert">
            Use this when payment was received offline (cash, bank transfer, insurance, etc.). This will mark the appointment as <strong>Confirmed</strong>. All overrides are logged.
          </div>
          <dl class="row small mb-4">
            <dt class="col-4 text-muted">Patient</dt>
            <dd class="col-8 fw-semibold manual-patient">—</dd>
            <dt class="col-4 text-muted">Schedule</dt>
            <dd class="col-8 manual-schedule">—</dd>
          </dl>
          <div class="mb-3">
            <label for="overrideMethod" class="form-label">Payment method <span class="text-danger">*</span></label>
            <select class="form-select" id="overrideMethod" name="override_method" required>
              <option value="cash">Cash / Walk-in</option>
              <option value="bank_transfer">Bank transfer / Deposit</option>
              <option value="insurance">Insurance coverage</option>
              <option value="check">Check</option>
              <option value="writeoff">Write-off / Complimentary</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="mb-3 d-none" id="overrideMethodOtherGroup">
            <label for="overrideMethodOther" class="form-label">Specify other method</label>
            <input type="text" class="form-control" id="overrideMethodOther" name="override_method_other" placeholder="Describe how payment was settled">
          </div>
          <div class="row g-3">
            
          </div>
          <div class="mb-3 mt-3">
            <label for="overrideReason" class="form-label">Reason / notes <span class="text-danger">*</span></label>
            <textarea class="form-control" id="overrideReason" name="override_reason" rows="3" placeholder="Why are you completing this appointment without an online payment?" required></textarea>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="overrideConfirm" name="override_confirm" required>
            <label class="form-check-label" for="overrideConfirm">
              I confirm the payment was received offline and the appointment should be marked as confirmed.
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Back</button>
          <button type="submit" class="btn btn-info">Confirm Appointment</button>
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
    const appointmentsBase = '<?= rtrim(base_url(), '/'); ?>/appointments';

    const cancelModalEl = document.getElementById('cancelModal');
    if (cancelModalEl) {
      const cancelForm = document.getElementById('cancelForm');
      const labelEl = cancelModalEl.querySelector('.appointment-label');
      const reasonEl = document.getElementById('cancelReason');

      cancelModalEl.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        if (!button) { return; }
        const appointmentId = button.getAttribute('data-id');
        const appointmentLabel = button.getAttribute('data-label') || '';

        cancelForm.action = appointmentsBase + '/' + appointmentId + '/cancel';
        labelEl.textContent = appointmentLabel;
        reasonEl.value = '';
      });

      cancelModalEl.addEventListener('hidden.bs.modal', function () {
        reasonEl.value = '';
        cancelForm.action = '';
        labelEl.textContent = '';
      });
    }

    const manualModalEl = document.getElementById('manualCompleteModal');
    const manualForm = document.getElementById('manualCompleteForm');
    const manualPatient = manualModalEl ? manualModalEl.querySelector('.manual-patient') : null;
    const manualSchedule = manualModalEl ? manualModalEl.querySelector('.manual-schedule') : null;
    const manualAmount = document.getElementById('overrideAmount');
    const manualMethod = document.getElementById('overrideMethod');
    const manualMethodOtherGroup = document.getElementById('overrideMethodOtherGroup');
    const manualMethodOther = document.getElementById('overrideMethodOther');
    const manualReason = document.getElementById('overrideReason');
    const manualConfirm = document.getElementById('overrideConfirm');

    document.querySelectorAll('.btn-mark-complete').forEach(function(button){
      button.addEventListener('click', function(){
        const appointmentId = this.getAttribute('data-id');
        const paymentStatus = (this.getAttribute('data-payment-status') || '').toLowerCase();
        const currentStatus = (this.getAttribute('data-status') || '').toLowerCase();

        // Confirmed appointments can be marked as completed directly
        if (currentStatus === 'confirmed') {
          window.location.href = appointmentsBase + '/' + appointmentId + '/complete';
          return;
        }

        // Scheduled appointments with paid status can be confirmed
        if (paymentStatus === 'paid') {
          window.location.href = appointmentsBase + '/' + appointmentId + '/complete';
          return;
        }

        if (!manualModalEl || !manualForm) {
          alert('Manual completion form is not available.');
          return;
        }

        manualForm.action = appointmentsBase + '/' + appointmentId + '/complete';
        if (manualPatient) {
          manualPatient.textContent = this.getAttribute('data-patient') || 'Patient';
        }
        if (manualSchedule) {
          manualSchedule.textContent = this.getAttribute('data-slot') || '—';
        }
        if (manualAmount) {
          const amountValue = parseFloat(this.getAttribute('data-amount') || '0');
          manualAmount.value = isNaN(amountValue) ? '' : amountValue.toFixed(2);
        }
        if (manualReason) {
          manualReason.value = '';
        }
        if (manualConfirm) {
          manualConfirm.checked = false;
        }
        if (manualMethod) {
          manualMethod.value = 'cash';
        }
        if (manualMethodOther) {
          manualMethodOther.value = '';
        }
        if (manualMethodOtherGroup) {
          manualMethodOtherGroup.classList.add('d-none');
          manualMethodOther && manualMethodOther.removeAttribute('required');
        }

        const modalInstance = bootstrap.Modal.getOrCreateInstance(manualModalEl);
        modalInstance.show();
      });
    });

    if (manualMethod && manualMethodOtherGroup) {
      manualMethod.addEventListener('change', function(){
        if (this.value === 'other') {
          manualMethodOtherGroup.classList.remove('d-none');
          manualMethodOther && manualMethodOther.setAttribute('required', 'required');
        } else {
          manualMethodOtherGroup.classList.add('d-none');
          if (manualMethodOther) {
            manualMethodOther.removeAttribute('required');
            manualMethodOther.value = '';
          }
        }
      });
    }
  })();
</script>
</body>
</html>
