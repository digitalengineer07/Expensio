function normalizeMinorUnits(amount) {
  if (typeof amount === 'bigint') {
    return amount;
  }

  if (typeof amount === 'number') {
    return BigInt(Math.round(amount));
  }

  if (typeof amount === 'string') {
    return BigInt(amount);
  }

  throw new Error('Unsupported monetary amount.');
}

function buildEqualSplits(totalAmountMinor, participants, currencyCode, note) {
  const total = normalizeMinorUnits(totalAmountMinor);
  const baseShare = total / BigInt(participants.length);
  let remainder = total % BigInt(participants.length);

  return participants.map((participant) => {
    const extra = remainder > 0n ? 1n : 0n;
    if (remainder > 0n) {
      remainder -= 1n;
    }

    return {
      participantUserId: participant.participantUserId,
      splitMethod: 'equal',
      owedAmountMinor: (baseShare + extra).toString(),
      percentageBasisPoints: null,
      exactAmountMinor: null,
      currencyCode,
      note: participant.note || note || null
    };
  });
}

function buildExactSplits(totalAmountMinor, participants, currencyCode, note) {
  const total = normalizeMinorUnits(totalAmountMinor);
  const exactTotal = participants.reduce(
    (sum, participant) => sum + normalizeMinorUnits(participant.exactAmountMinor),
    0n
  );

  if (exactTotal !== total) {
    throw new Error('Exact split amounts must add up to the expense total.');
  }

  return participants.map((participant) => ({
    participantUserId: participant.participantUserId,
    splitMethod: 'exact',
    owedAmountMinor: normalizeMinorUnits(participant.exactAmountMinor).toString(),
    percentageBasisPoints: null,
    exactAmountMinor: normalizeMinorUnits(participant.exactAmountMinor).toString(),
    currencyCode,
    note: participant.note || note || null
  }));
}

function buildPercentageSplits(totalAmountMinor, participants, currencyCode, note) {
  const total = normalizeMinorUnits(totalAmountMinor);
  const basisPointsTotal = participants.reduce(
    (sum, participant) => sum + Number(participant.percentageBasisPoints || 0),
    0
  );

  if (basisPointsTotal !== 10000) {
    throw new Error('Percentage split basis points must total exactly 10000.');
  }

  const provisional = participants.map((participant) => {
    const basisPoints = BigInt(participant.percentageBasisPoints);
    const raw = total * basisPoints;
    const owed = raw / 10000n;
    const remainder = raw % 10000n;

    return {
      participantUserId: participant.participantUserId,
      splitMethod: 'percentage',
      owedAmountMinor: owed,
      percentageBasisPoints: Number(participant.percentageBasisPoints),
      exactAmountMinor: null,
      currencyCode,
      note: participant.note || note || null,
      fractionalRemainder: remainder
    };
  });

  const allocated = provisional.reduce((sum, item) => sum + item.owedAmountMinor, 0n);
  let remainderMinorUnits = total - allocated;

  provisional
    .sort((left, right) => {
      if (left.fractionalRemainder === right.fractionalRemainder) {
        return left.participantUserId.localeCompare(right.participantUserId);
      }

      return left.fractionalRemainder > right.fractionalRemainder ? -1 : 1;
    })
    .forEach((item) => {
      if (remainderMinorUnits > 0n) {
        item.owedAmountMinor += 1n;
        remainderMinorUnits -= 1n;
      }
    });

  return provisional.map((participant) => ({
    participantUserId: participant.participantUserId,
    splitMethod: 'percentage',
    owedAmountMinor: participant.owedAmountMinor.toString(),
    percentageBasisPoints: participant.percentageBasisPoints,
    exactAmountMinor: null,
    currencyCode,
    note: participant.note
  }));
}

function splitExpense({
  totalAmountMinor,
  currencyCode,
  note = null,
  splitMethod,
  participants
}) {
  if (!Array.isArray(participants) || participants.length === 0) {
    throw new Error('At least one participant is required for a split.');
  }

  if (!currencyCode || !/^[A-Z]{3}$/.test(currencyCode)) {
    throw new Error('A valid three-letter currency code is required.');
  }

  if (splitMethod === 'equal') {
    return buildEqualSplits(totalAmountMinor, participants, currencyCode, note);
  }

  if (splitMethod === 'exact') {
    return buildExactSplits(totalAmountMinor, participants, currencyCode, note);
  }

  if (splitMethod === 'percentage') {
    return buildPercentageSplits(totalAmountMinor, participants, currencyCode, note);
  }

  throw new Error(`Unsupported split method: ${splitMethod}`);
}

module.exports = {
  splitExpense
};
