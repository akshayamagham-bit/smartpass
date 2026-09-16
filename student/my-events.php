<?php
require_once __DIR__ . '/../includes/functions.php';
require_student();
$sid = (int)$_SESSION['student_id'];

$upcoming = db()->query(
  "SELECT e.*, r.status AS reg_status FROM registrations r
   JOIN events e ON r.event_id=e.event_id
   WHERE r.student_id=$sid AND e.event_date >= CURDATE()
   ORDER BY e.event_date ASC"
)->fetch_all(MYSQLI_ASSOC);

$past = db()->query(
  "SELECT e.*, r.status AS reg_status,
          (SELECT status FROM attendance a WHERE a.student_id=$sid AND a.event_id=e.event_id) AS att_status
   FROM registrations r JOIN events e ON r.event_id=e.event_id
   WHERE r.student_id=$sid AND e.event_date < CURDATE()
   ORDER BY e.event_date DESC"
)->fetch_all(MYSQLI_ASSOC);

$dashboardTitle = 'My Events';
$activeItem = 'my-events';
$role = 'student';
require __DIR__ . '/../includes/dashboard_header.php';
?>
<div class="tab-section">
  <div class="tab-buttons" id="myEventsTabs">
    <button class="tab-btn active" data-tab="upcoming">Upcoming (<?= count($upcoming) ?>)</button>
    <button class="tab-btn" data-tab="completed">Completed (<?= count($past) ?>)</button>
  </div>

  <div class="tab-panel active" id="tab-upcoming">
    <?php if ($upcoming): ?>
      <div class="event-grid">
        <?php foreach ($upcoming as $ev): ?>
          <div class="event-card">
            <div class="event-card-top cat-<?= e(strtolower($ev['category'])) ?>">
              <span class="event-cat"><?= e($ev['category']) ?></span>
              <span class="event-cat-icon"><?= category_icon($ev['category']) ?></span>
            </div>
            <div class="event-card-body">
              <h3><?= e($ev['event_name']) ?></h3>
              <p class="event-meta">📅 <?= e(date('d M Y', strtotime($ev['event_date']))) ?> · <?= e($ev['event_time']) ?></p>
              <p class="event-meta">📍 <?= e($ev['venue']) ?></p>
              <span class="badge badge-info">Registered</span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="empty-state">You have no upcoming events. <a href="<?= e(base_url()) ?>student/events.php">Discover events →</a></p>
    <?php endif; ?>
  </div>

  <div class="tab-panel" id="tab-completed">
    <?php if ($past): ?>
      <div class="event-grid">
        <?php foreach ($past as $ev):
          $present = $ev['att_status'] === 'present';
        ?>
          <div class="event-card">
            <div class="event-card-top cat-<?= e(strtolower($ev['category'])) ?> <?= $present?'':'faded' ?>">
              <span class="event-cat"><?= e($ev['category']) ?></span>
              <span class="event-cat-icon"><?= category_icon($ev['category']) ?></span>
            </div>
            <div class="event-card-body">
              <h3><?= e($ev['event_name']) ?></h3>
              <p class="event-meta">📅 <?= e(date('d M Y', strtotime($ev['event_date']))) ?></p>
              <p class="event-meta">📍 <?= e($ev['venue']) ?></p>
              <?php if ($present): ?>
                <span class="badge badge-success">✓ Attended · Stamp earned</span>
              <?php elseif ($ev['att_status'] === 'absent'): ?>
                <span class="badge badge-error">Absent</span>
              <?php else: ?>
                <span class="badge badge-info">Awaiting attendance</span>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="empty-state">No completed events yet. Attend an event to earn your first stamp!</p>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>
