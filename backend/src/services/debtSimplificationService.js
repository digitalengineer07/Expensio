const Dinic = require('./dinic');

function toBigInt(value) {
  return typeof value === 'bigint' ? value : BigInt(value);
}

function buildNetBalances(debts) {
  const net = new Map();
  const allowedPairs = new Map();

  for (const debt of debts) {
    const amount = toBigInt(debt.amountMinor);
    const debtor = debt.debtorUserId;
    const creditor = debt.creditorUserId;

    net.set(debtor, (net.get(debtor) || 0n) - amount);
    net.set(creditor, (net.get(creditor) || 0n) + amount);
    allowedPairs.set(`${debtor}->${creditor}`, {
      debtorUserId: debtor,
      creditorUserId: creditor
    });
  }

  return { net, allowedPairs };
}

function materializeParticipants(net) {
  const debtors = [];
  const creditors = [];

  for (const [userId, balance] of net.entries()) {
    if (balance < 0n) {
      debtors.push({ userId, amount: balance * -1n });
    } else if (balance > 0n) {
      creditors.push({ userId, amount: balance });
    }
  }

  debtors.sort((left, right) => left.userId.localeCompare(right.userId));
  creditors.sort((left, right) => left.userId.localeCompare(right.userId));

  return { debtors, creditors };
}

function buildCandidateEdges({ debtors, creditors, allowedPairs }) {
  const candidates = [];

  for (const debtor of debtors) {
    for (const creditor of creditors) {
      const key = `${debtor.userId}->${creditor.userId}`;
      if (!allowedPairs.has(key)) {
        continue;
      }

      candidates.push({
        debtorUserId: debtor.userId,
        creditorUserId: creditor.userId,
        weight: debtor.amount < creditor.amount ? debtor.amount : creditor.amount
      });
    }
  }

  candidates.sort((left, right) => {
    if (left.weight === right.weight) {
      return `${left.debtorUserId}:${left.creditorUserId}`.localeCompare(
        `${right.debtorUserId}:${right.creditorUserId}`
      );
    }

    return left.weight < right.weight ? -1 : 1;
  });

  return candidates;
}

function runSettlementFlow({ debtors, creditors, candidates }) {
  const source = 0;
  const debtorOffset = 1;
  const creditorOffset = debtorOffset + debtors.length;
  const sink = creditorOffset + creditors.length;
  const graph = new Dinic(sink + 1);

  const totalRequired = debtors.reduce((sum, debtor) => sum + debtor.amount, 0n);
  const settlementEdges = [];
  const largeCapacity = totalRequired;

  debtors.forEach((debtor, index) => {
    graph.addEdge(source, debtorOffset + index, debtor.amount);
  });

  creditors.forEach((creditor, index) => {
    graph.addEdge(creditorOffset + index, sink, creditor.amount);
  });

  const debtorIndex = new Map(debtors.map((debtor, index) => [debtor.userId, index]));
  const creditorIndex = new Map(
    creditors.map((creditor, index) => [creditor.userId, index])
  );

  candidates.forEach((candidate) => {
    const from = debtorOffset + debtorIndex.get(candidate.debtorUserId);
    const to = creditorOffset + creditorIndex.get(candidate.creditorUserId);
    const edge = graph.addEdge(from, to, largeCapacity, candidate);
    settlementEdges.push({ candidate, edge });
  });

  const flow = graph.maxFlow(source, sink);
  if (flow !== totalRequired) {
    return null;
  }

  const settlements = settlementEdges
    .map(({ candidate, edge }) => {
      const reverseEdge = graph.graph[edge.to][edge.reverseIndex];
      return {
        debtorUserId: candidate.debtorUserId,
        creditorUserId: candidate.creditorUserId,
        amountMinor: reverseEdge.capacity
      };
    })
    .filter((item) => item.amountMinor > 0n);

  return {
    settlements,
    totalSettled: flow
  };
}

function greedilyPruneEdges({ debtors, creditors, candidates }) {
  const active = [...candidates];

  for (let index = 0; index < active.length;) {
    const nextCandidates = active.filter((_, edgeIndex) => edgeIndex !== index);
    const feasible = runSettlementFlow({
      debtors,
      creditors,
      candidates: nextCandidates
    });

    if (feasible) {
      active.splice(index, 1);
      continue;
    }

    index += 1;
  }

  return active;
}

function aggregateOriginalDebts(debts) {
  return debts.map((debt) => ({
    debtorUserId: debt.debtorUserId,
    creditorUserId: debt.creditorUserId,
    amountMinor: toBigInt(debt.amountMinor).toString()
  }));
}

function simplifyDebtsWithDinic(debts) {
  if (!Array.isArray(debts) || debts.length === 0) {
    return {
      fullySimplified: true,
      transactions: []
    };
  }

  const { net, allowedPairs } = buildNetBalances(debts);
  const { debtors, creditors } = materializeParticipants(net);

  if (debtors.length === 0 || creditors.length === 0) {
    return {
      fullySimplified: true,
      transactions: []
    };
  }

  const candidates = buildCandidateEdges({ debtors, creditors, allowedPairs });

  if (candidates.length === 0) {
    return {
      fullySimplified: false,
      transactions: aggregateOriginalDebts(debts)
    };
  }

  const prunedCandidates = greedilyPruneEdges({ debtors, creditors, candidates });
  const finalFlow = runSettlementFlow({
    debtors,
    creditors,
    candidates: prunedCandidates
  });

  if (!finalFlow) {
    return {
      fullySimplified: false,
      transactions: aggregateOriginalDebts(debts)
    };
  }

  return {
    fullySimplified: true,
    transactions: finalFlow.settlements.map((settlement) => ({
      debtorUserId: settlement.debtorUserId,
      creditorUserId: settlement.creditorUserId,
      amountMinor: settlement.amountMinor.toString()
    }))
  };
}

module.exports = {
  simplifyDebtsWithDinic
};