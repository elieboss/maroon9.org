/*
   "Who We Work For": two rows of student portraits scrolling endlessly in
   opposite directions. Each row's photos are rendered twice and slid by
   exactly half the row width, so the loop is seamless. Hovering a row
   pauses it. With reduced motion it becomes a static grid.
*/

function Row({ photos, reverse }) {
  const items = [...photos, ...photos];
  return (
    <div className="people-row">
      <ul
        className={`people-row__track${reverse ? ' people-row__track--reverse' : ''}`}
        style={{ '--people-duration': `${photos.length * 5}s` }}
      >
        {items.map((src, i) => (
          <li key={`${src}-${i}`} className="people-row__item" aria-hidden={i >= photos.length || undefined}>
            <img src={src} alt="" loading="lazy" draggable="false" />
          </li>
        ))}
      </ul>
    </div>
  );
}

export default function PeopleMarquee({ photos }) {
  const half = Math.ceil(photos.length / 2);
  return (
    <div className="people-marquee" role="img" aria-label={`Portraits of ${photos.length} Maroon 9 students`}>
      <Row photos={photos.slice(0, half)} />
      <Row photos={photos.slice(half)} reverse />
    </div>
  );
}
