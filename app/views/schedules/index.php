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
<?php include APP_DIR . 'views/_sidebar.php'; ?>

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

        <?php
            $groups = $slot_groups ?? [
                'available' => [],
                'booked' => [],
                'past' => [],
                'completed' => [],
                'cancelled' => [],
            ];

            $counts = $slot_counts ?? array_map('count', $groups);

            $tabConfig = [
                'available' => [
                    'label' => 'Available',
                    'summary_class' => 'bg-success bg-opacity-25 border border-success-subtle text-success',
                    'badge_class' => 'text-bg-success',
                    'badge_label' => 'Available',
                    'empty' => 'No available slots right now.',
                ],
                'booked' => [
                    'label' => 'Booked',
                    'summary_class' => 'bg-danger bg-opacity-25 border border-danger-subtle text-danger',
                    'badge_class' => 'text-bg-danger',
                    'badge_label' => 'Booked',
                    'empty' => 'No booked slots at the moment.',
                ],
                'past' => [
                    'label' => 'Past',
                    'summary_class' => 'bg-secondary bg-opacity-25 border border-secondary-subtle text-secondary',
                    'badge_class' => 'text-bg-secondary',
                    'badge_label' => 'Past',
                    'empty' => 'No past slots found.',
                ],
                'completed' => [
                    'label' => 'Completed',
                    'summary_class' => 'bg-success bg-opacity-10 border border-success-subtle text-success',
                    'badge_class' => 'text-bg-success',
                    'badge_label' => 'Done',
                    'empty' => 'No completed slots yet.',
                ],
                'cancelled' => [
                    'label' => 'Cancelled',
                    'summary_class' => 'bg-secondary bg-opacity-25 border border-secondary-subtle text-secondary',
                    'badge_class' => 'text-bg-secondary',
                    'badge_label' => 'Cancelled',
                    'empty' => 'No cancelled slots recorded.',
                ],
            ];

            $tabKeys = array_keys($tabConfig);
            $activeKey = $tabKeys[0] ?? 'available';
        ?>

        <div class="row g-3 mb-3">
            <?php foreach ($tabConfig as $key => $config): ?>
                <div class="col-12 col-sm-6 col-xl-4 col-xxl-3">
                    <div class="status-pill <?= $config['summary_class']; ?> rounded p-3 h-100">
                        <div class="fw-semibold text-uppercase small"><?= htmlspecialchars($config['label']); ?></div>
                        <div class="display-6 mb-0"><?= (int)($counts[$key] ?? 0); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <ul class="nav nav-pills mb-3" id="scheduleTab" role="tablist">
            <?php foreach ($tabConfig as $key => $config):
                $active = $key === $activeKey;
            ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link<?= $active ? ' active' : ''; ?>" id="<?= $key; ?>-tab" data-bs-toggle="pill" data-bs-target="#<?= $key; ?>-pane" type="button" role="tab" aria-controls="<?= $key; ?>-pane" aria-selected="<?= $active ? 'true' : 'false'; ?>">
                    <?= htmlspecialchars($config['label']); ?>
                    <span class="badge bg-light text-dark ms-2"><?= (int)($counts[$key] ?? 0); ?></span>
                </button>
            </li>
            <?php endforeach; ?>
        </ul>

        <div class="tab-content" id="scheduleTabContent">
            <?php foreach ($tabConfig as $key => $config):
                $active = $key === $activeKey;
                $rows = $groups[$key] ?? [];
            ?>
            <div class="tab-pane fade<?= $active ? ' show active' : ''; ?>" id="<?= $key; ?>-pane" role="tabpanel" aria-labelledby="<?= $key; ?>-tab" tabindex="0">
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
                            <?php if (!empty($rows)): ?>
                                <?php foreach ($rows as $s): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($s['date']); ?></td>
                                        <td><?= htmlspecialchars($s['doctor_name'] ?? 'Unknown'); ?><?= !empty($s['specialty']) ? ' — '.htmlspecialchars($s['specialty']) : ''; ?></td>
                                        <td><?= htmlspecialchars($s['start_time']); ?></td>
                                        <td><?= htmlspecialchars($s['end_time']); ?></td>
                                        <td><span class="badge <?= $config['badge_class']; ?>"><?= htmlspecialchars($config['badge_label']); ?></span></td>
                                        <td>
                                            <?php if ($key === 'available'): ?>
                                                <a href="<?= base_url(); ?>/schedules/edit_form/<?= (int)$s['id']; ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                                <a href="<?= base_url(); ?>/schedules/delete/<?= (int)$s['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this slot?')">Delete</a>
                                            <?php elseif ($key === 'past'): ?>
                                                <a href="<?= base_url(); ?>/schedules/delete/<?= (int)$s['id']; ?>" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Remove this expired slot?')">Remove</a>
                                            <?php else: ?>
                                                <span class="text-muted small">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4"><?= htmlspecialchars($config['empty']); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= base_url().'public/assets/ui.js'; ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
