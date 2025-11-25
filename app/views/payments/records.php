<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Payment Records — MediTrack+</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
</head>
<body>
<?php
  $role = $role ?? 'admin';
  $payments = $payments ?? [];
  $navTitle = 'MediTrack+';
  $navSubtitle = 'Records';
  $navLinks = [];
  $filters = $filters ?? ['status' => '', 'from' => '', 'to' => ''];
  $statusOptions = $status_options ?? ['pending','paid','expired','failed','cancelled','canceled','voided','refunded'];

  switch ($role) {
    case 'doctor':
      $navSubtitle = 'Doctor Portal';
      $navLinks = [
        ['label' => '🏠 Dashboard', 'href' => base_url() . '/dashboard_doctor'],
        ['label' => '💰 Payment Records', 'href' => base_url() . '/payments/records', 'active' => true],
        ['label' => '➕ Add Appointment', 'href' => base_url() . '/appointments/doc_add'],
        ['label' => '📆 Manage Slots', 'href' => base_url() . '/schedules/manage'],
      ];
      break;
    case 'staff':
      $navSubtitle = 'Staff Portal';
      $navLinks = [
        ['label' => '🏠 Dashboard', 'href' => base_url() . '/dashboard_staff'],
        ['label' => '➕ Add Appointment', 'href' => base_url() . '/appointments/staff_add'],
        ['label' => '👥 Patient Directory', 'href' => base_url() . '/patients'],
        ['label' => '💰 Payment Records', 'href' => base_url() . '/payments/records', 'active' => true],
        
      ];
      break;
    default:
      $navSubtitle = 'Clinic Manager';
      $navLinks = [
        ['label' => '🏠 Dashboard', 'href' => base_url() . '/dashboard_admin'],
        ['label' => '👥 Users', 'href' => base_url() . '/users'],
        ['label' => '🧾 Patients', 'href' => base_url() . '/patients'],
        ['label' => '🩺 Doctors', 'href' => base_url() . '/doctors'],
        ['label' => '📅 Appointments', 'href' => base_url() . '/appointments'],
        ['label' => '📆 Schedules', 'href' => base_url() . '/schedules'],
        ['label' => '💰 Payment Records', 'href' => base_url() . '/payments/records', 'active' => true],
      ];
      break;
  }

  $formatSlot = function(array $entry): string {
    $slotDate  = $entry['slot_date'] ?? null;
    $startTime = $entry['start_time'] ?? null;
    $endTime   = $entry['end_time'] ?? null;
    if (!$slotDate) {
      return 'N/A';
    }
    $startTs = strtotime($slotDate . ' ' . ($startTime ?: '00:00:00'));
    if ($startTs === false) {
      return $slotDate;
    }
    $label = date('M d, Y · h:i A', $startTs);
    if (!empty($endTime)) {
      $endTs = strtotime($slotDate . ' ' . $endTime);
      if ($endTs !== false) {
        $label .= ' - ' . date('h:i A', $endTs);
      }
    }
    return $label;
  };
?>

<?php include APP_DIR . 'views/_sidebar.php'; ?>

