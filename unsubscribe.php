<?php
  include 'includes/layout.php';
  require_once 'includes/newsletter.php';

  /* Link from the newsletter welcome email. Unsubscribing takes a button
     press (POST), so email scanners that pre-open links can't unsubscribe people. */
  $token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
  $done = null;
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valid($_POST['csrf'] ?? '')) {
    $done = newsletter_unsubscribe($token) ?? false;
  }

  layout_page_start('Unsubscribe — Maroon 9', 'Unsubscribe from the Maroon 9 newsletter.');
?>

  <section class="auth-section" aria-labelledby="page-title">
    <div class="auth-card">
      <span class="section-label">Newsletter</span>
<?php if ($done): ?>
      <h1 class="section-title" id="page-title">You’re Unsubscribed</h1>
      <p class="auth-card__intro"><strong><?= e($done) ?></strong> won’t receive the Maroon 9 newsletter anymore. You can sign up again anytime from the bottom of any page.</p>
      <a href="index.php" class="btn btn-maroon btn-block">Back to Home</a>
<?php elseif ($done === false): ?>
      <h1 class="section-title" id="page-title">Link Not Valid</h1>
      <p class="auth-card__intro">This unsubscribe link didn’t work. It may be from an older email. Please <a href="contact.php">contact us</a> and we’ll remove you right away.</p>
<?php else: ?>
      <h1 class="section-title" id="page-title">Unsubscribe?</h1>
      <p class="auth-card__intro">You’ll stop receiving Maroon 9 news, program and event updates.</p>
      <form action="unsubscribe.php" method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
        <input type="hidden" name="token" value="<?= e($token) ?>" />
        <button type="submit" class="btn btn-maroon btn-block">Unsubscribe</button>
      </form>
      <p class="auth-card__switch"><a href="index.php">Keep me subscribed</a></p>
<?php endif; ?>
    </div>
  </section>

<?php layout_footer(); ?>
