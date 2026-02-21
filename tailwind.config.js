/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./staff/**/*.php",
    "./staff/assets/js/**/*.js",
    "./*.php"
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'sans-serif']
      }
    }
  },
  plugins: []
};
