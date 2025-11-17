<?php
// booking.php - Simple lab booking and schedule view (Mon-Fri, next 2 weeks)
// Storage: data/bookings.json (no DB required)

// Config
$DATA_DIR = __DIR__ . '/data';
$BOOK_FILE = $DATA_DIR . '/bookings.json';
$SLOTS = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];
date_default_timezone_set('Asia/Jakarta');

function load_bookings($file)
{
    if (!file_exists($file)) return [];
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function save_bookings($file, $data)
{
    $dir = dirname($file);
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $fp = fopen($file, 'c+');
    if ($fp) {
        flock($fp, LOCK_EX);
        ftruncate($fp, 0);
        fwrite($fp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }
    return false;
}

function ymd($ts)
{
    return date('Y-m-d', $ts);
}
function day_name_id($ts){
    $names = [1=>'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
    return $names[(int)date('N',$ts)] ?? date('l',$ts);
}
function date_id($ts){
    $months = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $m = (int)date('n',$ts);
    return date('d',$ts).' '.$months[$m].' '.date('Y',$ts);
}
function is_weekday($ts)
{
    $w = date('N', $ts);
    return $w >= 1 && $w <= 5;
}

$bookings = load_bookings($BOOK_FILE);
$status_msg = '';
$status_ok = null;

// Load booking notice from CMS content
$content_file = __DIR__.'/data/content.json';
$booking_notice = '';
if (file_exists($content_file)){
    $c = json_decode(file_get_contents($content_file), true);
    if (is_array($c) && isset($c['bookingNotice'])) $booking_notice = trim($c['bookingNotice']);
}

// Handle reset request (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset']) ) {
    if (save_bookings($BOOK_FILE, [])) {
        // Redirect (PRG pattern) agar tampilan segar dan mencegah re-submit
        header('Location: booking.php?status=reset_ok');
        exit;
    } else {
        header('Location: booking.php?status=reset_err');
        exit;
    }
}

// Status via query (setelah redirect PRG)
if (isset($_GET['status'])){
    if ($_GET['status'] === 'reset_ok'){ $status_msg = 'Semua jadwal berhasil di-reset.'; $status_ok = true; }
    elseif ($_GET['status'] === 'reset_err'){ $status_msg = 'Gagal mereset jadwal.'; $status_ok = false; }
}

// Handle incoming booking (GET from index form)
$name = trim($_GET['name'] ?? '');
$id   = trim($_GET['id'] ?? '');
$date = trim($_GET['date'] ?? '');
$start = trim($_GET['start'] ?? '');
$end   = trim($_GET['end'] ?? '');

if ($name !== '' && $id !== '' && $date !== '' && $start !== '' && $end !== '') {
    // Normalize date/time
    $dt_valid = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date);
    $st_valid = in_array($start, $SLOTS, true);
    $en_valid = in_array($end, $SLOTS, true);
    if (!$dt_valid || !$st_valid || !$en_valid) {
        $status_msg = 'Input tanggal atau rentang jam tidak valid.';
        $status_ok = false;
    } else {
        $iStart = array_search($start, $SLOTS, true);
        $iEnd   = array_search($end, $SLOTS, true);
        if ($iStart === false || $iEnd === false || $iEnd <= $iStart) {
            $status_msg = 'Jam selesai harus lebih besar dari jam mulai.';
            $status_ok = false;
        } else {
            $key = $date;
            if (!isset($bookings[$key])) $bookings[$key] = [];
            // Range is [iStart, iEnd) exclusive of end
            $conflict = false;
            for ($i = $iStart; $i < $iEnd; $i++) {
                $t = $SLOTS[$i];
                if (isset($bookings[$key][$t])) {
                    $conflict = true;
                    break;
                }
            }
            if ($conflict) {
                $status_msg = 'Sebagian/semua slot pada rentang waktu tersebut sudah terisi.';
                $status_ok = false;
            } else {
                $entry = ['name' => $name, 'id' => $id, 'ts' => time(), 'span' => $iEnd - $iStart, 'range' => [$start, $end]];
                for ($i = $iStart; $i < $iEnd; $i++) {
                    $bookings[$key][$SLOTS[$i]] = $entry;
                }
                save_bookings($BOOK_FILE, $bookings);
                $status_msg = 'Booking berhasil disimpan untuk rentang ' . $start . '–' . $end . '.';
                $status_ok = true;
            }
        }
    }
}

// Build 2-week window (10 weekdays) anchored to the selected date's week if provided,
// otherwise start from today. This ensures the user sees the day they just booked.
$today = strtotime('today');
$base = $today;
if ($date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $sel = strtotime($date);
    // Start from Monday of the selected week
    $base = strtotime('monday this week', $sel);
}
$days = [];
for ($i = 0, $count = 0; $count < 10; $i++) {
    $ts = strtotime("+$i day", $base);
    if (is_weekday($ts)) {
        $days[] = $ts;
        $count++;
    }
}

?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Jadwal Peminjaman Lab</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles.css">
    <style>
        body {
            font-family: Inter, system-ui, sans-serif;
        }

        .wrap {
            max-width: 1100px;
            margin: 0 auto;
            padding: 16px
        }

        .row {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: space-between
        }

        .note {
            font-size: 12px;
            color: #0A2540CC
        }

        .head {
            background: rgba(0, 0, 128, .9);
            color: #EAF0FF;
            padding: 16px;
            border-radius: 14px;
            margin: 16px 0
        }

        .grid {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(5, 1fr)
        }

        .col {
            background: #fff;
            border: 1px solid var(--sky-100);
            border-radius: 12px;
            overflow: hidden
        }

        .col h4 {
            margin: 0;
            padding: 10px 12px;
            background: #F6FAFF;
            border-bottom: 1px solid var(--sky-100)
        }
        .col .date-sub {
            margin: 0;
            padding: 6px 12px 8px;
            background: #F6FAFF;
            border-bottom: 1px solid var(--sky-100);
            font-size: 12px;
            color: #0A2540B0;
            text-align: center;
        }

        .slot {
            display: block;
            padding: 8px 10px;
            margin: 8px;
            border-radius: 10px;
            border: 1px solid #E6F0FF;
            text-align: center
        }

        .slot.free {
            background: #ffffff
        }

        .slot.busy {
            background: #ffd6d6;
            border-color: #ffb3b3;
            color: #7a0000
        }

        .legend {
            display: flex;
            gap: 10px;
            align-items: center;
            font-size: 13px
        }

        .legend span {
            display: inline-block;
            width: 14px;
            height: 14px;
            border-radius: 4px;
            border: 1px solid #E6F0FF
        }

        .btn-back {
            background: #fff;
            color: #0B3A6F;
            border: 1px solid #D6EBFF;
            border-radius: 10px;
            padding: 8px 12px;
            text-decoration: none
        }

        .alert {
            padding: 10px 12px;
            border-radius: 10px;
            margin-top: 10px
        }

        .ok {
            background: #e6ffed;
            border: 1px solid #b7f5c5;
            color: #0a5f1a
        }

        .err {
            background: #ffeaea;
            border: 1px solid #ffc7c7;
            color: #7a0000
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="row">
            <h2>Jadwal Peminjaman Lab</h2>
            <div></div>
        </div>
        <?php if ($booking_notice !== ''): ?>
            <div class="alert ok" style="margin-bottom:10px;background:#f4f9ff;border:1px solid #d6ebff;color:#0B3A6F;">
                <?php echo htmlspecialchars($booking_notice); ?>
            </div>
        <?php endif; ?>
        <p class="note">Menampilkan 2 minggu (hari kerja Senin–Jumat). Slot terisi ditandai merah.</p>

        <?php if ($status_ok !== null): ?>
            <div class="alert <?php echo $status_ok ? 'ok' : 'err'; ?>"><?php echo htmlspecialchars($status_msg); ?></div>
        <?php endif; ?>

        <div class="head">
            <div class="legend">
                <span style="background:#ffffff"></span> Tersedia
                <span style="background:#ffd6d6;border-color:#ffb3b3"></span> Terisi
            </div>
        </div>

        <div class="grid">
            <?php foreach ($days as $ts): $d = ymd($ts); ?>
                <div class="col">
                    <h4><?php echo day_name_id($ts); ?></h4>
                    <div class="date-sub"><?php echo date_id($ts); ?></div>
                    <div style="padding:6px">
                        <?php foreach ($SLOTS as $slot): $busy = isset($bookings[$d][$slot]); ?>
                            <div class="slot <?php echo $busy ? 'busy' : 'free'; ?>">
                                <?php echo htmlspecialchars($slot); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>