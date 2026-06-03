# 02 - Base de données E-LLUSION

## Objectifs pédagogiques

À la fin de ce chapitre, vous serez capable de :
- Lire et comprendre un schéma de base de données
- Expliquer les notions de clé primaire, clé étrangère et cardinalité
- Justifier l'utilisation d'une table pivot (association)
- Comprendre l'intérêt de `ON DELETE CASCADE`

---

## 1. Modèle Conceptuel de Données (MCD)

### 1.1 Les entités du projet

Une **entité** représente un "objet" du monde réel qu'on veut stocker.

| Entité | Description | Exemples |
|--------|-------------|----------|
| **Salle** | Une salle de l'exposition | Salle Immersive, Salle Interactive |
| **Oeuvre** | Une œuvre exposée | "Horizon Infini", "Miroir de l'Âme" |
| **Créneau** | Un horaire de visite | Jeudi 18/06 à 15h00 |
| **Catégorie** | Type de visiteur | Enseignant, Étudiant MMI |
| **Inscription** | Une réservation | Jean Dupont, 3 personnes |
| **Utilisateur** | Un admin/référent | admin@ellusion.fr |

### 1.2 Les associations (relations)

```
┌─────────────┐         ┌─────────────┐
│    SALLE    │ 1 ───── │   OEUVRE    │
└─────────────┘    *    └─────────────┘
       │                      
       │ 1                    
       │                      
       │ *                    
┌─────────────┐         ┌─────────────┐
│   CRENEAU   │         │  CATEGORIE  │
└─────────────┘         └─────────────┘
       │                      │
       │ *                    │ 1
       │                      │
       │ *                    │ *
┌─────────────────────────────────────┐
│           INSCRIPTION               │
└─────────────────────────────────────┘
```

### 1.3 Lecture des cardinalités

| Relation | Cardinalité | Signification |
|----------|-------------|---------------|
| Salle → Oeuvre | 1,N | Une salle contient plusieurs œuvres |
| Salle → Créneau | 1,N | Une salle a plusieurs créneaux |
| Catégorie → Inscription | 1,N | Une catégorie concerne plusieurs inscriptions |
| Créneau → Inscription | N,M | Un créneau peut avoir plusieurs inscriptions, une inscription peut (potentiellement) concerner plusieurs créneaux |

---

## 2. Modèle Logique de Données (MLD)

### 2.1 Transformation en tables

Le MLD traduit le MCD en tables SQL avec :
- Des **clés primaires** (PK) pour identifier chaque ligne
- Des **clés étrangères** (FK) pour les relations
- Une **table pivot** pour les relations N,M

### 2.2 Schéma des tables

```sql
-- Table SALLES
salles (
    id_salle INT PRIMARY KEY AUTO_INCREMENT,
    numero VARCHAR(10) UNIQUE,
    nom VARCHAR(100),
    description TEXT,
    capacite_max INT DEFAULT 12,
    image VARCHAR(255)
)

-- Table OEUVRES (clé étrangère vers salles)
oeuvres (
    id_oeuvre INT PRIMARY KEY AUTO_INCREMENT,
    id_salle INT FOREIGN KEY → salles(id_salle),
    titre VARCHAR(150),
    description TEXT,
    artiste VARCHAR(100),
    image VARCHAR(255)
)

-- Table CRENEAUX (clé étrangère vers salles)
creneaux (
    id_creneau INT PRIMARY KEY AUTO_INCREMENT,
    id_salle INT FOREIGN KEY → salles(id_salle),
    date_creneau DATE,
    heure TIME,
    places_total INT DEFAULT 12
)

-- Table CATEGORIES
categories (
    id_categorie INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) UNIQUE,
    buffet_actif TINYINT(1) DEFAULT 0
)

-- Table INSCRIPTIONS (clé étrangère vers categories)
inscriptions (
    id_inscription INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    email VARCHAR(255),
    id_categorie INT FOREIGN KEY → categories(id_categorie),
    nb_personnes INT DEFAULT 1,
    buffet_jeudi TINYINT(1) DEFAULT 0,
    token VARCHAR(64) UNIQUE,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    email_referent VARCHAR(255)
)

-- Table RESERVATIONS (table pivot : clés étrangères vers inscriptions et creneaux)
reservations (
    id_reservation INT PRIMARY KEY AUTO_INCREMENT,
    id_inscription INT FOREIGN KEY → inscriptions(id_inscription),
    id_creneau INT FOREIGN KEY → creneaux(id_creneau)
)

-- Table UTILISATEURS
utilisateurs (
    id_utilisateur INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE,
    mot_de_passe VARCHAR(255),
    nom VARCHAR(100),
    prenom VARCHAR(100),
    role ENUM('admin', 'referent'),
    agence VARCHAR(100)
)
```

---

## 3. La table pivot `reservations`

### 3.1 Pourquoi une table pivot ?

La relation entre `inscriptions` et `creneaux` est de type **N,M** (plusieurs-à-plusieurs) :
- Une inscription peut réserver plusieurs créneaux (évolution future)
- Un créneau peut avoir plusieurs inscriptions

**Sans table pivot (impossible en SQL) :**
```
inscriptions.id_creneau = ???  // Comment stocker plusieurs créneaux ?
```

**Avec table pivot :**
```
inscriptions(id_inscription, ...)
reservations(id_inscription, id_creneau)  // Fait le lien !
creneaux(id_creneau, ...)
```

### 3.2 Exemple concret

