<?php
require_once __DIR__.'/helpers.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed'); }
check_csrf($_POST['csrf'] ?? '');
$section = $_POST['section'] ?? '';
$op = $_POST['op'] ?? 'update';
$data = load_content();

switch($section){
  case 'vision':
    $data['vision'] = trim($_POST['vision'] ?? '');
    break;

  case 'whatWeDo':
    if ($op === 'create'){
      $data['whatWeDo'][] = [
        'icon'=> trim($_POST['icon'] ?? ''),
        'title'=> trim($_POST['title'] ?? ''),
        'desc'=> trim($_POST['desc'] ?? ''),
      ];
    } elseif ($op === 'update'){
      $i = intval($_POST['index'] ?? -1);
      if (isset($data['whatWeDo'][$i])){
        $data['whatWeDo'][$i]['icon'] = trim($_POST['icon'] ?? '');
        $data['whatWeDo'][$i]['title'] = trim($_POST['title'] ?? '');
        $data['whatWeDo'][$i]['desc']  = trim($_POST['desc'] ?? '');
      }
    } elseif ($op === 'delete'){
      $i = intval($_POST['index'] ?? -1); if (isset($data['whatWeDo'][$i])) array_splice($data['whatWeDo'],$i,1);
    }
    break;

  case 'team':
    if ($op === 'create'){
      $photo = upload_image('photo');
      $data['team'][] = [
        'name'=> trim($_POST['name'] ?? ''),
        'role'=> trim($_POST['role'] ?? ''),
        'photo'=> $photo
      ];
    } elseif ($op === 'update'){
      $i = intval($_POST['index'] ?? -1);
      if (isset($data['team'][$i])){
        $data['team'][$i]['name'] = trim($_POST['name'] ?? '');
        $data['team'][$i]['role'] = trim($_POST['role'] ?? '');
        $photo = upload_image('photo');
        if ($photo) $data['team'][$i]['photo'] = $photo; // replace only if new uploaded
      }
    } elseif ($op === 'delete'){
      $i = intval($_POST['index'] ?? -1); if (isset($data['team'][$i])) array_splice($data['team'],$i,1);
    }
    break;

  case 'facilities':
    if ($op === 'create'){
      $t = trim($_POST['text'] ?? ''); if($t!=='') $data['facilities'][] = $t;
    } elseif ($op === 'update'){
      $i = intval($_POST['index'] ?? -1); if (isset($data['facilities'][$i])){ $data['facilities'][$i] = trim($_POST['text'] ?? ''); }
    } elseif ($op === 'delete'){
      $i = intval($_POST['index'] ?? -1); if (isset($data['facilities'][$i])) array_splice($data['facilities'],$i,1);
    }
    break;

  case 'research':
    if ($op === 'create'){
      $data['research'][] = [ 'title'=>trim($_POST['title']??''), 'desc'=>trim($_POST['desc']??'') ];
    } elseif ($op === 'update'){
      $i = intval($_POST['index'] ?? -1); if (isset($data['research'][$i])){
        $data['research'][$i]['title'] = trim($_POST['title']??'');
        $data['research'][$i]['desc'] = trim($_POST['desc']??'');
      }
    } elseif ($op === 'delete'){
      $i = intval($_POST['index'] ?? -1); if (isset($data['research'][$i])) array_splice($data['research'],$i,1);
    }
    break;

  case 'activities':
    if ($op === 'create'){
      $data['activities'][] = [ 'title'=>trim($_POST['title']??''), 'desc'=>trim($_POST['desc']??'') ];
    } elseif ($op === 'update'){
      $i = intval($_POST['index'] ?? -1); if (isset($data['activities'][$i])){
        $data['activities'][$i]['title'] = trim($_POST['title']??'');
        $data['activities'][$i]['desc'] = trim($_POST['desc']??'');
      }
    } elseif ($op === 'delete'){
      $i = intval($_POST['index'] ?? -1); if (isset($data['activities'][$i])) array_splice($data['activities'],$i,1);
    }
    break;

  case 'publications':
    if ($op === 'create'){
      $data['publications'][] = [ 'year'=>trim($_POST['year']??''), 'text'=>trim($_POST['text']??'') ];
    } elseif ($op === 'update'){
      $i = intval($_POST['index'] ?? -1); if (isset($data['publications'][$i])){
        $data['publications'][$i]['year'] = trim($_POST['year']??'');
        $data['publications'][$i]['text'] = trim($_POST['text']??'');
      }
    } elseif ($op === 'delete'){
      $i = intval($_POST['index'] ?? -1); if (isset($data['publications'][$i])) array_splice($data['publications'],$i,1);
    }
    break;

  case 'bookingNotice':
    $data['bookingNotice'] = trim($_POST['bookingNotice'] ?? '');
    break;

  case 'branding':
    // Update site name
    $data['siteName'] = trim($_POST['siteName'] ?? ($data['siteName'] ?? 'Business Analytics Lab'));
    // Upload logo if provided
    $logo = upload_image('logo');
    if ($logo) { $data['siteLogo'] = $logo; }
    break;

  case 'gallery':
    if ($op === 'create'){
      $up = upload_image('image');
      $src = $up; // only accept local uploads
      if ($src) $data['gallery'][] = $src;
    } elseif ($op === 'update'){
      $i = intval($_POST['index'] ?? -1); if (isset($data['gallery'][$i])){
        $up = upload_image('image');
        $src = $up ?: $data['gallery'][$i]; // keep old if no new upload
        if ($src) $data['gallery'][$i] = $src;
      }
    } elseif ($op === 'delete'){
      $i = intval($_POST['index'] ?? -1); if (isset($data['gallery'][$i])) array_splice($data['gallery'],$i,1);
    }
    break;

  default:
    http_response_code(400); exit('Unknown section');
}

save_content($data);
header('Location: dashboard.php#'.urlencode($section === 'whatWeDo' ? 'what' : $section));
exit;
