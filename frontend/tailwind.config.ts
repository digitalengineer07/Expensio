import type { Config } from 'tailwindcss';

const config: Config = {
  content: [
    './app/**/*.{ts,tsx}',
    './components/**/*.{ts,tsx}'
  ],
  theme: {
    extend: {
      colors: {
        ink: '#0F2742',
        'ink-soft': '#193B5A',
        mist: '#EEF4F6',
        cloud: '#F8FBFC',
        success: '#1C8C5E',
        'success-soft': '#DCEFE7',
        line: '#C8D6DE'
      },
      fontFamily: {
        sans: ['var(--font-sans)'],
        display: ['var(--font-display)']
      },
      boxShadow: {
        panel: '0 24px 60px rgba(15, 39, 66, 0.10)'
      },
      backgroundImage: {
        trust: 'radial-gradient(circle at top left, rgba(28, 140, 94, 0.18), transparent 35%), linear-gradient(180deg, #0F2742 0%, #18324F 60%, #F8FBFC 60%)'
      }
    }
  },
  plugins: []
};

export default config;
