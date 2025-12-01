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
  <link rel="stylesheet" href="../assets/styles.css">
</head>
<body class="cms-body">
  <header class="cms-header">
    <div class="cms-wrap cms-row">
      <div class="cms-row" style="gap:10px">
        <img src="../assets/img/logo.png" alt="" style="height:34px;border-radius:8px" onerror="this.style.display='none'">
        <strong>Business Analytics Lab – CMS Dashboard</strong>
      </div>
      <div class="cms-row" style="gap:8px">
        <a class="cms-btn-outline" href="../">View Site</a>
        <a class="cms-btn-outline" href="peminjaman-manage.php">Kelola Peminjaman</a>
        <a class="cms-btn-outline" href="kelola_bidang.php">Kelola Bidang</a>
        <a class="cms-btn-outline" href="admin-manage.php">Kelola Admin</a>
        <form method="post" action="logout.php"><button class="cms-btn-outline" type="submit">Logout</button></form>
      </div>
    </div>
  </header>
  <main class="cms-wrap" style="margin-top:16px">
    <div class="cms-tabs">
      <a class="cms-tab cms-tab-active" href="#vision">Vision</a>
      <a class="cms-tab" href="#branding">Branding</a>
      <a class="cms-tab" href="#what">What We Do</a>
      <a class="cms-tab" href="#team">Team</a>
      <a class="cms-tab" href="#facilities">Facilities</a>
      <a class="cms-tab" href="#research">Research</a>
      <a class="cms-tab" href="#news">News</a>
      <a class="cms-tab" href="#activities">Activities</a>
      <a class="cms-tab" href="#publications">Publications</a>
      <a class="cms-tab" href="#gallery">Gallery</a>
      <a class="cms-tab" href="#bookingNotice">Booking Notice</a>
    </div>

    <!-- Vision -->
    <section id="vision" class="cms-card">
      <h3>Vision</h3>
      <form method="post" action="action.php" class="cms-grid">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="section" value="vision">
        <textarea name="vision" rows="3" class="cms-textarea"><?php echo htmlspecialchars($data['vision'] ?? ''); ?></textarea>
        <div class="cms-row" style="justify-content:flex-end"><button class="cms-btn">Save</button></div>
      </form>
    </section>

    <!-- Branding -->
    <section id="branding" class="cms-card" style="margin-top:16px">
      <h3>Branding</h3>
      <form method="post" action="action.php" enctype="multipart/form-data" class="cms-grid cms-grid-two">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="section" value="branding">
        <div>
          <label>Nama Situs / Lab</label>
          <input class="cms-input" name="siteName" value="<?php echo htmlspecialchars($data['siteName'] ?? ''); ?>" placeholder="Business Analytics Lab">
        </div>
        <div>
          <label>Logo (opsional, unggah untuk mengganti)</label>
          <input class="cms-input" type="file" name="logo" accept="image/*">
        </div>
        <div style="grid-column:1/-1;text-align:right"><button class="cms-btn">Save</button></div>
      </form>
      <div class="cms-row" style="margin-top:10px;gap:12px;align-items:center">
        <strong>Pratinjau Logo Saat Ini:</strong>
        <?php if(!empty($data['siteLogo'])): ?>
          <img src="<?php echo htmlspecialchars('../'.$data['siteLogo']); ?>" alt="Logo" style="height:40px;border-radius:8px">
        <?php else: ?>
          <span class="cms-note">Belum ada logo diunggah.</span>
        <?php endif; ?>
      </div>
    </section>

    <!-- Booking Notice -->
    <section id="bookingNotice" class="cms-card" style="margin-top:16px;margin-bottom:24px">
      <h3>Booking Notice</h3>
      <p class="cms-note">Pesan ini akan tampil di atas jadwal pada halaman peminjaman. Contoh: "Jadwal yang kosong pada minggu ke 1".</p>
      <form method="post" action="action.php" class="cms-grid">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="section" value="bookingNotice">
        <textarea name="bookingNotice" rows="2" placeholder="Tulis pengumuman singkat..." class="cms-textarea"><?php echo htmlspecialchars($data['bookingNotice'] ?? ''); ?></textarea>
        <div class="cms-row" style="justify-content:flex-end"><button class="cms-btn">Save</button></div>
      </form>
      <div class="cms-row" style="margin-top:10px; gap:8px; align-items:center;">
        <a class="cms-btn-outline" href="../booking.php?ts=<?php echo time(); ?>">Lihat Jadwal</a>
        <a class="cms-btn-outline" href="../booking.php?ts=<?php echo time(); ?>">Refresh Jadwal</a>
        <form method="post" action="../booking.php" onsubmit="return confirm('Reset semua jadwal? Tindakan ini tidak dapat dibatalkan.');">
          <input type="hidden" name="reset" value="1">
          <button class="cms-btn-outline" type="submit" style="background:#ffecec;border-color:#ffb3b3;color:#7a0000">Reset Jadwal</button>
        </form>
      </div>
    </section>

    <!-- What We Do -->
    <section id="what" class="cms-card" style="margin-top:16px">
      <h3>What We Do</h3>
      <table class="cms-table">
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
                <button class="cms-btn-outline" onclick="return confirm('Delete this item?')">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
      <form method="post" action="action.php" class="cms-grid cms-grid-three" style="margin-top:10px">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="section" value="whatWeDo">
        <input type="hidden" name="op" value="create">
        <input class="cms-input" name="icon" placeholder="Icon (e.g. 📊)">
        <input class="cms-input" name="title" placeholder="Title">
        <input class="cms-input" name="desc" placeholder="Description">
        <div style="grid-column:1/-1;text-align:right"><button class="cms-btn">Add</button></div>
      </form>
    </section>

    <!-- Team -->
    <section id="team" class="cms-card" style="margin-top:16px">
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
                <button class="cms-btn-outline" onclick="return confirm('Delete member?')">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
      <form method="post" action="action.php" enctype="multipart/form-data" class="cms-grid cms-grid-three" style="margin-top:10px">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="section" value="team">
        <input type="hidden" name="op" value="create">
        <input class="cms-input" name="name" placeholder="Name">
        <input class="cms-input" name="role" placeholder="Role">
        <input class="cms-input" type="file" name="photo" accept="image/*">
        <div style="grid-column:1/-1;text-align:right"><button class="cms-btn">Add Member</button></div>
      </form>
    </section>

    <!-- Facilities -->
    <section id="facilities" class="cms-card" style="margin-top:16px">
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
                <button class="cms-btn-outline" onclick="return confirm('Delete facility?')">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
      <form method="post" action="action.php" class="cms-grid cms-grid-two" style="margin-top:10px">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="section" value="facilities">
        <input type="hidden" name="op" value="create">
        <input class="cms-input" name="text" placeholder="Facility text">
        <div style="text-align:right"><button class="cms-btn">Add</button></div>
      </form>
    </section>

    <!-- Research -->
    <section id="research" class="cms-card" style="margin-top:16px">
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
                <button class="cms-btn-outline" onclick="return confirm('Delete research?')">Delete</button>
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

    <!-- News / Berita -->
    <section id="news" class="cms-card" style="margin-top:16px">
      <h3>News / Berita</h3>
      <table>
        <tr><th>Image</th><th>Title</th><th>Date</th><th>Excerpt</th><th>Actions</th></tr>
        <?php foreach($data['news'] as $i=>$n): ?>
          <?php $img = $n['image'] ?? ''; $preview = $img; if (is_string($preview) && strpos($preview,'uploads/')===0) { $preview = '../'.$preview; } ?>
          <tr>
            <td><?php if($img): ?><img src="<?php echo htmlspecialchars($preview); ?>" style="height:40px;border-radius:6px"><?php endif; ?></td>
            <td><?php echo htmlspecialchars($n['title']??''); ?></td>
            <td><?php echo htmlspecialchars($n['date']??''); ?></td>
            <td><?php echo htmlspecialchars($n['excerpt']??''); ?></td>
            <td>
              <form style="display:inline" method="post" action="action.php">
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                <input type="hidden" name="section" value="news">
                <input type="hidden" name="op" value="delete">
                <input type="hidden" name="index" value="<?php echo $i; ?>">
                <button class="cms-btn-outline" onclick="return confirm('Delete this news item?')">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
      <form method="post" action="action.php" enctype="multipart/form-data" class="cms-grid cms-grid-two" style="margin-top:10px">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="section" value="news">
        <input type="hidden" name="op" value="create">
        <div>
          <label>Judul Berita</label>
          <input class="cms-input" name="title" placeholder="Judul berita">
        </div>
        <div>
          <label>Tanggal</label>
          <input class="cms-input" type="date" name="date">
        </div>
        <div>
          <label>Ringkasan Singkat (opsional)</label>
          <textarea class="cms-textarea" name="excerpt" rows="2" placeholder="Ringkasan 1–2 kalimat"></textarea>
        </div>
        <div>
          <label>Gambar</label>
          <input class="cms-input" type="file" name="image" accept="image/*">
        </div>
        <div style="grid-column:1/-1">
          <label>Isi Lengkap Berita</label>
          <textarea class="cms-textarea" name="content" rows="4" placeholder="Tulis isi lengkap berita di sini"></textarea>
        </div>
        <div style="grid-column:1/-1;text-align:right"><button class="cms-btn">Tambah Berita</button></div>
      </form>
    </section>

    <!-- Publications -->
    <section id="publications" class="cms-card" style="margin-top:16px">
      <h3>Publications</h3>
      <table>
        <tr><th>Year</th><th>Text</th><th>SINTA Link</th><th>Actions</th></tr>
        <?php foreach($data['publications'] as $i=>$p): ?>
          <tr>
            <td><?php echo htmlspecialchars($p['year']??''); ?></td>
            <td><?php echo htmlspecialchars($p['text']??''); ?></td>
            <td>
              <?php if (!empty($p['sinta_link'])): ?>
                <a href="<?php echo htmlspecialchars($p['sinta_link']); ?>">
                  <?php echo htmlspecialchars($p['sinta_link']); ?>
                </a>
              <?php else: ?>
                <span class="cms-note">(tidak ada link)</span>
              <?php endif; ?>
            </td>
            <td>
              <form style="display:inline" method="post" action="action.php">
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                <input type="hidden" name="section" value="publications">
                <input type="hidden" name="op" value="delete">
                <input type="hidden" name="index" value="<?php echo $i; ?>">
                <button class="cms-btn-outline" onclick="return confirm('Delete publication?')">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
      <form method="post" action="action.php" class="grid three" style="margin-top:10px">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="section" value="publications">
        <input type="hidden" name="op" value="create">
        <input class="cms-input" name="year" placeholder="Year">
        <input class="cms-input" name="text" placeholder="Text">
        <input class="cms-input" name="sinta_link" placeholder="Link SINTA (opsional)">
        <div style="grid-column:1/-1;text-align:right"><button class="cms-btn">Add</button></div>
      </form>
    </section>

    <!-- Gallery -->
    <section id="gallery" class="cms-card" style="margin-top:16px;margin-bottom:24px">
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
                <button class="cms-btn-outline" onclick="return confirm('Delete photo?')">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
      <form method="post" action="save.php" enctype="multipart/form-data" class="cms-grid cms-grid-two" style="margin-top:10px">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="section" value="gallery">
        <input type="hidden" name="op" value="create">
        <input class="cms-input" name="url" placeholder="Or paste image URL (optional)">
        <input class="cms-input" type="file" name="image" accept="image/*">
        <input class="cms-input" name="caption" placeholder="Caption / Deskripsi (opsional)">
        <div style="grid-column:1/-1;text-align:right"><button class="cms-btn">Add Photo</button></div>
      </form>
    </section>
    
  </main>
  <script src="../assets/app.js"></script>
</body>
</html>
