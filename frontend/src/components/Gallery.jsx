/*
   Photo grid that opens a full-screen viewer. Uses <dialog> so focus is
   trapped and Escape closes it natively; arrow keys move between photos.
*/
import { useEffect, useRef, useState } from 'react';

export default function Gallery({ images, className = 'photo-grid' }) {
  const [index, setIndex] = useState(null);
  const dialogRef = useRef(null);
  const triggerRefs = useRef([]);
  const lastOpened = useRef(0);

  const open = (i) => {
    lastOpened.current = i;
    setIndex(i);
  };
  const step = (delta) => setIndex((i) => (i + delta + images.length) % images.length);

  useEffect(() => {
    const dialog = dialogRef.current;
    if (index !== null && !dialog.open) dialog.showModal();
  }, [index]);

  const onClose = () => {
    setIndex(null);
    triggerRefs.current[lastOpened.current]?.focus();
  };

  const onKeyDown = (event) => {
    if (event.key === 'ArrowRight') step(1);
    if (event.key === 'ArrowLeft') step(-1);
  };

  const current = index !== null ? images[index] : null;

  return (
    <>
      <div className={className}>
        {images.map((image, i) => (
          <button
            key={image.src}
            ref={(el) => (triggerRefs.current[i] = el)}
            type="button"
            className="photo-grid__button"
            onClick={() => open(i)}
            aria-label={`View larger: ${image.alt}`}
          >
            <img src={image.src} alt={image.alt} className="photo-grid__image" loading="lazy" />
          </button>
        ))}
      </div>

      <dialog
        ref={dialogRef}
        className="lightbox"
        aria-label="Photo viewer"
        onClose={onClose}
        onKeyDown={onKeyDown}
        onClick={(event) => event.target === dialogRef.current && dialogRef.current.close()}
      >
        {current && (
          <figure className="lightbox__figure">
            <img src={current.src} alt={current.alt} className="lightbox__image" />
            <figcaption className="lightbox__caption">
              <span>{current.alt}</span>
              <span className="lightbox__count">{index + 1} / {images.length}</span>
            </figcaption>
          </figure>
        )}
        {images.length > 1 && (
          <>
            <button type="button" className="lightbox__nav lightbox__nav--prev" onClick={() => step(-1)} aria-label="Previous photo">‹</button>
            <button type="button" className="lightbox__nav lightbox__nav--next" onClick={() => step(1)} aria-label="Next photo">›</button>
          </>
        )}
        <button type="button" className="lightbox__close" onClick={() => dialogRef.current.close()} aria-label="Close photo viewer">×</button>
      </dialog>
    </>
  );
}
