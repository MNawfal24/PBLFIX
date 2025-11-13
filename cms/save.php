<?php
session_start();

// --- Cegah akses langsung tanpa CSRF ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit;
}

$csrf = $_POST['csrf'] ?? '';
if (!isset($_SESSION['csrf']) || $csrf !== $_SESSION['csrf']) {
    die("Invalid CSRF token");
}

// --- Lokasi file content.json ---
$jsonFile = __DIR__ . "/../data/content.json";
if (!file_exists($jsonFile)) {
    file_put_contents($jsonFile, json_encode([]));
}
$data = json_decode(file_get_contents($jsonFile), true);
if (!is_array($data)) $data = [];

// --- Pastikan key gallery ada ---
if (!isset($data['gallery'])) {
    $data['gallery'] = [];
}

// --- Pastikan folder uploads ada ---
$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// --- Jika upload gambar (pakai input file "image") ---
if (!empty($_FILES['image']['name'])) {
    $origName = basename($_FILES["image"]["name"]);
    $safeName = preg_replace('/[^a-zA-Z0-9-_\.]/','_', $origName);
    $ext = strtolower(pathinfo($safeName, PATHINFO_EXTENSION));
    $allowed = ["jpg","jpeg","png","gif","webp"];
    if (!in_array($ext, $allowed)) {
        die("Format file tidak didukung.");
    }
    // Buat nama unik
    $destName = uniqid('img_') . '.' . $ext;
    $targetFile = $uploadDir . $destName;
    if (is_uploaded_file($_FILES["image"]["tmp_name"]) && move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
        // Simpan sebagai objek {src, caption}
        $relPath = "uploads/" . $destName;
        $caption = trim($_POST['caption'] ?? '');
        $data['gallery'][] = [
            'src' => $relPath,
            'caption' => $caption
        ];
    } else {
        die("Gagal mengunggah file.");
    }
}
// --- Jika pakai URL gambar langsung (input name="url") ---
elseif (!empty($_POST['url'])) {
    $url = trim($_POST['url']);
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $caption = trim($_POST['caption'] ?? '');
        $data['gallery'][] = [
            'src' => $url,
            'caption' => $caption
        ];
    }
}

// --- Simpan kembali ke JSON ---
file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// --- Redirect balik ke dashboard ---
header("Location: dashboard.php#gallery");
exit;
?>
