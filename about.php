<?php
  include 'includes/layout.php';
  layout_page_start(
    'About — Maroon 9',
    'Maroon 9\'s mission, vision, story, core values, leadership, and impact.'
  );
?>

  <!-- PAGE HERO -->
  <section class="page-hero" aria-labelledby="page-title">
    <span class="section-label">Our Story</span>
    <h1 class="page-hero__title" id="page-title">About Maroon 9</h1>
    <p class="page-hero__desc">
      Maroon 9 Community Enrichment Organization is a youth development organization using the transformative power of the arts to prepare young people for life beyond the stage.
    </p>
  </section>

  <!-- MISSION / VISION / STORY -->
  <section class="content-section" aria-labelledby="mission-title">
    <div class="content-section__inner">
      <span class="section-label">Why We're Here</span>
      <h2 class="section-title" id="mission-title">Our Mission</h2>
      <p class="section-body" style="margin-bottom: 40px;">
        To inspire, empower, and develop youth through the transformative power of the arts.
      </p>

      <h2 class="section-title">Our Vision</h2>
      <p class="section-body" style="margin-bottom: 40px;">
        To prepare youth for life beyond the stage.
      </p>

      <h2 class="section-title">Our Story</h2>
      <div class="story-block" style="margin-left: 0;">
        <p>
          <strong>Maroon 9 was founded in 2016 by Cynthia Nobles</strong> to honor the life and legacy of her brother, Michael Patterson, who is also the uncle of current Executive Director ShaVonne Davis.
        </p>
        <p>
          Michael lived with Sickle Cell Disease until his passing at age 49 and was deeply involved in arts and entertainment. The arts brought him joy, purpose, and an outlet that helped him navigate the challenges of living with the disease.
        </p>
        <p>
          <strong>The name &ldquo;Maroon 9&rdquo; is a tribute to Michael.</strong> Maroon is the official awareness ribbon color for Sickle Cell Disease, and the number 9 represents September, which is Sickle Cell Disease Awareness Month.
        </p>
        <p>
          Today, his legacy continues through Maroon 9 and the leadership of his family. What began at the intersection of creativity and sickle cell advocacy has grown into an organization using the arts to empower young people, strengthen communities, and create opportunities for the next generation.
        </p>
      </div>
    </div>
  </section>

  <!-- CORE VALUES -->
  <section class="about" id="values" aria-labelledby="values-title">
    <div class="about__inner" style="grid-template-columns: 1fr;">
      <div>
        <span class="section-label">What Guides Us</span>
        <h2 class="section-title section-title--white" id="values-title">Our Core Values</h2>
        <p class="section-body">
          <strong>Creativity. Empathy. Empowerment. Prevention.</strong> These values guide how we design programs, engage young people and families, build partnerships, and serve our community.
        </p>
        <div class="about__values">
          <div class="about__value">
            <div class="about__value-name">Creativity</div>
            <div class="about__value-desc">Dynamic arts-centered storytelling and expression</div>
          </div>
          <div class="about__value">
            <div class="about__value-name">Empathy</div>
            <div class="about__value-desc">Authentic stories and participant voices at the center</div>
          </div>
          <div class="about__value">
            <div class="about__value-name">Empowerment</div>
            <div class="about__value-desc">Leadership, confidence, and growth opportunities</div>
          </div>
          <div class="about__value">
            <div class="about__value-name">Prevention</div>
            <div class="about__value-desc">Education and awareness around Sickle Cell Disease</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- OUR GOALS -->
  <section class="content-section goals" id="goals" aria-labelledby="goals-title">
    <div class="content-section__inner">
      <span class="section-label">Our Goals</span>
      <h2 class="section-title" id="goals-title">2025-2027 Strategic Goals</h2>
      <ol class="goal-grid">
<?php
  $goals = array(
    array('Provide Comprehensive Arts Education', 'Offer accessible and affordable opportunities for youth from diverse backgrounds and abilities to participate in high-quality arts enrichment activities.'),
    array('Promote Positive Youth Development', 'Teach youth transferable skills that support various career pathways and extend beyond life after high school.'),
    array('Advocacy for Prevention', 'Actively advocate for the role of creative expression in preventing juvenile crime through awareness campaigns, partnerships, and community outreach.'),
    array('Community Collaboration', 'Collaborate with local organizations to extend the reach of our programs, promote community involvement, and educate the community on important topics.'),
  );
  foreach ($goals as $i => list($title, $text)):
