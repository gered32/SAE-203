# 06 - Dashboard Administration

## Objectifs pédagogiques

À la fin de ce chapitre, vous serez capable de :
- Implémenter une authentification par session PHP
- Protéger des pages avec vérification de connexion
- Utiliser GROUP BY pour des statistiques agrégées
- Générer un fichier CSV pour Excel

---

## 1. Authentification par session PHP

### 1.1 Qu'est-ce qu'une session ?

Une **session** permet de conserver des informations sur l'utilisateur entre les pages.

```
┌─────────────┐                  ┌─────────────┐
│ NAVIGATEUR  │  ────cookie───→  │   SERVEUR   │
│             │  PHPSESSID=abc   │             │
│             │                  │  $_SESSION  │
│             │                  │  ['user']=1 │
└─────────────┘                  └─────────────┘
```

- Le serveur génère un **ID de session** (PHPSESSID)
- Cet ID est stocké dans un **cookie** côté navigateur
- Le serveur associe cet ID à des données (`$_SESSION`)

### 1.2 Démarrage de la session

```php
// Doit être appelé AVANT tout output HTML
session_start();

// Maintenant on peut utiliser $_SESSION
$_SESSION['user_id'] = 123;
$_SESSION['role'] = 'admin';
```

### 1.3 Flux de connexion complet

```php
// login.php

// 1. Démarrer la session
session_start();

// 2. Si formulaire soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // 3. Rechercher l'utilisateur en BDD
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
    
    // 4. Vérifier le mot de passe
    if ($user && password_verify($password, $user['mot_de_passe'])) {
        
        // 5. SÉCURITÉ : Régénérer l'ID de session
        session_regenerate_id(true);
        
        // 6. Stocker les infos utilisateur
        $_SESSION['user_id'] = $user['id_utilisateur'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = time();
        
        // 7. Rediriger vers le dashboard
        header('Location: admin/dashboard.php');
        exit;
        
    } else {
        $erreur = "Email ou mot de passe incorrect.";
    }
}
```

### 1.4 Pourquoi session_regenerate_id() ?

Cette fonction protège contre les attaques de **fixation de session** :

1. L'attaquant obtient un ID de session
2. Il force la victime à utiliser cet ID
3. Quand la victime se connecte, l'attaquant a accès à sa session

**Solution :** Générer un nouvel ID après la connexion → l'ancien ID est invalide.

---

## 2. Protection des pages admin

### 2.1 Fonction de vérification

```php
// includes/functions.php

function estConnecte(): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

function protegerPageAdmin(): void {
    if (!estConnecte()) {
        // Stocker un message flash
        $_SESSION['flash_message'] = [
            'message' => 'Veuillez vous connecter.',
            'type' => 'error'
        ];
        header('Location: ../login.php');
        exit;
    }
}
```

### 2.2 Utilisation dans les pages admin

```php
// admin/dashboard.php

<?php
require_once __DIR__ . '/../includes/functions.php';

// Cette ligne protège la page
protegerPageAdmin();

// Si on arrive ici, l'utilisateur est connecté
echo "Bienvenue, " . $_SESSION['user_nom'];
?>
```

### 2.3 Déconnexion

```php
// admin/logout.php

<?php
session_start();

// Vider les données de session
$_SESSION = [];

// Supprimer le cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Détruire la session
session_destroy();

// Rediriger vers login
header('Location: ../login.php');
exit;
```

---

## 3. Statistiques avec GROUP BY

### 3.1 Principe de GROUP BY

`GROUP BY` regroupe les lignes par valeur et permet d'appliquer des fonctions d'agrégation (COUNT, SUM, AVG...).

**Exemple :**
```sql
-- Compter les inscriptions par catégorie
SELECT 
    cat.nom AS categorie,
    COUNT(*) AS nombre
FROM inscriptions i
JOIN categories cat ON i.id_categorie = cat.id_categorie
GROUP BY cat.id_categorie;
```

