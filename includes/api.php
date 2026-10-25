<?php
/**
 * api.php
 * Helpers shared by the JSON endpoints in api/.
 * Every POST must carry the session's CSRF token in the X-CSRF-Token header.
 */

require_once __DIR__ . '/auth.php';

function json_out($data, $status = 200) {
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  header('Cache-Control: no-store');
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

function json_error($message, $status = 400, $fields = null) {
  $body = array('error' => $message);
  if ($fields) $body['fields'] = $fields;
  json_out($body, $status);
}

/* Reads the JSON request body and enforces CSRF for anything but GET */
function api_input() {
  $method = $_SERVER['REQUEST_METHOD'];
  if ($method === 'GET') return $_GET;
  if ($method !== 'POST') json_error('Method not allowed.', 405);

  if (!csrf_valid($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) {
    json_error('Your session expired. Refresh the page and try again.', 419);
  }

  $body = json_decode(file_get_contents('php://input'), true);
  return is_array($body) ? $body : array();
}

function api_require_login() {
  $user = current_user();
  if ($user === null) json_error('Please log in to continue.', 401);
  return $user;
}

function api_require_staff() {
  $user = api_require_login();
  if ($user['role'] !== 'staff') json_error('Staff access only.', 403);
  return $user;
}

/* Trimmed string field with a length cap. Single-line unless $multiline
   (messages, notes, descriptions), so names can't carry line breaks. */
function str_field($input, $key, $max = 200, $multiline = false) {
  $value = isset($input[$key]) && is_scalar($input[$key]) ? trim((string) $input[$key]) : '';
  return mb_substr($multiline ? $value : single_line($value), 0, $max);
}

function valid_email($email) {
  return filter_var($email, FILTER_VALIDATE_EMAIL) !== false && strlen($email) <= 200;
}

function valid_date($date) {
  $d = DateTime::createFromFormat('Y-m-d', $date);
  return $d !== false && $d->format('Y-m-d') === $date;
}
