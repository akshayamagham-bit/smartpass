<?php
/**
 * Dashboard header with sidebar.
 * Pass $dashboardTitle, $role ('student'|'admin'), $activeItem (sidebar key).
 */
require_once __DIR__ . '/auth.php';
$isStudent = $role === 'student';
$base = base_url();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($dashboardTitle ?? 'Dashboard') ?> · SmartPass</title>
<link rel="stylesheet" href="<?= e($base) ?>css/style.css">
</head>
<body class="dashboard-body">
<div class="dashboard-layout">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <span class="brand-mark">SP</span>
      <span class="brand-name">SMART<span class="brand-accent">PASS</span></span>
    </div>
    <nav class="sidebar-nav">
      <?php if ($isStudent):
       $items = [
     'dashboard'      => ['Dashboard', $base.'student/dashboard.php'],
     'events'         => ['Discover Events', $base.'student/events.php'],
     'my-events'      => ['My Events', $base.'student/my-events.php'],
     'passport'       => ['My Passport', $base.'student/passport.php'],
     'event-passes'   => ['Event Passes', $base.'student/event-passes.php'],
     'achievements'   => ['Achievements', $base.'student/achievements.php'],
     'leaderboard'    => ['Leaderboard', $base.'student/leaderboard.php'],
     'profile'        => ['Profile', $base.'student/profile.php'],
     ];
      else:
        $items = [
          'dashboard'      => ['Dashboard', $base.'admin/dashboard.php'],
          'events'         => ['Manage Events', $base.'admin/events.php'],
          'add-event'      => ['Add Event', $base.'admin/add-event.php'],
          'students'       => ['Students', $base.'admin/students.php'],
          'registrations'  => ['Registrations', $base.'admin/registrations.php'],
          'attendance'     => ['Attendance', $base.'admin/attendance.php'],
          'qr-checkin'     => ['QR Check-in', $base.'admin/qr-checkin.php'],
          'reports'        => ['Reports', $base.'admin/reports.php'],
        ];
      endif;
      foreach ($items as $key => [$label, $url]): ?>
        <a href="<?= e($url) ?>" class="<?= ($activeItem ?? '')===$key?'active':'' ?>"><?= e($label) ?></a>
      <?php endforeach; ?>
      <a href="<?= e($base) ?>logout.php" class="sidebar-logout">Logout</a>
    </nav>
  </aside>
  <div class="dashboard-main">
    <header class="dashboard-topbar">
      <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">☰</button>
      <h1><?= e($dashboardTitle ?? 'Dashboard') ?></h1>
      <div class="topbar-user">
        <?php if ($isStudent): $s = current_student(); ?>
          <span><?= e($s['name'] ?? 'Student') ?></span>
          <span class="avatar"><?= e(strtoupper(substr($s['name'] ?? 'S',0,1))) ?></span>
        <?php else: $a = current_admin(); ?>
          <span><?= e($a['name'] ?? 'Admin') ?></span>
          <span class="avatar">A</span>
        <?php endif; ?>
      </div>
    </header>
    <div class="dashboard-content">
