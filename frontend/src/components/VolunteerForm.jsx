/* Volunteer sign-up form, used on Get Involved and in My Account */
import { api } from '../api.js';
import { CheckboxGroup, Field, Notice, formatTimestamp, useForm } from './ui.jsx';

export const VOLUNTEER_INTERESTS = [
  'Events & performances', 'Backstage & costumes', 'Mentoring youth', 'Teaching artist support',
  'Fundraising', 'Photography & social media', 'Office & admin',
];
export const VOLUNTEER_AVAILABILITY = ['Weekday daytime', 'Weekday evenings', 'Weekends', 'Summer programs'];

const split = (text) => (text ? text.split(', ').filter(Boolean) : []);

export default function VolunteerForm({ volunteer, onSaved }) {
  const form = useForm({
    interests: split(volunteer?.interests),
    availability: split(volunteer?.availability),
    notes: volunteer?.notes || '',
  });

  const save = async (event) => {
    event.preventDefault();
    const ok = await form.submit(
      async (v) => {
        const data = await api.post('account', 'volunteer', {
          interests: v.interests.join(', '), availability: v.availability.join(', '), notes: v.notes,
        });
        onSaved(data);
      },
      { validate: (v) => ({ interests: v.interests.length === 0 && 'Choose at least one way to help.' }) },
    );
    if (ok) form.setMessage({ type: 'success', text: volunteer ? 'Your volunteer details were updated.' : 'Thanks for signing up! Our team will reach out soon.' });
  };

  const withdraw = async () => {
    if (!window.confirm('Withdraw your volunteer sign-up?')) return;
    const ok = await form.submit(async () => onSaved(await api.post('account', 'withdraw_volunteer')));
    if (ok) {
      form.setValues({ interests: [], availability: [], notes: '' });
      form.setMessage({ type: 'success', text: 'Your volunteer sign-up was withdrawn.' });
    }
  };

  return (
    <form className="panel" onSubmit={save} noValidate>
      {volunteer && <p className="panel__meta">Signed up · last updated {formatTimestamp(volunteer.updated_at)}</p>}
      <Notice type={form.message?.type}>{form.message?.text}</Notice>
      <CheckboxGroup
        legend="How would you like to help?"
        options={VOLUNTEER_INTERESTS}
        value={form.values.interests}
        onChange={(v) => form.set('interests', v)}
        error={form.errors.interests}
      />
      <CheckboxGroup
        legend="When are you available?"
        options={VOLUNTEER_AVAILABILITY}
        value={form.values.availability}
        onChange={(v) => form.set('availability', v)}
      />
      <Field label="Anything else we should know? (optional)" as="textarea" maxLength={2000} {...form.bind('notes')} />
      <div className="btn-group">
        <button type="submit" className="btn btn-maroon" disabled={form.busy}>
          {form.busy ? 'Saving…' : volunteer ? 'Update Sign-Up' : 'Sign Up to Volunteer'}
        </button>
        {volunteer && <button type="button" className="text-btn" onClick={withdraw} disabled={form.busy}>Withdraw sign-up</button>}
      </div>
    </form>
  );
}
