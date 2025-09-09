# Composants DaisyUI - Parc National des Calanques

## Utilisation

Inclure les composants dans vos templates PHP :

```php
<?php require_once 'templates/components/Button.php'; ?>
<?php require_once 'templates/components/GoogleButton.php'; ?>
<?php require_once 'templates/components/Modal.php'; ?>
<?php require_once 'templates/components/HamburgerMenu.php'; ?>
```

## Exemples d'utilisation

### Button
```php
// Bouton primary standard
echo primaryButton('Réserver');

// Bouton primary soft
echo softPrimaryButton('Annuler');

// Bouton personnalisé
echo renderButton('secondary', 'solid', 'Voir plus', ['onclick' => 'showDetails()']);
```

### GoogleButton
```php
// Bouton Google basique
echo renderGoogleButton();

// Bouton Google personnalisé
echo renderGoogleButton('Se connecter avec Google', ['onclick' => 'handleGoogleAuth()']);
```

### Modal
```php
// Modal simple
echo renderModal('reservation_modal', 'Confirmer la réservation', 'Voulez-vous confirmer cette réservation ?', 'Réserver');

// Modal avec contenu HTML
echo renderCustomModal('info_modal', 'Informations sur la zone', '<p>Cette zone est protégée...</p>', 'Infos');
```

### HamburgerMenu
```php
// Menu hamburger simple
echo renderHamburgerMenu();

// Menu hamburger avec action JavaScript
echo renderHamburgerMenuWithJS('nav_toggle', 'toggleNavigation()');
```

### Accordion
```php
// FAQ prédéfinie
echo renderFAQAccordion();

// Accordéon personnalisé
$items = [
    ['title' => 'Question 1', 'content' => 'Réponse 1', 'checked' => true],
    ['title' => 'Question 2', 'content' => 'Réponse 2']
];
echo renderAccordion($items, 'my_accordion');
```

### Avatar
```php
// Avatar placeholder
echo placeholderAvatar('JD');

// Avatar avec image
echo imageAvatar('/images/user.jpg', 'lg');

// Avatar en ligne
echo onlineAvatar('/images/user.jpg', 'JD');
```

### Badge
```php
// Badge simple
echo primaryBadge('Nouveau');
echo successBadge('Actif');
echo errorBadge('Erreur');

// Tous les badges
echo renderAllBadges();

// Badge de statut
echo statusBadge('active');
```

### Navbar
```php
// Navbar complète avec panier
echo renderNavbar('Mon Site', '/', ['count' => 3, 'total' => '$50']);

// Navbar simple
echo renderSimpleNavbar('Mon Site');

// Navbar spécifique au parc
echo renderParcNavbar('user', '/images/avatar.jpg');
```