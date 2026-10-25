<?php
/**
 * layout.php
 * Consolidated layout partials for Maroon 9.
 * Usage:
 *   include 'includes/layout.php';
 *   layout_page_start('Page Title — Maroon 9', 'Meta description');  // <head> + action bar + nav
 *   ...page content...
 *   layout_footer();                                                  // footer + </body></html>
 *
 * Updated for the 2026 site redesign — see Maroon 9 Site Redesign Notes.
 * Navigation: Home, About, Youth Programs (KAWP / Conflict Resolution
 * Through Theatre), The Hub, Arts Education Services,
 * Sickle Cell Stars, Events, Get Involved, Give.
 *
 * React: the compiled bundle in assets/ mounts onto any element with a
 * data-island attribute (see frontend/src/main.jsx). PHP renders working
 * HTML first, so every page still reads and navigates without JavaScript.
 */

require_once __DIR__ . '/auth.php';

/* Single source of truth for the menu; PHP renders it and React receives it */
function nav_items() {
  return array(
    array('label' => 'Home', 'href' => 'index.php'),
    array('label' => 'About', 'href' => 'about.php'),
    array('label' => 'Youth Programs', 'href' => 'kidsactingwithpurpose.php', 'children' => array(
      array('label' => 'Kids Acting With Purpose', 'href' => 'kidsactingwithpurpose.php'),
      array('label' => 'Conflict Resolution Through Theatre™', 'href' => 'conflictresolutionthroughtheatre.php'),
    )),
    array('label' => 'The Hub', 'href' => 'hub.php'),
    array('label' => 'Arts Education Services', 'href' => 'pagetostage.php'),
    array('label' => 'Sickle Cell Stars', 'href' => 'sicklecellstars.php'),
    array('label' => 'Events', 'href' => 'events.php'),
    array('label' => 'Get Involved', 'href' => 'getinvolved.php'),
    array('label' => 'Give', 'href' => 'give.php'),
  );
}

/* Social profiles shown in the footer; 'icon' picks the SVG in social_icon() */
function social_links() {
  return array(
    array('label' => 'Facebook',  'icon' => 'facebook',  'href' => 'https://www.facebook.com/maroon9communityenrichment'),
    array('label' => 'Instagram', 'icon' => 'instagram', 'href' => 'https://www.instagram.com/maroon9communityenrichment/'),
    array('label' => 'LinkedIn',  'icon' => 'linkedin',  'href' => 'https://www.linkedin.com/company/maroon9communityenrichment/'),
    array('label' => 'YouTube',   'icon' => 'youtube',   'href' => 'https://www.youtube.com/@maroon9'),
    array('label' => 'Eventbrite', 'icon' => 'eventbrite', 'href' => 'https://www.eventbrite.com/e/bone-soup-a-kwanzaa-story-by-kids-acting-with-purpose-tickets-1012893470637?aff=oddtdtcreator#organizer-card'),
  );
}

/* Brand glyphs drawn on a 24x24 grid, colored with currentColor */
function social_icon($name) {
  $paths = array(
    'facebook'  => '<path fill="currentColor" d="M13.4 21v-7.6h2.6l.4-3H13.4V8.5c0-.9.3-1.5 1.5-1.5h1.6V4.3c-.3 0-1.2-.1-2.3-.1-2.3 0-3.9 1.4-3.9 4v2.2H7.7v3h2.6V21z"/>',
    'instagram' => '<rect x="3.2" y="3.2" width="17.6" height="17.6" rx="5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4.1" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17.3" cy="6.7" r="1.25" fill="currentColor"/>',
    'linkedin'  => '<rect x="4" y="9.2" width="3.3" height="10.8" fill="currentColor"/><circle cx="5.65" cy="5.6" r="1.95" fill="currentColor"/><path fill="currentColor" d="M10 9.2h3.15v1.5h.05c.45-.85 1.5-1.7 3.1-1.7 3.3 0 3.9 2.15 3.9 5V20h-3.3v-5.3c0-1.25 0-2.9-1.75-2.9s-2 1.37-2 2.8V20H10z"/>',
    'eventbrite' => '<path fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" d="M6.5 12h11a5.5 5.5 0 1 0-1.6 3.9"/>',
    'youtube'   => '<path fill="currentColor" fill-rule="evenodd" d="M21.6 7.2a2.5 2.5 0 0 0-1.77-1.77C18.27 5 12 5 12 5s-6.27 0-7.83.43A2.5 2.5 0 0 0 2.4 7.2C2 8.76 2 12 2 12s0 3.24.4 4.8a2.5 2.5 0 0 0 1.77 1.77C5.73 19 12 19 12 19s6.27 0 7.83-.43a2.5 2.5 0 0 0 1.77-1.77C22 15.24 22 12 22 12s0-3.24-.4-4.8zM10 15.1V8.9l5.3 3.1z"/>',
  );
  return '<svg class="social-icon" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false">' . $paths[$name] . '</svg>';
}

