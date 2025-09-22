import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "../ui/card.jsx";
import { Button } from "../ui/button.jsx";
import { Input } from "../ui/input.jsx";
import { Label } from "../ui/label.jsx";
import api from "../../services/api.js";

const SentiersList = ({ onSentierSelect }) => {
  const [sentiers, setSentiers] = useState([]);
  const [filteredSentiers, setFilteredSentiers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [filters, setFilters] = useState({
    search: '',
    difficulty: '',
    zone_id: ''
  });
  const [filterOptions, setFilterOptions] = useState({
    difficulties: [],
    zones: []
  });

  useEffect(() => {
    loadSentiers();
    loadFilterOptions();
  }, []);

  useEffect(() => {
    applyFilters();
  }, [sentiers, filters]);

  const loadSentiers = async () => {
    try {
      setLoading(true);
      const response = await api.get('/api/sentiers');
      
      if (response.success) {
        setSentiers(response.data);
      } else {
        setError('Erreur lors du chargement des sentiers');
      }
    } catch (err) {
      console.error('Erreur:', err);
      setError(err.message || 'Erreur lors du chargement des sentiers');
    } finally {
      setLoading(false);
    }
  };

  const loadFilterOptions = async () => {
    try {
      const response = await api.get('/api/sentiers/filters');
      if (response.success) {
        setFilterOptions(response.data);
      }
    } catch (err) {
      console.error('Erreur lors du chargement des filtres:', err);
    }
  };

  const applyFilters = () => {
    let filtered = [...sentiers];

    // Filtre par recherche
    if (filters.search) {
      filtered = filtered.filter(sentier => 
        sentier.nom.toLowerCase().includes(filters.search.toLowerCase()) ||
        (sentier.description && sentier.description.toLowerCase().includes(filters.search.toLowerCase()))
      );
    }

    // Filtre par difficulté
    if (filters.difficulty) {
      filtered = filtered.filter(sentier => sentier.niveau_difficulte === filters.difficulty);
    }

    // Filtre par zone
    if (filters.zone_id) {
      filtered = filtered.filter(sentier => sentier.id_zone === parseInt(filters.zone_id));
    }

    setFilteredSentiers(filtered);
  };

  const handleFilterChange = (key, value) => {
    setFilters(prev => ({
      ...prev,
      [key]: value
    }));
  };

  const clearFilters = () => {
    setFilters({
      search: '',
      difficulty: '',
      zone_id: ''
    });
  };

  const getDifficultyBadge = (difficulty) => {
    const badges = {
      'facile': { icon: '🟢', class: 'bg-green-100 text-green-800', label: 'Facile' },
      'moyen': { icon: '🟡', class: 'bg-yellow-100 text-yellow-800', label: 'Moyen' },
      'difficile': { icon: '🔴', class: 'bg-red-100 text-red-800', label: 'Difficile' }
    };
    
    const badge = badges[difficulty] || { icon: '⚪', class: 'bg-gray-100 text-gray-800', label: difficulty };
    
    return (
      <span className={`inline-flex items-center px-2 py-1 text-xs font-medium rounded-full ${badge.class}`}>
        <span className="mr-1">{badge.icon}</span>
        {badge.label}
      </span>
    );
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center p-8">
        <div className="text-center">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-2"></div>
          <p className="text-gray-600">Chargement des sentiers...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="bg-red-50 border border-red-200 rounded-md p-4">
        <div className="flex">
          <div className="text-red-400">⚠️</div>
          <div className="ml-3">
            <h3 className="text-sm font-medium text-red-800">Erreur</h3>
            <p className="text-sm text-red-700 mt-1">{error}</p>
            <button 
              onClick={loadSentiers}
              className="mt-2 text-sm text-red-600 hover:text-red-500 underline"
            >
              Réessayer
            </button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="text-center">
        <h1 className="text-3xl font-bold text-gray-900 mb-2">
          🥾 Sentiers du Parc des Calanques
        </h1>
        <p className="text-gray-600 max-w-2xl mx-auto">
          Découvrez les magnifiques sentiers du Parc National des Calanques. 
          Choisissez votre niveau et explorez des paysages à couper le souffle.
        </p>
      </div>

      {/* Filtres */}
      <Card>
        <CardHeader>
          <CardTitle>🔍 Filtrer les sentiers</CardTitle>
          <CardDescription>
            Trouvez le sentier parfait selon vos préférences
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            {/* Recherche */}
            <div>
              <Label htmlFor="search">Recherche</Label>
              <Input
                id="search"
                type="text"
                placeholder="Nom du sentier..."
                value={filters.search}
                onChange={(e) => handleFilterChange('search', e.target.value)}
              />
            </div>

            {/* Difficulté */}
            <div>
              <Label htmlFor="difficulty">Difficulté</Label>
              <select
                id="difficulty"
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                value={filters.difficulty}
                onChange={(e) => handleFilterChange('difficulty', e.target.value)}
              >
                <option value="">Toutes les difficultés</option>
                {filterOptions.difficulties.map(difficulty => (
                  <option key={difficulty} value={difficulty}>
                    {difficulty.charAt(0).toUpperCase() + difficulty.slice(1)}
                  </option>
                ))}
              </select>
            </div>

            {/* Zone */}
            <div>
              <Label htmlFor="zone">Zone</Label>
              <select
                id="zone"
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                value={filters.zone_id}
                onChange={(e) => handleFilterChange('zone_id', e.target.value)}
              >
                <option value="">Toutes les zones</option>
                {filterOptions.zones.map(zone => (
                  <option key={zone.id_zone} value={zone.id_zone}>
                    {zone.nom}
                  </option>
                ))}
              </select>
            </div>

            {/* Bouton reset */}
            <div className="flex items-end">
              <Button 
                variant="outline" 
                onClick={clearFilters}
                className="w-full"
              >
                Réinitialiser
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Résultats */}
      <div>
        <div className="flex justify-between items-center mb-4">
          <h2 className="text-xl font-semibold text-gray-900">
            {filteredSentiers.length} sentier{filteredSentiers.length !== 1 ? 's' : ''} trouvé{filteredSentiers.length !== 1 ? 's' : ''}
          </h2>
        </div>

        {filteredSentiers.length === 0 ? (
          <div className="text-center py-8">
            <div className="text-6xl mb-4">🔍</div>
            <h3 className="text-lg font-medium text-gray-900 mb-2">Aucun sentier trouvé</h3>
            <p className="text-gray-600 mb-4">
              Essayez de modifier vos critères de recherche
            </p>
            <Button onClick={clearFilters} variant="outline">
              Voir tous les sentiers
            </Button>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {filteredSentiers.map(sentier => (
              <Card key={sentier.id_sentier} className="hover:shadow-lg transition-shadow cursor-pointer">
                <CardHeader>
                  <div className="flex justify-between items-start">
                    <CardTitle className="text-lg">{sentier.nom}</CardTitle>
                    {getDifficultyBadge(sentier.niveau_difficulte)}
                  </div>
                  <CardDescription>
                    📍 {sentier.nom_zone || 'Zone non spécifiée'}
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <p className="text-gray-600 text-sm mb-4 line-clamp-3">
                    {sentier.description || 'Aucune description disponible.'}
                  </p>
                  <div className="flex justify-between items-center">
                    <div className="text-xs text-gray-500">
                      Sentier #{sentier.id_sentier}
                    </div>
                    <Button 
                      size="sm"
                      onClick={() => onSentierSelect && onSentierSelect(sentier)}
                    >
                      Voir détails
                    </Button>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}
      </div>
    </div>
  );
};

export default SentiersList;


