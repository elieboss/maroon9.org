/* My Account: overview, event registrations, youth enrollment, volunteering, profile */
import { useEffect, useState } from 'react';
import { api, logOut } from '../api.js';
import { Badge, Field, Notice, Spinner, TabPanel, Tabs, formatDate, formatTimestamp, isEmail, todayIso, useForm } from './ui.jsx';
import VolunteerForm from './VolunteerForm.jsx';

const TABS = ['overview', 'events', 'enroll', 'volunteer', 'profile'];
const ENROLLMENT_TONE = { received: 'neutral', contacted: 'gold', enrolled: 'success' };

function useTabInUrl(initial) {
  const [tab, setTab] = useState(TABS.includes(initial) ? initial : 'overview');
  const change = (next) => {
    setTab(next);
    const url = new URL(window.location.href);
    url.searchParams.set('tab', next);
    url.searchParams.delete('denied');
    window.history.replaceState(null, '', url);
  };
  return [tab, change];
}

/* Shown until the user clicks the link in their sign-up confirmation email */
function ConfirmEmailBanner({ user, setData }) {
  const [state, setState] = useState({ busy: false, notice: null });
  if (user.email_verified) return null;
  const resend = async () => {
    setState({ busy: true, notice: null });
    try {
      setData(await api.post('account', 'resend_verification'));
      setState({ busy: false, notice: { type: 'success', text: `A new confirmation link was sent to ${user.email}.` } });
    } catch (e) {
      setState({ busy: false, notice: { type: 'error', text: e.message } });
    }
  };
  return (
    <div className="confirm-banner" role="status">
      <div>
        <strong>Please confirm your email address.</strong>
        <p>We sent a confirmation link to <strong>{user.email}</strong>. Check your inbox (and spam folder).</p>
        {state.notice && <p className={`confirm-banner__note confirm-banner__note--${state.notice.type}`}>{state.notice.text}</p>}
      </div>
      <button type="button" className="btn btn-outline btn-outline--dark btn-sm" onClick={resend} disabled={state.busy}>
        {state.busy ? 'Sending…' : 'Resend confirmation email'}
      </button>
    </div>
  );
}

function Overview({ data, go }) {
  const upcoming = data.registrations.filter((r) => r.event_date >= todayIso());
  const cards = [
    { label: 'Upcoming events', value: upcoming.length, tab: 'events', action: 'View registrations' },
    { label: 'Youth enrollments', value: data.enrollments.length, tab: 'enroll', action: 'Enroll a youth' },
    { label: 'Volunteer', value: data.volunteer ? 'Signed up' : 'Not yet', tab: 'volunteer', action: data.volunteer ? 'Update sign-up' : 'Sign up' },
  ];
  return (
    <>
      <div className="stat-cards">
        {cards.map((card) => (
          <button key={card.tab} type="button" className="stat-card" onClick={() => go(card.tab)}>
            <span className="stat-card__value">{card.value}</span>
            <span className="stat-card__label">{card.label}</span>
            <span className="stat-card__action">{card.action} →</span>
          </button>
        ))}
      </div>

      <h3 className="panel-title">Your messages</h3>
      {data.messages.length === 0 ? (
        <p className="muted">You haven't sent any messages yet. <a href="contact.php">Contact us</a></p>
      ) : (
        <ul className="item-list">
          {data.messages.map((m) => (
            <li key={m.id} className="item-list__row">
              <div>
                <p className="item-list__text">{m.message}</p>
                <p className="item-list__meta">{formatTimestamp(m.created_at)}</p>
              </div>
              <Badge tone={m.status === 'handled' ? 'success' : 'neutral'}>{m.status === 'handled' ? 'Answered' : 'Received'}</Badge>
            </li>
          ))}
        </ul>
      )}
    </>
  );
}

