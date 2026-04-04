const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const pool = require('../db/pool');
const env = require('../config/env');

function signToken(user) {
  return jwt.sign(
    {
      sub: user.id,
      email: user.email
    },
    env.jwtSecret,
    {
      expiresIn: env.jwtExpiresIn
    }
  );
}

async function authEntry(req, res) {
  const email = String(req.body.email || '').trim().toLowerCase();

  if (!email) {
    return res.status(400).json({ error: 'Email is required.' });
  }

  const result = await pool.query(
    'SELECT id, email FROM users WHERE email = $1 LIMIT 1',
    [email]
  );

  if (result.rowCount === 0) {
    return res.status(200).json({
      nextStep: 'signup',
      redirectTo: `/auth/signup?email=${encodeURIComponent(email)}`
    });
  }

  return res.status(200).json({
    nextStep: 'password',
    redirectTo: `/auth/login?email=${encodeURIComponent(email)}`
  });
}

async function register(req, res) {
  const fullName = String(req.body.fullName || '').trim();
  const email = String(req.body.email || '').trim().toLowerCase();
  const password = String(req.body.password || '');

  if (!fullName || !email || !password) {
    return res.status(400).json({ error: 'Full name, email, and password are required.' });
  }

  const existing = await pool.query('SELECT id FROM users WHERE email = $1 LIMIT 1', [email]);
  if (existing.rowCount > 0) {
    return res.status(409).json({ error: 'An account with that email already exists.' });
  }

  const passwordHash = await bcrypt.hash(password, 12);
  const inserted = await pool.query(
    `
      INSERT INTO users (full_name, email, password_hash)
      VALUES ($1, $2, $3)
      RETURNING id, full_name, email, preferred_currency
    `,
    [fullName, email, passwordHash]
  );

  return res.status(201).json({
    user: inserted.rows[0],
    accessToken: signToken(inserted.rows[0])
  });
}

async function login(req, res) {
  const email = String(req.body.email || '').trim().toLowerCase();
  const password = String(req.body.password || '');

  if (!email || !password) {
    return res.status(400).json({ error: 'Email and password are required.' });
  }

  const result = await pool.query(
    `
      SELECT id, full_name, email, password_hash, preferred_currency
      FROM users
      WHERE email = $1
      LIMIT 1
    `,
    [email]
  );

  if (result.rowCount === 0) {
    return res.status(404).json({
      error: 'No account exists for that email.',
      nextStep: 'signup',
      redirectTo: `/auth/signup?email=${encodeURIComponent(email)}`
    });
  }

  const user = result.rows[0];
  const isValidPassword = await bcrypt.compare(password, user.password_hash);

  if (!isValidPassword) {
    return res.status(401).json({ error: 'Incorrect password.' });
  }

  await pool.query('UPDATE users SET last_login_at = NOW() WHERE id = $1', [user.id]);

  return res.status(200).json({
    user: {
      id: user.id,
      fullName: user.full_name,
      email: user.email,
      preferredCurrency: user.preferred_currency
    },
    accessToken: signToken(user)
  });
}

module.exports = {
  authEntry,
  register,
  login
};