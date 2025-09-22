describe('Debug - Voir ce qui se charge', () => {
  it('doit afficher le contenu de la page de connexion', () => {
    cy.visit('http://localhost:3005')
    cy.wait(3000)

    // Afficher TOUT le texte de la page
    cy.get('body').then($body => {
      console.log('=== TEXTE COMPLET DE LA PAGE ===')
      console.log($body.text())
      console.log('=== FIN TEXTE ===')
    })

    // Prendre un screenshot
    cy.screenshot('page-connexion')

    // Afficher tous les boutons avec leur texte exact
    cy.get('body').then($body => {
      const buttons = $body.find('button')
      console.log('=== BOUTONS TROUVÉS ===')
      console.log('Nombre de boutons:', buttons.length)
      buttons.each((index, btn) => {
        console.log(`Bouton ${index}: "${btn.textContent}"`)
        console.log(`  - HTML: ${btn.outerHTML}`)
      })
    })

    // Chercher spécifiquement les mots liés à l'inscription
    cy.get('body').then($body => {
      const text = $body.text().toLowerCase()
      console.log('=== RECHERCHE MOTS CLÉS ===')
      console.log('Contient "inscrire":', text.includes('inscrire'))
      console.log('Contient "inscription":', text.includes('inscription'))
      console.log('Contient "créer":', text.includes('créer'))
      console.log('Contient "compte":', text.includes('compte'))
      console.log('Contient "signup":', text.includes('signup'))
    })
  })
})