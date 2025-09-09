<?php
/**
 * Composant Modal réutilisable
 * 
 * @param string $id - ID unique du modal
 * @param string $title - Titre du modal
 * @param string $content - Contenu du modal
 * @param string $buttonText - Texte du bouton d'ouverture
 * @param array $attributes - Attributs HTML supplémentaires pour le bouton
 */

function renderModal($id, $title = 'Hello!', $content = 'Press ESC key or click the button below to close', $buttonText = 'Open Modal', $attributes = []) {
    // Traitement des attributs supplémentaires
    $attrs = '';
    foreach ($attributes as $key => $value) {
        $attrs .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
    }
    
    return '
    <!-- Bouton d\'ouverture du modal -->
    <button class="btn" onclick="' . $id . '.showModal()"' . $attrs . '>' . htmlspecialchars($buttonText) . '</button>
    
    <!-- Modal -->
    <dialog id="' . $id . '" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">' . htmlspecialchars($title) . '</h3>
            <p class="py-4">' . htmlspecialchars($content) . '</p>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn">Close</button>
                </form>
            </div>
        </div>
    </dialog>';
}

/**
 * Fonction pour créer un modal avec contenu HTML personnalisé
 */
function renderCustomModal($id, $title, $htmlContent, $buttonText = 'Open Modal', $attributes = []) {
    // Traitement des attributs supplémentaires
    $attrs = '';
    foreach ($attributes as $key => $value) {
        $attrs .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
    }
    
    return '
    <!-- Bouton d\'ouverture du modal -->
    <button class="btn" onclick="' . $id . '.showModal()"' . $attrs . '>' . htmlspecialchars($buttonText) . '</button>
    
    <!-- Modal -->
    <dialog id="' . $id . '" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">' . htmlspecialchars($title) . '</h3>
            <div class="py-4">' . $htmlContent . '</div>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn">Close</button>
                </form>
            </div>
        </div>
    </dialog>';
}
?>