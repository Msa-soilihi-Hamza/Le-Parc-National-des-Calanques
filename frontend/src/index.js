import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App.jsx';

// Point d'entrée principal de React
console.log('React App chargé avec succès');

// Fonction pour monter l'application React principale
window.mountReactApp = () => {
  const container = document.getElementById('root');
  if (container) {
    const root = ReactDOM.createRoot(container);
    root.render(React.createElement(App));
    return root;
  }
  console.error('Container "root" non trouvé');
  return null;
};

// Export des composants pour utilisation globale si nécessaire
window.React = React;
window.ReactDOM = ReactDOM;