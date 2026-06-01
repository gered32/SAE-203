# Changelog - Améliorations de portabilité

## Version 1.1.0 - Corrections de portabilité

### ✅ Problèmes critiques corrigés

#### 1. Détection automatique de l'URL du site
**Fichier:** `config/config.php` (ligne 32-38)

**Avant:**
```php
define('SITE_URL', 'http://localhost/SAE203/SAE-203');
```

**Après:**
```php
// Détection automatique de l'URL du site (compatible tous environnements)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$scriptPath = rtrim($scriptPath, '/');
define('SITE_URL', $protocol . '://' . $_SERVER['HTTP_HOST'] . $scriptPath);
```

**Bénéfice:** Le site fonctionne maintenant quel que soit le nom du dossier d'installation ou le serveur utilisé.

---

#### 2. Amélioration de la détection d'environnement
**Fichier:** `config/config.php` (ligne 20-26)

**Avant:**
```php
$estEnLocal = ($_SERVER['HTTP_HOST'] === 'localhost' || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);
```

**Après:**
```php
// Détection robuste de l'environnement local
$estEnLocal = (
    in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']) ||
    strpos($_SERVER['HTTP_HOST'], '.local') !== false ||
    strpos($_SERVER['HTTP_HOST'], '.test') !== false ||
    strpos($_SERVER['HTTP_HOST'], '192.168.') === 0 ||
    strpos($_SERVER['HTTP_HOST'], '10.') === 0
);
```

**Bénéfice:** Détecte correctement les environnements locaux avec domaines personnalisés (`.local`, `.test`) et réseaux locaux.

---

### ✅ Redirections améliorées

#### 3. Redirections relatives converties en chemins absolus

**Fichiers modifiés:**
- `admin/delete-inscription.php` (3 redirections)
- `admin/edit-inscription.php` (2 redirections)
- `admin/logout.php` (1 redirection)

**Avant:**
```php
header('Location: dashboard.php');
exit;
```

**Après:**
```php
redirect(SITE_URL . '/admin/dashboard.php');
```

**Bénéfice:** Les redirections fonctionnent correctement même si la structure de dossiers change.

---

### ✅ Chemins d'inclusion robustes

#### 4. Conversion des includes relatifs en chemins absolus

**Fichiers modifiés:**
- `index.php`
- `login.php`
- `salles.php`
- `salle-detail.php`
- `inscription.php`
- `confirmation.php`
- `ma-reservation.php`
- `contact.php`

**Avant:**
```php
require_once 'includes/functions.php';
require_once 'includes/header.php';
```

**Après:**
```php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';
```

**Bénéfice:** Les fichiers peuvent être inclus depuis n'importe quel contexte sans erreur de chemin.

---

### ✅ API JavaScript robuste

#### 5. Ajout de SITE_URL global pour JavaScript

**Fichier:** `includes/header.php` (ligne 63)
```php
<!-- Variable globale JavaScript pour l'URL du site -->
<script>window.SITE_URL = '<?php echo SITE_URL; ?>';</script>
```

**Fichier:** `assets/js/main.js` (ligne 292-294)

**Avant:**
```javascript
const response = await fetch(`api/creneaux.php?id_salle=${encodeURIComponent(idSalle)}`);
```

**Après:**
```javascript
// Appel à l'API (utilise SITE_URL global défini dans header.php)
const apiUrl = window.SITE_URL ? `${window.SITE_URL}/api/creneaux.php` : 'api/creneaux.php';
const response = await fetch(`${apiUrl}?id_salle=${encodeURIComponent(idSalle)}`);
```

**Bénéfice:** Les appels API fonctionnent même si le site est dans un sous-dossier.

---

### 📝 Documentation ajoutée

#### 6. Création du README.md

**Fichier:** `README.md`

Contenu complet avec :
- Instructions d'installation détaillées
- Prérequis système (XAMPP, PHP, MySQL)
- Guide de configuration de la base de données
- Résolution des problèmes courants
- Structure du projet expliquée
- Informations de connexion admin

**Bénéfice:** Les collègues peuvent installer le projet en autonomie.

---

## Résumé des améliorations

| Type | Nombre | Impact |
|------|--------|--------|
| **Problèmes critiques** | 2 | Haute priorité - Empêchaient le partage |
| **Redirections** | 6 | Moyenne priorité - Fragilité |
| **Includes** | 16 | Moyenne priorité - Portabilité |
| **API JavaScript** | 1 | Basse priorité - Robustesse |
| **Documentation** | 2 fichiers | Critique - Facilite le partage |

---

## Compatibilité

Le projet est maintenant compatible avec :

- ✅ **Serveurs locaux:** XAMPP, WAMP, MAMP, LAMP
- ✅ **Systèmes d'exploitation:** Windows, macOS, Linux
- ✅ **Structures de dossiers:** N'importe quel nom ou emplacement
- ✅ **Domaines locaux:** `.local`, `.test`, IPs locales (192.168.x.x, 10.x.x.x)
- ✅ **Sous-dossiers:** Fonctionne dans n'importe quel sous-dossier de `htdocs`

---

## Migration

Pour mettre à jour un projet existant :

1. **Sauvegarder** votre base de données actuelle
2. **Remplacer** les fichiers modifiés par les nouveaux
3. **Tester** que tout fonctionne correctement
4. **Aucune modification** de la base de données n'est nécessaire

---

## Utilisation pour vos collègues

Vos collègues peuvent maintenant :

1. Copier le dossier `SAE-203` dans leur `htdocs` (n'importe quel nom)
2. Créer la base de données `sae203_ellusion`
3. Importer `config/database.sql`
4. Accéder au site via `http://localhost/[nom-du-dossier]/`

**Aucune configuration manuelle n'est nécessaire!**

---

## Support

Pour toute question ou problème :

1. Consulter le `README.md`
2. Vérifier que XAMPP (Apache + MySQL) est démarré
3. Vérifier que la base de données est créée et importée
4. Activer `DEBUG_MODE` dans `config/config.php` pour voir les erreurs

---

**Date de mise à jour:** 1 juin 2026  
**Version:** 1.1.0  
**Auteur:** Corrections de portabilité pour SAE203
