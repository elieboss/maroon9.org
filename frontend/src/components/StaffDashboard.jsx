/* Staff dashboard: contact messages, events, sign-ups, and user/staff management */
import { useEffect, useMemo, useState } from 'react';
import { api } from '../api.js';
import { Badge, Field, Notice, Spinner, TabPanel, Tabs, downloadCsv, formatDate, formatTimestamp, useForm } from './ui.jsx';

const TABS = ['messages', 'events', 'signups', 'users'];
const REASON_LABELS = { enroll: 'Enroll a Youth', volunteer: 'Volunteer', donate: 'Donate', sponsor: 'Sponsor', other: 'Other', '': 'General' };

function Messages({ messages, act }) {
  const [filter, setFilter] = useState('new');
  const shown = messages.filter((m) => filter === 'all' || m.status === filter);

  return (
    <>
      <div className="toolbar">
        <div className="segmented" role="group" aria-label="Filter messages">
          {[['new', 'New'], ['handled', 'Handled'], ['all', 'All']].map(([id, label]) => (
            <button key={id} type="button" aria-pressed={filter === id} className={filter === id ? 'is-on' : ''} onClick={() => setFilter(id)}>
              {label}
            </button>
          ))}
        </div>
        <button type="button" className="text-btn" onClick={() => downloadCsv('contact-messages.csv', messages, [
          { label: 'Date', value: (m) => m.created_at }, { label: 'Name', value: (m) => m.name },
          { label: 'Email', value: (m) => m.email }, { label: 'Reason', value: (m) => REASON_LABELS[m.reason] },
          { label: 'Message', value: (m) => m.message }, { label: 'Status', value: (m) => m.status },
        ])}>Export CSV</button>
      </div>

      {shown.length === 0 ? (
        <p className="empty-state">{filter === 'new' ? 'No new messages. You’re all caught up.' : 'No messages here.'}</p>
      ) : (
        <ul className="message-list">
          {shown.map((m) => (
            <li key={m.id} className={`message${m.status === 'handled' ? ' message--handled' : ''}`}>
              <div className="message__head">
                <div>
                  <strong>{m.name}</strong> · <a href={`mailto:${m.email}`}>{m.email}</a>
                  <p className="item-list__meta">{formatTimestamp(m.created_at)}</p>
                </div>
                <Badge tone={m.reason === 'enroll' || m.reason === 'volunteer' ? 'gold' : 'neutral'}>{REASON_LABELS[m.reason] || m.reason}</Badge>
              </div>
              <p className="message__body">{m.message}</p>
              <div className="message__actions">
                <a className="text-btn" href={`mailto:${m.email}?subject=${encodeURIComponent('Re: your message to Maroon 9')}`}>Reply by email</a>
                <button type="button" className="text-btn" onClick={() => act('message_status', { id: m.id, status: m.status === 'new' ? 'handled' : 'new' })}>
                  {m.status === 'new' ? 'Mark handled' : 'Mark as new'}
                </button>
              </div>
            </li>
          ))}
        </ul>
      )}
    </>
  );
}

const BLANK_EVENT = { id: 0, title: '', event_date: '', location: '', cta_label: 'Register', description: '' };

function EventEditor({ event, onSave, onCancel }) {
  const form = useForm(event);
  const submit = async (e) => {
    e.preventDefault();
    await form.submit((v) => onSave(v), {
      validate: (v) => ({ title: !v.title.trim() && 'Enter a title.', event_date: !v.event_date && 'Choose a date.' }),
    });
  };
  return (
    <form className="panel event-editor" onSubmit={submit} noValidate>
      <h3 className="panel-title">{event.id ? 'Edit event' : 'New event'}</h3>
      <Notice type={form.message?.type}>{form.message?.text}</Notice>
      <div className="form-row">
        <Field label="Title" {...form.bind('title')} error={form.errors.title} />
        <Field label="Date" type="date" {...form.bind('event_date')} error={form.errors.event_date} />
      </div>
      <div className="form-row">
        <Field label="Location" {...form.bind('location')} />
        <Field label="Button label" hint="e.g. RSVP, Buy Tickets, Register" {...form.bind('cta_label')} />
      </div>
      <Field label="Description (optional)" as="textarea" maxLength={2000} {...form.bind('description')} />
      <div className="btn-group">
        <button type="submit" className="btn btn-maroon" disabled={form.busy}>{form.busy ? 'Saving…' : 'Save Event'}</button>
        <button type="button" className="text-btn" onClick={onCancel}>Cancel</button>
      </div>
    </form>
  );
}

