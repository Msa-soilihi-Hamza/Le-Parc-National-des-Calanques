<?php
/**
 * Composant Accordion réutilisable
 * 
 * @param array $items - Tableau des éléments de l'accordéon [['title' => '', 'content' => '', 'checked' => false], ...]
 * @param string $name - Nom du groupe radio (pour n'ouvrir qu'un élément à la fois)
 * @param array $attributes - Attributs HTML supplémentaires
 */

function renderAccordion($items = [], $name = 'accordion', $attributes = []) {
    if (empty($items)) {
        return '';
    }
    
    // Traitement des attributs supplémentaires
    $attrs = '';
    foreach ($attributes as $key => $value) {
        $attrs .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
    }
    
    $html = '<div class="accordion-group"' . $attrs . '>';
    
    foreach ($items as $index => $item) {
        $title = isset($item['title']) ? htmlspecialchars($item['title']) : '';
        $content = isset($item['content']) ? htmlspecialchars($item['content']) : '';
        $checked = isset($item['checked']) && $item['checked'] ? 'checked="checked"' : '';
        
        $html .= '
        <div class="collapse collapse-plus bg-base-100 border border-base-300">
            <input type="radio" name="' . htmlspecialchars($name) . '" ' . $checked . ' />
            <div class="collapse-title font-semibold">' . $title . '</div>
            <div class="collapse-content text-sm">' . $content . '</div>
        </div>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Fonction pour créer un accordéon FAQ prédéfini
 */
function renderFAQAccordion($name = 'faq') {
    $faqItems = [
        [
            'title' => 'How do I create an account?',
            'content' => 'Click the "Sign Up" button in the top right corner and follow the registration process.',
            'checked' => true
        ],
        [
            'title' => 'I forgot my password. What should I do?',
            'content' => 'Click on "Forgot Password" on the login page and follow the instructions sent to your email.'
        ],
        [
            'title' => 'How do I update my profile information?',
            'content' => 'Go to "My Account" settings and select "Edit Profile" to make changes.'
        ]
    ];
    
    return renderAccordion($faqItems, $name);
}

/**
 * Fonction pour créer un accordéon avec contenu HTML personnalisé
 */
function renderCustomAccordion($items = [], $name = 'accordion', $attributes = []) {
    if (empty($items)) {
        return '';
    }
    
    // Traitement des attributs supplémentaires
    $attrs = '';
    foreach ($attributes as $key => $value) {
        $attrs .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
    }
    
    $html = '<div class="accordion-group"' . $attrs . '>';
    
    foreach ($items as $index => $item) {
        $title = isset($item['title']) ? htmlspecialchars($item['title']) : '';
        $content = isset($item['content']) ? $item['content'] : ''; // Pas de htmlspecialchars pour permettre HTML
        $checked = isset($item['checked']) && $item['checked'] ? 'checked="checked"' : '';
        
        $html .= '
        <div class="collapse collapse-plus bg-base-100 border border-base-300">
            <input type="radio" name="' . htmlspecialchars($name) . '" ' . $checked . ' />
            <div class="collapse-title font-semibold">' . $title . '</div>
            <div class="collapse-content text-sm">' . $content . '</div>
        </div>';
    }
    
    $html .= '</div>';
    
    return $html;
}
?>