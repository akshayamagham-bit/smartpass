</main>
<footer class="site-footer">
  <div class="container footer-grid">
    <div>
      <div class="brand"><span class="brand-mark">SP</span><span class="brand-name">SMART<span class="brand-accent">PASS</span></span></div>
      <p class="footer-tag">Discover. Participate. Collect. Achieve.</p>
    </div>
    <div>
      <h4>Explore</h4>
      <a href="<?= e(base_url()) ?>events.php">Events</a>
      <a href="<?= e(base_url()) ?>about.php">About</a>
      <a href="<?= e(base_url()) ?>register.php">Register</a>
    </div>
    <div>
      <h4>Account</h4>
      <a href="<?= e(base_url()) ?>login.php">Login</a>
      <a href="<?= e(base_url()) ?>student/dashboard.php">Student Dashboard</a>
      <a href="<?= e(base_url()) ?>admin/dashboard.php">Admin Dashboard</a>
    </div>
    <div>
      <h4>Project</h4>
      <p class="footer-meta">Web Technologies Academic Project<br>Smart College Event Passport</p>
    </div>
  </div>
  <div class="footer-bottom container">
    <p>© <?= date('Y') ?> SmartPass · Built for Web Technologies Lab Project</p>
  </div>
</footer>
<div class="toast-wrap" id="toastWrap"></div>
<script src="<?= e(base_url()) ?>js/script.js"></script>
</body>
</html>
