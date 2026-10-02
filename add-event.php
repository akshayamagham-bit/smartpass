<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$errors = []; $old = ['event_name'=>'','description'=>'','category'=>'Technical','event_date'=>'','event_time'=>'','venue'=>'','organizer'=>'','capacity'=>'100'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($old) as $f) $old[$f] = trim($_POST[$f] ?? '');
    if ($old['event_name']==='') $errors[] = 'Event name is required.';
    if ($old['description']==='') $errors[] = 'Description is required.';
    if (!in_array($old['category'], EVENT_CATEGORIES, true)) $errors[] = 'Invalid category.';
    if ($old['event_date']==='' || !strtotime($old['event_date'])) $errors[] = 'Valid date is required.';
    if ($old['event_time']==='') $errors[] = 'Time is required.';
    if ($old['venue']==='') $errors[] = 'Venue is required.';
    if ($old['organizer']==='') $errors[] = 'Organizer is required.';
    if ((int)$old['capacity'] < 1) $errors[] = 'Capacity must be at least 1.';

    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO events (event_name, description, category, event_date, event_time, venue, organizer, capacity, status) VALUES (?,?,?,?,?,?,?,?, "upcoming")');
        $cap = (int)$old['capacity'];
        $stmt->bind_param('sssssssi', $old['event_name'], $old['description'], $old['category'], $old['event_date'], $old['event_time'], $old['venue'], $old['organizer'], $cap);
        if ($stmt->execute()) {
            header('Location: '.base_url().'admin/events.php?created=1'); exit;
        }
        $errors[] = 'Failed to create event.';
    }
}

$dashboardTitle = 'Add Event';
$activeItem = 'add-event';
$role = 'admin';
require __DIR__ . '/../includes/dashboard_header.php';
?>
<?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $e) echo '<li>'.e($e).'</li>'; ?></ul></div><?php endif; ?>

<div class="dash-panel">
  <div class="panel-head"><h3>Create New Event</h3></div>
  <form method="post" class="auth-form wide" novalidate>
    <label><span>Event Name</span><input type="text" name="event_name" required value="<?= e($old['event_name']) ?>"></label>
    <label><span>Description</span><textarea name="description" rows="4" required><?= e($old['description']) ?></textarea></label>
    <div class="form-row">
      <label><span>Category</span>
        <select name="category">
          <?php foreach (EVENT_CATEGORIES as $c): ?><option <?= $old['category']===$c?'selected':'' ?>><?= e($c) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label><span>Capacity</span><input type="number" name="capacity" min="1" required value="<?= e($old['capacity']) ?>"></label>
    </div>
    <div class="form-row">
      <label><span>Date</span><input type="date" name="event_date" required value="<?= e($old['event_date']) ?>"></label>
      <label><span>Time</span><input type="text" name="event_time" required placeholder="10:00 AM" value="<?= e($old['event_time']) ?>"></label>
    </div>
    <label><span>Venue</span><input type="text" name="venue" required value="<?= e($old['venue']) ?>"></label>
    <label><span>Organizer</span><input type="text" name="organizer" required value="<?= e($old['organizer']) ?>"></label>
    <button type="submit" class="btn btn-primary">Create Event</button>
    <a href="<?= e(base_url()) ?>admin/events.php" class="btn btn-ghost">Cancel</a>
  </form>
</div>
<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>
