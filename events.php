<?php
  include 'includes/layout.php';
  layout_page_start(
    'Events — Maroon 9',
    'Upcoming Maroon 9 performances, workshops, and community events.'
  );

  require_once 'includes/events-data.php';
  $events   = events_list(current_user());
  $upcoming = array_values(array_filter($events, function ($e) { return !$e['is_past']; }));
  $past     = array_reverse(array_values(array_filter($events, function ($e) { return $e['is_past']; })));

  /* "Highlights" tab: featured past productions (newest first) */
  $highlights = array(
    array(
      'title'       => 'Bone Soup: A Kwanzaa Story',
      'date'        => 'December 7 & 8, 2024',
      'venue'       => 'Jean McClung Middle School · Fort Worth, Texas',
      'description' => 'Bone Soup: A Kwanzaa Story returns to the stage at Jean McClung Middle School in Fort Worth, Texas, on December 7 and 8, 2024. Directed by Mrs. ShaVonne Davis, this year’s production introduces fresh remixes of music and new dance routines, adding vibrant energy to this celebration of Kwanzaa’s values of unity, community, and cultural pride. The young cast will bring to life a story that explores the strength of togetherness and the beauty of shared traditions. Join us for an inspiring performance that honors heritage and community this holiday season!',
      'images'      => array(
        array('src' => 'images/BoneSoup1.avif', 'alt' => 'Bone Soup: A Kwanzaa Story poster showing the Kids Acting With Purpose cast in colorful African-print costumes on stage'),
        array('src' => 'images/BoneSoup2.avif', 'alt' => 'Bone Soup: A Kwanzaa Story poster showing two performers in African-print dresses holding woven fans beside a street lamp'),
      ),
    ),
  );
?>

  <!-- PAGE HERO -->
  <section class="page-hero" aria-labelledby="page-title">
    <span class="section-label">Mark Your Calendar</span>
    <h1 class="page-hero__title" id="page-title">Events</h1>
    <p class="page-hero__desc">
      From performances to workshops — here is what Maroon 9 has coming up in the Fort Worth community.
    </p>
  </section>

  <!-- EVENTS GRID -->
  <section class="content-section" aria-labelledby="events-title">
    <div class="content-section__inner">
      <span class="section-label">Upcoming Schedule</span>
      <h2 class="section-title" id="events-title">Events &amp; Performances</h2>

      <!-- React (EventsBoard) replaces this list with upcoming/past tabs, registration and add-to-calendar -->
      <div data-island="events-board" data-props="<?= island_props(array('events' => $events, 'user' => public_user(current_user()), 'highlights' => $highlights)) ?>">
<?php if (!$upcoming): ?>
        <p class="empty-state">New dates are coming soon. Follow <a href="https://www.linktr.ee/maroon9" target="_blank" rel="noopener">linktr.ee/maroon9</a> for the latest.</p>
<?php endif; ?>
        <div class="events__grid">
<?php foreach (array_merge($upcoming, $past) as $event): $d = new DateTime($event['event_date']); ?>
          <article class="event-card<?= $event['is_past'] ? ' event-card--past' : '' ?>">
            <div class="event-card__date-bar">
              <div class="event-card__day"><?= $d->format('j') ?></div>
              <div class="event-card__month-year"><?= $d->format('M') ?><br /><?= $d->format('Y') ?></div>
            </div>
            <div class="event-card__body">
              <h3 class="event-card__title"><?= e($event['title']) ?></h3>
              <p class="event-card__location"><?= e($event['location']) ?></p>
<?php if ($event['is_past']): ?>
              <span class="event-card__status">Past event</span>
<?php else: ?>
              <a href="login.php?next=events.php" class="event-card__cta"><?= e($event['cta_label']) ?></a>
<?php endif; ?>
            </div>
          </article>
<?php endforeach; ?>
        </div>

        <h3 class="events-highlights__heading">Highlights</h3>
<?php foreach ($highlights as $h): ?>
        <article class="highlight-card">
          <div class="highlight-card__gallery">
<?php foreach ($h['images'] as $img): ?>
            <img src="<?= e($img['src']) ?>" alt="<?= e($img['alt']) ?>" class="photo-grid__image" loading="lazy" />
<?php endforeach; ?>
          </div>
          <div class="highlight-card__body">
            <span class="highlight-card__badge">Highlight</span>
            <h4 class="highlight-card__title"><?= e($h['title']) ?></h4>
            <p class="highlight-card__meta"><?= e($h['date']) ?> · <?= e($h['venue']) ?></p>
            <p class="highlight-card__desc"><?= e($h['description']) ?></p>
          </div>
        </article>
<?php endforeach; ?>
      </div>

      <div class="link-row">
        <a href="https://www.linktr.ee/maroon9" target="_blank" rel="noopener">View Upcoming Events</a>
        <a href="https://www.linktr.ee/maroon9" target="_blank" rel="noopener">Register</a>
        <a href="contact.php">Purchase Tickets</a>
      </div>
    </div>
  </section>

  <!-- CTA BANNER -->
  <section class="cta-banner" aria-label="Call to Action">
    <div class="cta-banner__inner">
      <h2 class="cta-banner__title">Want to Perform or Volunteer?</h2>
      <p class="cta-banner__desc">
        We are always looking for community support. Reach out to learn how you can get involved at our next event.
      </p>
      <div class="cta-banner__actions">
        <a href="contact.php" class="btn btn-maroon">Contact Us</a>
        <a href="kidsactingwithpurpose.php" class="btn btn-outline">Explore Programs</a>
      </div>
    </div>
  </section>

<?php layout_footer(); ?>
