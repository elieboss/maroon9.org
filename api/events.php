<?php
/**
 * api/events.php
 * GET                         all events (with the current user's registration status)
 * POST action=register        { event_id, guests }        logged-in users
 * POST action=unregister      { event_id }                logged-in users
 * POST action=save            { id?, title, event_date, location, description, cta_label }  staff
 * POST action=delete          { id }                      staff
 */

require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/notify.php';

$input = api_input();

/* Shared with events.php so the no-JavaScript page and React see the same data */
require_once __DIR__ . '/../includes/events-data.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') json_out(array('events' => events_list(current_user())));

$action = str_field($input, 'action', 20);

if ($action === 'register' || $action === 'unregister') {
  $user    = api_require_login();
  $eventId = (int) ($input['event_id'] ?? 0);

  $event = db()->prepare('SELECT title, event_date, location FROM events WHERE id = ?');
  $event->execute(array($eventId));
  $event = $event->fetch();
  if ($event === false) json_error('That event no longer exists.', 404);
  $date = $event['event_date'];

  if ($action === 'register') {
    if ($date < date('Y-m-d')) json_error('Registration has closed for this event.', 409);
    $guests = max(0, min(10, (int) ($input['guests'] ?? 0)));
    db()->prepare('
      INSERT INTO event_registrations (event_id, user_id, guests) VALUES (?, ?, ?)
      ON CONFLICT (event_id, user_id) DO UPDATE SET guests = excluded.guests
    ')->execute(array($eventId, $user['id'], $guests));
    notify_form_completed('event registration', $user['name'], $user['email'],
      array('Event' => $event['title'], 'Date' => date('l, F j, Y', strtotime($date)), 'Location' => $event['location'], 'Guests' => (string) $guests),
      "You're registered for {$event['title']}. We can't wait to see you there!",
      'staff.php?tab=signups');
  } else {
    db()->prepare('DELETE FROM event_registrations WHERE event_id = ? AND user_id = ?')->execute(array($eventId, $user['id']));
  }
  $out = array('events' => events_list($user));
  json_out($action === 'register' ? with_email_status($out, $user['email']) : $out);
}

$user = api_require_staff();

if ($action === 'save') {
  $id   = (int) ($input['id'] ?? 0);
  $data = array(
    str_field($input, 'title', 150),
    str_field($input, 'event_date', 10),
    str_field($input, 'location', 200),
    str_field($input, 'description', 2000, true),
    str_field($input, 'cta_label', 40) ?: 'Register',
  );

  $fields = array();
  if ($data[0] === '') $fields['title'] = 'Enter a title.';
  if (!valid_date($data[1])) $fields['event_date'] = 'Choose a date.';
  if ($fields) json_error('Please fix the highlighted fields.', 422, $fields);

  if ($id > 0) {
    $data[] = $id;
    db()->prepare('UPDATE events SET title = ?, event_date = ?, location = ?, description = ?, cta_label = ? WHERE id = ?')->execute($data);
  } else {
    db()->prepare('INSERT INTO events (title, event_date, location, description, cta_label) VALUES (?, ?, ?, ?, ?)')->execute($data);
  }
  json_out(array('events' => events_list($user)));
}

if ($action === 'delete') {
  db()->prepare('DELETE FROM events WHERE id = ?')->execute(array((int) ($input['id'] ?? 0)));
  json_out(array('events' => events_list($user)));
}

json_error('Unknown action.');