<div class="main">
  <?php
    $topbar_title = 'Payment Records';
    $topbar_right = '<span class="text-muted small">Updated: ' . date('M d, Y h:i A') . '</span>';
    $topbar_class = 'd-flex justify-content-between align-items-center';
    include APP_DIR . 'views/_topbar.php';
  ?>

  <div class="p-4">
    <div class="card-soft">
      <?php
        $queryParams = [];
        if (!empty($filters['status'])) { $queryParams['status'] = $filters['status']; }
        if (!empty($filters['from'])) { $queryParams['from'] = $filters['from']; }
        if (!empty($filters['to'])) { $queryParams['to'] = $filters['to']; }
        $pdfUrl = base_url() . '/payments/records/pdf';
        if (!empty($queryParams)) {
          $pdfUrl .= '?' . http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);
        }
      ?>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h5 class="mb-0">Filter payments</h5>
          <small class="text-muted">Use filters below, then export with the PDF button.</small>
        </div>
        <a href="<?= htmlspecialchars($pdfUrl); ?>" class="btn btn-outline-dark">Download PDF</a>
      </div>
      <form method="get" class="row g-3 align-items-end mb-4">
        <div class="col-md-3">
          <label class="form-label fw-semibold text-muted small">Status</label>
          <select name="status" class="form-select">
            <option value="">All statuses</option>
            <?php foreach ($statusOptions as $statusOpt): ?>
              <option value="<?= htmlspecialchars($statusOpt); ?>" <?= ($filters['status'] ?? '') === $statusOpt ? 'selected' : ''; ?>><?= ucfirst($statusOpt); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold text-muted small">From</label>
          <input type="date" name="from" value="<?= htmlspecialchars($filters['from'] ?? ''); ?>" class="form-control" />
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold text-muted small">To</label>
          <input type="date" name="to" value="<?= htmlspecialchars($filters['to'] ?? ''); ?>" class="form-control" />
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button type="submit" class="btn btn-primary flex-grow-1">Apply filters</button>
          <a href="<?= base_url(); ?>/payments/records" class="btn btn-outline-secondary">Reset</a>
        </div>
      </form>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Invoice</th>
              <th>Patient</th>
              <th>Doctor</th>
              <th>Schedule</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Paid At</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($payments)): ?>
              <?php foreach ($payments as $payment): ?>
                <?php
                  $status = strtolower($payment['status'] ?? 'pending');
                  $badge = 'text-bg-secondary';
                  if ($status === 'paid') {
                    $badge = 'text-bg-success';
                  } elseif (in_array($status, ['expired','failed','cancelled','canceled'], true)) {
                    $badge = 'text-bg-danger';
                  } elseif ($status === 'pending') {
                    $badge = 'text-bg-warning text-dark';
                  }
                  $invoiceLabel = $payment['external_id'] ?? ('INV-' . ($payment['id'] ?? ''));
                  $schedule = $formatSlot($payment);
                  $amount = number_format((float)($payment['amount'] ?? 0), 2);
                  $paidAt = $payment['paid_at'] ?? ($payment['updated_at'] ?? null);
                ?>
                <tr>
                  <td>
                    <div class="fw-semibold"><?= htmlspecialchars($invoiceLabel); ?></div>
                    <small class="text-muted">#<?= (int)($payment['appointment_id'] ?? 0); ?></small>
                  </td>
                  <td><?= htmlspecialchars($payment['patient_name'] ?? 'N/A'); ?></td>
                  <td><?= htmlspecialchars($payment['doctor_name'] ?? 'N/A'); ?></td>
                  <td><?= htmlspecialchars($schedule); ?></td>
                  <td>₱ <?= $amount; ?></td>
                  <td><span class="badge <?= $badge; ?>" style="text-transform:capitalize;"><?= htmlspecialchars($status); ?></span></td>
                  <td><?= $paidAt ? date('M d, Y h:i A', strtotime($paidAt)) : '—'; ?></td>
                  <td class="text-end">
                    <?php if ($status === 'pending' && !empty($payment['invoice_url'])): ?>
                      <a href="<?= htmlspecialchars($payment['invoice_url']); ?>" class="btn btn-sm btn-primary" target="_blank" rel="noopener">Pay Now</a>
                    <?php elseif ($status === 'paid' && !empty($payment['invoice_url'])): ?>
                      <a href="<?= htmlspecialchars($payment['invoice_url']); ?>" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">View Receipt</a>
                    <?php elseif (in_array($status, ['failed','expired'], true) && !empty($payment['invoice_url'])): ?>
                      <a href="<?= htmlspecialchars($payment['invoice_url']); ?>" class="btn btn-sm btn-outline-warning" target="_blank" rel="noopener">View Details</a>
                    <?php else: ?>
                      <span class="text-muted small">—</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="text-center text-muted py-4">No payment records yet.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= base_url().'public/assets/ui.js'; ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
