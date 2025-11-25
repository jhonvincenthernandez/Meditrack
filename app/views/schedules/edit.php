<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit Schedule — MediTrack+</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url().'public/assets/theme.css'; ?>">
    <script>
        if (window.history.replaceState) { window.history.replaceState(null, '', window.location.href); }
    </script>
</head>
<body>

<!-- Sidebar -->
<?php include APP_DIR . 'views/_sidebar.php'; ?>

<!-- Main -->
<div class="main">
    <div class="topbar">
        <h4 class="m-0">Schedules — Edit Slot</h4>
        <span class="text-muted small">Today: <?= date('M d, Y'); ?></span>
    </div>

    <?php if(!empty($_SESSION['slot_warning'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <?= $_SESSION['slot_warning']; unset($_SESSION['slot_warning']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div id="clientAlert" class="alert alert-warning d-none" role="alert"></div>

    <div class="card-soft">
        <h5 class="mb-3">Edit Doctor Slot</h5>
        <form method="post" action="<?= base_url(); ?>/schedules/update/<?= $slot['id']; ?>" id="slotForm">
        <div class="mb-3">
            <label class="form-label">Doctor</label>
            <select name="doctor_id" class="form-select" required>
                <option value="">Select Doctor</option>
                <?php foreach($doctors as $d): ?>
                    <option value="<?= $d['id']; ?>" <?= $slot['doctor_id']==$d['id']?'selected':''; ?>>
                        <?= htmlspecialchars($d['name']); ?> — <?= htmlspecialchars($d['specialty']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" name="date" class="form-control" min="<?= date('Y-m-d'); ?>" required value="<?= $slot['date']; ?>">
        </div>

        <div class="row g-3 mb-3">
            <div class="col">
                <label class="form-label">Start Time</label>
                <input type="time" name="start_time" class="form-control" required value="<?= $slot['start_time']; ?>">
            </div>
            <div class="col">
                <label class="form-label">End Time</label>
                <input type="time" name="end_time" class="form-control" required value="<?= $slot['end_time']; ?>">
            </div>
        </div>

                <button class="btn btn-primary">Update Slot</button>
                <a href="<?= base_url(); ?>/schedules" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>

<script>
    (function(){
        const form = document.getElementById('slotForm');
        const start = form.querySelector('input[name="start_time"]');
        const end = form.querySelector('input[name="end_time"]');
        const alertBox = document.getElementById('clientAlert');

        function showWarn(msg){
            alertBox.textContent = msg;
            alertBox.classList.remove('d-none');
        }
        function clearWarn(){ alertBox.classList.add('d-none'); }

        function syncMin(){
            if (start.value) {
                end.min = start.value;
            } else {
                end.removeAttribute('min');
            }
        }

            function validateTimes(){
                end.setCustomValidity('');
                if (start.value && end.value && end.value <= start.value) {
                    end.setCustomValidity('End time must be after start time.');
                    return false;
                }
                return true;
            }

            start.addEventListener('change', () => { clearWarn(); syncMin(); validateTimes(); });
            end.addEventListener('change', () => { clearWarn(); validateTimes(); });
            end.addEventListener('input', () => { clearWarn(); validateTimes(); });

        form.addEventListener('submit', function(e){
                if (!validateTimes()) {
                    e.preventDefault();
                    showWarn('End time must be after start time.');
                    end.reportValidity();
                }
        });

    syncMin();
    })();
</script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= base_url().'public/assets/ui.js'; ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
