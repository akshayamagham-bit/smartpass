<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$eventId = (int)($_GET['event_id'] ?? 0);
$events = db()->query('SELECT * FROM events ORDER BY event_date DESC')->fetch_all(MYSQLI_ASSOC);

$where = ''; $params = []; $types = '';
if ($eventId > 0) { $where = 'WHERE r.event_id = ?'; $params = [$eventId]; $types = 'i'; }

$sql = "SELECT r.registration_id, s.name AS student_name, s.register_number, s.department,
               e.event_name, e.event_date, r.registration_date, r.status AS reg_status,
               (SELECT status FROM attendance a WHERE a.student_id=r.student_id AND a.event_id=r.event_id) AS att_status
        FROM registrations r
        JOIN students s ON r.student_id=s.student_id
        JOIN events e ON r.event_id=e.event_id
        $where ORDER BY e.event_date DESC, r.registration_date DESC";
$stmt = db()->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$regs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$dashboardTitle = 'Registrations';
$activeItem = 'registrations';
$role = 'admin';
require __DIR__ . '/../includes/dashboard_header.php';
?>
<form class="filter-bar" method="get">
  <label class="inline-label">Filter by event:
    <select name="event_id" onchange="this.form.submit()">
      <option value="0">All events</option>
      <?php foreach ($events as $ev): ?>
        <option value="<?= (int)$ev['event_id'] ?>" <?= $eventId===(int)$ev['event_id']?'selected':'' ?>><?= e($ev['event_name']) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
</form>

<div class="admin-table">
  <table>
    <thead><tr><th>Student</th><th>Register No.</th><th>Department</th><th>Event</th><th>Event Date</th><th>Reg. Status</th><th>Attendance</th></tr></thead>
    <tbody>
      <?php foreach ($regs as $r): ?>
        <tr>
          <td><?= e($r['student_name']) ?></td>
          <td><?= e($r['register_number']) ?></td>
          <td><?= e($r['department']) ?></td>
          <td><?= e($r['event_name']) ?></td>
          <td><?= e(date('d M Y', strtotime($r['event_date']))) ?></td>
          <td><span class="badge badge-info"><?= e($r['reg_status']) ?></span></td>
          <td>
            <?php if ($r['att_status']==='present'): ?><span class="badge badge-success">Present</span>
            <?php elseif ($r['att_status']==='absent'): ?><span class="badge badge-error">Absent</span>
            <?php else: ?><span class="badge">Pending</span><?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$regs): ?><tr><td colspan="7" class="empty-state">No registrations found.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>
