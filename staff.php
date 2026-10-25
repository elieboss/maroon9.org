<?php
  include 'includes/layout.php';
  require_staff_page();

  layout_page_start(
    'Staff Dashboard — Maroon 9',
    'Maroon 9 staff dashboard.'
  );
?>

  <section class="page-hero page-hero--compact" aria-labelledby="page-title">
    <span class="section-label">Staff Only</span>
    <h1 class="page-hero__title" id="page-title">Staff Dashboard</h1>
  </section>

  <section class="content-section" aria-label="Staff dashboard">
    <div class="content-section__inner content-section__inner--wide">
      <div data-island="staff-dashboard" data-props="<?= island_props(array('me' => (int) current_user()['id'])) ?>">
        <noscript><p class="notice">The staff dashboard needs JavaScript. Please enable it and reload the page.</p></noscript>
        <p class="loading">Loading dashboard…</p>
      </div>
    </div>
  </section>

<?php layout_footer(); ?>
