<?php
  include 'includes/layout.php';
  layout_page_start(
    'Get Involved — Maroon 9',
    'Ways to support and engage with Maroon 9 — volunteer, partner, sponsor, and stay connected.'
  );
?>

  <!-- PAGE HERO -->
  <section class="page-hero" aria-labelledby="page-title">
    <span class="section-label">Support Maroon 9</span>
    <h1 class="page-hero__title" id="page-title">Get Involved</h1>
    <p class="page-hero__desc">
      Multiple ways to support and engage with Maroon 9 — donate, sponsor, volunteer, partner, or attend an event.
    </p>
  </section>

  <!-- INTRO -->
  <section class="content-section" aria-labelledby="gi-title">
    <div class="content-section__inner">
      <span class="section-label">Join Us</span>
      <h2 class="section-title" id="gi-title">There's a Place for You at Maroon 9</h2>
      <p class="section-body" style="max-width: 760px;">
        Creating meaningful opportunities for young people takes a community. Whether you have time, expertise, resources, or connections to share, there are many ways to be part of Maroon 9's work.
      </p>
    </div>
  </section>

  <!-- VOLUNTEER / PARTNER / SPONSOR -->
  <section class="content-section" style="background: #fff;" aria-labelledby="ways-title">
    <div class="content-section__inner">
      <h2 class="section-title" id="ways-title" style="position: absolute; left: -9999px;">Ways to Get Involved</h2>

      <div class="feature-list">
        <div class="feature-block">
          <div class="feature-block__title">Volunteer</div>
          <div class="feature-block__desc">
            <p>Share your time and talents to support programs, performances, events, and community initiatives.</p>
          </div>
          <a href="#volunteer" class="feature-block__link">Become a Volunteer</a>
        </div>

        <div class="feature-block">
          <div class="feature-block__title">Partner With Us</div>
          <div class="feature-block__desc">
            <p>We collaborate with schools and districts, businesses, nonprofits, healthcare organizations, community groups, and other partners who share our commitment to youth and community development.</p>
          </div>
          <a href="https://forms.monday.com/forms/054afb28cf58134c16be669bc9fe9b8a?r=use1&partner+network=&community+partner+form=" target="_blank" rel="noopener" class="feature-block__link">Become a Partner</a>
        </div>

        <div class="feature-block">
          <div class="feature-block__title">Sponsor</div>
          <div class="feature-block__desc">
            <p>Invest in a program, event, Sickle Cell Stars&reg; activity, or other Maroon 9 initiative through corporate or community sponsorship.</p>
          </div>
          <a href="contact.php" class="feature-block__link">Explore Sponsorship</a>
        </div>

        <div class="feature-block">
          <div class="feature-block__title">Stay Connected</div>
          <div class="feature-block__desc">
            <p>Join our community and stay informed about programs, events, opportunities, and impact.</p>
          </div>
          <div class="link-row" style="margin-top: 12px;">
            <a href="#newsletter">Subscribe to Our Newsletter</a>
            <a href="https://www.instagram.com/maroon9communityenrichment" target="_blank" rel="noopener">Follow Us</a>
            <a href="contact.php">Contact Us</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- VOLUNTEER SIGN-UP (React: VolunteerSignup; saved to the volunteer's account) -->
  <section class="content-section" id="volunteer" aria-labelledby="volunteer-title">
    <div class="content-section__inner">
      <span class="section-label">Volunteer</span>
      <h2 class="section-title" id="volunteer-title">Sign Up to Volunteer</h2>
      <p class="section-body" style="max-width: 760px;">
        Tell us how you'd like to help and when you're available. Our team will reach out with opportunities that fit.
      </p>
      <div data-island="volunteer-signup" data-props="<?= island_props(array('user' => public_user(current_user()))) ?>">
        <div class="btn-group">
<?php if (current_user()): ?>
          <a href="account.php?tab=volunteer" class="btn btn-maroon">Sign Up in My Account</a>
<?php else: ?>
          <a href="login.php?next=getinvolved.php%23volunteer" class="btn btn-maroon">Log In to Sign Up</a>
          <a href="register.php?next=getinvolved.php%23volunteer" class="btn btn-outline btn-outline--dark">Create an Account</a>
<?php endif; ?>
        </div>
      </div>
      <p class="section-note">
        Prefer not to create an account? Use our
        <a href="https://forms.monday.com/forms/054afb28cf58134c16be669bc9fe9b8a?r=use1&partner+network=&community+partner+form=" target="_blank" rel="noopener">volunteer &amp; partner form</a> instead.
      </p>
    </div>
  </section>

  <!-- CTA BANNER -->
  <section class="cta-banner" aria-label="Call to Action">
    <div class="cta-banner__inner">
      <h2 class="cta-banner__title">Ready to Get Started?</h2>
      <p class="cta-banner__desc">
        Reach out and we'll point you to the best way to support Maroon 9 right now.
      </p>
      <div class="cta-banner__actions">
        <a href="contact.php" class="btn btn-maroon">Contact Us</a>
        <a href="give.php" class="btn btn-outline">Give</a>
      </div>
    </div>
  </section>

<?php layout_footer(); ?>
