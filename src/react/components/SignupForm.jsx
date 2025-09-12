import React, { useState } from 'react';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import api from "../services/api.js";

const SignupForm = ({ onSuccess, onSwitchToLogin }) => {
  const [formData, setFormData] = useState({
    nom: '',
    prenom: '',
    email: '',
    password: '',
    confirmPassword: ''
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    // Validation des mots de passe avec regex
    const passwordRegex = /^.{12,}$/; // Au moins 12 caractères
    
    if (!passwordRegex.test(formData.password)) {
      setError('Le mot de passe doit contenir au moins 12 caractères');
      setLoading(false);
      return;
    }

    if (formData.password !== formData.confirmPassword) {
      setError('Les mots de passe ne correspondent pas');
      setLoading(false);
      return;
    }
    
    try {
      const response = await api.signup({
        nom: formData.nom,
        prenom: formData.prenom,
        email: formData.email,
        password: formData.password
      });
      
      console.log('Signup success:', response);
      
      if (onSuccess) {
        onSuccess(response.user || response);
      }
    } catch (err) {
      console.error('Erreur d\'inscription:', err);
      setError(err.message || 'Erreur lors de l\'inscription');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form className="flex flex-col gap-6" onSubmit={handleSubmit}>
      <div className="flex flex-col items-center gap-2 text-center">
        <h1 className="text-2xl font-bold">Créer votre compte</h1>
        <p className="text-muted-foreground text-sm text-balance">
          Rejoignez-nous pour découvrir le Parc National des Calanques
        </p>
      </div>

      {/* Alertes d'erreur */}
      {error && (
        <div className="p-4 bg-red-50 border border-red-200 rounded-md text-red-800 text-sm">
          <div className="flex items-center gap-2">
            <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
            </svg>
            {error}
          </div>
        </div>
      )}

      <div className="grid gap-6">
        {/* Nom et Prénom sur la même ligne */}
        <div className="grid grid-cols-2 gap-3">
          <div className="grid gap-3">
            <Label htmlFor="nom">Nom</Label>
            <Input 
              id="nom" 
              name="nom"
              type="text" 
              placeholder="Votre nom"
              className="input-white"
              value={formData.nom}
              onChange={handleChange}
              required 
            />
          </div>
          <div className="grid gap-3">
            <Label htmlFor="prenom">Prénom</Label>
            <Input 
              id="prenom" 
              name="prenom"
              type="text" 
              placeholder="Votre prénom"
              className="input-white"
              value={formData.prenom}
              onChange={handleChange}
              required 
            />
          </div>
        </div>

        {/* Email */}
        <div className="grid gap-3">
          <Label htmlFor="email">Email</Label>
          <Input 
            id="email" 
            name="email"
            type="email" 
            placeholder="m@example.com"
            className="input-white"
            value={formData.email}
            onChange={handleChange}
            required 
          />
        </div>

        {/* Mot de passe */}
        <div className="grid gap-3">
          <Label htmlFor="password">Mot de passe</Label>
          <Input 
            id="password" 
            name="password"
            type="password"
            placeholder="Minimum 12 caractères"
            className="input-white"
            value={formData.password}
            onChange={handleChange}
            required 
          />
        </div>

        {/* Confirmation mot de passe */}
        <div className="grid gap-3">
          <Label htmlFor="confirmPassword">Confirmer le mot de passe</Label>
          <Input 
            id="confirmPassword" 
            name="confirmPassword"
            type="password"
            placeholder="Confirmez votre mot de passe"
            className="input-white"
            value={formData.confirmPassword}
            onChange={handleChange}
            required 
          />
        </div>

        {/* Bouton inscription */}
        <Button type="submit" className="w-full btn-login" disabled={loading}>
          {loading ? 'Inscription en cours...' : 'Créer mon compte'}
        </Button>

        {/* Séparateur */}
        <div className="after:border-border relative text-center text-sm after:absolute after:inset-0 after:top-1/2 after:z-0 after:flex after:items-center after:border-t">
          <span className="bg-background text-muted-foreground relative z-10 px-2">
            Ou continuer avec
          </span>
        </div>

        {/* Bouton GitHub */}
        <Button variant="outline" className="w-full">
          <svg className="w-4 h-4" viewBox="0 0 24 24">
            <path
              d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"
              fill="currentColor"
            />
          </svg>
          S'inscrire avec GitHub
        </Button>
      </div>

      {/* Lien connexion */}
      <div className="text-center text-sm">
        Vous avez déjà un compte ?{" "}
        <button 
          type="button"
          onClick={onSwitchToLogin}
          className="underline underline-offset-4 hover:text-primary font-medium"
        >
          Se connecter
        </button>
      </div>
    </form>
  );
};

export default SignupForm;