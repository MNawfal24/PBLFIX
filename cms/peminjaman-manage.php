<?php
require_once __DIR__.'/helpers.php';
require_admin();

$DATA_DIR = __DIR__ . '/../data';
$BOOK_FILE = $DATA_DIR . '/bookings.json';

function cms_load_bookings($file){
  if (!file_exists($file)) return [];
  $json = file_get_contents($file);
  $data = json_decode($json, true);
  return is_array($data) ? $data : [];
}

function cms_save_bookings($file, $data){
  $dir = dirname($file);
  if (!is_dir($dir)) mkdir($dir, 0775, true);
  $fp = fopen($file, 'c+');
  if ($fp){
    flock($fp, LOCK_EX);
    ftruncate($fp, 0);
    fwrite($fp, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return true;
  }
  return false;
}

function send_booking_email($to, $subject, $html){
  if (empty($to)) return false;
  $headers  = "MIME-Version: 1.0\r\n";
  $headers .= "Content-type: text/html; charset=UTF-8\r\n";
  $headers .= "From: Business Analytics Lab <no-reply@localhost>\r\n";
  return mail($to, $subject, $html, $headers);
}

$csrf = csrf_token();
$bookings = cms_load_bookings($BOOK_FILE);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  $action = $_POST['action'] ?? '';
  $date   = $_POST['date'] ?? '';
  $start  = $_POST['start'] ?? '';
  $end    = $_POST['end'] ?? '';
  $ts     = isset($_POST['ts']) ? (int)$_POST['ts'] : 0;
  $csrf_p = $_POST['csrf'] ?? '';

  if (!hash_equals($csrf, $csrf_p)){
    $message = 'Token sesi tidak valid.';
  } elseif ($date && $start && $end && $ts && isset($bookings[$date])){
    $slots   = array_keys($bookings[$date]);
    $notified = false;

    foreach ($slots as $slot){
      $entry = $bookings[$date][$slot] ?? null;
      if (!is_array($entry)) continue;
      $eTs   = (int)($entry['ts'] ?? 0);
      $range = $entry['range'] ?? [];
      $eStart = $range[0] ?? '';
      $eEnd   = $range[1] ?? '';

      if ($eTs === $ts && $eStart === $start && $eEnd === $end){
        $email  = $entry['email'] ?? '';
        $name   = $entry['name'] ?? '';
        $reason = $entry['reason'] ?? ($entry['keperluan'] ?? '');
        $waktu  = $eStart . ' - ' . $eEnd;

        if (!$notified && $email !== ''){
          if ($action === 'approve'){
            $subject = 'Peminjaman Laboratorium Diterima';
            $body  = '<p>Yth. '.htmlspecialchars($name).',</p>';
            $body .= '<p>Permintaan peminjaman laboratorium Anda <strong>disetujui</strong>.</p>';
            $body .= '<p><strong>Tanggal:</strong> '.htmlspecialchars($date).'<br>';
            $body .= '<strong>Waktu:</strong> '.htmlspecialchars($waktu).'<br>';
            if ($reason !== ''){
              $body .= '<strong>Keperluan:</strong> '.htmlspecialchars($reason).'<br>';
            }
            $body .= '</p>';
            $body .= '<p>Silakan datang tepat waktu dan mengikuti tata tertib laboratorium.</p>';
            $body .= '<p>Hormat kami,<br>Business Analytics Lab</p>';
            send_booking_email($email, $subject, $body);
          } elseif ($action === 'reject'){
            $subject = 'Peminjaman Laboratorium Ditolak';
            $body  = '<p>Yth. '.htmlspecialchars($name).',</p>';
            $body .= '<p>Terima kasih atas permintaan peminjaman laboratorium Anda, namun saat ini <strong>belum dapat kami penuhi</strong>.</p>';
            $body .= '<p><strong>Tanggal yang diajukan:</strong> '.htmlspecialchars($date).'<br>';
            $body .= '<strong>Waktu:</strong> '.htmlspecialchars($waktu).'</p>';
            $body .= '<p>Silakan mengajukan kembali pada jadwal lain yang tersedia.</p>';
            $body .= '<p>Hormat kami,<br>Business Analytics Lab</p>';
            send_booking_email($email, $subject, $body);
          }
          $notified = true;
        }

        if ($action === 'approve'){
          $entry['status'] = 'approved';
          $bookings[$date][$slot] = $entry;
        } elseif ($action === 'reject'){
          unset($bookings[$date][$slot]);
        }
      }
    }

    if (isset($bookings[$date]) && empty($bookings[$date])){
      unset($bookings[$date]);
    }
    cms_save_bookings($BOOK_FILE, $bookings);
    header('Location: peminjaman-manage.php');
    exit;
  }
}

