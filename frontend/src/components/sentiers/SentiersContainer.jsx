import React, { useState } from 'react';
import SentiersList from './SentiersList.jsx';
import SentierDetail from './SentierDetail.jsx';

const SentiersContainer = () => {
  const [selectedSentier, setSelectedSentier] = useState(null);
  const [currentView, setCurrentView] = useState('list'); // 'list' ou 'detail'

  const handleSentierSelect = (sentier) => {
    setSelectedSentier(sentier);
    setCurrentView('detail');
  };

  const handleBackToList = () => {
    setSelectedSentier(null);
    setCurrentView('list');
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="container mx-auto px-4 py-8">
        {currentView === 'list' ? (
          <SentiersList onSentierSelect={handleSentierSelect} />
        ) : (
          <SentierDetail 
            sentierId={selectedSentier?.id_sentier} 
            onBack={handleBackToList}
          />
        )}
      </div>
    </div>
  );
};

export default SentiersContainer;


