/* Contact form with live validation; saves to the database via api/contact.php */
import { useState } from 'react';
import { api } from '../api.js';
import { Field, Notice, emailNote, isEmail, useForm } from './ui.jsx';

const MESSAGE_MAX = 5000;

const validate = (v) => ({
  name: !v.name.trim() && 'Enter your full name.',
  email: !isEmail(v.email) && 'Enter a valid email address.',
  message: v.message.trim().length < 10 && 'Tell us a little more (at least 10 characters).',
});

export default function ContactForm({ reasons, user }) {
  const blank = { name: user?.name || '', email: user?.email || '', reason: '', message: '' };
  const form = useForm(blank);
  const [sent, setSent] = useState(false);
  const [email, setEmail] = useState(null);

  const onSubmit = async (event) => {
    event.preventDefault();
    const ok = await form.submit(async (values) => setEmail((await api.post('contact', 'send', values)).email), { validate });
    if (ok) setSent(true);
  };

  if (sent) {
    return (
      <div className="success-panel" role="status">
        <div className="success-panel__icon" aria-hidden="true">✓</div>
        <h3>Thanks, {form.values.name.split(' ')[0]}!</h3>
        <p>Your message was sent. We'll reply to <strong>{form.values.email}</strong> soon.</p>
        <p className="success-panel__email">{emailNote(email)}</p>
        {user && <p><a href="account.php">See your messages in My Account</a></p>}
        <button type="button" className="btn btn-outline btn-outline--dark" onClick={() => { form.setValues(blank); setSent(false); }}>
          Send another message
        </button>
      </div>
    );
  }

  return (
    <form onSubmit={onSubmit} noValidate>
      <Notice type={form.message?.type}>{form.message?.text}</Notice>
      <Field label="Full Name" placeholder="Your full name" autoComplete="name" {...form.bind('name')} error={form.errors.name} />
      <Field label="Email Address" type="email" placeholder="your@email.com" autoComplete="email" {...form.bind('email')} error={form.errors.email} />
      <Field label="Reason for Contact" as="select" {...form.bind('reason')} error={form.errors.reason}>
        {Object.entries(reasons).map(([value, label]) => <option key={value} value={value}>{label}</option>)}
      </Field>
      <Field
        label="Message"
        as="textarea"
        placeholder="Tell us how we can help..."
        maxLength={MESSAGE_MAX}
        {...form.bind('message')}
        error={form.errors.message}
        hint={`${form.values.message.length} / ${MESSAGE_MAX}`}
      />
      <button type="submit" className="btn btn-maroon" disabled={form.busy}>
        {form.busy ? 'Sending…' : 'Send Message'}
      </button>
    </form>
  );
}
