/* Log in / create account / staff login form (login.php, register.php, staff-login.php) */
import { api } from '../api.js';
import { Field, Notice, PasswordField, isEmail, useForm } from './ui.jsx';

export default function AuthForm({ mode, next }) {
  const isRegister = mode === 'register';
  const form = useForm({ name: '', email: '', password: '' });

  const validate = (v) => ({
    name: isRegister && !v.name.trim() && 'Enter your name.',
    email: !isEmail(v.email) && 'Enter a valid email address.',
    password: isRegister ? v.password.length < 8 && 'Use at least 8 characters.' : !v.password && 'Enter your password.',
  });

  const onSubmit = async (event) => {
    event.preventDefault();
    const ok = await form.submit((values) => api.post('auth', mode, values), { validate });
    if (ok) window.location.href = next;
  };

  return (
    <form onSubmit={onSubmit} noValidate>
      <Notice type={form.message?.type}>{form.message?.text}</Notice>
      {isRegister && <Field label="Full Name" autoComplete="name" {...form.bind('name')} error={form.errors.name} />}
      <Field label={mode === 'staff_login' ? 'Staff Email' : 'Email Address'} type="email" autoComplete="email" {...form.bind('email')} error={form.errors.email} />
      <PasswordField
        label="Password"
        autoComplete={isRegister ? 'new-password' : 'current-password'}
        {...form.bind('password')}
        error={form.errors.password}
        hint={isRegister ? 'At least 8 characters.' : undefined}
      />
      <button type="submit" className="btn btn-maroon btn-block" disabled={form.busy}>
        {form.busy ? 'Please wait…' : isRegister ? 'Create Account' : mode === 'staff_login' ? 'Log In to Staff Dashboard' : 'Log In'}
      </button>
    </form>
  );
}
