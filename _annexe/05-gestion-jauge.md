# 05 - Gestion de la jauge

## Objectifs pédagogiques

À la fin de ce chapitre, vous serez capable de :
- Comprendre le problème des race conditions
- Expliquer pourquoi SELECT puis INSERT est insuffisant
- Implémenter une vérification atomique avec transaction
- Calculer les places restantes avec une requête SQL

---

## 1. Le problème : la jauge

### 1.1 Règle métier

> Chaque créneau horaire peut accueillir **maximum 12 personnes**.
> Le champ `nb_personnes` d'une inscription compte dans la jauge.

**Exemple :**
- Créneau "Jeudi 15h00 - Salle 002" : capacité = 12
- Jean s'inscrit avec 3 personnes → reste 9 places
- Marie s'inscrit avec 5 personnes → reste 4 places
- Paul veut s'inscrire avec 6 personnes → **REFUSÉ** (seulement 4 places)

### 1.2 Calcul des places restantes

```
places_restantes = capacité_totale - somme(nb_personnes de toutes les réservations)
                 = 12 - SUM(nb_personnes)
```

---

## 2. Le danger : race condition

### 2.1 Qu'est-ce qu'une race condition ?

Une **race condition** survient quand deux processus accèdent simultanément aux mêmes données et produisent un résultat incorrect.

### 2.2 Scénario problématique

**Code naïf (DANGEREUX) :**
```php
// Étape 1 : Vérifier les places
$places = placesRestantes($id_creneau);  // Retourne 2

// Étape 2 : Si OK, insérer
if ($places >= $nb_personnes) {  // 2 >= 2 → OK
    // INSERT...
}
```

**Problème : deux utilisateurs simultanés**

| Temps | Utilisateur A (2 pers.) | Utilisateur B (2 pers.) | Places réelles |
|-------|-------------------------|-------------------------|----------------|
| T1 | SELECT → 2 places | | 2 |
| T2 | | SELECT → 2 places | 2 |
| T3 | 2 >= 2 → OK | | 2 |
| T4 | | 2 >= 2 → OK | 2 |
| T5 | INSERT (2 pers.) | | **0** |
| T6 | | INSERT (2 pers.) | **-2** ❌ |

**Résultat : SURBOOKING !** Le créneau a 4 personnes au lieu de 2 max.

### 2.3 Pourquoi ça arrive ?

Entre le SELECT (vérification) et l'INSERT (réservation), un autre utilisateur peut aussi faire son INSERT.

```
Temps ──────────────────────────────────────────────────────────→

User A:  ┌─SELECT─┐        ┌─INSERT─┐
User B:           ┌─SELECT─┐        ┌─INSERT─┐
                  ↑
                  Les deux voient 2 places libres !
```

---

## 3. La solution : transaction avec vérification atomique

### 3.1 Principe

On regroupe la vérification ET l'insertion dans une **transaction** qui s'exécute de manière **atomique** (indivisible).

```php
$pdo->beginTransaction();

try {
    // 1. Vérification (avec verrouillage implicite)
    $places = verifierPlacesAtomique($id_creneau);
    
    if ($places < $nb_personnes) {
        $pdo->rollBack();
        throw new Exception("Plus de places");
    }
    
    // 2. Insertion
    // INSERT...
    
    // 3. Validation
    $pdo->commit();
    
} catch (Exception $e) {
    $pdo->rollBack();
}
```

### 3.2 Pourquoi ça fonctionne ?

Avec InnoDB (moteur MySQL), les transactions avec SELECT + INSERT créent un **verrou implicite** sur les lignes concernées.

Le deuxième utilisateur doit **attendre** que le premier termine avant de pouvoir lire les données.

```
Temps ──────────────────────────────────────────────────────────→

User A:  ┌──────── TRANSACTION ────────┐
         │ SELECT │ CHECK │ INSERT     │
                                       │
User B:                                └─ ATTEND ─┐
                                                  ┌──── TRANSACTION ────┐
                                                  │ SELECT (1 place) → ❌ │
```

---

## 4. Implémentation dans le projet

