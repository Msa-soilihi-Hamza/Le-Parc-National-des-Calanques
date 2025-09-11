import React from 'react';
import ReactDOM from 'react-dom/client';

// Services pour l'API
import './services/api';

// Import des composants
import UserProfile from './components/UserProfile.jsx';
import LoginForm from './components/LoginForm.jsx';

// Point d'entrée principal de React
console.log('React chargé avec succès');

// Export des composants pour utilisation globale
window.React = React;
window.ReactDOM = ReactDOM;

// Export des composants spécifiques
window.UserProfile = UserProfile;
window.LoginForm = LoginForm;

// Fonction utilitaire pour monter des composants React
window.mountReactComponent = (Component, containerId, props = {}) => {
  const container = document.getElementById(containerId);
  if (container) {
    const root = ReactDOM.createRoot(container);
    root.render(React.createElement(Component, props));
    return root;
  }
  console.error(`Container avec l'ID "${containerId}" non trouvé`);
  return null;
};