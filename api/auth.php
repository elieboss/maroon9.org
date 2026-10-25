<?php
/**
 * api/auth.php
 * GET  ?action=me          current user (or null) and a CSRF token
 * POST action=login        { email, password }   any account
 * POST action=staff_login  { email, password }   staff accounts only
 * POST action=register     { name, email, password }  (sends a confirmation email)
 * POST action=logout
 */

require_once __DIR__ . '/../includes/api.php';

$input  = api_input();
$action = str_field($input, 'action', 20);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  if ($action !== 'me') json_error('Unknown action.');
  json_out(array('user' => public_user(current_user()), 'csrf' => csrf_token()));
}

switch ($action) {
  case 'login':
  case 'staff_login':
    $email    = str_field($input, 'email');
    $password = isset($input['password']) && is_string($input['password']) ? $input['password'] : '';

    if ($email === '' || $password === '') json_error('Enter your email and password.');
    if (login_locked_out($email)) {
      json_error('Too many failed attempts. Wait ' . M9_LOGIN_WINDOW_MIN . ' minutes and try again.', 429);
    }

    $user = attempt_login($email, $password, $action === 'staff_login');
    if ($user === false) json_error('This login is for Maroon 9 staff only. Please use the regular log in page.', 403);
    if ($user === null) json_error('That email and password don’t match an account.', 401);
    json_out(array('user' => public_user(current_user(true)), 'csrf' => csrf_token()));

  case 'register':
    $password = isset($input['password']) && is_string($input['password']) ? $input['password'] : '';
    $result = register_user(str_field($input, 'name', 100), str_field($input, 'email'), $password);
    if (isset($result['fields'])) json_error('Please fix the highlighted fields.', 422, $result['fields']);
    json_out(array('user' => public_user($result['user']), 'csrf' => csrf_token()), 201);

  case 'logout':
    log_out_user();
    json_out(array('user' => null));

  default:
    json_error('Unknown action.');
}
