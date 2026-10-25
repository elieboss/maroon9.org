/*
   "Latest News" tab on the edge of every page. Opens a side panel listing
   recent articles from maroon9.org. Uses <dialog> so focus stays inside
   while it's open and Escape closes it.
*/
import { useEffect, useRef, useState } from 'react';

export default function NewsPanel({ articles }) {
  const [open, setOpen] = useState(false);
  const dialogRef = useRef(null);
  const tabRef = useRef(null);

  useEffect(() => {
    const dialog = dialogRef.current;
    if (open && !dialog.open) dialog.showModal();
    if (!open && dialog.open) dialog.close();
  }, [open]);

  const onClose = () => {
    setOpen(false);
    tabRef.current?.focus();
  };

  return (
    <>
      <button ref={tabRef} type="button" className="news-tab" aria-haspopup="dialog" onClick={() => setOpen(true)}>
        <span className="news-tab__dot" aria-hidden="true" />
        Latest News
      </button>

      <dialog
        ref={dialogRef}
        className="news-panel"
        aria-labelledby="news-panel-title"
        onClose={onClose}
        onClick={(event) => event.target === dialogRef.current && dialogRef.current.close()}
      >
        <div className="news-panel__inner">
          <header className="news-panel__header">
            <div>
              <span className="section-label">From Our Blog</span>
              <h2 className="news-panel__title" id="news-panel-title">Latest News</h2>
            </div>
            <button type="button" className="news-panel__close" onClick={() => dialogRef.current.close()} aria-label="Close latest news">×</button>
          </header>

          <ul className="news-panel__list">
            {articles.map((article) => {
              const [headline, ...rest] = article.title.split(': ');
              return (
                <li key={article.href}>
                  <a className="news-card" href={article.href} target="_blank" rel="noopener">
                    <img className="news-card__image" src={article.image} alt="" loading="lazy" />
                    <span className="news-card__body">
                      <span className="news-card__tag">{article.tag}</span>
                      <span className="news-card__title">{headline}</span>
                      {rest.length > 0 && <span className="news-card__subtitle">{rest.join(': ')}</span>}
                      <span className="news-card__cta">
                        Read article <span aria-hidden="true">↗</span>
                        <span className="visually-hidden"> (opens in a new tab)</span>
                      </span>
                    </span>
                  </a>
                </li>
              );
            })}
          </ul>
        </div>
      </dialog>
    </>
  );
}
