import Link from 'next/link';
import {
  ArrowRight,
  BadgeCheck,
  Bot,
  BriefcaseBusiness,
  Check,
  CircleGauge,
  CreditCard,
  Globe2,
  Newspaper,
  Receipt,
  ShieldCheck,
  Sparkles,
  Users,
  WalletCards,
  type LucideIcon
} from 'lucide-react';

type CardItem = {
  title: string;
  description: string;
  icon: LucideIcon;
  eyebrow: string;
};

type Plan = {
  name: string;
  price: number;
  summary: string;
  highlight?: boolean;
  features: string[];
};

const heroStats = [
  { value: '48%', label: 'fewer settlement handoffs after simplification' },
  { value: '8 currencies', label: 'handled cleanly with one source of truth' },
  { value: '3 mins', label: 'to turn a receipt into a tracked shared expense' }
];

const operatingMetrics = [
  { label: 'Shared balance accuracy', value: '99.2%' },
  { label: 'Average group settlement', value: '?18,425' },
  { label: 'Receipt match confidence', value: '92%' },
  { label: 'Active trip workspaces', value: '126' }
];

const featureCards: CardItem[] = [
  {
    eyebrow: 'AI receipts',
    title: 'Scan paper receipts into structured spend',
    description:
      'Extract merchants, totals, line items, and notes in seconds so shared expenses do not get stuck in photo galleries.',
    icon: Receipt
  },
  {
    eyebrow: 'Group balance engine',
    title: 'See who owes whom without spreadsheet cleanup',
    description:
      'Track live debtor-creditor positions per group, then simplify debt paths before anyone settles up.',
    icon: WalletCards
  },
  {
    eyebrow: 'Trust controls',
    title: 'Invite only the people who belong in the room',
    description:
      'JWT sessions, secure invite links, and auditable notes keep high-context money conversations protected.',
    icon: ShieldCheck
  },
  {
    eyebrow: 'Rupee-first clarity',
    title: 'Stay grounded in local spending patterns',
    description:
      'Present totals, budgets, and weekly burn in INR so teams and households read numbers the way they already think.',
    icon: Globe2
  }
];

const workflow = [
  {
    step: '01',
    title: 'Create a trusted space',
    description:
      'Spin up a trip, home, or project group and invite members with secure links before the first rupee is spent.',
    icon: Users
  },
  {
    step: '02',
    title: 'Add expenses however they arrive',
    description:
      'Use equal, exact, or percentage splits, attach notes, and scan receipts to keep every decision documented.',
    icon: Bot
  },
  {
    step: '03',
    title: 'Close the loop with fewer transfers',
    description:
      'Let the balance engine simplify obligations so everyone settles faster with less awkward back-and-forth.',
    icon: CircleGauge
  }
];

const plans: Plan[] = [
  {
    name: 'Starter',
    price: 0,
    summary: 'For households and friend groups getting their first clean money system in place.',
    features: ['Up to 3 active groups', 'Equal and exact splits', 'Basic receipt capture']
  },
  {
    name: 'Growth',
    price: 349,
    summary: 'For busy teams, trips, and shared homes that need OCR, analytics, and live balance discipline.',
    highlight: true,
    features: ['Unlimited groups', 'AI receipt extraction', 'Debt simplification engine', 'Analytics dashboard']
  },
  {
    name: 'Scale',
    price: 799,
    summary: 'For operators managing many projects, members, and compliance-sensitive money trails.',
    features: ['Admin controls', 'Priority support', 'Custom export workflows', 'Advanced audit history']
  }
];

const journalPosts = [
  {
    category: 'Guide',
    title: 'How to run a trip budget that nobody resents',
    summary: 'A practical framework for splitting transport, food, and stays while keeping the group chat calm.',
    icon: BriefcaseBusiness
  },
  {
    category: 'Product',
    title: 'Why OCR should create context, not just totals',
    summary: 'Turning receipt scans into usable notes, categories, and confident follow-up actions.',
    icon: Newspaper
  },
  {
    category: 'Strategy',
    title: 'The real value of debt simplification in shared finance',
    summary: 'Fewer transfers means less friction, faster trust repair, and cleaner month-end closeouts.',
    icon: Sparkles
  }
];

