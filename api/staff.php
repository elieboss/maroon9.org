<?php
/**
 * api/staff.php  (staff only)
 * GET                          messages, users, event registrations, enrollments, volunteers
 * POST action=message_status   { id, status: new|handled }
 * POST action=enrollment_status { id, status: received|contacted|enrolled }
 * POST action=set_role         { id, role: user|staff }
 */

require_once __DIR__ . '/../includes/api.php';

$input = api_input();
$staff = api_require_staff();

function staff_overview() {
  $db = db();
  return array(
    'messages' => $db->query('SELECT id, name, email, reason, message, status, created_at FROM contact_messages ORDER BY status = \'handled\', created_at DESC')->fetchAll(),
    'users' => $db->query('
      SELECT u.id, u.name, u.email, u.role, u.created_at, u.email_verified_at IS NOT NULL AS email_verified,
        (SELECT COUNT(*) FROM event_registrations r WHERE r.user_id = u.id) AS registrations,
        (SELECT COUNT(*) FROM enrollments n WHERE n.user_id = u.id) AS enrollments,
        EXISTS (SELECT 1 FROM volunteers v WHERE v.user_id = u.id) AS volunteer
      FROM users u ORDER BY u.role = \'user\', u.name
    ')->fetchAll(),
    'registrations' => $db->query('
      SELECT r.id, r.guests, r.created_at, e.title, e.event_date, u.name, u.email
      FROM event_registrations r JOIN events e ON e.id = r.event_id JOIN users u ON u.id = r.user_id
      ORDER BY e.event_date DESC, u.name
    ')->fetchAll(),
    'enrollments' => $db->query('
      SELECT n.id, n.youth_name, n.youth_age, n.program, n.notes, n.status, n.created_at, u.name AS parent_name, u.email
      FROM enrollments n JOIN users u ON u.id = n.user_id ORDER BY n.created_at DESC
    ')->fetchAll(),
    'volunteers' => $db->query('
      SELECT v.id, v.interests, v.availability, v.notes, v.updated_at, u.name, u.email
      FROM volunteers v JOIN users u ON u.id = v.user_id ORDER BY v.updated_at DESC
    ')->fetchAll(),
    'subscribers' => $db->query('SELECT id, email, status, created_at, updated_at FROM newsletter_subscribers ORDER BY status = \'unsubscribed\', created_at DESC')->fetchAll(),
    'programs' => m9_programs(),
  );
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') json_out(staff_overview());

switch (str_field($input, 'action', 30)) {
  case 'message_status':
    $status = str_field($input, 'status', 10);
    if (!in_array($status, array('new', 'handled'), true)) json_error('Unknown status.');
    db()->prepare('UPDATE contact_messages SET status = ? WHERE id = ?')->execute(array($status, (int) ($input['id'] ?? 0)));
    json_out(staff_overview());

  case 'enrollment_status':
    $status = str_field($input, 'status', 10);
    if (!in_array($status, array('received', 'contacted', 'enrolled'), true)) json_error('Unknown status.');
    db()->prepare('UPDATE enrollments SET status = ? WHERE id = ?')->execute(array($status, (int) ($input['id'] ?? 0)));
    json_out(staff_overview());

  case 'set_role':
    $id   = (int) ($input['id'] ?? 0);
    $role = str_field($input, 'role', 10);
    if (!in_array($role, array('user', 'staff'), true)) json_error('Unknown role.');

    /* Never let staff lock themselves (or everyone) out of the dashboard */
    if ($id === (int) $staff['id'] && $role !== 'staff') json_error('You can’t remove your own staff access.', 409);

    db()->prepare('UPDATE users SET role = ? WHERE id = ?')->execute(array($role, $id));
    json_out(staff_overview());

  default:
    json_error('Unknown action.');
}
