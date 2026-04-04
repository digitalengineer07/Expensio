const pool = require('../db/pool');
const { simplifyDebtsWithDinic } = require('../services/debtSimplificationService');

async function simplifyGroupDebts(req, res) {
  const { groupId } = req.params;
  const requestedCurrency = String(req.query.currency || req.body.currencyCode || '').trim();

  if (!groupId) {
    return res.status(400).json({ error: 'groupId is required.' });
  }

  const groupResult = await pool.query(
    'SELECT id, name, base_currency FROM groups WHERE id = $1 LIMIT 1',
    [groupId]
  );

  if (groupResult.rowCount === 0) {
    return res.status(404).json({ error: 'Group not found.' });
  }

  const group = groupResult.rows[0];
  const currencyCode = requestedCurrency || group.base_currency;

  const balancesResult = await pool.query(
    `
      SELECT debtor_user_id, creditor_user_id, net_amount_minor
      FROM balances
      WHERE group_id = $1
        AND currency_code = $2
        AND net_amount_minor > 0
    `,
    [groupId, currencyCode]
  );

  const debts = balancesResult.rows.map((row) => ({
    debtorUserId: row.debtor_user_id,
    creditorUserId: row.creditor_user_id,
    amountMinor: row.net_amount_minor
  }));

  const simplification = simplifyDebtsWithDinic(debts);

  return res.status(200).json({
    groupId,
    currencyCode,
    fullySimplified: simplification.fullySimplified,
    transactionCount: simplification.transactions.length,
    transactions: simplification.transactions
  });
}

module.exports = {
  simplifyGroupDebts
};