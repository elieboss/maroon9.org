/* Shared building blocks: form fields, notices, tabs, dates, and a form-state hook */
import { useCallback, useId, useRef, useState } from 'react';
import { ApiError } from '../api.js';

export function Field({ label, error, hint, as: Tag = 'input', children, ...props }) {
  const id = useId();
  const describedBy = error ? `${id}-error` : hint ? `${id}-hint` : undefined;
  return (
    <div className={`form-group${error ? ' has-error' : ''}`}>
      <label htmlFor={id}>{label}</label>
      <Tag id={id} aria-invalid={error ? true : undefined} aria-describedby={describedBy} {...props}>
        {children}
      </Tag>
      {hint && !error && <p className="field-hint" id={`${id}-hint`}>{hint}</p>}
      {error && <p className="field-error" id={`${id}-error`}>{error}</p>}
    </div>
  );
}

export function CheckboxGroup({ legend, options, value, onChange, error }) {
  const toggle = (option) =>
    onChange(value.includes(option) ? value.filter((v) => v !== option) : [...value, option]);
  return (
    <fieldset className={`form-group checkbox-group${error ? ' has-error' : ''}`}>
      <legend>{legend}</legend>
      <div className="checkbox-group__options">
        {options.map((option) => (
          <label key={option} className={`chip${value.includes(option) ? ' chip--on' : ''}`}>
            <input type="checkbox" checked={value.includes(option)} onChange={() => toggle(option)} />
            {option}
          </label>
        ))}
      </div>
      {error && <p className="field-error">{error}</p>}
    </fieldset>
  );
}

export function Notice({ type = 'info', children }) {
  if (!children) return null;
  return (
    <div className={`notice notice--${type}`} role={type === 'error' ? 'alert' : 'status'}>
      {children}
    </div>
  );
}

export function Badge({ tone = 'neutral', children }) {
  return <span className={`badge badge--${tone}`}>{children}</span>;
}

export function Spinner({ label = 'Loading…' }) {
  return (
    <p className="loading" role="status">
      <span className="spinner" aria-hidden="true" /> {label}
    </p>
  );
}

/* Accessible tabs: arrow keys move between tabs, the panel follows */
export function Tabs({ tabs, active, onChange, label }) {
  const refs = useRef({});
  const onKeyDown = (event) => {
    const index = tabs.findIndex((t) => t.id === active);
    const step = { ArrowRight: 1, ArrowLeft: -1 }[event.key];
    if (!step) return;
    event.preventDefault();
    const next = tabs[(index + step + tabs.length) % tabs.length];
    onChange(next.id);
    refs.current[next.id]?.focus();
  };
  return (
    <div className="tabs" role="tablist" aria-label={label} onKeyDown={onKeyDown}>
      {tabs.map((tab) => (
        <button
          key={tab.id}
          ref={(el) => (refs.current[tab.id] = el)}
          type="button"
          role="tab"
          id={`tab-${tab.id}`}
          aria-selected={tab.id === active}
          aria-controls={`panel-${tab.id}`}
          tabIndex={tab.id === active ? 0 : -1}
          className={`tabs__tab${tab.id === active ? ' tabs__tab--active' : ''}`}
          onClick={() => onChange(tab.id)}
        >
          {tab.label}
          {tab.count > 0 && <span className="tabs__count">{tab.count}</span>}
        </button>
      ))}
    </div>
  );
}

export function TabPanel({ id, children }) {
  return (
    <div role="tabpanel" id={`panel-${id}`} aria-labelledby={`tab-${id}`} className="tab-panel">
      {children}
    </div>
  );
}

/* "2025-06-14" -> Date in local time (new Date("2025-06-14") would be UTC midnight) */
export function parseDate(isoDate) {
  const [y, m, d] = isoDate.slice(0, 10).split('-').map(Number);
  return new Date(y, m - 1, d);
}

/* Today as "YYYY-MM-DD" in the visitor's local time */
export function todayIso() {
  const d = new Date();
  return [d.getFullYear(), String(d.getMonth() + 1).padStart(2, '0'), String(d.getDate()).padStart(2, '0')].join('-');
}

export function formatDate(isoDate, options = { month: 'long', day: 'numeric', year: 'numeric' }) {
  return parseDate(isoDate).toLocaleDateString('en-US', options);
}

/* SQLite timestamps are UTC "YYYY-MM-DD HH:MM:SS" */
export function formatTimestamp(timestamp) {
  return new Date(timestamp.replace(' ', 'T') + 'Z').toLocaleString('en-US', {
    month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit',
  });
}

/*
   Form state with server-side validation errors.
   submit(fn) runs fn(values); ApiError field messages land in errors.
*/
export function useForm(initial) {
  const [values, setValues] = useState(initial);
  const [errors, setErrors] = useState({});
  const [message, setMessage] = useState(null);
  const [busy, setBusy] = useState(false);

  const set = useCallback((name, value) => {
    setValues((v) => ({ ...v, [name]: value }));
    setErrors((e) => (e[name] ? { ...e, [name]: undefined } : e));
  }, []);

  const bind = (name) => ({
    name,
    value: values[name] ?? '',
    onChange: (event) => set(name, event.target.value),
  });

  const submit = async (fn, { validate } = {}) => {
    setMessage(null);
    const clientErrors = validate ? validate(values) : {};
    if (Object.values(clientErrors).some(Boolean)) {
      setErrors(clientErrors);
      setMessage({ type: 'error', text: 'Please fix the highlighted fields.' });
      return false;
    }
    setBusy(true);
    try {
      await fn(values);
      setErrors({});
      return true;
    } catch (error) {
      setErrors(error instanceof ApiError ? error.fields : {});
      setMessage({ type: 'error', text: error.message });
      return false;
    } finally {
      setBusy(false);
    }
  };

  return { values, setValues, set, bind, errors, message, setMessage, busy, submit };
}

/* Sentence telling the visitor whether their confirmation email went out.
   `email` is the { status, to } object the PHP endpoints return. */
export function emailNote(email) {
  if (!email) return '';
  if (email.status === 'sent') {
    return `A confirmation email was sent to ${email.to}. If you don't see it in a few minutes, check your Spam or Promotions folder.`;
  }
  return 'We saved your submission, but couldn’t email a confirmation right now. Our team still has your details.';
}

export const isEmail = (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim());

/* Download rows as a CSV file (staff exports) */
export function downloadCsv(filename, rows, columns) {
  /* Prefix values Excel would run as formulas (user-submitted text could start with "=") */
  const escape = (value) => {
    const text = String(value ?? '');
    return `"${(/^[=+\-@\t\r]/.test(text) ? `'${text}` : text).replace(/"/g, '""')}"`;
  };
  const lines = [columns.map((c) => escape(c.label)).join(',')];
  rows.forEach((row) => lines.push(columns.map((c) => escape(c.value(row))).join(',')));
  const blob = new Blob([lines.join('\r\n')], { type: 'text/csv;charset=utf-8' });
  const link = Object.assign(document.createElement('a'), { href: URL.createObjectURL(blob), download: filename });
  link.click();
  URL.revokeObjectURL(link.href);
}
