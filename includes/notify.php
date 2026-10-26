<?php
/**
 * notify.php
 * The emails the site sends. Every completed form sends:
 *   1. a receipt to the person who filled it in, and
 *   2. a "form completed" notification to the Maroon 9 inbox (notify_to in mail-config).
 * Sign-up additionally asks the new user to confirm their email address.
 */

require_once __DIR__ . '/mail.php';

function email_lines($fields) {
  $lines = array();
  foreach ($fields as $label => $value) {
    if ($value === '' || $value === null) continue;
    $lines[] = "$label: $value";
  }
  return implode("\n", $lines);
}

function email_footer() {
  return "\n\n—\nMaroon 9 Community Enrichment Organization\nFort Worth, Texas\n" . site_url('index.php');
}

/* New account: confirmation link to the user + notification to staff inbox */
function notify_signup($user, $token) {
  $link = site_url('verify-email.php?token=' . $token);
  $first = strtok($user['name'], ' ');
  send_mail_batch(array(
    array(
      'to'      => $user['email'],
      'subject' => 'Confirm your Maroon 9 account',
      'body'    => "Hi $first,\n\nThanks for creating a Maroon 9 account! Please confirm your email address by opening this link:\n\n$link\n\nThe link expires in 48 hours. If you didn't create this account, you can ignore this email." . email_footer(),
    ),
    array(
      'to'       => mail_config()['notify_to'],
      'reply_to' => $user['email'],
      'subject'  => "New account: {$user['name']}",
      'body'     => "A new account was created on the Maroon 9 website.\n\n" . email_lines(array(
        'Name' => $user['name'], 'Email' => $user['email'], 'Created' => date('M j, Y g:i A'),
      )) . "\n\nManage accounts: " . site_url('staff.php?tab=users'),
    ),
  ));
}

/* Resent or changed-email confirmation (no staff notification) */
function notify_verification($user, $token) {
  $link = site_url('verify-email.php?token=' . $token);
  send_mail_batch(array(array(
    'to'      => $user['email'],
    'subject' => 'Confirm your email for Maroon 9',
    'body'    => 'Hi ' . strtok($user['name'], ' ') . ",\n\nPlease confirm this email address for your Maroon 9 account:\n\n$link\n\nThe link expires in 48 hours." . email_footer(),
  )));
}

/**
 * A form was completed.
 * $form: short name shown in the subject, e.g. "Contact form"
 * $fields: label => value pairs included in both emails
 * $receipt: first paragraph of the email the submitter receives
 * $staffLink: dashboard page where staff can follow up
 */
function notify_form_completed($form, $name, $email, $fields, $receipt, $staffLink) {
  $details = email_lines($fields);
  send_mail_batch(array(
    array(
      'to'      => $email,
      'subject' => "We received your $form — Maroon 9",
      'body'    => 'Hi ' . strtok($name, ' ') . ",\n\n$receipt\n\nHere's what you sent:\n\n$details" . email_footer(),
    ),
    array(
      'to'       => mail_config()['notify_to'],
      'reply_to' => $email,
      'subject'  => "Form completed: $form from $name",
      'body'     => "Someone completed the $form on the Maroon 9 website.\n\n" . email_lines(array('Name' => $name, 'Email' => $email)) . "\n$details\n\nFollow up: " . site_url($staffLink),
    ),
  ));
}

/* Newsletter sign-up: welcome email with an unsubscribe link + staff notification */
function notify_newsletter_signup($email, $token) {
  $unsubscribe = site_url('unsubscribe.php?token=' . $token);
  send_mail_batch(array(
    array(
      'to'      => $email,
      'subject' => 'You’re subscribed to the Maroon 9 newsletter',
      'body'    => "Hi there,\n\nThanks for signing up! You'll receive updates about Maroon 9 news, programs and events.\n\nIf you didn't sign up, or change your mind, you can unsubscribe anytime:\n$unsubscribe" . email_footer(),
    ),
    array(
      'to'       => mail_config()['notify_to'],
      'reply_to' => $email,
      'subject'  => "Form completed: newsletter sign-up from $email",
      'body'     => "Someone signed up for the newsletter on the Maroon 9 website.\n\n" . email_lines(array('Email' => $email, 'Date' => date('M j, Y g:i A'))) . "\n\nSubscriber list: " . site_url('staff.php?tab=signups'),
    ),
  ));
}

/* "Forgot your password?" email (sent only to the account owner) */
function notify_password_reset($user, $token) {
  $link = site_url('reset-password.php?token=' . $token);
  send_mail_batch(array(array(
    'to'      => $user['email'],
    'subject' => 'Reset your Maroon 9 password',
    'body'    => 'Hi ' . strtok($user['name'], ' ') . ",\n\nWe received a request to reset the password for your Maroon 9 account. Choose a new password here:\n\n$link\n\nThis link works once and expires in " . M9_RESET_MINUTES . " minutes. If you didn't ask to reset your password, you can ignore this email; your password won't change." . email_footer(),
  )));
}
