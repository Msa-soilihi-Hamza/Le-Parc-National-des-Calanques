import React from 'react';

const Header = ({ user, onLogout, currentPage, onPageChange }) => {
  return (
    <header className="bg-primary text-primary-foreground">
      <div className="container mx-auto px-4 py-4">
        <div className="flex justify-between items-center">
          <h1 className="text-2xl font-bold">
            🏔️ Parc National des Calanques
          </h1>
          {user && (
            <div className="flex items-center gap-4">
              <nav className="flex items-center gap-2">
                <button
                  onClick={() => onPageChange('profile')}
                  className={`px-3 py-1 text-sm rounded-md transition-colors ${
                    currentPage === 'profile' 
                      ? 'bg-primary-foreground/20 text-primary-foreground' 
                      : 'text-primary-foreground/80 hover:text-primary-foreground hover:bg-primary-foreground/10'
                  }`}
                >
                  👤 Profil
                </button>
                <button
                  onClick={() => onPageChange('sentiers')}
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
              <span>Bonjour {user.prenom || user.first_name || 'Utilisateur'}</span>
              <button
                onClick={onLogout}
                className="px-3 py-1 text-sm bg-transparent border border-primary-foreground/20 text-primary-foreground hover:bg-primary-foreground/10 rounded-md transition-colors"
              >
                Déconnexion
              </button>
            </div>
          )}
        </div>
      </div>
    </header>
  );
};

export default Header;