**Résultat :**
```
+------------------------+--------+
| categorie              | nombre |
+------------------------+--------+
| Enseignant·e           |      5 |
| Étudiant·e MMI 2 ou 3  |     12 |
| Personnel USMB         |      3 |
+------------------------+--------+
```

### 3.2 Statistiques du dashboard

```php
// admin/dashboard.php

// Nombre total d'inscriptions
$totalInscriptions = $pdo->query("SELECT COUNT(*) FROM inscriptions")->fetchColumn();

// Nombre total de personnes
$totalPersonnes = $pdo->query("SELECT COALESCE(SUM(nb_personnes), 0) FROM inscriptions")->fetchColumn();

// Participants au buffet
$totalBuffet = $pdo->query("SELECT COALESCE(SUM(nb_personnes), 0) FROM inscriptions WHERE buffet_jeudi = 1")->fetchColumn();

// Créneaux complets
$sqlComplets = "
    SELECT COUNT(*) FROM (
        SELECT c.id_creneau
        FROM creneaux c
        LEFT JOIN reservations r ON c.id_creneau = r.id_creneau
        LEFT JOIN inscriptions i ON r.id_inscription = i.id_inscription
        GROUP BY c.id_creneau
        HAVING c.places_total - COALESCE(SUM(i.nb_personnes), 0) <= 0
    ) AS complets
";
$creneauxComplets = $pdo->query($sqlComplets)->fetchColumn();
```

### 3.3 État des jauges par créneau

```sql
SELECT 
    cr.id_creneau,
    s.numero AS salle_numero,
    cr.date_creneau,
    cr.heure,
    cr.places_total,
    COALESCE(SUM(i.nb_personnes), 0) AS places_occupees,
    cr.places_total - COALESCE(SUM(i.nb_personnes), 0) AS places_restantes
FROM creneaux cr
JOIN salles s ON cr.id_salle = s.id_salle
LEFT JOIN reservations r ON cr.id_creneau = r.id_creneau
LEFT JOIN inscriptions i ON r.id_inscription = i.id_inscription
GROUP BY cr.id_creneau
ORDER BY cr.date_creneau, cr.heure, s.numero
```

---

## 4. Tableau HTML accessible

### 4.1 Structure accessible

```html
<table class="table">
    <thead>
        <tr>
            <!-- scope="col" indique que c'est un en-tête de colonne -->
            <th scope="col">#</th>
            <th scope="col">Nom</th>
            <th scope="col">Email</th>
            <th scope="col">Salle</th>
            <th scope="col">Date</th>
            <!-- ... -->
        </tr>
    </thead>
    <tbody>
        <?php foreach ($inscriptions as $index => $insc): ?>
        <tr>
            <td><?php echo $index + 1; ?></td>
            <td><?php echo sanitize($insc['nom'] . ' ' . $insc['prenom']); ?></td>
            <td>
                <a href="mailto:<?php echo sanitize($insc['email']); ?>">
                    <?php echo sanitize($insc['email']); ?>
                </a>
            </td>
            <td><?php echo sanitize($insc['salle_numero']); ?></td>
            <td><?php echo date('d/m/Y', strtotime($insc['date_creneau'])); ?></td>
            <!-- ... -->
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
```

### 4.2 Attribut scope

L'attribut `scope` aide les lecteurs d'écran à associer les cellules à leurs en-têtes :

| Valeur | Signification |
|--------|---------------|
| `scope="col"` | En-tête de colonne |
| `scope="row"` | En-tête de ligne |
| `scope="colgroup"` | En-tête de groupe de colonnes |

---

## 5. Export CSV

### 5.1 Qu'est-ce qu'un CSV ?

**CSV** (Comma-Separated Values) est un format texte simple pour les données tabulaires :

```
Nom;Prénom;Email
Dupont;Jean;jean@email.fr
Martin;Marie;marie@email.fr
```

