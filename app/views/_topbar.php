<?php
// Reusable topbar partial
// Expected variables (optional):
//  - $topbar_title : string
//  - $topbar_right : string (raw HTML)
//  - $topbar_class : string (additional classes)
?>
<div class="topbar <?= isset($topbar_class) ? $topbar_class : '' ?>">
  <div class="d-flex align-items-center gap-3">
    <!-- noscript-friendly toggle (JS will reuse existing .menu-toggle if present) -->
    <button type="button" id="menuToggle" class="menu-toggle" aria-controls="sidebar" aria-expanded="false" aria-label="Toggle menu">
      <span class="bar" aria-hidden="true"></span>
    </button>

    <?php if (!empty($topbar_title)): ?>
      <h4 class="m-0"><?= htmlspecialchars($topbar_title) ?></h4>
    <?php else: ?>
        <!-- Intentionally left blank: no logo or textual brand in topbar per user request -->
    <?php endif; ?>
  </div>

  <div class="topbar-right">
    <?php if (!empty($topbar_right)) echo $topbar_right; ?>
  </div>
</div>
