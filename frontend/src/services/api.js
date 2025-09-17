// Service API pour communiquer avec le backend PHP
class ApiService {
  constructor() {
    // Détection automatique du chemin de base avec debug
    const pathParts = window.location.pathname.split('/').filter(part => part !== '');

    // Si on est dans un sous-dossier du serveur local
    if (pathParts.length > 0 && !pathParts[0].includes('.')) {
      this.baseUrl = '/' + pathParts[0];
    } else {
      this.baseUrl = '';
    }

    console.log('API Base URL detected:', this.baseUrl);
    this.token = localStorage.getItem('auth_token');
  }

  setToken(token) {
    this.token = token;
    if (token) {
      localStorage.setItem('auth_token', token);
    } else {
      localStorage.removeItem('auth_token');
    }
  }

  clearAuthData() {
    this.setToken(null);
    localStorage.clear(); // Nettoyer tout le localStorage
  }

  async request(endpoint, options = {}) {
    const url = `${this.baseUrl}/backend/public/index.php${endpoint}`;
    const config = {
      headers: {
        'Content-Type': 'application/json',
        ...(this.token ? { 'Authorization': `Bearer ${this.token}` } : {}),
        ...options.headers
      },
      ...options
    };

    try {
      console.log('Calling API URL:', url);
      const response = await fetch(url, config);

      // Vérifier le type de contenu
      const contentType = response.headers.get('content-type');

      if (!contentType || !contentType.includes('application/json')) {
        const textResponse = await response.text();
        console.error('Response is not JSON:', textResponse.substring(0, 200));
        throw new Error(`Le serveur a retourné du ${contentType || 'contenu non-JSON'} au lieu de JSON. Vérifiez l'URL de l'API.`);
      }

      const data = await response.json();

      if (!response.ok) {
        // Si le token est invalide, le supprimer et permettre à l'utilisateur de se reconnecter
        if (response.status === 401 && (data.message?.includes('token') || data.message?.includes('signature'))) {
          console.log('Token invalide détecté, nettoyage...');
          this.setToken(null);
        }
        throw new Error(data.message || 'Erreur API');
      }

      return data;
    } catch (error) {
      console.error('Erreur API:', error);
      throw error;
    }
  }

  // Authentification
  async login(email, password, remember = false) {
    const response = await this.request('/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password, remember })
    });
    
    // Stocker le token après connexion réussie
    if (response.tokens && response.tokens.access_token) {
      this.setToken(response.tokens.access_token);
    }
    
    return response;
  }

  async register(userData) {
    return this.request('/auth/register', {
      method: 'POST',
      body: JSON.stringify(userData)
    });
  }

  async signup(userData) {
    const response = await this.request('/auth/register', {
      method: 'POST',
      body: JSON.stringify(userData)
    });
    
    // Stocker le token après inscription réussie si fourni
    if (response.tokens && response.tokens.access_token) {
      this.setToken(response.tokens.access_token);
    }
    
    return response;
  }

  async logout() {
    const response = await this.request('/auth/logout', {
      method: 'POST'
    });
    
    // Supprimer le token après déconnexion
    this.setToken(null);
    
    return response;
  }

  // Profil utilisateur  
  async get(endpoint) {
    return this.request(endpoint);
  }

  async post(endpoint, body = {}) {
    return this.request(endpoint, {
      method: 'POST',
      body: JSON.stringify(body)
    });
  }

  async getUserProfile() {
    return this.request('/auth/me');
  }

  async updateUserProfile(profileData) {
    return this.request('/user/profile', {
      method: 'PUT',
      body: JSON.stringify(profileData)
    });
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