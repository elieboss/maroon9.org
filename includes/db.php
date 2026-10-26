<?php
/**
 * db.php
 * SQLite connection for Maroon 9. The database file lives in data/,
 * which is blocked from the web (see router.php and data/.htaccess).
 * Tables are created on first use; the original six events are seeded
 * so the Events page is never empty on a fresh install.
 */

/* M9_DB_PATH can point tests or a second copy of the site at another database file */
define('M9_DB_PATH', getenv('M9_DB_PATH') ?: __DIR__ . '/../data/maroon9.sqlite');

/* Event dates are compared against "today" in Fort Worth */
date_default_timezone_set('America/Chicago');

function db() {
  static $pdo = null;
  if ($pdo !== null) return $pdo;

  $dir = dirname(M9_DB_PATH);
  if (!is_dir($dir)) mkdir($dir, 0700, true);

  $pdo = new PDO('sqlite:' . M9_DB_PATH);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
  $pdo->exec('PRAGMA foreign_keys = ON');
  $pdo->exec('PRAGMA journal_mode = WAL');

  db_migrate($pdo);
  return $pdo;
}

function db_migrate(PDO $pdo) {
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
      id            INTEGER PRIMARY KEY AUTOINCREMENT,
      name          TEXT NOT NULL,
      email         TEXT NOT NULL UNIQUE COLLATE NOCASE,
      password_hash TEXT NOT NULL,
      role          TEXT NOT NULL DEFAULT 'user' CHECK (role IN ('user', 'staff')),
      created_at    TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS login_attempts (
      id           INTEGER PRIMARY KEY AUTOINCREMENT,
      email        TEXT NOT NULL COLLATE NOCASE,
      ip           TEXT NOT NULL,
      attempted_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS contact_messages (
      id         INTEGER PRIMARY KEY AUTOINCREMENT,
      user_id    INTEGER REFERENCES users(id) ON DELETE SET NULL,
      name       TEXT NOT NULL,
      email      TEXT NOT NULL,
      reason     TEXT NOT NULL DEFAULT '',
      message    TEXT NOT NULL,
      status     TEXT NOT NULL DEFAULT 'new' CHECK (status IN ('new', 'handled')),
      created_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS events (
      id          INTEGER PRIMARY KEY AUTOINCREMENT,
      title       TEXT NOT NULL,
      event_date  TEXT NOT NULL,
      location    TEXT NOT NULL DEFAULT '',
      description TEXT NOT NULL DEFAULT '',
      cta_label   TEXT NOT NULL DEFAULT 'Register',
      created_at  TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS event_registrations (
      id         INTEGER PRIMARY KEY AUTOINCREMENT,
      event_id   INTEGER NOT NULL REFERENCES events(id) ON DELETE CASCADE,
      user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
      guests     INTEGER NOT NULL DEFAULT 0,
      created_at TEXT NOT NULL DEFAULT (datetime('now')),
      UNIQUE (event_id, user_id)
    );

    CREATE TABLE IF NOT EXISTS enrollments (
      id         INTEGER PRIMARY KEY AUTOINCREMENT,
      user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
      youth_name TEXT NOT NULL,
      youth_age  INTEGER NOT NULL,
      program    TEXT NOT NULL,
      notes      TEXT NOT NULL DEFAULT '',
      status     TEXT NOT NULL DEFAULT 'received' CHECK (status IN ('received', 'contacted', 'enrolled')),
      created_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS volunteers (
      id           INTEGER PRIMARY KEY AUTOINCREMENT,
      user_id      INTEGER NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
      interests    TEXT NOT NULL DEFAULT '',
      availability TEXT NOT NULL DEFAULT '',
      notes        TEXT NOT NULL DEFAULT '',
      updated_at   TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS password_resets (
      id         INTEGER PRIMARY KEY AUTOINCREMENT,
      user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
      token_hash TEXT NOT NULL UNIQUE,
      ip         TEXT NOT NULL DEFAULT '',
      expires_at TEXT NOT NULL,
      used_at    TEXT,
      created_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS newsletter_subscribers (
      id           INTEGER PRIMARY KEY AUTOINCREMENT,
      email        TEXT NOT NULL UNIQUE COLLATE NOCASE,
      status       TEXT NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'unsubscribed')),
      token        TEXT NOT NULL,
      ip           TEXT NOT NULL DEFAULT '',
      created_at   TEXT NOT NULL DEFAULT (datetime('now')),
      updated_at   TEXT NOT NULL DEFAULT (datetime('now'))
    );
  ");

  /* Columns added after the first release: email confirmation */
  $columns = array_column($pdo->query('PRAGMA table_info(users)')->fetchAll(), 'name');
  foreach (array('email_verified_at' => 'TEXT', 'email_token_hash' => 'TEXT', 'email_token_sent_at' => 'TEXT',
                 'session_version' => 'INTEGER NOT NULL DEFAULT 0') as $col => $type) {
    if (!in_array($col, $columns, true)) $pdo->exec("ALTER TABLE users ADD COLUMN $col $type");
  }

  /* Seed the events that were previously hard-coded in events.php */
  if ((int) $pdo->query('SELECT COUNT(*) FROM events')->fetchColumn() === 0) {
    $seed = $pdo->prepare('INSERT INTO events (title, event_date, location, cta_label) VALUES (?, ?, ?, ?)');
    $events = array(
      array('KAWP Spring Showcase',          '2025-06-14', 'Tarrant County Performing Arts Center, Fort Worth', 'Reserve a Seat'),
      array('Sickle Cell Awareness Walk',    '2025-07-22', 'Panther Island Pavilion, Fort Worth',              'Register to Walk'),
      array('Page to Stage Reading Festival','2025-08-09', 'Fort Worth Public Library, Main Branch',           'Learn More'),
      array('KAWP Fall Enrollment Day',      '2025-09-18', 'Maroon 9 Headquarters, Fort Worth',                'Sign Up'),
      array('Community Arts Night',          '2025-10-04', 'Diamond Hill Community Center, Fort Worth',        'RSVP'),
      array('Annual Gala & Fundraiser',      '2025-11-15', 'Fort Worth Convention Center',                     'Buy Tickets'),
    );
    foreach ($events as $e) $seed->execute($e);
  }
}

/* Programs a youth can be enrolled in (shared by the API and the forms) */
function m9_programs() {
  return array(
    'kawp-actor-training' => 'KAWP Actor Training Program',
    'kawp-traveling-troupe' => 'KAWP Traveling Troupe',
    'in-school' => 'In-School Programs',
    'private-lessons' => 'Private Lessons & Group Classes',
    'conflict-resolution' => 'Conflict Resolution Through Theatre™',
  );
}
