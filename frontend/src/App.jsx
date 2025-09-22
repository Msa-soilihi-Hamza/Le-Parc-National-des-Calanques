import React, { useState, useEffect } from 'react';
import LoginForm from './components/auth/LoginForm.jsx';
import SignupPage from './components/auth/SignupPage.jsx';
import UserProfile from './components/auth/UserProfile.jsx';
import SentiersContainer from './components/sentiers/SentiersContainer.jsx';
import api from './services/api.js';

const App = () => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [showSignup, setShowSignup] = useState(false);
  const [currentPage, setCurrentPage] = useState('profile'); // 'profile' ou 'sentiers'

  useEffect(() => {
    checkAuth();
  }, []);

  const checkAuth = async () => {
    try {
      const response = await api.get('/auth/me');
      console.log('🔍 checkAuth response:', response);
      setUser(response.user || response);
    } catch (error) {
      console.log('Non authentifié');
      setUser(null);
    } finally {
      setLoading(false);
    }
  };

  const handleLogin = (userData) => {
    console.log('📊 handleLogin appelé avec:', userData);
    console.log('📊 Type:', typeof userData);
    console.log('📊 Clés:', userData ? Object.keys(userData) : 'null');
    setUser(userData);
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
    setUser(userData);
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

  console.log('🎯 Render App - user:', user);
  console.log('🎯 User existe?', !!user);
  console.log('🎯 Type user:', typeof user);

  return (
    <div className="min-h-screen bg-background">
      {/* Header */}
      <header className="bg-primary text-primary-foreground">
        <div className="container mx-auto px-4 py-4">
          <div className="flex justify-between items-center">
            <h1 className="text-2xl font-bold">
              🏔️ Parc National des Calanques
            </h1>
            {(user || localStorage.getItem('auth_token')) && (
              <div className="flex items-center gap-4">
                <nav className="flex items-center gap-2">
                  <button
                    onClick={() => setCurrentPage('profile')}
                    className={`px-3 py-1 text-sm rounded-md transition-colors ${
                      currentPage === 'profile' 
                        ? 'bg-primary-foreground/20 text-primary-foreground' 
                        : 'text-primary-foreground/80 hover:text-primary-foreground hover:bg-primary-foreground/10'
                    }`}
                  >
                    👤 Profil
                  </button>
                  <button
                    onClick={() => setCurrentPage('sentiers')}
                    className={`px-3 py-1 text-sm rounded-md transition-colors ${
                      currentPage === 'sentiers' 
                        ? 'bg-primary-foreground/20 text-primary-foreground' 
                        : 'text-primary-foreground/80 hover:text-primary-foreground hover:bg-primary-foreground/10'
                    }`}
                  >
                    🥾 Sentiers
                  </button>
                </nav>
                <div className="h-4 w-px bg-primary-foreground/20"></div>
                <span>Bonjour {user?.prenom || user?.first_name || 'Utilisateur'}</span>
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