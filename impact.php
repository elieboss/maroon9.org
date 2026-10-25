<?php
  include 'includes/layout.php';
  layout_page_start(
    'Impact — Maroon 9',
    'See the measurable impact Maroon 9 has made in the Fort Worth community.'
  );
?>

  <!-- PAGE HERO -->
  <section class="page-hero" aria-labelledby="page-title">
    <span class="section-label">Our Numbers</span>
    <h1 class="page-hero__title" id="page-title">Our Impact</h1>
    <p class="page-hero__desc">
      Every rehearsal, every performance, and every mentorship session adds up to real change in young people's lives.
    </p>
  </section>

  <!-- IMPACT STATS -->
  <section class="impact" aria-labelledby="stats-title">
    <div class="impact__inner">
      <div>
        <span class="section-label">By the Numbers</span>
        <h2 class="section-title" id="stats-title">Making a Real Difference</h2>
        <p class="section-body">
          Since our founding, Maroon 9 has served hundreds of youth across the DFW metroplex — giving them a creative community and the skills to succeed beyond the stage.
        </p>
        <div class="impact__stats">
          <div class="impact__stat">
            <div class="impact__stat-number">500+</div>
            <div class="impact__stat-label">Youth Served</div>
          </div>
          <div class="impact__stat">
            <div class="impact__stat-number">10+</div>
            <div class="impact__stat-label">Years Active</div>
          </div>
          <div class="impact__stat">
            <div class="impact__stat-number">3</div>
            <div class="impact__stat-label">Core Programs</div>
          </div>
          <div class="impact__stat">
            <div class="impact__stat-number">100%</div>
            <div class="impact__stat-label">Community Driven</div>
          </div>
        </div>
      </div>
      <div>
        <img src="images/maroon6.png" alt="Maroon 9 youth ensemble in kente-print costumes singing together on stage" class="impact__image" />
      </div>
    </div>
  </section>

  <!-- CTA BANNER -->
  <section class="cta-banner" aria-label="Call to Action">
    <div class="cta-banner__inner">
      <h2 class="cta-banner__title">Help Us Grow Our Impact</h2>
      <p class="cta-banner__desc">
        Your support directly funds programs, mentors, and performance opportunities for Fort Worth youth.
      </p>
      <div class="cta-banner__actions">
        <a href="give.php" class="btn btn-maroon">Donate Today</a>
        <a href="kidsactingwithpurpose.php" class="btn btn-outline">Explore Programs</a>
      </div>
    </div>
  </section>

<?php layout_footer(); ?>
