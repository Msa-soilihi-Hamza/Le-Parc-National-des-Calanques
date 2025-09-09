<?php
/**
 * Composant Badge réutilisable
 * 
 * @param string $text - Texte du badge
 * @param string $type - Type du badge (primary, secondary, accent, neutral, info, success, warning, error)
 * @param string $size - Taille (xs, sm, md, lg)
 * @param array $attributes - Attributs HTML supplémentaires
 */

function renderBadge($text = '', $type = 'neutral', $size = 'md', $attributes = []) {
    // Traitement des attributs supplémentaires
    $attrs = '';
    foreach ($attributes as $key => $value) {
        $attrs .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
    }
    
    // Classes de base
    $baseClass = 'badge';
    
    // Ajouter le type
    $typeClass = $baseClass . ' badge-' . $type;
    
    // Ajouter la taille
    $sizeClasses = [
        'xs' => 'badge-xs',
        'sm' => 'badge-sm',
        'md' => '',
        'lg' => 'badge-lg'
    ];
    
    $sizeClass = isset($sizeClasses[$size]) ? $sizeClasses[$size] : '';
    $finalClass = trim($typeClass . ' ' . $sizeClass);
    
    return '<div class="' . $finalClass . '"' . $attrs . '>' . htmlspecialchars($text) . '</div>';
}

// Fonctions spécifiques pour chaque type
function primaryBadge($text, $size = 'md', $attributes = []) {
    return renderBadge($text, 'primary', $size, $attributes);
}

function secondaryBadge($text, $size = 'md', $attributes = []) {
    return renderBadge($text, 'secondary', $size, $attributes);
}

function accentBadge($text, $size = 'md', $attributes = []) {
    return renderBadge($text, 'accent', $size, $attributes);
}

function neutralBadge($text, $size = 'md', $attributes = []) {
    return renderBadge($text, 'neutral', $size, $attributes);
}

function infoBadge($text, $size = 'md', $attributes = []) {
    return renderBadge($text, 'info', $size, $attributes);
}

function successBadge($text, $size = 'md', $attributes = []) {
    return renderBadge($text, 'success', $size, $attributes);
}

function warningBadge($text, $size = 'md', $attributes = []) {
    return renderBadge($text, 'warning', $size, $attributes);
}

function errorBadge($text, $size = 'md', $attributes = []) {
    return renderBadge($text, 'error', $size, $attributes);
}

/**
 * Fonction pour afficher tous les types de badges (démo)
 */
function renderAllBadges($text = 'Badge', $size = 'md') {
    $types = ['primary', 'secondary', 'accent', 'neutral', 'info', 'success', 'warning', 'error'];
    $html = '<div class="flex flex-wrap gap-2">';
    
    foreach ($types as $type) {
        $html .= renderBadge(ucfirst($type), $type, $size) . ' ';
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * Badge avec icône
 */
function renderBadgeWithIcon($text, $icon, $type = 'neutral', $size = 'md', $attributes = []) {
    // Traitement des attributs supplémentaires
    $attrs = '';
    foreach ($attributes as $key => $value) {
        $attrs .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
    }
    
    $baseClass = 'badge badge-' . $type;
    $sizeClasses = [
        'xs' => 'badge-xs',
        'sm' => 'badge-sm',
        'md' => '',
        'lg' => 'badge-lg'
    ];
    
    $sizeClass = isset($sizeClasses[$size]) ? $sizeClasses[$size] : '';
    $finalClass = trim($baseClass . ' ' . $sizeClass);
    
    return '<div class="' . $finalClass . '"' . $attrs . '>
        <span class="mr-1">' . $icon . '</span>
        ' . htmlspecialchars($text) . '
    </div>';
}

/**
 * Badge de statut prédéfinis
 */
function statusBadge($status) {
    $statuses = [
        'active' => ['text' => '✓ Actif', 'type' => 'success'],
        'inactive' => ['text' => '✗ Inactif', 'type' => 'error'],
        'pending' => ['text' => '⏳ En attente', 'type' => 'warning'],
        'verified' => ['text' => '✅ Vérifié', 'type' => 'success'],
        'new' => ['text' => '🆕 Nouveau', 'type' => 'info']
    ];
    
    if (isset($statuses[$status])) {
        return renderBadge($statuses[$status]['text'], $statuses[$status]['type']);
    }
    
    return renderBadge($status, 'neutral');
}
?>