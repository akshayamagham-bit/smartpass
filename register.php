<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Register';
$activeNav = 'login';
require __DIR__ . '/includes/header.php';

if (is_student_logged_in()) { header('Location: '.base_url().'student/dashboard.php'); exit; }

$errors = []; $old = ['name'=>'','register_number'=>'','email'=>'','department'=>'CSE','year'=>'1st Year','phone'=>''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['name','register_number','email','department','year','password','confirm_password'] as $f) {
        $old[$f] = trim($_POST[$f] ?? '');
    }
    $old['phone'] = trim($_POST['phone'] ?? '');

    if ($old['name'] === '') $errors[] = 'Name is required.';
    if ($old['register_number'] === '') $errors[] = 'Register number is required.';
    elseif (!preg_match('/^[A-Za-z0-9]{4,20}$/', $old['register_number'])) $errors[] = 'Register number must be 4-20 alphanumeric characters.';
    if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (strlen($old['password']) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($old['password'] !== $old['confirm_password']) $errors[] = 'Passwords do not match.';

    // uniqueness checks
    if (!$errors) {
        $stmt = db()->prepare('SELECT student_id FROM students WHERE email = ? OR register_number = ?');
        $stmt->bind_param('ss', $old['email'], $old['register_number']);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) $errors[] = 'An account with this email or register number already exists.';
    }

    if (!$errors) {
        $hash = password_hash($old['password'], PASSWORD_DEFAULT);
        $stmt = db()->prepare('INSERT INTO students (name, register_number, email, department, year, phone, password_hash) VALUES (?,?,?,?,?,?,?)');
        $stmt->bind_param('sssssss', $old['name'], $old['register_number'], $old['email'], $old['department'], $old['year'], $old['phone'], $hash);
        if ($stmt->execute()) {
            $_SESSION['student_id'] = $stmt->insert_id;
            $_SESSION['student_name'] = $old['name'];
            header('Location: '.base_url().'student/dashboard.php?welcome=1'); exit;
        }
        $errors[] = 'Registration failed. Please try again.';
    }
}
?>
<section class="auth-section">
  <div class="auth-card">
    <h1>Create your passport</h1>
    <p class="auth-sub">Join SmartPass and start collecting stamps</p>

    <?php if ($errors): ?>
      <div class="alert alert-error"><ul><?php foreach ($errors as $er) echo '<li>'.e($er).'</li>'; ?></ul></div>
    <?php endif; ?>

    <form method="post" class="auth-form" id="registerForm" novalidate>
      <label><span>Full Name</span><input type="text" name="name" required value="<?= e($old['name']) ?>" placeholder="Your name"></label>
      <div class="form-row">
        <label><span>Register Number</span><input type="text" name="register_number" required value="<?= e($old['register_number']) ?>" placeholder="CS21B001"></label>
        <label><span>Phone</span><input type="text" name="phone" value="<?= e($old['phone']) ?>" placeholder="9876543210"></label>
      </div>
      <label><span>Email</span><input type="email" name="email" required value="<?= e($old['email']) ?>" placeholder="you@smartpass.edu"></label>
      <div class="form-row">
        <label>
          <span>Department</span>
          <select name="department">
            <?php foreach (['CSE','ECE','IT','MECH','CIVIL','EEE','BME'] as $d): ?>
              <option <?= $old['department']===$d?'selected':'' ?>><?= $d ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>
          <span>Year</span>
          <select name="year">
            <?php foreach (['1st Year','2nd Year','3rd Year','4th Year'] as $y): ?>
              <option <?= $old['year']===$y?'selected':'' ?>><?= $y ?></option>
            <?php endforeach; ?>
          </select>
        </label>
      </div>
      <label><span>Password</span>
        <div class="password-field">
          <input type="password" name="password" id="pw1" required minlength="6" placeholder="At least 6 characters">
          <button type="button" class="pw-toggle" data-target="pw1" aria-label="Show password">👁</button>
        </div>
      </label>
      <label><span>Confirm Password</span>
        <div class="password-field">
          <input type="password" name="confirm_password" id="pw2" required placeholder="Re-enter password">
          <button type="button" class="pw-toggle" data-target="pw2" aria-label="Show password">👁</button>
        </div>
      </label>
      <button type="submit" class="btn btn-primary btn-block">Create Account</button>
    </form>
    <p class="auth-switch">Already have an account? <a href="<?= e(base_url()) ?>login.php">Login</a></p>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
