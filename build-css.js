const { readFileSync, writeFileSync } = require('fs');
const path = require('path');

// Import Tailwind CSS pour la version 4.x
async function buildCSS() {
  try {
    // Pour Tailwind 4.x, on utilise l'API programmatique
    const tailwind = await import('tailwindcss');
    const postcss = require('postcss');
    
    const inputCSS = readFileSync('./public/css/input.css', 'utf8');
    
    const result = await postcss([
      tailwind.default('./tailwind.config.js')
    ]).process(inputCSS, {
      from: './public/css/input.css',
      to: './public/css/output.css'
    });
    
    writeFileSync('./public/css/output.css', result.css);
    console.log('✅ CSS built successfully!');
  } catch (error) {
    // Fallback pour une construction basique
    console.log('🔄 Using fallback CSS generation...');
    
    const basicCSS = `
/* Basic Tailwind CSS Reset */
*,::before,::after{box-sizing:border-box;border-width:0;border-style:solid;border-color:#e5e7eb}
::before,::after{--tw-content:''}
html{line-height:1.5;-webkit-text-size-adjust:100%;-moz-tab-size:4;tab-size:4;font-family:ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";font-feature-settings:normal}
body{margin:0;line-height:inherit}

/* DaisyUI theme variables */
:root, [data-theme] {
  --p: 219 39% 26%;
  --s: 142 76% 36%;
  --a: 24 70% 56%;
  --n: 220 13% 69%;
  --b1: 0 0% 100%;
  --b2: 220 13% 95%;
  --b3: 220 13% 90%;
}

/* DaisyUI Components */
.btn{display:inline-flex;align-items:center;justify-content:center;border-radius:0.5rem;height:3rem;padding-left:1rem;padding-right:1rem;min-height:3rem;font-size:0.875rem;line-height:1em;gap:0.5rem;font-weight:600;text-decoration-line:none;border-width:1px;border-color:transparent;background-color:hsl(var(--n));color:hsl(var(--nc));cursor:pointer;user-select:none;transition-property:color,background-color,border-color,opacity,box-shadow,transform;transition-timing-function:cubic-bezier(0.4,0,0.2,1);transition-duration:200ms;border:1px solid transparent;animation:button-pop var(--animation-btn,0.25s) ease-out;text-transform:uppercase;--tw-border-opacity:1;--tw-bg-opacity:1;--tw-text-opacity:1;outline-color:hsl(var(--n))}
.btn-primary{color:hsl(var(--pc));background-color:hsl(var(--p));border-color:hsl(var(--p))}
.btn:hover{background-color:hsl(var(--n)/0.9)}
.btn-primary:hover{background-color:hsl(var(--p)/0.8)}
.card{position:relative;display:flex;flex-direction:column;border-radius:var(--rounded-box,1rem);background-color:hsl(var(--b1));color:hsl(var(--bc));box-shadow:0 1px 3px 0 hsl(var(--bc) / 0.12), 0 1px 2px -1px hsl(var(--bc) / 0.12);padding:2rem}
.alert{display:grid;width:100%;grid-auto-flow:row;align-content:flex-start;align-items:center;justify-items:center;gap:1rem;text-align:center;border-width:1px;border-color:transparent;padding:1rem;border-radius:var(--rounded-box,1rem)}
.alert-success{border-color:hsl(var(--su));background-color:hsl(var(--su)/0.2);color:hsl(var(--suc))}
.alert-error{border-color:hsl(var(--er));background-color:hsl(var(--er)/0.2);color:hsl(var(--erc))}

/* Utility Classes */
.bg-blue-800{background-color:rgb(30 64 175)}
.bg-gray-100{background-color:rgb(243 244 246)}
.bg-gray-50{background-color:rgb(249 250 251)}
.bg-white{background-color:rgb(255 255 255)}
.text-white{color:rgb(255 255 255)}
.text-gray-900{color:rgb(17 24 39)}
.text-gray-700{color:rgb(55 65 81)}
.text-gray-600{color:rgb(75 85 99)}
.text-blue-800{color:rgb(30 64 175)}
.bg-blue-100{color:rgb(219 234 254)}
.text-blue-800{color:rgb(30 64 175)}
.bg-green-100{background-color:rgb(220 252 231)}
.text-green-800{color:rgb(22 101 52)}
.bg-red-100{background-color:rgb(254 226 226)}
.text-red-800{color:rgb(153 27 27)}
.p-4{padding:1rem}
.p-6{padding:1.5rem}
.py-2{padding-top:0.5rem;padding-bottom:0.5rem}
.py-4{padding-top:1rem;padding-bottom:1rem}
.py-8{padding-top:2rem;padding-bottom:2rem}
.px-4{padding-left:1rem;padding-right:1rem}
.px-8{padding-left:2rem;padding-right:2rem}
.rounded{border-radius:0.25rem}
.rounded-md{border-radius:0.375rem}
.rounded-lg{border-radius:0.5rem}
.rounded-full{border-radius:9999px}
.shadow-md{box-shadow:0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)}
.border{border-width:1px}
.border-gray-200{border-color:rgb(229 231 235)}
.border-gray-300{border-color:rgb(209 213 219)}
.border-green-200{border-color:rgb(187 247 208)}
.border-red-200{border-color:rgb(254 202 202)}
.w-24{width:6rem}
.w-full{width:100%}
.h-24{height:6rem}
.max-w-4xl{max-width:56rem}
.max-w-6xl{max-width:72rem}
.mx-auto{margin-left:auto;margin-right:auto}
.mb-2{margin-bottom:0.5rem}
.mb-4{margin-bottom:1rem}
.mb-8{margin-bottom:2rem}
.mt-4{margin-top:1rem}
.mt-8{margin-top:2rem}
.mr-4{margin-right:1rem}
.flex{display:flex}
.grid{display:grid}
.inline-flex{display:inline-flex}
.inline-block{display:inline-block}
.block{display:block}
.items-center{align-items:center}
.justify-center{justify-content:center}
.justify-between{justify-content:space-between}
.gap-8{gap:2rem}
.grid-cols-1{grid-template-columns:repeat(1,minmax(0,1fr))}
.text-center{text-align:center}
.text-sm{font-size:0.875rem;line-height:1.25rem}
.text-lg{font-size:1.125rem;line-height:1.75rem}
.text-xl{font-size:1.25rem;line-height:1.75rem}
.text-2xl{font-size:1.5rem;line-height:2rem}
.text-3xl{font-size:1.875rem;line-height:2.25rem}
.font-bold{font-weight:700}
.font-semibold{font-weight:600}
.font-medium{font-weight:500}
.font-sans{font-family:ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"}
.leading-relaxed{line-height:1.625}
.transition-opacity{transition-property:opacity;transition-timing-function:cubic-bezier(0.4,0,0.2,1);transition-duration:150ms}
.transition{transition-property:color,background-color,border-color,text-decoration-color,fill,stroke,opacity,box-shadow,transform,filter,backdrop-filter;transition-timing-function:cubic-bezier(0.4,0,0.2,1);transition-duration:150ms}
.duration-200{transition-duration:200ms}
.hover\\:opacity-80:hover{opacity:0.8}
.hover\\:bg-gray-600:hover{background-color:rgb(75 85 99)}
.focus\\:outline-none:focus{outline:2px solid transparent;outline-offset:2px}
.bg-opacity-10{--tw-bg-opacity:0.1}
.overflow-x-auto{overflow-x:auto}

/* Custom Components */
.btn-primary{background-color:rgb(37 99 235);color:rgb(255 255 255);font-weight:500;padding:0.5rem 1rem;border-radius:0.375rem;transition:background-color 0.2s}
.btn-primary:hover{background-color:rgb(29 78 216)}
.card{background-color:rgb(255 255 255);box-shadow:0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);border-radius:0.5rem;padding:1.5rem;border:1px solid rgb(229 231 235)}
.form-input{width:100%;padding:0.75rem;border:1px solid rgb(209 213 219);border-radius:0.375rem}
.form-input:focus{outline:2px solid rgb(37 99 235);border-color:transparent}
.form-label{display:block;font-size:0.875rem;font-weight:500;color:rgb(55 65 81);margin-bottom:0.5rem}

/* Responsive Design */
@media (min-width: 768px) {
  .md\\:grid-cols-2{grid-template-columns:repeat(2,minmax(0,1fr))}
}
`;
    
    writeFileSync('./public/css/output.css', basicCSS);
    console.log('✅ Basic CSS generated successfully!');
  }
}

buildCSS().catch(console.error);