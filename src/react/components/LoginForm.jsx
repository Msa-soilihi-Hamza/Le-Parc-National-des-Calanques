import React, { useState } from 'react';
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import api from "../services/api.js";

const LoginForm = ({ onSuccess }) => {
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
    <div className="flex items-center justify-center px-4 py-12" style={{ minHeight: 'calc(100vh - 4rem)' }}>
      <div className="max-w-md w-full">
        {/* Formulaire de connexion */}
        <div className="card bg-white shadow-xl border-0 w-full">
          <div className="card-body p-8">
            {/* Header */}
            <div className="text-center mb-8">
              <h1 className="text-3xl font-bold text-gray-800 mb-2">Connexion</h1>
              <p className="text-gray-600">Accédez à votre compte</p>
            </div>

            {/* Alertes */}
            {error && (
              <div className="alert alert-error mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" className="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{error}</span>
              </div>
            )}


            {/* Formulaire */}
            <form onSubmit={handleSubmit} className="space-y-6">
              <div className="form-control">
                <label className="label" htmlFor="email">
                  <span className="label-text font-medium">Adresse email</span>
                </label>
                <input 
                  type="email" 
                  id="email" 
                  name="email" 
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  required 
                  autoComplete="email"
                  placeholder="votre@email.com"
                  className="w-full border border-gray-300 rounded-lg px-4 py-6 text-blue-900 h-16 text-xl bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 placeholder-blue-300"
                />
              </div>

              <div className="form-control">
                <label className="label" htmlFor="password">
                  <span className="label-text font-medium">Mot de passe</span>
                </label>
                <input 
                  type="password" 
                  id="password" 
                  name="password" 
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  required 
                  autoComplete="current-password"
                  placeholder="Votre mot de passe"
                  className="w-full border border-gray-300 rounded-lg px-4 py-6 text-blue-900 h-16 text-xl bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 placeholder-blue-300"
                />
              </div>

              <div className="form-control">
                <label className="label cursor-pointer justify-start gap-3">
                  <input 
                    type="checkbox" 
                    name="remember" 
                    value="1"
                    checked={remember}
                    onChange={(e) => setRemember(e.target.checked)}
                    className="checkbox checkbox-primary checkbox-sm"
                  />
                  <span className="label-text">Se souvenir de moi</span>
                </label>
              </div>

              <Button 
                type="submit" 
                className="w-full h-12 text-lg"
                disabled={loading}
              >
                {loading ? 'Connexion...' : 'Se connecter'}
              </Button>
            </form>

            {/* Divider */}
            <div className="divider my-6">ou</div>

            {/* Google Button - Garde le style existant pour l'instant */}
            <div className="text-center mb-6">
              <Button 
                variant="outline" 
                className="w-full h-12 text-lg border-gray-300 hover:bg-gray-50"
                type="button"
              >
                <svg className="w-5 h-5 mr-2" viewBox="0 0 24 24">
                  <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                  <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                  <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                  <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Se connecter avec Google
              </Button>
            </div>

            {/* Comptes de démonstration */}
            <div className="bg-gray-50 p-4 rounded-lg mb-6">
              <h4 className="text-sm font-medium text-gray-700 mb-3 text-center">🔐 Comptes de test</h4>
              <div className="grid grid-cols-2 gap-3">
                <Button 
                  type="button" 
                  variant="outline"
                  size="sm"
                  className="border-green-300 text-green-700 hover:bg-green-50"
                  onClick={() => fillLoginForm('hamza@hamza.fr', 'Hamza 123')}
                >
                  Test Hamza
                </Button>
              </div>
            </div>

            {/* Liens */}
            <div className="text-center space-y-2 text-sm">
              <p>
                <a href="#" className="link link-primary">
                  Mot de passe oublié ?
                </a>
              </p>
              <p className="text-gray-600">
                Pas encore de compte ? 
                <a href="#" className="link link-primary font-medium ml-1">
                  S'inscrire
                </a>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default LoginForm;