<?php
/**
 * newsletter.php
 * Footer newsletter sign-up. Used by api/newsletter.php (React form) and
 * newsletter-signup.php (plain form when JavaScript is off).
 * Subscribing requires an explicit "yes, subscribe me" checkbox.
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/notify.php';

/* Returns array('status' => 'subscribed' | 'already') on success,
   or array('error' => ..., 'status_code' => ..., 'fields' => ...) */
function newsletter_subscribe($email, $consent) {
  $email = single_line(mb_substr((string) $email, 0, 200));

  $fields = array();
  if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) $fields['email'] = 'Enter a valid email address.';
  if (!$consent) $fields['consent'] = 'Please check the box to confirm you’d like to subscribe.';
  if ($fields) return array('error' => 'Please fix the highlighted fields.', 'status_code' => 422, 'fields' => $fields);

  /* Flood guard: at most 5 sign-ups from one address per hour */
  $recent = db()->prepare("SELECT COUNT(*) FROM newsletter_subscribers WHERE ip = ? AND updated_at > datetime('now', '-1 hour')");
  $recent->execute(array(client_ip()));
  if ((int) $recent->fetchColumn() >= 5) {
    return array('error' => 'Too many sign-ups from this connection. Please try again later.', 'status_code' => 429);
  }

  $existing = db()->prepare('SELECT id, status, token FROM newsletter_subscribers WHERE email = ?');
  $existing->execute(array($email));
  $row = $existing->fetch();
  if ($row && $row['status'] === 'active') return array('status' => 'already');

  $token = bin2hex(random_bytes(24));
  if ($row) {
    db()->prepare("UPDATE newsletter_subscribers SET status = 'active', token = ?, ip = ?, updated_at = datetime('now') WHERE id = ?")
        ->execute(array($token, client_ip(), $row['id']));
  } else {
    db()->prepare('INSERT INTO newsletter_subscribers (email, token, ip) VALUES (?, ?, ?)')
        ->execute(array($email, $token, client_ip()));
  }

  notify_newsletter_signup($email, $token);
  return array('status' => 'subscribed');
}

/* Unsubscribe link from the welcome email. Returns the email, or null if the link is invalid. */
function newsletter_unsubscribe($token) {
  if (!is_string($token) || !preg_match('/^[0-9a-f]{48}$/', $token)) return null;
  $stmt = db()->prepare('SELECT id, email FROM newsletter_subscribers WHERE token = ?');
  $stmt->execute(array($token));
  $row = $stmt->fetch();
  if (!$row) return null;
  db()->prepare("UPDATE newsletter_subscribers SET status = 'unsubscribed', updated_at = datetime('now') WHERE id = ?")
      ->execute(array($row['id']));
  return $row['email'];
}
