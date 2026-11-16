<?php
require_once 'includes/auth.php';
require_login();
$uid = current_user_id();
$search = trim($_GET['q'] ?? $_POST['q'] ?? '');
$qs = $search !== '' ? '?q=' . urlencode($search) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $pdo->prepare("INSERT INTO courts (court_name,location,status,added_by,last_updated_by) VALUES (?,?,?,?,?)")
            ->execute([trim($_POST['court_name']), trim($_POST['location']), $_POST['status'], $uid, $uid]);
    } elseif ($action === 'update') {
        $pdo->prepare("UPDATE courts SET court_name=?, location=?, status=?, last_updated_by=? WHERE court_id=?")
            ->execute([trim($_POST['court_name']), trim($_POST['location']), $_POST['status'], $uid, $_POST['court_id']]);
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM courts WHERE court_id=?")->execute([$_POST['court_id']]);
    }
    header('Location: index.php' . $qs);
    exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM courts WHERE court_id=?");
    $s->execute([$_GET['edit']]);
    $edit = $s->fetch();
}

$sql = "
  SELECT c.*, a.username AS added_name, u.username AS updated_name,
         (SELECT COUNT(*) FROM queue_entries q WHERE q.court_id=c.court_id AND q.status='Waiting') AS waiting
  FROM courts c
  LEFT JOIN users a ON a.user_id = c.added_by
  LEFT JOIN users u ON u.user_id = c.last_updated_by
  WHERE (c.court_name LIKE :q1 OR c.location LIKE :q2 OR c.status LIKE :q3 OR a.username LIKE :q4)
  ORDER BY c.court_id";
$st = $pdo->prepare($sql);
$like = '%' . $search . '%';
$st->execute([':q1'=>$like, ':q2'=>$like, ':q3'=>$like, ':q4'=>$like]);
$courts = $st->fetchAll();

include 'includes/header.php';
?>
<div class="card">
  <h2><?= $edit ? 'Edit Court' : 'Add Court' ?></h2>
  <form method="post" class="inline">
    <input type="hidden" name="action" value="<?= $edit ? 'update' : 'add' ?>">
    <input type="hidden" name="q" value="<?= e($search) ?>">
    <?php if ($edit): ?><input type="hidden" name="court_id" value="<?= e($edit['court_id']) ?>"><?php endif; ?>
    <label>Court name<input name="court_name" required value="<?= e($edit['court_name'] ?? '') ?>"></label>
    <label>Location<input name="location" required value="<?= e($edit['location'] ?? '') ?>"></label>
    <label>Status
      <select name="status">
        <?php foreach (['Available','In Use','Maintenance'] as $s): ?>
          <option <?= ($edit['status'] ?? '')===$s?'selected':'' ?>><?= $s ?></option>
        <?php endforeach; ?>
      </select></label>
    <button class="btn"><?= $edit ? 'Update' : 'Add' ?></button>
    <?php if ($edit): ?><a class="btn gray" href="index.php<?= e($qs) ?>">Cancel</a><?php endif; ?>
  </form>
</div>

<div class="card">
  <h2>Courts</h2>
  <form method="get" class="inline" style="margin-bottom:14px">
    <label>Search courts
      <input name="q" placeholder="Court name, location, status or added by" value="<?= e($search) ?>"></label>
    <button class="btn">Search</button>
    <?php if ($search !== ''): ?><a class="btn gray" href="index.php">Clear</a><?php endif; ?>
  </form>
  <?php if ($search !== ''): ?><p><small><?= count($courts) ?> result(s) for "<?= e($search) ?>"</small></p><?php endif; ?>
  <table>
    <tr><th>ID</th><th>Court</th><th>Location</th><th>Status</th><th>Waiting</th>
        <th>Date added</th><th>Added by</th><th>Last updated by</th><th>Last updated</th><th>Actions</th></tr>
    <?php foreach ($courts as $c): ?>
    <tr>
      <td><?= e($c['court_id']) ?></td>
      <td><?= e($c['court_name']) ?></td>
      <td><?= e($c['location']) ?></td>
      <td><span class="badge <?= strtolower(str_replace(' ','',$c['status'])) ?>"><?= e($c['status']) ?></span></td>
      <td><?= e($c['waiting']) ?></td>
      <td><?= e($c['date_added']) ?></td>
      <td><?= e($c['added_name'] ?? '—') ?></td>
      <td><?= e($c['updated_name'] ?? '—') ?></td>
      <td><?= e($c['last_updated']) ?></td>
      <td class="actions">
        <a class="btn small" href="queue.php?court_id=<?= e($c['court_id']) ?>">View Queue</a>
        <a class="btn small gray" href="index.php?edit=<?= e($c['court_id']) ?><?= $search !== '' ? '&q=' . urlencode($search) : '' ?>">Edit</a>
        <form method="post" onsubmit="return confirm('Delete this court and its whole queue?')">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="q" value="<?= e($search) ?>">
          <input type="hidden" name="court_id" value="<?= e($c['court_id']) ?>">
          <button class="btn small red">Delete</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$courts): ?><tr><td colspan="10">No courts found.</td></tr><?php endif; ?>
  </table>
</div>
<?php include 'includes/footer.php'; ?>