// Bangun list request unik (berdasarkan tanggal + ts + start)
$requests = [];
foreach ($bookings as $date => $slots){
  if (!is_array($slots)) continue;
  foreach ($slots as $time => $entry){
    if (!is_array($entry)) continue;
    $ts = (int)($entry['ts'] ?? 0);
    $range = $entry['range'] ?? [];
    $start = $range[0] ?? $time;
    $end   = $range[1] ?? $time;
    $key = $date.'|'.$ts.'|'.$start;
    if (!isset($requests[$key])){
      $status = $entry['status'] ?? 'pending';
      $requests[$key] = [
        'date'   => $date,
        'ts'     => $ts,
        'name'   => $entry['name'] ?? '',
        'id'     => $entry['id'] ?? '',
        'email'  => $entry['email'] ?? '',
        'start'  => $start,
        'end'    => $end,
        'reason' => $entry['reason'] ?? ($entry['keperluan'] ?? ''),
        'status' => $status,
      ];
    }
  }
}

// Ambil hanya yang pending
$pending = array_values(array_filter($requests, function($r){
  return ($r['status'] ?? 'pending') === 'pending';
}));

?><!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Kelola Peminjaman Lab – CMS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/styles.css">
</head>
<body class="cms-body">
  <header class="cms-header">
    <div class="cms-wrap cms-row">
      <div class="cms-row" style="gap:10px">
        <img src="../assets/img/logo.png" alt="" style="height:34px;border-radius:8px" onerror="this.style.display='none'">
        <strong>Business Analytics Lab – Kelola Peminjaman</strong>
      </div>
      <div class="cms-row" style="gap:8px">
        <a class="cms-btn-outline" href="dashboard.php">Kembali ke Dashboard</a>
        <a class="cms-btn-outline" href="../booking.php">Lihat Jadwal Publik</a>
        <form method="post" action="logout.php"><button class="cms-btn-outline" type="submit">Logout</button></form>
      </div>
    </div>
  </header>

  <main class="cms-wrap" style="margin-top:16px">
    <section class="cms-card">
      <h3>Daftar Peminjaman Pending</h3>
      <p class="cms-note">Permintaan dengan status <strong>pending</strong> dapat Anda terima atau tolak. Jadwal yang sudah <strong>approved</strong> akan muncul di halaman jadwal publik.</p>

      <?php if($message): ?>
        <div class="booking-alert booking-alert-err"><?php echo htmlspecialchars($message); ?></div>
      <?php endif; ?>

      <?php if (empty($pending)): ?>
        <p class="cms-note">Belum ada permintaan peminjaman dengan status pending.</p>
      <?php else: ?>
        <div class="cms-table-wrap">
          <table class="cms-table">
            <tr>
              <th>Nama</th>
              <th>Email</th>
              <th>Tanggal</th>
              <th>Waktu</th>
              <th>Keperluan</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
            <?php foreach($pending as $req): ?>
              <tr>
                <td><?php echo htmlspecialchars($req['name'].' ('.$req['id'].')'); ?></td>
                <td><?php echo htmlspecialchars($req['email'] ?: '-'); ?></td>
                <td><?php echo htmlspecialchars($req['date']); ?></td>
                <td><?php echo htmlspecialchars($req['start'].' - '.$req['end']); ?></td>
                <td><?php echo htmlspecialchars($req['reason'] ?: '-'); ?></td>
                <td><?php echo htmlspecialchars($req['status']); ?></td>
                <td>
                  <form method="post" action="peminjaman-manage.php" style="display:inline">
                    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                    <input type="hidden" name="date" value="<?php echo htmlspecialchars($req['date']); ?>">
                    <input type="hidden" name="start" value="<?php echo htmlspecialchars($req['start']); ?>">
                    <input type="hidden" name="end" value="<?php echo htmlspecialchars($req['end']); ?>">
                    <input type="hidden" name="ts" value="<?php echo (int)$req['ts']; ?>">
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" class="btn-approve">Terima</button>
                  </form>
                  <form method="post" action="peminjaman-manage.php" style="display:inline" onsubmit="return confirm('Tolak permintaan ini?');">
                    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                    <input type="hidden" name="date" value="<?php echo htmlspecialchars($req['date']); ?>">
                    <input type="hidden" name="start" value="<?php echo htmlspecialchars($req['start']); ?>">
                    <input type="hidden" name="end" value="<?php echo htmlspecialchars($req['end']); ?>">
                    <input type="hidden" name="ts" value="<?php echo (int)$req['ts']; ?>">
                    <input type="hidden" name="action" value="reject">
                    <button type="submit" class="btn-reject">Tolak</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </table>
        </div>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