### 4.1 Requête SQL de calcul

```sql
SELECT 
    c.places_total - COALESCE(SUM(i.nb_personnes), 0) AS places_restantes
FROM creneaux c
LEFT JOIN reservations r ON c.id_creneau = r.id_creneau
LEFT JOIN inscriptions i ON r.id_inscription = i.id_inscription
WHERE c.id_creneau = :id_creneau
GROUP BY c.id_creneau
```

**Explication :**
- `c.places_total` : capacité maximale (12)
- `SUM(i.nb_personnes)` : total des personnes déjà inscrites
- `COALESCE(..., 0)` : retourne 0 si aucune inscription (évite NULL)
- `LEFT JOIN` : inclut les créneaux même sans réservation

### 4.2 Fonction de vérification

```php
// includes/functions.php

function placesRestantes(int $id_creneau): int {
    $pdo = getPDO();
    
    $sql = "
        SELECT 
            c.places_total - COALESCE(
                (SELECT SUM(i.nb_personnes) 
                 FROM reservations r 
                 JOIN inscriptions i ON r.id_inscription = i.id_inscription 
                 WHERE r.id_creneau = c.id_creneau
                ), 0
            ) AS places_restantes
        FROM creneaux c
        WHERE c.id_creneau = :id_creneau
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id_creneau', $id_creneau, PDO::PARAM_INT);
    $stmt->execute();
    
    $resultat = $stmt->fetch();
    return $resultat ? max(0, (int)$resultat['places_restantes']) : 0;
}
```

### 4.3 Code complet d'inscription

```php
// inscription.php

try {
    // Début de la transaction
    $pdo->beginTransaction();
    
    // Calcul atomique des places
    $sqlPlaces = "
        SELECT c.places_total - COALESCE(SUM(i.nb_personnes), 0) AS places_restantes
        FROM creneaux c
        LEFT JOIN reservations r ON c.id_creneau = r.id_creneau
        LEFT JOIN inscriptions i ON r.id_inscription = i.id_inscription
        WHERE c.id_creneau = :id_creneau
        GROUP BY c.id_creneau
    ";
    
    $stmtPlaces = $pdo->prepare($sqlPlaces);
    $stmtPlaces->bindValue(':id_creneau', $id_creneau, PDO::PARAM_INT);
    $stmtPlaces->execute();
    $resultat = $stmtPlaces->fetch();
    
    $placesRestantes = $resultat ? (int)$resultat['places_restantes'] : JAUGE_MAX;
    
    // Vérification
    if ($placesRestantes < $nb_personnes) {
        $pdo->rollBack();
        $erreurs[] = "Il ne reste que {$placesRestantes} place(s).";
    } else {
        // Token unique
        $token = bin2hex(random_bytes(32));
        
        // INSERT inscription
        $sqlIns = "INSERT INTO inscriptions (...) VALUES (...)";
        $pdo->prepare($sqlIns)->execute([...]);
        $idInscription = $pdo->lastInsertId();
        
        // INSERT réservation
        $sqlRes = "INSERT INTO reservations (...) VALUES (...)";
        $pdo->prepare($sqlRes)->execute([...]);
        
        // Tout est OK : valider
        $pdo->commit();
        
        redirect('confirmation.php?token=' . $token);
    }
    
} catch (PDOException $e) {
    // En cas d'erreur : annuler
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $erreurs[] = "Erreur technique.";
}
```

---

## 5. API JSON pour l'affichage temps réel

### 5.1 Endpoint api/creneaux.php

```php
// api/creneaux.php

header('Content-Type: application/json');

$id_salle = intval($_GET['id_salle'] ?? 0);

$sql = "
    SELECT 
        c.id_creneau,
        c.date_creneau,
        c.heure,
        c.places_total,
        (c.places_total - COALESCE(SUM(i.nb_personnes), 0)) AS places_restantes
    FROM creneaux c
    LEFT JOIN reservations r ON c.id_creneau = r.id_creneau
    LEFT JOIN inscriptions i ON r.id_inscription = i.id_inscription
    WHERE c.id_salle = :id_salle
    GROUP BY c.id_creneau
    ORDER BY c.date_creneau, c.heure
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':id_salle' => $id_salle]);
$creneaux = $stmt->fetchAll();

echo json_encode($creneaux);
```

