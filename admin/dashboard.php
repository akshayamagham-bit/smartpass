<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$recentEvents = db()->query('SELECT * FROM events ORDER BY created_at DESC LIMIT 5')->fetch_all(MYSQLI_ASSOC);
$recentRegs = db()->query(
  "SELECT r.registration_id, s.name AS student_name, e.event_name, r.registration_date
   FROM registrations r JOIN students s ON r.student_id=s.student_id JOIN events e ON r.event_id=e.event_id
   ORDER BY r.registration_date DESC LIMIT 5"
)->fetch_all(MYSQLI_ASSOC);

$dashboardTitle = 'Admin Dashboard';
$activeItem = 'dashboard';
$role = 'admin';
require __DIR__ . '/../includes/dashboard_header.php';
?>
<div class="dash-greeting">
  <h2>Welcome, <?= e(current_admin()['name']) ?> 👋</h2>
  <p>Here's your campus event platform at a glance.</p>
</div>

<div class="stat-cards">
  <div class="stat-card"><span class="stat-card-icon">👥</span><div><span class="stat-card-num"><?= total_students() ?></span><span class="stat-card-label">Total Students</span></div></div>
  <div class="stat-card"><span class="stat-card-icon">📅</span><div><span class="stat-card-num"><?= total_events() ?></span><span class="stat-card-label">Total Events</span></div></div>
  <div class="stat-card"><span class="stat-card-icon">✓</span><div><span class="stat-card-num"><?= total_registrations() ?></span><span class="stat-card-label">Registrations</span></div></div>
  <div class="stat-card"><span class="stat-card-icon">🎫</span><div><span class="stat-card-num"><?= total_stamps() ?></span><span class="stat-card-label">Stamps Issued</span></div></div>
</div>

<div class="quick-actions">
  <h3>Quick Actions</h3>
  <div class="qa-grid">
    <a href="<?= e(base_url()) ?>admin/add-event.php" class="qa-card"><span class="qa-icon">+</span><span>Create Event</span></a>
    <a href="<?= e(base_url()) ?>admin/events.php" class="qa-card"><span class="qa-icon">📋</span><span>Manage Events</span></a>
    <a href="<?= e(base_url()) ?>admin/registrations.php" class="qa-card"><span class="qa-icon">📝</span><span>View Registrations</span></a>
    <a href="<?= e(base_url()) ?>admin/attendance.php" class="qa-card"><span class="qa-icon">✓</span><span>Verify Attendance</span></a>
  </div>
</div>

<div class="dash-grid">
  <div class="dash-panel">
    <div class="panel-head"><h3>Recent Events</h3><a href="<?= e(base_url()) ?>admin/events.php" class="link-arrow">All →</a></div>
    <ul class="mini-event-list">
      <?php foreach ($recentEvents as $ev): ?>
        <li>
          <a href="<?= e(base_url()) ?>admin/edit-event.php?id=<?= (int)$ev['event_id'] ?>" class="mini-event">
            <span class="mini-cat cat-<?= e(strtolower($ev['category'])) ?>"><?= category_icon($ev['category']) ?></span>
            <div><strong><?= e($ev['event_name']) ?></strong><span><?= e(date('d M Y', strtotime($ev['event_date']))) ?> · <?= e($ev['venue']) ?></span></div>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <div class="dash-panel">
    <div class="panel-head"><h3>Recent Registrations</h3><a href="<?= e(base_url()) ?>admin/registrations.php" class="link-arrow">All →</a></div>
    <ul class="activity-list">
      <?php foreach ($recentRegs as $r): ?>
        <li><span class="activity-tick">📝</span> <strong><?= e($r['student_name']) ?></strong> registered for <strong><?= e($r['event_name']) ?></strong></li>
      <?php endforeach; ?>
      <?php if (!$recentRegs): ?><li class="empty-state small">No registrations yet.</li><?php endif; ?>
    </ul>
  </div>
</div>
<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>
