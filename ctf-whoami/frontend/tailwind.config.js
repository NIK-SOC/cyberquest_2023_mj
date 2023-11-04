/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./public/index.html', './src/**/*.{vue,js}'],
  theme: {
    typography: {
      default: {
        css: {
          'code::before': {
            content: '""',
          },
          'code::after': {
            content: '""',
          },
        },
      },
    },
    extend: {},
  },
  plugins: [
    require(`@tailwindcss/typography`)
  ],
}

