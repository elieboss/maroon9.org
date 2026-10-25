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
$password = str_replace(' ', '', ask('Gmail app password (16 characters, hidden)', '', true));
if ($password === '') exit("No password entered; nothing saved.\n");
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
  echo $status === 'sent' ? "Test email sent to $notify.\n" : "Sending failed. See data/mail.log for the reason.\n";
}
