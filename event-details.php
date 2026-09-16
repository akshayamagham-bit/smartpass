<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Event Details';
$activeNav = 'events';
require __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM events WHERE event_id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$ev = $stmt->get_result()->fetch_assoc();
if (!$ev) {
    echo '<section class="container section"><p class="empty-state">Event not found.</p><p><a href="'.e(base_url()).'events.php" class="btn btn-ghost">← Back to events</a></p></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}
$rem = seats_remaining($id);
$isStudent = is_student_logged_in();

// registration check
$alreadyRegistered = false;
if ($isStudent) {
    $stmt = db()->prepare('SELECT registration_id FROM registrations WHERE student_id=? AND event_id=?');
    $sid = $_SESSION['student_id'];
    $stmt->bind_param('ii', $sid, $id);
    $stmt->execute();
    $alreadyRegistered = (bool)$stmt->get_result()->fetch_assoc();
}

// handle register
$msg = ''; $msgType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isStudent) {
    $sid = $_SESSION['student_id'];
    if ($alreadyRegistered) {
        $msg = 'You are already registered for this event.'; $msgType = 'info';
    } elseif ($rem <= 0) {
        $msg = 'Sorry, this event is full.'; $msgType = 'error';
    } else {
        $stmt = db()->prepare('INSERT INTO registrations (student_id, event_id) VALUES (?, ?)');
        $stmt->bind_param('ii', $sid, $id);
        if ($stmt->execute()) {
            $msg = 'Successfully registered! See you at the event.'; $msgType = 'success';
            $alreadyRegistered = true;
            $rem = seats_remaining($id);
        } else {
            $msg = 'Registration failed. Please try again.'; $msgType = 'error';
        }
    }
}
?>
<section class="container section">
  <a href="<?= e(base_url()) ?>events.php" class="link-arrow">← All events</a>
  <div class="detail-layout">
    <div class="detail-hero cat-<?= e(strtolower($ev['category'])) ?>">
      <span class="event-cat"><?= e($ev['category']) ?></span>
      <span class="event-cat-icon big"><?= category_icon($ev['category']) ?></span>
    </div>
    <div class="detail-body">
      <h1><?= e($ev['event_name']) ?></h1>
      <p class="event-org">Organized by <?= e($ev['organizer']) ?></p>
      <p class="detail-desc"><?= nl2br(e($ev['description'])) ?></p>

      <div class="detail-meta-grid">
        <div class="meta-item"><span class="meta-label">Date</span><span class="meta-value"><?= e(date('d M Y', strtotime($ev['event_date']))) ?></span></div>
        <div class="meta-item"><span class="meta-label">Time</span><span class="meta-value"><?= e($ev['event_time']) ?></span></div>
        <div class="meta-item"><span class="meta-label">Venue</span><span class="meta-value"><?= e($ev['venue']) ?></span></div>
        <div class="meta-item"><span class="meta-label">Category</span><span class="meta-value"><?= e($ev['category']) ?></span></div>
        <div class="meta-item"><span class="meta-label">Capacity</span><span class="meta-value"><?= (int)$ev['capacity'] ?></span></div>
        <div class="meta-item"><span class="meta-label">Seats Left</span><span class="meta-value <?= $rem<=0?'seats-full':'' ?>"><?= $rem ?></span></div>
      </div>

      <?php if ($msg): ?>
        <div class="alert alert-<?= e($msgType) ?>"><?= e($msg) ?></div>
      <?php endif; ?>

      <div class="detail-actions">
        <?php if (!$isStudent): ?>
          <a href="<?= e(base_url()) ?>login.php" class="btn btn-primary">Login to Register</a>
        <?php elseif ($alreadyRegistered): ?>
          <span class="badge badge-success">✓ Registered</span>
          <a href="<?= e(base_url()) ?>student/my-events.php" class="btn btn-ghost">View My Events</a>
        <?php elseif ($rem <= 0): ?>
          <span class="badge badge-error">Event Full</span>
        <?php else: ?>
          <form method="post" onsubmit="return confirm('Register for this event?');">
            <button type="submit" class="btn btn-primary">Register Now</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
