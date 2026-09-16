<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$eventId = (int)($_GET['event_id'] ?? 0);
$msg = $_GET['msg'] ?? '';
// handle marking attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $regId = (int)($_POST['registration_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';
    $evId = (int)($_POST['event_id'] ?? 0);
    $stuId = (int)($_POST['student_id'] ?? 0);

    if (in_array($newStatus, ['present','absent'], true) && $regId && $evId && $stuId) {
        // upsert attendance
        $stmt = db()->prepare('SELECT attendance_id FROM attendance WHERE student_id=? AND event_id=?');
        $stmt->bind_param('ii', $stuId, $evId);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();

        if ($existing) {
            $stmt = db()->prepare('UPDATE attendance SET status=? WHERE attendance_id=?');
            $stmt->bind_param('si', $newStatus, $existing['attendance_id']);
            $stmt->execute();
            // if switching to absent, remove stamp
            if ($newStatus === 'absent') {
                $stmt = db()->prepare('DELETE FROM stamps WHERE student_id=? AND event_id=?');
                $stmt->bind_param('ii', $stuId, $evId);
                $stmt->execute();
            } elseif ($newStatus === 'present') {
                award_stamp($stuId, $evId);
            }
        } else {
            $stmt = db()->prepare('INSERT INTO attendance (student_id, event_id, status) VALUES (?,?,?)');
            $stmt->bind_param('iis', $stuId, $evId, $newStatus);
            $stmt->execute();
            if ($newStatus === 'present') {
                award_stamp($stuId, $evId);
            }
        }
        $msg = $newStatus === 'present'
    ? 'Attendance marked Present — stamp awarded, achievements & leaderboard updated.'
    : 'Attendance marked Absent — stamp removed if present.';

header(
    'Location: attendance.php?event_id=' . $evId .
    '&msg=' . urlencode($msg)
);

exit;
    }
}

$events = db()->query('SELECT * FROM events ORDER BY event_date DESC')->fetch_all(MYSQLI_ASSOC);

$regs = [];
if ($eventId > 0) {
    $stmt = db()->prepare(
        "SELECT r.registration_id, r.student_id, s.name AS student_name, s.register_number, s.department,
                (SELECT status FROM attendance a WHERE a.student_id=r.student_id AND a.event_id=r.event_id) AS att_status
         FROM registrations r JOIN students s ON r.student_id=s.student_id
         WHERE r.event_id = ? ORDER BY s.name ASC"
    );
    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $regs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$dashboardTitle = 'Verify Attendance';
$activeItem = 'attendance';
$role = 'admin';
require __DIR__ . '/../includes/dashboard_header.php';
?>
<?php if ($msg): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>

<form class="filter-bar" method="get">
  <label class="inline-label">Select event to verify attendance:
    <select name="event_id" onchange="this.form.submit()">
      <option value="0">— Choose an event —</option>
      <?php foreach ($events as $ev): ?>
        <option value="<?= (int)$ev['event_id'] ?>" <?= $eventId===(int)$ev['event_id']?'selected':'' ?>>
          <?= e($ev['event_name']) ?> (<?= e(date('d M Y', strtotime($ev['event_date']))) ?>)
        </option>
      <?php endforeach; ?>
    </select>
  </label>
</form>

<?php if ($eventId > 0): ?>
  <?php if ($regs): ?>
    <div class="admin-table">
      <table>
        <thead><tr><th>Student</th><th>Register No.</th><th>Department</th><th>Current</th><th>Mark Attendance</th></tr></thead>
        <tbody>
          <?php foreach ($regs as $r): ?>
            <tr>
              <td><strong><?= e($r['student_name']) ?></strong></td>
              <td><?= e($r['register_number']) ?></td>
              <td><?= e($r['department']) ?></td>
              <td>
                <?php if ($r['att_status']==='present'): ?><span class="badge badge-success">Present</span>
                <?php elseif ($r['att_status']==='absent'): ?><span class="badge badge-error">Absent</span>
                <?php else: ?><span class="badge">Pending</span><?php endif; ?>
              </td>
              <td>
                <form method="post" style="display:flex; gap:8px;">
                  <input type="hidden" name="registration_id" value="<?= (int)$r['registration_id'] ?>">
                  <input type="hidden" name="student_id" value="<?= (int)$r['student_id'] ?>">
                  <input type="hidden" name="event_id" value="<?= $eventId ?>">
                  <button type="submit" name="status" value="present" class="btn btn-sm btn-success"
                          <?= $r['att_status']==='present'?'disabled':'' ?>>Present ✓</button>
                  <button type="submit" name="status" value="absent" class="btn btn-sm btn-danger"
                          <?= $r['att_status']==='absent'?'disabled':'' ?>>Absent ✗</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="empty-state">No students registered for this event yet.</p>
  <?php endif; ?>
<?php else: ?>
  <p class="empty-state">Select an event above to verify attendance.</p>
<?php endif; ?>
<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>
