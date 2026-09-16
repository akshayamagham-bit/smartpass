<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$search = trim($_GET['q'] ?? '');
$sql = 'SELECT * FROM students';
$params = []; $types = '';
if ($search !== '') {
    $sql .= ' WHERE name LIKE ? OR register_number LIKE ? OR email LIKE ? OR department LIKE ?';
    $like = '%'.$search.'%';
    $params = [$like,$like,$like,$like]; $types = 'ssss';
}
$sql .= ' ORDER BY name ASC';
$stmt = db()->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$dashboardTitle = 'Students';
$activeItem = 'students';
$role = 'admin';
require __DIR__ . '/../includes/dashboard_header.php';
?>
<form class="filter-bar" method="get">
  <div class="search-input"><input type="text" name="q" placeholder="Search by name, register no, email, department…" value="<?= e($search) ?>"></div>
  <button type="submit" class="btn btn-primary">Search</button>
</form>

<div class="admin-table">
  <table>
    <thead><tr><th>Name</th><th>Register No.</th><th>Department</th><th>Year</th><th>Email</th><th>Stamps</th></tr></thead>
    <tbody>
      <?php foreach ($students as $s):
        $sc = student_stamp_count((int)$s['student_id']);
      ?>
        <tr>
          <td><strong><?= e($s['name']) ?></strong></td>
          <td><?= e($s['register_number']) ?></td>
          <td><?= e($s['department']) ?></td>
          <td><?= e($s['year']) ?></td>
          <td><?= e($s['email']) ?></td>
          <td><?= $sc ?> 🎫</td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$students): ?><tr><td colspan="6" class="empty-state">No students found.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>
