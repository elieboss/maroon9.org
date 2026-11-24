<?php
/**
 * api/gallery.php
 * GET                              anyone: photos and videos on the Gallery page
 * POST action=start   { name, size, caption }   staff: begin an upload
 * POST ?upload=ID&offset=N  (raw file bytes)   staff: one piece of the file
 * POST action=caption { id, caption }          staff
 * POST action=delete  { id }                   staff
 */

require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/gallery-data.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') json_out(array('items' => gallery_items()));

/* File pieces are sent as raw bytes, not JSON, so check CSRF here */
if (isset($_GET['upload'])) {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed.', 405);
  if (!csrf_valid($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) json_error('Your session expired. Refresh the page and try again.', 419);
  api_require_staff();
  $result = gallery_receive_chunk((string) $_GET['upload'], (int) ($_GET['offset'] ?? -1), file_get_contents('php://input'));
  if (isset($result['error'])) json_error($result['error'], $result['status'] ?? 400);
  json_out($result, isset($result['item']) ? 201 : 200);
}

$input = api_input();
$staff = api_require_staff();

switch (str_field($input, 'action', 30)) {
  case 'start':
    $result = gallery_start_upload(str_field($input, 'name', 255), (int) ($input['size'] ?? 0), str_field($input, 'caption', 300), (int) $staff['id']);
    if (isset($result['error'])) json_error($result['error'], 422);
    json_out($result);

  case 'caption':
    gallery_update_caption((int) ($input['id'] ?? 0), str_field($input, 'caption', 300));
    json_out(array('items' => gallery_items()));

  case 'delete':
    gallery_delete((int) ($input['id'] ?? 0));
    json_out(array('items' => gallery_items()));

  default:
    json_error('Unknown action.');
}
