<?php
  include 'includes/layout.php';

  $next = safe_next($_GET['next'] ?? $_POST['next'] ?? 'account.php');
  if (current_user()) { header('Location: ' . $next); exit; }

  /* Plain-form login (JavaScript off). React posts to api/auth.php instead. */
  $error = null;
  $email = '';
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if (!csrf_valid($_POST['csrf'] ?? '')) {
      $error = 'Your session expired. Please try again.';
    } elseif (login_locked_out($email)) {
      $error = 'Too many failed attempts. Wait ' . M9_LOGIN_WINDOW_MIN . ' minutes and try again.';
    } elseif (attempt_login($email, $password)) {
      header('Location: ' . $next); exit;
    } else {
      $error = 'That email and password don’t match an account.';
    }
  }

  layout_page_start(
    'Log In — Maroon 9',
    'Log in to your Maroon 9 account to register for events, enroll a youth, and volunteer.'
  );
?>

  <section class="auth-section" aria-labelledby="page-title">
    <div class="auth-card">
      <span class="section-label">Welcome Back</span>
      <h1 class="section-title" id="page-title">Log In</h1>
      <p class="auth-card__intro">Register for events, enroll a youth, and manage your volunteer sign-up.</p>
<?php if (isset($_GET['reset'])): ?>
      <div class="notice notice--success" role="status">Your password was changed. Log in with your new password.</div>
<?php endif; ?>

      <div data-island="auth-form" data-props="<?= island_props(array('mode' => 'login', 'next' => $next)) ?>">
<?php if ($error): ?>
        <div class="notice notice--error" role="alert"><?= e($error) ?></div>
<?php endif; ?>
        <form action="login.php" method="post">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
          <input type="hidden" name="next" value="<?= e($next) ?>" />
          <div class="form-group">
            <label for="login-email">Email Address</label>
            <input type="email" id="login-email" name="email" autocomplete="email" value="<?= e($email) ?>" required />
          </div>
          <div class="form-group">
            <label for="login-password">Password</label>
            <input type="password" id="login-password" name="password" autocomplete="current-password" required />
          </div>
          <button type="submit" class="btn btn-maroon btn-block">Log In</button>
        </form>
      </div>

      <p class="auth-card__forgot"><a href="forgot-password.php">Forgot your password?</a></p>
      <p class="auth-card__switch">New to Maroon 9? <a href="register.php?next=<?= e(urlencode($next)) ?>">Create an account</a></p>
      <p class="auth-card__switch auth-card__switch--small">Maroon 9 team member? <a href="staff-login.php">Staff login</a></p>
    </div>
  </section>

<?php layout_footer(); ?>
