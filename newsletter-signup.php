<?php
/**
 * newsletter-signup.php
 * Plain-form handler for the footer newsletter sign-up (JavaScript off).
 * Sends the visitor back to the page they came from with the result.
 */
require_once 'includes/newsletter.php';

$back = safe_next(basename((string) ($_POST['back'] ?? 'index.php')));
$back = $back === 'account.php' ? 'index.php' : $back;   // safe_next's default isn't a public page

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid($_POST['csrf'] ?? '')) {
  header("Location: $back?newsletter=expired#newsletter"); exit;
}

$result = newsletter_subscribe($_POST['email'] ?? '', !empty($_POST['consent']));
$state = isset($result['error'])
  ? (isset($result['fields']['consent']) && !isset($result['fields']['email']) ? 'consent' : (isset($result['fields']) ? 'invalid' : 'limit'))
  : $result['status'];
header("Location: $back?newsletter=$state#newsletter");
