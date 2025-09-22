import React, { useState, useEffect } from 'react';
import LoginForm from './components/auth/LoginForm.jsx';
import SignupPage from './components/auth/SignupPage.jsx';
import UserProfile from './components/auth/UserProfile.jsx';
import EmailVerification from './components/auth/EmailVerification.jsx';
import Header from './components/layout/Header.jsx';
import api from './services/api.js';

const App = () => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [showSignup, setShowSignup] = useState(false);

  // Détecter si nous sommes sur la page de vérification d'email
  const isEmailVerificationPage = () => {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.has('token') && urlParams.has('verify');
  };

  // Debug du state user
  console.log('🔄 App render - user:', user ? 'connecté' : 'non connecté', 'loading:', loading);

  useEffect(() => {
    checkAuth();
  }, []);

  const checkAuth = async () => {
    // Debug du token
    const storedToken = localStorage.getItem('auth_token');
    console.log('🔍 Token dans localStorage:', storedToken ? 'présent' : 'absent');

    try {
      const response = await api.get('/auth/me');
      console.log('✅ Auth réussie:', response);

      // Extraire l'objet user de la réponse
      const userData = response.user || response;
      console.log('👤 Données utilisateur extraites:', userData);

      setUser(userData);
    } catch (error) {
      console.log('❌ Non authentifié:', error.response?.status, error.response?.data);
      setUser(null);
    } finally {
      setLoading(false);
    }
  };

  const handleLogin = (userData) => {
    // Extraire l'objet user si la réponse est wrappée
    const user = userData.user || userData;
    setUser(user);
    setShowSignup(false);
  };

  const handleSignup = (userData) => {
    // Si l'inscription réussit mais les tokens sont null (email non vérifié)
    // ne pas connecter l'utilisateur, juste afficher un message
    if (userData && !userData.tokens) {
      // Afficher un message ou rester sur la page d'inscription avec un message de succès
      alert('🎉 Inscription réussie ! Vérifiez votre email pour activer votre compte.');
      setShowSignup(false); // Retourner à la page de login
      return;
    }

    // Si les tokens sont présents, connecter normalement l'utilisateur
    // Extraire l'objet user si la réponse est wrappée
    const user = userData.user || userData;
    setUser(user);
    setShowSignup(false);
  };

  const handleLogout = async () => {
    try {
      await api.logout();  // ← Utilise la méthode logout() qui supprime le token
      setUser(null);
    } catch (error) {
      console.error('Erreur lors de la déconnexion:', error);
      // Déconnexion forcée même en cas d'erreur - supprimer le token quand même
      api.setToken(null);
      setUser(null);
    }
  };

  const switchToLogin = () => setShowSignup(false);
  const switchToSignup = () => setShowSignup(true);

  // Si c'est la page de vérification d'email, afficher directement le composant
  if (isEmailVerificationPage()) {
    return <EmailVerification />;
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>
    );
  }

  console.log('🎯 Render App - user:', user);
  console.log('🎯 User existe?', !!user);
  console.log('🎯 Type user:', typeof user);

  return (
    <div className="min-h-screen bg-background">
      <Header user={user} onLogout={handleLogout} />

      {/* Main Content */}
      <main>
        {(user || localStorage.getItem('auth_token')) ? (
          currentPage === 'profile' ? (
            <div className="container mx-auto px-4 py-8">
              <UserProfile user={user} onUpdate={setUser} />
            </div>
          ) : (
            <SentiersContainer />
          )
        ) : showSignup ? (
          <SignupPage 
            onSuccess={handleSignup}
            onSwitchToLogin={switchToLogin}
          />
        ) : (
          <LoginForm 
            onSuccess={handleLogin} 
            onSwitchToSignup={switchToSignup}
          />
        )}
      </main>
    </div>
  );
};

export default App;