### 5.2 Headers HTTP pour le téléchargement

```php
// admin/export-csv.php

// Type MIME : fichier CSV en UTF-8
header('Content-Type: text/csv; charset=utf-8');

// Forcer le téléchargement (pas l'affichage)
header('Content-Disposition: attachment; filename="inscriptions_' . date('Y-m-d') . '.csv"');

// Pas de cache
header('Pragma: no-cache');
header('Expires: 0');
```

### 5.3 BOM UTF-8 pour Excel

Excel sur Windows a besoin du **BOM** (Byte Order Mark) pour reconnaître l'UTF-8 :

```php
// Les 3 octets magiques du BOM UTF-8
echo "\xEF\xBB\xBF";
```

Sans le BOM, les caractères accentués seraient mal affichés dans Excel.

### 5.4 Génération avec fputcsv()

```php
// Ouvrir le flux de sortie
$output = fopen('php://output', 'w');

// Écrire les en-têtes (première ligne)
fputcsv($output, [
    'Date inscription',
    'Nom',
    'Prénom',
    'Email',
    'Catégorie',
    'Salle',
    'Date créneau',
    'Heure',
    'Nb personnes',
    'Buffet'
], ';');  // Séparateur point-virgule (standard français)

// Écrire les données
foreach ($inscriptions as $insc) {
    fputcsv($output, [
        date('d/m/Y H:i', strtotime($insc['date_inscription'])),
        $insc['nom'],
        $insc['prenom'],
        $insc['email'],
        $insc['categorie'],
        $insc['salle_numero'],
        date('d/m/Y', strtotime($insc['date_creneau'])),
        date('H:i', strtotime($insc['heure'])),
        $insc['nb_personnes'],
        $insc['buffet_jeudi'] ? 'Oui' : 'Non'
    ], ';');
}

// Fermer le flux
fclose($output);

// Important : arrêter le script
exit;
```

### 5.5 Résultat

Fichier `inscriptions_2026-06-15.csv` :
```
Date inscription;Nom;Prénom;Email;Catégorie;Salle;Date créneau;Heure;Nb personnes;Buffet
15/06/2026 10:30;Dupont;Jean;jean@email.fr;Enseignant·e;002;18/06/2026;15:00;3;Oui
15/06/2026 11:45;Martin;Marie;marie@email.fr;Étudiant·e MMI 2 ou 3;001;18/06/2026;16:00;1;Non
```

---

## 6. Points clés à retenir pour l'oral

1. **Session PHP** = stockage de données côté serveur, identifié par un cookie
2. **session_regenerate_id(true)** = protection contre la fixation de session
3. **GROUP BY** = regroupement pour calculs agrégés (COUNT, SUM...)
4. **scope="col"** = accessibilité des tableaux HTML
5. **BOM UTF-8** = nécessaire pour que Excel affiche correctement les accents
6. **fputcsv()** = fonction PHP native pour générer du CSV

---

## 7. Questions fréquentes du jury

**Q : Pourquoi stocker le rôle en session plutôt que le relire en BDD à chaque page ?**
> R : Pour des raisons de performance. Lire la BDD à chaque requête ralentirait le site. On stocke les infos essentielles en session.

**Q : Que se passe-t-il si l'utilisateur modifie le cookie PHPSESSID ?**
> R : L'ID de session est aléatoire (128 bits). La probabilité de deviner un ID valide est quasi nulle. Et les données sont côté serveur, pas dans le cookie.

**Q : Pourquoi utiliser un point-virgule comme séparateur CSV ?**
> R : En France, la virgule est le séparateur décimal. Le point-virgule est le standard pour les CSV français. Excel le reconnaît automatiquement.

**Q : Le fichier CSV est-il sécurisé ?**
> R : La page est protégée par `protegerPageAdmin()`. Seuls les utilisateurs connectés peuvent télécharger le CSV. Mais il faut faire attention à ne pas exposer des données sensibles.
