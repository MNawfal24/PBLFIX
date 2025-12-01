<?php
require_once __DIR__.'/helpers.php';

// Only logged-in admin with username can access
require_admin();
if (empty($_SESSION['username'])){
  header('Location: login.php');
  exit;
}

$csrf   = csrf_token();
$admins = load_admins();
$message = '';

// Normalize admins to array of associative arrays with id, username, password
if (!is_array($admins)) $admins = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  $action = $_POST['action'] ?? '';
  $token  = $_POST['csrf'] ?? '';
  check_csrf($token);

  if ($action === 'add'){
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if ($username === '' || $password === ''){
      $message = 'Username dan password wajib diisi.';
    } else {
      foreach ($admins as $a){
        if (isset($a['username']) && $a['username'] === $username){
          $message = 'Username sudah digunakan.';
          break;
        }
      }
      if ($message === ''){
        $nextId = 1;
        foreach ($admins as $a){
          if (isset($a['id']) && is_int($a['id']) && $a['id'] >= $nextId){
            $nextId = $a['id'] + 1;
          }
        }
        $admins[] = [
          'id'       => $nextId,
          'username' => $username,
          'password' => password_hash($password, PASSWORD_DEFAULT),
        ];
        save_admins($admins);
        header('Location: admin-manage.php');
        exit;
      }
    }
  } elseif ($action === 'delete'){
    $id = (int)($_POST['id'] ?? 0);
    $currentUser = $_SESSION['username'] ?? '';
    $newAdmins = [];
    foreach ($admins as $a){
      if (!isset($a['id']) || !isset($a['username'])){
        $newAdmins[] = $a;
        continue;
      }
      if ($a['id'] === $id && $a['username'] === $currentUser){
        // Prevent deleting yourself
        $newAdmins[] = $a;
      } elseif ($a['id'] === $id){
        // skip (delete)
        continue;
      } else {
        $newAdmins[] = $a;
      }
    }
    $admins = $newAdmins;
    save_admins($admins);
    header('Location: admin-manage.php');
    exit;
  }
}

?><!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Kelola Admin – CMS</title>
  <link rel="stylesheet" href="../assets/styles.css">
</head>
<body class="cms-body">
  <header class="cms-header">
    <div class="cms-wrap cms-row">
      <div class="cms-row" style="gap:10px">
        <img src="../assets/img/logo.png" alt="" style="height:34px;border-radius:8px" onerror="this.style.display='none'">
        <strong>Business Analytics Lab – Kelola Admin</strong>
      </div>
      <div class="cms-row" style="gap:8px">
        <a class="cms-btn-outline" href="dashboard.php">Kembali ke Dashboard</a>
        <form method="post" action="logout.php"><button class="cms-btn-outline" type="submit">Logout</button></form>
      </div>
    </div>
  </header>

  <main class="cms-wrap" style="margin-top:16px">
    <section class="cms-card">
      <h3>Daftar Admin</h3>
      <p class="cms-note">Anda dapat menambah admin baru dan menghapus admin lain. Anda tidak dapat menghapus akun Anda sendiri.</p>

      <?php if($message): ?>
        <div class="booking-alert booking-alert-err"><?php echo htmlspecialchars($message); ?></div>
      <?php endif; ?>

      <div class="cms-table-wrap">
        <table class="cms-table">
          <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Aksi</th>
          </tr>
          <?php foreach($admins as $a): ?>
            <tr>
              <td><?php echo isset($a['id']) ? (int)$a['id'] : '-'; ?></td>
              <td><?php echo htmlspecialchars($a['username'] ?? ''); ?></td>
              <td>
                <?php $currentUser = $_SESSION['username'] ?? ''; ?>
                <?php if (!empty($a['username']) && $a['username'] !== $currentUser): ?>
                  <form method="post" action="admin-manage.php" style="display:inline" onsubmit="return confirm('Hapus admin ini?');">
                    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo isset($a['id']) ? (int)$a['id'] : 0; ?>">
                    <button type="submit" class="cms-btn-outline">Hapus</button>
                  </form>
                <?php else: ?>
                  <span class="cms-note">(akun Anda)</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>
    </section>

    <section class="cms-card" style="margin-top:16px;margin-bottom:24px">
      <h3>Tambah Admin Baru</h3>
      <form method="post" action="admin-manage.php" class="cms-grid cms-grid-two">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="action" value="add">
        <div>
          <label>Username</label>
          <input class="cms-input" type="text" name="username" required>
        </div>
        <div>
          <label>Password</label>
          <input class="cms-input" type="password" name="password" required>
        </div>
        <div style="grid-column:1/-1;text-align:right">
          <button type="submit" class="cms-btn">Tambah Admin</button>
        </div>
      </form>
    </section>
  </main>
</body>
</html>
