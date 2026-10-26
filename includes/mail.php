<?php
/**
 * mail.php
 * Sends the site's emails: sign-up confirmations, receipts to people who
 * complete a form, and notifications to the Maroon 9 inbox.
 *
 * Settings live in includes/mail-config.php (created by `php bin/setup-mail.php`,
 * never committed). Without a password configured, emails are written to
 * data/mail-log/ instead of being sent, so every flow still works locally.
 */

require_once __DIR__ . '/db.php';

function mail_config() {
  static $config = null;
  if ($config !== null) return $config;

  $defaults = array(
    'transport'   => 'smtp',              // 'smtp' or 'log'
    'host'        => 'smtp.gmail.com',
    'port'        => 587,
    'encryption'  => 'tls',               // 'tls' (STARTTLS), 'ssl', or 'none'
    'username'    => 'eliebossombo@gmail.com',
    'password'    => '',                  // Gmail app password
    'from_email'  => 'eliebossombo@gmail.com',
    'from_name'   => 'Maroon 9',
    'notify_to'   => 'eliebossombo@gmail.com',
    'site_url'    => '',                  // e.g. https://maroon9.example.org (blank = detect)
  );
  $file = getenv('M9_MAIL_CONFIG') ?: __DIR__ . '/mail-config.php';
  $config = array_merge($defaults, is_file($file) ? (array) require $file : array());
  /* On a hosting service, set these in its environment settings instead of the file */
  if (getenv('M9_SMTP_PASSWORD')) $config['password'] = getenv('M9_SMTP_PASSWORD');
  if (getenv('M9_SITE_URL')) $config['site_url'] = getenv('M9_SITE_URL');
  if ($config['password'] === '') $config['transport'] = 'log';
  return $config;
}

/* Absolute URL for links inside emails */
function site_url($path = '') {
  $base = rtrim(mail_config()['site_url'], '/');
  if ($base === '') {
    $https  = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $host   = preg_replace('/[^A-Za-z0-9.:\-\[\]]/', '', $_SERVER['HTTP_HOST'] ?? 'localhost:8080');
    $root   = realpath(__DIR__ . '/..');
    $docroot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: $root;
    $prefix = str_starts_with($root, $docroot) ? str_replace(DIRECTORY_SEPARATOR, '/', substr($root, strlen($docroot))) : '';
    $base   = ($https ? 'https' : 'http') . '://' . $host . rtrim($prefix, '/');
  }
  return $base . '/' . ltrim($path, '/');
}

/* Header values must never contain line breaks (prevents header injection) */
function mail_header_safe($value) {
  return trim(preg_replace('/[\r\n\t]+/', ' ', (string) $value));
}

function mail_encode_header($text) {
  $text = mail_header_safe($text);
  return preg_match('/[^\x20-\x7E]/', $text) ? '=?UTF-8?B?' . base64_encode($text) . '?=' : $text;
}

function mail_build($message, $config) {
  $fromEmail = mail_header_safe($config['from_email']);
  $headers = array(
    'Date: ' . date(DATE_RFC2822),
    'From: ' . mail_encode_header($config['from_name']) . " <$fromEmail>",
    'To: ' . mail_header_safe($message['to']),
    'Subject: ' . mail_encode_header($message['subject']),
    'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . substr(strrchr($fromEmail, '@'), 1) . '>',
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: base64',
  );
  if (!empty($message['reply_to']) && filter_var($message['reply_to'], FILTER_VALIDATE_EMAIL)) {
    $headers[] = 'Reply-To: ' . mail_header_safe($message['reply_to']);
  }
  return implode("\r\n", $headers) . "\r\n\r\n" . rtrim(chunk_split(base64_encode($message['body']), 76, "\r\n"));
}

/**
 * Send several messages over one connection.
 * Each message: array('to' => ..., 'subject' => ..., 'body' => ..., 'reply_to' => optional).
 * Never throws: a mail problem must not break the form the person just submitted.
 * Returns 'sent', 'logged' or 'failed'.
 */
/* Outcome of the most recent send_mail_batch(): 'sent', 'failed', 'logged' (Gmail not set up) or null */
function last_mail_status($set = null) {
  static $status = null;
  if ($set !== null) $status = $set;
  return $status;
}

