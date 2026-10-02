<?php
require_once __DIR__ . '/../includes/functions.php';
require_student();

$search = trim($_GET['q'] ?? '');
$cat = trim($_GET['category'] ?? '');
$sql = 'SELECT * FROM events WHERE status="upcoming"';
$params = []; $types = '';
if ($search !== '') { $sql .= ' AND event_name LIKE ?'; $params[] = '%'.$search.'%'; $types .= 's'; }
if ($cat !== '' && in_array($cat, EVENT_CATEGORIES, true)) { $sql .= ' AND category = ?'; $params[] = $cat; $types .= 's'; }
$sql .= ' ORDER BY event_date ASC';
$stmt = db()->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$sid = $_SESSION['student_id'];

$dashboardTitle = 'Discover Events';
$activeItem = 'events';
$role = 'student';
require __DIR__ . '/../includes/dashboard_header.php';
?>
<form class="filter-bar" method="get" id="eventFilter">
  <div class="search-input"><input type="text" name="q" placeholder="Search events…" value="<?= e($search) ?>"></div>
  <div class="category-pills" id="categoryPills">
    <button type="button" class="pill <?= $cat===''?'active':'' ?>" data-cat="">All</button>
    <?php foreach (EVENT_CATEGORIES as $c): ?>
      <button type="button" class="pill <?= $cat===$c?'active':'' ?>" data-cat="<?= e($c) ?>"><?= e($c) ?></button>
    <?php endforeach; ?>
  </div>
  <input type="hidden" name="category" id="catHidden" value="<?= e($cat) ?>">
  <button type="submit" class="btn btn-primary">Search</button>
</form>

<div class="event-grid">
  <?php foreach ($events as $ev):
    $rem = seats_remaining((int)$ev['event_id']);
    $stmt = db()->prepare('SELECT registration_id FROM registrations WHERE student_id=? AND event_id=?');
    $stmt->bind_param('ii', $sid, $ev['event_id']); $stmt->execute();
    $reg = (bool)$stmt->get_result()->fetch_assoc();
  ?>
    <a href="<?= e(base_url()) ?>event-details.php?id=<?= (int)$ev['event_id'] ?>" class="event-card">
      <div class="event-card-top cat-<?= e(strtolower($ev['category'])) ?>">
        <span class="event-cat"><?= e($ev['category']) ?></span>
        <span class="event-cat-icon"><?= category_icon($ev['category']) ?></span>
      </div>
      <div class="event-card-body">
        <h3><?= e($ev['event_name']) ?></h3>
        <p class="event-meta">📅 <?= e(date('d M Y', strtotime($ev['event_date']))) ?></p>
        <p class="event-meta">📍 <?= e($ev['venue']) ?></p>
        <div class="event-card-foot">
          <?php if ($reg): ?><span class="badge badge-success">✓ Registered</span><?php else: ?>
          <span class="seats <?= $rem<=0?'seats-full':'' ?>"><?= $rem>0 ? $rem.' seats left' : 'Full' ?></span><?php endif; ?>
          <span class="link-arrow">View →</span>
        </div>
      </div>
    </a>
  <?php endforeach; ?>
  <?php if (!$events): ?><p class="empty-state">No events found.</p><?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>
