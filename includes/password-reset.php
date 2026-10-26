<?php
/**
 * password-reset.php
 * "Forgot your password?" flow. A reset link is emailed, works once, and
 * expires after M9_RESET_MINUTES. Only a hash of the link's token is stored.
 * Requests always get the same response, so the form can't be used to
 * discover which emails have accounts.
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/notify.php';

define('M9_RESET_MINUTES', 60);

/* Returns true when the request was accepted (whether or not the email has an
   account), or 'limit' when too many requests were made recently. */
function request_password_reset($email) {
  $email = single_line(mb_substr((string) $email, 0, 200));

  $ipCount = db()->prepare("SELECT COUNT(*) FROM password_resets WHERE ip = ? AND created_at > datetime('now', '-1 hour')");
  $ipCount->execute(array(client_ip()));
  if ((int) $ipCount->fetchColumn() >= 10) return 'limit';

  $stmt = db()->prepare('SELECT id, name, email FROM users WHERE email = ?');
  $stmt->execute(array($email));
  $user = $stmt->fetch();
  if (!$user) return true;

  /* At most 3 reset emails per account per hour */
  $userCount = db()->prepare("SELECT COUNT(*) FROM password_resets WHERE user_id = ? AND created_at > datetime('now', '-1 hour')");
  $userCount->execute(array($user['id']));
  if ((int) $userCount->fetchColumn() >= 3) return true;

  /* Only the newest link works. Older links are expired, not deleted, so they
     still count toward the hourly limit above. */
  db()->prepare("UPDATE password_resets SET expires_at = datetime('now', '-1 second') WHERE user_id = ? AND used_at IS NULL")->execute(array($user['id']));

  $token = bin2hex(random_bytes(32));
  db()->prepare("INSERT INTO password_resets (user_id, token_hash, ip, expires_at) VALUES (?, ?, ?, datetime('now', ?))")
      ->execute(array($user['id'], hash('sha256', $token), client_ip(), '+' . M9_RESET_MINUTES . ' minutes'));

  notify_password_reset($user, $token);
  return true;
}

/* The account a reset link belongs to, or null if the link is invalid/expired/used */
function find_password_reset($token) {
  if (!is_string($token) || !preg_match('/^[0-9a-f]{64}$/', $token)) return null;
  $stmt = db()->prepare("
    SELECT r.id AS reset_id, u.id, u.name, u.email, u.role
    FROM password_resets r JOIN users u ON u.id = r.user_id
    WHERE r.token_hash = ? AND r.used_at IS NULL AND r.expires_at > datetime('now')
  ");
  $stmt->execute(array(hash('sha256', $token)));
  return $stmt->fetch() ?: null;
}

/* Sets the new password. Returns the user on success, or array('fields' => ...) */
function complete_password_reset($token, $password, $confirm) {
  $reset = find_password_reset($token);
  if (!$reset) return array('fields' => array('token' => 'This reset link has expired or was already used.'));

  $fields = array();
  if (!valid_password($password)) $fields['password'] = 'Use at least 8 characters.';
  elseif ($password !== $confirm) $fields['confirm'] = 'Passwords don’t match.';
  if ($fields) return array('fields' => $fields);

  $db = db();
  $db->beginTransaction();
  $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute(array(password_hash($password, PASSWORD_DEFAULT), $reset['id']));
  /* Opening the emailed link proves they own the address */
  $db->prepare("UPDATE users SET email_verified_at = COALESCE(email_verified_at, datetime('now')) WHERE id = ?")->execute(array($reset['id']));
  $db->prepare("UPDATE password_resets SET used_at = datetime('now') WHERE id = ?")->execute(array($reset['reset_id']));
  $db->prepare("UPDATE password_resets SET expires_at = datetime('now', '-1 second') WHERE user_id = ? AND used_at IS NULL")->execute(array($reset['id']));
  $db->prepare('DELETE FROM login_attempts WHERE email = ?')->execute(array($reset['email']));
  $db->commit();

  sign_out_other_sessions($reset['id']);
  return $reset;
}