function formatRupees(amount: number) {
  return new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency: 'INR',
    maximumFractionDigits: 0
  }).format(amount);
}

export default function LandingPage() {
  return (
    <main className="relative overflow-x-hidden bg-[radial-gradient(circle_at_top,rgba(28,140,94,0.12),transparent_24%),linear-gradient(180deg,#eef4f7_0%,#ffffff_44%,#f7fbfc_100%)] text-ink">
      <div className="absolute inset-x-0 top-0 h-[760px] bg-[radial-gradient(circle_at_top_right,rgba(28,140,94,0.2),transparent_24%),linear-gradient(135deg,#0f2742_0%,#17344f_58%,#204b54_100%)]" />
      <div className="absolute right-[6%] top-32 hidden h-56 w-56 rounded-full bg-success/15 blur-3xl lg:block" />
      <div className="absolute left-[10%] top-[34rem] hidden h-44 w-44 rounded-full bg-white/10 blur-3xl lg:block" />

      <header className="sticky top-0 z-30 border-b border-white/10 bg-ink/75 backdrop-blur-xl">
        <div className="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
          <div>
            <p className="font-display text-[2rem] tracking-tight text-white">Expensio</p>
            <p className="-mt-1 text-sm text-white/65">Shared money, handled with confidence</p>
          </div>

          <nav className="hidden items-center gap-9 text-sm font-semibold text-slate-200 lg:flex">
            <a href="#features" className="transition hover:text-white">
              Features
            </a>
            <a href="#how-it-works" className="transition hover:text-white">
              How it Works
            </a>
            <a href="#pricing" className="transition hover:text-white">
              Pricing
            </a>
            <a href="#blog" className="transition hover:text-white">
              Blog
            </a>
          </nav>

          <div className="flex items-center gap-3">
            <Link href="/auth" className="hidden text-sm font-semibold text-white transition hover:text-green-100 sm:inline-flex">
              Sign In
            </Link>
            <Link
              href="/auth"
              className="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-ink shadow-[0_16px_40px_rgba(6,20,33,0.18)] transition hover:bg-mist"
            >
              Sign Up
            </Link>
          </div>
        </div>
      </header>

      <section className="relative z-10 mx-auto max-w-7xl px-6 pb-20 pt-10 lg:px-8 lg:pb-24 lg:pt-16">
        <div className="grid gap-14 lg:grid-cols-[1.02fr,0.98fr] lg:items-center">
          <div className="animate-rise space-y-9 text-white">
            <span className="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold tracking-[0.08em] text-green-100 backdrop-blur">
              <span className="h-2.5 w-2.5 rounded-full bg-success shadow-[0_0_18px_rgba(28,140,94,0.8)]" />
              NEW: SMART AI RECEIPTS SCANNING
            </span>

            <div className="space-y-5">
              <h1 className="max-w-4xl font-display text-5xl leading-[0.98] text-white sm:text-6xl lg:text-7xl">
                Master your money flow without making shared spending feel heavy.
              </h1>
              <p className="max-w-2xl text-lg leading-8 text-slate-200 sm:text-xl">
                Expensio helps households, trips, and teams split bills, scan receipts, monitor balances, and settle up with less noise and more trust.
              </p>
            </div>

            <div className="flex flex-wrap gap-4">
              <Link
                href="/auth"
                className="inline-flex items-center gap-2 rounded-full bg-success px-6 py-3.5 text-base font-semibold text-white transition hover:bg-[#166f4b]"
              >
                Start Free
                <ArrowRight className="h-4 w-4" />
              </Link>
              <a
                href="#features"
                className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-6 py-3.5 text-base font-semibold text-white transition hover:bg-white/10"
              >
                Explore Features
              </a>
            </div>

            <div className="grid gap-5 pt-2 sm:grid-cols-3">
              {heroStats.map((item, index) => (
                <div
                  key={item.label}
                  className="rounded-[28px] border border-white/10 bg-white/6 p-4 backdrop-blur"
                  style={{ animationDelay: `${index * 120}ms` }}
                >
                  <p className="text-2xl font-bold text-white">{item.value}</p>
                  <p className="mt-2 text-sm leading-6 text-slate-300">{item.label}</p>
                </div>
              ))}
            </div>
          </div>

          <div className="relative min-h-[620px] animate-rise [animation-delay:160ms]">
            <div className="absolute -right-2 top-2 hidden font-display text-[13rem] leading-none text-white/5 lg:block">?</div>
            <div className="absolute left-0 top-10 w-[72%] rounded-[34px] border border-white/12 bg-white/10 p-5 shadow-panel backdrop-blur-xl animate-float-slow">
              <div className="rounded-[28px] bg-white p-5 text-ink">
                <div className="flex items-start justify-between gap-4 border-b border-line pb-4">
                  <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.24em] text-ink-soft">Group pulse</p>
                    <h2 className="mt-2 font-display text-3xl">Goa Escape</h2>
                  </div>
                  <span className="rounded-full bg-success-soft px-3 py-1 text-sm font-semibold text-success">
                    4 members live
                  </span>
                </div>

                <div className="mt-5 grid gap-3 sm:grid-cols-2">
                  <div className="rounded-3xl bg-cloud p-4">
                    <p className="text-sm text-ink-soft">You are owed</p>
                    <p className="mt-2 text-2xl font-bold text-success">?18,425</p>
                  </div>
                  <div className="rounded-3xl bg-cloud p-4">
                    <p className="text-sm text-ink-soft">You owe</p>
                    <p className="mt-2 text-2xl font-bold text-ink">?6,324</p>
                  </div>
                </div>

                <div className="mt-5 rounded-[28px] border border-line bg-white p-4">
                  <div className="flex items-center justify-between text-sm text-ink-soft">
                    <span>Debt simplification</span>
                    <span className="font-semibold text-success">3 transfers instead of 7</span>
                  </div>
                  <div className="mt-4 space-y-3">
                    {[
                      'Aarav pays Meera ?4,210',
                      'Meera pays Dev ?2,980',
                      'Rohan pays Aarav ?1,350'
                    ].map((line) => (
                      <div key={line} className="flex items-center justify-between rounded-2xl bg-cloud px-4 py-3 text-sm font-medium text-ink">
                        <span>{line}</span>
                        <BadgeCheck className="h-4 w-4 text-success" />
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            </div>

            <div className="absolute bottom-4 left-3 z-10 w-[46%] rounded-[30px] bg-white p-4 text-ink shadow-panel animate-float-fast">
              <div className="flex items-center gap-3">
                <div className="rounded-2xl bg-success-soft p-3 text-success">
                  <Receipt className="h-5 w-5" />
                </div>
                <div>
                  <p className="text-sm font-semibold text-ink">Receipt ready</p>
                  <p className="text-xs text-ink-soft">Cafe Indigo � OCR confidence 92%</p>
                </div>
              </div>
              <div className="mt-4 space-y-2 rounded-[24px] bg-cloud p-4 text-sm text-ink-soft">
                <div className="flex items-center justify-between">
                  <span>3 cappuccinos</span>
                  <span className="font-semibold text-ink">?780</span>
                </div>
                <div className="flex items-center justify-between">
                  <span>Garlic bread</span>
                  <span className="font-semibold text-ink">?320</span>
                </div>
                <div className="flex items-center justify-between border-t border-line pt-2">
                  <span>Total</span>
                  <span className="text-base font-bold text-success">?1,100</span>
                </div>
              </div>
            </div>

            <div className="absolute bottom-0 right-0 w-[62%] rounded-[36px] border border-white/10 bg-ink p-6 text-white shadow-[0_30px_80px_rgba(8,22,34,0.32)] animate-rise [animation-delay:320ms]">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-xs font-semibold uppercase tracking-[0.2em] text-green-100">Monthly runway</p>
                  <h3 className="mt-2 font-display text-3xl">?42,300 left</h3>
                </div>
                <div className="rounded-3xl border border-white/10 bg-white/5 p-3 text-green-100">
                  <CreditCard className="h-5 w-5" />
                </div>
              </div>
              <div className="mt-5 h-3 overflow-hidden rounded-full bg-white/10">
                <div className="h-full w-[68%] rounded-full bg-[linear-gradient(90deg,#28a56f_0%,#8fe2b6_100%)]" />
              </div>
              <div className="mt-5 grid gap-3 text-sm text-slate-200">
                <div className="flex items-center justify-between rounded-2xl bg-white/5 px-4 py-3">
                  <span>Rent & utilities</span>
                  <span className="font-semibold text-white">?18,200</span>
                </div>
                <div className="flex items-center justify-between rounded-2xl bg-white/5 px-4 py-3">
                  <span>Shared travel</span>
                  <span className="font-semibold text-white">?9,480</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="relative z-10 mx-auto max-w-7xl px-6 pb-6 lg:px-8">
        <div className="grid gap-4 rounded-[34px] border border-white/70 bg-white/85 p-4 shadow-panel backdrop-blur sm:grid-cols-2 lg:grid-cols-4 lg:p-5">
          {operatingMetrics.map((metric) => (
            <div key={metric.label} className="rounded-[26px] bg-cloud px-5 py-4">
              <p className="text-xs font-semibold uppercase tracking-[0.22em] text-ink-soft">{metric.label}</p>
              <p className="mt-2 text-2xl font-bold text-ink">{metric.value}</p>
            </div>
          ))}
        </div>
      </section>

      <section id="features" className="relative z-10 mx-auto max-w-7xl px-6 py-20 lg:px-8">
        <div className="flex flex-col gap-5 lg:max-w-3xl">
          <p className="text-sm font-semibold uppercase tracking-[0.28em] text-success">Features</p>
          <h2 className="font-display text-4xl leading-tight text-ink sm:text-5xl">
            A bill-splitting product that feels calmer, clearer, and more accountable.
          </h2>
          <p className="text-lg leading-8 text-ink-soft">
            Expensio goes beyond tracking totals. It gives every shared money decision context, history, and a clean way to close the loop.
          </p>
        </div>

        <div className="mt-12 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
          {featureCards.map(({ eyebrow, title, description, icon: Icon }, index) => (
            <article
              key={title}
              className="rounded-[32px] border border-line bg-white p-6 shadow-panel animate-rise"
              style={{ animationDelay: `${index * 100}ms` }}
            >
              <div className="flex items-center justify-between">
                <span className="text-xs font-semibold uppercase tracking-[0.24em] text-success">{eyebrow}</span>
                <div className="rounded-2xl bg-success-soft p-3 text-success">
                  <Icon className="h-5 w-5" />
                </div>
              </div>
              <h3 className="mt-6 text-2xl font-semibold leading-tight text-ink">{title}</h3>
              <p className="mt-4 text-base leading-7 text-ink-soft">{description}</p>
            </article>
          ))}
        </div>
      </section>

      <section id="how-it-works" className="relative z-10 bg-[#f1f7f4] py-20">
        <div className="mx-auto max-w-7xl px-6 lg:px-8">
          <div className="grid gap-12 lg:grid-cols-[0.76fr,1.24fr]">
            <div className="space-y-5">
              <p className="text-sm font-semibold uppercase tracking-[0.28em] text-success">How it Works</p>
              <h2 className="font-display text-4xl leading-tight text-ink sm:text-5xl">
                Built to move from capture to clarity without losing the human story.
              </h2>
              <p className="text-lg leading-8 text-ink-soft">
                Each step keeps the product useful for real people, not just perfect spreadsheets.
              </p>
            </div>

            <div className="grid gap-6 lg:grid-cols-3">
              {workflow.map(({ step, title, description, icon: Icon }) => (
                <article key={step} className="rounded-[30px] border border-white/70 bg-white p-6 shadow-panel">
                  <div className="flex items-center justify-between">
                    <span className="font-display text-4xl text-ink">{step}</span>
                    <div className="rounded-2xl bg-mist p-3 text-success">
                      <Icon className="h-5 w-5" />
                    </div>
                  </div>
                  <h3 className="mt-8 text-2xl font-semibold text-ink">{title}</h3>
                  <p className="mt-4 text-base leading-7 text-ink-soft">{description}</p>
                </article>
              ))}
            </div>
          </div>
        </div>
      </section>

      <section id="pricing" className="relative z-10 mx-auto max-w-7xl px-6 py-20 lg:px-8">
        <div className="flex flex-col gap-5 text-center">
          <p className="text-sm font-semibold uppercase tracking-[0.28em] text-success">Pricing</p>
          <h2 className="font-display text-4xl leading-tight text-ink sm:text-5xl">
            Start simple, then scale shared money operations without starting over.
          </h2>
          <p className="mx-auto max-w-3xl text-lg leading-8 text-ink-soft">
            Pricing is rupee-first, transparent, and built for the way households, friend groups, and project teams actually adopt software.
          </p>
        </div>

        <div className="mt-12 grid gap-6 xl:grid-cols-3">
          {plans.map((plan) => (
            <article
              key={plan.name}
              className={`rounded-[34px] border p-7 shadow-panel ${
                plan.highlight
                  ? 'border-success bg-[linear-gradient(180deg,#17344f_0%,#1f4b55_100%)] text-white'
                  : 'border-line bg-white text-ink'
              }`}
            >
              <div className="flex items-center justify-between">
                <div>
                  <p className={`text-sm font-semibold uppercase tracking-[0.24em] ${plan.highlight ? 'text-green-100' : 'text-success'}`}>
                    {plan.highlight ? 'Most popular' : 'Plan'}
                  </p>
                  <h3 className="mt-3 font-display text-3xl">{plan.name}</h3>
                </div>
                {plan.highlight && (
                  <span className="rounded-full bg-white/10 px-3 py-1 text-sm font-semibold text-green-100">
                    Growth teams love this
                  </span>
                )}
              </div>
              <div className="mt-8 flex items-end gap-3">
                <p className="text-4xl font-bold">{formatRupees(plan.price)}</p>
                <p className={`pb-1 text-sm ${plan.highlight ? 'text-slate-200' : 'text-ink-soft'}`}>per month</p>
              </div>
              <p className={`mt-5 text-base leading-7 ${plan.highlight ? 'text-slate-200' : 'text-ink-soft'}`}>
                {plan.summary}
              </p>
              <div className="mt-8 space-y-3">
                {plan.features.map((feature) => (
                  <div key={feature} className={`flex items-center gap-3 rounded-2xl px-4 py-3 ${plan.highlight ? 'bg-white/6' : 'bg-cloud'}`}>
                    <Check className={`h-4 w-4 ${plan.highlight ? 'text-green-100' : 'text-success'}`} />
                    <span className="text-sm font-medium">{feature}</span>
                  </div>
                ))}
              </div>
              <Link
                href="/auth"
                className={`mt-8 inline-flex items-center gap-2 rounded-full px-5 py-3 text-sm font-semibold transition ${
                  plan.highlight
                    ? 'bg-white text-ink hover:bg-mist'
                    : 'bg-ink text-white hover:bg-ink-soft'
                }`}
              >
                Choose {plan.name}
                <ArrowRight className="h-4 w-4" />
              </Link>
            </article>
          ))}
        </div>
      </section>

      <section id="blog" className="relative z-10 bg-[#eff4f8] py-20">
        <div className="mx-auto max-w-7xl px-6 lg:px-8">
          <div className="flex flex-col gap-5 lg:max-w-3xl">
            <p className="text-sm font-semibold uppercase tracking-[0.28em] text-success">Blog</p>
            <h2 className="font-display text-4xl leading-tight text-ink sm:text-5xl">
              Practical thinking for modern shared finance.
            </h2>
            <p className="text-lg leading-8 text-ink-soft">
              Product notes, budgeting frameworks, and trust-building habits for teams and households who want money conversations to feel lighter.
            </p>
          </div>

          <div className="mt-12 grid gap-6 lg:grid-cols-3">
            {journalPosts.map(({ category, title, summary, icon: Icon }) => (
              <article key={title} className="rounded-[30px] border border-white/70 bg-white p-6 shadow-panel">
                <div className="flex items-center justify-between">
                  <span className="text-xs font-semibold uppercase tracking-[0.24em] text-success">{category}</span>
                  <div className="rounded-2xl bg-mist p-3 text-ink-soft">
                    <Icon className="h-5 w-5" />
                  </div>
                </div>
                <h3 className="mt-8 text-2xl font-semibold leading-tight text-ink">{title}</h3>
                <p className="mt-4 text-base leading-7 text-ink-soft">{summary}</p>
                <a href="#" className="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-success transition hover:text-[#166f4b]">
                  Read article
                  <ArrowRight className="h-4 w-4" />
                </a>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="relative z-10 mx-auto max-w-7xl px-6 py-20 lg:px-8">
        <div className="rounded-[40px] bg-[linear-gradient(135deg,#0f2742_0%,#18344f_56%,#1f4d55_100%)] px-8 py-12 text-white shadow-[0_28px_80px_rgba(8,22,34,0.28)] lg:px-12">
          <div className="grid gap-8 lg:grid-cols-[1.1fr,0.9fr] lg:items-center">
            <div>
              <p className="text-sm font-semibold uppercase tracking-[0.28em] text-green-100">Ready to move</p>
              <h2 className="mt-4 font-display text-4xl leading-tight sm:text-5xl">
                Give every rupee a clearer path from spend to settlement.
              </h2>
              <p className="mt-5 max-w-2xl text-lg leading-8 text-slate-200">
                Start with a household, a trip, or a project team, then grow into OCR, analytics, and fewer settlement transfers without rebuilding your workflow.
              </p>
            </div>
            <div className="flex flex-wrap gap-4 lg:justify-end">
              <Link
                href="/auth"
                className="inline-flex items-center gap-2 rounded-full bg-white px-6 py-3.5 text-base font-semibold text-ink transition hover:bg-mist"
              >
                Create Workspace
                <ArrowRight className="h-4 w-4" />
              </Link>
              <a
                href="#pricing"
                className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-6 py-3.5 text-base font-semibold text-white transition hover:bg-white/10"
              >
                View Pricing
              </a>
            </div>
          </div>
        </div>
      </section>

      <footer className="relative z-10 border-t border-line/80 bg-white/80 backdrop-blur">
        <div className="mx-auto flex max-w-7xl flex-col gap-5 px-6 py-8 text-sm text-ink-soft lg:flex-row lg:items-center lg:justify-between lg:px-8">
          <div>
            <p className="font-display text-2xl text-ink">Expensio</p>
            <p className="mt-1">Shared finance software for households, trips, and project teams.</p>
          </div>
          <div className="flex flex-wrap gap-5">
            <a href="#features" className="transition hover:text-ink">
              Features
            </a>
            <a href="#how-it-works" className="transition hover:text-ink">
              How it Works
            </a>
            <a href="#pricing" className="transition hover:text-ink">
              Pricing
            </a>
            <a href="#blog" className="transition hover:text-ink">
              Blog
            </a>
          </div>
        </div>
      </footer>
    </main>
  );
}
