<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Home';
$activeNav = 'home';
require __DIR__ . '/includes/header.php';

$featured = featured_events(4);
$tEvents  = total_events();
$tStudents = total_students();
$tAttended = total_attendance();
$tStamps = total_stamps();
?>
<section class="hero">
  <div class="container hero-inner">
    <div class="hero-text">
      <span class="hero-eyebrow">Smart College Event Passport</span>
      <h1>YOUR CAMPUS LIFE.<br>YOUR DIGITAL <span class="accent">PASSPORT.</span></h1>
      <p class="hero-sub">Discover events, participate in campus activities, collect digital stamps and build your college journey.</p>
      <div class="hero-actions">
        <a href="<?= e(base_url()) ?>events.php" class="btn btn-primary">Explore Events</a>
        <a href="<?= e(base_url()) ?>register.php" class="btn btn-ghost">Get Started</a>
      </div>
    </div>
    <div class="hero-card">
      <div class="passport-preview">
        <div class="pp-header">
          <span class="pp-title">EVENT PASSPORT</span>
          <span class="pp-mark">SP</span>
        </div>
        <div class="pp-stamps">
          <div class="pp-stamp earned">⚙<span>Web Workshop</span></div>
          <div class="pp-stamp earned">🏆<span>Hackathon</span></div>
          <div class="pp-stamp earned">♪<span>Cultural Fest</span></div>
          <div class="pp-stamp locked">⚑<span>Sports Meet</span></div>
          <div class="pp-stamp locked">🔬<span>Science Expo</span></div>
          <div class="pp-stamp locked">◆<span>AI Seminar</span></div>
        </div>
        <div class="pp-footer">12 / 20 events completed</div>
      </div>
    </div>
  </div>
</section>

<section class="stats-band">
  <div class="container stats-grid">
    <div class="stat"><span class="stat-num"><?= $tEvents ?></span><span class="stat-label">Total Events</span></div>
    <div class="stat"><span class="stat-num"><?= $tStudents ?></span><span class="stat-label">Registered Students</span></div>
    <div class="stat"><span class="stat-num"><?= $tAttended ?></span><span class="stat-label">Events Completed</span></div>
    <div class="stat"><span class="stat-num"><?= $tStamps ?></span><span class="stat-label">Stamps Earned</span></div>
  </div>
</section>

<section class="container section">
  <div class="section-head">
    <h2>Featured Events</h2>
    <a href="<?= e(base_url()) ?>events.php" class="link-arrow">View all →</a>
  </div>
  <div class="event-grid">
    <?php foreach ($featured as $ev): $rem = seats_remaining((int)$ev['event_id']); ?>
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
            <span class="seats <?= $rem<=0?'seats-full':'' ?>"><?= $rem>0 ? $rem.' seats left' : 'Full' ?></span>
            <span class="link-arrow">View →</span>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
    <?php if (!$featured): ?>
      <p class="empty-state">No upcoming events yet. Check back soon.</p>
    <?php endif; ?>
  </div>
</section>

<section class="how-section">
  <div class="container section">
    <h2 class="center">How It Works</h2>
    <div class="steps-grid">
      <div class="step"><span class="step-num">1</span><h3>Discover</h3><p>Browse upcoming campus events by category, date or venue.</p></div>
      <div class="step"><span class="step-num">2</span><h3>Register</h3><p>Sign up for events that interest you with a single click.</p></div>
      <div class="step"><span class="step-num">3</span><h3>Attend</h3><p>Show up and get your attendance verified by the organizer.</p></div>
      <div class="step"><span class="step-num">4</span><h3>Collect</h3><p>Earn a digital stamp for every verified attendance.</p></div>
      <div class="step"><span class="step-num">5</span><h3>Achieve</h3><p>Unlock achievements and climb the leaderboard.</p></div>
    </div>
  </div>
</section>

<section class="container section why-grid">
  <div class="why-text">
    <h2>Why SmartPass?</h2>
    <p>SmartPass turns scattered event participation into a personal journey. No more paper registers or lost certificates — your campus life is recorded as a digital passport you can be proud of.</p>
    <ul class="why-list">
      <li>✓ One unified campus event calendar</li>
      <li>✓ Verified attendance with automatic stamps</li>
      <li>✓ Personal digital passport that grows with you</li>
      <li>✓ Achievements and leaderboard for motivation</li>
    </ul>
    <a href="<?= e(base_url()) ?>register.php" class="btn btn-primary">Create your passport</a>
  </div>
  <div class="why-cta-card">
    <h3>Ready to begin?</h3>
    <p>Join your campus community and start collecting stamps today.</p>
    <a href="<?= e(base_url()) ?>register.php" class="btn btn-dark">Get Started →</a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
