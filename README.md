# Maroon 9 Site

PHP site with React-powered interactive parts and SQLite accounts.

## Run locally

Requires PHP 8.1+ (with `pdo_sqlite`). Node 18+ is only needed to rebuild the React code.

```sh
php -S localhost:8080 router.php
```

Open http://localhost:8080. The database (`data/maroon9.sqlite`) is created automatically the first time
it's needed (for example, opening the Events page), with the original six events added.

Always start the server with `router.php`. It blocks `data/`, `includes/`, `bin/` and `frontend/`,
which would otherwise be downloadable. On Apache, `.htaccess` does the same.

## Staff vs. regular accounts

Every account has a `role` in the `users` table: `user` (the default) or `staff`.

- **Regular users** sign up at `register.php` and log in at `login.php`. Public sign-up can only
  ever create `user` accounts.
- **Staff** log in at `staff-login.php` (linked in the footer). That page refuses regular accounts
  even with a correct password. Staff accounts are created, or existing accounts promoted, from the terminal:

  ```sh
  php bin/create-staff.php
  ```

  After that, staff can promote or demote others from the Staff Dashboard (Users & Staff tab).
  Staff can't remove their own access.
- **Restriction:** `staff.php`, `api/staff.php` and event editing check the role on *every request*
  (`require_staff_page()` / `api_require_staff()` in `includes/`). Logged-out visitors are sent to the
  staff login and regular users are turned away. Removing someone's staff role takes effect immediately.

## Email (confirmations and notifications)

The site sends:
- a **confirmation email** with a link when someone signs up (and when they change their email), and
- for every completed form (sign-up, contact, youth enrollment, volunteer sign-up, event registration):
  a **receipt** to the person and a **"Form completed" notification** to the Maroon 9 inbox.

Emails are sent through Gmail as `eliebossombo@gmail.com`. One-time setup:

1. Turn on 2-Step Verification for that Google account.
2. Create an app password at https://myaccount.google.com/apppasswords.
3. Run `php bin/setup-mail.php` and paste the app password when asked. It sends a test email.

Settings are saved in `includes/mail-config.php` (private, git-ignored). Until that's done, emails are
saved as files in `data/mail-log/` instead of being sent, and `data/mail.log` records every attempt.
A mail problem never blocks a form; the message is kept in `data/mail-log/`.

The address shown on the Contact page (info@maroon9org.com) is display text only.

## Deploying

### GitHub Pages (preview only)

GitHub Pages can't run PHP, so it gets a static copy: every page and image,
with forms, sign-in and email turned off and a "preview copy" note at the top.
The copy lives on the `gh-pages` branch and is served at
https://elieboss.github.io/maroon9.org/.

To refresh it after changing the site, with the local server running:

```sh
python3 bin/export-static.py          # writes _site/
```

then commit the contents of `_site/` to the `gh-pages` branch.

### Full site on a PHP host (Render, Railway, …)

The `Dockerfile` runs the whole site. On the host, create a new web service
from this GitHub repo (it detects the Dockerfile) and set these environment
variables in its dashboard:

| Variable           | Value                                              |
|--------------------|----------------------------------------------------|
| `M9_SMTP_PASSWORD` | the 16-letter Gmail app password                   |
| `M9_SITE_URL`      | the site's public address, e.g. `https://….onrender.com` |
| `M9_DB_PATH`       | a file on a persistent disk, e.g. `/data/maroon9.sqlite` |

Without a persistent disk, the database (accounts, messages, sign-ups) is
wiped whenever the host restarts the service. After the first deploy, create
the staff account from the host's shell with `php bin/create-staff.php`.

## Editing the React parts

React source is in `frontend/src/`. The built files in `assets/` are what the site loads.

```sh
cd frontend
npm install      # first time only
npm run build    # rebuild once
npm run watch    # or rebuild on every save
```

## How it fits together

- **Pages** (`*.php`): PHP renders all content. `includes/layout.php` provides the shared head, nav and footer.
- **React islands**: elements with `data-island="..."` are taken over by the matching component
  (see `frontend/src/main.jsx`). The PHP markup inside is the fallback when JavaScript is off.
- **Backend**: `includes/db.php` (SQLite schema), `includes/auth.php` (sessions, roles, CSRF),
  and the JSON endpoints in `api/` (`auth`, `account`, `events`, `contact`, `staff`).
