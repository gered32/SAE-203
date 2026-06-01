# 01 - Architecture du projet E-LLUSION

## Objectifs pédagogiques

À la fin de ce chapitre, vous serez capable de :
- Comprendre l'organisation des fichiers d'un projet web PHP
- Expliquer le flux de données entre le navigateur, PHP et MySQL
- Justifier les choix d'architecture du projet

---

## 1. Structure des dossiers

### 1.1 Vue d'ensemble

```
SAE-203/
├── index.php                 # Page d'accueil
├── salles.php                # Liste des salles
├── salle-detail.php          # Détail d'une salle
├── inscription.php           # Formulaire d'inscription
├── confirmation.php          # Confirmation après inscription
├── ma-reservation.php        # Gestion de réservation (token)
├── contact.php               # Formulaire de contact
├── login.php                 # Connexion admin
│
├── config/                   # Configuration
│   ├── config.php            # Connexion BDD + constantes
│   └── database.sql          # Script SQL de création
│
├── includes/                 # Fichiers réutilisables
│   ├── header.php            # En-tête HTML
│   ├── footer.php            # Pied de page
│   └── functions.php         # Fonctions utilitaires
│
├── admin/                    # Espace administration
│   ├── dashboard.php         # Tableau de bord
│   ├── export-csv.php        # Export tableur
│   └── logout.php            # Déconnexion
│
├── api/                      # API JSON
│   └── creneaux.php          # Créneaux disponibles
│
├── assets/                   # Ressources statiques
│   ├── css/style.css         # Feuille de style
│   ├── js/main.js            # JavaScript
│   ├── fonts/                # Polices (CutePixel)
│   └── images/               # Images
│
├── logs/                     # Fichiers de log
│   └── confirmation.log      # Simulation emails
│
└── _annexe/                  # Documentation (ce dossier)
```

### 1.2 Pourquoi cette organisation ?

| Dossier | Rôle | Avantage |
|---------|------|----------|
| `config/` | Paramètres centralisés | Modification facile selon l'environnement |
| `includes/` | Code réutilisable | Évite la duplication (DRY) |
| `admin/` | Pages protégées | Séparation claire public/privé |
| `api/` | Endpoints JSON | Communication front-back |
| `assets/` | Fichiers statiques | Cache navigateur optimisé |

---

## 2. Flux de données : de la requête à la réponse

### 2.1 Schéma général

```
┌─────────────┐      ┌─────────────┐      ┌─────────────┐      ┌─────────────┐
│ NAVIGATEUR  │ ──→  │   SERVEUR   │ ──→  │     PHP     │ ──→  │    MySQL    │
│  (Client)   │      │   (Apache)  │      │  (Script)   │      │    (BDD)    │
└─────────────┘      └─────────────┘      └─────────────┘      └─────────────┘
       │                    │                    │                    │
       │  1. Requête HTTP   │                    │                    │
       │ ────────────────→  │                    │                    │
       │                    │  2. Exécute PHP    │                    │
       │                    │ ────────────────→  │                    │
       │                    │                    │  3. Requête SQL    │
       │                    │                    │ ────────────────→  │
       │                    │                    │                    │
       │                    │                    │  4. Résultat       │
       │                    │                    │ ←────────────────  │
       │                    │                    │                    │
       │                    │  5. Génère HTML    │                    │
       │                    │ ←────────────────  │                    │
       │                    │                    │                    │
       │  6. Réponse HTML   │                    │                    │
       │ ←────────────────  │                    │                    │
       │                    │                    │                    │
```

### 2.2 Exemple concret : afficher la liste des salles

**Étape 1 : L'utilisateur clique sur "Les Salles"**

Le navigateur envoie une requête HTTP :
```
GET /SAE203/SAE-203/salles.php HTTP/1.1
Host: localhost
```

**Étape 2 : Apache reçoit la requête et exécute `salles.php`**

```php
<?php
// salles.php - Ligne 1 à 10
require_once 'includes/functions.php';  // Charge les fonctions + config
$salles = getSalles();                   // Appelle la fonction
```

**Étape 3 : PHP envoie une requête SQL à MySQL**

```php
// includes/functions.php - Fonction getSalles()
function getSalles(): array {
    $pdo = getPDO();  // Récupère la connexion PDO
    $stmt = $pdo->query("SELECT * FROM salles ORDER BY numero");
    return $stmt->fetchAll();
}
```

La requête SQL envoyée :
```sql
SELECT * FROM salles ORDER BY numero
```