function EventsAdmin({ events, setEvents }) {
  const [editing, setEditing] = useState(null);
  const [notice, setNotice] = useState(null);

  const save = async (values) => {
    const data = await api.post('events', 'save', values);
    setEvents(data.events);
    setEditing(null);
    setNotice({ type: 'success', text: `“${values.title}” was saved.` });
  };

  const remove = async (event) => {
    const extra = event.registrations ? ` ${event.registrations} registration(s) will also be removed.` : '';
    if (!window.confirm(`Delete “${event.title}”?${extra}`)) return;
    try {
      setEvents((await api.post('events', 'delete', { id: event.id })).events);
      setNotice({ type: 'success', text: `“${event.title}” was deleted.` });
    } catch (e) {
      setNotice({ type: 'error', text: e.message });
    }
  };

  return (
    <>
      <Notice type={notice?.type}>{notice?.text}</Notice>
      {editing ? (
        <EventEditor key={editing.id || 'new'} event={editing} onSave={save} onCancel={() => setEditing(null)} />
      ) : (
        <div className="toolbar">
          <button type="button" className="btn btn-maroon btn-sm" onClick={() => { setNotice(null); setEditing(BLANK_EVENT); }}>+ Add Event</button>
          {events.every((e) => e.is_past) && <span className="muted">All events are in the past. Add new dates so the Events page has something upcoming.</span>}
        </div>
      )}
      <div className="table-wrap">
        <table className="data-table">
          <thead>
            <tr><th>Date</th><th>Event</th><th>Location</th><th>Registered</th><th><span className="visually-hidden">Actions</span></th></tr>
          </thead>
          <tbody>
            {[...events].reverse().map((e) => (
              <tr key={e.id} className={e.is_past ? 'is-past' : ''}>
                <td>{formatDate(e.event_date, { month: 'short', day: 'numeric', year: 'numeric' })}{e.is_past && <> <Badge>Past</Badge></>}</td>
                <td>{e.title}</td>
                <td>{e.location}</td>
                <td>{e.registrations}</td>
                <td className="data-table__actions">
                  <button type="button" className="text-btn" onClick={() => { setNotice(null); setEditing(e); window.scrollTo({ top: 0, behavior: 'smooth' }); }}>Edit</button>
                  <button type="button" className="text-btn text-btn--danger" onClick={() => remove(e)}>Delete</button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </>
  );
}

function Signups({ data, act }) {
  const [view, setView] = useState('enrollments');
  const views = {
    enrollments: { label: 'Enrollments', rows: data.enrollments },
    registrations: { label: 'Event registrations', rows: data.registrations },
    volunteers: { label: 'Volunteers', rows: data.volunteers },
    newsletter: { label: 'Newsletter', rows: data.subscribers },
  };
  const exportCurrent = () => {
    const columns = {
      enrollments: [
        { label: 'Submitted', value: (r) => r.created_at }, { label: 'Youth', value: (r) => r.youth_name },
        { label: 'Age', value: (r) => r.youth_age }, { label: 'Program', value: (r) => data.programs[r.program] || r.program },
        { label: 'Parent/guardian', value: (r) => r.parent_name }, { label: 'Email', value: (r) => r.email },
        { label: 'Notes', value: (r) => r.notes }, { label: 'Status', value: (r) => r.status },
      ],
      registrations: [
        { label: 'Event date', value: (r) => r.event_date }, { label: 'Event', value: (r) => r.title },
        { label: 'Name', value: (r) => r.name }, { label: 'Email', value: (r) => r.email }, { label: 'Guests', value: (r) => r.guests },
      ],
      volunteers: [
        { label: 'Name', value: (r) => r.name }, { label: 'Email', value: (r) => r.email },
        { label: 'Interests', value: (r) => r.interests }, { label: 'Availability', value: (r) => r.availability },
        { label: 'Notes', value: (r) => r.notes }, { label: 'Updated', value: (r) => r.updated_at },
      ],
      newsletter: [
        { label: 'Email', value: (r) => r.email }, { label: 'Status', value: (r) => r.status },
        { label: 'Signed up', value: (r) => r.created_at }, { label: 'Last change', value: (r) => r.updated_at },
      ],
    }[view];
    downloadCsv(`${view}.csv`, views[view].rows, columns);
  };

  return (
    <>
      <div className="toolbar">
        <div className="segmented" role="group" aria-label="Sign-up type">
          {Object.entries(views).map(([id, v]) => (
            <button key={id} type="button" aria-pressed={view === id} className={view === id ? 'is-on' : ''} onClick={() => setView(id)}>
              {v.label} <span className="segmented__count">{v.rows.length}</span>
            </button>
          ))}
        </div>
        <button type="button" className="text-btn" onClick={exportCurrent} disabled={views[view].rows.length === 0}>Export CSV</button>
      </div>

      {views[view].rows.length === 0 ? (
        <p className="empty-state">Nothing here yet.</p>
      ) : (
        <div className="table-wrap">
          {view === 'enrollments' && (
            <table className="data-table">
              <thead><tr><th>Youth</th><th>Program</th><th>Parent / guardian</th><th>Notes</th><th>Status</th></tr></thead>
              <tbody>
                {data.enrollments.map((r) => (
                  <tr key={r.id}>
                    <td><strong>{r.youth_name}</strong>, {r.youth_age}<div className="item-list__meta">{formatTimestamp(r.created_at)}</div></td>
                    <td>{data.programs[r.program] || r.program}</td>
                    <td>{r.parent_name}<div className="item-list__meta"><a href={`mailto:${r.email}`}>{r.email}</a></div></td>
                    <td className="data-table__notes">{r.notes}</td>
                    <td>
                      <select aria-label={`Status for ${r.youth_name}`} value={r.status} onChange={(e) => act('enrollment_status', { id: r.id, status: e.target.value })}>
                        <option value="received">Received</option><option value="contacted">Contacted</option><option value="enrolled">Enrolled</option>
                      </select>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
          {view === 'registrations' && (
            <table className="data-table">
              <thead><tr><th>Event</th><th>Name</th><th>Email</th><th>Guests</th></tr></thead>
              <tbody>
                {data.registrations.map((r) => (
                  <tr key={r.id}>
                    <td>{r.title}<div className="item-list__meta">{formatDate(r.event_date)}</div></td>
                    <td>{r.name}</td>
                    <td><a href={`mailto:${r.email}`}>{r.email}</a></td>
                    <td>{r.guests}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
          {view === 'newsletter' && (
            <table className="data-table">
              <thead><tr><th>Email</th><th>Status</th><th>Signed up</th></tr></thead>
              <tbody>
                {data.subscribers.map((r) => (
                  <tr key={r.id} className={r.status === 'unsubscribed' ? 'is-past' : ''}>
                    <td><a href={`mailto:${r.email}`}>{r.email}</a></td>
                    <td><Badge tone={r.status === 'active' ? 'success' : 'neutral'}>{r.status === 'active' ? 'Subscribed' : 'Unsubscribed'}</Badge></td>
                    <td className="item-list__meta">{formatTimestamp(r.created_at)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
          {view === 'volunteers' && (
            <table className="data-table">
              <thead><tr><th>Name</th><th>Interests</th><th>Availability</th><th>Notes</th></tr></thead>
              <tbody>
                {data.volunteers.map((r) => (
                  <tr key={r.id}>
                    <td>{r.name}<div className="item-list__meta"><a href={`mailto:${r.email}`}>{r.email}</a></div></td>
                    <td>{r.interests}</td>
                    <td>{r.availability || '—'}</td>
                    <td className="data-table__notes">{r.notes}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>
      )}
    </>
  );
}

function Users({ users, me, act }) {
  const [query, setQuery] = useState('');
  const shown = useMemo(() => {
    const q = query.trim().toLowerCase();
    return q ? users.filter((u) => `${u.name} ${u.email}`.toLowerCase().includes(q)) : users;
  }, [users, query]);

  const setRole = (user, role) => {
    const text = role === 'staff'
      ? `Give ${user.name} staff access? They'll be able to see all messages, sign-ups and user accounts.`
      : `Remove staff access from ${user.name}?`;
    if (window.confirm(text)) act('set_role', { id: user.id, role });
  };

  return (
    <>
      <div className="toolbar">
        <input type="search" className="search-input" placeholder="Search by name or email" aria-label="Search users" value={query} onChange={(e) => setQuery(e.target.value)} />
        <span className="muted">{users.filter((u) => u.role === 'staff').length} staff · {users.filter((u) => u.role === 'user').length} users</span>
      </div>
      <div className="table-wrap">
        <table className="data-table">
          <thead><tr><th>Name</th><th>Role</th><th>Activity</th><th>Joined</th><th><span className="visually-hidden">Actions</span></th></tr></thead>
          <tbody>
            {shown.map((u) => (
              <tr key={u.id}>
                <td>
                  <strong>{u.name}</strong>{u.id === me && ' (you)'}
                  <div className="item-list__meta">{u.email} {u.email_verified ? <Badge tone="success">Confirmed</Badge> : <Badge>Unconfirmed</Badge>}</div>
                </td>
                <td><Badge tone={u.role === 'staff' ? 'maroon' : 'neutral'}>{u.role}</Badge></td>
                <td className="item-list__meta">{u.registrations} events · {u.enrollments} enrollments{u.volunteer ? ' · volunteer' : ''}</td>
                <td className="item-list__meta">{formatTimestamp(u.created_at)}</td>
                <td className="data-table__actions">
                  {u.id === me ? null : u.role === 'staff' ? (
                    <button type="button" className="text-btn text-btn--danger" onClick={() => setRole(u, 'user')}>Remove staff</button>
                  ) : (
                    <button type="button" className="text-btn" onClick={() => setRole(u, 'staff')}>Make staff</button>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </>
  );
}

export default function StaffDashboard({ me }) {
  const initial = new URLSearchParams(window.location.search).get('tab');
  const [tab, setTabState] = useState(TABS.includes(initial) ? initial : 'messages');
  const [data, setData] = useState(null);
  const [events, setEvents] = useState(null);
  const [error, setError] = useState(null);
  const [notice, setNotice] = useState(null);

  useEffect(() => {
    Promise.all([api.get('staff'), api.get('events')]).then(
      ([staff, ev]) => { setData(staff); setEvents(ev.events); },
      (e) => setError(e.message),
    );
  }, []);

  const setTab = (next) => {
    setTabState(next);
    setNotice(null);
    const url = new URL(window.location.href);
    url.searchParams.set('tab', next);
    window.history.replaceState(null, '', url);
  };

  const act = async (action, body) => {
    setNotice(null);
    try {
      setData(await api.post('staff', action, body));
    } catch (e) {
      setNotice({ type: 'error', text: e.message });
    }
  };

  if (error) return <Notice type="error">{error}</Notice>;
  if (!data || !events) return <Spinner label="Loading dashboard…" />;

  const newMessages = data.messages.filter((m) => m.status === 'new').length;
  const newEnrollments = data.enrollments.filter((e) => e.status === 'received').length;

  return (
    <div className="dashboard">
      <div className="stat-cards stat-cards--compact">
        <button type="button" className="stat-card" onClick={() => setTab('messages')}>
          <span className="stat-card__value">{newMessages}</span><span className="stat-card__label">New messages</span>
        </button>
        <button type="button" className="stat-card" onClick={() => setTab('events')}>
          <span className="stat-card__value">{events.filter((e) => !e.is_past).length}</span><span className="stat-card__label">Upcoming events</span>
        </button>
        <button type="button" className="stat-card" onClick={() => setTab('signups')}>
          <span className="stat-card__value">{newEnrollments}</span><span className="stat-card__label">New enrollments</span>
        </button>
        <button type="button" className="stat-card" onClick={() => setTab('users')}>
          <span className="stat-card__value">{data.users.length}</span><span className="stat-card__label">Accounts</span>
        </button>
      </div>

      <Tabs
        label="Dashboard sections"
        active={tab}
        onChange={setTab}
        tabs={[
          { id: 'messages', label: 'Messages', count: newMessages },
          { id: 'events', label: 'Events' },
          { id: 'signups', label: 'Sign-ups', count: newEnrollments },
          { id: 'users', label: 'Users & Staff' },
        ]}
      />
      <Notice type={notice?.type}>{notice?.text}</Notice>
      <TabPanel id={tab}>
        {tab === 'messages' && <Messages messages={data.messages} act={act} />}
        {tab === 'events' && <EventsAdmin events={events} setEvents={(next) => { setEvents(next); api.get('staff').then(setData, () => {}); }} />}
        {tab === 'signups' && <Signups data={data} act={act} />}
        {tab === 'users' && <Users users={data.users} me={me} act={act} />}
      </TabPanel>
    </div>
  );
}
