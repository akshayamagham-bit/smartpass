<?php
require_once __DIR__ . '/../includes/functions.php';
require_student();
$student = current_student();
$sid = (int)$student['student_id'];

// Fetch stamps earned by this student (event_id => earned_date)
$stampRows = db()->query(
  "SELECT event_id, earned_date FROM stamps WHERE student_id=$sid"
)->fetch_all(MYSQLI_ASSOC);
$earnedMap = [];
foreach ($stampRows as $r) { $earnedMap[(int)$r['event_id']] = $r['earned_date']; }

// Fetch registrations for this student (event_id => status)
$regRows = db()->query(
  "SELECT event_id, status FROM registrations WHERE student_id=$sid"
)->fetch_all(MYSQLI_ASSOC);
$regMap = [];
foreach ($regRows as $r) { $regMap[(int)$r['event_id']] = $r['status']; }

// The 6 passport events — mapped to reference design with icons
// We match by keywords in the event name so it works with the sample data
$passportEventDefs = [
  ['icon' => '⚙',  'keyword' => 'Web Workshop'],
  ['icon' => '🏆', 'keyword' => 'Hackathon'],
  ['icon' => '🎵', 'keyword' => 'Cultural Fest'],
  ['icon' => '🚩', 'keyword' => 'Sports Meet'],
  ['icon' => '🔬', 'keyword' => 'Science Exhibition'],
  ['icon' => '◆',  'keyword' => 'AI'],
];

$allEvents = db()->query("SELECT event_id, event_name, category FROM events ORDER BY event_date ASC")->fetch_all(MYSQLI_ASSOC);

// For each passport slot, find the matching event from the DB
$passportEvents = [];
foreach ($passportEventDefs as $def) {
  $matched = null;
  foreach ($allEvents as $ev) {
    if (stripos($ev['event_name'], $def['keyword']) !== false) {
      $matched = $ev;
      break;
    }
  }
  $eventId = $matched ? (int)$matched['event_id'] : 0;
  $isCompleted = isset($earnedMap[$eventId]);
  $isRegistered = isset($regMap[$eventId]);
  $shortName = $matched ? preg_replace('/\s*\(.+\)\s*/', '', $matched['event_name']) : $def['keyword'];

  // Shorten common names to match the reference card labels
  $shortName = str_ireplace('Development ', '', $shortName);
  $shortName = str_ireplace('Inter-College ', '', $shortName);
  $shortName = str_ireplace('Annual ', '', $shortName);
  $shortName = str_ireplace(' & Machine Learning Seminar', ' Seminar', $shortName);
  $shortName = str_ireplace(' 2026', '', $shortName);

  $passportEvents[] = [
    'event_id'   => $eventId,
    'icon'       => $def['icon'],
    'name'       => $shortName,
    'completed'  => $isCompleted,
    'registered' => $isRegistered,
    'earned_date'=> $isCompleted ? $earnedMap[$eventId] : null,
  ];
}

$completedCount = 0;
foreach ($passportEvents as $pe) { if ($pe['completed']) $completedCount++; }
$totalPassportEvents = count($passportEvents);
$progressPct = $totalPassportEvents > 0 ? ($completedCount / $totalPassportEvents * 100) : 0;

$dashboardTitle = 'My Passport';
$activeItem = 'passport';
$role = 'student';
require __DIR__ . '/../includes/dashboard_header.php';
?>
<div class="passport">
  <div class="passport-card">
    <div class="passport-header">
      <div class="passport-header-text">
        <h2>Event Passport</h2>
        <p>Discover · Participate · Collect · Achieve</p>
      </div>
      <div class="passport-logo">SP</div>
    </div>

    <div class="passport-student">
      <div class="passport-student-item"><span class="label">Name</span><span class="value"><?= e($student['name']) ?></span></div>
      <div class="passport-student-item"><span class="label">Register No.</span><span class="value"><?= e($student['register_number']) ?></span></div>
      <div class="passport-student-item"><span class="label">Department</span><span class="value"><?= e($student['department']) ?></span></div>
      <div class="passport-student-item"><span class="label">Year</span><span class="value"><?= e($student['year']) ?></span></div>
    </div>

    <div class="passport-events" id="passportEvents">
      <?php foreach ($passportEvents as $pe):
        $cls = $pe['completed'] ? 'completed' : 'locked';
        $href = $pe['event_id'] > 0
          ? e(base_url()) . 'event-details.php?id=' . $pe['event_id']
          : 'javascript:void(0)';
        $statusText = $pe['completed']
          ? '✓ Stamped'
          : ($pe['registered'] ? 'Registered' : 'Not registered');
      ?>
        <a href="<?= $href ?>" class="passport-event <?= $cls ?>" <?= $pe['event_id'] > 0 ? '' : 'style="cursor:default"' ?>>
          <span class="passport-event-icon"><?= $pe['icon'] ?></span>
          <span class="passport-event-name"><?= e($pe['name']) ?></span>
          <span class="passport-event-status<?= $pe['completed'] ? ' stamp-mark' : '' ?>"><?= $statusText ?></span>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="passport-footer">
      <p class="passport-completion"><span class="count"><?= $completedCount ?></span> / <?= $totalPassportEvents ?> events completed</p>
      <div class="passport-progress-bar"><div class="passport-progress-fill" style="width: <?= $progressPct ?>%"></div></div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>
