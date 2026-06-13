'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { ArrowRight } from 'lucide-react';

const apiBaseUrl = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:4000';

type AuthGatewayFormProps = {
  initialMode: string;
  initialEmail: string;
};

export function AuthGatewayForm({ initialMode, initialEmail }: AuthGatewayFormProps) {
  const router = useRouter();
  const [mode, setMode] = useState(initialMode);
  const [email, setEmail] = useState(initialEmail);
  const [fullName, setFullName] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function handleEntrySubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setIsSubmitting(true);
    setError(null);

    const response = await fetch(`${apiBaseUrl}/api/auth/entry`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ email })
    });

    const data = await response.json();
    setIsSubmitting(false);

    if (!response.ok) {
      setError(data.error || 'Unable to continue with that email.');
      return;
    }

    const nextMode = data.nextStep === 'signup' ? 'signup' : 'login';
    setMode(nextMode);
    router.replace(`/auth?mode=${nextMode}&email=${encodeURIComponent(email)}`);
  }

  async function handleLoginSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setIsSubmitting(true);
    setError(null);

    const response = await fetch(`${apiBaseUrl}/api/auth/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ email, password })
    });

    const data = await response.json();
    setIsSubmitting(false);

    if (response.status === 404 && data.nextStep === 'signup') {
      setMode('signup');
      router.replace(`/auth?mode=signup&email=${encodeURIComponent(email)}`);
      return;
    }

    if (!response.ok) {
      setError(data.error || 'Login failed.');
      return;
    }

    window.localStorage.setItem('expense.accessToken', data.accessToken);
    router.push('/dashboard');
  }

  async function handleSignupSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setIsSubmitting(true);
    setError(null);

    const response = await fetch(`${apiBaseUrl}/api/auth/register`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ fullName, email, password })
    });

    const data = await response.json();
    setIsSubmitting(false);

    if (!response.ok) {
      setError(data.error || 'Sign-up failed.');
      return;
    }

    window.localStorage.setItem('expense.accessToken', data.accessToken);
    router.push('/dashboard');
  }

  return (
    <section className="rounded-[32px] border border-white/15 bg-white p-8 shadow-panel">
      <div className="space-y-3 border-b border-line pb-6">
        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-success">
          {mode === 'entry' ? 'Email check' : mode === 'login' ? 'Welcome back' : 'Create account'}
        </p>
        <h2 className="font-display text-3xl text-ink">
          {mode === 'entry'
            ? 'Check your route'
            : mode === 'login'
              ? 'Finish sign in'
              : 'Complete your setup'}
        </h2>
      </div>

      {mode === 'entry' && (
        <form className="mt-6 space-y-5" onSubmit={handleEntrySubmit}>
          <label className="block space-y-2">
            <span className="text-sm font-medium text-ink-soft">Email</span>
            <input
              className="w-full rounded-2xl border border-line px-4 py-3 text-base outline-none transition focus:border-success"
              type="email"
              value={email}
              onChange={(event) => setEmail(event.target.value)}
              placeholder="you@example.com"
              required
            />
          </label>
          {error && <p className="text-sm text-red-600">{error}</p>}
          <button
            type="submit"
            className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-success px-6 py-3 font-semibold text-white transition hover:bg-[#166f4b] disabled:opacity-70"
            disabled={isSubmitting}
          >
            Continue
            <ArrowRight className="h-4 w-4" />
          </button>
        </form>
      )}

      {mode === 'login' && (
        <form className="mt-6 space-y-5" onSubmit={handleLoginSubmit}>
          <label className="block space-y-2">
            <span className="text-sm font-medium text-ink-soft">Email</span>
            <input
              className="w-full rounded-2xl border border-line bg-mist px-4 py-3 text-base outline-none"
              type="email"
              value={email}
              readOnly
            />
          </label>
          <label className="block space-y-2">
            <span className="text-sm font-medium text-ink-soft">Password</span>
            <input
              className="w-full rounded-2xl border border-line px-4 py-3 text-base outline-none transition focus:border-success"
              type="password"
              value={password}
              onChange={(event) => setPassword(event.target.value)}
              placeholder="Enter your password"
              required
            />
          </label>
          {error && <p className="text-sm text-red-600">{error}</p>}
          <button
            type="submit"
            className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-ink px-6 py-3 font-semibold text-white transition hover:bg-ink-soft disabled:opacity-70"
            disabled={isSubmitting}
          >
            Log In
          </button>
        </form>
      )}

      {mode === 'signup' && (
        <form className="mt-6 space-y-5" onSubmit={handleSignupSubmit}>
          <label className="block space-y-2">
            <span className="text-sm font-medium text-ink-soft">Full name</span>
            <input
              className="w-full rounded-2xl border border-line px-4 py-3 text-base outline-none transition focus:border-success"
              type="text"
              value={fullName}
              onChange={(event) => setFullName(event.target.value)}
              placeholder="Your full name"
              required
            />
          </label>
          <label className="block space-y-2">
            <span className="text-sm font-medium text-ink-soft">Email</span>
            <input
              className="w-full rounded-2xl border border-line bg-mist px-4 py-3 text-base outline-none"
              type="email"
              value={email}
              readOnly
            />
          </label>
          <label className="block space-y-2">
            <span className="text-sm font-medium text-ink-soft">Password</span>
            <input
              className="w-full rounded-2xl border border-line px-4 py-3 text-base outline-none transition focus:border-success"
              type="password"
              value={password}
              onChange={(event) => setPassword(event.target.value)}
              placeholder="Create a password"
              required
            />
          </label>
          {error && <p className="text-sm text-red-600">{error}</p>}
          <button
            type="submit"
            className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-success px-6 py-3 font-semibold text-white transition hover:bg-[#166f4b] disabled:opacity-70"
            disabled={isSubmitting}
          >
            Create account
          </button>
        </form>
      )}
    </section>
  );
}
