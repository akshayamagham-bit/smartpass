<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$totalEvents = total_events();
$totalStudents = total_students();
$totalRegs = total_registrations();
$totalAtt = total_attendance();
$totalStamps = total_stamps();

// most popular event (most registrations)
$popular = db()->query(
  "SELECT e.event_name, COUNT(r.registration_id) AS regs
   FROM events e LEFT JOIN registrations r ON e.event_id=r.event_id
   GROUP BY e.event_id ORDER BY regs DESC LIMIT 1"
)->fetch_assoc();

// most active student (most stamps)
$active = db()->query(
  "SELECT s.name, s.department, COUNT(st.stamp_id) AS stamps
   FROM students s LEFT JOIN stamps st ON s.student_id=st.student_id
   GROUP BY s.student_id ORDER BY stamps DESC LIMIT 1"
)->fetch_assoc();

// category-wise participation
$catStats = db()->query(
  "SELECT e.category, COUNT(r.registration_id) AS regs
   FROM events e LEFT JOIN registrations r ON e.event_id=r.event_id
   GROUP BY e.category ORDER BY regs DESC"
)->fetch_all(MYSQLI_ASSOC);

$maxCatRegs = max(1, max(array_column($catStats, 'regs') ?: [1]));

// attendance stats
$attPresent = db()->query("SELECT COUNT(*) c FROM attendance WHERE status='present'")->fetch_assoc()['c'];
$attAbsent = db()->query("SELECT COUNT(*) c FROM attendance WHERE status='absent'")->fetch_assoc()['c'];

$dashboardTitle = 'Reports';
$activeItem = 'reports';
$role = 'admin';
require __DIR__ . '/../includes/dashboard_header.php';
?>
<div class="report-summary">
  <div class="report-card"><span class="rc-num"><?= $totalEvents ?></span><span class="rc-label">Total Events</span></div>
  <div class="report-card"><span class="rc-num"><?= $totalStudents ?></span><span class="rc-label">Participants</span></div>
  <div class="report-card"><span class="rc-num"><?= $totalRegs ?></span><span class="rc-label">Registrations</span></div>
  <div class="report-card"><span class="rc-num"><?= $totalStamps ?></span><span class="rc-label">Stamps Issued</span></div>
</div>

<div class="dash-grid">
  <div class="dash-panel">
    <div class="panel-head"><h3>Most Popular Event</h3></div>
    <?php if ($popular && $popular['regs']>0): ?>
      <p class="report-highlight"><?= e($popular['event_name']) ?></p>
      <p><?= (int)$popular['regs'] ?> registrations</p>
    <?php else: ?><p class="empty-state small">No registrations yet.</p><?php endif; ?>
  </div>
  <div class="dash-panel">
    <div class="panel-head"><h3>Most Active Student</h3></div>
    <?php if ($active && $active['stamps']>0): ?>
      <p class="report-highlight"><?= e($active['name']) ?></p>
      <p><?= e($active['department']) ?> · <?= (int)$active['stamps'] ?> stamps</p>
    <?php else: ?><p class="empty-state small">No stamps earned yet.</p><?php endif; ?>
  </div>
</div>

<div class="dash-panel">
  <div class="panel-head"><h3>Category-wise Participation</h3></div>
  <?php if ($catStats): ?>
    <div class="cat-bars">
      <?php foreach ($catStats as $c): ?>
        <div class="cat-bar-row">
          <span class="cat-bar-label"><?= e($c['category']) ?></span>
          <div class="cat-bar-track"><div class="cat-bar-fill cat-<?= e(strtolower($c['category'])) ?>" style="width: <?= ($c['regs']/$maxCatRegs)*100 ?>%"></div></div>
          <span class="cat-bar-num"><?= (int)$c['regs'] ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?><p class="empty-state small">No data.</p><?php endif; ?>
</div>

<div class="dash-panel">
  <div class="panel-head"><h3>Attendance Statistics</h3></div>
  <div class="att-stats">
    <div class="att-stat present"><span><?= (int)$attPresent ?></span><label>Present</label></div>
    <div class="att-stat absent"><span><?= (int)$attAbsent ?></span><label>Absent</label></div>
    <div class="att-stat pending"><span><?= max(0, $totalRegs - $attPresent - $attAbsent) ?></span><label>Pending</label></div>
  </div>
  <div class="progress-bar big">
    <div class="progress-fill present" style="width: <?= $totalRegs>0?($attPresent/$totalRegs)*100:0 ?>%"></div>
  </div>
  <p class="progress-note"><?= $totalRegs>0?round(($attPresent/$totalRegs)*100):0 ?>% attendance rate</p>
</div>
<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>
