<?php
  include 'includes/layout.php';

  /* Signs the visitor out. Only a POST with the session's CSRF token signs out,
     so another site can't log people out with a hidden link. If the token is
     stale (e.g. the password was changed in another tab), this page asks the
     visitor to confirm with a fresh form instead of failing. */
  if (current_user() === null) { header('Location: index.php'); exit; }

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valid($_POST['csrf'] ?? '')) {
    log_out_user();
    header('Location: index.php?signed_out=1');
    exit;
  }

  layout_page_start(
    'Sign Out — Maroon 9',
    'Sign out of your Maroon 9 account.'
  );
?>

  <section class="auth-section" aria-labelledby="page-title">
    <div class="auth-card">
      <span class="section-label">Your Account</span>
      <h1 class="section-title" id="page-title">Sign Out?</h1>
      <p class="auth-card__intro">You're signed in as <strong><?= e(current_user()['email']) ?></strong>.</p>
      <form action="logout.php" method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
        <button type="submit" class="btn btn-maroon btn-block">Sign Out</button>
      </form>
      <p class="auth-card__switch"><a href="account.php">Stay signed in and go to My Account</a></p>
    </div>
  </section>

<?php layout_footer(); ?>
