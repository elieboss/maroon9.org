/*
   Small wrapper around fetch for the PHP endpoints in api/.
   Sends the session's CSRF token on every POST and keeps it current
   (PHP issues a new one after login, sign-up and password changes).
*/

export class ApiError extends Error {
  constructor(message, status, fields) {
    super(message);
    this.status = status;
    this.fields = fields || {};
  }
}

const boot = window.M9 || { csrf: '', user: null, page: '' };

export function currentUser() {
  return boot.user;
}

async function request(endpoint, { method = 'GET', params, body } = {}) {
  const url = new URL(`api/${endpoint}.php`, document.baseURI);
  Object.entries(params || {}).forEach(([key, value]) => url.searchParams.set(key, value));

  const headers = { Accept: 'application/json' };
  if (method !== 'GET') {
    headers['Content-Type'] = 'application/json';
    headers['X-CSRF-Token'] = boot.csrf;
  }

  let response;
  try {
    response = await fetch(url, {
      method,
      headers,
      credentials: 'same-origin',
      body: body ? JSON.stringify(body) : undefined,
    });
  } catch {
    throw new ApiError('Couldn’t reach the server. Check your connection and try again.', 0);
  }

  let data;
  try {
    data = await response.json();
  } catch {
    throw new ApiError('The server sent an unexpected response. Please try again.', response.status);
  }

  if (data.csrf) boot.csrf = data.csrf;
  if ('user' in data) boot.user = data.user;
  if (!response.ok) throw new ApiError(data.error || 'Something went wrong.', response.status, data.fields);
  return data;
}

export const api = {
  get: (endpoint, params) => request(endpoint, { params }),
  post: (endpoint, action, body = {}) => request(endpoint, { method: 'POST', body: { action, ...body } }),
};

export async function logOut() {
  try {
    await api.post('auth', 'logout');
  } finally {
    window.location.href = 'index.php?signed_out=1';
  }
}
