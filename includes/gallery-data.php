<?php
/**
 * gallery-data.php
 * Photos and videos on the Gallery page. Staff upload them from the dashboard
 * in small pieces (so large videos fit under PHP's request limits); each piece
 * is appended to a temp file in data/, then checked and moved into uploads/.
 *
 * Photos are re-saved as WebP: a display copy (max 2400px) and a grid
 * thumbnail (max 800px). Re-saving drops camera metadata such as GPS location.
 */

require_once __DIR__ . '/db.php';

define('M9_UPLOADS_DIR', getenv('M9_UPLOADS_DIR') ?: __DIR__ . '/../uploads');
define('M9_GALLERY_CHUNK', 2 * 1024 * 1024);
define('M9_PHOTO_MAX', 25 * 1024 * 1024);
define('M9_VIDEO_MAX', 500 * 1024 * 1024);
define('M9_PHOTO_MAX_PIXELS', 50 * 1000 * 1000);   // decoding more than this could exhaust memory

function gallery_types() {
  return array(
    'image/jpeg' => array('photo', 'jpg'), 'image/png' => array('photo', 'png'), 'image/webp' => array('photo', 'webp'),
    'image/avif' => array('photo', 'avif'), 'image/gif' => array('photo', 'gif'),
    'video/mp4' => array('video', 'mp4'), 'video/webm' => array('video', 'webm'), 'video/quicktime' => array('video', 'mov'),
  );
}

/* Site-relative path ("images/a b.png") -> URL path ("images/a%20b.png") */
function gallery_url($path) {
  return $path === '' ? '' : implode('/', array_map('rawurlencode', explode('/', $path)));
}

/* Absolute file for a stored path; uploads may live outside the site folder */
function gallery_file($path) {
  if (strpos($path, 'uploads/') === 0) return M9_UPLOADS_DIR . '/' . substr($path, 8);
  return __DIR__ . '/../' . $path;
}

function gallery_item_out($row) {
  return array(
    'id'         => (int) $row['id'],
    'kind'       => $row['kind'],
    'src'        => gallery_url($row['path']),
    'thumb'      => gallery_url($row['thumb'] !== '' && is_file(gallery_file($row['thumb'])) ? $row['thumb'] : ($row['kind'] === 'photo' ? $row['path'] : '')),
    'caption'    => $row['caption'],
    'type'       => $row['mime'],
    'created_at' => $row['created_at'],
    'uploaded_by'=> $row['uploader'] ?? null,
  );
}

function gallery_items($kind = null) {
  $sql = 'SELECT g.*, u.name AS uploader FROM gallery_media g LEFT JOIN users u ON u.id = g.uploaded_by';
  $rows = db()->query($sql . ' ORDER BY g.id DESC')->fetchAll();
  if ($kind) $rows = array_filter($rows, function ($r) use ($kind) { return $r['kind'] === $kind; });
  return array_values(array_map('gallery_item_out', $rows));
}

function gallery_tmp_dir() {
  $dir = dirname(M9_DB_PATH) . '/upload-tmp';
  if (!is_dir($dir)) mkdir($dir, 0700, true);
  return $dir;
}

function gallery_uploads_dir() {
  $dir = M9_UPLOADS_DIR . '/gallery';
  if (!is_dir($dir)) mkdir($dir, 0755, true);
  /* Apache: never run scripts from the uploads folder (router.php does this for php -S) */
  $htaccess = M9_UPLOADS_DIR . '/.htaccess';
  if (!is_file($htaccess)) file_put_contents($htaccess, "Options -ExecCGI -Indexes\nRemoveHandler .php .phtml .php5\nRemoveType .php .phtml .php5\n<FilesMatch \"\\.(php|phtml|php5)$\">\n  Require all denied\n</FilesMatch>\n");
  return $dir;
}

/* Begin an upload; returns the id the browser sends its pieces to */
function gallery_start_upload($name, $size, $caption, $userId) {
  $size = (int) $size;
  if ($size <= 0) return array('error' => 'That file is empty.');
  if ($size > M9_VIDEO_MAX) return array('error' => 'That file is too large. Videos can be up to 500 MB and photos up to 25 MB.');
  if (preg_match('/\.(heic|heif)$/i', $name)) {
    return array('error' => 'iPhone HEIC photos can’t be shown in most browsers. Export the photo as JPEG and try again.');
  }

  /* Clear pieces of uploads that were abandoned more than a day ago */
  foreach (glob(gallery_tmp_dir() . '/*.part') ?: array() as $old) {
    if (filemtime($old) < time() - 86400) @unlink($old);
  }

  $id = bin2hex(random_bytes(16));
  touch(gallery_tmp_dir() . "/$id.part");
  $_SESSION['gallery_uploads'][$id] = array('size' => $size, 'caption' => $caption, 'user' => $userId);
  return array('upload' => $id, 'chunk_size' => M9_GALLERY_CHUNK);
}

