'use client';

import { ArrowDownLeft, ArrowUpRight, BadgeDollarSign, Plus, Users } from 'lucide-react';
import {
  Bar,
  BarChart,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis
} from 'recharts';
import { ReceiptOcrUploader } from '@/components/receipts/ReceiptOcrUploader';

export type DashboardSnapshot = {
  userName: string;
  currencyCode: string;
  owedToYouMinor: number;
  youOweMinor: number;
  activeGroups: number;
  unsettledExpenses: number;
  recentNetChangePercent: number;
  dailySpending: Array<{
    day: string;
    food: number;
    commute: number;
    shopping: number;
    utilities: number;
  }>;
  groupBalances: Array<{
    groupName: string;
    counterparties: number;
    owedToYouMinor: number;
    youOweMinor: number;
    currencyCode: string;
  }>;
};

type UserDashboardProps = {
  snapshot: DashboardSnapshot;
};

const chartLegend = [
  { key: 'food', label: 'Food', color: '#1C8C5E' },
  { key: 'commute', label: 'Commute', color: '#3E6A8E' },
  { key: 'shopping', label: 'Shopping', color: '#7FA8C6' },
  { key: 'utilities', label: 'Utilities', color: '#B9CBD7' }
] as const;

function formatCurrency(minor: number, currencyCode: string) {
  return new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency: currencyCode,
    maximumFractionDigits: 2
  }).format(minor / 100);
}

