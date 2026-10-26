<?php
  include 'includes/layout.php';
  require_once 'includes/password-reset.php';

  /* Step 1 of resetting a password: ask for the account email */
  $staff = isset($_GET['staff']) || isset($_POST['staff']);
  $sent  = false;
  $error = null;
  $email = '';
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    if (!csrf_valid($_POST['csrf'] ?? '')) {
      $error = 'Your session expired. Please try again.';
    } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
      $error = 'Enter the email address you use to log in.';
    } elseif (request_password_reset($email) === 'limit') {
      $error = 'Too many reset requests from this connection. Please wait an hour and try again.';
    } else {
      $sent = true;
    }
  }

  layout_page_start('Reset Your Password — Maroon 9', 'Get a link to reset your Maroon 9 account password.');
?>

  <section class="auth-section" aria-labelledby="page-title">
    <div class="auth-card">
      <span class="section-label">Account Help</span>
      <h1 class="section-title" id="page-title">Forgot Your Password?</h1>
<?php if ($sent): ?>
      <div class="notice notice--success" role="status">
        If an account exists for <strong><?= e($email) ?></strong>, we’ve sent a link to reset its password. Check your inbox (and spam folder). The link expires in <?= M9_RESET_MINUTES ?> minutes.
      </div>
      <p class="auth-card__switch"><a href="<?= $staff ? 'staff-login.php' : 'login.php' ?>">Back to log in</a></p>
<?php else: ?>
      <p class="auth-card__intro">Enter the email address for your account and we’ll send you a link to choose a new password.</p>
<?php if ($error): ?>
      <div class="notice notice--error" role="alert"><?= e($error) ?></div>
<?php endif; ?>
      <form action="forgot-password.php" method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
<?php if ($staff): ?>
        <input type="hidden" name="staff" value="1" />
<?php endif; ?>
        <div class="form-group">
          <label for="reset-email">Email Address</label>
          <input type="email" id="reset-email" name="email" autocomplete="email" value="<?= e($email) ?>" required autofocus />
        </div>
        <button type="submit" class="btn btn-maroon btn-block">Send Reset Link</button>
      </form>
      <p class="auth-card__switch">Remembered it? <a href="<?= $staff ? 'staff-login.php' : 'login.php' ?>">Back to log in</a></p>
<?php endif; ?>
    </div>
  </section>

<?php layout_footer(); ?>
