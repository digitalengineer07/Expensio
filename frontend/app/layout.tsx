import type { Metadata } from 'next';
import { Libre_Baskerville, Manrope } from 'next/font/google';
import './globals.css';

const sans = Manrope({
  subsets: ['latin'],
  variable: '--font-sans'
});

const display = Libre_Baskerville({
  subsets: ['latin'],
  weight: ['400', '700'],
  variable: '--font-display'
});

export const metadata: Metadata = {
  title: 'Expensio',
  description: 'AI-assisted expense management and bill splitting for trusted shared money workflows.'
};

export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  return (
    <html lang="en">
      <body className={`${sans.variable} ${display.variable} font-sans text-ink`}>
        {children}
      </body>
    </html>
  );
}
