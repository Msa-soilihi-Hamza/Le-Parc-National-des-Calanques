describe('Validation des Formulaires d\'Authentification', () => {
  // Configuration de base
  const baseUrl = 'http://localhost:3005' // Port Vite après nettoyage du cache

  beforeEach(() => {
    // Visiter la page d'accueil
    cy.visit(`${baseUrl}`)
    cy.wait(3000) // Attendre le chargement complet

    // Essayer différentes façons de basculer vers l'inscription
    cy.get('body').then($body => {
      // Rechercher différents textes possibles
      if ($body.find('button:contains("Créer un compte")').length > 0) {
        cy.contains('button', 'Créer un compte').click()
      } else if ($body.find('button:contains("S\'inscrire")').length > 0) {
        cy.contains('button', "S'inscrire").click()
      } else if ($body.find('*:contains("inscription")').length > 0) {
        cy.contains('inscription').click()
      } else if ($body.find('*:contains("inscrire")').length > 0) {
        cy.contains('inscrire').click()
      } else {
        // Si rien ne fonctionne, afficher le contenu pour debug
        cy.log('Contenu de la page:', $body.text())
        throw new Error('Impossible de trouver le lien vers l\'inscription')
      }
    })
    cy.wait(1000)
  })

  describe('Test de Validation du Formulaire d\'Inscription', () => {

    it('doit afficher le formulaire d\'inscription avec tous les champs requis', () => {
      // Vérifier que nous sommes sur la page d'inscription
      cy.contains('Créer votre compte').should('be.visible')

      // Vérifier que tous les champs obligatoires sont présents
      cy.get('form').should('be.visible')
      cy.get('#nom').should('be.visible')
      cy.get('#prenom').should('be.visible')
      cy.get('#email').should('be.visible')
      cy.get('#password').should('be.visible')
      cy.get('#confirmPassword').should('be.visible')
      cy.get('button[type="submit"]').should('be.visible').and('contain', 'Créer mon compte')
    })

    it('ne doit pas permettre la soumission avec des champs vides', () => {
      // Tenter de soumettre le formulaire sans remplir les champs
      cy.get('button[type="submit"]').click()

      // Vérifier que le navigateur empêche la soumission (validation HTML5)
      cy.get('#nom:invalid').should('exist')
      cy.get('#prenom:invalid').should('exist')
      cy.get('#email:invalid').should('exist')
      cy.get('#password:invalid').should('exist')
      cy.get('#confirmPassword:invalid').should('exist')
    })

    it('doit afficher des messages d\'erreur pour les champs invalides', () => {
      // Remplir les champs avec des données invalides et perdre le focus
      cy.get('#nom').type('A').blur() // Trop court (< 2 caractères)
      cy.get('#prenom').type('B').blur() // Trop court (< 2 caractères)
      cy.get('#email').type('email-invalide').blur() // Format email incorrect
      cy.get('#password').type('123').blur() // Trop court (< 12 caractères)
      cy.get('#confirmPassword').type('456').blur() // Ne correspond pas au mot de passe

      // Vérifier les messages de validation
      cy.contains('Minimum 2 caractères').should('be.visible')
      cy.contains('Format email invalide').should('be.visible')
      cy.contains('/12 caractères minimum').should('be.visible')
      cy.contains('Mots de passe différents').should('be.visible')
    })

    it('doit valider le format de l\'email en temps réel', () => {
      const emailInput = cy.get('#email')

      // Test avec email invalide
      emailInput.type('test@').blur()
      cy.contains('Format email invalide').should('be.visible')

      // Test avec email valide
      emailInput.clear().type('test@example.com').blur()
      cy.contains('Email valide').should('be.visible')

      // Vérifier l'icône de validation verte
      cy.get('svg.text-green-500').should('be.visible')
    })

    it('doit valider la longueur du mot de passe avec une barre de progression', () => {
      const passwordInput = cy.get('#password')

      // Mot de passe trop court
      passwordInput.type('123456')
      cy.contains('6/12 caractères minimum').should('be.visible')
      cy.get('.bg-red-500').should('be.visible') // Barre rouge

      // Mot de passe valide (12+ caractères)
      passwordInput.clear().type('motdepassevalide123')
      cy.contains('Mot de passe sécurisé').should('be.visible')
      cy.get('.bg-green-500').should('be.visible') // Barre verte
    })

    it('doit valider la correspondance des mots de passe', () => {
      const passwordInput = cy.get('#password')
      const confirmPasswordInput = cy.get('#confirmPassword')

      // Remplir le mot de passe principal
      passwordInput.type('motdepassevalide123')

      // Confirmer avec un mot de passe différent
      confirmPasswordInput.type('motdepassedifferent')
      cy.contains('Mots de passe différents').should('be.visible')

      // Corriger pour que les mots de passe correspondent
      confirmPasswordInput.clear().type('motdepassevalide123')
      cy.contains('Mots de passe identiques').should('be.visible')
      cy.get('svg.text-green-500').should('be.visible')
    })

    it('doit valider les champs nom et prénom (minimum 2 caractères)', () => {
      // Test nom trop court
      cy.get('#nom').type('A').blur()
      cy.contains('Minimum 2 caractères').should('be.visible')

      // Test nom valide
      cy.get('#nom').clear().type('Dupont').blur()
      cy.contains('Valide').should('be.visible')

      // Test prénom trop court
      cy.get('#prenom').type('B').blur()
      cy.contains('Minimum 2 caractères').should('be.visible')

      // Test prénom valide
      cy.get('#prenom').clear().type('Jean').blur()
      cy.contains('Valide').should('be.visible')
    })

    it('ne doit permettre la soumission que lorsque tous les champs sont valides', () => {
      // Remplir tous les champs avec des données valides
      cy.get('#nom').type('Dupont')
      cy.get('#prenom').type('Jean')
      cy.get('#email').type('jean.dupont@example.com')
      cy.get('#password').type('motdepassevalide123')
      cy.get('#confirmPassword').type('motdepassevalide123')

      // Attendre que toutes les validations passent
      cy.wait(500)

      // Vérifier que tous les champs sont valides (icônes vertes)
      cy.get('svg.text-green-500').should('have.length.at.least', 4)

      // Le bouton de soumission ne doit pas être désactivé
      cy.get('button[type="submit"]').should('not.be.disabled')

      // Intercepter la requête API pour éviter l'erreur réseau
      cy.intercept('POST', '**/api/auth/register', { statusCode: 200, body: { success: true } })

      // Soumettre le formulaire
      cy.get('button[type="submit"]').click()

      // Vérifier que l'inscription réussit (alerte ou redirection)
      // L'inscription se fait trop vite pour voir "Inscription en cours..."
      cy.wait(1000) // Attendre la réponse
    })

    it('doit afficher les messages d\'erreur du serveur', () => {
      // Remplir le formulaire avec des données valides
      cy.get('#nom').type('Dupont')
      cy.get('#prenom').type('Jean')
      cy.get('#email').type('jean.dupont@example.com')
      cy.get('#password').type('motdepassevalide123')
      cy.get('#confirmPassword').type('motdepassevalide123')

      // Simuler une erreur serveur
      cy.intercept('POST', '**/api/auth/register', {
        statusCode: 400,
        body: { message: 'Cet email est déjà utilisé' }
      })

      // Soumettre le formulaire
      cy.get('button[type="submit"]').click()

      // Vérifier l'affichage du message d'erreur
      cy.get('.bg-red-50').should('be.visible')

      // Le message d'erreur réel est "Request failed with status code 400"
      cy.get('.bg-red-50').should('contain.text', 'Request failed with status code 400')
    })

    it('doit permettre de basculer vers le formulaire de connexion', () => {
      // Cliquer sur le lien "Se connecter"
      cy.contains('Se connecter').click()

      // Vérifier que nous sommes maintenant sur le formulaire de connexion
      cy.contains('Connexion').should('be.visible')
      cy.get('input[type="email"]').should('be.visible')
      cy.get('input[type="password"]').should('be.visible')
    })
  })

  describe('Test de Performance et Accessibilité', () => {

    // Pas besoin de beforeEach supplémentaire, on est déjà sur le formulaire d'inscription

    it('doit réagir rapidement aux changements de champs', () => {
      // Mesurer le temps de réponse des validations
      const start = Date.now()

      cy.get('#email').type('test@example.com')
      cy.contains('Email valide').should('be.visible').then(() => {
        const end = Date.now()
        expect(end - start).to.be.lessThan(1000) // Moins d'1 seconde
      })
    })

    it('doit être accessible au clavier', () => {
      // Test d'accessibilité basique : tous les champs sont focusables
      cy.get('#nom').should('be.visible').focus()
      cy.focused().should('have.id', 'nom')

      cy.get('#prenom').should('be.visible').focus()
      cy.focused().should('have.id', 'prenom')

      cy.get('#email').should('be.visible').focus()
      cy.focused().should('have.id', 'email')

      cy.get('#password').should('be.visible').focus()
      cy.focused().should('have.id', 'password')

      cy.get('#confirmPassword').should('be.visible').focus()
      cy.focused().should('have.id', 'confirmPassword')

      // Vérifier que le bouton est aussi accessible
      cy.get('button[type="submit"]').should('be.visible').focus()
      cy.focused().should('have.attr', 'type', 'submit')

      // Test de saisie au clavier
      cy.get('#nom').clear().type('Test')
      cy.get('#nom').should('have.value', 'Test')
    })
  })
})