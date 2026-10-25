<?php
/**
 * create-staff.php
 * Create a staff account, or promote an existing account to staff.
 * Run from the project folder:  php bin/create-staff.php
 */

if (PHP_SAPI !== 'cli') exit("Run this from the terminal.\n");

require_once __DIR__ . '/../includes/db.php';

function ask($label, $hidden = false) {
  echo $label;
  if ($hidden) system('stty -echo');
  $value = trim((string) fgets(STDIN));
  if ($hidden) { system('stty echo'); echo "\n"; }
  return $value;
}

$email = ask('Staff email: ');
if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) exit("That isn't a valid email.\n");

$existing = db()->prepare('SELECT id, name FROM users WHERE email = ?');
$existing->execute(array($email));
$user = $existing->fetch();

if ($user) {
  db()->prepare("UPDATE users SET role = 'staff', email_verified_at = COALESCE(email_verified_at, datetime('now')) WHERE id = ?")->execute(array($user['id']));
  exit("{$user['name']} ({$email}) is now staff.\n");
}

$name = ask('Full name: ');
if ($name === '') exit("A name is required.\n");

$password = ask('Password (8+ characters, hidden): ', true);
if (strlen($password) < 8) exit("Password must be at least 8 characters.\n");
if (ask('Confirm password: ', true) !== $password) exit("Passwords don't match.\n");

db()->prepare("INSERT INTO users (name, email, password_hash, role, email_verified_at) VALUES (?, ?, ?, 'staff', datetime('now'))")
    ->execute(array($name, $email, password_hash($password, PASSWORD_DEFAULT)));
echo "Staff account created for {$name} ({$email}).\n";
