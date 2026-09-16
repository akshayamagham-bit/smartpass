<?php
require_once __DIR__ . '/../includes/functions.php';
require_student();
$student = current_student();
$sid = (int)$student['student_id'];

$stampCount = student_stamp_count($sid);
$rank = student_rank($sid);
$upcoming = db()->query(
  "SELECT e.* FROM registrations r JOIN events e ON r.event_id=e.event_id
   WHERE r.student_id=$sid AND e.event_date >= CURDATE() ORDER BY e.event_date ASC LIMIT 5"
)->fetch_all(MYSQLI_ASSOC);

$recent = db()->query(
  "(SELECT 'Registered for' AS action, e.event_name AS target, r.registration_date AS d
    FROM registrations r JOIN events e ON r.event_id=e.event_id WHERE r.student_id=$sid)
   UNION
   (SELECT 'Earned stamp for' AS action, e.event_name AS target, s.earned_date AS d
    FROM stamps s JOIN events e ON s.event_id=e.event_id WHERE s.student_id=$sid)
   ORDER BY d DESC LIMIT 6"
)->fetch_all(MYSQLI_ASSOC);

$totalRegistered = db()->query("SELECT COUNT(*) c FROM registrations WHERE student_id=$sid")->fetch_assoc()['c'];
$progressTarget = max(20, (int)ceil($stampCount/5)*5);
$leaderboard = db()->query(
  "SELECT s.name, s.department, COUNT(st.stamp_id) AS stamps
   FROM students s LEFT JOIN stamps st ON s.student_id=st.student_id
   GROUP BY s.student_id ORDER BY stamps DESC, s.name ASC LIMIT 5"
)->fetch_all(MYSQLI_ASSOC);

$hour = (int)date('H');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$unlocked = student_unlocked_codes($sid);
$allAch = all_achievements();

$dashboardTitle = 'Dashboard';
$activeItem = 'dashboard';
$role = 'student';
require __DIR__ . '/../includes/dashboard_header.php';
?>
<?php if (isset($_GET['welcome'])): ?>
  <div class="alert alert-success">Welcome to SmartPass, <?= e($student['name']) ?>! Your journey starts now.</div>
<?php endif; ?>

<div class="dash-greeting">
  <h2><?= e($greeting) ?>, <?= e($student['name']) ?> 👋</h2>
  <p>Here's your campus activity at a glance.</p>
</div>

<div class="stat-cards">
  <div class="stat-card"><span class="stat-card-icon">✓</span><div><span class="stat-card-num"><?= $stampCount ?></span><span class="stat-card-label">Events Attended</span></div></div>
  <div class="stat-card"><span class="stat-card-icon">🎫</span><div><span class="stat-card-num"><?= $stampCount ?></span><span class="stat-card-label">Stamps Earned</span></div></div>
  <div class="stat-card"><span class="stat-card-icon">📅</span><div><span class="stat-card-num"><?= count($upcoming) ?></span><span class="stat-card-label">Upcoming Events</span></div></div>
  <div class="stat-card"><span class="stat-card-icon">🏆</span><div><span class="stat-card-num"><?= $rank ?: '—' ?></span><span class="stat-card-label">Leaderboard Rank</span></div></div>
</div>

<div class="dash-grid">
  <div class="dash-panel">
    <div class="panel-head"><h3>Upcoming Events</h3><a href="<?= e(base_url()) ?>student/events.php" class="link-arrow">All →</a></div>
    <?php if ($upcoming): ?>
      <ul class="mini-event-list">
        <?php foreach ($upcoming as $ev): $rem = seats_remaining((int)$ev['event_id']); ?>
          <li>
            <a href="<?= e(base_url()) ?>event-details.php?id=<?= (int)$ev['event_id'] ?>" class="mini-event">
              <span class="mini-cat cat-<?= e(strtolower($ev['category'])) ?>"><?= category_icon($ev['category']) ?></span>
              <div><strong><?= e($ev['event_name']) ?></strong><span><?= e(date('d M', strtotime($ev['event_date']))) ?> · <?= e($ev['venue']) ?></span></div>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="empty-state small">No upcoming events registered. <a href="<?= e(base_url()) ?>student/events.php">Discover events →</a></p>
    <?php endif; ?>
  </div>

  <div class="dash-panel">
    <div class="panel-head"><h3>Recent Activity</h3></div>
    <?php if ($recent): ?>
      <ul class="activity-list">
        <?php foreach ($recent as $r): ?>
          <li><span class="activity-tick">✓</span> <?= e($r['action']) ?> <strong><?= e($r['target']) ?></strong></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="empty-state small">No activity yet. Register for your first event!</p>
    <?php endif; ?>
  </div>

  <div class="dash-panel">
    <div class="panel-head"><h3>Passport Progress</h3><a href="<?= e(base_url()) ?>student/passport.php" class="link-arrow">View →</a></div>
    <div class="progress-block">
      <div class="progress-label"><span><?= $stampCount ?> / <?= $progressTarget ?></span> events completed</div>
      <div class="progress-bar"><div class="progress-fill" style="width: <?= min(100, ($stampCount/max(1,$progressTarget))*100) ?>%"></div></div>
    </div>
  </div>

  <div class="dash-panel">
    <div class="panel-head"><h3>Achievement Preview</h3><a href="<?= e(base_url()) ?>student/achievements.php" class="link-arrow">All →</a></div>
    <div class="badge-preview-grid">
  <?php foreach (array_slice($allAch, 0, 6) as $a): ?>
    
    <?php $isUnlocked = in_array($a['code'], $unlocked, true); ?>

    <div class="badge-sm <?= $isUnlocked ? '' : 'locked' ?>">
      <span class="badge-icon"><?= $isUnlocked ? '🏅' : '🔒' ?></span>
      <span class="badge-name"><?= e($a['name']) ?></span>
    </div>

    <?php endforeach; ?>
   </div>
  </div>

  <div class="dash-panel">
    <div class="panel-head"><h3>Leaderboard Preview</h3><a href="<?= e(base_url()) ?>student/leaderboard.php" class="link-arrow">Full →</a></div>
    <ol class="mini-leaderboard">
      <?php foreach ($leaderboard as $i => $l): ?>
        <li>
          <span class="lb-rank"><?= $i+1 ?></span>
          <span class="lb-name"><?= e($l['name']) ?></span>
          <span class="lb-dept"><?= e($l['department']) ?></span>
          <span class="lb-stamps"><?= (int)$l['stamps'] ?> 🎫</span>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</div>
<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>
