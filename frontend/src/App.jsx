import React, { useState, useEffect } from 'react';
import LoginForm from './components/auth/LoginForm.jsx';
import SignupPage from './components/auth/SignupPage.jsx';
import UserProfile from './components/auth/UserProfile.jsx';
import api from './services/api.js';

const App = () => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [showSignup, setShowSignup] = useState(false);

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

  if (loading) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-background">
      {/* Header */}
      <header className="bg-primary text-primary-foreground">
        <div className="container mx-auto px-4 py-4">
          <div className="flex justify-between items-center">
            <h1 className="text-2xl font-bold">
              🏔️ Parc National des Calanques
            </h1>
            {user && (
              <div className="flex items-center gap-4">
                <span>Bonjour {user.prenom}</span>
                <button 
                  onClick={handleLogout}
                  className="px-3 py-1 text-sm bg-transparent border border-primary-foreground/20 text-primary-foreground hover:bg-primary-foreground/10 rounded-md transition-colors"
                >
                  Déconnexion
                </button>
              </div>
            )}
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main>
        {user ? (
          <div className="container mx-auto px-4 py-8">
            <UserProfile user={user} onUpdate={setUser} />
          </div>
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