import React from 'react';
import SignupForm from './SignupForm.jsx';

// Composant pour l'icône GalleryVerticalEnd (remplace Lucide)
const GalleryVerticalEndIcon = ({ className = "size-4" }) => (
  <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
    <path d="M7 2h10l-2 14H9l-2-14Z" />
    <path d="M19 22H5" />
    <path d="M22 22H2" />
  </svg>
);

const SignupPage = ({ onSuccess, onSwitchToLogin }) => {
  return (
    <div className="grid min-h-svh lg:grid-cols-2">
      <div className="flex flex-col gap-4 p-6 md:p-10">
        <div className="flex justify-center gap-2 md:justify-start">
          <a href="#" className="flex items-center gap-2 font-medium">
            <div className="bg-primary text-primary-foreground flex size-6 items-center justify-center rounded-md">
              <GalleryVerticalEndIcon />
            </div>
            Parc National des Calanques
          </a>
        </div>
        <div className="flex flex-1 items-center justify-center">
          <div className="w-full max-w-xs">
            <SignupForm 
              onSuccess={onSuccess}
              onSwitchToLogin={onSwitchToLogin}
            />
          </div>
        </div>
      </div>
      <div className="bg-muted relative hidden lg:block">
        {/* Image de fond - vous pouvez remplacer par une vraie image des Calanques */}
        <div className="absolute inset-0 bg-gradient-to-br from-blue-400 via-blue-500 to-blue-600 opacity-90"></div>
        <div className="absolute inset-0 flex items-center justify-center text-white">
          <div className="text-center p-8">
            <h2 className="text-4xl font-bold mb-4">🏔️ Bienvenue</h2>
            <p className="text-xl text-blue-100">
              Découvrez les merveilles du Parc National des Calanques
            </p>
            <p className="mt-4 text-blue-200">
              Créez votre compte pour accéder aux réservations, aux sentiers et bien plus encore.
            </p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default SignupPage;