**Étape 4 : MySQL retourne les données**

```
+----------+--------+--------------------+--------------+
| id_salle | numero | nom                | capacite_max |
+----------+--------+--------------------+--------------+
|        2 | 001    | Salle Interactive  |           12 |
|        1 | 002    | Salle Immersive    |           12 |
|        3 | 005    | Salle Contemplative|           12 |
|        4 | 021    | Salle Expérimentale|           12 |
+----------+--------+--------------------+--------------+
```

**Étape 5 : PHP génère le HTML**

```php
// salles.php - Boucle d'affichage
<?php foreach ($salles as $salle): ?>
<article class="card">
    <h2><?php echo sanitize($salle['nom']); ?></h2>
    <!-- ... -->
</article>
<?php endforeach; ?>
```

**Étape 6 : Le HTML est envoyé au navigateur**

```html
<article class="card">
    <h2>Salle Interactive</h2>
    <!-- ... -->
</article>
<article class="card">
    <h2>Salle Immersive</h2>
    <!-- ... -->
</article>
<!-- etc. -->
```

---

## 3. Le pattern "Include" en PHP

### 3.1 Principe

Au lieu de répéter le même code dans chaque page, on le met dans un fichier séparé qu'on inclut.

**❌ Sans include (code dupliqué) :**
```php
// index.php
<!DOCTYPE html>
<html><head><title>Accueil</title></head><body>
<header><nav>...</nav></header>
<!-- Contenu spécifique -->
<footer>...</footer></body></html>

// salles.php
<!DOCTYPE html>
<html><head><title>Salles</title></head><body>
<header><nav>...</nav></header>  <!-- Copie identique ! -->
<!-- Contenu spécifique -->
<footer>...</footer></body></html>  <!-- Copie identique ! -->
```

**✅ Avec include (code factorisé) :**
```php
// index.php
<?php require_once 'includes/header.php'; ?>
<!-- Contenu spécifique -->
<?php require_once 'includes/footer.php'; ?>

// salles.php
<?php require_once 'includes/header.php'; ?>
<!-- Contenu spécifique -->
<?php require_once 'includes/footer.php'; ?>
```

### 3.2 Différence entre `include` et `require`

| Instruction | Comportement si fichier absent |
|-------------|-------------------------------|
| `include` | Warning, le script continue |
| `require` | Fatal error, le script s'arrête |
| `include_once` | Comme `include`, mais n'inclut qu'une fois |
| `require_once` | Comme `require`, mais n'inclut qu'une fois |

**Recommandation :** Utiliser `require_once` pour les fichiers critiques (config, functions).

---

## 4. Séparation des responsabilités

### 4.1 Chaque fichier a un rôle précis

| Fichier | Responsabilité |
|---------|----------------|
| `config.php` | Connexion BDD, constantes |
| `functions.php` | Fonctions réutilisables |
| `header.php` | Structure HTML commune (haut) |
| `footer.php` | Structure HTML commune (bas) |
| Pages PHP | Logique métier + affichage |

### 4.2 Avantages de cette séparation

1. **Maintenabilité** : modifier le header = 1 seul fichier
2. **Lisibilité** : chaque fichier est court et ciblé
3. **Testabilité** : les fonctions peuvent être testées isolément
4. **Collaboration** : plusieurs développeurs peuvent travailler en parallèle

---

## 5. Points clés à retenir pour l'oral

1. **L'architecture suit le principe DRY** (Don't Repeat Yourself)
2. **Le dossier `config/` centralise les paramètres** pour faciliter le déploiement
3. **Le dossier `includes/` contient le code réutilisable** (header, footer, functions)
4. **Le dossier `admin/` est séparé** pour les pages nécessitant une authentification
5. **Le flux de données est : Navigateur → Apache → PHP → MySQL → PHP → Navigateur**

---

## 6. Questions fréquentes du jury

**Q : Pourquoi ne pas mettre tout le code dans un seul fichier ?**
> R : La séparation facilite la maintenance, la lisibilité et le travail en équipe. Un fichier de 5000 lignes serait ingérable.

**Q : Pourquoi utiliser `require_once` plutôt que `require` ?**
> R : `require_once` évite d'inclure plusieurs fois le même fichier, ce qui pourrait causer des erreurs (redéfinition de fonctions, connexions multiples...).

**Q : Que se passe-t-il si `config.php` est absent ?**
> R : Le script s'arrête avec une erreur fatale car on utilise `require_once`. C'est voulu : sans configuration, le site ne peut pas fonctionner.
