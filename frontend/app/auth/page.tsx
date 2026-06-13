import { AuthGatewayForm } from '@/components/auth/AuthGatewayForm';

export default async function AuthPage({
  searchParams
}: {
  searchParams?: Promise<{ [key: string]: string | string[] | undefined }>;
}) {
  const resolvedSearchParams = (await searchParams) || {};
  const modeParam = resolvedSearchParams.mode;
  const emailParam = resolvedSearchParams.email;

  const mode = typeof modeParam === 'string' ? modeParam : 'entry';
  const email = typeof emailParam === 'string' ? emailParam : '';

  return (
    <main className="mx-auto flex min-h-screen max-w-7xl items-center px-6 py-16 lg:px-8">
      <div className="grid w-full gap-10 lg:grid-cols-[0.95fr,1.05fr]">
        <div className="space-y-5 text-white">
          <p className="text-sm font-semibold uppercase tracking-[0.25em] text-green-100">Secure access</p>
          <h1 className="font-display text-5xl leading-tight">Start with your email, then we route you to the right path.</h1>
          <p className="max-w-xl text-lg leading-8 text-slate-200">
            Existing members go straight to password sign-in. New emails move directly into sign-up, so onboarding stays fast and intentional.
          </p>
        </div>
        <AuthGatewayForm initialMode={mode} initialEmail={email} />
      </div>
    </main>
  );
}
