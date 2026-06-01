# 03 - PDO et Sécurité

## Objectifs pédagogiques

À la fin de ce chapitre, vous serez capable de :
- Établir une connexion sécurisée à MySQL avec PDO
- Utiliser les requêtes préparées pour éviter les injections SQL
- Protéger contre les failles XSS avec `htmlspecialchars()`
- Implémenter une protection CSRF
- Hasher et vérifier des mots de passe avec `password_hash()`

---

## 1. Connexion PDO

### 1.1 Qu'est-ce que PDO ?

**PDO** (PHP Data Objects) est une extension PHP qui fournit une interface uniforme pour accéder à différentes bases de données (MySQL, PostgreSQL, SQLite...).

### 1.2 Avantages de PDO vs mysqli

| Critère | PDO | mysqli |
|---------|-----|--------|
| Bases supportées | Multiples | MySQL uniquement |
| Requêtes préparées | Oui | Oui |
| Programmation objet | Oui | Oui et procédural |
| Gestion des erreurs | Exceptions | Erreurs classiques |

**Choix du projet :** PDO pour sa portabilité et sa gestion moderne des erreurs.

### 1.3 Code de connexion expliqué

```php
<?php
// config/config.php

function getPDO(): PDO {
    // Variable statique : conserve sa valeur entre les appels (Singleton)
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            // DSN = Data Source Name (chaîne de connexion)
            $dsn = 'mysql:host=localhost;dbname=sae203_ellusion;charset=utf8mb4';
            
            // Options de configuration
            $options = [
                // Lance des exceptions en cas d'erreur SQL
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                
                // Retourne les résultats en tableaux associatifs
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                
                // Désactive l'émulation des requêtes préparées
                // = vraies requêtes préparées côté MySQL (plus sécurisé)
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            // Création de la connexion
            $pdo = new PDO($dsn, 'root', '', $options);
            
        } catch (PDOException $e) {
            // Ne JAMAIS afficher les détails de l'erreur en production !
            die('Erreur de connexion : ' . $e->getMessage());
        }
    }
    
    return $pdo;
}
```

### 1.4 Explication ligne par ligne

| Ligne | Explication |
|-------|-------------|
| `static $pdo = null` | Variable qui persiste entre les appels (évite de se reconnecter à chaque fois) |
| `charset=utf8mb4` | Encodage UTF-8 complet (supporte les emojis) |
| `PDO::ERRMODE_EXCEPTION` | Les erreurs SQL deviennent des exceptions PHP (plus facile à gérer) |
| `PDO::FETCH_ASSOC` | Les résultats sont des tableaux avec les noms de colonnes comme clés |
| `PDO::EMULATE_PREPARES => false` | MySQL prépare vraiment la requête (sécurité renforcée) |

---

## 2. Injection SQL et requêtes préparées

### 2.1 Qu'est-ce qu'une injection SQL ?

Une **injection SQL** est une attaque où un utilisateur malveillant injecte du code SQL via un formulaire.

### 2.2 Exemple d'attaque

**Code vulnérable :**
```php
// ❌ DANGEREUX - Ne JAMAIS faire ça !
$email = $_POST['email'];
$sql = "SELECT * FROM utilisateurs WHERE email = '$email'";
$result = $pdo->query($sql);
```

**Attaque :**
L'utilisateur entre comme email :
```
' OR '1'='1
```

**Requête générée :**
```sql
SELECT * FROM utilisateurs WHERE email = '' OR '1'='1'
```

**Résultat :** L'attaquant récupère TOUS les utilisateurs !

### 2.3 Solution : requêtes préparées

```php
// ✅ SÉCURISÉ - Requête préparée
$email = $_POST['email'];

// Étape 1 : Préparer la requête avec un placeholder
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = :email");

// Étape 2 : Lier la valeur au placeholder
$stmt->bindValue(':email', $email, PDO::PARAM_STR);

// Étape 3 : Exécuter
$stmt->execute();

// Étape 4 : Récupérer les résultats
$utilisateur = $stmt->fetch();
```

### 2.4 Pourquoi c'est sécurisé ?

Avec les requêtes préparées :
1. La structure de la requête est envoyée à MySQL **d'abord**
2. Les valeurs sont envoyées **séparément** et traitées comme des données, jamais comme du code SQL

**Requête avec l'attaque :**
```sql
SELECT * FROM utilisateurs WHERE email = '\' OR \'1\'=\'1'
```
→ Les quotes sont **échappées**, l'attaque échoue !

### 2.5 Types de paramètres PDO

| Constante | Type | Exemple |
|-----------|------|---------|
| `PDO::PARAM_STR` | Chaîne | Email, nom |
| `PDO::PARAM_INT` | Entier | ID, nb_personnes |
| `PDO::PARAM_BOOL` | Booléen | buffet_jeudi |
| `PDO::PARAM_NULL` | NULL | Valeur nulle |

---

## 3. Protection XSS (Cross-Site Scripting)

### 3.1 Qu'est-ce qu'une faille XSS ?

Une attaque **XSS** injecte du code JavaScript malveillant via les données utilisateur.

### 3.2 Exemple d'attaque

**Code vulnérable :**
```php
// ❌ DANGEREUX
<h1>Bienvenue, <?php echo $_GET['nom']; ?></h1>
```

**Attaque :**
L'URL contient :
```
?nom=<script>alert('Piraté!')</script>
```

**HTML généré :**
```html
<h1>Bienvenue, <script>alert('Piraté!')</script></h1>
```

