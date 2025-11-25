<?php
$payments = $payments ?? [];
$filters = $filters ?? ['status' => '', 'from' => '', 'to' => ''];
$generatedAt = $generated_at ?? date('M d, Y h:i A');
$totals = $totals ?? ['count' => 0, 'amount' => 0, 'status_breakdown' => []];
$roleLabel = ucfirst($role ?? 'admin');

$formatMoney = function ($value): string {
    return number_format((float) $value, 2);
};

$formatSlot = function (array $entry): string {
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

$statusBadgeClass = function (string $status): string {
  switch ($status) {
    case 'paid':
      return 'badge badge-success';
    case 'pending':
      return 'badge badge-warning';
    case 'expired':
    case 'failed':
    case 'cancelled':
    case 'canceled':
      return 'badge badge-danger';
    default:
      return 'badge badge-neutral';
  }
};

$filterChips = array_filter([
    $filters['status'] ? 'Status: ' . ucfirst($filters['status']) : null,
    $filters['from'] ? 'From: ' . date('M d, Y', strtotime($filters['from'])) : null,
    $filters['to'] ? 'To: ' . date('M d, Y', strtotime($filters['to'])) : null,
]);
?>

<div class="report-header">
  <h1>MediTrack+ Payment Records</h1>
  <p class="report-meta">Generated on <?= htmlspecialchars($generatedAt); ?> · <?= htmlspecialchars($roleLabel); ?> Portal</p>
</div>

<div class="summary-grid">
  <div class="summary-card">
    <h3>Total Transactions</h3>
    <strong><?= (int) $totals['count']; ?></strong>
    <p class="report-meta">matching current filters</p>
  </div>
  <div class="summary-card">
    <h3>Total Amount</h3>
    <strong>₱ <?= $formatMoney($totals['amount']); ?></strong>
    <p class="report-meta">sums raw payment amounts</p>
  </div>
  <div class="summary-card">
    <h3>Filters</h3>
    <?php if (!empty($filterChips)): ?>
      <?php foreach ($filterChips as $chip): ?>
        <p class="report-meta">• <?= htmlspecialchars($chip); ?></p>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="report-meta">No filters applied</p>
    <?php endif; ?>
  </div>
</div>

<div class="status-table">
  <table>
    <thead>
      <tr>
        <th>Status</th>
        <th>Count</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($totals['status_breakdown'])): ?>
        <?php foreach ($totals['status_breakdown'] as $status => $count): ?>
          <tr>
            <td><?= htmlspecialchars(ucfirst($status)); ?></td>
            <td><?= (int) $count; ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="2">No payment data available.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<table>
  <thead>
    <tr>
      <th>#</th>
      <th>Invoice</th>
      <th>Patient</th>
      <th>Doctor</th>
      <th>Schedule</th>
      <th>Amount</th>
      <th>Status</th>
      <th>Paid At</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($payments)): ?>
      <?php foreach ($payments as $idx => $payment): ?>
        <?php
          $status = strtolower($payment['status'] ?? 'pending');
          $schedule = $formatSlot($payment);
          $amount = $formatMoney($payment['amount'] ?? 0);
          $paidAt = $payment['paid_at'] ?? ($payment['updated_at'] ?? null);
        ?>
        <tr>
          <td><?= $idx + 1; ?></td>
          <td>
            <strong><?= htmlspecialchars($payment['external_id'] ?? ('INV-' . ($payment['id'] ?? 'N/A'))); ?></strong><br>
            <small class="report-meta">Appointment #<?= (int) ($payment['appointment_id'] ?? 0); ?></small>
          </td>
          <td><?= htmlspecialchars($payment['patient_name'] ?? 'N/A'); ?></td>
          <td><?= htmlspecialchars($payment['doctor_name'] ?? 'N/A'); ?></td>
          <td><?= htmlspecialchars($schedule); ?></td>
          <td>₱ <?= $amount; ?></td>
          <td>
            <span class="<?= $statusBadgeClass($status); ?>">
              <?= htmlspecialchars(ucfirst($status)); ?>
            </span>
          </td>
          <td><?= $paidAt ? date('M d, Y h:i A', strtotime($paidAt)) : '—'; ?></td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr>
        <td colspan="8" style="text-align:center; padding:20px;">No payment records found for the selected filters.</td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>
