// Service API pour communiquer avec le backend PHP
class ApiService {
  constructor() {
    // Détection automatique du chemin de base
    const basePath = window.location.pathname.split('/').slice(0, -1).join('/');
    this.baseUrl = basePath || '';
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
    const url = `${this.baseUrl}/api.php${endpoint}`;
    const config = {
      headers: {
        'Content-Type': 'application/json',
        ...(this.token ? { 'Authorization': `Bearer ${this.token}` } : {}),
        ...options.headers
      },
      ...options
    };

    try {
      const response = await fetch(url, config);
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