<?php
  include 'includes/layout.php';
  layout_page_start(
    'Gallery — Maroon 9',
    'Photos and videos of Maroon 9 youth on stage, in rehearsal, and out in the Fort Worth community.'
  );

  require_once 'includes/gallery-data.php';
  $photos = gallery_items('photo');
  $videos = gallery_items('video');
  $images = array_map(function ($p) {
    return array('src' => $p['src'], 'thumb' => $p['thumb'], 'alt' => $p['caption'] !== '' ? $p['caption'] : 'Maroon 9 in action');
  }, $photos);
?>

  <!-- PAGE HERO -->
  <section class="page-hero" aria-labelledby="page-title">
    <span class="section-label">Gallery</span>
    <h1 class="page-hero__title" id="page-title">Additional Content From Our Programs</h1>
    <p class="page-hero__desc">More photos and videos of Maroon 9 youth on stage, in rehearsal, and out in the community.</p>
<?php if (is_staff()): ?>
    <p class="gallery-manage"><a href="#add" class="btn btn-outline">+ Add Photos &amp; Videos</a></p>
<?php endif; ?>
  </section>

<?php if (is_staff()): ?>
  <!-- STAFF ONLY: upload here (api/gallery.php checks the role on every request) -->
  <section class="content-section" id="add" aria-label="Add photos and videos">
    <div class="content-section__inner">
      <div data-island="gallery-uploader"><p>Turn on JavaScript to upload photos and videos.</p></div>
      <p><a href="staff.php?tab=gallery" class="text-btn">Edit captions or remove items</a></p>
    </div>
  </section>
<?php endif; ?>

  <!-- PHOTOS -->
  <section class="content-section" id="photos" aria-labelledby="photos-title">
    <div class="content-section__inner">
      <span class="section-label">Photos</span>
      <h2 class="section-title" id="photos-title">Moments On Stage &amp; Beyond</h2>
<?php if ($images): ?>
      <div class="photo-grid photo-grid--gallery" data-island="gallery" data-props="<?= island_props(array('images' => $images)) ?>">
<?php foreach ($images as $img): ?>
        <img src="<?= e($img['thumb']) ?>" alt="<?= e($img['alt']) ?>" class="photo-grid__image" loading="lazy" />
<?php endforeach; ?>
      </div>
<?php else: ?>
      <p class="empty-state">Photos are coming soon.</p>
<?php endif; ?>
    </div>
  </section>

  <!-- VIDEOS -->
  <section class="content-section gallery-videos" id="videos" aria-labelledby="videos-title">
    <div class="content-section__inner">
      <span class="section-label">Videos</span>
      <h2 class="section-title" id="videos-title">Watch Maroon 9</h2>
<?php if ($videos): ?>
      <div class="video-grid">
<?php foreach ($videos as $v): ?>
        <figure class="video-card">
          <video controls playsinline preload="metadata"<?= $v['thumb'] !== '' ? ' poster="' . e($v['thumb']) . '"' : '' ?> aria-label="<?= e($v['caption'] !== '' ? $v['caption'] : 'Maroon 9 video') ?>">
            <source src="<?= e($v['src']) ?>#t=0.1"<?= in_array($v['type'], array('video/mp4', 'video/webm'), true) ? ' type="' . e($v['type']) . '"' : '' ?> />
            <a href="<?= e($v['src']) ?>">Download the video</a>
          </video>
<?php if ($v['caption'] !== ''): ?>
          <figcaption class="video-card__caption"><?= e($v['caption']) ?></figcaption>
<?php endif; ?>
        </figure>
<?php endforeach; ?>
      </div>
<?php else: ?>
      <p class="empty-state">Videos are coming soon.</p>
<?php endif; ?>
    </div>
  </section>

<?php layout_footer(); ?>
