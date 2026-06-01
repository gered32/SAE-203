# E-LLUSION - SAE203

Site web de réservation pour l'exposition E-LLUSION - Projet BUT MMI 1ère année

## Prérequis

- **XAMPP** (ou équivalent : WAMP, MAMP, LAMP)
  - PHP 8.0 ou supérieur
  - MySQL 5.7 ou supérieur
  - Apache

## Installation

### 1. Cloner/Copier le projet

Placer le dossier `SAE-203` dans votre répertoire XAMPP :
```
D:\Xampp\htdocs\SAE203\SAE-203\
```
Ou selon votre système :
- Windows : `C:\xampp\htdocs\SAE203\SAE-203\`
- Mac : `/Applications/XAMPP/htdocs/SAE203/SAE-203/`
- Linux : `/opt/lampp/htdocs/SAE203/SAE-203/`

### 2. Démarrer XAMPP

1. Ouvrir le **XAMPP Control Panel**
2. Démarrer **Apache** (port 80)
3. Démarrer **MySQL** (port 3306)

> **Attention** : Si le port 80 est occupé, vérifier qu'aucun autre serveur web n'est actif (IIS, Skype, etc.)

### 3. Créer la base de données

1. Ouvrir **phpMyAdmin** : [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Cliquer sur **"Nouvelle base de données"**
3. Nom : `sae203_ellusion`
4. Interclassement : `utf8mb4_unicode_ci`
5. Cliquer sur **"Créer"**

### 4. Importer les données

1. Sélectionner la base `sae203_ellusion` dans phpMyAdmin
2. Aller dans l'onglet **"Importer"**
3. Cliquer sur **"Choisir un fichier"**
4. Sélectionner le fichier : `config/database.sql`
5. Cliquer sur **"Exécuter"**

✅ Vous devriez voir le message : **"Importation réussie"**

### 5. Vérifier la configuration

Ouvrir le fichier `config/config.php` et vérifier les lignes 26-33 :

```php
if ($estEnLocal) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'sae203_ellusion');
    define('DB_USER', 'root');
    define('DB_PASS', '');  // Vide par défaut sur XAMPP
    define('SITE_URL', 'http://localhost/SAE203/SAE-203');
    define('DEBUG_MODE', true);
}
```

**Si votre dossier a un chemin différent**, modifier la ligne 32 :
```php
// Exemple : si votre projet est dans htdocs/monprojet/
define('SITE_URL', 'http://localhost/monprojet');
```

### 6. Accéder au site

Ouvrir votre navigateur et aller à :
```
http://localhost/SAE203/SAE-203/
```

## Compte administrateur

Pour accéder au dashboard admin (`/admin/dashboard.php`) :

- **Email** : `admin@ellusion.fr`
- **Mot de passe** : `admin123`

> **Important** : Changer ce mot de passe en production !

## Structure du projet

```
SAE-203/
├── config/              # Configuration (BDD, constantes)
│   ├── config.php       # Fichier de configuration principal
│   └── database.sql     # Script de création de la BDD
├── includes/            # Fichiers réutilisables
│   ├── header.php
│   ├── footer.php
│   └── functions.php
├── admin/               # Espace administration
├── api/                 # Endpoints JSON
├── assets/              # CSS, JS, images, fonts
├── logs/                # Fichiers de log
└── _annexe/             # Documentation

Pages principales :
├── index.php            # Accueil
├── salles.php           # Liste des salles
├── salle-detail.php     # Détail d'une salle
├── inscription.php      # Formulaire d'inscription
├── confirmation.php     # Confirmation après inscription
├── ma-reservation.php   # Gestion de réservation (token)
├── contact.php          # Formulaire de contact
└── login.php            # Connexion admin
```

## Résolution des problèmes courants

### ❌ "Warning: require_once(includes/functions.php): Failed to open stream"

**Cause** : Mauvais chemin ou fichier manquant

**Solution** :
- Vérifier que tous les fichiers sont bien copiés
- Vérifier que vous êtes dans le bon répertoire

### ❌ "SQLSTATE[HY000] [1049] Unknown database 'sae203_ellusion'"

**Cause** : Base de données non créée

**Solution** :
- Retourner à l'étape 3 (Créer la base de données)
- Vérifier le nom exact : `sae203_ellusion`

### ❌ "SQLSTATE[HY000] [2002] Connection refused"

**Cause** : MySQL n'est pas démarré

**Solution** :
- Ouvrir XAMPP Control Panel
- Cliquer sur "Start" à côté de MySQL
- Attendre que le bouton devienne vert

### ❌ Page blanche sans message d'erreur

**Cause** : Erreur PHP masquée

**Solution** :
- Ouvrir `config/config.php`
- Vérifier que `DEBUG_MODE` est à `true` (ligne 33)
- Recharger la page pour voir l'erreur détaillée

### ❌ "Access denied for user 'root'@'localhost'"

**Cause** : Mot de passe MySQL incorrect

**Solution** :
- Par défaut sur XAMPP, le mot de passe est vide (`''`)
- Si vous avez défini un mot de passe, modifier `DB_PASS` dans `config/config.php`

### ❌ Styles CSS non chargés

**Cause** : Chemin incorrect dans SITE_URL

**Solution** :
- Vérifier `SITE_URL` dans `config/config.php` ligne 32
- Il doit correspondre au chemin réel de votre projet

## Fonctionnalités

- ✅ Affichage des salles et œuvres
- ✅ Système d'inscription avec gestion des créneaux
- ✅ Jauge de remplissage (12 personnes max par créneau)
- ✅ Token unique pour modification/suppression de réservation
- ✅ Dashboard administrateur avec export CSV
- ✅ Gestion du buffet du jeudi selon la catégorie
- ✅ API JSON pour les créneaux disponibles
- ✅ Logs de confirmation (simulation email)

## Technologies utilisées

- **Backend** : PHP 8+ (POO, PDO)
- **Base de données** : MySQL
- **Frontend** : HTML5, CSS3, JavaScript vanilla
- **Sécurité** : Requêtes préparées, validation des données, tokens uniques

## Auteur

Projet réalisé dans le cadre de la SAE203 - BUT MMI 1ère année

## Support

En cas de problème :
1. Vérifier que XAMPP (Apache + MySQL) est bien démarré
2. Vérifier que la base de données est créée et importée
3. Vérifier les logs d'erreur PHP dans `xampp/apache/logs/error.log`
4. Activer le mode debug dans `config/config.php` pour voir les erreurs détaillées
