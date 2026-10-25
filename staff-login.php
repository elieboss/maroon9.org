<?php
  include 'includes/layout.php';

  /* Staff-only login. Only accounts with role = 'staff' can sign in here;
     a regular user's correct password is refused and does not log them in. */
  if (is_staff()) { header('Location: staff.php'); exit; }

  $error = null;
  $email = '';
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if (!csrf_valid($_POST['csrf'] ?? '')) {
      $error = 'Your session expired. Please try again.';
    } elseif (login_locked_out($email)) {
      $error = 'Too many failed attempts. Wait ' . M9_LOGIN_WINDOW_MIN . ' minutes and try again.';
    } else {
      $result = attempt_login($email, $password, true);
      if ($result) { header('Location: staff.php'); exit; }
      $error = $result === false
        ? 'This login is for Maroon 9 staff only. Please use the regular log in page.'
        : 'That email and password don’t match a staff account.';
    }
  }

  layout_page_start(
    'Staff Login — Maroon 9',
    'Sign in to the Maroon 9 staff dashboard.'
  );
?>

  <section class="auth-section auth-section--staff" aria-labelledby="page-title">
    <div class="auth-card auth-card--staff">
      <span class="section-label">Staff Only</span>
      <h1 class="section-title" id="page-title">Staff Login</h1>
      <p class="auth-card__intro">For Maroon 9 team members. Manage messages, events, sign-ups and accounts.</p>

      <div data-island="auth-form" data-props="<?= island_props(array('mode' => 'staff_login', 'next' => 'staff.php')) ?>">
<?php if ($error): ?>
        <div class="notice notice--error" role="alert"><?= e($error) ?></div>
<?php endif; ?>
        <form action="staff-login.php" method="post">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
          <div class="form-group">
            <label for="staff-email">Staff Email</label>
            <input type="email" id="staff-email" name="email" autocomplete="username" value="<?= e($email) ?>" required />
          </div>
          <div class="form-group">
            <label for="staff-password">Password</label>
            <input type="password" id="staff-password" name="password" autocomplete="current-password" required />
          </div>
          <button type="submit" class="btn btn-maroon btn-block">Log In to Staff Dashboard</button>
        </form>
      </div>

      <p class="auth-card__switch">Not on staff? <a href="login.php">Use the regular log in</a></p>
    </div>
  </section>

<?php layout_footer(); ?>
