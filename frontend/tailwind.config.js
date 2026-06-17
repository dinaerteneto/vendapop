/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      keyframes: {
        'phone-slide': {
          '0%, 18.75%': { opacity: '1', transform: 'translateY(0)' },
          '25%, 100%': { opacity: '0', transform: 'translateY(-100%)' },
        },
      },
      animation: {
        'phone-slide': 'phone-slide 16s ease-in-out infinite',
      },
    },
  },
  plugins: [],
}

