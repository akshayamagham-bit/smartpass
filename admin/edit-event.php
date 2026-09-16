<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM events WHERE event_id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$ev = $stmt->get_result()->fetch_assoc();
if (!$ev) { header('Location: '.base_url().'admin/events.php'); exit; }

$errors = []; $done = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['event_name','description','category','event_date','event_time','venue','organizer','capacity','status'];
    $vals = [];
    foreach ($fields as $f) $vals[$f] = trim($_POST[$f] ?? '');
    if ($vals['event_name']==='') $errors[] = 'Event name is required.';
    if ($vals['description']==='') $errors[] = 'Description is required.';
    if (!in_array($vals['category'], EVENT_CATEGORIES, true)) $errors[] = 'Invalid category.';
    if ($vals['event_date']==='' || !strtotime($vals['event_date'])) $errors[] = 'Valid date required.';
    if ((int)$vals['capacity'] < 1) $errors[] = 'Capacity must be at least 1.';
    if (!in_array($vals['status'], ['upcoming','completed','cancelled'], true)) $errors[] = 'Invalid status.';

    if (!$errors) {
        $stmt = db()->prepare('UPDATE events SET event_name=?, description=?, category=?, event_date=?, event_time=?, venue=?, organizer=?, capacity=?, status=? WHERE event_id=?');
        $cap = (int)$vals['capacity'];
        $stmt->bind_param('sssssssssi', $vals['event_name'], $vals['description'], $vals['category'], $vals['event_date'], $vals['event_time'], $vals['venue'], $vals['organizer'], $vals['status'], $cap, $id);
        if ($stmt->execute()) {
            $done = true;
            $stmt = db()->prepare('SELECT * FROM events WHERE event_id = ?');
            $stmt->bind_param('i', $id); $stmt->execute();
            $ev = $stmt->get_result()->fetch_assoc();
        } else {
            $errors[] = 'Update failed.';
        }
    }
}

$dashboardTitle = 'Edit Event';
$activeItem = 'events';
$role = 'admin';
require __DIR__ . '/../includes/dashboard_header.php';
?>
<?php if ($done): ?><div class="alert alert-success">Event updated successfully.</div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $e) echo '<li>'.e($e).'</li>'; ?></ul></div><?php endif; ?>

<div class="dash-panel">
  <div class="panel-head"><h3>Edit: <?= e($ev['event_name']) ?></h3></div>
  <form method="post" class="auth-form wide" novalidate>
    <label><span>Event Name</span><input type="text" name="event_name" required value="<?= e($ev['event_name']) ?>"></label>
    <label><span>Description</span><textarea name="description" rows="4" required><?= e($ev['description']) ?></textarea></label>
    <div class="form-row">
      <label><span>Category</span>
        <select name="category">
          <?php foreach (EVENT_CATEGORIES as $c): ?><option <?= $ev['category']===$c?'selected':'' ?>><?= e($c) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label><span>Capacity</span><input type="number" name="capacity" min="1" required value="<?= (int)$ev['capacity'] ?>"></label>
    </div>
    <div class="form-row">
      <label><span>Date</span><input type="date" name="event_date" required value="<?= e($ev['event_date']) ?>"></label>
      <label><span>Time</span><input type="text" name="event_time" required value="<?= e($ev['event_time']) ?>"></label>
    </div>
    <label><span>Venue</span><input type="text" name="venue" required value="<?= e($ev['venue']) ?>"></label>
    <label><span>Organizer</span><input type="text" name="organizer" required value="<?= e($ev['organizer']) ?>"></label>
    <label><span>Status</span>
      <select name="status">
        <?php foreach (['upcoming','completed','cancelled'] as $st): ?>
          <option value="<?= e($st) ?>" <?= $ev['status']===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <button type="submit" class="btn btn-primary">Save Changes</button>
    <a href="<?= e(base_url()) ?>admin/events.php" class="btn btn-ghost">Back</a>
  </form>
</div>
<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>
