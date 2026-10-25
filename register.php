<?php
  include 'includes/layout.php';

  $next = safe_next($_GET['next'] ?? $_POST['next'] ?? 'account.php');
  if (current_user()) { header('Location: ' . $next); exit; }

  /* Plain-form sign-up (JavaScript off). React posts to api/auth.php instead. */
  $error  = null;
  $fields = array();
  $name   = '';
  $email  = '';
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim(mb_substr((string) ($_POST['name'] ?? ''), 0, 100));
    $email    = trim(mb_substr((string) ($_POST['email'] ?? ''), 0, 200));
    $password = (string) ($_POST['password'] ?? '');

    if (!csrf_valid($_POST['csrf'] ?? '')) {
      $error = 'Your session expired. Please try again.';
    } else {
      $result = register_user($name, $email, $password);
      if (isset($result['user'])) { header('Location: ' . $next); exit; }
      $fields = $result['fields'];
      $error  = 'Please fix the highlighted fields.';
    }
  }

  layout_page_start(
    'Create an Account — Maroon 9',
    'Create a Maroon 9 account to register for events, enroll a youth, and volunteer.'
  );
?>

  <section class="auth-section" aria-labelledby="page-title">
    <div class="auth-card">
      <span class="section-label">Join the Community</span>
      <h1 class="section-title" id="page-title">Create an Account</h1>
      <p class="auth-card__intro">One account for event registration, youth enrollment, and volunteering.</p>

      <div data-island="auth-form" data-props="<?= island_props(array('mode' => 'register', 'next' => $next)) ?>">
<?php if ($error): ?>
        <div class="notice notice--error" role="alert"><?= e($error) ?></div>
<?php endif; ?>
        <form action="register.php" method="post">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
          <input type="hidden" name="next" value="<?= e($next) ?>" />
<?php foreach (array('name' => array('Full Name', 'text', 'name', $name), 'email' => array('Email Address', 'email', 'email', $email), 'password' => array('Password (8+ characters)', 'password', 'new-password', '')) as $key => $f): ?>
          <div class="form-group">
            <label for="register-<?= $key ?>"><?= $f[0] ?></label>
            <input type="<?= $f[1] ?>" id="register-<?= $key ?>" name="<?= $key ?>" autocomplete="<?= $f[2] ?>" value="<?= e($f[3]) ?>" required />
<?php if (isset($fields[$key])): ?><p class="field-error"><?= e($fields[$key]) ?></p><?php endif; ?>
          </div>
<?php endforeach; ?>
          <button type="submit" class="btn btn-maroon btn-block">Create Account</button>
        </form>
      </div>

      <p class="auth-card__switch">Already have an account? <a href="login.php?next=<?= e(urlencode($next)) ?>">Log in</a></p>
    </div>
  </section>

<?php layout_footer(); ?>
