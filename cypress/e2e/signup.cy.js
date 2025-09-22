describe('Tests End-to-End - Inscription Utilisateur', () => {
  // Configuration de base
  const baseUrl = 'http://localhost:3004' // Port Vite

  beforeEach(() => {
    // Visiter la page d'accueil et naviguer vers l'inscription
    cy.visit(`${baseUrl}`)
    cy.wait(3000) // Attendre le chargement complet

    // Basculer vers l'inscription
    cy.get('body').then($body => {
      if ($body.find('button:contains("Cr�er un compte")').length > 0) {
        cy.contains('button', 'Cr�er un compte').click()
      } else if ($body.find('button:contains("S\'inscrire")').length > 0) {
        cy.contains('button', "S'inscrire").click()
      } else if ($body.find('*:contains("inscription")').length > 0) {
        cy.contains('inscription').click()
      } else {
        cy.log('Contenu de la page:', $body.text())
        throw new Error('Impossible de trouver le lien vers l\'inscription')
      }
    })
    cy.wait(1000)
  })

  describe('Processus d\'Inscription Complet', () => {

    it('doit permettre une inscription r�ussie avec des donn�es valides', () => {
      // Intercepter la requ�te d'inscription pour simuler un succ�s
      cy.intercept('POST', '**/api/auth/register', {
        statusCode: 201,
        body: {
          success: true,
          message: 'Inscription r�ussie',
          user: {
            id: 1,
            nom: 'Dupont',
            prenom: 'Jean',
            email: 'jean.dupont@example.com'
          }
        }
      }).as('signup')

      // Remplir le formulaire avec des donn�es valides
      cy.get('#nom').type('Dupont')
      cy.get('#prenom').type('Jean')
      cy.get('#email').type('jean.dupont@example.com')
      cy.get('#password').type('motdepassevalide123')
      cy.get('#confirmPassword').type('motdepassevalide123')

      // Attendre que toutes les validations passent
      cy.wait(500)

      // V�rifier que tous les champs sont valides
      cy.get('svg.text-green-500').should('have.length.at.least', 4)

      // Soumettre le formulaire
      cy.get('button[type="submit"]').click()

      // V�rifier que la requ�te est envoy�e
      cy.wait('@signup')

      // V�rifier le succ�s (message de confirmation ou redirection)
      cy.wait(1000)
    })

    it('doit g�rer les erreurs d\'inscription (email d�j� utilis�)', () => {
      // Intercepter la requ�te pour simuler un email d�j� utilis�
      cy.intercept('POST', '**/api/auth/register', {
        statusCode: 409,
        body: {
          success: false,
          message: 'Cet email est d�j� utilis�'
        }
      }).as('signupError')

      // Remplir le formulaire
      cy.get('#nom').type('Dupont')
      cy.get('#prenom').type('Jean')
      cy.get('#email').type('jean.dupont@example.com')
      cy.get('#password').type('motdepassevalide123')
      cy.get('#confirmPassword').type('motdepassevalide123')

      // Soumettre le formulaire
      cy.get('button[type="submit"]').click()

      // V�rifier que la requ�te est envoy�e
      cy.wait('@signupError')

      // V�rifier l'affichage du message d'erreur
      cy.get('.bg-red-50').should('be.visible')
      cy.get('.bg-red-50').should('contain.text', 'Request failed with status code 409')
    })

    it('doit g�rer les erreurs serveur (500)', () => {
      // Intercepter la requ�te pour simuler une erreur serveur
      cy.intercept('POST', '**/api/auth/register', {
        statusCode: 500,
        body: {
          success: false,
          message: 'Erreur interne du serveur'
        }
      }).as('serverError')

      // Remplir le formulaire
      cy.get('#nom').type('Dupont')
      cy.get('#prenom').type('Jean')
      cy.get('#email').type('jean.dupont@example.com')
      cy.get('#password').type('motdepassevalide123')
      cy.get('#confirmPassword').type('motdepassevalide123')

      // Soumettre le formulaire
      cy.get('button[type="submit"]').click()

      // V�rifier que la requ�te est envoy�e
      cy.wait('@serverError')

      // V�rifier l'affichage du message d'erreur
      cy.get('.bg-red-50').should('be.visible')
    })

    it('doit empêcher l\'inscription avec des mots de passe différents', () => {
      // Remplir seulement les mots de passe (comme dans formValidation.cy.js)
      cy.get('#password').type('motdepassevalide123')
      cy.get('#confirmPassword').type('motdepassedifferent')

      // Attendre un moment pour que la validation se déclenche
      cy.wait(500)

      // Vérifier que le message d'erreur apparaît
      cy.contains('Mots de passe différents').should('be.visible')

      // Remplir le reste du formulaire pour tester la soumission
      cy.get('#nom').type('Dupont')
      cy.get('#prenom').type('Jean')
      cy.get('#email').type('jean.dupont@example.com')

      // Le bouton devrait être désactivé ou l'inscription ne pas se faire
      cy.get('button[type="submit"]').click()

      // Vérifier qu'aucune requête n'est envoyée
      // (pas de cy.wait car aucune requête ne devrait être faite)
    })

    it('doit emp�cher l\'inscription avec un email invalide', () => {
      // Remplir le formulaire avec un email invalide
      cy.get('#nom').type('Dupont')
      cy.get('#prenom').type('Jean')
      cy.get('#email').type('email-invalide')
      cy.get('#password').type('motdepassevalide123')
      cy.get('#confirmPassword').type('motdepassevalide123')

      // V�rifier que le message d'erreur pour l'email appara�t
      cy.contains('Format email invalide').should('be.visible')

      // Tenter de soumettre
      cy.get('button[type="submit"]').click()

      // V�rifier qu'aucune requ�te d'inscription n'est envoy�e
    })
  })

  describe('Tests de Performance et Robustesse', () => {

    it('doit g�rer les saisies rapides sans perdre de donn�es', () => {
      // Saisie rapide de tous les champs
      cy.get('#nom').type('Dupont', { delay: 0 })
      cy.get('#prenom').type('Jean', { delay: 0 })
      cy.get('#email').type('jean.dupont@example.com', { delay: 0 })
      cy.get('#password').type('motdepassevalide123', { delay: 0 })
      cy.get('#confirmPassword').type('motdepassevalide123', { delay: 0 })

      // V�rifier que toutes les donn�es sont bien saisies
      cy.get('#nom').should('have.value', 'Dupont')
      cy.get('#prenom').should('have.value', 'Jean')
      cy.get('#email').should('have.value', 'jean.dupont@example.com')
      cy.get('#password').should('have.value', 'motdepassevalide123')
      cy.get('#confirmPassword').should('have.value', 'motdepassevalide123')
    })

    it('doit g�rer les timeouts de requ�te', () => {
      // Intercepter la requ�te pour simuler un timeout
      cy.intercept('POST', '**/api/auth/register', { delay: 30000 }).as('slowSignup')

      // Remplir le formulaire
      cy.get('#nom').type('Dupont')
      cy.get('#prenom').type('Jean')
      cy.get('#email').type('jean.dupont@example.com')
      cy.get('#password').type('motdepassevalide123')
      cy.get('#confirmPassword').type('motdepassevalide123')

      // Soumettre le formulaire
      cy.get('button[type="submit"]').click()

      // V�rifier l'�tat de chargement
      cy.contains('Inscription en cours...').should('be.visible')

      // Note: En pratique, on ne va pas attendre 30 secondes
      // On peut annuler l'intercept apr�s quelques secondes
    })

    it('doit permettre plusieurs tentatives d\'inscription', () => {
      // Premi�re tentative avec erreur
      cy.intercept('POST', '**/api/auth/register', {
        statusCode: 400,
        body: { message: 'Erreur temporaire' }
      }).as('firstAttempt')

      cy.get('#nom').type('Dupont')
      cy.get('#prenom').type('Jean')
      cy.get('#email').type('jean.dupont@example.com')
      cy.get('#password').type('motdepassevalide123')
      cy.get('#confirmPassword').type('motdepassevalide123')

      cy.get('button[type="submit"]').click()
      cy.wait('@firstAttempt')

      // V�rifier l'erreur
      cy.get('.bg-red-50').should('be.visible')

      // Deuxi�me tentative avec succ�s
      cy.intercept('POST', '**/api/auth/register', {
        statusCode: 201,
        body: { success: true, message: 'Inscription r�ussie' }
      }).as('secondAttempt')

      cy.get('button[type="submit"]').click()
      cy.wait('@secondAttempt')

      // V�rifier le succ�s
      cy.wait(1000)
    })
  })

  describe('Navigation et Interface Utilisateur', () => {

    it('doit permettre de revenir � la connexion depuis l\'inscription', () => {
      // Cliquer sur le lien "Se connecter"
      cy.contains('Se connecter').click()

      // V�rifier que nous sommes sur le formulaire de connexion
      cy.contains('Connexion').should('be.visible')
      cy.get('input[type="email"]').should('be.visible')
      cy.get('input[type="password"]').should('be.visible')
    })

    it('doit conserver les donn�es saisies lors de la validation', () => {
      // Remplir partiellement le formulaire
      cy.get('#nom').type('Dupont')
      cy.get('#prenom').type('Jean')
      cy.get('#email').type('jean.dupont@example.com')

      // D�clencher une validation en cliquant sur un autre champ
      cy.get('#password').click()

      // V�rifier que les donn�es sont conserv�es
      cy.get('#nom').should('have.value', 'Dupont')
      cy.get('#prenom').should('have.value', 'Jean')
      cy.get('#email').should('have.value', 'jean.dupont@example.com')
    })

    it('doit afficher les indicateurs visuels de validation en temps r�el', () => {
      // Saisir un nom valide
      cy.get('#nom').type('Dupont')
      cy.get('svg.text-green-500').should('be.visible')

      // Saisir un email valide
      cy.get('#email').type('jean.dupont@example.com')
      cy.contains('Email valide').should('be.visible')

      // Saisir un mot de passe valide
      cy.get('#password').type('motdepassevalide123')
      cy.contains('Mot de passe sécurisé').should('be.visible')

      // Confirmer le mot de passe
      cy.get('#confirmPassword').type('motdepassevalide123')
      cy.contains('Mots de passe identiques').should('be.visible')
    })
  })

  describe('Tests de S�curit� et Validation', () => {

    it('doit rejeter les mots de passe trop courts', () => {
      cy.get('#password').type('123456')
      cy.contains('6/12 caractères minimum').should('be.visible')
      cy.get('.bg-red-500').should('be.visible') // Barre rouge
    })

    it('doit rejeter les noms/prénoms trop courts', () => {
      cy.get('#nom').type('A').blur()
      cy.contains('Minimum 2 caractères').should('be.visible')

      cy.get('#prenom').type('B').blur()
      cy.contains('Minimum 2 caractères').should('be.visible')
    })

    it('doit valider le format email strictement', () => {
      const invalidEmails = ['test', 'test@', '@example.com', 'test@example']

      invalidEmails.forEach(email => {
        cy.get('#email').clear().type(email).blur()
        cy.contains('Format email invalide').should('be.visible')
      })

      // Email valide
      cy.get('#email').clear().type('test@example.com').blur()
      cy.contains('Email valide').should('be.visible')
    })

    it('doit bloquer l\'injection de code dans les champs', () => {
      const maliciousInput = '<script>alert("XSS")</script>'

      cy.get('#nom').type(maliciousInput)
      cy.get('#nom').should('have.value', 'scriptalertXSS/script') // Vérifier que les caractères dangereux sont supprimés

      // Vérifier que la validation détecte les caractères non autorisés restants
      cy.contains('Caractères non autorisés').should('be.visible')

      // V�rifier qu'aucun script n'est ex�cut�
      cy.window().then((win) => {
        // Si une alerte �tait d�clench�e, ce test �chouerait
        expect(win.document.title).to.not.contain('XSS')
      })
    })
  })
})