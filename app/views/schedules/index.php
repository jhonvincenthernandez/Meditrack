<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Schedules — MediTrack+</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
    <script>
        // Prevent resubmission dialog on refresh after POST redirect
        if (window.history.replaceState) { window.history.replaceState(null, '', window.location.href); }
    </script>
    </head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <a href="<?= base_url(); ?>/" class="d-flex align-items-center mb-4 text-decoration-none">
        <div class="brand-logo"></div>
        <div class="ms-2">
            <div class="brand-title">MediTrack+</div>
            <small class="text-muted">Clinic Manager</small>
        </div>
    </a>
    <nav class="nav flex-column">
        <a class="nav-link" href="<?= base_url(); ?>/dashboard_admin">🏠 Dashboard</a>
        <a class="nav-link" href="<?= base_url(); ?>/users">👥 Users</a>
        <a class="nav-link" href="<?= base_url(); ?>/patients">🧾 Patients</a>
        <a class="nav-link" href="<?= base_url(); ?>/doctors">🩺 Doctors</a>
        <a class="nav-link" href="<?= base_url(); ?>/appointments">📅 Appointments</a>
        <a class="nav-link active" href="<?= base_url(); ?>/schedules">📆 Schedules</a>
        <a href="<?= site_url('auth/logout'); ?>" class="btn btn-danger mt-3">Logout</a>
    </nav>
 </aside>

<!-- Main -->
<div class="main">
    <div class="topbar">
        <h4 class="m-0">Schedules</h4>
        <span class="text-muted small">Today: <?= date('M d, Y'); ?></span>
    </div>

    <?php if (!empty($_SESSION['slot_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['slot_success']; unset($_SESSION['slot_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['slot_warning'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <?= $_SESSION['slot_warning']; unset($_SESSION['slot_warning']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card-soft">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="m-0">Doctor Slots</h5>
            <a href="<?= base_url(); ?>/schedules/add_form" class="btn btn-primary">+ Add Slot</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Doctor</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($slots)): ?>
                        <?php foreach ($slots as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['date']); ?></td>
                                <td><?= htmlspecialchars($s['doctor_name']); ?> — <?= htmlspecialchars($s['specialty']); ?></td>
                                <td><?= htmlspecialchars($s['start_time']); ?></td>
                                <td><?= htmlspecialchars($s['end_time']); ?></td>
                                <td>
                                    <?php
                                        $now = time();
                                        $slotEndTs = strtotime($s['date'].' '.$s['end_time']);
                                        $isPast = $slotEndTs !== false && $slotEndTs < $now;
                                        $apptStatus = $s['appt_status'] ?? null;
                                    ?>
                                    <?php if ($apptStatus === 'completed'): ?>
                                        <span class="badge text-bg-success">Done</span>
                                    <?php elseif ($apptStatus === 'cancelled'): ?>
                                        <span class="badge text-bg-secondary">Cancelled</span>
                                    <?php elseif ($isPast): ?>
                                        <span class="badge bg-secondary">Past</span>
                                    <?php elseif (!empty($s['is_booked'])): ?>
                                        <span class="badge bg-danger">Booked</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Available</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= base_url(); ?>/schedules/edit_form/<?= $s['id']; ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <a href="<?= base_url(); ?>/schedules/delete/<?= $s['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this slot?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted">No slots found</td></tr>
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
