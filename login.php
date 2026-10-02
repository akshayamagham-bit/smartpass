<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Login';
$activeNav = 'login';
require __DIR__ . '/includes/header.php';

// redirect if already logged in
if (is_student_logged_in()) { header('Location: '.base_url().'student/dashboard.php'); exit; }
if (is_admin_logged_in())   { header('Location: '.base_url().'admin/dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? 'student';
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } elseif ($role === 'admin') {
        $stmt = db()->prepare('SELECT * FROM admins WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['name'];
            header('Location: '.base_url().'admin/dashboard.php'); exit;
        }
        $error = 'Invalid admin credentials.';
    } else {
        $stmt = db()->prepare('SELECT * FROM students WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stu = $stmt->get_result()->fetch_assoc();
        if ($stu && password_verify($password, $stu['password_hash'])) {
            $_SESSION['student_id'] = $stu['student_id'];
            $_SESSION['student_name'] = $stu['name'];
            header('Location: '.base_url().'student/dashboard.php'); exit;
        }
        $error = 'Invalid student credentials.';
    }
}
?>
<section class="auth-section">
  <div class="auth-card">
    <h1>Welcome back</h1>
    <p class="auth-sub">Login to your SmartPass account</p>

    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <div class="role-toggle" id="roleToggle">
      <button type="button" class="role-btn active" data-role="student">Student</button>
      <button type="button" class="role-btn" data-role="admin">Admin</button>
    </div>

    <form method="post" id="loginForm" class="auth-form">
      <input type="hidden" name="role" id="roleHidden" value="student">
      <label>
        <span>Email</span>
        <input type="email" name="email" required placeholder="you@smartpass.edu">
      </label>
      <label>
        <span>Password</span>
        <div class="password-field">
          <input type="password" name="password" id="passwordInput" required placeholder="••••••••">
          <button type="button" class="pw-toggle" data-target="passwordInput" aria-label="Show password">👁</button>
        </div>
      </label>
      <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>

    <p class="auth-switch">New student? <a href="<?= e(base_url()) ?>register.php">Create an account</a></p>

    <div class="demo-creds">
      <h4>Demo credentials</h4>
      <p><strong>Student:</strong> rahul@smartpass.edu / password123</p>
      <p><strong>Admin:</strong> admin@smartpass.edu / admin123</p>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
