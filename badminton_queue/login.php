<?php
require_once 'includes/auth.php';
if (!empty($_SESSION['user_id'])) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([trim($_POST['username'] ?? '')]);
    $u = $stmt->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $u['user_id'];
        $_SESSION['username'] = $u['username'];
        header('Location: index.php');
        exit;
    }
    $error = 'Invalid username or password.';
}
include 'includes/header.php';
?>
<div class="card narrow">
  <h2>Login</h2>
  <?php if (isset($_GET['registered'])): ?><p class="success">Registration successful. Please log in.</p><?php endif; ?>
  <?php if (isset($_GET['loggedout'])): ?><p class="success">You have been logged out.</p><?php endif; ?>
  <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
  <form method="post">
    <label>Username<input name="username" required></label>
    <label>Password<input type="password" name="password" required></label>
    <button class="btn">Login</button>
    <p>No account yet? <a href="register.php">Register</a></p>
  </form>
</div>
<?php include 'includes/footer.php'; ?>
