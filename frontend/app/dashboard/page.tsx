import { UserDashboard, type DashboardSnapshot } from '@/components/dashboard/UserDashboard';

const snapshot: DashboardSnapshot = {
  userName: 'Aarav',
  currencyCode: 'INR',
  owedToYouMinor: 184250,
  youOweMinor: 63240,
  activeGroups: 6,
  unsettledExpenses: 14,
  recentNetChangePercent: 12.4,
  dailySpending: [
    { day: 'Mon', food: 4200, commute: 1200, shopping: 0, utilities: 900 },
    { day: 'Tue', food: 1800, commute: 1600, shopping: 2400, utilities: 0 },
    { day: 'Wed', food: 3100, commute: 900, shopping: 800, utilities: 0 },
    { day: 'Thu', food: 1200, commute: 600, shopping: 4200, utilities: 1800 },
    { day: 'Fri', food: 4500, commute: 1100, shopping: 1500, utilities: 0 },
    { day: 'Sat', food: 5200, commute: 0, shopping: 2600, utilities: 0 },
    { day: 'Sun', food: 1700, commute: 0, shopping: 600, utilities: 2300 }
  ],
  groupBalances: [
    {
      groupName: 'Goa Trip',
      counterparties: 4,
      owedToYouMinor: 84200,
      youOweMinor: 0,
      currencyCode: 'INR'
    },
    {
      groupName: 'Apartment',
      counterparties: 2,
      owedToYouMinor: 32150,
      youOweMinor: 21450,
      currencyCode: 'INR'
    },
    {
      groupName: 'Design Sprint',
      counterparties: 5,
      owedToYouMinor: 0,
      youOweMinor: 41790,
      currencyCode: 'INR'
    }
  ]
};

export default function DashboardPage() {
  return <UserDashboard snapshot={snapshot} />;
}

