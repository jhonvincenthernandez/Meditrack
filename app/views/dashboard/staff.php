<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Staff Dashboard — MediTrack+</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar p-3">
    <h5>MediTrack+ Staff</h5>
    <hr>
    <nav class="nav flex-column">
        <a class="nav-link" href="<?= base_url(); ?>/dashboard_staff">Dashboard</a>
        <a href="<?= site_url('auth/logout'); ?>" class="btn btn-danger mt-3">Logout</a>
    </nav>
</aside>

<div class="main">
    <div class="topbar d-flex justify-content-between align-items-center">
        <h4 class="ms-3">My Dashboard</h4>
        <a href="<?= base_url(); ?>/appointments/staff_add" class="btn btn-primary">Add Appointment</a>
    </div>

    <div class="p-4">
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card-soft p-3">
                    <h5>Patients</h5>
                    <h3><?= $patient_count ?></h3>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card-soft p-3">
                    <h5>Appointments</h5>
                    <h3><?= $appointment_count ?></h3>
                </div>
            </div>
        </div>

        <div class="card-soft table-responsive">
            <h5 class="mb-3">Upcoming Appointments</h5>
            <table class="table table-hover align-middle mb-4">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Notes</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(!empty($upcoming)): foreach($upcoming as $a): ?>
                    <tr>
                        <?php
                            $slotDate  = $a['slot_date']  ?? null;
                            $startTime = $a['start_time'] ?? null;
                            $endTime   = $a['end_time']   ?? null;
                            if (!empty($slotDate)) {
                                $startStr = !empty($startTime) ? $startTime : '00:00:00';
                                $ts = strtotime($slotDate . ' ' . $startStr);
                                if ($ts !== false) {
                                    $datePart = date('M d, Y', $ts);
                                    $startPart = date('H:i', $ts);
                                    $range = $startPart;
                                    if (!empty($endTime)) {
                                        $te = strtotime($slotDate . ' ' . $endTime);
                                        if ($te !== false) { $range .= ' - ' . date('H:i', $te); }
                                    }
                                    $dateText = $datePart . ' ' . $range;
                                } else { $dateText = '-'; }
                            } else { $ts = false; $dateText = '-'; }
                        ?>
                        <td><?= htmlspecialchars($dateText) ?></td>
                        <td><?= htmlspecialchars($a['patient_name'] ?? $a['patient_id']) ?></td>
                        <td><?= htmlspecialchars(substr($a['notes'] ?? '',0,80)) ?></td>
                        <td>
                            <?php
                                if ($ts === false) {
                                    $status = 'Unknown';
                                } else {
                                    $status = $ts >= time() ? 'Upcoming' : 'Past';
                                }
                                $color = $status === 'Upcoming' ? 'text-success' : ($status === 'Past' ? 'text-muted' : 'text-secondary');
                            ?>
                            <span class="<?= $color ?>"><?= $status ?></span>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="4" class="text-muted text-center">No scheduled appointments.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>

            <h5 class="mb-3">Past Appointments</h5>
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Notes</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(!empty($past)): foreach($past as $a): ?>
                    <tr>
                        <?php
                            $slotDate  = $a['slot_date']  ?? null;
                            $startTime = $a['start_time'] ?? null;
                            $endTime   = $a['end_time']   ?? null;
                            if (!empty($slotDate)) {
                                $startStr = !empty($startTime) ? $startTime : '00:00:00';
                                $ts = strtotime($slotDate . ' ' . $startStr);
                                if ($ts !== false) {
                                    $datePart = date('M d, Y', $ts);
                                    $startPart = date('H:i', $ts);
                                    $range = $startPart;
                                    if (!empty($endTime)) {
                                        $te = strtotime($slotDate . ' ' . $endTime);
                                        if ($te !== false) { $range .= ' - ' . date('H:i', $te); }
                                    }
                                    $dateText = $datePart . ' ' . $range;
                                } else { $dateText = '-'; }
                            } else { $ts = false; $dateText = '-'; }
                        ?>
                        <td><?= htmlspecialchars($dateText) ?></td>
                        <td><?= htmlspecialchars($a['patient_name'] ?? $a['patient_id']) ?></td>
                        <td><?= htmlspecialchars(substr($a['notes'] ?? '',0,80)) ?></td>
                        <td>
                            <?php
                                if ($ts === false) {
                                    $status = 'Unknown';
                                } else {
                                    $status = $ts >= time() ? 'Upcoming' : 'Past';
                                }
                                $color = $status === 'Upcoming' ? 'text-success' : ($status === 'Past' ? 'text-muted' : 'text-secondary');
                            ?>
                            <span class="<?= $color ?>"><?= $status ?></span>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="4" class="text-muted text-center">No past appointments.</td></tr>
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
