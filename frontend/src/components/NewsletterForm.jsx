/*
   Footer newsletter sign-up. Submits without leaving the page; subscribing
   requires ticking the "Yes, send me the newsletter" box.
*/
import { useId, useState } from 'react';
import { api } from '../api.js';
import { isEmail, useForm } from './ui.jsx';

export default function NewsletterForm({ email = '' }) {
  const id = useId();
  const form = useForm({ email, consent: false });
  const [done, setDone] = useState(null);
  const [mailed, setMailed] = useState(null);

  const onSubmit = async (event) => {
    event.preventDefault();
    let status = 'subscribed';
    const ok = await form.submit(
      async (v) => { const d = await api.post('newsletter', 'subscribe', v); status = d.status; setMailed(d.email || null); },
      {
        validate: (v) => ({
          email: !isEmail(v.email) && 'Enter a valid email address.',
          consent: !v.consent && 'Please check the box to confirm you’d like to subscribe.',
        }),
      },
    );
    if (ok) setDone(status);
  };

  if (done) {
    return (
      <div className="newsletter__done" role="status">
        <span className="newsletter__done-icon" aria-hidden="true">✓</span>
        <div>
          <strong>{done === 'already' ? 'You’re already subscribed!' : 'Thanks for subscribing!'}</strong>
          <p>
            {done === 'already'
              ? `${form.values.email} is already on our list. Thanks for staying connected.`
              : mailed?.status === 'sent'
                ? `We sent a confirmation to ${form.values.email}. Not there? Check Spam or Promotions.`
                : 'You’re on the list. We couldn’t send the confirmation email right now.'}
          </p>
        </div>
      </div>
    );
  }

  const error = form.errors.email || form.errors.consent || (form.message?.type === 'error' && !form.errors.email && !form.errors.consent ? form.message.text : null);

  return (
    <form className="newsletter__form" onSubmit={onSubmit} noValidate aria-labelledby="newsletter-title">
      <div className="newsletter__row">
        <label className="visually-hidden" htmlFor={`${id}-email`}>Email address</label>
        <input
          type="email"
          id={`${id}-email`}
          placeholder="Your email address"
          autoComplete="email"
          aria-invalid={form.errors.email ? true : undefined}
          aria-describedby={error ? `${id}-msg` : undefined}
          {...form.bind('email')}
        />
        <button type="submit" className="btn btn-primary" disabled={form.busy}>
          {form.busy ? 'Subscribing…' : 'Subscribe'}
        </button>
      </div>
      <label className={`newsletter__consent${form.errors.consent ? ' has-error' : ''}`}>
        <input
          type="checkbox"
          checked={form.values.consent}
          aria-invalid={form.errors.consent ? true : undefined}
          aria-describedby={error ? `${id}-msg` : undefined}
          onChange={(e) => form.set('consent', e.target.checked)}
        />
        <span>Yes, send me the Maroon 9 newsletter. I can unsubscribe anytime.</span>
      </label>
      {error && <p className="newsletter__message newsletter__message--error" id={`${id}-msg`} role="alert">{error}</p>}
    </form>
  );
}
