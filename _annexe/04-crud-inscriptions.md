# 04 - CRUD des inscriptions

## Objectifs pédagogiques

À la fin de ce chapitre, vous serez capable de :
- Expliquer les 4 opérations CRUD
- Implémenter une création avec validation et transaction
- Lire des données avec des jointures SQL
- Mettre à jour des données en gérant les contraintes
- Supprimer des données en cascade

---

## 1. Qu'est-ce que CRUD ?

**CRUD** est l'acronyme des 4 opérations de base sur les données :

| Lettre | Opération | SQL | HTTP | Page |
|--------|-----------|-----|------|------|
| **C** | Create (Créer) | INSERT | POST | inscription.php |
| **R** | Read (Lire) | SELECT | GET | confirmation.php |
| **U** | Update (Modifier) | UPDATE | POST | ma-reservation.php |
| **D** | Delete (Supprimer) | DELETE | POST | ma-reservation.php |

---

## 2. CREATE - Création d'une inscription

### 2.1 Flux complet

```
┌─────────────────────────────────────────────────────────────┐
│                    INSCRIPTION.PHP                          │
├─────────────────────────────────────────────────────────────┤
│ 1. Affichage du formulaire (GET)                            │
│ 2. Soumission du formulaire (POST)                          │
│ 3. Vérification CSRF                                        │
│ 4. Validation des données                                   │
│ 5. BEGIN TRANSACTION                                        │
│ 6. Vérification des places (SELECT)                         │
│ 7. Génération du token                                      │
│ 8. INSERT inscription                                       │
│ 9. INSERT réservation                                       │
│ 10. COMMIT ou ROLLBACK                                      │
│ 11. Envoi email (simulation)                                │
│ 12. Redirection vers confirmation                           │
└─────────────────────────────────────────────────────────────┘
```

### 2.2 Code détaillé

```php
// inscription.php - Traitement POST

// Étape 1-2 : Vérification CSRF
if (!verifierCsrf($_POST['csrf_token'])) {
    $erreurs[] = "Erreur de sécurité.";
}

// Étape 3 : Récupération et nettoyage
$nom = nettoyer($_POST['nom']);
$prenom = nettoyer($_POST['prenom']);
$email = nettoyer($_POST['email']);
$id_categorie = intval($_POST['id_categorie']);
$id_creneau = intval($_POST['id_creneau']);
$nb_personnes = intval($_POST['nb_personnes']);
$buffet_jeudi = isset($_POST['buffet_jeudi']) ? 1 : 0;

// Étape 4 : Validation
if (empty($nom)) $erreurs[] = "Le nom est requis.";
if (!validerEmail($email)) $erreurs[] = "Email invalide.";
// ... autres validations ...

// Si pas d'erreur, on continue
if (empty($erreurs)) {
    try {
        // Étape 5 : Début de la transaction
        $pdo->beginTransaction();
        
        // Étape 6 : Vérification des places
        $sqlPlaces = "
            SELECT c.places_total - COALESCE(SUM(i.nb_personnes), 0) AS places_restantes
            FROM creneaux c
            LEFT JOIN reservations r ON c.id_creneau = r.id_creneau
            LEFT JOIN inscriptions i ON r.id_inscription = i.id_inscription
            WHERE c.id_creneau = :id_creneau
            GROUP BY c.id_creneau
        ";
        $stmtPlaces = $pdo->prepare($sqlPlaces);
        $stmtPlaces->execute([':id_creneau' => $id_creneau]);
        $places = $stmtPlaces->fetch()['places_restantes'];
        
        if ($places < $nb_personnes) {
            // Pas assez de places : annuler la transaction
            $pdo->rollBack();
            $erreurs[] = "Plus assez de places.";
        } else {
            // Étape 7 : Génération du token
            $token = bin2hex(random_bytes(32));
            
            // Étape 8 : INSERT inscription
            $sqlInscription = "
                INSERT INTO inscriptions 
                (nom, prenom, email, id_categorie, nb_personnes, buffet_jeudi, token, email_referent)
                VALUES 
                (:nom, :prenom, :email, :id_categorie, :nb_personnes, :buffet_jeudi, :token, :email_referent)
            ";
            $stmtIns = $pdo->prepare($sqlInscription);
            $stmtIns->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':email' => $email,
                ':id_categorie' => $id_categorie,
                ':nb_personnes' => $nb_personnes,
                ':buffet_jeudi' => $buffet_jeudi,
                ':token' => $token,
                ':email_referent' => EMAIL_REFERENT
            ]);
            
            // Récupérer l'ID auto-généré
            $idInscription = $pdo->lastInsertId();
            
            // Étape 9 : INSERT réservation
            $sqlReservation = "
                INSERT INTO reservations (id_inscription, id_creneau)
                VALUES (:id_inscription, :id_creneau)
            ";
            $stmtRes = $pdo->prepare($sqlReservation);
            $stmtRes->execute([
                ':id_inscription' => $idInscription,
                ':id_creneau' => $id_creneau
            ]);
            
            // Étape 10 : Validation de la transaction
            $pdo->commit();
            
            // Étape 11 : Email (simulation)
            simulerEnvoiMail($email, "Confirmation", "...");
            
            // Étape 12 : Redirection
            redirect('confirmation.php?token=' . urlencode($token));
        }
        
    } catch (PDOException $e) {
        // Erreur : annuler la transaction
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $erreurs[] = "Erreur lors de l'inscription.";
    }
}
```

