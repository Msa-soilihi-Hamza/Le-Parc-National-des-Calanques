<?php
/**
 * Composant Navbar réutilisable avec dropdown et avatar
 * 
 * @param string $brandText - Texte de la marque
 * @param string $brandUrl - URL de la marque
 * @param array $cartItems - Informations du panier ['count' => 8, 'total' => '$999']
 * @param array $userMenu - Menu utilisateur [['text' => 'Profile', 'url' => '#', 'badge' => 'New'], ...]
 * @param string $avatarSrc - URL de l'avatar utilisateur
 * @param array $attributes - Attributs HTML supplémentaires
 */

function renderNavbar($brandText = 'daisyUI', $brandUrl = '#', $cartItems = [], $userMenu = [], $avatarSrc = '', $attributes = []) {
    // Traitement des attributs supplémentaires
    $attrs = '';
    foreach ($attributes as $key => $value) {
        $attrs .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
    }
    
    // Données par défaut du panier
    $defaultCart = ['count' => 8, 'total' => '$999'];
    $cart = array_merge($defaultCart, $cartItems);
    
    // Menu utilisateur par défaut
    $defaultUserMenu = [
        ['text' => 'Profile', 'url' => '#', 'badge' => 'New'],
        ['text' => 'Settings', 'url' => '#'],
        ['text' => 'Logout', 'url' => '#']
    ];
    $menu = !empty($userMenu) ? $userMenu : $defaultUserMenu;
    
    $html = '<div class="navbar bg-base-100 shadow-sm"' . $attrs . '>
        <div class="flex-1">
            <a href="' . htmlspecialchars($brandUrl) . '" class="btn btn-ghost text-xl">' . htmlspecialchars($brandText) . '</a>
        </div>
        <div class="flex-none">
            <!-- Dropdown Panier -->
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn btn-ghost btn-circle">
                    <div class="indicator">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="badge badge-sm indicator-item">' . htmlspecialchars($cart['count']) . '</span>
                    </div>
                </div>
                <div tabindex="0" class="card card-compact dropdown-content bg-base-100 z-1 mt-3 w-52 shadow">
                    <div class="card-body">
                        <span class="text-lg font-bold">' . htmlspecialchars($cart['count']) . ' Items</span>
                        <span class="text-info">Subtotal: ' . htmlspecialchars($cart['total']) . '</span>
                        <div class="card-actions">
                            <button class="btn btn-primary btn-block">View cart</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Dropdown Avatar -->
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                    <div class="w-10 rounded-full">';
    
    if (!empty($avatarSrc)) {
        $html .= '<img alt="User Avatar" src="' . htmlspecialchars($avatarSrc) . '" />';
    } else {
        $html .= '<div class="bg-neutral text-neutral-content w-10 h-10 rounded-full flex items-center justify-center">
                    <span class="text-sm">UI</span>
                  </div>';
    }
    
    $html .= '</div>
                </div>
                <ul tabindex="0" class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow">';
    
    foreach ($menu as $item) {
        $text = htmlspecialchars($item['text']);
        $url = htmlspecialchars($item['url']);
        $badge = isset($item['badge']) ? '<span class="badge">' . htmlspecialchars($item['badge']) . '</span>' : '';
        
        if ($badge) {
            $html .= '<li><a href="' . $url . '" class="justify-between">' . $text . $badge . '</a></li>';
        } else {
            $html .= '<li><a href="' . $url . '">' . $text . '</a></li>';
        }
    }
    
    $html .= '</ul>
            </div>
        </div>
    </div>';
    
    return $html;
}

/**
 * Navbar simple sans panier
 */
function renderSimpleNavbar($brandText = 'Mon Site', $brandUrl = '#', $userMenu = [], $avatarSrc = '', $attributes = []) {
    // Traitement des attributs supplémentaires
    $attrs = '';
    foreach ($attributes as $key => $value) {
        $attrs .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
    }
    
    $defaultUserMenu = [
        ['text' => 'Profil', 'url' => '/profile'],
        ['text' => 'Paramètres', 'url' => '/settings'],
        ['text' => 'Déconnexion', 'url' => '/logout']
    ];
    $menu = !empty($userMenu) ? $userMenu : $defaultUserMenu;
    
    $html = '<div class="navbar bg-base-100 shadow-sm"' . $attrs . '>
        <div class="flex-1">
            <a href="' . htmlspecialchars($brandUrl) . '" class="btn btn-ghost text-xl">' . htmlspecialchars($brandText) . '</a>
        </div>
        <div class="flex-none">
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                    <div class="w-10 rounded-full">';
    
    if (!empty($avatarSrc)) {
        $html .= '<img alt="User Avatar" src="' . htmlspecialchars($avatarSrc) . '" />';
    } else {
        $html .= '<div class="bg-neutral text-neutral-content w-10 h-10 rounded-full flex items-center justify-center">
                    <span class="text-sm">UI</span>
                  </div>';
    }
    
    $html .= '</div>
                </div>
                <ul tabindex="0" class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow">';
    
    foreach ($menu as $item) {
        $text = htmlspecialchars($item['text']);
        $url = htmlspecialchars($item['url']);
        $badge = isset($item['badge']) ? '<span class="badge">' . htmlspecialchars($item['badge']) . '</span>' : '';
        
        if ($badge) {
            $html .= '<li><a href="' . $url . '" class="justify-between">' . $text . $badge . '</a></li>';
        } else {
            $html .= '<li><a href="' . $url . '">' . $text . '</a></li>';
        }
    }
    
    $html .= '</ul>
            </div>
        </div>
    </div>';
    
    return $html;
}

/**
 * Navbar pour le projet Parc National des Calanques
 */
function renderParcNavbar($userRole = 'visitor', $avatarSrc = '', $attributes = []) {
    $brandText = 'Parc des Calanques';
    $brandUrl = '/';
    
    // Menu selon le rôle
    $userMenus = [
        'admin' => [
            ['text' => 'Tableau de bord', 'url' => '/admin/dashboard'],
            ['text' => 'Gestion zones', 'url' => '/admin/zones'],
            ['text' => 'Réservations', 'url' => '/admin/reservations'],
            ['text' => 'Profil', 'url' => '/profile'],
            ['text' => 'Déconnexion', 'url' => '/logout']
        ],
        'user' => [
            ['text' => 'Mes réservations', 'url' => '/reservations'],
            ['text' => 'Profil', 'url' => '/profile'],
            ['text' => 'Paramètres', 'url' => '/settings'],
            ['text' => 'Déconnexion', 'url' => '/logout']
        ],
        'visitor' => [
            ['text' => 'Connexion', 'url' => '/login'],
            ['text' => 'Inscription', 'url' => '/register']
        ]
    ];
    
    $menu = isset($userMenus[$userRole]) ? $userMenus[$userRole] : $userMenus['visitor'];
    
    return renderSimpleNavbar($brandText, $brandUrl, $menu, $avatarSrc, $attributes);
}
?>