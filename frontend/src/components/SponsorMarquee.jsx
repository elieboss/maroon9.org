/*
   Endless scrolling strip of partner logos. Logos are grayscale; hovering
   one shows its color, and hovering the strip pauses it. The list is
   rendered twice and slid left by exactly half its width, so the loop is
   seamless. With reduced motion it becomes a static row.
*/

export default function SponsorMarquee({ sponsors }) {
  /* Second copy exists only to make the loop seamless, so it's hidden
     from screen readers */
  const items = [...sponsors, ...sponsors];

  return (
    <div className="marquee">
      <div className="marquee__viewport">
        <ul className="marquee__track" style={{ '--marquee-duration': `${sponsors.length * 5}s` }}>
          {items.map((sponsor, i) => {
            const copy = i >= sponsors.length;
            return (
              <li key={`${sponsor.name}-${i}`} className="marquee__item" aria-hidden={copy || undefined}>
                <div className="sponsor-logo" title={sponsor.name}>
                  <img src={sponsor.logo} alt={copy ? '' : sponsor.name} draggable="false" />
                </div>
              </li>
            );
          })}
        </ul>
      </div>
    </div>
  );
}
