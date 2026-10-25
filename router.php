<?php
/**
 * router.php
 * Router for PHP's built-in dev server:  php -S localhost:8080 router.php
 * Blocks private folders (database, PHP internals, React source) that the
 * dev server would otherwise serve as plain files. .htaccess does the same on Apache.
 */

$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if (preg_match('#^/(data|includes|bin|frontend|node_modules)(/|$)#', $path) || preg_match('#/\.#', $path)
    || $path === '/router.php') {
  http_response_code(404);
  echo 'Not found';
  return true;
}

/* The dev server ignores Range requests, and Safari/iOS won't play video
   without them, so serve video files here with partial-content support */
if (preg_match('#^/images/[^/]+\.(mp4|webm|mov)$#i', $path, $m) && is_file(__DIR__ . $path)) {
  serve_video(__DIR__ . $path, strtolower($m[1]));
  return true;
}

if ($path === '/') {
  require __DIR__ . '/index.php';
  return true;
}

/* Let the dev server handle real files (.php pages, css, images, assets) */
return false;

function serve_video($file, $ext) {
  $types = array('mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime');
  $size  = filesize($file);
  $start = 0;
  $end   = $size - 1;

  header('Content-Type: ' . $types[$ext]);
  header('Accept-Ranges: bytes');
  header('Cache-Control: public, max-age=86400');

  if (isset($_SERVER['HTTP_RANGE']) && preg_match('/^bytes=(\d*)-(\d*)$/', trim($_SERVER['HTTP_RANGE']), $r)) {
    if ($r[1] === '' && $r[2] !== '') {          // "bytes=-500": the last 500 bytes
      $start = max(0, $size - (int) $r[2]);
    } else {
      $start = (int) $r[1];
      if ($r[2] !== '') $end = min((int) $r[2], $size - 1);
    }
    if ($start > $end || $start >= $size) {
      http_response_code(416);
      header("Content-Range: bytes */$size");
      return;
    }
    http_response_code(206);
    header("Content-Range: bytes $start-$end/$size");
  }

  $length = $end - $start + 1;
  header('Content-Length: ' . $length);
  if ($_SERVER['REQUEST_METHOD'] === 'HEAD') return;

  $handle = fopen($file, 'rb');
  fseek($handle, $start);
  while ($length > 0 && !feof($handle)) {
    $chunk = fread($handle, min(1 << 16, $length));
    echo $chunk;
    $length -= strlen($chunk);
    flush();
  }
  fclose($handle);
}
