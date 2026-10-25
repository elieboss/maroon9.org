<?php
/**
 * api/account.php  (logged-in users)
 * GET                        profile, event registrations, enrollments, volunteer info, sent messages
 * POST action=profile        { name, email }
 * POST action=password       { current_password, new_password }
 * POST action=enroll         { youth_name, youth_age, program, notes }
 * POST action=withdraw_enrollment { id }
 * POST action=volunteer      { interests, availability, notes }
 * POST action=withdraw_volunteer
 * POST action=resend_verification
 */

require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/notify.php';

$input = api_input();
$user  = api_require_login();
$uid   = (int) $user['id'];

function account_summary($uid) {
  $db = db();

  $regs = $db->prepare('
    SELECT r.id, r.guests, r.created_at, e.id AS event_id, e.title, e.event_date, e.location
    FROM event_registrations r JOIN events e ON e.id = r.event_id
    WHERE r.user_id = ? ORDER BY e.event_date
  ');
  $regs->execute(array($uid));

  $enroll = $db->prepare('SELECT id, youth_name, youth_age, program, notes, status, created_at FROM enrollments WHERE user_id = ? ORDER BY created_at DESC');
  $enroll->execute(array($uid));

  $vol = $db->prepare('SELECT interests, availability, notes, updated_at FROM volunteers WHERE user_id = ?');
  $vol->execute(array($uid));

  $msgs = $db->prepare('SELECT id, reason, message, status, created_at FROM contact_messages WHERE user_id = ? ORDER BY created_at DESC');
  $msgs->execute(array($uid));

  $user = current_user(true);
  return array(
    'user'          => public_user($user) + array('created_at' => $user['created_at']),
    'registrations' => $regs->fetchAll(),
    'enrollments'   => $enroll->fetchAll(),
    'volunteer'     => $vol->fetch() ?: null,
    'messages'      => $msgs->fetchAll(),
    'programs'      => m9_programs(),
  );
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') json_out(account_summary($uid));

switch (str_field($input, 'action', 30)) {
  case 'profile':
    $name  = str_field($input, 'name', 100);
    $email = str_field($input, 'email');
    $fields = array();
    if ($name === '') $fields['name'] = 'Enter your name.';
    if (!valid_email($email)) $fields['email'] = 'Enter a valid email address.';
    if (!$fields) {
      $taken = db()->prepare('SELECT 1 FROM users WHERE email = ? AND id <> ?');
      $taken->execute(array($email, $uid));
      if ($taken->fetchColumn()) $fields['email'] = 'Another account already uses this email.';
    }
    if ($fields) json_error('Please fix the highlighted fields.', 422, $fields);

    $emailChanged = strcasecmp($email, $user['email']) !== 0;
    db()->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?')->execute(array($name, $email, $uid));
    if ($emailChanged) {
      db()->prepare('UPDATE users SET email_verified_at = NULL WHERE id = ?')->execute(array($uid));
      notify_verification(array('name' => $name, 'email' => $email), create_email_token($uid));
    }
    json_out(account_summary($uid));

  case 'password':
    $current = isset($input['current_password']) && is_string($input['current_password']) ? $input['current_password'] : '';
    $new     = isset($input['new_password']) && is_string($input['new_password']) ? $input['new_password'] : '';

    $row = db()->prepare('SELECT password_hash FROM users WHERE id = ?');
    $row->execute(array($uid));
    if (!password_verify($current, $row->fetchColumn())) {
      json_error('Please fix the highlighted fields.', 422, array('current_password' => 'Your current password is incorrect.'));
    }
    if (!valid_password($new)) {
      json_error('Please fix the highlighted fields.', 422, array('new_password' => 'Use at least 8 characters.'));
    }

    db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute(array(password_hash($new, PASSWORD_DEFAULT), $uid));
    log_in_user($uid);   // new session ID after a credential change
    $summary = account_summary($uid);
    $summary['csrf'] = csrf_token();
    json_out($summary);

  case 'enroll':
    $youthName = str_field($input, 'youth_name', 100);
    $youthAge  = isset($input['youth_age']) ? (int) $input['youth_age'] : 0;
    $program   = str_field($input, 'program', 50);
    $notes     = str_field($input, 'notes', 2000, true);

    $fields = array();
    if ($youthName === '') $fields['youth_name'] = 'Enter the youth’s name.';
    if ($youthAge < 5 || $youthAge > 17) $fields['youth_age'] = 'Programs serve ages 5–17.';
    if (!array_key_exists($program, m9_programs())) $fields['program'] = 'Choose a program.';
    if ($fields) json_error('Please fix the highlighted fields.', 422, $fields);

    db()->prepare('INSERT INTO enrollments (user_id, youth_name, youth_age, program, notes) VALUES (?, ?, ?, ?, ?)')
        ->execute(array($uid, $youthName, $youthAge, $program, $notes));
    notify_form_completed('youth enrollment', $user['name'], $user['email'],
      array('Youth' => $youthName, 'Age' => $youthAge, 'Program' => m9_programs()[$program], 'Notes' => $notes),
      "Thanks for enrolling $youthName! Our team will contact you about schedules and next steps.",
      'staff.php?tab=signups');
    json_out(account_summary($uid), 201);

  case 'withdraw_enrollment':
    db()->prepare("DELETE FROM enrollments WHERE id = ? AND user_id = ? AND status = 'received'")
        ->execute(array((int) ($input['id'] ?? 0), $uid));
    json_out(account_summary($uid));

  case 'volunteer':
    $interests    = str_field($input, 'interests', 500);
    $availability = str_field($input, 'availability', 500);
    $notes        = str_field($input, 'notes', 2000, true);
    if ($interests === '') json_error('Please fix the highlighted fields.', 422, array('interests' => 'Choose at least one way to help.'));

    db()->prepare("
      INSERT INTO volunteers (user_id, interests, availability, notes) VALUES (?, ?, ?, ?)
      ON CONFLICT (user_id) DO UPDATE SET interests = excluded.interests, availability = excluded.availability,
        notes = excluded.notes, updated_at = datetime('now')
    ")->execute(array($uid, $interests, $availability, $notes));
    notify_form_completed('volunteer sign-up', $user['name'], $user['email'],
      array('Interests' => $interests, 'Availability' => $availability, 'Notes' => $notes),
      'Thank you for volunteering with Maroon 9! Our team will reach out with opportunities that fit your interests.',
      'staff.php?tab=signups');
    json_out(account_summary($uid));

  case 'resend_verification':
    $row = db()->prepare("SELECT email_verified_at, email_token_sent_at > datetime('now', '-2 minutes') AS recent FROM users WHERE id = ?");
    $row->execute(array($uid));
    $state = $row->fetch();
    if ($state['email_verified_at']) json_error('Your email is already confirmed.', 409);
    if ($state['recent']) json_error('We just sent a confirmation email. Please wait a couple of minutes before asking again.', 429);
    notify_verification($user, create_email_token($uid));
    json_out(account_summary($uid));

  case 'withdraw_volunteer':
    db()->prepare('DELETE FROM volunteers WHERE user_id = ?')->execute(array($uid));
    json_out(account_summary($uid));

  default:
    json_error('Unknown action.');
}
