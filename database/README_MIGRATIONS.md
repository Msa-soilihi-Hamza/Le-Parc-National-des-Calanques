# 🗂️ Système de Migrations - Parc National des Calanques

## 📋 Guide pour l'équipe

### 🚀 Installation rapide (nouveaux membres)

```bash
# 1. Cloner le projet
git clone [url-du-repo]
cd Le-Parc-National-des-Calanques

# 2. Créer la base de données
mysql -u root -P 3306 -e "CREATE DATABASE IF NOT EXISTS \`le-parc-national-des-calanques\`"

# 3. Exécuter les migrations
php database/migrate.php
```

### 🔄 Synchronisation quotidienne

```bash
# 1. Récupérer les dernières migrations
git pull origin main

# 2. Exécuter les nouvelles migrations
php database/migrate.php

# 3. Vérifier le statut
php database/migrate.php status
```

## 📁 Structure des migrations

```
database/
├── migrate.php              # Script principal
├── migrations/              # Dossier des migrations
│   ├── 001_create_base_tables.sql
│   ├── 002_create_user_system.sql
│   ├── 003_create_camping_system.sql
│   ├── 004_create_payment_system.sql
│   ├── 005_create_subscription_system.sql
│   └── 006_create_park_features.sql
└── README_MIGRATIONS.md     # Ce fichier
```

## 🛠️ Commandes disponibles

### Exécuter les migrations
```bash
php database/migrate.php
# ou simplement
php database/migrate.php migrate
```

### Vérifier le statut
```bash
php database/migrate.php status
```

## ➕ Ajouter une nouvelle migration

### 1. Créer le fichier
```bash
# Nom format: XXX_description.sql (XXX = numéro à 3 chiffres)
# Exemple: 007_add_photo_system.sql
```

### 2. Structure du fichier
```sql
-- =====================================================
-- MIGRATION 007: Description de votre migration
-- Auteur: [Votre nom]
-- =====================================================

USE \`le-parc-national-des-calanques\`;

SET FOREIGN_KEY_CHECKS = 0;

-- Vos modifications ici
CREATE TABLE exemple (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255)
);

SET FOREIGN_KEY_CHECKS = 1;
```

### 3. Tester localement
```bash
php database/migrate.php status  # Vérifier avant
php database/migrate.php         # Exécuter
```

### 4. Commiter et pousser
```bash
git add database/migrations/007_add_photo_system.sql
git commit -m "Migration: Ajout système de photos"
git push origin main
```

## 🏗️ Workflow d'équipe

### Développeur qui ajoute une migration
1. ✅ Créer la migration localement
2. ✅ Tester sur sa base locale
3. ✅ Commiter le fichier SQL uniquement
4. ✅ Informer l'équipe sur Discord/Slack

### Autres membres de l'équipe
1. ✅ `git pull` pour récupérer les migrations
2. ✅ `php database/migrate.php` pour les appliquer
3. ✅ Continuer le développement

## ⚠️ Règles importantes

### ✅ À FAIRE
- Toujours tester vos migrations localement
- Numéroter les migrations de manière séquentielle
- Inclure `SET FOREIGN_KEY_CHECKS = 0/1` si nécessaire
- Commiter seulement les fichiers `.sql`
- Informer l'équipe des nouvelles migrations

### ❌ À ÉVITER
- Modifier une migration déjà commitée
- Supprimer des migrations existantes
- Commiter des fichiers de base de données (.db, .sql dumps)
- Oublier de tester avant de commit

## 🔧 Configuration

### Modifier la connexion DB
Éditez `database/migrate.php` ligne 8-12 :

```php
private $host = 'localhost';
private $port = '3306';        // Changez si nécessaire
private $dbname = 'le-parc-national-des-calanques';
private $username = 'root';
private $password = '';        // Ajoutez si nécessaire
```

## 🆘 Dépannage

### Erreur "Table already exists"
```bash
# Vérifiez le statut
php database/migrate.php status

# Si une migration apparaît comme "En attente" alors qu'elle est déjà appliquée,
# ajoutez-la manuellement à la table migrations :
mysql -u root -P 3306 le-parc-national-des-calanques -e "INSERT INTO migrations (migration) VALUES ('001_create_base_tables.sql')"
```

### Erreur de connexion
- Vérifiez que WAMP/XAMPP est démarré
- Vérifiez le port MySQL (3306 ou 3306)
- Vérifiez que la base existe

### Migration échoue
- Regardez l'erreur SQL affichée
- Vérifiez les dépendances entre tables
- Testez les requêtes SQL manuellement

## 📞 Support

En cas de problème :
1. Vérifiez ce README
2. Demandez à l'équipe
3. Regardez les logs d'erreur MySQL

---

**🎯 Objectif** : Garder toute l'équipe synchronisée avec la même structure de base de données automatiquement !