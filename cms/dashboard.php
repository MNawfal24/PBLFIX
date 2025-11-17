<?php
require_once __DIR__.'/helpers.php';
require_admin();
$data = load_content();
$csrf = csrf_token();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>CMS Dashboard – Business Analytics Lab</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body{font-family:Inter,system-ui,sans-serif;background:linear-gradient(135deg,#EAF4FF,#FFFFFF 40%,#D6EBFF);color:#0A2540;margin:0}
    header{position:sticky;top:0;backdrop-filter:blur(8px);background:rgba(255,255,255,.7);border-bottom:1px solid #D6EBFF}
    .wrap{max-width:1000px;margin:0 auto;padding:16px}
    .row{display:flex;align-items:center;justify-content:space-between;gap:12px}
    .btn{background:linear-gradient(135deg,#54A9FF,#114B8C);color:#fff;border:none;border-radius:10px;padding:10px 14px;cursor:pointer}
    .btn-outline{background:#fff;color:#0B3A6F;border:1px solid #D6EBFF;border-radius:10px;padding:10px 14px;cursor:pointer}
    .note{font-size:12px;color:#0A2540CC}
    .card{background:#fff;border:1px solid #D6EBFF;border-radius:14px;box-shadow:0 18px 40px rgba(16,76,140,.08);padding:16px}
    table{width:100%;border-collapse:collapse}
    th,td{border:1px solid #E6F0FF;padding:8px}
    th{background:#F6FAFF;text-align:left}
    .grid{display:grid;gap:12px}
    .two{grid-template-columns:1fr 1fr}
    .three{grid-template-columns:1fr 1fr 1fr}
    input,select,textarea{width:100%;padding:10px;border:1px solid #D6EBFF;border-radius:10px}
    .tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
    .tab{padding:8px 12px;border:1px solid #D6EBFF;border-radius:999px;background:#fff;color:#0B3A6F;text-decoration:none}
    .active{background:linear-gradient(135deg,#54A9FF,#114B8C);color:#fff}
  </style>
</head>
<body>
  <header>
    <div class="wrap row">
      <div class="row" style="gap:10px">
        <img src="../assets/img/logo.png" alt="" style="height:34px;border-radius:8px" onerror="this.style.display='none'">
        <strong>Business Analytics Lab – CMS Dashboard</strong>
      </div>
      <div class="row" style="gap:8px">
        <a class="btn-outline" href="../">View Site</a>
        <form method="post" action="logout.php"><button class="btn-outline" type="submit">Logout</button></form>
      </div>
    </div>
  </header>
  <main class="wrap" style="margin-top:16px">
    <div class="tabs">
      <a class="tab active" href="#vision">Vision</a>
      <a class="tab" href="#branding">Branding</a>
      <a class="tab" href="#what">What We Do</a>
      <a class="tab" href="#team">Team</a>
      <a class="tab" href="#facilities">Facilities</a>
      <a class="tab" href="#research">Research</a>
      <a class="tab" href="#activities">Activities</a>
      <a class="tab" href="#publications">Publications</a>
      <a class="tab" href="#gallery">Gallery</a>
      <a class="tab" href="#bookingNotice">Booking Notice</a>
    </div>

    <!-- Vision -->
    <section id="vision" class="card">
      <h3>Vision</h3>
      <form method="post" action="action.php" class="grid">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="section" value="vision">
        <textarea name="vision" rows="3"><?php echo htmlspecialchars($data['vision'] ?? ''); ?></textarea>
        <div class="row" style="justify-content:flex-end"><button class="btn">Save</button></div>
      </form>
    </section>

    <!-- Branding -->
    <section id="branding" class="card" style="margin-top:16px">
      <h3>Branding</h3>
      <form method="post" action="action.php" enctype="multipart/form-data" class="grid two">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="section" value="branding">
        <div>
          <label>Nama Situs / Lab</label>
          <input name="siteName" value="<?php echo htmlspecialchars($data['siteName'] ?? ''); ?>" placeholder="Business Analytics Lab">
        </div>
        <div>
          <label>Logo (opsional, unggah untuk mengganti)</label>
          <input type="file" name="logo" accept="image/*">
        </div>
        <div style="grid-column:1/-1;text-align:right"><button class="btn">Save</button></div>
      </form>
      <div class="row" style="margin-top:10px;gap:12px;align-items:center">
        <strong>Pratinjau Logo Saat Ini:</strong>
        <?php if(!empty($data['siteLogo'])): ?>
          <img src="<?php echo htmlspecialchars('../'.$data['siteLogo']); ?>" alt="Logo" style="height:40px;border-radius:8px">
        <?php else: ?>
          <span class="note">Belum ada logo diunggah.</span>
        <?php endif; ?>
      </div>
    </section>

    <!-- Booking Notice -->
    <section id="bookingNotice" class="card" style="margin-top:16px;margin-bottom:24px">
      <h3>Booking Notice</h3>
      <p class="note">Pesan ini akan tampil di atas jadwal pada halaman peminjaman. Contoh: "Jadwal yang kosong pada minggu ke 1".</p>
      <form method="post" action="action.php" class="grid">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="section" value="bookingNotice">
        <textarea name="bookingNotice" rows="2" placeholder="Tulis pengumuman singkat..."><?php echo htmlspecialchars($data['bookingNotice'] ?? ''); ?></textarea>
        <div class="row" style="justify-content:flex-end"><button class="btn">Save</button></div>
      </form>
      <div class="row" style="margin-top:10px; gap:8px; align-items:center;">
        <a class="btn-outline" href="../booking.php?ts=<?php echo time(); ?>">Lihat Jadwal</a>
        <a class="btn-outline" href="../booking.php?ts=<?php echo time(); ?>">Refresh Jadwal</a>
        <form method="post" action="../booking.php" onsubmit="return confirm('Reset semua jadwal? Tindakan ini tidak dapat dibatalkan.');">
          <input type="hidden" name="reset" value="1">
          <button class="btn-outline" type="submit" style="background:#ffecec;border-color:#ffb3b3;color:#7a0000">Reset Jadwal</button>
        </form>
      </div>
    </section>

    <!-- What We Do -->
    <section id="what" class="card" style="margin-top:16px">
      <h3>What We Do</h3>
      <table>
        <tr><th>Icon</th><th>Title</th><th>Description</th><th>Actions</th></tr>
        <?php foreach($data['whatWeDo'] as $i=>$w): ?>
          <tr>
            <td><?php echo htmlspecialchars($w['icon']??''); ?></td>
            <td><?php echo htmlspecialchars($w['title']??''); ?></td>
            <td><?php echo htmlspecialchars($w['desc']??''); ?></td>
            <td>
              <form style="display:inline" method="post" action="action.php">
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                <input type="hidden" name="section" value="whatWeDo">
                <input type="hidden" name="op" value="delete">
                <input type="hidden" name="index" value="<?php echo $i; ?>">
                <button class="btn-outline" onclick="return confirm('Delete this item?')">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
      <form method="post" action="action.php" class="grid three" style="margin-top:10px">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="section" value="whatWeDo">
        <input type="hidden" name="op" value="create">
        <input name="icon" placeholder="Icon (e.g. 📊)">
        <input name="title" placeholder="Title">
        <input name="desc" placeholder="Description">
        <div style="grid-column:1/-1;text-align:right"><button class="btn">Add</button></div>
      </form>
    </section>

    <!-- Team -->
    <section id="team" class="card" style="margin-top:16px">
      <h3>Team</h3>
      <table>
        <tr><th>Photo</th><th>Name</th><th>Role</th><th>Actions</th></tr>
        <?php foreach($data['team'] as $i=>$t): ?>
          <tr>
            <td><?php if(!empty($t['photo'])): ?><img src="<?php echo htmlspecialchars($t['photo']); ?>" style="height:40px"><?php endif; ?></td>
            <td><?php echo htmlspecialchars($t['name']??''); ?></td>
            <td><?php echo htmlspecialchars($t['role']??''); ?></td>
            <td>
              <form style="display:inline" method="post" action="action.php">
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                <input type="hidden" name="section" value="team">
                <input type="hidden" name="op" value="delete">
                <input type="hidden" name="index" value="<?php echo $i; ?>">
                <button class="btn-outline" onclick="return confirm('Delete member?')">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
      <form method="post" action="action.php" enctype="multipart/form-data" class="grid three" style="margin-top:10px">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="section" value="team">
        <input type="hidden" name="op" value="create">
        <input name="name" placeholder="Name">
        <input name="role" placeholder="Role">
        <input type="file" name="photo" accept="image/*">
        <div style="grid-column:1/-1;text-align:right"><button class="btn">Add Member</button></div>
      </form>
    </section>

    <!-- Facilities -->
    <section id="facilities" class="card" style="margin-top:16px">
      <h3>Facilities</h3>
      <table>
        <tr><th>Item</th><th>Actions</th></tr>
        <?php foreach($data['facilities'] as $i=>$f): ?>
          <tr>
            <td><?php echo htmlspecialchars($f); ?></td>
            <td>
              <form style="display:inline" method="post" action="action.php">
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                <input type="hidden" name="section" value="facilities">
                <input type="hidden" name="op" value="delete">
                <input type="hidden" name="index" value="<?php echo $i; ?>">
                <button class="btn-outline" onclick="return confirm('Delete facility?')">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
      <form method="post" action="action.php" class="grid two" style="margin-top:10px">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="section" value="facilities">
        <input type="hidden" name="op" value="create">
        <input name="text" placeholder="Facility text">
        <div style="text-align:right"><button class="btn">Add</button></div>
      </form>
    </section>

    <!-- Research -->
    <section id="research" class="card" style="margin-top:16px">
      <h3>Research</h3>
      <table>
        <tr><th>Title</th><th>Description</th><th>Actions</th></tr>
        <?php foreach($data['research'] as $i=>$r): ?>
          <tr>
            <td><?php echo htmlspecialchars($r['title']??''); ?></td>
            <td><?php echo htmlspecialchars($r['desc']??''); ?></td>
            <td>
              <form style="display:inline" method="post" action="action.php">
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                <input type="hidden" name="section" value="research">
                <input type="hidden" name="op" value="delete">
                <input type="hidden" name="index" value="<?php echo $i; ?>">
                <button class="btn-outline" onclick="return confirm('Delete research?')">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
      <form method="post" action="action.php" class="grid three" style="margin-top:10px">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="section" value="research">
        <input type="hidden" name="op" value="create">
        <input name="title" placeholder="Title">
        <input name="desc" placeholder="Description">
        <div style="grid-column:1/-1;text-align:right"><button class="btn">Add</button></div>
      </form>
    </section>

    <!-- Activities -->
    <section id="activities" class="card" style="margin-top:16px">
      <h3>Activities</h3>
      <table>
        <tr><th>Title</th><th>Description</th><th>Actions</th></tr>
        <?php foreach($data['activities'] as $i=>$a): ?>
          <tr>
            <td><?php echo htmlspecialchars($a['title']??''); ?></td>
            <td><?php echo htmlspecialchars($a['desc']??''); ?></td>
            <td>
              <form style="display:inline" method="post" action="action.php">
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                <input type="hidden" name="section" value="activities">
                <input type="hidden" name="op" value="delete">
                <input type="hidden" name="index" value="<?php echo $i; ?>">
                <button class="btn-outline" onclick="return confirm('Delete activity?')">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
      <form method="post" action="action.php" class="grid three" style="margin-top:10px">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="section" value="activities">
        <input type="hidden" name="op" value="create">
        <input name="title" placeholder="Title">
        <input name="desc" placeholder="Description">
        <div style="grid-column:1/-1;text-align:right"><button class="btn">Add</button></div>
      </form>
    </section>

    <!-- Publications -->
    <section id="publications" class="card" style="margin-top:16px">
      <h3>Publications</h3>
      <table>
        <tr><th>Year</th><th>Text</th><th>Actions</th></tr>
        <?php foreach($data['publications'] as $i=>$p): ?>
          <tr>
            <td><?php echo htmlspecialchars($p['year']??''); ?></td>
            <td><?php echo htmlspecialchars($p['text']??''); ?></td>
            <td>
              <form style="display:inline" method="post" action="action.php">
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                <input type="hidden" name="section" value="publications">
                <input type="hidden" name="op" value="delete">
                <input type="hidden" name="index" value="<?php echo $i; ?>">
                <button class="btn-outline" onclick="return confirm('Delete publication?')">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
      <form method="post" action="action.php" class="grid three" style="margin-top:10px">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="section" value="publications">
        <input type="hidden" name="op" value="create">
        <input name="year" placeholder="Year">
        <input name="text" placeholder="Text">
        <div style="grid-column:1/-1;text-align:right"><button class="btn">Add</button></div>
      </form>
    </section>

    <!-- Gallery -->
    <section id="gallery" class="card" style="margin-top:16px;margin-bottom:24px">
      <h3>Gallery</h3>
      <table>
        <tr><th>Image</th><th>URL</th><th>Caption</th><th>Actions</th></tr>
        <?php foreach($data['gallery'] as $i=>$g): $url = is_array($g)?($g['src']??''):$g; $cap = is_array($g)?($g['caption']??''):''; $preview = $url; if (is_string($preview) && strpos($preview,'uploads/')===0) { $preview = '../'.$preview; } ?>
          <tr>
            <td><?php if($url): ?><img src="<?php echo htmlspecialchars($preview); ?>" style="height:40px"><?php endif; ?></td>
            <td><?php echo htmlspecialchars($url); ?></td>
            <td><?php echo htmlspecialchars($cap); ?></td>
            <td>
              <form style="display:inline" method="post" action="action.php">
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                <input type="hidden" name="section" value="gallery">
                <input type="hidden" name="op" value="delete">
                <input type="hidden" name="index" value="<?php echo $i; ?>">
                <button class="btn-outline" onclick="return confirm('Delete photo?')">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
      <form method="post" action="save.php" enctype="multipart/form-data" class="grid two" style="margin-top:10px">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="section" value="gallery">
        <input type="hidden" name="op" value="create">
        <input name="url" placeholder="Or paste image URL (optional)">
        <input type="file" name="image" accept="image/*">
        <input name="caption" placeholder="Caption / Deskripsi (opsional)">
        <div style="grid-column:1/-1;text-align:right"><button class="btn">Add Photo</button></div>
      </form>
    </section>
    
  </main>
  <script>
    // simple active tab styling
    const tabs = document.querySelectorAll('.tabs .tab');
    function sync(){
      const hash = location.hash || '#vision';
      tabs.forEach(t=>t.classList.toggle('active', t.getAttribute('href')===hash));
    }
    window.addEventListener('hashchange', sync); sync();
  </script>
</body>
</html>
