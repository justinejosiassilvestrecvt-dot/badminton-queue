<?php
require_once 'includes/auth.php';
require_login();
$uid = current_user_id();
$court_id = (int)($_GET['court_id'] ?? $_POST['court_id'] ?? 0);
$search = trim($_GET['q'] ?? $_POST['q'] ?? '');
$qs = $search !== '' ? '&q=' . urlencode($search) : '';

$cs = $pdo->prepare("SELECT * FROM courts WHERE court_id=?");
$cs->execute([$court_id]);
$court = $cs->fetch();
if (!$court) { header('Location: index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $pdo->prepare("INSERT INTO queue_entries (court_id,player_name,skill_level,game_type,status,added_by,last_updated_by)
                       VALUES (?,?,?,?,?,?,?)")
            ->execute([$court_id, trim($_POST['player_name']), $_POST['skill_level'], $_POST['game_type'], 'Waiting', $uid, $uid]);
    } elseif ($action === 'update') {
        $pdo->prepare("UPDATE queue_entries SET player_name=?, skill_level=?, game_type=?, status=?, last_updated_by=? WHERE queue_id=? AND court_id=?")
            ->execute([trim($_POST['player_name']), $_POST['skill_level'], $_POST['game_type'], $_POST['status'], $uid, $_POST['queue_id'], $court_id]);
    } elseif ($action === 'status') {
        $pdo->prepare("UPDATE queue_entries SET status=?, last_updated_by=? WHERE queue_id=? AND court_id=?")
            ->execute([$_POST['status'], $uid, $_POST['queue_id'], $court_id]);
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM queue_entries WHERE queue_id=? AND court_id=?")->execute([$_POST['queue_id'], $court_id]);
    }
    header("Location: queue.php?court_id=$court_id$qs");
    exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM queue_entries WHERE queue_id=? AND court_id=?");
    $s->execute([$_GET['edit'], $court_id]);
    $edit = $s->fetch();
}

