<?php
require_once __DIR__.'/helpers.php';
require_admin();

$csrf = csrf_token();
$bidang = load_bidang();

// Handle create / update / delete
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  check_csrf($_POST['csrf'] ?? '');
  $op   = $_POST['op'] ?? '';
  $id   = isset($_POST['id']) ? (int)$_POST['id'] : 0;
  $nama = trim($_POST['nama'] ?? '');

  if ($op === 'create' && $nama !== ''){
    $maxId = 0;
    foreach($bidang as $b){ if (($b['id'] ?? 0) > $maxId) $maxId = (int)$b['id']; }
    $bidang[] = ['id' => $maxId + 1, 'nama' => $nama];
    save_bidang($bidang);
    header('Location: kelola_bidang.php'); exit;
  }

  if ($op === 'update' && $id > 0 && $nama !== ''){
    foreach($bidang as &$b){
      if ((int)$b['id'] === $id){ $b['nama'] = $nama; break; }
    }
    unset($b);
    save_bidang($bidang);
    header('Location: kelola_bidang.php'); exit;
  }

  if ($op === 'delete' && $id > 0){
    $bidang = array_values(array_filter($bidang, function($b) use ($id){ return (int)($b['id'] ?? 0) !== $id; }));
    save_bidang($bidang);
    header('Location: kelola_bidang.php'); exit;
  }
}

?><!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Kelola Bidang – CMS Business Analytics Lab</title>
  <link rel="stylesheet" href="../assets/styles.css">
</head>
<body class="cms-body">
  <header class="cms-header">
    <div class="cms-wrap cms-row">
      <div class="cms-row" style="gap:10px">
        <img src="../assets/img/logo.png" alt="" style="height:34px;border-radius:8px" onerror="this.style.display='none'">
        <strong>Kelola Bidang – Business Analytics Lab</strong>
      </div>
      <div class="cms-row" style="gap:8px">
        <a class="cms-btn-outline" href="dashboard.php">Kembali ke Dashboard</a>
        <form method="post" action="logout.php"><button class="cms-btn-outline" type="submit">Logout</button></form>
      </div>
    </div>
  </header>

  <main class="cms-wrap" style="margin-top:16px">
    <section class="cms-card">
      <h3>Daftar Bidang</h3>
      <table class="cms-table">
        <tr><th>ID</th><th>Nama Bidang</th><th>Aksi</th></tr>
        <?php if (empty($bidang)): ?>
          <tr><td colspan="3"><span class="cms-note">Belum ada bidang. Tambahkan di bawah ini.</span></td></tr>
        <?php else: ?>
          <?php foreach($bidang as $b): ?>
            <tr>
              <td><?php echo (int)($b['id'] ?? 0); ?></td>
              <td><?php echo htmlspecialchars($b['nama'] ?? ''); ?></td>
              <td>
                <!-- Form edit sederhana (inline) -->
                <form method="post" action="kelola_bidang.php" style="display:inline-block;margin-right:4px;">
                  <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                  <input type="hidden" name="op" value="update">
                  <input type="hidden" name="id" value="<?php echo (int)($b['id'] ?? 0); ?>">
                  <input class="cms-input" style="width:160px" name="nama" value="<?php echo htmlspecialchars($b['nama'] ?? ''); ?>">
                  <button class="cms-btn" type="submit">Simpan</button>
                </form>
                
                <!-- Hapus -->
                <form method="post" action="kelola_bidang.php" style="display:inline-block" onsubmit="return confirm('Hapus bidang ini? Data dosen/publikasi yang memakai bidang ini tidak akan dihapus, tetapi akan tampil sebagai \"Bidang sudah dihapus\".');">
                  <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                  <input type="hidden" name="op" value="delete">
                  <input type="hidden" name="id" value="<?php echo (int)($b['id'] ?? 0); ?>">
                  <button class="cms-btn-outline" type="submit">Hapus</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </table>
    </section>

    <section class="cms-card" style="margin-top:16px">
      <h3>Tambah Bidang Baru</h3>
      <form method="post" action="kelola_bidang.php" class="cms-grid cms-grid-two">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="op" value="create">
        <div>
          <label>Nama Bidang</label>
          <input class="cms-input" name="nama" placeholder="Contoh: AI, Jaringan, IoT" required>
        </div>
        <div style="grid-column:1/-1;text-align:right">
          <button class="cms-btn" type="submit">Tambah</button>
        </div>
      </form>
    </section>
  </main>
</body>
</html>
