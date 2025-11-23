<?php
session_start();

if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $u = $_POST['username'] ?? '';
  $p = $_POST['password'] ?? '';
  $csrf = $_POST['csrf'] ?? '';
  if (!hash_equals($_SESSION['csrf'], $csrf)) {
    $error = 'Invalid session token.';
  } else if ($u === 'admin' && $p === 'admin123') {
    $_SESSION['admin'] = true;
    header('Location: dashboard.php');
    exit;
  } else {
    $error = 'Username atau password salah.';
  }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>CMS Login – Business Analytics Lab</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/styles.css">
</head>

<body class="cms-login-body">
  <div class="cms-login-wrap">
    <div class="cms-login-logo">
      <img src="../assets/img/Logo.png" alt="Logo" onerror="this.style.display='none'">
      <div>
        <div class="cms-login-title">Business Analytics Lab</div>
        <div class="cms-login-sub">CMS Administrator Login</div>
      </div>
    </div>

    <?php if($error): ?>
      <div class="cms-login-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="cms-login-form" id="cmsLoginForm">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
      
      <div>
        <label class="cms-login-label" for="username">Username</label>
        <input class="cms-login-input" type="text" id="username" name="username" placeholder="Masukkan username" required>
      </div>

      <div>
        <label class="cms-login-label" for="password">Password</label>
        <input class="cms-login-input" type="password" id="password" name="password" placeholder="Masukkan password" required>
      </div>

      <button class="cms-login-btn" type="submit">Login</button>
    </form>

    <p class="cms-login-hint">💡 Hint: admin / admin123</p>
    <div class="cms-login-footer">© 2025 Business Analytics Lab – Polinema IT</div>
  </div>
  <script src="../assets/app.js"></script>
</body>
</html>
