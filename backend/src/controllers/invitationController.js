const crypto = require('crypto');
const pool = require('../db/pool');
const env = require('../config/env');

function hashToken(token) {
  return crypto.createHash('sha256').update(token).digest('hex');
}

async function createGroupInvitation(req, res) {
  const { groupId } = req.params;
  const createdByUserId = req.auth.sub;
  const inviteeEmail = req.body.email ? String(req.body.email).trim().toLowerCase() : null;

  const membership = await pool.query(
    `
      SELECT gm.user_id, g.name
      FROM group_members gm
      INNER JOIN groups g ON g.id = gm.group_id
      WHERE gm.group_id = $1
        AND gm.user_id = $2
        AND gm.is_active = TRUE
      LIMIT 1
    `,
    [groupId, createdByUserId]
  );

  if (membership.rowCount === 0) {
    return res.status(403).json({ error: 'Only active group members can create invites.' });
  }

  const rawToken = crypto.randomBytes(24).toString('hex');
  const tokenHash = hashToken(rawToken);
  const expiresAt = new Date(Date.now() + env.invitationTtlHours * 60 * 60 * 1000);

  const insertResult = await pool.query(
    `
      INSERT INTO group_invitations (
        group_id,
        created_by_user_id,
        invitee_email,
        token_hash,
        expires_at
      )
      VALUES ($1, $2, $3, $4, $5)
      RETURNING id, expires_at, status
    `,
    [groupId, createdByUserId, inviteeEmail, tokenHash, expiresAt]
  );

  const invitation = insertResult.rows[0];

  return res.status(201).json({
    invitationId: invitation.id,
    status: invitation.status,
    expiresAt: invitation.expires_at,
    inviteUrl: `${env.appOrigin}/invite/${rawToken}`
  });
}

async function acceptGroupInvitation(req, res) {
  const token = String(req.params.token || '').trim();
  const acceptedByUserId = req.auth.sub;

  if (!token) {
    return res.status(400).json({ error: 'Invitation token is required.' });
  }

  const invitationResult = await pool.query(
    `
      SELECT id, group_id, invitee_email, status, expires_at
      FROM group_invitations
      WHERE token_hash = $1
      LIMIT 1
    `,
    [hashToken(token)]
  );

  if (invitationResult.rowCount === 0) {
    return res.status(404).json({ error: 'Invitation not found.' });
  }

  const invitation = invitationResult.rows[0];
  const hasExpired = new Date(invitation.expires_at).getTime() <= Date.now();

  if (invitation.status !== 'pending' || hasExpired) {
    return res.status(410).json({ error: 'Invitation is no longer valid.' });
  }

  await pool.query(
    `
      INSERT INTO group_members (group_id, user_id, role, is_active)
      VALUES ($1, $2, 'member', TRUE)
      ON CONFLICT (group_id, user_id)
      DO UPDATE SET is_active = TRUE, left_at = NULL
    `,
    [invitation.group_id, acceptedByUserId]
  );

  await pool.query(
    `
      UPDATE group_invitations
      SET status = 'accepted',
          accepted_by_user_id = $1,
          accepted_at = NOW()
      WHERE id = $2
    `,
    [acceptedByUserId, invitation.id]
  );

  return res.status(200).json({
    groupId: invitation.group_id,
    status: 'accepted'
  });
}

module.exports = {
  createGroupInvitation,
  acceptGroupInvitation
};