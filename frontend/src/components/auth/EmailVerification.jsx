import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from "../ui/card.jsx";
import { Button } from "../ui/button.jsx";
import api from "../../services/api.js";

// Icône de succès
const CheckCircleIcon = ({ className = "size-16" }) => (
  <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
    <path d="M9 12l2 2 4-4" />
    <circle cx="12" cy="12" r="10" />
  </svg>
);

// Icône d'erreur
const XCircleIcon = ({ className = "size-16" }) => (
  <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
    <circle cx="12" cy="12" r="10" />
    <path d="M15 9l-6 6" />
    <path d="M9 9l6 6" />
  </svg>
);

// Icône de chargement
const LoaderIcon = ({ className = "size-8" }) => (
  <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
    <path d="M21 12a9 9 0 11-6.219-8.56" />
  </svg>
);

const EmailVerification = () => {
  const [status, setStatus] = useState('loading'); // 'loading', 'success', 'error'
  const [message, setMessage] = useState('');
  const [userInfo, setUserInfo] = useState(null);

  useEffect(() => {
    const verifyToken = async () => {
      // Récupérer le token depuis l'URL
      const urlParams = new URLSearchParams(window.location.search);
      const token = urlParams.get('token');

      if (!token) {
        setStatus('error');
        setMessage('Token de vérification manquant.');
        return;
      }

      try {
        console.log('🔍 Vérification du token:', token.substring(0, 20) + '...');

        const response = await api.verifyEmail(token);

        console.log('✅ Réponse de vérification:', response);

        if (response.success) {
          setStatus('success');
          setMessage(response.message);
          setUserInfo(response.user);

          // Redirection automatique après 5 secondes
          setTimeout(() => {
            window.location.href = '/';
          }, 5000);
        } else {
          setStatus('error');
          setMessage(response.message || 'Erreur de vérification');
        }

      } catch (error) {
        console.error('❌ Erreur lors de la vérification:', error);

        setStatus('error');

        if (error.response?.data?.message) {
          setMessage(error.response.data.message);
        } else {
          setMessage('Une erreur est survenue lors de la vérification. Veuillez réessayer plus tard.');
        }
      }
    };

    verifyToken();
  }, []);

  const handleReturnHome = () => {
    window.location.href = '/';
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-400 via-blue-500 to-blue-600 flex items-center justify-center p-4">
      <div className="w-full max-w-md">
        <Card className="shadow-2xl">
          <CardHeader className="text-center pb-6">
            <div className="mx-auto mb-4">
              {status === 'loading' && (
                <div className="animate-spin text-blue-500">
                  <LoaderIcon className="size-16" />
                </div>
              )}

              {status === 'success' && (
                <div className="text-green-500 animate-pulse">
                  <CheckCircleIcon className="size-16" />
                </div>
              )}

              {status === 'error' && (
                <div className="text-red-500">
                  <XCircleIcon className="size-16" />
                </div>
              )}
            </div>

            <CardTitle className="text-2xl font-bold">
              {status === 'loading' && 'Vérification en cours...'}
              {status === 'success' && '🎉 Email vérifié !'}
              {status === 'error' && '❌ Erreur de vérification'}
            </CardTitle>
          </CardHeader>

          <CardContent className="text-center space-y-6">
            {status === 'loading' && (
              <p className="text-gray-600">
                Vérification de votre email en cours...
              </p>
            )}

            {status === 'success' && (
              <>
                <div className="space-y-4">
                  <p className="text-gray-600">
                    {message}
                  </p>

                  {userInfo && (
                    <div className="bg-green-50 border border-green-200 rounded-lg p-4">
                      <p className="text-green-800">
                        <strong>Bienvenue, {userInfo.first_name} !</strong><br />
                        Votre compte est maintenant actif.
                      </p>
                    </div>
                  )}

                  <div className="text-sm text-gray-500">
                    Vous allez être redirigé vers la page d'accueil dans 5 secondes...
                  </div>
                </div>

                <Button
                  onClick={handleReturnHome}
                  className="w-full bg-blue-600 hover:bg-blue-700"
                >
                  🏔️ Accéder à votre compte
                </Button>
              </>
            )}

            {status === 'error' && (
              <>
                <div className="bg-red-50 border border-red-200 rounded-lg p-4">
                  <p className="text-red-800">
                    {message}
                  </p>
                </div>

                <div className="space-y-2 text-sm text-gray-500">
                  <p>Le lien de vérification peut avoir expiré (24h).</p>
                  <p>Contactez-nous si le problème persiste.</p>
                </div>

                <Button
                  onClick={handleReturnHome}
                  variant="outline"
                  className="w-full"
                >
                  🏠 Retour à l'accueil
                </Button>
              </>
            )}
          </CardContent>
        </Card>

        {/* Footer */}
        <div className="text-center mt-8 text-white/80">
          <p className="text-sm">
            🌊 Parc National des Calanques 🌊
          </p>
          <p className="text-xs mt-2 opacity-75">
            Un patrimoine naturel exceptionnel à préserver
          </p>
        </div>
      </div>
    </div>
  );
};

export default EmailVerification;