import React from 'react';

const Header = ({ user, onLogout }) => {
  return (
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