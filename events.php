<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Events';
$activeNav = 'events';
require __DIR__ . '/includes/header.php';

$search = trim($_GET['q'] ?? '');
$cat = trim($_GET['category'] ?? '');

$sql = 'SELECT * FROM events WHERE status="upcoming"';
$params = []; $types = '';
if ($search !== '') { $sql .= ' AND event_name LIKE ?'; $params[] = '%'.$search.'%'; $types .= 's'; }
if ($cat !== '' && in_array($cat, EVENT_CATEGORIES, true)) { $sql .= ' AND category = ?'; $params[] = $cat; $types .= 's'; }
$sql .= ' ORDER BY event_date ASC';

$stmt = db()->prepare($sql);
if ($params) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<section class="container section">
  <span class="hero-eyebrow">Campus Events</span>
  <h1>Discover Events</h1>
  <p class="lead">Search and filter upcoming events happening on campus.</p>

  <form class="filter-bar" method="get" action="events.php" id="eventFilter">
    <div class="search-input">
      <input type="text" name="q" id="searchQ" placeholder="Search by event name…" value="<?= e($search) ?>">
    </div>
    <div class="category-pills" id="categoryPills">
      <button type="button" class="pill <?= $cat===''?'active':'' ?>" data-cat="">All</button>
      <?php foreach (EVENT_CATEGORIES as $c): ?>
        <button type="button" class="pill <?= $cat===$c?'active':'' ?>" data-cat="<?= e($c) ?>"><?= e($c) ?></button>
      <?php endforeach; ?>
    </div>
    <input type="hidden" name="category" id="catHidden" value="<?= e($cat) ?>">
    <button type="submit" class="btn btn-primary">Search</button>
  </form>

  <div class="event-grid" id="eventGrid">
    <?php foreach ($events as $ev): $rem = seats_remaining((int)$ev['event_id']); ?>
      <a href="<?= e(base_url()) ?>event-details.php?id=<?= (int)$ev['event_id'] ?>" class="event-card">
        <div class="event-card-top cat-<?= e(strtolower($ev['category'])) ?>">
          <span class="event-cat"><?= e($ev['category']) ?></span>
          <span class="event-cat-icon"><?= category_icon($ev['category']) ?></span>
        </div>
        <div class="event-card-body">
          <h3><?= e($ev['event_name']) ?></h3>
          <p class="event-meta">📅 <?= e(date('d M Y', strtotime($ev['event_date']))) ?> · <?= e($ev['event_time']) ?></p>
          <p class="event-meta">📍 <?= e($ev['venue']) ?></p>
          <p class="event-org">by <?= e($ev['organizer']) ?></p>
          <div class="event-card-foot">
            <span class="seats <?= $rem<=0?'seats-full':'' ?>"><?= $rem>0 ? $rem.' seats left' : 'Full' ?></span>
            <span class="link-arrow">View →</span>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
    <?php if (!$events): ?>
      <p class="empty-state">No events match your search. Try a different keyword or category.</p>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