### 5.2 Réponse JSON

```json
[
    {
        "id_creneau": 1,
        "date_creneau": "2026-06-18",
        "heure": "15:00:00",
        "places_total": 12,
        "places_restantes": 8
    },
    {
        "id_creneau": 2,
        "date_creneau": "2026-06-18",
        "heure": "15:30:00",
        "places_total": 12,
        "places_restantes": 0
    }
]
```

### 5.3 Utilisation JavaScript

```javascript
// main.js

async function chargerCreneaux(idSalle) {
    const response = await fetch(`api/creneaux.php?id_salle=${idSalle}`);
    const creneaux = await response.json();
    
    const select = document.getElementById('id_creneau');
    select.innerHTML = '<option value="">-- Choisir --</option>';
    
    creneaux.forEach(creneau => {
        const option = document.createElement('option');
        option.value = creneau.id_creneau;
        
        if (creneau.places_restantes <= 0) {
            option.textContent = `${creneau.heure} - COMPLET`;
            option.disabled = true;
        } else {
            option.textContent = `${creneau.heure} - ${creneau.places_restantes} places`;
        }
        
        select.appendChild(option);
    });
}
```

---

## 6. Indicateur visuel dans le dashboard

### 6.1 Jauge colorée

```php
// admin/dashboard.php

<?php foreach ($jauges as $jauge): 
    $pourcentage = ($jauge['places_occupees'] / $jauge['places_total']) * 100;
    $estComplet = $jauge['places_restantes'] <= 0;
?>
<div class="jauge-item <?php echo $estComplet ? 'complet' : ''; ?>">
    <div class="jauge-bar">
        <div class="jauge-fill <?php echo $estComplet ? 'complet' : ''; ?>" 
             style="width: <?php echo min(100, $pourcentage); ?>%">
        </div>
    </div>
    <span class="jauge-text">
        <?php echo $estComplet ? 'COMPLET' : $jauge['places_restantes'] . '/' . $jauge['places_total']; ?>
    </span>
</div>
<?php endforeach; ?>
```

### 6.2 CSS de la jauge

```css
.jauge-bar {
    width: 100%;
    height: 8px;
    background: #e0e0e0;
    border-radius: 4px;
}

.jauge-fill {
    height: 100%;
    background: #00bbaa;  /* Cyan foncé */
    border-radius: 4px;
    transition: width 0.3s;
}

.jauge-fill.complet {
    background: #af0000;  /* Rouge */
}
```

---

## 7. Points clés à retenir pour l'oral

1. **Race condition** = deux opérations simultanées qui corrompent les données
2. **Transaction** = bloc d'opérations atomique (tout ou rien)
3. **La vérification doit être dans la transaction**, pas avant
4. **Formule** : `places_restantes = capacité - SUM(nb_personnes)`
5. **COALESCE** évite les erreurs quand il n'y a pas de réservation

---

## 8. Questions fréquentes du jury

**Q : Que se passe-t-il si deux personnes cliquent vraiment en même temps ?**
> R : La première transaction qui commence verrouille les lignes. La seconde attend. Quand elle peut enfin lire, les données sont à jour et elle voit qu'il n'y a plus de place.

**Q : Pourquoi ne pas simplement verrouiller toute la table ?**
> R : Ce serait trop restrictif. Avec notre approche, deux personnes peuvent s'inscrire à des créneaux différents en parallèle.

**Q : La double vérification (JavaScript + PHP) est-elle redondante ?**
> R : Non. Le JavaScript améliore l'expérience utilisateur (feedback immédiat), mais seule la vérification PHP est fiable car le JS peut être contourné.

**Q : Pourquoi `max(0, ...)` dans la fonction placesRestantes() ?**
> R : Pour éviter de retourner un nombre négatif si, par erreur, il y avait plus de réservations que de places (situation anormale mais possible).
