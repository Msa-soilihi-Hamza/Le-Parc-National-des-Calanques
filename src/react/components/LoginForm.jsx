import React, { useState } from 'react';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import api from "../services/api.js";

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
      
      if (onSuccess) {
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
              Connectez-vous avec Apple, Google ou votre compte
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
                {/* Boutons sociaux */}
                <div className="flex flex-col gap-4">
                  <Button variant="outline" className="w-full">
                    <svg className="w-4 h-4" viewBox="0 0 24 24">
                      <path
                        d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-.039-.013-3.182-1.221-3.22-4.857-.026-3.04 2.48-4.494 2.597-4.559-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09zM15.53 3.83c.843-1.012 1.4-2.427 1.245-3.83-1.207.052-2.662.805-3.532 1.818-.78.896-1.454 2.338-1.273 3.714 1.338.104 2.715-.688 3.559-1.701"
                        fill="currentColor"
                      />
                    </svg>
                    Connexion avec Apple
                  </Button>
                  <Button variant="outline" className="w-full">
                    <svg className="w-4 h-4" viewBox="0 0 24 24">
                      <path
                        d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z"
                        fill="currentColor"
                      />
                    </svg>
                    Connexion avec Google
                  </Button>
                </div>
                
                {/* Séparateur */}
                <div className="after:border-border relative text-center text-sm after:absolute after:inset-0 after:top-1/2 after:z-0 after:flex after:items-center after:border-t">
                  <span className="bg-card text-muted-foreground relative z-10 px-2">
                    Ou continuer avec
                  </span>
                </div>
                
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
                  <Button type="submit" className="w-full btn-login" disabled={loading}>
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