/* Articles in the "Latest News" side panel (newest first) */
function news_articles() {
  return array(
    array(
      'title' => 'When Culture Meets Cowtown: Bone Soup: A Kwanzaa Story 2024',
      'href'  => 'https://www.maroon9.org/post/when-culture-meets-cowtown-bone-soup-a-kwanzaa-story-2024',
      'image' => 'images/When Culture Meets Cowtown.avif',
      'tag'   => 'Production',
    ),
    array(
      'title' => 'Celebrating Courage and Resilience: Harriet: Take My Hand and Follow Me',
      'href'  => 'https://www.maroon9.org/post/celebrating-courage-and-resilience-harriet-take-my-hand-and-follow-me',
      'image' => 'images/Courage and Resilience- Harriet.avif',
      'tag'   => 'Production',
    ),
    array(
      'title' => 'Empowering Young Creators: Theatre Arts and Media Arts Camp 2024',
      'href'  => 'https://www.maroon9.org/post/empowering-young-creators-theatre-arts-and-media-arts-camp-2024',
      'image' => 'images/Empowering Young Creators.webp',
      'tag'   => 'Camp',
    ),
    array(
      'title' => 'A Journey of Inspiration and Empowerment: The Stand Up Documentary Project',
      'href'  => 'https://www.maroon9.org/post/a-journey-of-inspiration-and-empowerment-the-stand-up-documentary-project',
      'image' => 'images/Journey of Inspiration and Empowerment.webp',
      'tag'   => 'Documentary',
    ),
  );
}

