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
<style>
  :root {
    --navy-900:#0A2540;
    --navy-800:#0B3A6F;
    --sky-400:#54A9FF;
    --sky-100:#D6EBFF;
    --sky-50:#EAF4FF;
  }

  body {
    font-family:'Inter',system-ui,sans-serif;
    background:linear-gradient(135deg,var(--sky-50),#fff 40%,var(--sky-100));
    display:flex;
    align-items:center;
    justify-content:center;
    min-height:100vh;
    margin:0;
    color:var(--navy-900);
  }

  .wrap {
    background:#fff;
    border:1px solid var(--sky-100);
    border-radius:24px;
    box-shadow:0 18px 40px rgba(16,76,140,.12);
    width:90%;
    max-width:420px;
    padding:40px 36px;
    text-align:center;
  }

  .logo {
    display:flex;
    align-items:center;
    justify-content:center;
    gap:12px;
    margin-bottom:28px;
  }

  .logo img {
    height:48px;
    width:48px;
    border-radius:10px;
    border:1px solid var(--sky-100);
  }

  .title {
    font-weight:700;
    font-size:20px;
    color:var(--navy-900);
  }

  .sub {
    font-size:13px;
    color:rgba(10,37,64,.65);
    margin-top:4px;
  }

  form {
    display:flex;
    flex-direction:column;
    gap:18px;
    margin-top:8px;
    text-align:left;
  }

  label {
    font-size:14px;
    font-weight:600;
    color:var(--navy-900);
    display:block;
    margin-bottom:6px;
  }

  .input {
    width:100%;
    padding:12px 14px;
    border:1px solid var(--sky-100);
    border-radius:10px;
    background:#fff;
    font-size:15px;
    transition:all .2s ease;
  }

  .input:focus {
    outline:none;
    border-color:var(--sky-400);
    box-shadow:0 0 0 3px rgba(84,169,255,.25);
  }

  .btn {
    background:linear-gradient(135deg,var(--sky-400),var(--navy-800));
    color:#fff;
    font-weight:600;
    border:none;
    border-radius:10px;
    padding:12px 0;
    font-size:15px;
    cursor:pointer;
    transition:.25s ease;
    width:100%;
    text-align:center;
  }

  .btn:hover {
    filter:brightness(1.05);
    transform:translateY(-1px);
  }

  .err {
    background:#ffe9e9;
    border:1px solid #ffc9c9;
    color:#b00020;
    padding:10px;
    border-radius:10px;
    font-size:13px;
    margin-bottom:12px;
  }

  .hint {
    font-size:13px;
    color:rgba(10,37,64,.65);
    margin-top:14px;
    text-align:center;
  }

  footer {
    margin-top:18px;
    font-size:12px;
    color:rgba(10,37,64,.5);
    text-align:center;
  }
</style>
</head>

<body>
  <div class="wrap">
    <div class="logo">
      <img src="../assets/img/Logo.png" alt="Logo" onerror="this.style.display='none'">
      <div>
        <div class="title">Business Analytics Lab</div>
        <div class="sub">CMS Administrator Login</div>
      </div>
    </div>

    <?php if($error): ?>
      <div class="err"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
      
      <div>
        <label for="username">Username</label>
        <input class="input" type="text" id="username" name="username" placeholder="Masukkan username" required>
      </div>

      <div>
        <label for="password">Password</label>
        <input class="input" type="password" id="password" name="password" placeholder="Masukkan password" required>
      </div>

      <button class="btn" type="submit">Login</button>
    </form>

    <p class="hint">💡 Hint: admin / admin123</p>
    <footer>© 2025 Business Analytics Lab – Polinema IT</footer>
  </div>
</body>
</html>
