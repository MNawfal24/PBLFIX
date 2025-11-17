<?php
session_start();

function require_admin(){
  if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) { header('Location: login.php'); exit; }
}

function csrf_token(){
  if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
  return $_SESSION['csrf'];
}

function check_csrf($token){
  if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) { http_response_code(400); exit('Invalid CSRF token'); }
}

function content_path(){ return __DIR__ . '/../data/content.json'; }

function load_content(){
  $file = content_path();
  if (!file_exists($file)) return [
    'vision'=>'',
    'whatWeDo'=>[], 'team'=>[], 'facilities'=>[], 'research'=>[], 'activities'=>[], 'publications'=>[], 'gallery'=>[],
    'bookingNotice'=>'',
    'siteName'=>'Business Analytics Lab',
    'siteLogo'=>''
  ];
  $data = json_decode(file_get_contents($file), true);
  if (!is_array($data)) $data = [];
  // Ensure arrays exist
  foreach(['whatWeDo','team','facilities','research','activities','publications','gallery'] as $k){ if (!isset($data[$k]) || !is_array($data[$k])) $data[$k]=[]; }
  if (!isset($data['bookingNotice'])) $data['bookingNotice'] = '';
  if (!isset($data['siteName'])) $data['siteName'] = 'Business Analytics Lab';
  if (!isset($data['siteLogo'])) $data['siteLogo'] = '';
  if (!isset($data['version'])) $data['version'] = time();
  return $data;
}

function save_content($data){
  $file = content_path();
  if (!is_dir(dirname($file))) mkdir(dirname($file), 0775, true);
  $data['version'] = time(); // bump version for cache-busting / auto-refresh
  file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
}

function ensure_uploads(){
  $dir = __DIR__ . '/../uploads';
  if (!is_dir($dir)) mkdir($dir, 0775, true);
  return $dir;
}

function upload_image($field){
  if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;
  $tmp = $_FILES[$field]['tmp_name'];
  $name = preg_replace('/[^a-zA-Z0-9-_\.]/','_', $_FILES[$field]['name']);
  $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
  if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) return null;
  $destDir = ensure_uploads();
  $destName = uniqid('img_').'.'.$ext;
  $dest = $destDir . '/' . $destName;
  if (move_uploaded_file($tmp, $dest)) return 'uploads/'.$destName; // store consistent relative path for frontend
  return null;
}

// Download image from a remote URL and save into uploads
function download_image_url($url){
  $url = trim($url ?? '');
  if ($url === '') return null;
  if (!preg_match('#^https?://#i', $url)) return null;
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 3,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_USERAGENT => 'PBL-CMS/1.0',
    CURLOPT_HEADER => true,
  ]);
  $response = curl_exec($ch);
  if ($response === false) { curl_close($ch); return null; }
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
  $headers = substr($response, 0, $headerSize);
  $body = substr($response, $headerSize);
  curl_close($ch);
  if ($status < 200 || $status >= 300) return null;
  // Determine extension from content-type or URL path
  $ctype = null;
  if (preg_match('/^Content-Type:\s*([^\r\n]+)/mi', $headers, $m)) $ctype = trim($m[1]);
  // If it's an HTML page (like Pinterest), try to extract og:image
  if ($ctype && stripos($ctype, 'text/html') !== false){
    // Find og:image or twitter:image
    if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]*content=["\']([^"\']+)["\']/i', $body, $mm)
      || preg_match('/<meta[^>]+name=["\']og:image["\'][^>]*content=["\']([^"\']+)["\']/i', $body, $mm)
      || preg_match('/<meta[^>]+property=["\']twitter:image["\'][^>]*content=["\']([^"\']+)["\']/i', $body, $mm)){
      $imgUrl = html_entity_decode(trim($mm[1]));
      // absolutize URL
      $p = parse_url($url);
      if (strpos($imgUrl, '//') === 0) { $imgUrl = ($p['scheme'] ?? 'https') . ':' . $imgUrl; }
      elseif (!preg_match('#^https?://#i', $imgUrl)) {
        $base = ($p['scheme'] ?? 'https') . '://' . ($p['host'] ?? '');
        if (isset($p['port'])) $base .= ':' . $p['port'];
        $path = isset($p['path']) ? rtrim(dirname($p['path']), '/') : '';
        if (substr($imgUrl,0,1) === '/') $imgUrl = $base . $imgUrl; else $imgUrl = $base . $path . '/' . $imgUrl;
      }
      return download_image_url($imgUrl);
    }
  }
  $map = [
    'image/jpeg' => 'jpg', 'image/jpg'=>'jpg', 'image/png'=>'png', 'image/gif'=>'gif', 'image/webp'=>'webp'
  ];
  $ext = $map[$ctype] ?? strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
  if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) return null;
  $destDir = ensure_uploads();
  $destName = uniqid('img_').'.'.$ext;
  $dest = $destDir . '/' . $destName;
  if (file_put_contents($dest, $body) !== false) return '../uploads/'.$destName;
  return null;
}

function new_id(){ return bin2hex(random_bytes(6)); }
