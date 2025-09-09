<?php
/**
 * Composant Button réutilisable
 * 
 * @param string $type - Type du bouton (primary, secondary, etc.)
 * @param string $variant - Variante (solid, soft)
 * @param string $text - Texte du bouton
 * @param array $attributes - Attributs HTML supplémentaires
 */

function renderButton($type = 'primary', $variant = 'solid', $text = 'Button', $attributes = []) {
    $baseClass = 'btn';
    
    // Classes selon la variante
    if ($variant === 'soft') {
        $btnClass = $baseClass . ' btn-soft btn-' . $type;
    } else {
        $btnClass = $baseClass . ' btn-' . $type;
    }
    
    // Traitement des attributs supplémentaires
    $attrs = '';
    foreach ($attributes as $key => $value) {
        $attrs .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
    }
    
    return '<button class="' . $btnClass . '"' . $attrs . '>' . htmlspecialchars($text) . '</button>';
}

// Fonctions spécifiques pour chaque type
function primaryButton($text = 'Primary', $attributes = []) {
    return renderButton('primary', 'solid', $text, $attributes);
}

function softPrimaryButton($text = 'Primary', $attributes = []) {
    return renderButton('primary', 'soft', $text, $attributes);
}
?>