?>
        <li class="goal-card">
          <span class="goal-card__number" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span>
          <h3 class="goal-card__title"><?= e($title) ?></h3>
          <p class="goal-card__text"><?= e($text) ?></p>
        </li>
<?php endforeach; ?>
      </ol>
    </div>
  </section>

  <!-- OUR PEOPLE -->
  <section class="content-section" id="people" aria-labelledby="people-title">
    <div class="content-section__inner">
      <span class="section-label">Who Makes This Possible</span>
      <h2 class="section-title" id="people-title">Our People</h2>
      <p class="section-body">
        Maroon 9's work is made possible by committed staff, board members, teaching artists, volunteers, partners, and community supporters who believe in the potential of young people.
      </p>

      <div class="link-row" role="list">
        <a href="#leadership">Meet Our Leadership</a>
        <a href="#board">Board of Directors</a>
        <a href="#partners">Community Partners</a>
      </div>

      <div class="leader-grid" id="leadership">
        <div class="leader-card">
          <img src="images/ShaVonne Headshot 2.avif" alt="ShaVonne Davis, Executive Director of Maroon 9" class="leader-card__photo" />
          <div>
            <div class="leader-card__name">ShaVonne Davis</div>
            <div class="leader-card__role">Executive Director</div>
            <div class="leader-card__bio">
              <p>
                ShaVonne Davis is a social entrepreneur, theatre educator, director, and arts consultant from Fort Worth, Texas, with more than 20 years of experience using theatre as a tool for youth development, literacy, and community impact. She is a proud graduate of Clark Atlanta University, where she earned a Bachelor of Arts in Theatre Arts.
              </p>
              <p>
                Mrs. Davis is the founder of <strong>Acting With Mrs. Davis</strong>, a theatre arts enrichment company providing workshops, residencies, and performance-based learning experiences for K&ndash;12 youth in schools, theatres, afterschool programs, and community settings. She also serves as Executive Director of <strong>Maroon 9 Community Enrichment Organization</strong>, a nonprofit founded by her mother and rooted in a mission to inspire, empower, and develop youth through the transformative power of the arts.
              </p>
              <p>
                Under her leadership, Maroon 9 has delivered <strong>more than 950 hours of enrichment programming</strong>, served <strong>over 3,000 youth</strong>, and partnered with more than 65 schools and community organizations across Texas through theatre arts literacy programs, conflict resolution workshops, youth performances, and educator training. She is currently leading the development of the Maroon 9 Youth Arts &amp; Creative Workforce Hub, a future cultural arts space focused on youth arts access, creative workforce development, and community engagement in Fort Worth.
              </p>
              <p>
                In 2021, her work received national recognition through the <strong>$10,000 @BeyGOOD Small Business Impact Grant</strong> presented by Beyonc&eacute; and the NAACP. In 2025, Davis received the <strong>Arts Fort Worth Arts Advocate of the Year Award</strong>. Her work has been featured by WFAA Channel 8, ABC13 Houston, NBC 5 DFW, Fox 4, and Localish.
              </p>
              <p class="leader-card__contact">
                Email: <a href="mailto:sdavis@maroon9.org">sdavis@maroon9.org</a>
              </p>
            </div>
          </div>
        </div>
      </div>

      <div id="board" style="margin-top: 48px;">
        <h3 class="feature-block__title">Board of Directors</h3>
        <ul class="board-grid">
<?php
  $board = array(
    array('Dr. DeAnne Young', 'President', 'Dr_ DeAnne Young_heic.avif'),
    array('Cynthia Nobles', 'Vice President & Founder', 'cynthia headshot.avif'),
    array('LaWanda Gates', 'Treasurer', 'lawanda gates.avif'),
    array('Justin Davis', 'Secretary', 'justindavis.avif'),
    array('Linda Sayles', 'Board Member', 'Linda sayles_edited_edited.avif'),
    array('Melvin Lee', 'Board Member', 'melvinlee.avif'),
  );
  foreach ($board as list($name, $role, $photo)):
?>
          <li class="board-card">
            <img src="images/<?= e($photo) ?>" alt="<?= e("$name, $role of Maroon 9") ?>" class="board-card__photo" loading="lazy" />
            <div class="board-card__name"><?= e($name) ?></div>
            <div class="board-card__role"><?= e($role) ?></div>
          </li>
