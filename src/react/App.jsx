import React, { useState, useEffect } from 'react';
import LoginForm from './components/LoginForm.jsx';
import UserProfile from './components/UserProfile.jsx';
import api from './services/api.js';

const App = () => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    checkAuth();
  }, []);

  const checkAuth = async () => {
    try {
      const response = await api.get('/auth/me');
      setUser(response);
    } catch (error) {
      console.log('Non authentifié');
      setUser(null);
    } finally {
      setLoading(false);
    }
  };

  const handleLogin = (userData) => {
    setUser(userData);
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

  if (loading) {
    return (
      <div className="min-h-screen bg-base-100 flex items-center justify-center">
        <div className="loading loading-spinner loading-lg text-primary"></div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-base-100">
      {/* Header */}
      <header className="bg-primary text-primary-content">
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
                  className="btn btn-sm btn-ghost"
                >
                  Déconnexion
                </button>
              </div>
            )}
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main className="container mx-auto px-4 py-8">
        {user ? (
          <UserProfile user={user} onUpdate={setUser} />
        ) : (
          <div className="max-w-md mx-auto">
            <div className="card bg-base-200 shadow-xl">
              <div className="card-body">
                <h2 className="card-title justify-center text-2xl mb-6">
                  Connexion
                </h2>
                <LoginForm onSuccess={handleLogin} />
              </div>
            </div>
          </div>
        )}
      </main>
    </div>
  );
};

export default App;