Le script s'exécute dans le navigateur de la victime !

### 3.3 Solution : htmlspecialchars()

```php
// ✅ SÉCURISÉ
<h1>Bienvenue, <?php echo htmlspecialchars($_GET['nom'], ENT_QUOTES, 'UTF-8'); ?></h1>
```

**HTML généré (sécurisé) :**
```html
<h1>Bienvenue, &lt;script&gt;alert('Piraté!')&lt;/script&gt;</h1>
```

Les balises sont **encodées** et affichées comme du texte, pas exécutées.

### 3.4 Fonction utilitaire dans le projet

```php
// includes/functions.php
function sanitize(?string $string): string {
    if ($string === null) return '';
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Utilisation
echo sanitize($reservation['nom']);
```

---

## 4. Protection CSRF

### 4.1 Qu'est-ce qu'une attaque CSRF ?

**CSRF** (Cross-Site Request Forgery) force un utilisateur connecté à effectuer une action à son insu.

### 4.2 Exemple d'attaque

L'attaquant crée une page malveillante :
```html
<img src="https://e-llusion.fr/admin/delete.php?id=1" style="display:none">
```

Si un admin visite cette page pendant qu'il est connecté, son navigateur effectue la requête de suppression !

### 4.3 Solution : token CSRF

**1. Générer un token unique en session :**
```php
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
```

**2. Inclure le token dans le formulaire :**
```html
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
    <!-- autres champs -->
</form>
```

**3. Vérifier le token à la soumission :**
```php
function verifierCsrf(string $token): bool {
    return !empty($_SESSION['csrf_token']) 
        && hash_equals($_SESSION['csrf_token'], $token);
}

// Dans le traitement du formulaire
if (!verifierCsrf($_POST['csrf_token'])) {
    die('Erreur de sécurité');
}
```

### 4.4 Pourquoi c'est sécurisé ?

- Le token est **unique par session**
- L'attaquant ne peut pas **deviner** le token
- `hash_equals()` compare en **temps constant** (protection contre les attaques de timing)

---

## 5. Hashage des mots de passe

### 5.1 Pourquoi hasher ?

Si la base de données est compromise, les mots de passe en clair seraient lisibles.
Le **hashage** transforme le mot de passe en une chaîne irréversible.

### 5.2 Hasher un mot de passe

```php
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);
// Résultat : $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
```

**Caractéristiques :**
- Algorithme bcrypt (par défaut)
- Salt automatique (chaque hash est unique même pour le même mot de passe)
- Coût adaptatif (ralentit les attaques par force brute)

### 5.3 Vérifier un mot de passe

```php
$passwordSaisi = $_POST['password'];
$hashStocke = $utilisateur['mot_de_passe']; // depuis la BDD

if (password_verify($passwordSaisi, $hashStocke)) {
    echo "Mot de passe correct !";
} else {
    echo "Mot de passe incorrect.";
}
```

### 5.4 Flux complet de connexion

```php
// login.php
$email = $_POST['email'];
$password = $_POST['password'];

// 1. Rechercher l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = :email");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

// 2. Vérifier le mot de passe
if ($user && password_verify($password, $user['mot_de_passe'])) {
    // 3. Régénérer l'ID de session (sécurité)
    session_regenerate_id(true);
    
    // 4. Stocker les infos en session
    $_SESSION['user_id'] = $user['id_utilisateur'];
    $_SESSION['role'] = $user['role'];
    
    // 5. Rediriger vers le dashboard
    header('Location: admin/dashboard.php');
    exit;
} else {
    echo "Email ou mot de passe incorrect.";
}
```

---

## 6. Récapitulatif des protections

| Attaque | Protection | Fonction/Technique |
|---------|------------|-------------------|
| Injection SQL | Requêtes préparées | `prepare()`, `bindValue()` |
| XSS | Échappement HTML | `htmlspecialchars()`, `sanitize()` |
| CSRF | Token de formulaire | `csrfToken()`, `verifierCsrf()` |
| Vol de mot de passe | Hashage | `password_hash()`, `password_verify()` |
| Fixation de session | Régénération | `session_regenerate_id(true)` |

---

## 7. Points clés à retenir pour l'oral

1. **PDO** est préféré à mysqli pour sa portabilité et sa gestion des exceptions
2. **Les requêtes préparées** séparent la structure SQL des données utilisateur
3. **htmlspecialchars()** encode les caractères HTML pour éviter l'exécution de scripts
4. **Le token CSRF** vérifie que la requête provient bien de notre formulaire
5. **password_hash()** génère un hash salé unique pour chaque mot de passe

---

## 8. Questions fréquentes du jury

**Q : Pourquoi utiliser PDO::ERRMODE_EXCEPTION ?**
> R : Les exceptions permettent de gérer les erreurs avec try/catch, ce qui est plus propre que les warnings PHP classiques.

**Q : Peut-on décrypter un mot de passe hashé ?**
> R : Non, le hashage est irréversible. On compare uniquement les hashs avec `password_verify()`.

**Q : Pourquoi `session_regenerate_id(true)` après connexion ?**
> R : Pour éviter les attaques de "fixation de session" où un attaquant connaîtrait l'ID de session à l'avance.

**Q : Un token CSRF doit-il changer à chaque requête ?**
> R : Dans notre implémentation, il change après chaque action réussie. Certains systèmes le changent à chaque formulaire, mais c'est plus complexe.
