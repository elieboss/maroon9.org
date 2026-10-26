<?php
/**
 * api/contact.php
 * POST { name, email, reason, message }   anyone; linked to the account when logged in
 */

require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/contact-data.php';

$input = api_input();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed.', 405);

$result = contact_submit($input);
if (isset($result['error'])) json_error($result['error'], $result['status'], $result['fields'] ?? null);
json_out(with_email_status(array('ok' => true), str_field($input, 'email')), 201);
