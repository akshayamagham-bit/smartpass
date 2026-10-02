<?php
require_once __DIR__ . '/../includes/functions.php';
require_student();
$sid = (int)$_SESSION['student_id'];
$allAch = all_achievements();
$unlocked = student_unlocked_codes($sid);
$stampCount = student_stamp_count($sid);

$dashboardTitle = 'Achievements';
$activeItem = 'achievements';
$role = 'student';
require __DIR__ . '/../includes/dashboard_header.php';
?>
<div class="ach-summary">
  <div class="ach-summary-stat"><span class="ach-big"><?= count($unlocked) ?></span><span>Unlocked</span></div>
  <div class="ach-summary-stat"><span class="ach-big"><?= count($allAch) - count($unlocked) ?></span><span>Locked</span></div>
  <div class="ach-summary-stat"><span class="ach-big"><?= $stampCount ?></span><span>Stamps</span></div>
</div>

<div class="achievement-grid">
  <?php foreach ($allAch as $a):
    $isUnlocked = in_array($a['code'], $unlocked, true);
    [$cur, $tgt] = achievement_progress($a['code'], $sid);
  ?>
    <div class="achievement-card <?= $isUnlocked?'':'locked' ?>">
      <div class="achievement-icon"><?= $isUnlocked ? '🏅' : '🔒' ?></div>
      <div class="achievement-body">
        <h3><?= e($a['name']) ?> <?= $isUnlocked?'<span class="ach-check">✓</span>':'' ?></h3>
        <p><?= e($a['description']) ?></p>
        <?php if (!$isUnlocked): ?>
          <div class="ach-progress">
            <div class="progress-bar small"><div class="progress-fill" style="width: <?= ($cur/max(1,$tgt))*100 ?>%"></div></div>
            <span class="ach-count"><?= $cur ?> / <?= $tgt ?></span>
          </div>
        <?php else: ?>
          <span class="badge badge-success">Unlocked</span>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>
