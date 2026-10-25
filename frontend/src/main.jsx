/*
   MAROON 9 React entry
   PHP renders every page. Elements marked data-island="name" are handed to
   the matching React component below, with data-props as its props. The
   PHP markup inside an island is the no-JavaScript fallback and is replaced.
*/
import { lazy, Suspense, StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import './styles.css';
import { initPageEffects } from './effects.js';

/* Dashboards are only loaded on the pages that use them */
const islands = {
  'site-nav': lazy(() => import('./components/SiteNav.jsx')),
  gallery: lazy(() => import('./components/Gallery.jsx')),
  'events-board': lazy(() => import('./components/EventsBoard.jsx')),
  'contact-form': lazy(() => import('./components/ContactForm.jsx')),
  'sponsor-marquee': lazy(() => import('./components/SponsorMarquee.jsx')),
  'news-panel': lazy(() => import('./components/NewsPanel.jsx')),
  'newsletter-form': lazy(() => import('./components/NewsletterForm.jsx')),
  'auth-form': lazy(() => import('./components/AuthForm.jsx')),
  'volunteer-signup': lazy(() => import('./components/VolunteerSignup.jsx')),
  'account-dashboard': lazy(() => import('./components/AccountDashboard.jsx')),
  'staff-dashboard': lazy(() => import('./components/StaffDashboard.jsx')),
};

/* Some islands build their props from the fallback markup itself */
const propsFromMarkup = {
  gallery: (el) => ({
    images: [...el.querySelectorAll('img')].map((img) => ({ src: img.getAttribute('src'), alt: img.alt })),
    className: el.className,
  }),
};

function mountIslands() {
  document.querySelectorAll('[data-island]').forEach((el) => {
    const name = el.dataset.island;
    const Component = islands[name];
    if (!Component) return;

    let props = {};
    try {
      props = el.dataset.props ? JSON.parse(el.dataset.props) : {};
    } catch {
      console.warn(`Island "${name}" has invalid props; keeping the PHP fallback.`);
      return;
    }
    if (propsFromMarkup[name]) props = { ...propsFromMarkup[name](el), ...props };

    /* Islands that style their own wrapper (e.g. the gallery grid) take over the
       container's classes so the layout isn't nested twice */
    const wrapperClass = props.className || '';
    if (wrapperClass) el.removeAttribute('class');

    /* Keep the fallback visible until the component's code has loaded */
    const fallback = (
      <div
        className={wrapperClass || undefined}
        style={wrapperClass ? undefined : { display: 'contents' }}
        dangerouslySetInnerHTML={{ __html: el.innerHTML }}
      />
    );
    el.classList.add('island--mounted');
    createRoot(el).render(
      <StrictMode>
        <Suspense fallback={fallback}>
          <Component {...props} />
        </Suspense>
      </StrictMode>,
    );
  });
}

mountIslands();
initPageEffects();
