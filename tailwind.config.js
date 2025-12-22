/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.php",
    "./**/*.php",
    "./js/*.js"
  ],
  theme: {
    extend: {
      colors: {
        'hazel-red': '#C4161C',
        'hazel-bg': '#FAF7F2',
      },
      fontFamily: {
        'thai': ['Prompt', 'Sarabun', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
      },
      maxWidth: {
        'app': '600px',
      }
    },
  },
  plugins: [],
}