export function UserDashboard({ snapshot }: UserDashboardProps) {
  const netMinor = snapshot.owedToYouMinor - snapshot.youOweMinor;
  const weeklyPersonalSpend = snapshot.dailySpending.reduce(
    (sum, day) => sum + day.food + day.commute + day.shopping + day.utilities,
    0
  );

  return (
    <main className="min-h-screen px-6 py-8 text-ink lg:px-8">
      <div className="mx-auto max-w-7xl space-y-8">
        <section className="rounded-[36px] border border-white/15 bg-ink px-7 py-7 text-white shadow-panel lg:px-10">
          <div className="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div className="space-y-4">
              <p className="text-sm font-semibold uppercase tracking-[0.25em] text-green-100">Dashboard</p>
              <div>
                <h1 className="font-display text-4xl leading-tight lg:text-5xl">Good evening, {snapshot.userName}.</h1>
                <p className="mt-3 max-w-2xl text-base leading-7 text-slate-200">
                  Your balances are current across active groups, and your personal spending pattern is ready for review.
                </p>
              </div>
            </div>
            <div className="flex flex-wrap gap-3">
              <button className="inline-flex items-center gap-2 rounded-full bg-success px-5 py-3 font-semibold text-white transition hover:bg-[#166f4b]">
                <Plus className="h-4 w-4" />
                Add Expense
              </button>
              <button className="inline-flex items-center gap-2 rounded-full border border-white/20 px-5 py-3 font-semibold text-white transition hover:bg-white/10">
                <Users className="h-4 w-4" />
                Create Trip/Group
              </button>
              <button className="inline-flex items-center gap-2 rounded-full border border-white/20 px-5 py-3 font-semibold text-white transition hover:bg-white/10">
                <BadgeDollarSign className="h-4 w-4" />
                Settle Up
              </button>
            </div>
          </div>
        </section>

        <section className="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
          <article className="rounded-[30px] border border-line bg-white p-6 shadow-panel">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-ink-soft">Amount Owed To You</p>
                <p className="mt-3 text-3xl font-bold text-ink">
                  {formatCurrency(snapshot.owedToYouMinor, snapshot.currencyCode)}
                </p>
              </div>
              <span className="rounded-2xl bg-success-soft p-3 text-success">
                <ArrowUpRight className="h-5 w-5" />
              </span>
            </div>
          </article>
          <article className="rounded-[30px] border border-line bg-white p-6 shadow-panel">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-ink-soft">Amount You Owe</p>
                <p className="mt-3 text-3xl font-bold text-ink">
                  {formatCurrency(snapshot.youOweMinor, snapshot.currencyCode)}
                </p>
              </div>
              <span className="rounded-2xl bg-[#EAF1F5] p-3 text-ink-soft">
                <ArrowDownLeft className="h-5 w-5" />
              </span>
            </div>
          </article>
          <article className="rounded-[30px] border border-line bg-white p-6 shadow-panel">
            <p className="text-sm font-medium text-ink-soft">Net Balance</p>
            <p className={`mt-3 text-3xl font-bold ${netMinor >= 0 ? 'text-success' : 'text-red-600'}`}>
              {formatCurrency(netMinor, snapshot.currencyCode)}
            </p>
            <p className="mt-3 text-sm text-ink-soft">
              {snapshot.recentNetChangePercent}% change versus the previous period.
            </p>
          </article>
          <article className="rounded-[30px] border border-line bg-white p-6 shadow-panel">
            <p className="text-sm font-medium text-ink-soft">Operational Snapshot</p>
            <div className="mt-4 space-y-3 text-sm text-ink">
              <div className="flex items-center justify-between">
                <span>Active groups</span>
                <strong>{snapshot.activeGroups}</strong>
              </div>
              <div className="flex items-center justify-between">
                <span>Unsettled expenses</span>
                <strong>{snapshot.unsettledExpenses}</strong>
              </div>
              <div className="flex items-center justify-between">
                <span>Personal spend this week</span>
                <strong>{formatCurrency(weeklyPersonalSpend, snapshot.currencyCode)}</strong>
              </div>
            </div>
          </article>
        </section>

        <section className="grid gap-6 xl:grid-cols-[1.35fr,0.65fr]">
          <article className="rounded-[32px] border border-line bg-white p-6 shadow-panel">
            <div className="flex flex-col gap-4 border-b border-line pb-5 md:flex-row md:items-end md:justify-between">
              <div>
                <p className="text-sm font-semibold uppercase tracking-[0.2em] text-success">Analytics</p>
                <h2 className="mt-2 font-display text-3xl text-ink">Daily personal spending</h2>
              </div>
              <div className="flex flex-wrap gap-3 text-sm text-ink-soft">
                {chartLegend.map((entry) => (
                  <span key={entry.key} className="inline-flex items-center gap-2 rounded-full bg-mist px-3 py-1.5">
                    <span className="h-2.5 w-2.5 rounded-full" style={{ backgroundColor: entry.color }} />
                    {entry.label}
                  </span>
                ))}
              </div>
            </div>
            <div className="mt-6 h-[360px]">
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={snapshot.dailySpending} barGap={6}>
                  <CartesianGrid stroke="#E3EBEF" vertical={false} />
                  <XAxis dataKey="day" tickLine={false} axisLine={false} />
                  <YAxis
                    tickFormatter={(value) =>
                      new Intl.NumberFormat('en-IN', {
                        style: 'currency',
                        currency: snapshot.currencyCode,
                        maximumFractionDigits: 0
                      }).format(Number(value) / 100)
                    }
                    tickLine={false}
                    axisLine={false}
                  />
                  <Tooltip
                    cursor={{ fill: 'rgba(15, 39, 66, 0.05)' }}
                    formatter={(value) => formatCurrency(Number(value), snapshot.currencyCode)}
                    contentStyle={{
                      borderRadius: 18,
                      borderColor: '#D6E1E6',
                      boxShadow: '0 18px 45px rgba(15, 39, 66, 0.12)'
                    }}
                  />
                  <Bar dataKey="food" stackId="spend" fill="#1C8C5E" radius={[8, 8, 0, 0]} />
                  <Bar dataKey="commute" stackId="spend" fill="#3E6A8E" radius={[8, 8, 0, 0]} />
                  <Bar dataKey="shopping" stackId="spend" fill="#7FA8C6" radius={[8, 8, 0, 0]} />
                  <Bar dataKey="utilities" stackId="spend" fill="#B9CBD7" radius={[8, 8, 0, 0]} />
                </BarChart>
              </ResponsiveContainer>
            </div>
          </article>

          <div className="space-y-6">
            <article className="rounded-[32px] border border-line bg-white p-6 shadow-panel">
              <div className="border-b border-line pb-5">
                <p className="text-sm font-semibold uppercase tracking-[0.2em] text-success">Balances</p>
                <h2 className="mt-2 font-display text-3xl text-ink">Group positions</h2>
              </div>
              <div className="mt-5 space-y-4">
                {snapshot.groupBalances.map((group) => {
                  const net = group.owedToYouMinor - group.youOweMinor;
                  return (
                    <div key={group.groupName} className="rounded-3xl border border-line bg-cloud p-4">
                      <div className="flex items-start justify-between gap-3">
                        <div>
                          <h3 className="text-lg font-semibold text-ink">{group.groupName}</h3>
                          <p className="text-sm text-ink-soft">{group.counterparties} counterparties</p>
                        </div>
                        <span className={`rounded-full px-3 py-1 text-sm font-semibold ${net >= 0 ? 'bg-success-soft text-success' : 'bg-red-50 text-red-600'}`}>
                          {net >= 0 ? 'Net positive' : 'Net payable'}
                        </span>
                      </div>
                      <div className="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div className="rounded-2xl bg-white p-3">
                          <p className="text-ink-soft">Owed to you</p>
                          <p className="mt-1 font-semibold text-ink">
                            {formatCurrency(group.owedToYouMinor, group.currencyCode)}
                          </p>
                        </div>
                        <div className="rounded-2xl bg-white p-3">
                          <p className="text-ink-soft">You owe</p>
                          <p className="mt-1 font-semibold text-ink">
                            {formatCurrency(group.youOweMinor, group.currencyCode)}
                          </p>
                        </div>
                      </div>
                    </div>
                  );
                })}
              </div>
            </article>

            <article className="rounded-[32px] border border-line bg-white p-6 shadow-panel">
              <div className="border-b border-line pb-5">
                <p className="text-sm font-semibold uppercase tracking-[0.2em] text-success">Receipt OCR</p>
                <h2 className="mt-2 font-display text-3xl text-ink">Scan and draft an expense</h2>
              </div>
              <div className="mt-5">
                <ReceiptOcrUploader />
              </div>
            </article>
          </div>
        </section>
      </div>
    </main>
  );
}



