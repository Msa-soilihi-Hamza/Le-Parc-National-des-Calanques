import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "../ui/card.jsx";
import { Button } from "../ui/button.jsx";
import api from "../../services/api.js";

const SentierDetail = ({ sentierId, onBack }) => {
  const [sentier, setSentier] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (sentierId) {
      loadSentier();
    }
  }, [sentierId]);

  const loadSentier = async () => {
    try {
      setLoading(true);
      setError(null);
      
      const response = await api.get(`/api/sentiers/${sentierId}`);
      
      if (response.success) {
        setSentier(response.data);
      } else {
        setError('Sentier non trouvé');
      }
    } catch (err) {
      console.error('Erreur:', err);
      setError(err.message || 'Erreur lors du chargement du sentier');
    } finally {
      setLoading(false);
    }
  };

  const getDifficultyInfo = (difficulty) => {
    const difficulties = {
      'facile': {
        icon: '🟢',
        color: 'text-green-600',
        bgColor: 'bg-green-50',
        borderColor: 'border-green-200',
        label: 'Facile',
        description: 'Accessible à tous, terrain plat ou légèrement vallonné'
      },
      'moyen': {
        icon: '🟡',
        color: 'text-yellow-600',
        bgColor: 'bg-yellow-50',
        borderColor: 'border-yellow-200',
        label: 'Moyen',
        description: 'Nécessite une condition physique correcte, quelques passages techniques'
      },
      'difficile': {
        icon: '🔴',
        color: 'text-red-600',
        bgColor: 'bg-red-50',
        borderColor: 'border-red-200',
        label: 'Difficile',
        description: 'Réservé aux randonneurs expérimentés, terrain accidenté'
      }
    };

    return difficulties[difficulty] || {
      icon: '⚪',
      color: 'text-gray-600',
      bgColor: 'bg-gray-50',
      borderColor: 'border-gray-200',
      label: difficulty,
      description: 'Niveau de difficulté non spécifié'
    };
  };

  const formatDate = (dateString) => {
    if (!dateString) return 'Non spécifiée';
    
    try {
      const date = new Date(dateString);
      return date.toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    } catch {
      return 'Date invalide';
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center p-8">
        <div className="text-center">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-2"></div>
          <p className="text-gray-600">Chargement du sentier...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="max-w-2xl mx-auto">
        <div className="bg-red-50 border border-red-200 rounded-md p-6">
          <div className="flex">
            <div className="text-red-400 text-2xl">⚠️</div>
            <div className="ml-3">
              <h3 className="text-lg font-medium text-red-800">Erreur</h3>
              <p className="text-red-700 mt-1">{error}</p>
              <div className="mt-4 flex space-x-2">
                <button 
                  onClick={loadSentier}
                  className="text-sm bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700"
                >
                  Réessayer
                </button>
                <button 
                  onClick={onBack}
                  className="text-sm border border-red-600 text-red-600 px-3 py-1 rounded hover:bg-red-50"
                >
                  Retour à la liste
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }

  if (!sentier) {
    return null;
  }

  const difficultyInfo = getDifficultyInfo(sentier.niveau_difficulte);

  return (
    <div className="max-w-4xl mx-auto space-y-6">
      {/* Bouton retour */}
      <div>
        <Button 
          variant="outline" 
          onClick={onBack}
          className="mb-4"
        >
          ← Retour à la liste
        </Button>
      </div>

      {/* Header du sentier */}
      <Card>
        <CardHeader>
          <div className="flex flex-col md:flex-row md:justify-between md:items-start gap-4">
            <div>
              <CardTitle className="text-2xl md:text-3xl font-bold text-gray-900">
                🥾 {sentier.nom}
              </CardTitle>
              <CardDescription className="text-lg mt-2">
                📍 {sentier.nom_zone || 'Zone non spécifiée'}
              </CardDescription>
            </div>
            
            {/* Badge de difficulté */}
            <div className={`inline-flex items-center px-4 py-2 rounded-lg border ${difficultyInfo.bgColor} ${difficultyInfo.borderColor}`}>
              <span className="text-2xl mr-2">{difficultyInfo.icon}</span>
              <div>
                <div className={`font-semibold ${difficultyInfo.color}`}>
                  {difficultyInfo.label}
                </div>
                <div className="text-xs text-gray-600">
                  Niveau de difficulté
                </div>
              </div>
            </div>
          </div>
        </CardHeader>
      </Card>

      {/* Description principale */}
      {sentier.description && (
        <Card>
          <CardHeader>
            <CardTitle>📝 Description du sentier</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-gray-700 leading-relaxed whitespace-pre-wrap">
              {sentier.description}
            </p>
          </CardContent>
        </Card>
      )}

      {/* Informations détaillées */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {/* Informations sur la difficulté */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center">
              <span className="text-xl mr-2">{difficultyInfo.icon}</span>
              Niveau de difficulté
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className={`text-lg font-semibold ${difficultyInfo.color} mb-2`}>
              {difficultyInfo.label}
            </div>
            <p className="text-gray-600 text-sm">
              {difficultyInfo.description}
            </p>
          </CardContent>
        </Card>

        {/* Zone géographique */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center">
              🗺️ Zone géographique
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-lg font-semibold text-blue-600 mb-2">
              {sentier.nom_zone || 'Zone non spécifiée'}
            </div>
            <p className="text-gray-600 text-sm">
              Zone ID: {sentier.id_zone}
            </p>
          </CardContent>
        </Card>
      </div>

      {/* Conseils et recommandations */}
      <Card>
        <CardHeader>
          <CardTitle>💡 Conseils pour votre randonnée</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h4 className="font-semibold text-gray-900 mb-2">🎒 Équipement recommandé</h4>
              <ul className="text-sm text-gray-600 space-y-1">
                <li>• Chaussures de randonnée adaptées</li>
                <li>• Eau en quantité suffisante</li>
                <li>• Protection solaire (crème, chapeau)</li>
                <li>• Carte du sentier</li>
                {sentier.niveau_difficulte === 'difficile' && (
                  <>
                    <li>• Bâtons de randonnée</li>
                    <li>• Trousse de premiers secours</li>
                  </>
                )}
              </ul>
            </div>
            
            <div>
              <h4 className="font-semibold text-gray-900 mb-2">🌱 Respect de l'environnement</h4>
              <ul className="text-sm text-gray-600 space-y-1">
                <li>• Restez sur les sentiers balisés</li>
                <li>• Ne cueillez pas la végétation</li>
                <li>• Emportez vos déchets</li>
                <li>• Respectez la faune sauvage</li>
                <li>• Évitez les heures de grande chaleur</li>
              </ul>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Informations techniques */}
      <Card>
        <CardHeader>
          <CardTitle>ℹ️ Informations techniques</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
              <div className="font-semibold text-gray-900">ID Sentier</div>
              <div className="text-gray-600">#{sentier.id_sentier}</div>
            </div>
            <div>
              <div className="font-semibold text-gray-900">Zone ID</div>
              <div className="text-gray-600">#{sentier.id_zone}</div>
            </div>
            <div>
              <div className="font-semibold text-gray-900">Créé le</div>
              <div className="text-gray-600">{formatDate(sentier.created_at)}</div>
            </div>
            <div>
              <div className="font-semibold text-gray-900">Mis à jour le</div>
              <div className="text-gray-600">{formatDate(sentier.updated_at)}</div>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Actions */}
      <Card>
        <CardContent className="pt-6">
          <div className="flex flex-col sm:flex-row gap-3 justify-center">
            <Button 
              className="bg-blue-600 hover:bg-blue-700 text-white"
              onClick={() => alert('Fonctionnalité de réservation à venir !')}
            >
              🎫 Réserver ce sentier
            </Button>
            <Button 
              variant="outline"
              onClick={() => alert('Fonctionnalité à venir !')}
            >
              📍 Voir sur la carte
            </Button>
            <Button 
              variant="outline"
              onClick={() => {
                const url = window.location.href;
                navigator.clipboard.writeText(url);
                alert('Lien copié dans le presse-papiers !');
              }}
            >
              🔗 Partager
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

export default SentierDetail;


