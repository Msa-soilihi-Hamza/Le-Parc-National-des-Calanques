import axios from 'axios';

// Service API pour communiquer avec le backend PHP
class ApiService {
  constructor() {
    // Configuration de base pour différents environnements
    const isDev = import.meta.env.DEV;

    // URL de base de l'API
    this.baseURL = isDev
      ? 'http://localhost:8000/api'  // Serveur PHP en développement
      : '/api';  // Production

    // Configuration axios
    this.client = axios.create({
      baseURL: this.baseURL,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      timeout: 10000
    });

    // Intercepteur pour ajouter le token automatiquement
    this.client.interceptors.request.use(
      (config) => {
        const token = localStorage.getItem('auth_token');
        if (token) {
          config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
      },
      (error) => Promise.reject(error)
    );

    // Intercepteur pour gérer les réponses et erreurs
    this.client.interceptors.response.use(
      (response) => response.data,
      (error) => {
        if (error.response?.status === 401) {
          // Token expiré ou invalide - juste nettoyer sans recharger
          this.clearAuthData();
          // NE PAS recharger automatiquement la page !
        }
        return Promise.reject(error);
      }
    );

    console.log('API Base URL:', this.baseURL);
  }

  setToken(token) {
    if (token) {
      localStorage.setItem('auth_token', token);
    } else {
      localStorage.removeItem('auth_token');
    }
  }

  clearAuthData() {
    localStorage.removeItem('auth_token');
  }

  // Méthodes HTTP génériques
  async get(endpoint) {
    return this.client.get(endpoint);
  }

  async post(endpoint, data = {}) {
    return this.client.post(endpoint, data);
  }

  async put(endpoint, data = {}) {
    return this.client.put(endpoint, data);
  }

  async delete(endpoint) {
    return this.client.delete(endpoint);
  }

  // Authentification
  async login(email, password, remember = false) {
    const response = await this.post('/auth/login', {
      email,
      password,
      remember
    });

    // Stocker le token après connexion réussie
    if (response.tokens?.access_token) {
      this.setToken(response.tokens.access_token);
    }

    return response;
  }

  async register(userData) {
    return this.post('/auth/register', userData);
  }

  async signup(userData) {
    const response = await this.post('/auth/register', userData);

    // Stocker le token après inscription réussie si fourni
    if (response.tokens?.access_token) {
      this.setToken(response.tokens.access_token);
    }

    return response;
  }

  async logout() {
    try {
      await this.post('/auth/logout');
    } catch (error) {
      console.warn('Erreur lors de la déconnexion:', error);
    } finally {
      // Supprimer le token même en cas d'erreur
      this.clearAuthData();
    }
  }

  // Profil utilisateur
  async getUserProfile() {
    return this.get('/auth/me');
  }

  async updateUserProfile(profileData) {
    return this.put('/user/profile', profileData);
  }
}

// Instance globale
const api = new ApiService();

// Export pour utilisation dans les composants
window.api = api;

// Fonction globale pour nettoyer l'auth (pour debug)
window.clearAuth = () => {
  api.clearAuthData();
  console.log('Auth data cleared');
};

export default api;