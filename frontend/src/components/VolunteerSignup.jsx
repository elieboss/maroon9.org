/* Get Involved page: volunteer form for logged-in users, log-in prompt otherwise */
import { useEffect, useState } from 'react';
import { api } from '../api.js';
import { Notice, Spinner } from './ui.jsx';
import VolunteerForm from './VolunteerForm.jsx';

export default function VolunteerSignup({ user }) {
  const [account, setAccount] = useState(null);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (user) api.get('account').then(setAccount, (e) => setError(e.message));
  }, [user]);

  if (!user) {
    const next = encodeURIComponent('getinvolved.php#volunteer');
    return (
      <div className="panel panel--prompt">
        <p>Volunteer sign-ups are saved to your Maroon 9 account so you can update them anytime.</p>
        <div className="btn-group">
          <a href={`login.php?next=${next}`} className="btn btn-maroon">Log In to Sign Up</a>
          <a href={`register.php?next=${next}`} className="btn btn-outline btn-outline--dark">Create an Account</a>
        </div>
      </div>
    );
  }
  if (error) return <Notice type="error">{error}</Notice>;
  if (!account) return <Spinner />;
  return <VolunteerForm volunteer={account.volunteer} onSaved={setAccount} />;
}
