<?php
// Script simple pour créer la base et les tables nécessaires

echo "<h1>Création Base de Données Simple</h1>";

try {
    // Connexion MySQL
    $pdo = new PDO("mysql:host=localhost;port=3308;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ Connexion MySQL réussie</p>";
    
    // Créer la base si elle n'existe pas
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `le-parc-national-des-calanques`");
    echo "<p>✅ Base de données créée/vérifiée</p>";
    
    // Utiliser la base
    $pdo->exec("USE `le-parc-national-des-calanques`");
    
    // Créer table users (version simple pour JWT)
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(191) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        is_active BOOLEAN NOT NULL DEFAULT TRUE,
        email_verified_at TIMESTAMP NULL,
        remember_token VARCHAR(100) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "<p>✅ Table 'users' créée</p>";
    
    // Vérifier si on a des utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch()['count'];
    
    if ($count == 0) {
        echo "<p>📝 Création des utilisateurs de test...</p>";
        
        // Hash des mots de passe
        $adminPassword = password_hash('admin123', PASSWORD_ARGON2ID);
        $userPassword = password_hash('user123', PASSWORD_ARGON2ID);
        
        // Insérer utilisateurs de test
        $pdo->exec("
            INSERT INTO users (email, password_hash, role, first_name, last_name) VALUES
            ('admin@calanques.fr', '$adminPassword', 'admin', 'Admin', 'Calanques'),
            ('user@calanques.fr', '$userPassword', 'user', 'User', 'Test')
        ");
        
        echo "<p>✅ Utilisateurs de test créés:</p>";
        echo "<ul>";
        echo "<li>Admin: admin@calanques.fr / admin123</li>";
        echo "<li>User: user@calanques.fr / user123</li>";
        echo "</ul>";
    } else {
        echo "<p>✅ $count utilisateurs trouvés dans la base</p>";
    }
    
    echo "<div style='background: #d4edda; padding: 20px; margin: 20px 0; border: 1px solid #c3e6cb; border-radius: 5px;'>";
    echo "<h3>🎉 Base de données prête !</h3>";
    echo "<p>Vous pouvez maintenant tester:</p>";
    echo "<ul>";
    echo "<li><a href='index.php'>Page d'accueil principale</a></li>";
    echo "<li><a href='api.php?test=1'>Test API simple</a></li>";
    echo "<li>Endpoints JWT avec Postman/curl</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erreur: " . $e->getMessage() . "</p>";
}
?>