### 2.3 Pourquoi une transaction ?

Sans transaction, si l'INSERT dans `reservations` échoue après l'INSERT dans `inscriptions`, on aurait une inscription orpheline (sans réservation).

**Avec transaction :**
- Soit les 2 INSERT réussissent → `commit()`
- Soit un échoue → `rollBack()` annule tout

---

## 3. READ - Lecture d'une réservation

### 3.1 Requête avec jointures

Pour afficher le récapitulatif complet, on doit joindre plusieurs tables :

```php
// includes/functions.php
function getReservationComplete(string $token): ?array {
    $pdo = getPDO();
    
    $sql = "
        SELECT 
            i.*,                          -- Toutes les colonnes d'inscriptions
            c.nom AS categorie_nom,       -- Nom de la catégorie
            c.buffet_actif,               -- Droit au buffet
            cr.id_creneau,                -- ID du créneau
            cr.date_creneau,              -- Date
            cr.heure,                     -- Heure
            s.id_salle,                   -- ID de la salle
            s.numero AS salle_numero,     -- Numéro de la salle
            s.nom AS salle_nom            -- Nom de la salle
        FROM inscriptions i
        JOIN categories c ON i.id_categorie = c.id_categorie
        JOIN reservations r ON i.id_inscription = r.id_inscription
        JOIN creneaux cr ON r.id_creneau = cr.id_creneau
        JOIN salles s ON cr.id_salle = s.id_salle
        WHERE i.token = :token
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':token' => $token]);
    
    $result = $stmt->fetch();
    return $result ?: null;  // null si non trouvé
}
```

### 3.2 Schéma des jointures

```
inscriptions ──┬── categories (i.id_categorie = c.id_categorie)
               │
               └── reservations ──── creneaux ──── salles
                   (i.id = r.id)     (r.id = cr.id)  (cr.id = s.id)
```

### 3.3 Utilisation dans confirmation.php

```php
$token = $_GET['token'] ?? '';
$reservation = getReservationComplete($token);

if (!$reservation) {
    redirectAvecMessage('index.php', 'Réservation non trouvée.', 'error');
}

// Affichage
echo "Nom : " . sanitize($reservation['nom']);
echo "Salle : " . sanitize($reservation['salle_nom']);
echo "Date : " . formaterDate($reservation['date_creneau']);
```

---

## 4. UPDATE - Modification d'une réservation

### 4.1 Contraintes à gérer

1. Le visiteur peut changer de créneau → vérifier la jauge du nouveau créneau
2. Il peut augmenter le nombre de personnes → vérifier les places
3. Il peut cocher le buffet → vérifier si sa catégorie l'autorise

### 4.2 Calcul du delta de places

Si on change de créneau :
- On "libère" les places de l'ancien créneau
- On "prend" les places du nouveau créneau

```php
// Ancien créneau : ID=5, nb_personnes=3
// Nouveau créneau : ID=8, nb_personnes=4

// Places disponibles sur créneau 8 avant modif
$placesDisponibles = placesRestantes(8);

// Si c'est le même créneau, on récupère nos places
if ($nouveauCreneau == $ancienCreneau) {
    $placesDisponibles += $ancienNbPersonnes;
}

// Vérification
if ($placesDisponibles >= $nouveauNbPersonnes) {
    // OK, on peut modifier
} else {
    // Pas assez de places
}
```

