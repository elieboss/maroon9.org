<?php
  require_once 'includes/contact-data.php';

  /* Plain-form submission (JavaScript off). React posts to api/contact.php instead. */
  $user   = current_user();
  $sent   = isset($_GET['sent']);
  $error  = null;
  $fields = array();
  $values = array('name' => $user['name'] ?? '', 'email' => $user['email'] ?? '', 'reason' => '', 'message' => '');

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = array_merge($values, array_intersect_key(array_map('strval', array_filter($_POST, 'is_scalar')), $values));
    if (!csrf_valid($_POST['csrf'] ?? '')) {
      $error = 'Your session expired. Please try again.';
    } else {
      $result = contact_submit($_POST);
      if (!empty($result['ok'])) { header('Location: contact.php?sent=1&mail=' . urlencode(last_mail_status() ?: 'none')); exit; }
      $error  = $result['error'];
      $fields = $result['fields'] ?? array();
    }
  }

  include 'includes/layout.php';
  layout_page_start(
    'Contact — Maroon 9',
    'Get in touch with Maroon 9 to volunteer, donate, or enroll a youth in our programs.'
  );
?>

  <!-- PAGE HERO -->
  <section class="page-hero" aria-labelledby="page-title">
    <span class="section-label">Get in Touch</span>
    <h1 class="page-hero__title" id="page-title">Contact Us</h1>
    <p class="page-hero__desc">
      Whether you want to enroll, volunteer, donate, or partner with us — we would love to hear from you.
    </p>
  </section>

  <!-- CONTACT SECTION -->
  <section class="content-section" aria-labelledby="contact-title">
    <div class="content-section__inner">
      <div class="contact-grid">

        <!-- Contact Info -->
        <div>
          <span class="section-label">Reach Out</span>
          <h2 class="section-title" id="contact-title">Let's Connect</h2>

          <div class="contact-info__item">
            <div class="contact-info__label">Email</div>
            <div class="contact-info__value">info@maroon9org.com</div>
          </div>

          <div class="contact-info__item">
            <div class="contact-info__label">Location</div>
            <div class="contact-info__value">Fort Worth, Texas</div>
          </div>

          <div class="contact-info__item">
            <div class="contact-info__label">Programs</div>
            <div class="contact-info__value">KAWP &bull; Page to Stage &bull; Sickle Cell Stars</div>
          </div>

          <div class="contact-info__item">
            <div class="contact-info__label">How You Can Help</div>
            <div class="contact-info__value">Enroll a Youth &bull; Volunteer &bull; Donate &bull; Sponsor</div>
          </div>
        </div>

        <!-- Contact Form: React (ContactForm) adds live validation; this plain form works without JavaScript -->
        <div data-island="contact-form" data-props="<?= island_props(array('reasons' => contact_reasons(), 'user' => public_user($user))) ?>">
<?php if ($sent): ?>
          <div class="notice notice--success" role="status">Thanks! Your message was sent. We'll get back to you soon.
            <?= ($_GET['mail'] ?? '') === 'sent' ? 'A confirmation email is on its way. If you don’t see it in a few minutes, check your Spam or Promotions folder.' : 'We couldn’t email a confirmation right now, but your message was received.' ?></div>
<?php endif; ?>
<?php if ($error): ?>
          <div class="notice notice--error" role="alert"><?= e($error) ?></div>
<?php endif; ?>
          <form action="contact.php" method="post" novalidate>
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
<?php foreach (array('name' => array('Full Name', 'text', 'Your full name'), 'email' => array('Email Address', 'email', 'your@email.com')) as $key => $f): ?>
            <div class="form-group">
              <label for="contact-<?= $key ?>"><?= $f[0] ?></label>
              <input type="<?= $f[1] ?>" id="contact-<?= $key ?>" name="<?= $key ?>" placeholder="<?= $f[2] ?>" value="<?= e($values[$key]) ?>" required />
<?php if (isset($fields[$key])): ?><p class="field-error"><?= e($fields[$key]) ?></p><?php endif; ?>
            </div>
<?php endforeach; ?>
            <div class="form-group">
              <label for="contact-reason">Reason for Contact</label>
              <select id="contact-reason" name="reason">
<?php foreach (contact_reasons() as $value => $label): ?>
                <option value="<?= e($value) ?>"<?= $values['reason'] === $value ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label for="contact-message">Message</label>
              <textarea id="contact-message" name="message" placeholder="Tell us how we can help..." required><?= e($values['message']) ?></textarea>
<?php if (isset($fields['message'])): ?><p class="field-error"><?= e($fields['message']) ?></p><?php endif; ?>
            </div>
            <button type="submit" class="btn btn-maroon">Send Message</button>
          </form>
        </div>

      </div>
    </div>
  </section>

  <!-- CTA BANNER -->
  <section class="cta-banner" aria-label="Call to Action">
    <div class="cta-banner__inner">
      <h2 class="cta-banner__title">Every Dollar Makes a Difference</h2>
      <p class="cta-banner__desc">
        Your donation funds program equipment, mentors, event venues, and direct support for youth participants.
      </p>
      <div class="cta-banner__actions">
        <a href="kidsactingwithpurpose.php" class="btn btn-maroon">See Our Programs</a>
        <a href="about.php#impact" class="btn btn-outline">See Our Impact</a>
      </div>
    </div>
  </section>

<?php layout_footer(); ?>
