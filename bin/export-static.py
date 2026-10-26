#!/usr/bin/env python3
"""
Export a static preview of the site for GitHub Pages.

GitHub Pages can't run PHP, so this saves each public page as plain HTML
from a running local server, points links at the .html copies, and turns
off forms, sign-in and email with a short "preview" note.

    php -S localhost:8080 router.php          # in one terminal
    python3 bin/export-static.py [out_dir]    # default: _site
"""

import re
import shutil
import sys
import urllib.request
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
SERVER = 'http://localhost:8080/'
OUT = Path(sys.argv[1]).resolve() if len(sys.argv) > 1 else ROOT / '_site'

PAGES = [
    'index', 'about', 'programs', 'conflictresolutionthroughtheatre', 'kidsactingwithpurpose',
    'pagetostage', 'sicklecellstars', 'hub', 'impact', 'events', 'getinvolved', 'give',
    'contact', 'login', 'register', 'staff-login', 'forgot-password',
]
# Pages that need the PHP backend; links to them go to the matching login page
REDIRECTS = {'account': 'login', 'staff': 'staff-login'}
COPY_DIRS = ['assets', 'css', 'images']

NOTICE = ('This is a preview copy of the Maroon 9 site. Forms, sign-in and email '
          'confirmations work on the full site only.')

SHIM = f"""<script>
(function () {{
  var msg = {NOTICE!r};
  var realFetch = window.fetch;
  window.fetch = function (input) {{
    var url = String((input && input.url) || input);
    if (/\\/api\\/[a-z-]+\\.php/.test(url)) {{
      return Promise.resolve(new Response(JSON.stringify({{ error: msg }}),
        {{ status: 503, headers: {{ 'Content-Type': 'application/json' }} }}));
    }}
    return realFetch.apply(this, arguments);
  }};
  document.addEventListener('submit', function (e) {{
    if ((e.target.getAttribute('method') || '').toLowerCase() === 'post') {{
      e.preventDefault();
      alert(msg);
    }}
  }}, true);
}})();
</script>"""

BANNER = (f'<div style="background:#2b0a12;color:#f5e6c8;font:500 13px/1.4 \'DM Sans\',sans-serif;'
          f'text-align:center;padding:6px 16px">{NOTICE}</div>')

NAMES = '|'.join(re.escape(n) for n in PAGES + list(REDIRECTS))
LINK = re.compile(rf'(?<![\w/\\-])({NAMES})\.php')


def rewrite_links(text):
    return LINK.sub(lambda m: REDIRECTS.get(m.group(1), m.group(1)) + '.html', text)


def main():
    if OUT.exists():
        shutil.rmtree(OUT)
    OUT.mkdir(parents=True)

    for d in COPY_DIRS:
        shutil.copytree(ROOT / d, OUT / d, ignore=shutil.ignore_patterns('.DS_Store'))
    for js in (OUT / 'assets').glob('*.js'):
        js.write_text(rewrite_links(js.read_text(encoding='utf-8')), encoding='utf-8')

    for page in PAGES:
        with urllib.request.urlopen(f'{SERVER}{page}.php') as response:
            html = response.read().decode('utf-8')
        html = rewrite_links(html)
        html = html.replace('<head>', '<head>\n' + SHIM, 1)
        html = re.sub(r'(<body[^>]*>)', r'\1' + BANNER, html, count=1)
        (OUT / f'{page}.html').write_text(html, encoding='utf-8')
        print(f'  {page}.html')

    (OUT / '.nojekyll').write_text('')  # serve files as-is
    print(f'Exported {len(PAGES)} pages to {OUT}')


if __name__ == '__main__':
    main()