<?php endforeach; ?>
        </ul>
      </div>

      <!-- COMMUNITY PARTNERS (React: SponsorMarquee scrolls the logos; tap one to show its colors) -->
      <div id="partners" class="partners">
        <h3 class="feature-block__title">Community Partners</h3>
        <p class="section-body">We're proud to work alongside schools, districts, foundations, and community organizations across Tarrant County. Maroon 9's programs are made possible by these generous partners.</p>
<?php
    $sponsors = array(
      array('name' => 'Braver Together', 'logo' => 'images/Braver Logo 2_edited.avif'),
      array('name' => 'Arts Fort Worth', 'logo' => 'images/ARTS FW.avif'),
      array('name' => 'North Texas Community Foundation', 'logo' => 'images/NTFC_Fund_Logo_FARE_Primary - Full Color.avif'),
      array('name' => 'United Way of Tarrant County', 'logo' => 'images/unitedway.avif'),
      array('name' => 'Sid W. Richardson Foundation', 'logo' => 'images/SID richardson .avif'),
      array('name' => 'H-E-B', 'logo' => 'images/HEB LOGO_edited.avif'),
      array('name' => 'Fort Worth Tourism Public Improvement District', 'logo' => 'images/FWTPID LOGO.avif'),
      array('name' => 'Big Thought', 'logo' => 'images/Big Thought.avif'),
    );
?>
        <div data-island="sponsor-marquee" data-props="<?= island_props(array('sponsors' => $sponsors)) ?>">
          <ul class="sponsors__grid">
<?php foreach ($sponsors as $s): ?>
            <li class="sponsor-logo"><img src="<?= e($s['logo']) ?>" alt="<?= e($s['name']) ?>" loading="lazy" /></li>
<?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- OUR IMPACT (narrative) -->
  <section class="content-section our-impact" id="our-impact" aria-labelledby="our-impact-title">
    <div class="content-section__inner our-impact__inner">
      <div class="our-impact__text">
        <h2 class="section-title" id="our-impact-title">Our Impact</h2>
        <p class="our-impact__lead">
          Since its inception, Maroon 9 Community Enrichment Organization has made measurable impacts through dedicated arts and enrichment programs that support youth, families, and communities in Fort Worth and beyond. We focus on providing high-quality after-school and summer programs in Fine Arts, STEM, and Life Skills for underserved youth, empowering them to achieve academic, personal, and career success. By creating safe, structured environments for creativity and self-expression, our programs have shown positive results in improving school attendance, academic performance, and behavior, while also reducing the likelihood of involvement in crime and violence.
        </p>
        <p>
          Through consistent financial support from grants, donors, and community sponsors, Maroon 9 has maintained its mission of offering programs at no cost to families. Each year, our financial statements and impact reports reflect responsible stewardship of funds, prioritizing program expansion, quality enrichment materials, and support services. Our impact reports highlight achievements such as increased school engagement, reduced disciplinary actions, and career exploration in fields like performing arts and technology. With over 40 partnerships with organizations and professionals, our programs provide youth with skills and resources that shape their futures and uplift our community.
        </p>
        <p>
          Maroon 9 remains committed to transparency and accountability, as demonstrated in our annual financial statements and impact reports. We invite you to review these reports to see the transformative effects of your support and the ongoing positive change we create together for our youth.
        </p>
      </div>

      <!-- Highlights pulled from the text above -->
      <aside class="our-impact__highlights" aria-label="Impact highlights">
        <div class="impact-highlight">
          <span class="impact-highlight__value">$0</span>
          <span class="impact-highlight__label">Programs offered at no cost to families</span>
        </div>
        <div class="impact-highlight">
          <span class="impact-highlight__value">40+</span>
          <span class="impact-highlight__label">Partnerships with organizations and professionals</span>
        </div>
        <div class="impact-highlight">
          <span class="impact-highlight__value impact-highlight__value--text">Fine Arts · STEM · Life&nbsp;Skills</span>
          <span class="impact-highlight__label">After-school and summer programs for underserved youth</span>
        </div>
      </aside>
    </div>
  </section>

  <!-- IMPACT -->
  <section class="impact" id="impact" aria-labelledby="stats-title">
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
      <h2 class="cta-banner__title">Join Our Mission</h2>
      <p class="cta-banner__desc">
        Whether you are a parent, educator, sponsor, or community member — there is a place for you in the Maroon 9 family.
      </p>
      <div class="cta-banner__actions">
        <a href="contact.php" class="btn btn-maroon">Contact Us</a>
        <a href="kidsactingwithpurpose.php" class="btn btn-outline">See Our Programs</a>
      </div>
    </div>
  </section>

<?php layout_footer(); ?>
