<?php
  include 'includes/layout.php';

  /* Landing page for the link in the sign-up confirmation email */
  $name = confirm_email_token($_GET['token'] ?? '');

  layout_page_start(
    'Confirm Your Email — Maroon 9',
    'Confirm the email address for your Maroon 9 account.'
  );
?>

  <section class="auth-section" aria-labelledby="page-title">
    <div class="auth-card">
<?php if ($name !== null): ?>
      <span class="section-label">All Set</span>
      <h1 class="section-title" id="page-title">Email Confirmed</h1>
      <p class="auth-card__intro">Thanks, <?= e(strtok($name, ' ')) ?>! Your email address is confirmed.</p>
      <a href="<?= current_user() ? 'account.php' : 'login.php' ?>" class="btn btn-maroon btn-block"><?= current_user() ? 'Go to My Account' : 'Log In' ?></a>
<?php else: ?>
      <span class="section-label">Link Not Valid</span>
      <h1 class="section-title" id="page-title">Couldn’t Confirm</h1>
      <p class="auth-card__intro">This confirmation link has expired or was already used. Log in and choose <strong>Resend confirmation email</strong> on your account page to get a new one.</p>
      <a href="account.php" class="btn btn-maroon btn-block">Go to My Account</a>
<?php endif; ?>
    </div>
  </section>

<?php layout_footer(); ?>