/* Append one piece; when the last piece arrives, check the file and save it */
function gallery_receive_chunk($id, $offset, $data) {
  $upload = $_SESSION['gallery_uploads'][$id] ?? null;
  $part = gallery_tmp_dir() . "/$id.part";
  if (!$upload || !preg_match('/^[0-9a-f]{32}$/', $id) || !is_file($part)) {
    return array('error' => 'This upload expired. Please choose the file again.', 'status' => 410);
  }

  clearstatcache(true, $part);
  $received = filesize($part);
  if ((int) $offset !== $received || strlen($data) > M9_GALLERY_CHUNK || $received + strlen($data) > $upload['size']) {
    return array('error' => 'Part of the file went missing on the way. Please try again.', 'status' => 409);
  }
  file_put_contents($part, $data, FILE_APPEND);
  $received += strlen($data);
  if ($received < $upload['size']) return array('received' => $received);

  unset($_SESSION['gallery_uploads'][$id]);
  $result = gallery_store($part, $upload['caption'], $upload['user']);
  @unlink($part);
  return $result;
}

function gallery_store($file, $caption, $userId) {
  $mime  = (new finfo(FILEINFO_MIME_TYPE))->file($file);
  $types = gallery_types();
  if (!isset($types[$mime])) {
    return array('error' => 'That file type isn’t supported. Use JPG, PNG, WebP, GIF or AVIF photos, or MP4, WebM or MOV videos.', 'status' => 415);
  }
  list($kind, $ext) = $types[$mime];
  if ($kind === 'photo' && filesize($file) > M9_PHOTO_MAX) return array('error' => 'Photos can be up to 25 MB.', 'status' => 413);
  if ($kind === 'photo') {
    $dimensions = @getimagesize($file);
    if ($dimensions === false) return array('error' => 'That photo couldn’t be read. It may be damaged.', 'status' => 415);
    if ($dimensions[0] * $dimensions[1] > M9_PHOTO_MAX_PIXELS) return array('error' => 'That photo’s dimensions are too large. Use one under 50 megapixels.', 'status' => 413);
  }

  $dir  = gallery_uploads_dir();
  $name = date('Ymd') . '-' . bin2hex(random_bytes(6));
  $path = $thumb = '';

  /* Photos (except animated GIFs) become WebP copies. The original is never
     published instead: it can carry private data such as GPS location. */
  if ($kind === 'photo' && $mime !== 'image/gif') {
    if (!gallery_resize($file, "$dir/$name.webp", 2400, 85)
        || !gallery_resize($file, "$dir/$name-thumb.webp", 800, 78)) {
      @unlink("$dir/$name.webp");
      @unlink("$dir/$name-thumb.webp");
      return array('error' => 'That photo couldn’t be processed. Save it as a JPG and try again.', 'status' => 415);
    }
    $path  = "uploads/gallery/$name.webp";
    $thumb = "uploads/gallery/$name-thumb.webp";
    $mime  = 'image/webp';
  } else {
    if (!rename($file, "$dir/$name.$ext")) return array('error' => 'The file couldn’t be saved. Please try again.', 'status' => 500);
    chmod("$dir/$name.$ext", 0644);
    $path = "uploads/gallery/$name.$ext";
  }

  db()->prepare('INSERT INTO gallery_media (kind, path, thumb, mime, caption, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)')
      ->execute(array($kind, $path, $thumb, $mime, $caption, $userId));
  return array('item' => gallery_item_out(db()->query('SELECT g.*, u.name AS uploader FROM gallery_media g LEFT JOIN users u ON u.id = g.uploaded_by WHERE g.id = ' . (int) db()->lastInsertId())->fetch()));
}

