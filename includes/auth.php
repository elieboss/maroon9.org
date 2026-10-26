<?php
/**
 * auth.php
 * Sessions, login/logout, role checks and CSRF protection.
 * PHP is the only place permissions are decided; the React UI just
 * hides controls a user can't use.
 */

require_once __DIR__ . '/db.php';

define('M9_LOGIN_MAX_ATTEMPTS', 5);   // failed attempts allowed...
define('M9_LOGIN_WINDOW_MIN', 15);    // ...within this many minutes

function m9_session_start() {
  if (session_status() === PHP_SESSION_ACTIVE) return;

  $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
  session_name('m9_session');
  session_set_cookie_params(array(
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
  ));
  ini_set('session.use_strict_mode', '1');
  session_start();
}

/* The logged-in user as an array, or null */
function current_user($refresh = false) {
  static $loaded = false, $user = null;
  if ($loaded && !$refresh) return $user;
  $loaded = true;
  $user = null;

  m9_session_start();
  if (empty($_SESSION['user_id'])) return null;

  $stmt = db()->prepare('SELECT id, name, email, role, created_at, email_verified_at, session_version FROM users WHERE id = ?');
  $stmt->execute(array($_SESSION['user_id']));
  $user = $stmt->fetch() ?: null;

  /* Account was deleted while logged in */
  if ($user === null) { unset($_SESSION['user_id']); return null; }

  /* Password was changed or reset elsewhere: this older session is signed out */
  if (!isset($_SESSION['sv'])) {
    $_SESSION['sv'] = (int) $user['session_version'];   // sessions from before this check existed
  } elseif ((int) $_SESSION['sv'] !== (int) $user['session_version']) {
    unset($_SESSION['user_id'], $_SESSION['sv']);
    $user = null;
  }
  return $user;
}

function is_staff() {
  $user = current_user();
  return $user !== null && $user['role'] === 'staff';
}

/* Public shape of a user that is safe to hand to the browser */
function public_user($user) {
  if ($user === null) return null;
  return array(
    'id'    => (int) $user['id'],
    'name'  => $user['name'],
    'email' => $user['email'],
    'role'  => $user['role'],
    'email_verified' => !empty($user['email_verified_at']),
  );
}

/* Page guards: send visitors to the login page and back afterwards */
function require_login_page() {
  if (current_user() !== null) return;
  $next = basename($_SERVER['PHP_SELF']);
  header('Location: login.php?next=' . urlencode($next));
  exit;
}

/* Staff pages: visitors go to the staff login; logged-in regular users are
   turned away (their role is checked on every request, not just at login) */
function require_staff_page() {
  if (current_user() === null) {
    header('Location: staff-login.php');
    exit;
  }
  if (is_staff()) return;
  header('Location: account.php?denied=1');
  exit;
}

/* Only allow redirects back to a page on this site */
function safe_next($next) {
  return preg_match('/^[a-z]+\.php(#[a-z-]+)?$/', (string) $next) ? $next : 'account.php';
}

function csrf_token() {
  m9_session_start();
  if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
  return $_SESSION['csrf'];
}