function e($text) {
  return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

/* Props for a React island, safely embedded in an HTML attribute */
function island_props($props) {
  return e(json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

/* The built React bundle's JS and CSS files, from Vite's manifest.
   File names are content-hashed, so they never need a ?v= query (a query
   would make the browser load the entry module twice). Empty before the
   first `npm run build`; pages still work without it. */
function app_assets() {
  $manifest = __DIR__ . '/../assets/.vite/manifest.json';
  if (!is_file($manifest)) return array('js' => null, 'css' => array());
  $entry = json_decode(file_get_contents($manifest), true)['src/main.jsx'] ?? null;
  if (!$entry) return array('js' => null, 'css' => array());
  return array(
    'js'  => 'assets/' . $entry['file'],
    'css' => array_map(function ($file) { return 'assets/' . $file; }, $entry['css'] ?? array()),
  );
}

function layout_page_start($title, $description) {
  $user = current_user();
  $boot = array(
    'csrf' => csrf_token(),
    'user' => public_user($user),
    'page' => basename($_SERVER['PHP_SELF']),
  );
  $app = app_assets();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($title) ?></title>
  <meta name="description" content="<?= e($description) ?>" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&family=Playfair+Display:ital,wght@1,400&display=swap" rel="stylesheet" />

  <link rel="stylesheet" href="css/styles.css?v=<?= filemtime(__DIR__ . '/../css/styles.css') ?>" />
<?php foreach ($app['css'] as $css): ?>
  <link rel="stylesheet" href="<?= e($css) ?>" />
<?php endforeach; ?>
  <script>document.documentElement.classList.add('js'); window.M9 = <?= json_encode($boot, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<?php if ($app['js']): ?>
  <script type="module" src="<?= e($app['js']) ?>"></script>
<?php endif; ?>
</head>
<body>
<a class="skip-link" href="#main">Skip to main content</a>
<?php
  layout_header();
  echo "<main id=\"main\">\n";
  if (isset($_GET['signed_out']) && $user === null) {
    echo '  <div class="signed-out-notice" role="status"><div class="signed-out-notice__inner">'
       . 'You’ve been signed out. <a href="login.php">Sign back in</a></div></div>' . "\n";
  }
}

function layout_header() {
  $current = basename($_SERVER['PHP_SELF']);
  $user = current_user();
  $items = nav_items();

  $isActive = function ($item) use ($current) {
    if ($item['href'] === $current) return true;
    foreach ($item['children'] ?? array() as $child) {
      if ($child['href'] === $current) return true;
    }
    return false;
  };
?>
  <!-- ACTION BAR -->
  <div class="action-bar">
    <span class="action-bar__tagline">Transforming Lives Through The Arts.</span>
    <div class="action-bar__links">
      <a href="kidsactingwithpurpose.php">KAWP</a>
      <a href="contact.php">Contact</a>
<?php if ($user && $user['role'] === 'staff'): ?>
      <a href="staff.php"<?= $current === 'staff.php' ? ' aria-current="page"' : '' ?>>Staff</a>
<?php endif; ?>
<?php if ($user): ?>
      <a href="account.php"<?= $current === 'account.php' ? ' aria-current="page"' : '' ?>>My Account</a>
      <form action="logout.php" method="post" class="action-bar__signout">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
        <button type="submit" title="Sign out of <?= e($user['email']) ?>">
          <svg viewBox="0 0 16 16" width="13" height="13" aria-hidden="true" focusable="false"><path d="M6 2.5H3.5a1 1 0 0 0-1 1v9a1 1 0 0 0 1 1H6M10.5 11 13.5 8l-3-3M13.5 8H6" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Sign Out
        </button>
      </form>
<?php else: ?>
      <a href="login.php"<?= $current === 'login.php' ? ' aria-current="page"' : '' ?>>Log In</a>
<?php endif; ?>
      <a href="give.php" class="highlight">Donate</a>
    </div>
  </div>

  <!-- PRIMARY NAVIGATION (React takes this over; this markup is the no-JS fallback) -->
  <div data-island="site-nav" data-props="<?= island_props(array('items' => $items, 'current' => $current, 'user' => public_user($user))) ?>">
  <nav class="nav" aria-label="Main navigation">
    <div class="nav__inner">

      <a href="index.php" class="nav__logo" aria-label="Maroon 9 Home">
        <img src="images/logo.avif" alt="Maroon 9 Logo" />
      </a>

      <ul class="nav__links">
<?php foreach ($items as $item): $active = $isActive($item); ?>
<?php if (!empty($item['children'])): ?>
        <li class="nav__item<?= $active ? ' active' : '' ?>">
          <a href="<?= e($item['href']) ?>" class="nav__dropdown-toggle<?= $active ? ' active' : '' ?>">
            <?= e($item['label']) ?> <svg class="nav__caret" viewBox="0 0 12 12" width="12" height="12" aria-hidden="true" focusable="false"><path d="M2.5 4.25 6 7.75l3.5-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </a>
          <ul class="nav__dropdown">
<?php foreach ($item['children'] as $child): ?>
            <li><a href="<?= e($child['href']) ?>"<?= $child['href'] === $current ? ' class="active" aria-current="page"' : '' ?>><?= e($child['label']) ?></a></li>
<?php endforeach; ?>
          </ul>
        </li>
<?php else: ?>
        <li><a href="<?= e($item['href']) ?>"<?= $active ? ' class="active" aria-current="page"' : '' ?>><?= e($item['label']) ?></a></li>
<?php endif; ?>
<?php endforeach; ?>
      </ul>

    </div>
  </nav>
  </div>
<?php
}

function layout_footer() {
?>
</main>

  <!-- FOOTER -->
  <footer class="footer">
    <div class="footer__inner">

      <!-- NEWSLETTER (React: NewsletterForm submits in place; this form works without JavaScript) -->
<?php
  $nlMessages = array(
    'subscribed' => array('success', 'Thanks for subscribing! Check your inbox for a confirmation.'),
    'already'    => array('success', 'You’re already subscribed. Thanks for staying connected!'),
    'consent'    => array('error', 'Please check the box to confirm you’d like to subscribe.'),
    'invalid'    => array('error', 'Please enter a valid email address and check the box to subscribe.'),
    'limit'      => array('error', 'Too many sign-ups from this connection. Please try again later.'),
    'expired'    => array('error', 'Your session expired. Please try again.'),
  );
  $nl = $nlMessages[$_GET['newsletter'] ?? ''] ?? null;
  $nlUser = current_user();
?>
      <div class="newsletter" id="newsletter">
        <div class="newsletter__intro">
          <h2 class="newsletter__title" id="newsletter-title">Newsletter Sign-Up</h2>
          <p class="newsletter__desc">Stay updated with news, programs and events.</p>
        </div>
        <div data-island="newsletter-form" data-props="<?= island_props(array('email' => $nlUser['email'] ?? '')) ?>">
          <form class="newsletter__form" action="newsletter-signup.php" method="post" aria-labelledby="newsletter-title">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
            <input type="hidden" name="back" value="<?= e(basename($_SERVER['PHP_SELF'])) ?>" />
            <div class="newsletter__row">
              <label class="visually-hidden" for="newsletter-email">Email address</label>
              <input type="email" id="newsletter-email" name="email" placeholder="Your email address" autocomplete="email" value="<?= e($nlUser['email'] ?? '') ?>" required />
              <button type="submit" class="btn btn-primary">Subscribe</button>
            </div>
            <label class="newsletter__consent">
              <input type="checkbox" name="consent" value="1" required />
              <span>Yes, send me the Maroon 9 newsletter. I can unsubscribe anytime.</span>
            </label>
<?php if ($nl): ?>
            <p class="newsletter__message newsletter__message--<?= $nl[0] ?>" role="status"><?= e($nl[1]) ?></p>
<?php endif; ?>
          </form>
        </div>
      </div>

      <div class="footer__top">

        <div>
          <div class="footer__brand-name">Maroon 9</div>
          <p class="footer__brand-desc">
            Preparing Fort Worth youth for life beyond the stage through creative arts, mentorship, conflict resolution, and sickle cell disease advocacy since 2016.
          </p>
          <div class="footer__col-title">Follow Us</div>
          <ul class="footer__social">
<?php foreach (social_links() as $s): ?>
            <li>
              <a href="<?= e($s['href']) ?>" class="footer__social-link footer__social-link--<?= e($s['icon']) ?>" target="_blank" rel="noopener" aria-label="Maroon 9 on <?= e($s['label']) ?> (opens in a new tab)">
                <?= social_icon($s['icon']) ?>
              </a>
            </li>
<?php endforeach; ?>
          </ul>
        </div>

        <div>
          <div class="footer__col-title">Navigate</div>
          <ul class="footer__col-links">
            <li><a href="about.php">About Us</a></li>
            <li><a href="about.php#impact">Impact</a></li>
            <li><a href="hub.php">The Hub</a></li>
            <li><a href="events.php">Events</a></li>
          </ul>
        </div>

        <div>
          <div class="footer__col-title">Programs</div>
          <ul class="footer__col-links">
            <li><a href="kidsactingwithpurpose.php">Kids Acting With Purpose</a></li>
            <li><a href="conflictresolutionthroughtheatre.php">Conflict Resolution Through Theatre&trade;</a></li>
            <li><a href="pagetostage.php">Arts Education Services</a></li>
            <li><a href="sicklecellstars.php">Sickle Cell Stars</a></li>
          </ul>
        </div>

        <div>
          <div class="footer__col-title">Latest News</div>
          <ul class="footer__col-links">
<?php foreach (news_articles() as $a): ?>
            <li><a href="<?= e($a['href']) ?>" target="_blank" rel="noopener"><?= e(strtok($a['title'], ':')) ?></a></li>
<?php endforeach; ?>
          </ul>
        </div>

        <div>
          <div class="footer__col-title">Get Involved</div>
          <ul class="footer__col-links">
            <li><a href="getinvolved.php#volunteer">Volunteer</a></li>
            <li><a href="getinvolved.php">Partner With Us</a></li>
            <li><a href="give.php">Give</a></li>
            <li><a href="contact.php">Contact Us</a></li>
            <li><a href="staff-login.php">Staff Login</a></li>
          </ul>
        </div>

      </div>
      <div class="footer__bottom">
        <span>&copy; <?php echo date('Y'); ?> Maroon 9 Community Enrichment Organization. All rights reserved.</span>
      </div>
      <p class="footer__disclaimer">
        <a href="https://catalog.uta.edu/coursedescriptions/ctec/" target="_blank" rel="noopener">
          This website was created by Elie Bossombo in collaboration with the Communication Technology Department at UTA. It was created as a class project for CTEC 4350 — Web Communication Design and Development III at The University of Texas at Arlington and is intended for educational purposes only.
        </a>
      </p>
    </div>
  </footer>

  <!-- LATEST NEWS: React (NewsPanel) adds a "Latest News" tab that opens a side panel -->
  <div data-island="news-panel" data-props="<?= island_props(array('articles' => news_articles())) ?>"></div>

</body>
</html>
<?php
}