Jean Dupont (inscription #1) réserve le créneau #5 :

| Table | Données |
|-------|---------|
| `inscriptions` | id_inscription=1, nom="Dupont", prenom="Jean", ... |
| `reservations` | id_inscription=1, id_creneau=5 |
| `creneaux` | id_creneau=5, date="2026-06-18", heure="15:00" |

### 3.3 Récupérer les données liées (JOIN)

```sql
-- Récupérer la réservation complète de Jean Dupont
SELECT 
    i.nom, i.prenom,
    c.date_creneau, c.heure,
    s.nom AS salle
FROM inscriptions i
JOIN reservations r ON i.id_inscription = r.id_inscription
JOIN creneaux c ON r.id_creneau = c.id_creneau
JOIN salles s ON c.id_salle = s.id_salle
WHERE i.id_inscription = 1;
```

**Résultat :**
```
+--------+---------+--------------+----------+------------------+
| nom    | prenom  | date_creneau | heure    | salle            |
+--------+---------+--------------+----------+------------------+
| Dupont | Jean    | 2026-06-18   | 15:00:00 | Salle Immersive  |
+--------+---------+--------------+----------+------------------+
```

---

## 4. ON DELETE CASCADE

### 4.1 Problème sans CASCADE

Si on supprime une inscription sans précaution :
```sql
DELETE FROM inscriptions WHERE id_inscription = 1;
```

**Erreur !** La table `reservations` contient encore une ligne avec `id_inscription = 1`.
C'est une **violation de contrainte d'intégrité référentielle**.

### 4.2 Solution : ON DELETE CASCADE

```sql
CREATE TABLE reservations (
    -- ...
    CONSTRAINT fk_reservation_inscription 
        FOREIGN KEY (id_inscription) 
        REFERENCES inscriptions(id_inscription) 
        ON DELETE CASCADE  -- ← Suppression en cascade
);
```

**Comportement :**
Quand on supprime une inscription, toutes les réservations associées sont **automatiquement supprimées**.

### 4.3 Avantages

1. **Intégrité garantie** : pas de données orphelines
2. **Code simplifié** : pas besoin de supprimer manuellement les réservations
3. **Atomicité** : tout est supprimé ou rien

---

## 5. Les données initiales

### 5.1 Salles (4 salles)

```sql
INSERT INTO salles (numero, nom, description) VALUES
('002', 'Salle Immersive', '...'),
('001', 'Salle Interactive', '...'),
('005', 'Salle Contemplative', '...'),
('021', 'Salle Expérimentale', '...');
```

### 5.2 Œuvres (14 œuvres)

- Salle 002 : 3 œuvres
- Salle 001 : 3 œuvres
- Salle 005 : 4 œuvres
- Salle 020 : **2 œuvres**

### 5.3 Créneaux (48 créneaux)

**Jeudi 18/06/2026 :**
- 15h00, 15h30, 16h00, 16h30, 17h00, 17h30, 18h00, 19h00, 19h30, 20h00 (10 créneaux)

**Vendredi 19/06/2026 :**
- 9h30, 10h00, 10h30, 11h00 (4 créneaux)

**Total : 12 horaires × 4 salles = 48 créneaux**

### 5.4 Catégories (5 catégories)

| Catégorie | Buffet autorisé |
|-----------|-----------------|
| Enseignant·e | Oui |
| Étudiant·e MMI 2 ou 3 | **Non** |
| Personnel USMB | Oui |
| Professionnels/partenaires | Oui |
| Visiteur·se extérieur | Oui |

---

## 6. Index pour les performances

### 6.1 Pourquoi des index ?

Un index accélère les recherches, comme un index dans un livre.

```sql
-- Sans index : MySQL parcourt TOUTES les lignes
SELECT * FROM inscriptions WHERE token = 'abc123...';

-- Avec index : MySQL trouve directement la bonne ligne
CREATE INDEX idx_inscriptions_token ON inscriptions(token);
```

### 6.2 Index créés dans le projet

```sql
CREATE INDEX idx_inscriptions_token ON inscriptions(token);
CREATE INDEX idx_inscriptions_email ON inscriptions(email);
CREATE INDEX idx_creneaux_date ON creneaux(date_creneau);
CREATE INDEX idx_reservations_creneau ON reservations(id_creneau);
```

---

## 7. Points clés à retenir pour l'oral

1. **Clé primaire** : identifiant unique de chaque ligne (AUTO_INCREMENT)
2. **Clé étrangère** : référence vers une autre table (intégrité référentielle)
3. **Table pivot** : nécessaire pour les relations N,M (plusieurs-à-plusieurs)
4. **ON DELETE CASCADE** : suppression automatique des données liées
5. **Index** : accélèrent les recherches sur des colonnes fréquemment utilisées

---

## 8. Questions fréquentes du jury

**Q : Pourquoi `id_salle` est dans `creneaux` et pas l'inverse ?**
> R : Car un créneau appartient à UNE salle (relation 1,N). C'est la table du côté "N" qui porte la clé étrangère.

**Q : Pourquoi ne pas stocker directement `id_creneau` dans `inscriptions` ?**
> R : Pour permettre une évolution future (réserver plusieurs créneaux). La table pivot offre plus de flexibilité.

**Q : Que se passe-t-il si on supprime une salle ?**
> R : Grâce à `ON DELETE CASCADE`, tous les créneaux de cette salle sont supprimés, puis toutes les réservations de ces créneaux.

**Q : Pourquoi `token` est UNIQUE ?**
> R : Chaque inscription doit avoir un token différent pour que le visiteur puisse accéder uniquement à SA réservation.
