<?php
/**
 * events-data.php
 * Event queries shared by events.php (server-rendered list) and api/events.php.
 */

require_once __DIR__ . '/db.php';

/* All events, soonest first. When a user is given, each event says whether
   they are registered; staff also get the registration count. */
function events_list($user = null) {
  $rows = db()->query('
    SELECT e.*, (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = e.id) AS registrations
    FROM events e ORDER BY e.event_date
  ')->fetchAll();

  $mine = array();
  if ($user !== null) {
    $stmt = db()->prepare('SELECT event_id, guests FROM event_registrations WHERE user_id = ?');
    $stmt->execute(array($user['id']));
    foreach ($stmt->fetchAll() as $r) $mine[(int) $r['event_id']] = (int) $r['guests'];
  }

  $today = date('Y-m-d');
  $isStaff = $user !== null && $user['role'] === 'staff';

  return array_map(function ($e) use ($mine, $today, $isStaff) {
    $id = (int) $e['id'];
    $event = array(
      'id'          => $id,
      'title'       => $e['title'],
      'event_date'  => $e['event_date'],
      'location'    => $e['location'],
      'description' => $e['description'],
      'cta_label'   => $e['cta_label'],
      'is_past'     => $e['event_date'] < $today,
      'registered'  => array_key_exists($id, $mine),
      'guests'      => $mine[$id] ?? 0,
    );
    if ($isStaff) $event['registrations'] = (int) $e['registrations'];
    return $event;
  }, $rows);
}
