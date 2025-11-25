<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Payment Status — MediTrack+</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" crossorigin="anonymous">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
<link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
<style>
.success-icon{width:80px;height:80px;margin:0 auto 1.5rem;border-radius:50%;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;animation:scaleIn .5s ease-out}
.failed-icon{width:80px;height:80px;margin:0 auto 1.5rem;border-radius:50%;background:linear-gradient(135deg,#ef4444,#dc2626);display:flex;align-items:center;justify-content:center;animation:scaleIn .5s ease-out}
@keyframes scaleIn{0%{transform:scale(0);opacity:0}100%{transform:scale(1);opacity:1}}
.info-card{background:#f8f9fa;border-radius:12px;padding:1.5rem;margin:1.5rem 0}
.info-row{display:flex;justify-content:space-between;padding:.75rem 0;border-bottom:1px solid #e5e7eb}
.info-row:last-child{border-bottom:none}
</style>
</head>
<body>
<div class="main p-4">
  <div class="card-soft container-narrow text-center" style="max-width:600px;margin:2rem auto;">
    <?php
      $state = $status ?? 'pending';
      $isSuccess = $state === 'success';
      $isFailed = $state === 'failed';
      $payment = $payment ?? [];
      $appointment = $appointment ?? [];
      $returnUrl = !empty($return_url) ? $return_url : (rtrim(base_url(), '/') . '/appointments');
    ?>
    
    <?php if($isSuccess): ?>
      <div class="success-icon">
        <svg width="48" height="48" fill="white" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
      </div>
      <h2 class="mb-2 fw-bold text-success">✓ Payment Successful!</h2>
      <p class="text-muted mb-4">Your appointment has been confirmed</p>
    <?php elseif($isFailed): ?>
      <div class="failed-icon">
        <svg width="48" height="48" fill="white" viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
      </div>
      <h2 class="mb-2 fw-bold text-danger">✗ Payment Failed</h2>
      <p class="text-muted mb-4">Please try again or contact support</p>
    <?php else: ?>
      <h2 class="mb-2 fw-bold"><?= htmlspecialchars($message ?? 'Payment Processing'); ?></h2>
    <?php endif; ?>

    <?php if(!empty($external_id)): ?>
      <div class="alert alert-light border mb-4">
        <small class="text-muted d-block">Reference Number</small>
        <strong class="fs-6"><?= htmlspecialchars($external_id); ?></strong>
      </div>
    <?php endif; ?>

    <?php if(!empty($appointment)): ?>
      <div class="info-card text-start">
        <h6 class="fw-bold mb-3">Appointment Details</h6>
        <?php if(!empty($appointment['patient_name'])): ?>
        <div class="info-row">
          <span class="text-muted">Patient</span>
          <strong><?= htmlspecialchars($appointment['patient_name']); ?></strong>
        </div>
        <?php endif; ?>
        <?php if(!empty($appointment['doctor_name'])): ?>
        <div class="info-row">
          <span class="text-muted">Doctor</span>
          <strong><?= htmlspecialchars($appointment['doctor_name']); ?></strong>
        </div>
        <?php endif; ?>
        <?php if(!empty($appointment['slot_date'])): ?>
        <div class="info-row">
          <span class="text-muted">Date & Time</span>
          <strong><?= date('M d, Y', strtotime($appointment['slot_date'])); ?>
          <?php if(!empty($appointment['start_time'])): ?>
            • <?= date('h:i A', strtotime($appointment['start_time'])); ?>
          <?php endif; ?>
          </strong>
        </div>
        <?php endif; ?>
        <?php if(!empty($payment['amount'])): ?>
        <div class="info-row">
          <span class="text-muted">Amount Paid</span>
          <strong class="text-success">₱ <?= number_format((float)$payment['amount'], 2); ?></strong>
        </div>
        <?php endif; ?>
        <?php if(!empty($payment['payment_channel'])): ?>
        <div class="info-row">
          <span class="text-muted">Payment Method</span>
          <strong><?= htmlspecialchars($payment['payment_channel']); ?></strong>
        </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if($isSuccess): ?>
      <div class="alert alert-info text-start mb-4">
        <h6 class="fw-bold mb-2">📌 What's Next?</h6>
        <ul class="mb-0 ps-3">
          <li>Check your email for confirmation details</li>
          <li>Bring a valid ID and medical records if needed</li>
        </ul>
      </div>
    <?php endif; ?>

    <div class="d-flex gap-2 justify-content-center mt-4">
      <?php if($isSuccess && !empty($payment['invoice_url'])): ?>
        <a href="<?= htmlspecialchars($payment['invoice_url']); ?>" class="btn btn-outline-secondary" target="_blank" rel="noopener">📝 View Receipt</a>
      <?php endif; ?>
      <a href="<?= htmlspecialchars($returnUrl); ?>" class="btn btn-primary">← Back to Dashboard</a>
    </div>

    <?php if($isSuccess): ?>
      <p class="text-muted small mt-4 mb-0">This page will auto-close in <span id="countdown">10</span> seconds</p>
    <?php endif; ?>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<?php if($isSuccess): ?>
<script>
let count=10;const timer=setInterval(()=>{count--;document.getElementById('countdown').textContent=count;if(count<=0){clearInterval(timer);window.location.href='<?= htmlspecialchars($returnUrl); ?>';}},1000);
</script>
<?php endif; ?>
</body>
</html>
