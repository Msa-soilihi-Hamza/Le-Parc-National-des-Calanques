import React, { useState, useEffect } from 'react';

const UserProfile = ({ userData }) => {
  const [user, setUser] = useState(userData || null);
  const [loading, setLoading] = useState(!userData);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (!userData) {
      loadUserProfile();
    }
  }, [userData]);

  const loadUserProfile = async () => {
    try {
      setLoading(true);
      const profileData = await window.api.getUserProfile();
      setUser(profileData.user);
      setError(null);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const formatDate = (dateString) => {
    if (!dateString) return 'Non disponible';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    });
  };

  const formatDateTime = (dateString) => {
    if (!dateString) return 'Non disponible';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center py-8">
        <div className="loading loading-spinner loading-lg text-blue-600"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-md mb-4">
        Erreur lors du chargement du profil: {error}
      </div>
    );
  }

  if (!user) {
    return (
      <div className="bg-yellow-100 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-md mb-4">
        Aucune donnée utilisateur disponible
      </div>
    );
  }

  return (
    <div className="card mb-8">
      {/* En-tête du profil */}
      <div className="text-center mb-8">
        <div className="w-24 h-24 bg-blue-800 rounded-full flex items-center justify-center text-white text-3xl mx-auto mb-4">
          {user.firstName && user.lastName 
            ? (user.firstName.charAt(0) + user.lastName.charAt(0)).toUpperCase()
            : '??'}
        </div>
        <h1 className="text-3xl text-blue-800 font-bold mb-2">
          {user.firstName && user.lastName 
            ? `${user.firstName} ${user.lastName}` 
            : 'Utilisateur'}
        </h1>
        <div className={`inline-block px-4 py-1 rounded-full text-sm font-medium ${
          user.role === 'admin' 
            ? 'bg-orange-100 text-orange-800' 
            : 'bg-blue-100 text-blue-800'
        }`}>
          {user.role === 'admin' ? '👑 Administrateur' : '👤 Utilisateur'}
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
        {/* Informations de compte */}
        <div className="bg-gray-50 p-6 rounded-lg">
          <h3 className="text-xl text-blue-800 font-semibold mb-4">📧 Informations de compte</h3>
          
          <div className="mb-4">
            <div className="form-label">Adresse email</div>
            <div className="text-gray-900">{user.email || 'Non disponible'}</div>
          </div>

          <div className="mb-4">
            <div className="form-label">Statut du compte</div>
            <div className="text-gray-900">
              <span className={`inline-block px-3 py-1 rounded-md text-xs font-medium ${
                user.isActive 
                  ? 'bg-green-100 text-green-800' 
                  : 'bg-red-100 text-red-800'
              }`}>
                {user.isActive ? '✓ Actif' : '✗ Inactif'}
              </span>
            </div>
          </div>

          <div className="mb-4">
            <div className="form-label">Email vérifié</div>
            <div className="text-gray-900">
              <span className={`inline-block px-3 py-1 rounded-md text-xs font-medium ${
                user.emailVerified 
                  ? 'bg-blue-100 text-blue-800' 
                  : 'bg-red-100 text-red-800'
              }`}>
                {user.emailVerified ? '✓ Vérifié' : '✗ Non vérifié'}
              </span>
              {user.emailVerifiedAt && (
                <><br /><small className="text-gray-500">
                  Le {formatDateTime(user.emailVerifiedAt)}
                </small></>
              )}
            </div>
          </div>

          <div className="mb-4">
            <div className="form-label">Rôle utilisateur</div>
            <div className="text-gray-900">
              <strong>{user.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : 'Utilisateur'}</strong>
              <br />
              <small className="text-gray-500">
                {user.role === 'admin' 
                  ? 'Accès complet à l\'administration' 
                  : 'Accès utilisateur standard'}
              </small>
            </div>
          </div>
        </div>

        {/* Informations de membre */}
        <div className="bg-gray-50 p-6 rounded-lg">
          <h3 className="text-xl text-blue-800 font-semibold mb-4">📅 Informations de membre</h3>
          
          <div className="mb-4">
            <div className="form-label">Membre depuis</div>
            <div className="text-gray-900">
              {formatDate(user.createdAt)}
              {user.createdAt && (
                <><br /><small className="text-gray-500">
                  {new Date(user.createdAt).toLocaleTimeString('fr-FR', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                  })}
                </small></>
              )}
            </div>
          </div>

          <div className="mb-4">
            <div className="form-label">Dernière mise à jour</div>
            <div className="text-gray-900">
              {formatDateTime(user.updatedAt)}
            </div>
          </div>

          <div className="mb-4">
            <div className="form-label">Abonnement</div>
            <div className="text-gray-900">
              <span className={`inline-block px-3 py-1 rounded-md text-xs font-medium ${
                user.hasAbonnement 
                  ? 'bg-green-100 text-green-800' 
                  : 'bg-gray-100 text-gray-800'
              }`}>
                {user.hasAbonnement ? '✓ Actif' : 'Aucun abonnement'}
              </span>
            </div>
          </div>

          {/* Carte de membre */}
          {user.carteMembreNumero && (
            <div className={`border rounded-lg p-4 mt-4 ${
              user.carteMembreValide 
                ? 'border-green-400 bg-green-50' 
                : 'border-gray-300'
            }`}>
              <div className="mb-4">
                <div className="form-label">Carte de membre</div>
                <div className="text-gray-900">
                  <strong>{user.carteMembreNumero}</strong>
                  {user.carteMembreDateValidite && (
                    <><br /><small className="text-gray-500">
                      Valide jusqu'au {formatDate(user.carteMembreDateValidite)}
                      <span className={`inline-block px-2 py-1 rounded text-xs font-medium ml-2 ${
                        user.carteMembreValide 
                          ? 'bg-blue-100 text-blue-800' 
                          : 'bg-red-100 text-red-800'
                      }`}>
                        {user.carteMembreValide ? '✓ Valide' : '✗ Expirée'}
                      </span>
                    </small></>
                  )}
                </div>
              </div>
            </div>
          )}
        </div>
      </div>

      {/* Actions */}
      <div className="text-center mt-8">
        <button 
          type="button" 
          className="btn-primary mr-4" 
          onClick={() => alert('Fonctionnalité à venir')}
        >
          Modifier le profil
        </button>
        <button 
          type="button" 
          className="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-md transition duration-200" 
          onClick={() => alert('Fonctionnalité à venir')}
        >
          Changer le mot de passe
        </button>
      </div>
    </div>
  );
};

export default UserProfile;