function EventRegistrations({ data, reload }) {
  const [notice, setNotice] = useState(null);
  const cancel = async (r) => {
    if (!window.confirm(`Cancel your registration for ${r.title}?`)) return;
    try {
      await api.post('events', 'unregister', { event_id: r.event_id });
      await reload();
      setNotice({ type: 'success', text: `Your registration for ${r.title} was cancelled.` });
    } catch (e) {
      setNotice({ type: 'error', text: e.message });
    }
  };
  const today = todayIso();

  return (
    <>
      <Notice type={notice?.type}>{notice?.text}</Notice>
      {data.registrations.length === 0 ? (
        <div className="empty-state">
          <p>You aren't registered for any events.</p>
          <a href="events.php" className="btn btn-maroon">Browse Events</a>
        </div>
      ) : (
        <ul className="item-list">
          {data.registrations.map((r) => (
            <li key={r.id} className="item-list__row">
              <div>
                <p className="item-list__title">{r.title}</p>
                <p className="item-list__meta">
                  {formatDate(r.event_date)} · {r.location}{r.guests > 0 && ` · +${r.guests} guest${r.guests > 1 ? 's' : ''}`}
                </p>
              </div>
              {r.event_date < today ? <Badge>Past</Badge> : <button type="button" className="text-btn" onClick={() => cancel(r)}>Cancel</button>}
            </li>
          ))}
        </ul>
      )}
    </>
  );
}

function Enrollments({ data, setData }) {
  const programs = data.programs;
  const form = useForm({ youth_name: '', youth_age: '', program: '', notes: '' });

  const submit = async (event) => {
    event.preventDefault();
    const ok = await form.submit(async (v) => setData(await api.post('account', 'enroll', v)), {
      validate: (v) => ({
        youth_name: !v.youth_name.trim() && 'Enter the youth’s name.',
        youth_age: !(Number(v.youth_age) >= 5 && Number(v.youth_age) <= 17) && 'Programs serve ages 5–17.',
        program: !v.program && 'Choose a program.',
      }),
    });
    if (ok) {
      form.setValues({ youth_name: '', youth_age: '', program: '', notes: '' });
      form.setMessage({ type: 'success', text: 'Enrollment received! Our team will contact you about next steps.' });
    }
  };

  const withdraw = async (e) => {
    if (!window.confirm(`Withdraw the enrollment for ${e.youth_name}?`)) return;
    try {
      setData(await api.post('account', 'withdraw_enrollment', { id: e.id }));
    } catch (err) {
      form.setMessage({ type: 'error', text: err.message });
    }
  };

  return (
    <div className="split">
      <form className="panel" onSubmit={submit} noValidate>
        <h3 className="panel-title">Enroll a youth</h3>
        <Notice type={form.message?.type}>{form.message?.text}</Notice>
        <Field label="Youth’s full name" {...form.bind('youth_name')} error={form.errors.youth_name} />
        <Field label="Age" type="number" min="5" max="17" inputMode="numeric" {...form.bind('youth_age')} error={form.errors.youth_age} />
        <Field label="Program" as="select" {...form.bind('program')} error={form.errors.program}>
          <option value="">Choose a program…</option>
          {Object.entries(programs).map(([key, label]) => <option key={key} value={key}>{label}</option>)}
        </Field>
        <Field label="Notes (optional)" as="textarea" maxLength={2000} placeholder="Interests, experience, schedule, accessibility needs…" {...form.bind('notes')} />
        <button type="submit" className="btn btn-maroon" disabled={form.busy}>{form.busy ? 'Submitting…' : 'Submit Enrollment'}</button>
      </form>

      <div>
        <h3 className="panel-title">Your enrollments</h3>
        {data.enrollments.length === 0 ? (
          <p className="muted">No enrollments yet.</p>
        ) : (
          <ul className="item-list">
            {data.enrollments.map((e) => (
              <li key={e.id} className="item-list__row">
                <div>
                  <p className="item-list__title">{e.youth_name}, {e.youth_age}</p>
                  <p className="item-list__meta">{programs[e.program] || e.program} · {formatTimestamp(e.created_at)}</p>
                </div>
                <div className="item-list__side">
                  <Badge tone={ENROLLMENT_TONE[e.status]}>{e.status}</Badge>
                  {e.status === 'received' && <button type="button" className="text-btn" onClick={() => withdraw(e)}>Withdraw</button>}
                </div>
              </li>
            ))}
          </ul>
        )}
      </div>
    </div>
  );
}

