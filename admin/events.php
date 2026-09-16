<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = db()->prepare('DELETE FROM events WHERE event_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header('Location: '.base_url().'admin/events.php?deleted=1'); exit;
}
if (isset($_GET['deleted'])) { $msg = 'Event deleted.'; }

$events = db()->query('SELECT * FROM events ORDER BY event_date DESC')->fetch_all(MYSQLI_ASSOC);

$dashboardTitle = 'Manage Events';
$activeItem = 'events';
$role = 'admin';
require __DIR__ . '/../includes/dashboard_header.php';
?>
<?php if (!empty($msg)): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>

<div class="panel-head">
  <h3>All Events (<?= count($events) ?>)</h3>
  <a href="<?= e(base_url()) ?>admin/add-event.php" class="btn btn-primary">+ Add Event</a>
</div>

<div class="admin-table">
  <table>
    <thead><tr><th>Event</th><th>Category</th><th>Date</th><th>Venue</th><th>Capacity</th><th>Regs</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($events as $ev):
        $rem = seats_remaining((int)$ev['event_id']);
        $regs = (int)$ev['capacity'] - $rem;
      ?>
        <tr>
          <td><strong><?= e($ev['event_name']) ?></strong><br><small><?= e($ev['organizer']) ?></small></td>
          <td><span class="cat-pill cat-<?= e(strtolower($ev['category'])) ?>"><?= e($ev['category']) ?></span></td>
          <td><?= e(date('d M Y', strtotime($ev['event_date']))) ?></td>
          <td><?= e($ev['venue']) ?></td>
          <td><?= (int)$ev['capacity'] ?></td>
          <td><?= $regs ?></td>
          <td class="row-actions">
            <a href="<?= e(base_url()) ?>admin/edit-event.php?id=<?= (int)$ev['event_id'] ?>" class="btn btn-sm">Edit</a>
            <a href="<?= e(base_url()) ?>admin/events.php?delete=<?= (int)$ev['event_id'] ?>"
               class="btn btn-sm btn-danger" onclick="return confirm('Delete this event? This cannot be undone.');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$events): ?><tr><td colspan="7" class="empty-state">No events yet. Click "Add Event" to create one.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>
