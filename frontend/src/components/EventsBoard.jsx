/*
   Events page: upcoming/past tabs, register (with guests) or cancel,
   and an "Add to calendar" download. Staff see registration counts.
*/
import { useState } from 'react';
import { api } from '../api.js';
import { Badge, Notice, TabPanel, Tabs, formatDate, parseDate } from './ui.jsx';
import Gallery from './Gallery.jsx';

function downloadIcs(event) {
  const start = event.event_date.replace(/-/g, '');
  const next = parseDate(event.event_date);
  next.setDate(next.getDate() + 1);
  const end = [next.getFullYear(), String(next.getMonth() + 1).padStart(2, '0'), String(next.getDate()).padStart(2, '0')].join('');
  const clean = (text) => text.replace(/[\\;,]/g, (c) => `\\${c}`).replace(/\n/g, '\\n');

  const ics = [
    'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Maroon 9//Events//EN', 'BEGIN:VEVENT',
    `UID:maroon9-event-${event.id}@maroon9`,
    `DTSTAMP:${new Date().toISOString().replace(/[-:]/g, '').slice(0, 15)}Z`,
    `DTSTART;VALUE=DATE:${start}`, `DTEND;VALUE=DATE:${end}`,
    `SUMMARY:${clean(event.title)}`, `LOCATION:${clean(event.location)}`,
    `DESCRIPTION:${clean(event.description || 'Maroon 9 event')}`,
    'END:VEVENT', 'END:VCALENDAR',
  ].join('\r\n');

  const link = Object.assign(document.createElement('a'), {
    href: URL.createObjectURL(new Blob([ics], { type: 'text/calendar' })),
    download: `${event.title.replace(/[^\w]+/g, '-').toLowerCase()}.ics`,
  });
  link.click();
  URL.revokeObjectURL(link.href);
}

function EventCard({ event, user, busy, onRegister, onUnregister }) {
  const [choosing, setChoosing] = useState(false);
  const [guests, setGuests] = useState(event.guests || 0);
  const date = parseDate(event.event_date);

  return (
    <article className={`event-card${event.is_past ? ' event-card--past' : ''}${event.registered ? ' event-card--registered' : ''}`}>
      <div className="event-card__date-bar">
        <div className="event-card__day">{date.getDate()}</div>
        <div className="event-card__month-year">
          {date.toLocaleDateString('en-US', { month: 'short' })}<br />{date.getFullYear()}
        </div>
        {event.registered && <span className="event-card__flag">✓ Registered</span>}
      </div>
      <div className="event-card__body">
        <h3 className="event-card__title">{event.title}</h3>
        {event.location && <p className="event-card__location">{event.location}</p>}
        {event.description && <p className="event-card__desc">{event.description}</p>}
        {'registrations' in event && (
          <p className="event-card__staff">{event.registrations} registered · <a href="staff.php?tab=events">Manage</a></p>
        )}

        {event.is_past ? (
          <Badge>Past event</Badge>
        ) : !user ? (
          <a href="login.php?next=events.php" className="event-card__cta">Log in to {event.cta_label.toLowerCase()}</a>
        ) : event.registered ? (
          <div className="event-card__actions">
            <span className="event-card__note">
              You{event.guests > 0 ? ` + ${event.guests} guest${event.guests > 1 ? 's' : ''}` : ''} are on the list.
            </span>
            <button type="button" className="text-btn" disabled={busy} onClick={() => onUnregister(event)}>Cancel registration</button>
          </div>
        ) : choosing ? (
          <div className="event-card__register">
            <label>
              Guests
              <select value={guests} onChange={(e) => setGuests(Number(e.target.value))}>
                {Array.from({ length: 11 }, (_, n) => <option key={n} value={n}>{n}</option>)}
              </select>
            </label>
            <button type="button" className="btn btn-maroon btn-sm" disabled={busy} onClick={() => onRegister(event, guests)}>
              {busy ? 'Saving…' : 'Confirm'}
            </button>
            <button type="button" className="text-btn" onClick={() => setChoosing(false)}>Back</button>
          </div>
        ) : (
          <button type="button" className="event-card__cta" onClick={() => setChoosing(true)}>{event.cta_label}</button>
        )}

        {!event.is_past && (
          <button type="button" className="text-btn event-card__calendar" onClick={() => downloadIcs(event)}>
            + Add to calendar
          </button>
        )}
      </div>
    </article>
  );
}

/* A featured past production: posters (click to enlarge) + description */
function HighlightCard({ highlight }) {
  return (
    <article className="highlight-card">
      <Gallery images={highlight.images} className="highlight-card__gallery" />
      <div className="highlight-card__body">
        <span className="highlight-card__badge">Highlight</span>
        <h3 className="highlight-card__title">{highlight.title}</h3>
        <p className="highlight-card__meta">{highlight.date} · {highlight.venue}</p>
        <p className="highlight-card__desc">{highlight.description}</p>
      </div>
    </article>
  );
}

export default function EventsBoard({ events: initialEvents, user, highlights = [] }) {
  const [events, setEvents] = useState(initialEvents);
  const upcoming = events.filter((e) => !e.is_past);
  const past = events.filter((e) => e.is_past).reverse();
  const [tab, setTab] = useState(upcoming.length ? 'upcoming' : 'past');
  const [busyId, setBusyId] = useState(null);
  const [notice, setNotice] = useState(null);

  const run = async (event, action, body, success) => {
    setBusyId(event.id);
    setNotice(null);
    try {
      const data = await api.post('events', action, { event_id: event.id, ...body });
      setEvents(data.events);
      setNotice({ type: 'success', text: success });
    } catch (error) {
      setNotice({ type: 'error', text: error.message });
    } finally {
      setBusyId(null);
    }
  };

  const list = tab === 'upcoming' ? upcoming : past;

  return (
    <div className="events-board">
      <Tabs
        label="Event lists"
        active={tab}
        onChange={setTab}
        tabs={[
          { id: 'upcoming', label: 'Upcoming', count: upcoming.length },
          { id: 'past', label: 'Past Events', count: past.length },
          ...(highlights.length ? [{ id: 'highlights', label: 'Highlights', count: highlights.length }] : []),
        ]}
      />
      <Notice type={notice?.type}>{notice?.text}</Notice>
      <TabPanel id={tab}>
        {tab === 'highlights' ? (
          <div className="events-highlights">
            {highlights.map((h) => <HighlightCard key={h.title} highlight={h} />)}
          </div>
        ) : list.length === 0 ? (
          <div className="empty-state">
            <p><strong>New dates are coming soon.</strong></p>
            <p>
              Follow <a href="https://www.linktr.ee/maroon9" target="_blank" rel="noopener">linktr.ee/maroon9</a> or{' '}
              <button type="button" className="text-btn" onClick={() => setTab('past')}>see past events</button>.
            </p>
          </div>
        ) : (
          <div className="events__grid">
            {list.map((event) => (
              <EventCard
                key={event.id}
                event={event}
                user={user}
                busy={busyId === event.id}
                onRegister={(e, guests) => run(e, 'register', { guests }, `You're registered for ${e.title} on ${formatDate(e.event_date)}.`)}
                onUnregister={(e) => run(e, 'unregister', {}, `Your registration for ${e.title} was cancelled.`)}
              />
            ))}
          </div>
        )}
      </TabPanel>
    </div>
  );
}