$st = $pdo->prepare("
  SELECT q.*, a.username AS added_name, u.username AS updated_name
  FROM queue_entries q
  LEFT JOIN users a ON a.user_id = q.added_by
  LEFT JOIN users u ON u.user_id = q.last_updated_by
  WHERE q.court_id = ?
    AND (q.player_name LIKE ? OR q.skill_level LIKE ? OR q.game_type LIKE ? OR q.status LIKE ?)
  ORDER BY FIELD(q.status,'Playing','Waiting','Done'), q.queue_id");
$like = '%' . $search . '%';
$st->execute([$court_id, $like, $like, $like, $like]);
$entries = $st->fetchAll();

include 'includes/header.php';
?>
<p><a href="index.php">&larr; Back to courts</a></p>
<div class="card">
  <h2><?= e($court['court_name']) ?> <small>(<?= e($court['location']) ?> · <?= e($court['status']) ?>)</small></h2>
  <h3><?= $edit ? 'Edit Queue Entry' : 'Add Player to Queue' ?></h3>
  <form method="post" class="inline">
    <input type="hidden" name="court_id" value="<?= $court_id ?>"><input type="hidden" name="q" value="<?= e($search) ?>">
    <input type="hidden" name="action" value="<?= $edit ? 'update' : 'add' ?>">
    <?php if ($edit): ?><input type="hidden" name="queue_id" value="<?= e($edit['queue_id']) ?>"><?php endif; ?>
    <label>Player name<input name="player_name" required value="<?= e($edit['player_name'] ?? '') ?>"></label>
    <label>Skill
      <select name="skill_level">
        <?php foreach (['Beginner','Intermediate','Advanced'] as $o): ?>
          <option <?= ($edit['skill_level'] ?? '')===$o?'selected':'' ?>><?= $o ?></option>
        <?php endforeach; ?>
      </select></label>
    <label>Game
      <select name="game_type">
        <?php foreach (['Singles','Doubles'] as $o): ?>
          <option <?= ($edit['game_type'] ?? '')===$o?'selected':'' ?>><?= $o ?></option>
        <?php endforeach; ?>
      </select></label>
    <?php if ($edit): ?>
    <label>Status
      <select name="status">
        <?php foreach (['Waiting','Playing','Done'] as $o): ?>
          <option <?= $edit['status']===$o?'selected':'' ?>><?= $o ?></option>
        <?php endforeach; ?>
      </select></label>
    <?php endif; ?>
    <button class="btn"><?= $edit ? 'Update' : 'Add to Queue' ?></button>
    <?php if ($edit): ?><a class="btn gray" href="queue.php?court_id=<?= $court_id ?><?= $qs ?>">Cancel</a><?php endif; ?>
  </form>
</div>

<div class="card">
  <h3>Queue</h3>
  <form method="get" class="inline" style="margin-bottom:14px">
    <input type="hidden" name="court_id" value="<?= $court_id ?>">
    <label>Search players
      <input name="q" placeholder="Player, skill, game type or status" value="<?= e($search) ?>"></label>
    <button class="btn">Search</button>
    <?php if ($search !== ''): ?><a class="btn gray" href="queue.php?court_id=<?= $court_id ?>">Clear</a><?php endif; ?>
  </form>
  <?php if ($search !== ''): ?><p><small><?= count($entries) ?> result(s) for "<?= e($search) ?>"</small></p><?php endif; ?>
  <table>
    <tr><th>#</th><th>Player</th><th>Skill</th><th>Game</th><th>Status</th>
        <th>Date added</th><th>Added by</th><th>Last updated by</th><th>Last updated</th><th>Actions</th></tr>
    <?php $n = 0; foreach ($entries as $q): $n++; ?>
    <tr>
      <td><?= $n ?></td>
      <td><?= e($q['player_name']) ?></td>
      <td><?= e($q['skill_level']) ?></td>
      <td><?= e($q['game_type']) ?></td>
      <td><span class="badge <?= strtolower($q['status']) ?>"><?= e($q['status']) ?></span></td>
      <td><?= e($q['date_added']) ?></td>
      <td><?= e($q['added_name'] ?? '—') ?></td>
      <td><?= e($q['updated_name'] ?? '—') ?></td>
      <td><?= e($q['last_updated']) ?></td>
      <td class="actions">
        <?php if ($q['status'] === 'Waiting'): ?>
          <form method="post"><input type="hidden" name="court_id" value="<?= $court_id ?>"><input type="hidden" name="q" value="<?= e($search) ?>">
            <input type="hidden" name="action" value="status"><input type="hidden" name="status" value="Playing">
            <input type="hidden" name="queue_id" value="<?= e($q['queue_id']) ?>"><button class="btn small">Call to court</button></form>
        <?php elseif ($q['status'] === 'Playing'): ?>
          <form method="post"><input type="hidden" name="court_id" value="<?= $court_id ?>"><input type="hidden" name="q" value="<?= e($search) ?>">
            <input type="hidden" name="action" value="status"><input type="hidden" name="status" value="Done">
            <input type="hidden" name="queue_id" value="<?= e($q['queue_id']) ?>"><button class="btn small">Finish</button></form>
        <?php endif; ?>
        <a class="btn small gray" href="queue.php?court_id=<?= $court_id ?>&edit=<?= e($q['queue_id']) ?><?= $qs ?>">Edit</a>
        <form method="post" onsubmit="return confirm('Remove this player from the queue?')">
          <input type="hidden" name="court_id" value="<?= $court_id ?>"><input type="hidden" name="q" value="<?= e($search) ?>">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="queue_id" value="<?= e($q['queue_id']) ?>">
          <button class="btn small red">Delete</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$entries): ?><tr><td colspan="10">No queue entries found.</td></tr><?php endif; ?>
  </table>
</div>
<?php include 'includes/footer.php'; ?>
