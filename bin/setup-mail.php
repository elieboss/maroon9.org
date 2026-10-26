<?php
/**
 * setup-mail.php
 * Saves the Gmail settings the site uses to send confirmation and
 * notification emails. Run from the project folder:  php bin/setup-mail.php
 *
 * You need a Gmail *app password* (not your normal password):
 *   1. Turn on 2-Step Verification for the Google account.
 *   2. Visit https://myaccount.google.com/apppasswords and create one named "Maroon 9 site".
 *   3. Paste the 16-character password here when asked.
 * The password is stored only in includes/mail-config.php, which is blocked
 * from the web and excluded from git.
 */

if (PHP_SAPI !== 'cli') exit("Run this from the terminal.\n");
require_once __DIR__ . '/../includes/mail.php';

function ask($label, $default = '', $hidden = false) {
  echo $label . ($default !== '' ? " [$default]" : '') . ': ';
  if ($hidden) system('stty -echo');
  $value = trim((string) fgets(STDIN));
  if ($hidden) { system('stty echo'); echo "\n"; }
  return $value === '' ? $default : $value;
}

$current = mail_config();
$account = ask('Gmail address that sends the emails', $current['username']);
$notify  = ask('Inbox that receives "form completed" notifications', $current['notify_to']);
/* App passwords are exactly 16 letters (Google shows them in groups of 4).
   Anything else is usually the normal account password, which Gmail refuses. */
for ($try = 1; ; $try++) {
  $password = preg_replace('/[\s\x{00A0}\x{2000}-\x{200B}]+/u', '', ask('Gmail app password (16 letters, hidden)', '', true));
  if ($password === '') exit("No password entered; nothing saved.\n");
  if (preg_match('/^[a-z]{16}$/i', $password)) break;
  echo "That's " . strlen($password) . " characters" . (preg_match('/[^a-z]/i', $password) ? ', including numbers or symbols' : '') . ".\n";
  echo "A Gmail app password is exactly 16 letters (no numbers), e.g. abcd efgh ijkl mnop.\n";
  echo "Create one at https://myaccount.google.com/apppasswords (2-Step Verification must be on).\n";
  if ($try >= 3) exit("Nothing saved.\n");
}
$siteUrl = ask('Public site address for email links (leave blank for local testing)', $current['site_url']);

$config = array(
  'transport'  => 'smtp',
  'host'       => 'smtp.gmail.com',
  'port'       => 587,
  'encryption' => 'tls',
  'username'   => $account,
  'password'   => $password,
  'from_email' => $account,
  'from_name'  => 'Maroon 9',
  'notify_to'  => $notify,
  'site_url'   => $siteUrl,
);
$file = __DIR__ . '/../includes/mail-config.php';
file_put_contents($file, "<?php\n// Created by bin/setup-mail.php. Keep private: contains the Gmail app password.\nreturn " . var_export($config, true) . ";\n");
chmod($file, 0600);
echo "Saved includes/mail-config.php.\n";

if (strtolower(ask('Send a test email now? (y/n)', 'y')) === 'y') {
  $status = send_mail_batch(array(array(
    'to' => $notify, 'subject' => 'Maroon 9 website email test',
    'body' => "Email sending is working. Sign-up confirmations and form notifications will arrive here.",
  )));
  if ($status === 'sent') {
    echo "Test email sent to $notify. Check that inbox (and spam).\n";
  } else {
    $log = @file(dirname(M9_DB_PATH) . '/mail.log', FILE_IGNORE_NEW_LINES) ?: array();
    $reason = '';
    foreach (array_reverse($log) as $line) { if (strpos($line, 'FAILED:') !== false) { $reason = substr($line, strpos($line, 'FAILED:') + 8); break; } }
    echo "Sending failed.\n  Reason: " . ($reason ?: 'unknown (see data/mail.log)') . "\n";
    if (strpos($reason, '534') !== false) echo "  Fix: Gmail wants an app password, not the normal account password.\n";
    if (strpos($reason, '535') !== false) echo "  Fix: Gmail didn't accept this address + app password. Make sure the app password\n       was created while signed in as $account, then run this again.\n";
    echo "  The settings were saved; run php bin/setup-mail.php again to replace them.\n";
  }
}
