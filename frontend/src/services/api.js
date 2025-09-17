// Service API pour communiquer avec le backend PHP
class ApiService {
  constructor() {
    console.log('🚀 ApiService constructor appelé - Nouvelle instance créée');

    // Détection automatique du chemin de base avec debug
    const pathParts = window.location.pathname.split('/').filter(part => part !== '');

    // Si on est dans un sous-dossier du serveur local
    if (pathParts.length > 0 && !pathParts[0].includes('.')) {
      this.baseUrl = '/' + pathParts[0];
    } else {
      this.baseUrl = '';
    }

    console.log('API Base URL detected:', this.baseUrl);

    // Récupérer le token depuis localStorage avec debug détaillé
    this.token = localStorage.getItem('auth_token');
    console.log('🔑 Token récupéré depuis localStorage:', this.token ? 'Présent' : 'Absent');
    if (this.token) {
      console.log('🔑 Token (premiers 50 chars):', this.token.substring(0, 50));
    }

    // Vérifier tout le localStorage
    console.log('🔑 Nombre d\'items dans localStorage:', localStorage.length);
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i);
      const value = localStorage.getItem(key);
      console.log(`🔑 localStorage[${key}]:`, value ? value.substring(0, 50) + '...' : 'null');
    }
  }

  setToken(token) {
    console.log('🔑 setToken appelé avec:', token ? 'Token présent' : 'Token null');
    this.token = token;
    if (token) {
      localStorage.setItem('auth_token', token);
      console.log('🔑 Token sauvegardé dans localStorage');
    } else {
      localStorage.removeItem('auth_token');
      console.log('🔑 Token supprimé de localStorage');
    }
    // Vérifier que le token a bien été sauvegardé
    const savedToken = localStorage.getItem('auth_token');
    console.log('🔑 Vérification - Token dans localStorage:', savedToken ? 'Présent' : 'Absent');
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

      // Lire la réponse en tant que texte d'abord
      const textResponse = await response.text();

      // Essayer de parser en JSON
      let data;
      try {
        data = JSON.parse(textResponse);
      } catch (jsonError) {
        const contentType = response.headers.get('content-type');
        console.error('Response is not valid JSON:', textResponse.substring(0, 200));
        console.error('Content-Type:', contentType);
        throw new Error(`Le serveur a retourné du ${contentType || 'contenu non-JSON'} au lieu de JSON. Vérifiez l'URL de l'API.`);
      }

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

    console.log('🔑 Réponse login complète:', response);

    // Stocker le token après connexion réussie
    if (response.tokens && response.tokens.access_token) {
      console.log('🔑 Token reçu du serveur:', response.tokens.access_token.substring(0, 20) + '...');
      this.setToken(response.tokens.access_token);
    } else {
      console.log('❌ Aucun token reçu dans la réponse de login');
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

  // Méthodes admin
  async getUsers(page = 1) {
    return this.get(`/admin/users?page=${page}`);
  }

  async toggleUser(id, action) {
    return this.request(`/admin/users/${id}/${action}`, { method: 'PATCH' });
  }

  async deleteUser(id) {
    return this.request(`/admin/users/${id}`, { method: 'DELETE' });
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