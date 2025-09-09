<?php
/**
 * Composant Avatar réutilisable
 * 
 * @param string $type - Type d'avatar (image, placeholder, online)
 * @param string $src - URL de l'image (pour type 'image')
 * @param string $initials - Initiales (pour type 'placeholder')
 * @param string $size - Taille (xs, sm, md, lg, xl)
 * @param array $attributes - Attributs HTML supplémentaires
 */

function renderAvatar($type = 'placeholder', $src = '', $initials = 'UI', $size = 'md', $attributes = []) {
    // Traitement des attributs supplémentaires
    $attrs = '';
    foreach ($attributes as $key => $value) {
        $attrs .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
    }
    
    // Définir les tailles
    $sizeClasses = [
        'xs' => 'w-6',
        'sm' => 'w-8',
        'md' => 'w-12',
        'lg' => 'w-16',
        'xl' => 'w-24'
    ];
    
    $textSizes = [
        'xs' => 'text-xs',
        'sm' => 'text-xs',
        'md' => 'text-sm',
        'lg' => 'text-lg',
        'xl' => 'text-xl'
    ];
    
    $sizeClass = isset($sizeClasses[$size]) ? $sizeClasses[$size] : $sizeClasses['md'];
    $textSize = isset($textSizes[$size]) ? $textSizes[$size] : $textSizes['md'];
    
    $html = '<div class="avatar"' . $attrs . '>';
    
    switch ($type) {
        case 'image':
            $html .= '<div class="' . $sizeClass . ' rounded-full">';
            $html .= '<img src="' . htmlspecialchars($src) . '" alt="Avatar" />';
            $html .= '</div>';
            break;
            
        case 'online':
            $html .= '<div class="' . $sizeClass . ' rounded-full online">';
            if (!empty($src)) {
                $html .= '<img src="' . htmlspecialchars($src) . '" alt="Avatar" />';
            } else {
                $html .= '<div class="bg-neutral text-neutral-content rounded-full flex items-center justify-center">';
                $html .= '<span class="' . $textSize . '">' . htmlspecialchars($initials) . '</span>';
                $html .= '</div>';
            }
            $html .= '</div>';
            break;
            
        case 'placeholder':
        default:
            $html .= '<div class="avatar-placeholder">';
            $html .= '<div class="bg-neutral text-neutral-content ' . $sizeClass . ' rounded-full">';
            $html .= '<span class="' . $textSize . '">' . htmlspecialchars($initials) . '</span>';
            $html .= '</div>';
            $html .= '</div>';
            break;
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Avatar avec image
 */
function imageAvatar($src, $size = 'md', $attributes = []) {
    return renderAvatar('image', $src, '', $size, $attributes);
}

/**
 * Avatar placeholder avec initiales
 */
function placeholderAvatar($initials = 'UI', $size = 'md', $attributes = []) {
    return renderAvatar('placeholder', '', $initials, $size, $attributes);
}

/**
 * Avatar online (avec indicateur de statut)
 */
function onlineAvatar($src = '', $initials = 'UI', $size = 'md', $attributes = []) {
    return renderAvatar('online', $src, $initials, $size, $attributes);
}

/**
 * Groupe d'avatars
 */
function renderAvatarGroup($avatars = [], $attributes = []) {
    if (empty($avatars)) {
        return '';
    }
    
    // Traitement des attributs supplémentaires
    $attrs = '';
    foreach ($attributes as $key => $value) {
        $attrs .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
    }
    
    $html = '<div class="avatar-group -space-x-6 rtl:space-x-reverse"' . $attrs . '>';
    
    foreach ($avatars as $avatar) {
        $type = isset($avatar['type']) ? $avatar['type'] : 'placeholder';
        $src = isset($avatar['src']) ? $avatar['src'] : '';
        $initials = isset($avatar['initials']) ? $avatar['initials'] : 'UI';
        $size = isset($avatar['size']) ? $avatar['size'] : 'md';
        
        $html .= renderAvatar($type, $src, $initials, $size);
    }
    
    $html .= '</div>';
    
    return $html;
}
?>