<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Redirecting to Checkout — MediTrack+</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
</head>
<body>
<div class="main p-4">
  <div class="card-soft container-narrow">
    <?php
      $invoiceUrl = $appointment['invoice_url'] ?? '';
      $invoiceId = $appointment['invoice_id'] ?? 'N/A';
      $amount = number_format((float)($appointment['amount'] ?? 0), 2);
      $paymentStatus = strtolower($appointment['payment_status'] ?? 'pending');
      $slot = trim(($appointment['slot_date'] ?? '').' '.($appointment['start_time'] ?? ''));
      $reissuedInvoice = !empty($reissued);
      $returnUrl = !empty($return_url) ? $return_url : (rtrim(base_url(), '/') . '/appointments');
      $returnLabel = !empty($return_label) ? $return_label : 'Back to appointments';
    ?>
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h4 class="mb-0">Checkout invoice</h4>
      <span class="badge bg-secondary text-uppercase">Invoice #<?= htmlspecialchars($invoiceId); ?></span>
    </div>
    <?php if($reissuedInvoice): ?>
      <div class="alert alert-info">
        We generated a fresh invoice because the previous one could no longer accept payments.
      </div>
    <?php endif; ?>

    <?php if($invoiceUrl): ?>
      <div class="alert alert-success">
        We are opening the secure Xendit checkout page in a new tab. If it does not appear automatically, click the button below.
      </div>
    <?php else: ?>
      <div class="alert alert-danger">
        No invoice URL was stored for this appointment. Please try saving the appointment again.
      </div>
    <?php endif; ?>

    <dl class="row small">
      <dt class="col-sm-4">Patient</dt>
      <dd class="col-sm-8"><?= htmlspecialchars($appointment['patient_name'] ?? 'N/A'); ?></dd>
      <dt class="col-sm-4">Doctor</dt>
      <dd class="col-sm-8"><?= htmlspecialchars($appointment['doctor_name'] ?? 'N/A'); ?></dd>
      <dt class="col-sm-4">Schedule</dt>
      <dd class="col-sm-8"><?= htmlspecialchars($slot ?: 'TBD'); ?></dd>
      <dt class="col-sm-4">Amount</dt>
      <dd class="col-sm-8">₱ <?= $amount; ?></dd>
      <dt class="col-sm-4">Payment status</dt>
      <dd class="col-sm-8 text-capitalize"><?= htmlspecialchars($paymentStatus); ?></dd>
    </dl>

    <?php if($invoiceUrl): ?>
      <a href="<?= htmlspecialchars($invoiceUrl); ?>" target="_blank" rel="noopener" class="btn btn-primary">Open secure checkout</a>
    <?php endif; ?>
    <a href="<?= htmlspecialchars($returnUrl); ?>" class="btn btn-link"><?= htmlspecialchars($returnLabel); ?></a>
  </div>
</div>
<script>
(function(){
  var invoiceUrl = <?= json_encode($invoiceUrl); ?>;
  if(invoiceUrl){
    setTimeout(function(){ window.open(invoiceUrl, '_blank'); }, 800);
  }
})();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
