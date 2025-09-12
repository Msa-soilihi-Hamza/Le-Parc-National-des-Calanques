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
  const [fieldValidation, setFieldValidation] = useState({
    nom: false,
    prenom: false,
    email: false,
    password: false,
    confirmPassword: false
  });

  // Fonctions de validation
  const validateField = (fieldName, value) => {
    switch (fieldName) {
      case 'nom':
      case 'prenom':
        return value.trim().length >= 2;
      case 'email':
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(value);
      case 'password':
        const passwordRegex = /^.{12,}$/;
        return passwordRegex.test(value);
      case 'confirmPassword':
        return value === formData.password && value.length >= 12;
      default:
        return false;
    }
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));

    // Validation en temps réel
    const isValid = validateField(name, value);
    setFieldValidation(prev => ({
      ...prev,
      [name]: isValid
    }));

    // Validation spéciale pour confirmPassword quand password change
    if (name === 'password' && formData.confirmPassword) {
      const confirmPasswordValid = validateField('confirmPassword', formData.confirmPassword);
      setFieldValidation(prev => ({
        ...prev,
        confirmPassword: confirmPasswordValid
      }));
    }
  };

  // Composant de barre de progression élégante
  const ProgressBar = ({ isValid, fieldName, value }) => {
    const getProgress = () => {
      if (!value) return 0;
      
      switch (fieldName) {
        case 'nom':
        case 'prenom':
          return value.trim().length >= 2 ? 100 : Math.min((value.trim().length / 2) * 100, 90);
        case 'email':
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (emailRegex.test(value)) return 100;
          if (value.includes('@')) return 60;
          if (value.length > 0) return 30;
          return 0;
        case 'password':
          const progress = Math.min((value.length / 12) * 100, 100);
          return progress;
        case 'confirmPassword':
          if (value === formData.password && value.length >= 12) return 100;
          if (value.length > 0 && formData.password.length > 0) {
            const matchProgress = value === formData.password.substring(0, value.length) ? 70 : 20;
            return Math.min(matchProgress, (value.length / 12) * 100);
          }
          return 0;
        default:
          return 0;
      }
    };

    const progress = getProgress();
    const getBarColor = () => {
      if (progress === 100) return 'bg-green-500';
      return 'bg-red-500';
    };

    if (!value) return null;

    return (
      <div className="mt-1">
        <div className="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
          <div 
            className={`h-full transition-all duration-300 ease-out ${getBarColor()}`}
            style={{ width: `${progress}%` }}
          />
        </div>
        <div className="flex justify-between items-center mt-1">
          <span className={`text-xs ${isValid ? 'text-green-600' : 'text-gray-500'}`}>
            {getValidationMessage(fieldName, value, isValid)}
          </span>
          {isValid && (
            <svg className="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
            </svg>
          )}
        </div>
      </div>
    );
  };

  const getValidationMessage = (fieldName, value, isValid) => {
    if (!value) return '';
    
    switch (fieldName) {
      case 'nom':
      case 'prenom':
        return isValid ? 'Valide' : 'Minimum 2 caractères';
      case 'email':
        return isValid ? 'Email valide' : 'Format email invalide';
      case 'password':
        if (isValid) return 'Mot de passe sécurisé';
        return `${value.length}/12 caractères minimum`;
      case 'confirmPassword':
        if (isValid) return 'Mots de passe identiques';
        if (formData.password && value !== formData.password) return 'Mots de passe différents';
        return 'Confirmez votre mot de passe';
      default:
        return '';
    }
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
          <div className="grid gap-2">
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
            <ProgressBar 
              isValid={fieldValidation.nom} 
              fieldName="nom" 
              value={formData.nom} 
            />
          </div>
          <div className="grid gap-2">
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
            <ProgressBar 
              isValid={fieldValidation.prenom} 
              fieldName="prenom" 
              value={formData.prenom} 
            />
          </div>
        </div>

        {/* Email */}
        <div className="grid gap-2">
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
          <ProgressBar 
            isValid={fieldValidation.email} 
            fieldName="email" 
            value={formData.email} 
          />
        </div>

        {/* Mot de passe */}
        <div className="grid gap-2">
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
          <ProgressBar 
            isValid={fieldValidation.password} 
            fieldName="password" 
            value={formData.password} 
          />
        </div>

        {/* Confirmation mot de passe */}
        <div className="grid gap-2">
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
          <ProgressBar 
            isValid={fieldValidation.confirmPassword} 
            fieldName="confirmPassword" 
            value={formData.confirmPassword} 
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

        {/* Bouton Google */}
        <Button variant="outline" className="w-full">
          <svg className="w-4 h-4" viewBox="0 0 24 24">
            <path
              d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
              fill="#4285F4"
            />
            <path
              d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
              fill="#34A853"
            />
            <path
              d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
              fill="#FBBC05"
            />
            <path
              d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
              fill="#EA4335"
            />
          </svg>
          S'inscrire avec Google
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