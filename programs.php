<?php
  include 'includes/layout.php';
  layout_page_start(
    'Programs — Maroon 9',
    'Explore Maroon 9\'s youth programs including KAWP, Page to Stage, and Sickle Cell Stars.'
  );
?>

  <!-- PAGE HERO -->
  <section class="page-hero" aria-labelledby="page-title">
    <span class="section-label">What We Offer</span>
    <h1 class="page-hero__title" id="page-title">Our Programs</h1>
    <p class="page-hero__desc">
      Three signature programs that give Fort Worth youth the tools to grow, perform, and lead.
    </p>
  </section>

  <!-- ALL PROGRAMS -->
  <section class="programs" aria-labelledby="programs-list-title">
    <div class="programs__inner">
      <div class="programs__header">
        <div>
          <span class="section-label">All Programs</span>
          <h2 class="section-title" id="programs-list-title">What We Offer</h2>
        </div>
        <a href="contact.php" class="btn btn-maroon">Enroll or Get Involved</a>
      </div>
      <div class="programs__grid">

        <article class="program-card" id="in-school">
          <img src="images/kidsinblue.avif" alt="In-School Programs — youth in classroom theatre workshop" class="program-card__image" />
          <div class="program-card__body">
            <div class="program-card__tag">In-School &bull; Free</div>
            <h3 class="program-card__title">In-School Programs</h3>
            <p class="program-card__desc">
              Our In-School Programs bring theatre arts directly into classrooms through acting, singing, dancing, and media arts. Curriculum-aligned and led by experienced teaching artists, these sessions build creativity, communication, and collaboration skills — giving students tools they can use on stage and in everyday life.
            </p>
            <a href="contact.php" class="program-card__link">Bring Us to Your School</a>
          </div>
        </article>

        <article class="program-card" id="kawp">
          <img src="images/Bone Soup Meme 4.avif" alt="KAWP Youth Performance Group — Bone Soup production" class="program-card__image" />
          <div class="program-card__body">
            <div class="program-card__tag">Performance &bull; Free</div>
            <h3 class="program-card__title">KAWP Youth Performance Group</h3>
            <p class="program-card__desc">
              Open to youth ages 9–17, this ensemble develops acting, singing, and dancing skills through original productions celebrating cultural heritage and community. Members have performed in productions like <em>Harriet: Take My Hand</em> and <em>Bone Soup: A Kwanzaa Story</em>, building confidence and artistic discipline with every show.
            </p>
            <a href="https://www.kidsactingwithpurpose.org/" target="_blank" rel="noopener" class="program-card__link">Visit KAWP</a>
          </div>
        </article>

        <article class="program-card" id="lessons">
          <img src="images/Empowering Young Creators.webp" alt="Private Lessons and Group Classes" class="program-card__image" />
          <div class="program-card__body">
            <div class="program-card__tag">Lessons &bull; Free</div>
            <h3 class="program-card__title">Private Lessons &amp; Group Classes</h3>
            <p class="program-card__desc">
              Personalized one-on-one coaching and collaborative group sessions in acting, singing, dancing, and media arts. Private lessons focus on individual growth, while group classes foster teamwork and performance experience — supporting students at every skill level in a nurturing environment.
            </p>
            <a href="contact.php" class="program-card__link">Enroll Today</a>
          </div>
        </article>

        <article class="program-card" id="educator">
          <img src="images/Bone Soup Meme 19.avif" alt="Teaching artist sharing a high-five with a young performer" class="program-card__image" />
          <div class="program-card__body">
            <div class="program-card__tag">Educators &bull; Free</div>
            <h3 class="program-card__title">Educator Development</h3>
            <p class="program-card__desc">
              Hands-on workshops that equip teachers with theatre-based strategies to increase student engagement, build social-emotional skills, and foster inclusive classrooms. Practical techniques for conflict resolution, confidence-building, and teamwork — designed for educators at all experience levels.
            </p>
            <a href="contact.php" class="program-card__link">Register Your School</a>
          </div>
        </article>

      </div>
    </div>
  </section>

  <!-- PROGRAM CALENDAR -->
  <section class="content-section" id="calendar" aria-labelledby="calendar-title">
    <div class="content-section__inner">
      <span class="section-label">Stay Up to Date</span>
      <h2 class="section-title" id="calendar-title">Program Schedule</h2>
      <p class="section-body" style="margin-bottom: 32px;">
        View upcoming sessions, performances, and enrollment dates for all Maroon 9 programs.
      </p>
      <div class="calendar-wrap">
        <iframe
          src="https://calendar.google.com/calendar/embed?height=600&wkst=1&ctz=America%2FChicago&showPrint=0&src=Ym02NDA0MjFAZ21haWwuY29t&src=ZW4udXNhI2hvbGlkYXlAZ3JvdXAudi5jYWxlbmRhci5nb29nbGUuY29t&color=%23039be5&color=%230b8043"
          style="border: solid 1px #ddd;"
          width="100%"
          height="600"
          frameborder="0"
          scrolling="no"
          title="Maroon 9 Program Schedule">
        </iframe>
      </div>
    </div>
  </section>

  <!-- CTA BANNER -->
  <section class="cta-banner" aria-label="Call to Action">
    <div class="cta-banner__inner">
      <h2 class="cta-banner__title">Ready to Enroll?</h2>
      <p class="cta-banner__desc">
        Reach out today to learn more about program eligibility, schedules, and how to get started.
      </p>
      <div class="cta-banner__actions">
        <a href="account.php?tab=enroll" class="btn btn-primary">Enroll a Youth Online</a>
        <a href="contact.php" class="btn btn-maroon">Contact Us</a>
        <a href="about.php" class="btn btn-outline">Learn About Us</a>
      </div>
    </div>
  </section>

<?php layout_footer(); ?>
