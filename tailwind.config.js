/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./templates/**/*.php",
    "./public/**/*.html",
    "./src/**/*.php",
    "./index.php"
  ],
  theme: {
    extend: {
      colors: {
        'parc-blue': '#2563eb',
        'parc-green': '#16a34a',
        'parc-orange': '#ea580c',
      }
    },
  },
  plugins: [require('daisyui')],
  daisyui: {
    themes: [
      {
        parc: {
          "primary": "#2563eb",
          "secondary": "#16a34a", 
          "accent": "#ea580c",
          "neutral": "#374151",
          "base-100": "#ffffff",
          "base-200": "#f3f4f6",
          "base-300": "#e5e7eb",
          "info": "#3b82f6",
          "success": "#10b981",
          "warning": "#f59e0b",
          "error": "#ef4444",
        },
      },
    ],
  },
}