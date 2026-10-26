<?php
  include 'includes/layout.php';
  require_once 'includes/password-reset.php';

  /* Step 2: the link from the email. Choose a new password. */
  $token  = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
  $reset  = find_password_reset($token);
  $fields = array();
  $error  = null;

  if ($reset && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf'] ?? '')) {
      $error = 'Your session expired. Please try again.';
    } else {
      $result = complete_password_reset($token, (string) ($_POST['password'] ?? ''), (string) ($_POST['confirm'] ?? ''));
      if (isset($result['fields'])) {
        $fields = $result['fields'];
        $error  = $fields['token'] ?? 'Please fix the highlighted fields.';
      } else {
        $login = $result['role'] === 'staff' ? 'staff-login.php' : 'login.php';
        header("Location: $login?reset=1"); exit;
      }
    }
  }

  layout_page_start('Choose a New Password — Maroon 9', 'Choose a new password for your Maroon 9 account.');
?>

  <section class="auth-section" aria-labelledby="page-title">
    <div class="auth-card">
      <span class="section-label">Account Help</span>
<?php if (!$reset): ?>
      <h1 class="section-title" id="page-title">Link Expired</h1>
      <p class="auth-card__intro">This password reset link has expired or was already used. Reset links work once and last <?= M9_RESET_MINUTES ?> minutes.</p>
      <a href="forgot-password.php" class="btn btn-maroon btn-block">Send a New Link</a>
<?php else: ?>
      <h1 class="section-title" id="page-title">Choose a New Password</h1>
      <p class="auth-card__intro">For <strong><?= e($reset['email']) ?></strong>. You’ll be signed out on any other devices.</p>
<?php if ($error): ?>
      <div class="notice notice--error" role="alert"><?= e($error) ?></div>
<?php endif; ?>
      <form action="reset-password.php" method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
        <input type="hidden" name="token" value="<?= e($token) ?>" />
        <input type="hidden" name="username" value="<?= e($reset['email']) ?>" autocomplete="username" />
        <div class="form-group<?= isset($fields['password']) ? ' has-error' : '' ?>">
          <label for="new-password">New Password</label>
          <input type="password" id="new-password" name="password" autocomplete="new-password" minlength="8" required autofocus aria-describedby="new-password-hint" />
<?php if (isset($fields['password'])): ?>
          <p class="field-error"><?= e($fields['password']) ?></p>
<?php else: ?>
          <p class="field-hint" id="new-password-hint">At least 8 characters.</p>
<?php endif; ?>
        </div>
        <div class="form-group<?= isset($fields['confirm']) ? ' has-error' : '' ?>">
          <label for="confirm-password">Confirm New Password</label>
          <input type="password" id="confirm-password" name="confirm" autocomplete="new-password" required />
<?php if (isset($fields['confirm'])): ?>
          <p class="field-error"><?= e($fields['confirm']) ?></p>
<?php endif; ?>
        </div>
        <button type="submit" class="btn btn-maroon btn-block">Save New Password</button>
      </form>
<?php endif; ?>
    </div>
  </section>

<?php layout_footer(); ?>