/* Save a WebP copy no wider/taller than $max, turned upright using the camera's EXIF orientation */
function gallery_resize($src, $dest, $max, $quality) {
  if (!function_exists('imagewebp')) return false;
  $info = @getimagesize($src);
  if (!$info) return false;
  ini_set('memory_limit', '512M');

  $loaders = array(IMAGETYPE_JPEG => 'imagecreatefromjpeg', IMAGETYPE_PNG => 'imagecreatefrompng', IMAGETYPE_WEBP => 'imagecreatefromwebp');
  if (defined('IMAGETYPE_AVIF')) $loaders[IMAGETYPE_AVIF] = 'imagecreatefromavif';
  if (!isset($loaders[$info[2]]) || !function_exists($loaders[$info[2]])) return false;
  $img = @$loaders[$info[2]]($src);
  if (!$img) return false;

  if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
    $orientation = (int) (@exif_read_data($src)['Orientation'] ?? 1);
    if (in_array($orientation, array(2, 4, 5, 7), true)) imageflip($img, IMG_FLIP_HORIZONTAL);
    /* imagerotate() turns counter-clockwise */
    $angle = array(3 => 180, 4 => 180, 5 => 90, 6 => 270, 7 => 270, 8 => 90)[$orientation] ?? 0;
    if ($angle) $img = imagerotate($img, $angle, 0);
  }

  $w = imagesx($img);
  $h = imagesy($img);
  $scale = min(1, $max / max($w, $h));
  $out = imagecreatetruecolor(max(1, (int) round($w * $scale)), max(1, (int) round($h * $scale)));
  imagealphablending($out, false);
  imagesavealpha($out, true);
  imagecopyresampled($out, $img, 0, 0, 0, 0, imagesx($out), imagesy($out), $w, $h);
  $ok = imagewebp($out, $dest, $quality);
  if ($ok) chmod($dest, 0644);
  return $ok;
}

function gallery_update_caption($id, $caption) {
  db()->prepare('UPDATE gallery_media SET caption = ? WHERE id = ?')->execute(array($caption, (int) $id));
}

/* Remove the entry; uploaded files are deleted, the site's own images/ files are kept */
function gallery_delete($id) {
  $stmt = db()->prepare('SELECT path, thumb FROM gallery_media WHERE id = ?');
  $stmt->execute(array((int) $id));
  $row = $stmt->fetch();
  if (!$row) return;
  db()->prepare('DELETE FROM gallery_media WHERE id = ?')->execute(array((int) $id));
  foreach (array($row['path'], $row['thumb']) as $path) {
    if (strpos($path, 'uploads/gallery/') === 0 && is_file(gallery_file($path))) unlink(gallery_file($path));
  }
}

/* Starting content: photos and the intro video already on the site (first one shows first) */
function gallery_seed(PDO $pdo) {
  $photos = array(
    'maroon1.png'               => 'Two young performers in kente-print costumes on stage, one cradling a baby doll',
    'maroon11.png'              => 'Dancers in African-print costumes performing a group routine',
    'kidsonsstagehomepage.avif' => 'The cast celebrating on stage at the end of a performance',
    'maroon2.png'               => 'A performer in a purple-and-gold print dress and crown, arms open wide',
    'maroon13.png'              => 'Performers in colorful costumes on a market-scene set beside a large black pot',
    'maroon14.png'              => 'A row of dancers reaching forward in unison',
    'maroon10.png'              => 'A storyteller reading beside a kinara on a Kwanzaa set',
    'maroon4.png'               => 'Dancers leaping with arms raised under the stage lights',
    'maroon12.png'              => 'A performer in a gold skirt mid-dance on a wooden stage',
    'maroon6.png'               => 'The cast gathered on stage in African-print costumes',
    'maroon8.png'               => 'Dancers striking a pose together',
    'maroon3.png'               => 'A young actor in a gold-patterned robe performing a solo',
    'maroon16.png'              => 'A performer holding a woven fan, speaking from the stage',
    'maroon5.png'               => 'Performers on stage in a Kwanzaa scene',
    'maroon7.png'               => 'Youth in white costumes performing outdoors on the grass',
    'kidsinblue.avif'           => 'Young participants in blue shirts gathered outside',
    'maroon15.png'              => 'An actor in a hat performing a monologue',
    'Bone Soup Meme 28.avif'    => 'A performer in a wide stance in front of a red curtain',
    'Bone Soup Meme 4.avif'     => 'An actor in an orange print shirt with arms raised',
    'Bone Soup Meme 18.avif'    => 'A young performer seated cross-legged during rehearsal',
    'Bone Soup Meme 19.avif'    => 'A student and instructor sharing a high five',
    'Bone Soup Meme 23.avif'    => 'A teen actor gesturing while rehearsing a scene',
  );

  /* Newest shows first, so insert in reverse */
  $insert = $pdo->prepare('INSERT INTO gallery_media (kind, path, thumb, mime, caption) VALUES (?, ?, ?, ?, ?)');
  $insert->execute(array('video', 'images/Maroon9 (1).mp4', 'images/maroon9-intro-poster.jpg', 'video/mp4', 'Meet Maroon 9'));
  foreach (array_reverse($photos, true) as $file => $caption) {
    $thumb = 'images/gallery-thumbs/' . preg_replace('/\.\w+$/', '', str_replace(' ', '-', $file)) . '.webp';
    $insert->execute(array('photo', "images/$file", $thumb, '', $caption));
  }
}
