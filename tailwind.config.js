/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./node_modules/flowbite/**/*.js"
  ],
  theme: {
    extend: {
      colors: {
        dark: {
            'eval-0': '#121212',
            'eval-1': '#090a11',
            'eval-2': '#2A2F42',
            'eval-3': '#2C3142',
        },
      },
    },
  },
  plugins: [
      require('flowbite/plugin')
  ],
}
