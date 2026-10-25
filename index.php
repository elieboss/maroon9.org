<?php
  include 'includes/layout.php';
  layout_page_start(
    'Maroon 9 Community Enrichment Organization',
    'Maroon 9 prepares Fort Worth youth for life beyond the stage through theatre-based learning, conflict resolution, arts education, and sickle cell advocacy.'
  );
?>

  <!-- HERO SECTION -->
  <section class="hero" aria-label="Hero">
    <div class="hero__bg" role="img" aria-label="Youth performing on stage"></div>
    <div class="hero__content">
      <span class="hero__tag">Fort Worth, TX &middot; Youth Arts &amp; Advocacy</span>
      <h1 class="hero__title">Preparing Youth for Life Beyond the Stage</h1>
      <p class="hero__desc">
        Maroon 9 is a <strong>Tarrant County, Texas-based nonprofit organization</strong> that inspires, empowers, and develops youth through the transformative power of the arts. Through theatre-based learning, youth development, conflict resolution, creative workforce exposure, and community engagement, we create opportunities for young people to discover their voice, build confidence, develop leadership skills, explore creative careers, and prepare for their future.
      </p>
      <p class="hero__desc">
        <strong>Our work extends beyond the stage.</strong> We use theatre and creativity as tools to strengthen youth, connect learning to real-world opportunities, and build stronger families, schools, and communities.
      </p>
      <div class="hero__actions">
        <a href="kidsactingwithpurpose.php" class="btn btn-primary">Explore Our Programs</a>
        <a href="events.php" class="btn btn-outline">Upcoming Events</a>
        <a href="give.php" class="btn btn-outline">Give</a>
      </div>
    </div>
  </section>

  <!-- MEET MAROON 9 (VIDEO) -->
  <section class="content-section video-section" id="meet-maroon-9" aria-labelledby="video-title">
    <div class="content-section__inner">
      <span class="section-label">Watch</span>
      <h2 class="section-title" id="video-title">Meet Maroon 9</h2>
      <p class="section-body">Building community, one program at a time. Hear from the youth and families who make Maroon 9 what it is.</p>
      <div class="video-frame">
        <video controls playsinline preload="none" poster="images/maroon9-intro-poster.jpg" aria-describedby="video-title">
          <source src="images/Maroon9%20(1).mp4" type="video/mp4" />
          Your browser can't play this video. <a href="images/Maroon9%20(1).mp4">Download the Meet Maroon 9 video</a>.
        </video>
      </div>
    </div>
  </section>

  <!-- WHO WE WORK FOR (React: PeopleMarquee scrolls the portraits; this grid is the no-JS fallback) -->
<?php $students = array_map(function ($i) { return "images/people-web/people$i.jpg"; }, range(1, 25)); ?>
  <section class="who-we-serve" aria-labelledby="who-title">
    <div class="who-we-serve__inner">
      <span class="section-label">Our Students</span>
      <h2 class="section-title section-title--white" id="who-title">Who We Work For</h2>
      <p class="section-body">The young artists, leaders, and creatives at the heart of everything we do.</p>
    </div>
    <div data-island="people-marquee" data-props="<?= island_props(array('photos' => $students)) ?>">
      <ul class="people-grid" aria-label="Portraits of Maroon 9 students">
<?php foreach ($students as $src): ?>
        <li><img src="<?= e($src) ?>" alt="" loading="lazy" /></li>
