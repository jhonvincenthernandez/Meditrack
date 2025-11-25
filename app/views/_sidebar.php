<?php
// Shared sidebar partial
// Uses $role if provided, otherwise falls back to session or 'admin'
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
$role = $role ?? ($_SESSION['role'] ?? 'admin');

switch ($role) {
  case 'doctor':
    $navSubtitle = 'Doctor Portal';
    $navLinks = [
      ['label' => '🏠 Dashboard', 'href' => base_url() . '/dashboard_doctor'],
      ['label' => '💰 Payment Records', 'href' => base_url() . '/payments/records'],
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
      ['label' => '💰 Payment Records', 'href' => base_url() . '/payments/records'],
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
      ['label' => '💰 Payment Records', 'href' => base_url() . '/payments/records'],
    ];
    break;
}
?>
<aside class="sidebar p-3" role="navigation" aria-label="Main sidebar">
  <a href="<?= base_url(); ?>/" class="d-flex align-items-center mb-4 text-decoration-none sidebar-brand">
    <div class="brand-logo" aria-hidden="true">
      <img src="<?= base_url() . 'public/assets/meditrack-logo.png'; ?>" alt="Meditrack logo" onerror="this.style.display='none'" />
    </div>
    <div class="ms-2">
      <div class="brand-title">MediTrack+</div>
      <small class="text-muted small"><?= htmlspecialchars($navSubtitle); ?></small>
    </div>
  </a>
  <nav class="nav flex-column">
    <?php foreach ($navLinks as $link): ?>
      <a class="nav-link<?= !empty($link['active']) ? ' active' : ''; ?>" href="<?= htmlspecialchars($link['href']); ?>"><?= htmlspecialchars($link['label']); ?></a>
    <?php endforeach; ?>
    <a href="<?= site_url('auth/logout'); ?>" class="btn btn-danger mt-3 w-100">Logout</a>
  </nav>
</aside>