### 4.3 Code de mise à jour

```php
// ma-reservation.php - Action modifier

$pdo->beginTransaction();

try {
    // 1. Mise à jour de l'inscription
    $sqlUpdate = "
        UPDATE inscriptions SET
            nom = :nom,
            prenom = :prenom,
            id_categorie = :id_categorie,
            nb_personnes = :nb_personnes,
            buffet_jeudi = :buffet_jeudi
        WHERE token = :token
    ";
    $stmt = $pdo->prepare($sqlUpdate);
    $stmt->execute([
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':id_categorie' => $id_categorie,
        ':nb_personnes' => $nb_personnes,
        ':buffet_jeudi' => $buffet_jeudi,
        ':token' => $token
    ]);
    
    // 2. Si changement de créneau
    if ($nouveauCreneau != $ancienCreneau) {
        $sqlReservation = "
            UPDATE reservations SET id_creneau = :id_creneau
            WHERE id_inscription = :id_inscription
        ";
        $stmt = $pdo->prepare($sqlReservation);
        $stmt->execute([
            ':id_creneau' => $nouveauCreneau,
            ':id_inscription' => $idInscription
        ]);
    }
    
    $pdo->commit();
    $succes = "Modification enregistrée.";
    
} catch (PDOException $e) {
    $pdo->rollBack();
    $erreurs[] = "Erreur lors de la modification.";
}
```

---

## 5. DELETE - Suppression d'une réservation

### 5.1 Grâce à ON DELETE CASCADE

La suppression est simplifiée car les clés étrangères sont configurées avec `ON DELETE CASCADE` :

```php
// ma-reservation.php - Action supprimer

$stmt = $pdo->prepare("DELETE FROM inscriptions WHERE token = :token");
$stmt->execute([':token' => $token]);

// Les lignes correspondantes dans 'reservations' sont 
// automatiquement supprimées par MySQL !
```

### 5.2 Confirmation côté client

```html
<button type="submit" 
        data-confirm="Êtes-vous sûr de vouloir supprimer votre réservation ?">
    Supprimer
</button>
```

```javascript
// main.js
document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', function(e) {
        if (!confirm(this.dataset.confirm)) {
            e.preventDefault();  // Annule la soumission
        }
    });
});
```

---

## 6. Récapitulatif CRUD

| Opération | Fichier | Méthode | SQL | Particularité |
|-----------|---------|---------|-----|---------------|
| Create | inscription.php | POST | INSERT | Transaction + jauge |
| Read | confirmation.php | GET | SELECT + JOIN | Token comme identifiant |
| Update | ma-reservation.php | POST | UPDATE | Calcul delta places |
| Delete | ma-reservation.php | POST | DELETE | CASCADE automatique |

---

## 7. Points clés à retenir pour l'oral

1. **CRUD** = Create, Read, Update, Delete (les 4 opérations de base)
2. **Transaction** = garantit que plusieurs opérations réussissent ou échouent ensemble
3. **Jointures** = permettent de récupérer des données de plusieurs tables liées
4. **ON DELETE CASCADE** = supprime automatiquement les données liées
5. **Token** = identifiant unique permettant l'accès sans authentification

---

## 8. Questions fréquentes du jury

**Q : Pourquoi utiliser une transaction pour l'inscription ?**
> R : Pour garantir l'atomicité : si l'INSERT dans `reservations` échoue, on annule aussi l'INSERT dans `inscriptions`. Sinon on aurait une inscription sans réservation.

**Q : Comment le visiteur modifie-t-il sa réservation sans compte ?**
> R : Grâce au token unique de 64 caractères généré à l'inscription. Ce token est comme un "mot de passe jetable" propre à chaque réservation.

**Q : Que se passe-t-il si deux personnes s'inscrivent en même temps sur le dernier créneau ?**
> R : La transaction avec vérification atomique évite le surbooking. Une seule inscription réussira, l'autre recevra un message d'erreur.

**Q : Pourquoi vérifier le buffet côté serveur si c'est déjà fait côté client ?**
> R : Le JavaScript peut être contourné. Un utilisateur malveillant pourrait envoyer directement une requête POST avec `buffet_jeudi=1`. La validation serveur est obligatoire.
