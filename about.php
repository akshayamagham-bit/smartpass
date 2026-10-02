<?php
$pageTitle = 'About';
$activeNav = 'about';
require __DIR__ . '/includes/header.php';
?>
<section class="container section">
  <span class="hero-eyebrow">About the project</span>
  <h1>Smart College Event Passport</h1>
  <p class="lead">A centralized college event participation platform that turns attendance into a personal digital journey.</p>
</section>

<section class="container section">
  <div class="about-grid">
    <div class="about-card">
      <span class="about-icon">⚠</span>
      <h2>The Problem</h2>
      <p>College events are scattered across departments, paper registers and WhatsApp groups. Students have no record of what they attended, and organizers struggle to track participation or reward engagement.</p>
    </div>
    <div class="about-card">
      <span class="about-icon">✓</span>
      <h2>The Solution</h2>
      <p>A single platform where students discover events, register online, get attendance verified, and automatically earn digital stamps that build a personal passport — with achievements and a leaderboard to keep them motivated.</p>
    </div>
  </div>
</section>

<section class="container section">
  <h2>Key Features</h2>
  <div class="features-grid">
    <div class="feature-item"><span class="feature-icon">📅</span><h3>Event Discovery</h3><p>Search and filter upcoming campus events by category and name.</p></div>
    <div class="feature-item"><span class="feature-icon">✓</span><h3>Online Registration</h3><p>One-click registration with capacity checks and duplicate prevention.</p></div>
    <div class="feature-item"><span class="feature-icon">🎫</span><h3>Digital Stamps</h3><p>Verified attendance automatically awards a unique stamp per event.</p></div>
    <div class="feature-item"><span class="feature-icon">📖</span><h3>Event Passport</h3><p>A personal visual passport that fills with stamps as you participate.</p></div>
    <div class="feature-item"><span class="feature-icon">🏅</span><h3>Achievements</h3><p>Unlock badges like First Step, Event Explorer and Campus Champion.</p></div>
    <div class="feature-item"><span class="feature-icon">🏆</span><h3>Leaderboard</h3><p>Compete with peers and filter by overall, month or department.</p></div>
  </div>
</section>

<section class="container section tech-section">
  <h2>Technologies Used</h2>
  <div class="tech-pills">
    <span>HTML5</span><span>CSS3</span><span>JavaScript</span><span>PHP</span>
    <span>MySQL</span><span>XML</span><span>DTD</span><span>XSLT</span>
    <span>Sessions</span><span>Prepared Statements</span>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
