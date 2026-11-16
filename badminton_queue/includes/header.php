<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Badminton Queue</title>
<link rel="stylesheet" href="css/style.css">
</head><body>
<nav class="nav">
  <a class="brand" href="index.php">🏸 Badminton Queue</a>
  <?php if (!empty($_SESSION['user_id'])): ?>
    <span>Logged in as <strong><?= e($_SESSION['username']) ?></strong>
    <a class="btn small" href="logout.php">Logout</a></span>
  <?php endif; ?>
</nav>
<main class="container">
