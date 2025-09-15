# 🏕️ PARC NATIONAL DES CALANQUES - INSTALLATION ÉQUIPE

## 📁 Fichier à utiliser
**`database/migrations/MIGRATION_COMPLETE_POUR_EQUIPE.sql`**

## 🚀 Instructions pour vos coéquipiers

### **Étape 1 : Préparation**
1. Démarrer WAMP/XAMPP
2. Aller sur phpMyAdmin : `http://localhost/phpmyadmin`
3. Vérifier que MySQL fonctionne

### **Étape 2 : Installation de la base**

#### **Méthode A : Via phpMyAdmin (Simple)**
1. Ouvrir phpMyAdmin
2. Cliquer sur "Importer" dans le menu
3. Choisir le fichier `MIGRATION_COMPLETE_POUR_EQUIPE.sql`
4. Cliquer "Exécuter"

#### **Méthode B : Via ligne de commande**
```bash
# Dans le dossier du projet
mysql -u root -P 3308 < database/migrations/MIGRATION_COMPLETE_POUR_EQUIPE.sql
```

### **Étape 3 : Vérification**
1. Dans phpMyAdmin, sélectionner la base `le-parc-national-des-calanques`
2. Vérifier qu'il y a **14 tables** :
   - Zone
   - Role  
   - Utilisateur
   - Camping
   - Disponibilite_Camping
   - Reservation
   - Paiement
   - Type_Abonnement
   - Abonnement_Utilisateur
   - Sentier
   - Point_Interet
   - Ressource_Naturelle
   - Notification
   - migrations

## 🔑 Connexion admin par défaut
- **Email :** `admin@calanques.fr`
- **Mot de passe :** `password`

## ✅ Résultat attendu
- ✅ Base `le-parc-national-des-calanques` créée
- ✅ 14 tables installées
- ✅ Données de test insérées (zones, rôles, admin, etc.)
- ✅ Toute l'équipe a la même base identique

## 🆘 En cas de problème
1. Vérifier que WAMP/XAMPP est démarré
2. Vérifier le port MySQL (3306 ou 3308)
3. Si erreur "base existe déjà", la supprimer avant l'import
4. Contacter l'équipe sur Discord/WhatsApp

---

**🎯 Une fois installé, tout le monde aura exactement la même base de données !**