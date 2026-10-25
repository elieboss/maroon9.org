/*
   Main navigation. Replaces js/main.js: dropdown that opens on hover,
   click or keyboard; mobile menu with Escape/outside-click to close;
   a shadow once the page scrolls; account links in the mobile menu.
*/
import { useEffect, useRef, useState } from 'react';
import { logOut } from '../api.js';

function isActive(item, current) {
  return item.href === current || (item.children || []).some((c) => c.href === current);
}

export default function SiteNav({ items, current, user }) {
  const [mobileOpen, setMobileOpen] = useState(false);
  const [openDropdown, setOpenDropdown] = useState(null);
  const [scrolled, setScrolled] = useState(false);
  const navRef = useRef(null);
  const hamburgerRef = useRef(null);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 8);
    const onClick = (event) => {
      if (navRef.current && !navRef.current.contains(event.target)) {
        setMobileOpen(false);
        setOpenDropdown(null);
      }
    };
    const onKey = (event) => {
      if (event.key !== 'Escape') return;
      setOpenDropdown(null);
      setMobileOpen((open) => {
        if (open) hamburgerRef.current?.focus();
        return false;
      });
    };
    const desktop = window.matchMedia('(min-width: 1280px)');
    const onResize = () => desktop.matches && setMobileOpen(false);

    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
    document.addEventListener('click', onClick);
    document.addEventListener('keydown', onKey);
    desktop.addEventListener('change', onResize);
    return () => {
      window.removeEventListener('scroll', onScroll);
      document.removeEventListener('click', onClick);
      document.removeEventListener('keydown', onKey);
      desktop.removeEventListener('change', onResize);
    };
  }, []);

  return (
    <nav ref={navRef} className={`nav${scrolled ? ' nav--scrolled' : ''}`} aria-label="Main navigation">
      <div className="nav__inner">
        <a href="index.php" className="nav__logo" aria-label="Maroon 9 Home">
          <img src="images/logo.avif" alt="Maroon 9 Logo" />
        </a>

        <ul className="nav__links">
          {items.map((item) => {
            const active = isActive(item, current);
            if (!item.children) {
              return (
                <li key={item.href}>
                  <a href={item.href} className={active ? 'active' : undefined} aria-current={active ? 'page' : undefined}>
                    {item.label}
                  </a>
                </li>
              );
            }
            const open = openDropdown === item.href;
            const menuId = `nav-menu-${item.href.replace(/\W/g, '')}`;
            return (
              <li
                key={item.href}
                className={`nav__item${active ? ' active' : ''}${open ? ' open' : ''}`}
                onBlur={(event) => !event.currentTarget.contains(event.relatedTarget) && setOpenDropdown(null)}
              >
                <a href={item.href} className={`nav__dropdown-toggle${active ? ' active' : ''}`}>
                  {item.label}
                </a>
                <button
                  type="button"
                  className="nav__caret-btn"
                  aria-expanded={open}
                  aria-controls={menuId}
                  aria-label={`${open ? 'Hide' : 'Show'} ${item.label} pages`}
                  onClick={() => setOpenDropdown(open ? null : item.href)}
                >
                  <svg className="nav__caret" viewBox="0 0 12 12" width="12" height="12" aria-hidden="true" focusable="false">
                    <path d="M2.5 4.25 6 7.75l3.5-3.5" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                  </svg>
                </button>
                <ul className="nav__dropdown" id={menuId}>
                  {item.children.map((child) => (
                    <li key={child.href}>
                      <a
                        href={child.href}
                        className={child.href === current ? 'active' : undefined}
                        aria-current={child.href === current ? 'page' : undefined}
                      >
                        {child.label}
                      </a>
                    </li>
                  ))}
                </ul>
              </li>
            );
          })}
        </ul>

        <button
          ref={hamburgerRef}
          type="button"
          className={`nav__hamburger${mobileOpen ? ' open' : ''}`}
          aria-expanded={mobileOpen}
          aria-controls="mobileMenu"
          aria-label={mobileOpen ? 'Close menu' : 'Open menu'}
          onClick={() => setMobileOpen(!mobileOpen)}
        >
          <span /><span /><span />
        </button>
      </div>

      <div className={`nav__mobile-menu${mobileOpen ? ' open' : ''}`} id="mobileMenu" hidden={!mobileOpen}>
        {items.map((item) =>
          item.children ? (
            <div className="nav__mobile-group" key={item.href}>
              <div className="nav__mobile-group-label">{item.label}</div>
              {item.children.map((child) => (
                <a key={child.href} href={child.href} className="nav__mobile-sublink" aria-current={child.href === current ? 'page' : undefined}>
                  {child.label}
                </a>
              ))}
            </div>
          ) : (
            <a key={item.href} href={item.href} aria-current={item.href === current ? 'page' : undefined}>
              {item.label}
            </a>
          ),
        )}
        <a href="contact.php" aria-current={current === 'contact.php' ? 'page' : undefined}>Contact</a>

        <div className="nav__mobile-account">
          {user ? (
            <>
              <a href="account.php" className="btn btn-maroon">My Account</a>
              {user.role === 'staff' && <a href="staff.php" className="btn btn-outline btn-outline--dark">Staff Dashboard</a>}
              <button type="button" className="btn btn-outline btn-outline--dark nav__mobile-logout" onClick={logOut}>Sign Out</button>
            </>
          ) : (
            <>
              <a href={`login.php?next=${encodeURIComponent(current)}`} className="btn btn-maroon">Log In</a>
              <a href="register.php" className="btn btn-outline btn-outline--dark">Create Account</a>
            </>
          )}
        </div>
      </div>
    </nav>
  );
}