function send_mail_batch($messages) {
  return last_mail_status(send_mail_batch_inner($messages));
}

function send_mail_batch_inner($messages) {
  $messages = array_values(array_filter($messages, function ($m) {
    return filter_var($m['to'] ?? '', FILTER_VALIDATE_EMAIL);
  }));
  if (!$messages) return 'sent';
  $config = mail_config();

  if ($config['transport'] === 'log') {
    foreach ($messages as $m) mail_write_log($m, $config);
    return 'logged';
  }

  try {
    $smtp = new M9Smtp($config);
    foreach ($messages as $m) $smtp->send($config['from_email'], $m['to'], mail_build($m, $config));
    $smtp->quit();
    mail_note('sent ' . count($messages) . ' message(s): ' . implode(', ', array_column($messages, 'subject')));
    return 'sent';
  } catch (Throwable $e) {
    mail_note('FAILED: ' . $e->getMessage());
    foreach ($messages as $m) mail_write_log($m, $config);   // keep a copy so nothing is lost
    return 'failed';
  }
}

/* Logs live next to the database file (data/ by default) */
function mail_write_log($message, $config) {
  $dir = dirname(M9_DB_PATH) . '/mail-log';
  if (!is_dir($dir)) mkdir($dir, 0700, true);
  $name = date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.eml';
  file_put_contents("$dir/$name", mail_build($message, $config));
  mail_note("logged '{$message['subject']}' to data/mail-log/$name");
}

function mail_note($line) {
  $dir = dirname(M9_DB_PATH);
  if (!is_dir($dir)) mkdir($dir, 0700, true);
  file_put_contents("$dir/mail.log", '[' . date('Y-m-d H:i:s') . "] $line\n", FILE_APPEND);
}

/* Minimal SMTP client (STARTTLS + AUTH LOGIN), enough for Gmail */
class M9Smtp {
  private $socket;

  public function __construct($config) {
    $prefix = $config['encryption'] === 'ssl' ? 'ssl://' : 'tcp://';
    $this->socket = @stream_socket_client($prefix . $config['host'] . ':' . (int) $config['port'], $errno, $errstr, 10);
    if (!$this->socket) throw new RuntimeException("Can't connect to {$config['host']}: $errstr");
    stream_set_timeout($this->socket, 15);

    $this->expect(220);
    $helo = gethostname() ?: 'localhost';
    $this->command("EHLO $helo", 250);
    if ($config['encryption'] === 'tls') {
      $this->command('STARTTLS', 220);
      if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        throw new RuntimeException('TLS handshake failed.');
      }
      $this->command("EHLO $helo", 250);
    }
    if ($config['username'] !== '') {
      $this->command('AUTH LOGIN', 334);
      $this->command(base64_encode($config['username']), 334);
      $this->command(base64_encode($config['password']), 235, 'Login rejected. Check the Gmail app password in includes/mail-config.php.');
    }
  }

  public function send($from, $to, $data) {
    $this->command('MAIL FROM:<' . mail_header_safe($from) . '>', 250);
    $this->command('RCPT TO:<' . mail_header_safe($to) . '>', array(250, 251));
    $this->command('DATA', 354);
    /* Dot-stuffing: a line starting with "." must be doubled */
    $data = preg_replace('/^\./m', '..', $data);
    $this->command($data . "\r\n.", 250);
  }

  public function quit() {
    try { $this->command('QUIT', 221); } catch (Throwable $e) { /* already done */ }
    fclose($this->socket);
  }

  private function command($line, $expect, $error = null) {
    fwrite($this->socket, $line . "\r\n");
    $this->expect($expect, $error);
  }

  private function expect($codes, $error = null) {
    $codes = (array) $codes;
    $reply = '';
    while (($line = fgets($this->socket, 1024)) !== false) {
      $reply .= $line;
      if (strlen($line) < 4 || $line[3] === ' ') break;   // last line of a multi-line reply
    }
    $code = (int) substr($reply, 0, 3);
    if (!in_array($code, $codes, true)) {
      /* Keep the server's own reply (e.g. Gmail's 534/535 codes): it says why */
      $server = preg_replace('/\s+/', ' ', trim($reply ?: 'no response'));
      throw new RuntimeException(($error ? "$error " : 'SMTP error. ') . "Server said: $server");
    }
  }
}
