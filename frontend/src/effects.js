/*
   Page-wide polish that doesn't need a component:
   - sections fade/slide in as they scroll into view
   - impact numbers ("500+", "10+") count up the first time they're seen
   Both are skipped for visitors who prefer reduced motion.
*/

const REVEAL_SELECTOR = [
  '.section-title', '.section-body', '.feature-block', '.program-card', '.give-card',
  '.impact__stat', '.leader-card', '.board-card', '.goal-card', '.impact-highlight', '.benefit-card', '.pull-quote--card', '.literacy-list li', '.timeline-item', '.photo-grid', '.section-photo',
  '.about__image-wrap', '.cta-banner__inner', '.story-block', '.highlight-box',
].join(',');

function prefersReducedMotion() {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function initReveal() {
  const targets = [...document.querySelectorAll(REVEAL_SELECTOR)].filter(
    (el) => !el.closest('.hero, .page-hero, [data-island]'),
  );
  /* Anything already on screen shows immediately; only below-the-fold content animates */
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      });
    },
    { rootMargin: '0px 0px -8% 0px', threshold: 0.08 },
  );
  targets.forEach((el) => {
    if (el.getBoundingClientRect().top < window.innerHeight) return;
    el.classList.add('reveal');
    observer.observe(el);
  });
}

function countUp(el) {
  const match = el.textContent.trim().match(/^(\d+)(.*)$/);
  if (!match) return;
  const target = Number(match[1]);
  const suffix = match[2];
  const duration = 1200;
  const start = performance.now();

  el.setAttribute('aria-label', el.textContent.trim());
  const tick = (now) => {
    const progress = Math.min((now - start) / duration, 1);
    const eased = 1 - Math.pow(1 - progress, 3);
    el.textContent = Math.round(target * eased) + suffix;
    if (progress < 1) requestAnimationFrame(tick);
  };
  requestAnimationFrame(tick);
}

function initCountUp() {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      countUp(entry.target);
      observer.unobserve(entry.target);
    });
  }, { threshold: 0.6 });
  document.querySelectorAll('.impact__stat-number').forEach((el) => observer.observe(el));
}

/* "Expand all / Collapse all" buttons for groups of <details> drop-downs.
   The buttons start hidden so they only appear when they can work. */
function initExpandAll() {
  document.querySelectorAll('[data-expand-all]').forEach((button) => {
    const group = document.getElementById(button.dataset.expandAll);
    if (!group) return;
    const items = [...group.querySelectorAll('details')];
    const sync = () => {
      const allOpen = items.every((d) => d.open);
      button.textContent = allOpen ? 'Collapse all' : 'Expand all';
      button.setAttribute('aria-expanded', String(allOpen));
    };
    button.hidden = false;
    button.addEventListener('click', () => {
      const open = !items.every((d) => d.open);
      items.forEach((d) => { d.open = open; });
      sync();
    });
    items.forEach((d) => d.addEventListener('toggle', sync));
    sync();
  });
}

export function initPageEffects() {
  initExpandAll();
  if (prefersReducedMotion() || !('IntersectionObserver' in window)) return;
  initReveal();
  initCountUp();
}
