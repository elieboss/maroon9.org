<?php
  include 'includes/layout.php';
  require_login_page();

  layout_page_start(
    'My Account — Maroon 9',
    'Manage your Maroon 9 profile, event registrations, youth enrollments, and volunteer sign-up.'
  );
  $user = current_user();
?>

  <section class="page-hero page-hero--compact" aria-labelledby="page-title">
    <span class="section-label">My Account</span>
    <h1 class="page-hero__title" id="page-title">Welcome, <?= e(strtok($user['name'], ' ')) ?></h1>
  </section>

  <section class="content-section" aria-label="Account dashboard">
    <div class="content-section__inner">
<?php if (isset($_GET['denied'])): ?>
      <div class="notice notice--error" role="alert">That page is for Maroon 9 staff only.</div>
<?php endif; ?>
      <div data-island="account-dashboard" data-props="<?= island_props(array('tab' => $_GET['tab'] ?? 'overview')) ?>">
        <noscript><p class="notice">Your account dashboard needs JavaScript. Please enable it and reload the page.</p></noscript>
        <p class="loading">Loading your account…</p>
      </div>
    </div>
  </section>

<?php layout_footer(); ?>