function csrf_valid($token) {
  m9_session_start();
  return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function client_ip() {
  return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

function login_locked_out($email) {
  $stmt = db()->prepare("
    SELECT COUNT(*) FROM login_attempts
    WHERE email = ? AND ip = ? AND attempted_at > datetime('now', ?)
  ");
  $stmt->execute(array($email, client_ip(), '-' . M9_LOGIN_WINDOW_MIN . ' minutes'));
  return (int) $stmt->fetchColumn() >= M9_LOGIN_MAX_ATTEMPTS;
}

/* Returns the user on success, null on bad credentials. With $staffOnly
   (the staff login page), a correct password for a regular account returns
   false and does NOT log in. */
function attempt_login($email, $password, $staffOnly = false) {
  $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
  $stmt->execute(array($email));
  $user = $stmt->fetch();

  /* Verify against a dummy hash when the email is unknown so response
     time doesn't reveal which emails have accounts */
  $hash = $user ? $user['password_hash'] : password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
  if (!password_verify($password, $hash) || !$user) {
    db()->prepare('INSERT INTO login_attempts (email, ip) VALUES (?, ?)')->execute(array($email, client_ip()));
    return null;
  }

  if ($staffOnly && $user['role'] !== 'staff') {
    db()->prepare('INSERT INTO login_attempts (email, ip) VALUES (?, ?)')->execute(array($email, client_ip()));
    return false;
  }

  if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
    db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
        ->execute(array(password_hash($password, PASSWORD_DEFAULT), $user['id']));
  }
  db()->prepare('DELETE FROM login_attempts WHERE email = ? AND ip = ?')->execute(array($email, client_ip()));

  log_in_user((int) $user['id']);
  return $user;
}

function log_in_user($userId) {
  m9_session_start();
  session_regenerate_id(true);
  $_SESSION['user_id'] = $userId;
  $version = db()->prepare('SELECT session_version FROM users WHERE id = ?');
  $version->execute(array($userId));
  $_SESSION['sv'] = (int) $version->fetchColumn();
  unset($_SESSION['csrf']);   // fresh token for the new session
}

/* Call after a password change/reset: every other signed-in device is signed out */
function sign_out_other_sessions($userId) {
  db()->prepare('UPDATE users SET session_version = session_version + 1 WHERE id = ?')->execute(array($userId));
}

function log_out_user() {
  m9_session_start();
  $_SESSION = array();
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
  session_destroy();
}

/* Names, emails, titles: collapse line breaks/tabs so they can't spill into
   other lines of an email or a CSV export */
function single_line($text) {
  return trim(preg_replace('/[\x00-\x1F\x7F]+/u', ' ', (string) $text));
}

function valid_password($password) {
  return is_string($password) && strlen($password) >= 8 && strlen($password) <= 200;
}

/**
 * Create a regular (non-staff) account, log it in, and send the
 * confirmation email. Returns array('user' => ...) or array('fields' => errors).
 * Shared by api/auth.php (React) and register.php (no JavaScript).
 */
function register_user($name, $email, $password) {
  $name  = single_line(mb_substr((string) $name, 0, 100));
  $email = single_line(mb_substr((string) $email, 0, 200));

  $fields = array();
  if ($name === '') $fields['name'] = 'Enter your name.';
  if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) $fields['email'] = 'Enter a valid email address.';
  if (!valid_password($password)) $fields['password'] = 'Use at least 8 characters.';
  if (!$fields) {
    $exists = db()->prepare('SELECT 1 FROM users WHERE email = ?');
    $exists->execute(array($email));
    if ($exists->fetchColumn()) $fields['email'] = 'An account with this email already exists. Try logging in.';
  }
  if ($fields) return array('fields' => $fields);

  /* Public sign-up always creates a regular user; staff are promoted
     from the staff dashboard or bin/create-staff.php */
  db()->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'user')")
      ->execute(array($name, $email, password_hash($password, PASSWORD_DEFAULT)));
  $userId = (int) db()->lastInsertId();
  log_in_user($userId);

  require_once __DIR__ . '/notify.php';
  $user = array('id' => $userId, 'name' => $name, 'email' => $email);
  notify_signup($user, create_email_token($userId));
  return array('user' => current_user(true));
}

/* Email confirmation: the link carries a random token; only its hash is stored */
define('M9_EMAIL_TOKEN_HOURS', 48);

function create_email_token($userId) {
  $token = bin2hex(random_bytes(32));
  db()->prepare("UPDATE users SET email_token_hash = ?, email_token_sent_at = datetime('now') WHERE id = ?")
      ->execute(array(hash('sha256', $token), $userId));
  return $token;
}

/* Returns the confirmed user's name, or null for an invalid/expired link */
function confirm_email_token($token) {
  if (!is_string($token) || !preg_match('/^[0-9a-f]{64}$/', $token)) return null;
  $stmt = db()->prepare("
    SELECT id, name FROM users
    WHERE email_token_hash = ? AND email_token_sent_at > datetime('now', ?)
  ");
  $stmt->execute(array(hash('sha256', $token), '-' . M9_EMAIL_TOKEN_HOURS . ' hours'));
  $user = $stmt->fetch();
  if (!$user) return null;

  db()->prepare("UPDATE users SET email_verified_at = datetime('now'), email_token_hash = NULL WHERE id = ?")
      ->execute(array($user['id']));
  return $user['name'];
}
