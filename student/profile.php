<?php
require_once __DIR__ . '/../includes/functions.php';
require_student();
$student = current_student();
$sid = (int)$student['student_id'];

$msg = ''; $msgType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $bio = trim($_POST['profile_bio'] ?? '');
        if ($name === '') {
            $msg = 'Name cannot be empty.'; $msgType = 'error';
        } else {
            $stmt = db()->prepare('UPDATE students SET name=?, phone=?, profile_bio=? WHERE student_id=?');
            $stmt->bind_param('sssi', $name, $phone, $bio, $sid);
            if ($stmt->execute()) {
                $msg = 'Profile updated successfully.'; $msgType = 'success';
                $_SESSION['student_name'] = $name;
                $student = current_student();
            } else { $msg = 'Update failed.'; $msgType = 'error'; }
        }
    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (!password_verify($current, $student['password_hash'])) {
            $msg = 'Current password is incorrect.'; $msgType = 'error';
        } elseif (strlen($new) < 6) {
            $msg = 'New password must be at least 6 characters.'; $msgType = 'error';
        } elseif ($new !== $confirm) {
            $msg = 'New passwords do not match.'; $msgType = 'error';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = db()->prepare('UPDATE students SET password_hash=? WHERE student_id=?');
            $stmt->bind_param('si', $hash, $sid);
            if ($stmt->execute()) { $msg = 'Password changed successfully.'; $msgType = 'success'; $student = current_student(); }
            else { $msg = 'Password change failed.'; $msgType = 'error'; }
        }
    }
}

$stampCount = student_stamp_count($sid);
$achCount = count(student_unlocked_codes($sid));

$dashboardTitle = 'Profile';
$activeItem = 'profile';
$role = 'student';
require __DIR__ . '/../includes/dashboard_header.php';
?>
<?php if ($msg): ?><div class="alert alert-<?= e($msgType) ?>"><?= e($msg) ?></div><?php endif; ?>

<div class="profile-grid">
  <div class="profile-card">
    <div class="profile-avatar"><?= e(strtoupper(substr($student['name'],0,1))) ?></div>
    <h2><?= e($student['name']) ?></h2>
    <p class="profile-reg"><?= e($student['register_number']) ?></p>
    <p class="profile-email"><?= e($student['email']) ?></p>
    <div class="profile-stats">
      <div><span><?= $stampCount ?></span><label>Stamps</label></div>
      <div><span><?= $achCount ?></span><label>Achievements</label></div>
      <div><span><?= student_rank($sid) ?: '—' ?></span><label>Rank</label></div>
    </div>
    <div class="profile-meta">
      <p><strong>Department:</strong> <?= e($student['department']) ?></p>
      <p><strong>Year:</strong> <?= e($student['year']) ?></p>
      <p><strong>Phone:</strong> <?= e($student['phone'] ?: '—') ?></p>
    </div>
    <?php if ($student['profile_bio']): ?>
      <div class="profile-bio"><h4>Bio</h4><p><?= nl2br(e($student['profile_bio'])) ?></p></div>
    <?php endif; ?>
  </div>

  <div class="profile-forms">
    <div class="dash-panel">
      <div class="panel-head"><h3>Edit Profile</h3></div>
      <form method="post" class="auth-form">
        <input type="hidden" name="action" value="update_profile">
        <label><span>Name</span><input type="text" name="name" required value="<?= e($student['name']) ?>"></label>
        <label><span>Phone</span><input type="text" name="phone" value="<?= e($student['phone'] ?? '') ?>"></label>
        <label><span>Bio</span><textarea name="profile_bio" rows="3" placeholder="Tell us about yourself…"><?= e($student['profile_bio'] ?? '') ?></textarea></label>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </form>
    </div>

    <div class="dash-panel">
      <div class="panel-head"><h3>Change Password</h3></div>
      <form method="post" class="auth-form" id="pwForm" novalidate>
        <input type="hidden" name="action" value="change_password">
        <label><span>Current Password</span>
          <div class="password-field"><input type="password" name="current_password" id="cpw" required><button type="button" class="pw-toggle" data-target="cpw">👁</button></div>
        </label>
        <label><span>New Password</span>
          <div class="password-field"><input type="password" name="new_password" id="npw" required minlength="6"><button type="button" class="pw-toggle" data-target="npw">👁</button></div>
        </label>
        <label><span>Confirm New Password</span>
          <div class="password-field"><input type="password" name="confirm_password" id="cnpw" required><button type="button" class="pw-toggle" data-target="cnpw">👁</button></div>
        </label>
        <button type="submit" class="btn btn-dark">Change Password</button>
      </form>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>
