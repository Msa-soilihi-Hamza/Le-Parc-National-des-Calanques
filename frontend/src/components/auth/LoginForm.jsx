import React, { useState } from 'react';
import { Button } from "../ui/button.jsx";
import { Input } from "../ui/input.jsx";
import { Label } from "../ui/label.jsx";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "../ui/card.jsx";
import api from "../../services/api.js";

// Composant pour l'icône GalleryVerticalEnd (remplace Lucide)
const GalleryVerticalEndIcon = ({ className = "size-4" }) => (
  <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
    <path d="M7 2h10l-2 14H9l-2-14Z" />
    <path d="M19 22H5" />
    <path d="M22 22H2" />
  </svg>
);

const LoginForm = ({ onSuccess, onSwitchToSignup }) => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [remember, setRemember] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);
    
    try {
      const response = await api.login(email, password, remember);
      console.log('Login success:', response);
      console.log('🔍 response.user:', response.user);
      console.log('🔍 onSuccess fonction:', typeof onSuccess);
      
      if (onSuccess) {
        console.log('🚀 Appel onSuccess avec:', response.user || response);
        onSuccess(response.user || response);
      }
    } catch (err) {
      console.error('Erreur de connexion:', err);
      setError(err.message || 'Erreur de connexion');
    } finally {
      setLoading(false);
    }
  };

  const fillLoginForm = (demoEmail, demoPassword) => {
    setEmail(demoEmail);
    setPassword(demoPassword);
  };

  return (
    <div className="bg-muted flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
      <div className="flex w-full max-w-sm flex-col gap-6">
        {/* Header avec logo */}
        <a href="#" className="flex items-center gap-2 self-center font-medium">
          <div className="bg-primary text-primary-foreground flex size-6 items-center justify-center rounded-md">
            <GalleryVerticalEndIcon />
          </div>
          Parc National des Calanques
        </a>
        
        {/* Carte de connexion */}
        <Card>
          <CardHeader className="text-center">
            <CardTitle className="text-xl">Bienvenue</CardTitle>
            <CardDescription>
              Connectez-vous à votre compte
            </CardDescription>
          </CardHeader>
          
          <CardContent>
            {/* Alertes d'erreur */}
            {error && (
              <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-md text-red-800 text-sm">
                <div className="flex items-center gap-2">
                  <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                  </svg>
                  {error}
                </div>
              </div>
            )}

            <form onSubmit={handleSubmit}>
              <div className="grid gap-6">
                {/* Formulaire */}
                <div className="grid gap-6">
                  <div className="grid gap-3">
                    <Label htmlFor="email">Email</Label>
                    <Input
                      id="email"
                      name="email"
                      type="email"
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      placeholder="m@example.com"
                      className="input-white"
                      autoComplete="email"
                      required
                    />
                  </div>
                  <div className="grid gap-3">
                    <div className="flex items-center">
                      <Label htmlFor="password">Mot de passe</Label>
                      <a
                        href="#"
                        className="ml-auto text-sm underline-offset-4 hover:underline"
                      >
                        Mot de passe oublié ?
                      </a>
                    </div>
                    <Input 
                      id="password" 
                      name="password"
                      type="password" 
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      className="input-white"
                      autoComplete="current-password"
                      required 
                    />
                  </div>
                  <Button type="submit" className="w-full bg-[#2a69f5] hover:bg-[#1e5cd4] text-white border-none" disabled={loading}>
                    {loading ? 'Connexion en cours...' : 'Connexion'}
                  </Button>
                </div>
                
                {/* Lien inscription */}
                <div className="text-center text-sm">
                  Vous n&apos;avez pas de compte ?{" "}
                  <button
                    type="button"
                    onClick={onSwitchToSignup}
                    className="underline underline-offset-4 hover:text-primary font-medium"
                  >
                    S&apos;inscrire
                  </button>
                </div>
              </div>
            </form>
          </CardContent>
        </Card>
        
        {/* Conditions d'utilisation */}
        <div className="text-muted-foreground text-center text-xs text-balance">
          En continuant, vous acceptez nos{" "}
          <a href="#" className="underline underline-offset-4 hover:text-primary">
            Conditions d&apos;utilisation
          </a>{" "}
          et notre{" "}
          <a href="#" className="underline underline-offset-4 hover:text-primary">
            Politique de confidentialité
          </a>.
        </div>
        
        {/* Compte de test (pour développement) */}
        {process.env.NODE_ENV === 'development' && (
          <div className="p-4 bg-blue-50 border border-blue-200 rounded-md">
            <h4 className="text-sm font-medium text-blue-900 mb-2 text-center">
              🧪 Compte de test
            </h4>
            <Button 
              type="button" 
              variant="outline"
              size="sm"
              className="w-full border-blue-300 text-blue-700 hover:bg-blue-100"
              onClick={() => fillLoginForm('hamza@hamza.fr', 'Hamza 123')}
            >
              Utiliser le compte test
            </Button>
          </div>
        )}
      </div>
    </div>
  );
};

export default LoginForm;