function Profile({ data, setData }) {
  const profile = useForm({ name: data.user.name, email: data.user.email });
  const password = useForm({ current_password: '', new_password: '', confirm: '' });

  const saveProfile = async (event) => {
    event.preventDefault();
    const ok = await profile.submit(async (v) => setData(await api.post('account', 'profile', v)), {
      validate: (v) => ({ name: !v.name.trim() && 'Enter your name.', email: !isEmail(v.email) && 'Enter a valid email address.' }),
    });
    if (ok) profile.setMessage({ type: 'success', text: 'Profile saved. If you changed your email, check the new inbox for a confirmation link.' });
  };

  const savePassword = async (event) => {
    event.preventDefault();
    const ok = await password.submit(async (v) => setData(await api.post('account', 'password', v)), {
      validate: (v) => ({
        current_password: !v.current_password && 'Enter your current password.',
        new_password: v.new_password.length < 8 && 'Use at least 8 characters.',
        confirm: v.confirm !== v.new_password && 'Passwords don’t match.',
      }),
    });
    if (ok) {
      password.setValues({ current_password: '', new_password: '', confirm: '' });
      password.setMessage({ type: 'success', text: 'Password changed.' });
    }
  };

  return (
    <div className="split">
      <form className="panel" onSubmit={saveProfile} noValidate>
        <h3 className="panel-title">Profile</h3>
        <Notice type={profile.message?.type}>{profile.message?.text}</Notice>
        <Field label="Full Name" autoComplete="name" {...profile.bind('name')} error={profile.errors.name} />
        <Field label="Email Address" type="email" autoComplete="email" {...profile.bind('email')} error={profile.errors.email} />
        <p className="panel__meta">Member since {formatTimestamp(data.user.created_at)}</p>
        <button type="submit" className="btn btn-maroon" disabled={profile.busy}>{profile.busy ? 'Saving…' : 'Save Profile'}</button>
      </form>

      <form className="panel" onSubmit={savePassword} noValidate>
        <h3 className="panel-title">Change password</h3>
        <Notice type={password.message?.type}>{password.message?.text}</Notice>
        <Field label="Current password" type="password" autoComplete="current-password" {...password.bind('current_password')} error={password.errors.current_password} />
        <Field label="New password" type="password" autoComplete="new-password" hint="At least 8 characters." {...password.bind('new_password')} error={password.errors.new_password} />
        <Field label="Confirm new password" type="password" autoComplete="new-password" {...password.bind('confirm')} error={password.errors.confirm} />
        <button type="submit" className="btn btn-maroon" disabled={password.busy}>{password.busy ? 'Saving…' : 'Change Password'}</button>
        <hr className="panel__divider" />
        <button type="button" className="btn btn-outline btn-outline--dark" onClick={logOut}>Sign Out</button>
      </form>
    </div>
  );
}

export default function AccountDashboard({ tab: initialTab }) {
  const [tab, setTab] = useTabInUrl(initialTab);
  const [data, setData] = useState(null);
  const [error, setError] = useState(null);

  const load = () => api.get('account').then(setData, (e) => setError(e.message));
  useEffect(() => { load(); }, []);

  if (error) return <Notice type="error">{error}</Notice>;
  if (!data) return <Spinner label="Loading your account…" />;

  const tabs = [
    { id: 'overview', label: 'Overview' },
    { id: 'events', label: 'My Events', count: data.registrations.length },
    { id: 'enroll', label: 'Enroll a Youth', count: data.enrollments.length },
    { id: 'volunteer', label: 'Volunteer' },
    { id: 'profile', label: 'Profile' },
  ];

  return (
    <div className="dashboard">
      <div className="dashboard__header">
        <p className="muted">Signed in as <strong>{data.user.email}</strong>{data.user.role === 'staff' && <> · <a href="staff.php">Staff dashboard</a></>}</p>
        <button type="button" className="btn btn-outline btn-outline--dark btn-sm" onClick={logOut}>Sign Out</button>
      </div>
      <ConfirmEmailBanner user={data.user} setData={setData} />
      <Tabs label="Account sections" tabs={tabs} active={tab} onChange={setTab} />
      <TabPanel id={tab}>
        {tab === 'overview' && <Overview data={data} go={setTab} />}
        {tab === 'events' && <EventRegistrations data={data} reload={load} />}
        {tab === 'enroll' && <Enrollments data={data} setData={setData} />}
        {tab === 'volunteer' && (
          <>
            <p className="section-body">Tell us how you'd like to help. You can update or withdraw anytime.</p>
            <VolunteerForm volunteer={data.volunteer} onSaved={setData} />
          </>
        )}
        {tab === 'profile' && <Profile data={data} setData={setData} />}
      </TabPanel>
    </div>
  );
}
