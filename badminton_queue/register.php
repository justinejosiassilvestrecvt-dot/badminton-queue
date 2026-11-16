<?php
require_once 'includes/auth.php';
$errors = [];
$f = ['first_name'=>'','last_name'=>'','age'=>'','gender'=>'','address'=>'','contact_number'=>'','email'=>'','username'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($f as $k => $_) $f[$k] = trim($_POST[$k] ?? '');
    $pass = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    foreach ($f as $k => $v) if ($v === '') $errors[] = ucfirst(str_replace('_',' ',$k)) . ' is required.';
    if ($f['age'] !== '' && (!ctype_digit($f['age']) || $f['age'] < 5 || $f['age'] > 120)) $errors[] = 'Enter a valid age.';
    if ($f['email'] !== '' && !filter_var($f['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email.';
    if (strlen($pass) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($pass !== $confirm) $errors[] = 'Passwords do not match.';

    if (!$errors) {
        $chk = $pdo->prepare("SELECT 1 FROM users WHERE username = ? OR email = ?");
        $chk->execute([$f['username'], $f['email']]);
        if ($chk->fetch()) $errors[] = 'Username or email already taken.';
    }
    if (!$errors) {
        $ins = $pdo->prepare("INSERT INTO users (first_name,last_name,age,gender,address,contact_number,email,username,password_hash)
                              VALUES (?,?,?,?,?,?,?,?,?)");
        $ins->execute([$f['first_name'],$f['last_name'],$f['age'],$f['gender'],$f['address'],
                       $f['contact_number'],$f['email'],$f['username'],password_hash($pass, PASSWORD_DEFAULT)]);
        header('Location: login.php?registered=1');
        exit;
    }
}
include 'includes/header.php';
?>
<div class="card narrow">
  <h2>Create an account</h2>
  <?php foreach ($errors as $er): ?><p class="error"><?= e($er) ?></p><?php endforeach; ?>
  <form method="post">
    <div class="grid2">
      <label>First name<input name="first_name" value="<?= e($f['first_name']) ?>"></label>
      <label>Last name<input name="last_name" value="<?= e($f['last_name']) ?>"></label>
      <label>Age<input type="number" name="age" value="<?= e($f['age']) ?>"></label>
      <label>Gender
        <select name="gender">
          <?php foreach (['Male','Female','Other'] as $g): ?>
            <option <?= $f['gender']===$g?'selected':'' ?>><?= $g ?></option>
          <?php endforeach; ?>
        </select></label>
    </div>
    <label>Address<input name="address" value="<?= e($f['address']) ?>"></label>
    <label>Contact number<input name="contact_number" value="<?= e($f['contact_number']) ?>"></label>
    <label>Email<input type="email" name="email" value="<?= e($f['email']) ?>"></label>
    <label>Username<input name="username" value="<?= e($f['username']) ?>"></label>
    <div class="grid2">
      <label>Password<input type="password" name="password"></label>
      <label>Confirm password<input type="password" name="confirm"></label>
    </div>
    <button class="btn">Register</button>
    <p>Already have an account? <a href="login.php">Login</a></p>
  </form>
</div>
<?php include 'includes/footer.php'; ?>
