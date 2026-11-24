/* Show/Hide buttons for password inputs in plain PHP forms (reset-password.php).
   React forms use PasswordField in components/ui.jsx instead. */
export function initPasswordToggles() {
  document.querySelectorAll('[data-password-toggle]').forEach((wrapper) => {
    const input = wrapper.querySelector('input[type="password"]');
    if (!input) return;
    const label = wrapper.querySelector('label')?.textContent.trim().toLowerCase() || 'password';

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'password-field__toggle';
    const setVisible = (visible) => {
      input.type = visible ? 'text' : 'password';
      button.textContent = visible ? 'Hide' : 'Show';
      button.setAttribute('aria-label', `${visible ? 'Hide' : 'Show'} ${label}`);
    };
    setVisible(false);
    button.addEventListener('click', () => setVisible(input.type === 'password'));
    /* Submit as a password so browsers don't remember it as ordinary text */
    input.form?.addEventListener('submit', () => setVisible(false));
    input.autocapitalize = 'none';
    input.spellcheck = false;
    wrapper.append(button);
  });
}
