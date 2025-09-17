import React, { useState, useEffect } from 'react';
import api from '../services/api.js';

const UserProfile = ({ user: initialUser, onUpdate }) => {
  const [user, setUser] = useState(initialUser || null);
  const [loading, setLoading] = useState(!initialUser);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (!initialUser) {
      loadUserProfile();
    } else {
      setUser(initialUser);
    }
  }, [initialUser]);

  const loadUserProfile = async () => {
    try {
      setLoading(true);
      const response = await api.get('/auth/me');
      setUser(response);
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
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="max-w-4xl mx-auto p-6">
        <div className="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
          <div className="flex items-center">
            <svg className="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
            </svg>
            Erreur lors du chargement du profil: {error}
          </div>
        </div>
      </div>
    );
  }

  if (!user) {
    return (
      <div className="max-w-4xl mx-auto p-6">
        <div className="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg">
          <div className="flex items-center">
            <svg className="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
            </svg>
            Aucune donnée utilisateur disponible
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-4xl mx-auto space-y-8">
      {/* Header avec avatar et informations principales */}
      <div className="bg-white overflow-hidden shadow rounded-lg">
        <div className="px-4 py-5 sm:p-6">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <div className="h-20 w-20 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                {user.first_name && user.last_name 
                  ? (user.first_name.charAt(0) + user.last_name.charAt(0)).toUpperCase()
                  : '??'}
              </div>
            </div>
            <div className="ml-6 flex-1">
              <div className="flex items-center justify-between">
                <div>
                  <h1 className="text-2xl font-bold text-gray-900">
                    {user.full_name || `${user.first_name} ${user.last_name}` || 'Utilisateur'}
                  </h1>
                  <p className="text-gray-500">{user.email}</p>
                  <div className="mt-2 flex items-center space-x-2">
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${
                      user.role === 'admin' 
                        ? 'bg-purple-100 text-purple-800' 
                        : 'bg-blue-100 text-blue-800'
                    }`}>
                      {user.role === 'admin' ? '👑 Administrateur' : '👤 Utilisateur'}
                    </span>
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${
                      user.is_active 
                        ? 'bg-green-100 text-green-800' 
                        : 'bg-red-100 text-red-800'
                    }`}>
                      {user.is_active ? '✓ Actif' : '✗ Inactif'}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Grid avec sections d'informations */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        {/* Informations de compte */}
        <div className="bg-white overflow-hidden shadow rounded-lg">
          <div className="px-4 py-5 sm:p-6">
            <div className="flex items-center mb-4">
              <div className="flex-shrink-0">
                <svg className="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <h3 className="ml-3 text-lg font-medium text-gray-900">Informations de compte</h3>
            </div>
            
            <dl className="space-y-4">
              <div>
                <dt className="text-sm font-medium text-gray-500">Adresse email</dt>
                <dd className="mt-1 text-sm text-gray-900">{user.email}</dd>
              </div>
              
              <div>
                <dt className="text-sm font-medium text-gray-500">Email vérifié</dt>
                <dd className="mt-1">
                  <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${
                    user.is_email_verified 
                      ? 'bg-green-100 text-green-800' 
                      : 'bg-red-100 text-red-800'
                  }`}>
                    {user.is_email_verified ? '✓ Vérifié' : '✗ Non vérifié'}
                  </span>
                  {user.email_verified_at && (
                    <p className="mt-1 text-xs text-gray-500">
                      Vérifié le {formatDateTime(user.email_verified_at)}
                    </p>
                  )}
                </dd>
              </div>
              
              <div>
                <dt className="text-sm font-medium text-gray-500">Rôle</dt>
                <dd className="mt-1 text-sm text-gray-900">
                  {user.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : 'Utilisateur'}
                  <p className="text-xs text-gray-500 mt-1">
                    {user.role === 'admin' 
                      ? 'Accès complet à l\'administration' 
                      : 'Accès utilisateur standard'}
                  </p>
                </dd>
              </div>
            </dl>
          </div>
        </div>

        {/* Informations de membre */}
        <div className="bg-white overflow-hidden shadow rounded-lg">
          <div className="px-4 py-5 sm:p-6">
            <div className="flex items-center mb-4">
              <div className="flex-shrink-0">
                <svg className="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4m-4 8h2m-2 0a4 4 0 01-4-4V9a1 1 0 011-1h6a1 1 0 011 1v6a4 4 0 01-4 4z" />
                </svg>
              </div>
              <h3 className="ml-3 text-lg font-medium text-gray-900">Informations de membre</h3>
            </div>
            
            <dl className="space-y-4">
              <div>
                <dt className="text-sm font-medium text-gray-500">Membre depuis</dt>
                <dd className="mt-1 text-sm text-gray-900">
                  {formatDate(user.created_at)}
                  <p className="text-xs text-gray-500 mt-1">
                    {user.created_at && new Date(user.created_at).toLocaleTimeString('fr-FR', { 
                      hour: '2-digit', 
                      minute: '2-digit' 
                    })}
                  </p>
                </dd>
              </div>
              
              <div>
                <dt className="text-sm font-medium text-gray-500">Dernière mise à jour</dt>
                <dd className="mt-1 text-sm text-gray-900">{formatDateTime(user.updated_at)}</dd>
              </div>
              
              <div>
                <dt className="text-sm font-medium text-gray-500">Abonnement</dt>
                <dd className="mt-1">
                  <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${
                    user.abonnement 
                      ? 'bg-green-100 text-green-800' 
                      : 'bg-gray-100 text-gray-800'
                  }`}>
                    {user.abonnement ? '✓ Actif' : 'Aucun abonnement'}
                  </span>
                </dd>
              </div>
            </dl>
          </div>
        </div>

        {/* Carte de membre */}
        {user.carte_membre_numero && (
          <div className="lg:col-span-2">
            <div className={`bg-white overflow-hidden shadow rounded-lg border-l-4 ${
              user.carte_membre_valide 
                ? 'border-green-400' 
                : 'border-red-400'
            }`}>
              <div className="px-4 py-5 sm:p-6">
                <div className="flex items-center mb-4">
                  <div className="flex-shrink-0">
                    <svg className={`h-6 w-6 ${user.carte_membre_valide ? 'text-green-600' : 'text-red-600'}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                  </div>
                  <h3 className="ml-3 text-lg font-medium text-gray-900">Carte de membre</h3>
                  <span className={`ml-auto inline-flex px-2 py-1 text-xs font-medium rounded-full ${
                    user.carte_membre_valide 
                      ? 'bg-green-100 text-green-800' 
                      : 'bg-red-100 text-red-800'
                  }`}>
                    {user.carte_membre_valide ? '✓ Valide' : '✗ Expirée'}
                  </span>
                </div>
                
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <dt className="text-sm font-medium text-gray-500">Numéro de carte</dt>
                    <dd className="mt-1 text-lg font-mono font-semibold text-gray-900">{user.carte_membre_numero}</dd>
                  </div>
                  {user.carte_membre_date_validite && (
                    <div>
                      <dt className="text-sm font-medium text-gray-500">Valide jusqu'au</dt>
                      <dd className="mt-1 text-sm text-gray-900">{formatDate(user.carte_membre_date_validite)}</dd>
                    </div>
                  )}
                </div>
              </div>
            </div>
          </div>
        )}
      </div>

      {/* Actions */}
      <div className="bg-white overflow-hidden shadow rounded-lg">
        <div className="px-4 py-5 sm:p-6">
          <h3 className="text-lg font-medium text-gray-900 mb-4">Actions</h3>
          <div className="flex flex-col sm:flex-row gap-4">
            <button 
              type="button" 
              className="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out"
              onClick={() => alert('Fonctionnalité à venir')}
            >
              <svg className="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Modifier le profil
            </button>
            <button 
              type="button" 
              className="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out"
              onClick={() => alert('Fonctionnalité à venir')}
            >
              <svg className="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              Changer le mot de passe
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default UserProfile;