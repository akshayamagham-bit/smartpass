<?php
require_once __DIR__ . '/../includes/functions.php';
require_student();
$sid = (int)$_SESSION['student_id'];

$filter = $_GET['filter'] ?? 'overall';
$dept = trim($_GET['department'] ?? '');

$sql = "SELECT s.student_id, s.name, s.department, COUNT(st.stamp_id) AS stamps
        FROM students s LEFT JOIN stamps st ON s.student_id=st.student_id";
$where = []; $params = []; $types = '';
if ($filter === 'month') {
  $sql .= " LEFT JOIN stamps st2 ON st2.stamp_id=st.stamp_id AND st2.earned_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
  // simpler: re-do query below
}
// We'll build simpler queries per filter.
if ($filter === 'month') {
  $sql = "SELECT s.student_id, s.name, s.department, COUNT(st.stamp_id) AS stamps
          FROM students s LEFT JOIN stamps st ON s.student_id=st.student_id
          AND st.earned_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
} elseif ($filter === 'department' && $dept !== '') {
  $sql .= " WHERE s.department = ?";
  $params[] = $dept; $types .= 's';
}
$sql .= " GROUP BY s.student_id ORDER BY stamps DESC, s.name ASC";

$stmt = db()->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// filter out 0-stamp students in month view for cleaner display
if ($filter === 'month') $rows = array_filter($rows, fn($r) => (int)$r['stamps'] > 0);

$student = current_student();
$departments = db()->query("SELECT DISTINCT department FROM students ORDER BY department ASC")->fetch_all(MYSQLI_ASSOC);

$dashboardTitle = 'Leaderboard';
$activeItem = 'leaderboard';
$role = 'student';
require __DIR__ . '/../includes/dashboard_header.php';
?>
<form class="filter-bar" method="get" id="leaderboardFilter">
  <div class="category-pills">
    <button type="button" class="pill <?= $filter==='overall'?'active':'' ?>" data-filter="overall">Overall</button>
    <button type="button" class="pill <?= $filter==='month'?'active':'' ?>" data-filter="month">This Month</button>
    <button type="button" class="pill <?= $filter==='department'?'active':'' ?>" data-filter="department">Department</button>
  </div>
  <input type="hidden" name="filter" id="filterHidden" value="<?= e($filter) ?>">
  <?php if ($filter === 'department'): ?>
    <select name="department" onchange="this.form.submit()">
      <option value="">All departments</option>
      <?php foreach ($departments as $d): ?>
        <option value="<?= e($d['department']) ?>" <?= $dept===$d['department']?'selected':'' ?>><?= e($d['department']) ?></option>
      <?php endforeach; ?>
    </select>
  <?php endif; ?>
  <button type="submit" class="btn btn-primary">Apply</button>
</form>

<div class="leaderboard-table">
  <table>
    <thead><tr><th>Rank</th><th>Student</th><th>Department</th><th>Stamps</th></tr></thead>
    <tbody>
      <?php if (!$rows): ?>
        <tr><td colspan="4" class="empty-state">No data for this filter yet.</td></tr>
      <?php else: foreach ($rows as $i => $r): $me = (int)$r['student_id'] === $sid; ?>
        <tr class="<?= $me?'is-me':'' ?> <?= $i<3?'rank-'.$i:'' ?>">
          <td class="lb-rank-cell"><?php
            if ($i === 0) echo '🥇';
            elseif ($i === 1) echo '🥈';
            elseif ($i === 2) echo '🥉';
            else echo ($i+1);
          ?></td>
          <td><?= e($r['name']) ?> <?= $me?'<span class="you-tag">(You)</span>':'' ?></td>
          <td><?= e($r['department']) ?></td>
          <td class="lb-stamp-count"><?= (int)$r['stamps'] ?> 🎫</td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>
