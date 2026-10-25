<?php
/**
 * contact-data.php
 * Validates and stores a contact-form message. Used by api/contact.php
 * (React form) and contact.php (plain form when JavaScript is off).
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/notify.php';

function contact_reasons() {
  return array('' => 'Select one...', 'enroll' => 'Enroll a Youth', 'volunteer' => 'Volunteer',
    'donate' => 'Donate', 'sponsor' => 'Become a Sponsor', 'other' => 'Other');
}

/* Returns array('ok' => true) or array('error' => ..., 'status' => ..., 'fields' => ...) */
function contact_submit($input) {
  $get = function ($key, $max, $multiline = false) use ($input) {
    $v = isset($input[$key]) && is_scalar($input[$key]) ? trim((string) $input[$key]) : '';
    return mb_substr($multiline ? $v : single_line($v), 0, $max);
  };
  $name    = $get('name', 100);
  $email   = $get('email', 200);
  $reason  = $get('reason', 20);
  $message = $get('message', 5000, true);

  $fields = array();
  if ($name === '') $fields['name'] = 'Enter your full name.';
  if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) $fields['email'] = 'Enter a valid email address.';
  if (!array_key_exists($reason, contact_reasons())) $fields['reason'] = 'Choose a reason.';
  if (mb_strlen($message) < 10) $fields['message'] = 'Tell us a little more (at least 10 characters).';
  if ($fields) return array('error' => 'Please fix the highlighted fields.', 'status' => 422, 'fields' => $fields);

  /* Simple flood guard: at most 5 messages per address per hour */
  $recent = db()->prepare("SELECT COUNT(*) FROM contact_messages WHERE email = ? AND created_at > datetime('now', '-1 hour')");
  $recent->execute(array($email));
  if ((int) $recent->fetchColumn() >= 5) {
    return array('error' => 'You’ve sent several messages recently. Please wait a bit before sending another.', 'status' => 429);
  }

  $user = current_user();
  db()->prepare('INSERT INTO contact_messages (user_id, name, email, reason, message) VALUES (?, ?, ?, ?, ?)')
      ->execute(array($user ? $user['id'] : null, $name, $email, $reason, $message));

  notify_form_completed('contact form', $name, $email,
    array('Reason' => $reason === '' ? 'General' : contact_reasons()[$reason], 'Message' => $message),
    'Thanks for reaching out to Maroon 9! Our team will reply to this email address soon.',
    'staff.php?tab=messages');
  return array('ok' => true);
}
