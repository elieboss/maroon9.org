<?php
/**
 * api/newsletter.php
 * POST { email, consent: true }   anyone
 */

require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/newsletter.php';

$input = api_input();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed.', 405);

$result = newsletter_subscribe(str_field($input, 'email'), !empty($input['consent']));
if (isset($result['error'])) json_error($result['error'], $result['status_code'], $result['fields'] ?? null);
json_out(array('status' => $result['status']), $result['status'] === 'subscribed' ? 201 : 200);
