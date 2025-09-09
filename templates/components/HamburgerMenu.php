<?php
/**
 * Composant HamburgerMenu avec animation swap
 * 
 * @param string $id - ID unique pour le contrôle (optionnel)
 * @param array $attributes - Attributs HTML supplémentaires
 */

function renderHamburgerMenu($id = '', $attributes = []) {
    // Traitement des attributs supplémentaires
    $attrs = '';
    foreach ($attributes as $key => $value) {
        $attrs .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
    }
    
    $inputId = $id ? 'id="' . htmlspecialchars($id) . '"' : '';
    
    return '<label class="btn btn-circle swap swap-rotate"' . $attrs . '>
        <!-- Checkbox caché qui contrôle l\'état -->
        <input type="checkbox" ' . $inputId . ' />

        <!-- Icône hamburger -->
        <svg
            class="swap-off fill-current"
            xmlns="http://www.w3.org/2000/svg"
            width="32"
            height="32"
            viewBox="0 0 512 512">
            <path d="M64,384H448V341.33H64Zm0-106.67H448V234.67H64ZM64,128v42.67H448V128Z" />
        </svg>

        <!-- Icône fermeture -->
        <svg
            class="swap-on fill-current"
            xmlns="http://www.w3.org/2000/svg"
            width="32"
            height="32"
            viewBox="0 0 512 512">
            <polygon
                points="400 145.49 366.51 112 256 222.51 145.49 112 112 145.49 222.51 256 112 366.51 145.49 400 256 289.49 366.51 400 400 366.51 289.49 256 400 145.49" />
        </svg>
    </label>';
}

/**
 * Fonction pour créer un menu hamburger avec JavaScript personnalisé
 */
function renderHamburgerMenuWithJS($id, $onToggle = '', $attributes = []) {
    // Traitement des attributs supplémentaires
    $attrs = '';
    foreach ($attributes as $key => $value) {
        $attrs .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
    }
    
    $jsAction = $onToggle ? 'onchange="' . htmlspecialchars($onToggle) . '"' : '';
    
    return '<label class="btn btn-circle swap swap-rotate"' . $attrs . '>
        <!-- Checkbox caché qui contrôle l\'état -->
        <input type="checkbox" id="' . htmlspecialchars($id) . '" ' . $jsAction . ' />

        <!-- Icône hamburger -->
        <svg
            class="swap-off fill-current"
            xmlns="http://www.w3.org/2000/svg"
            width="32"
            height="32"
            viewBox="0 0 512 512">
            <path d="M64,384H448V341.33H64Zm0-106.67H448V234.67H64ZM64,128v42.67H448V128Z" />
        </svg>

        <!-- Icône fermeture -->
        <svg
            class="swap-on fill-current"
            xmlns="http://www.w3.org/2000/svg"
            width="32"
            height="32"
            viewBox="0 0 512 512">
            <polygon
                points="400 145.49 366.51 112 256 222.51 145.49 112 112 145.49 222.51 256 112 366.51 145.49 400 256 289.49 366.51 400 400 366.51 289.49 256 400 145.49" />
        </svg>
    </label>';
}
?>