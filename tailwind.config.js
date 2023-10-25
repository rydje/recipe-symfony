/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
    container: {
      padding: '10rem'
    },
    fontFamily: {
      sans: [
        '"Inter", sans-serif',
      ],
      title: [
        '"Playfair Display", sans-serif'
      ]
    },
    extend: {},
  },
  plugins: [],
}