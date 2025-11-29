/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        dark: {
          DEFAULT: '#0a1016',
          700: '#0f1720',
          800: '#0d1419',
          900: '#020b12',
        },
        blue: {
          DEFAULT: '#0268a8',
          dark: '#025a90',
        },
        orange: {
          DEFAULT: '#ed7f20',
          light: '#ffb056',
        },
        light: '#b1dde9',
      },
    },
  },
  plugins: [],
}