<?php endforeach; ?>
      </ul>
    </div>
  </section>

  <!-- WHAT WE DO -->
  <section class="programs" id="what-we-do" aria-labelledby="what-we-do-title">
    <div class="programs__inner">

      <div class="programs__header">
        <div>
          <span class="section-label">How We Show Up</span>
          <h2 class="section-title" id="what-we-do-title">What We Do</h2>
        </div>
      </div>

      <div class="feature-list">

        <div class="feature-block">
          <div class="feature-block__title">Youth Development Through the Arts</div>
          <div class="feature-block__desc">
            <p>Maroon 9 provides <strong>year-round community theatre programs for youth ages 5&ndash;17</strong> throughout Dallas&ndash;Fort Worth, often at no cost through partnerships with schools, districts, community centers, and local organizations.</p>
            <p>Our signature <strong>Kids Acting With Purpose (KAWP)</strong> program takes youth beyond theatre training through performances, field trips, mentorship, creative workforce exploration, and paid opportunities&mdash;building confidence, leadership, and skills for life beyond the stage.</p>
          </div>
          <a href="kidsactingwithpurpose.php" class="feature-block__link">Explore KAWP</a>
        </div>

        <div class="feature-block">
          <div class="feature-block__title">Conflict Resolution Through Theatre&trade;</div>
          <div class="feature-block__desc">
            <p>Our signature theatre-based curriculum equips youth with practical tools to navigate conflict safely and responsibly. Through the <strong>Know Your W.O.R.T.H. framework</strong>, real-life stories, role-playing, and interactive theatre, participants learn five safe, nonviolent conflict de-escalation strategies while strengthening communication, decision-making, and problem-solving skills.</p>
          </div>
          <a href="conflictresolutionthroughtheatre.php" class="feature-block__link">Learn More</a>
        </div>

        <div class="feature-block">
          <div class="feature-block__title">Arts Education Services</div>
          <div class="feature-block__desc">
            <p>Through <strong>Page To Stage Arts Education Services</strong>, our social enterprise, we provide schools and community organizations with curriculum, professional development, theatre workshops and master classes, plays, and performance booking services. <strong>Revenue generated through Page To Stage is reinvested into Maroon 9&rsquo;s mission</strong>, helping expand access to youth arts programming and opportunities throughout the community.</p>
          </div>
          <a href="pagetostage.php" class="feature-block__link">See Our Services</a>
        </div>

        <div class="feature-block">
          <div class="feature-block__title">Sickle Cell Advocacy</div>
          <div class="feature-block__desc">
            <p><strong>Maroon9 Sickle Cell Stars&reg;</strong> empowers youth living with Sickle Cell Disease through care, creativity, and celebration while increasing community awareness and connecting families to support.</p>
          </div>
          <a href="sicklecellstars.php" class="feature-block__link">Meet Sickle Cell Stars</a>
        </div>

      </div>
    </div>
  </section>

  <!-- OUR FUTURE HOME TEASER -->
  <section class="about" id="future-home" aria-labelledby="future-home-title">
    <div class="about__inner">
      <div class="about__image-wrap">
        <img src="images/thehub.jpg" alt="Rendering of the future Maroon 9 Youth Arts &amp; Creative Workforce Hub at dusk, with string lights, rooftop terraces and a youth arts mural on its side wall" class="about__image about__image--landscape" />
        <div class="about__image-badge">
          <strong>Coming</strong>
          Soon
        </div>
      </div>
      <div>
        <span class="section-label">More Than a Building</span>
        <h2 class="section-title section-title--white" id="future-home-title">We&rsquo;re Building Our Future Home</h2>
        <p class="section-body">
          Maroon 9 is working toward the development of <strong>The Maroon9 Youth Arts &amp; Creative Workforce Hub</strong>, our vision for a permanent youth arts and creative workforce facility where young people can create, perform, learn, lead, and prepare for creative careers.
        </p>
        <a href="hub.php" class="btn btn-primary">Explore The Hub</a>
      </div>
    </div>
  </section>

  <!-- BE PART OF THE IMPACT -->
  <section class="cta-banner" aria-label="Call to Action">
    <div class="cta-banner__inner">
      <h2 class="cta-banner__title">Be Part of the Impact</h2>
      <p class="cta-banner__desc">
        Whether you participate in a program, attend an event, partner with us, volunteer, or make a financial contribution, there is a place for you in the Maroon 9 community.
      </p>
      <div class="cta-banner__actions">
        <a href="getinvolved.php" class="btn btn-maroon">Get Involved</a>
        <a href="give.php" class="btn btn-outline">Give</a>
      </div>
    </div>
  </section>

<?php layout_footer(); ?>
