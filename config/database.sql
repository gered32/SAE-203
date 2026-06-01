-- ============================================================================
-- SAE203 E-LLUSION - Script de création de la base de données
-- BUT MMI 1ère année - Compétence "Développer"
-- ============================================================================
-- Ce fichier contient la structure complète de la base de données ainsi que
-- les données initiales nécessaires au fonctionnement du site.
-- 
-- INSTRUCTIONS D'UTILISATION :
-- 1. Ouvrir phpMyAdmin
-- 2. Créer une nouvelle base de données nommée "sae203_ellusion"
-- 3. Sélectionner cette base de données
-- 4. Aller dans l'onglet "Importer"
-- 5. Sélectionner ce fichier et exécuter
-- ============================================================================

-- Définir le charset par défaut
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ============================================================================
-- CRÉATION DES TABLES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table : salles
-- Description : Contient les 4 salles de l'exposition E-LLUSION
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS salles (
    id_salle INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(10) NOT NULL UNIQUE COMMENT 'Numéro de la salle (ex: 002, 001, 005, 021)',
    nom VARCHAR(100) NOT NULL COMMENT 'Nom descriptif de la salle',
    description TEXT COMMENT 'Description détaillée de la salle',
    capacite_max INT NOT NULL DEFAULT 12 COMMENT 'Capacité maximale par créneau',
    image VARCHAR(255) DEFAULT 'placeholder.jpg' COMMENT 'Chemin vers image de la salle'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table : oeuvres
-- Description : Œuvres exposées dans chaque salle (4 par salle, sauf 021 = 2)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS oeuvres (
    id_oeuvre INT AUTO_INCREMENT PRIMARY KEY,
    id_salle INT NOT NULL COMMENT 'Clé étrangère vers la salle',
    titre VARCHAR(150) NOT NULL COMMENT 'Titre de oeuvre',
    description TEXT COMMENT 'Description de oeuvre',
    artiste VARCHAR(100) NOT NULL COMMENT 'Nom de artiste',
    image VARCHAR(255) DEFAULT 'placeholder.jpg' COMMENT 'Chemin vers image de oeuvre',
    CONSTRAINT fk_oeuvre_salle FOREIGN KEY (id_salle) 
        REFERENCES salles(id_salle) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table : creneaux
-- Description : Créneaux horaires disponibles pour chaque salle
-- 14 créneaux × 4 salles = 56 créneaux au total
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS creneaux (
    id_creneau INT AUTO_INCREMENT PRIMARY KEY,
    id_salle INT NOT NULL COMMENT 'Clé étrangère vers la salle',
    date_creneau DATE NOT NULL COMMENT 'Date du créneau',
    heure TIME NOT NULL COMMENT 'Heure de début du créneau',
    places_total INT NOT NULL DEFAULT 12 COMMENT 'Nombre total de places disponibles',
    CONSTRAINT fk_creneau_salle FOREIGN KEY (id_salle) 
        REFERENCES salles(id_salle) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_creneau (id_salle, date_creneau, heure)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table : categories
-- Description : Catégories de visiteurs avec droit au buffet ou non
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id_categorie INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE COMMENT 'Nom de la catégorie',
    buffet_actif TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = accès buffet autorisé, 0 = non autorisé'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table : inscriptions
-- Description : Inscriptions des visiteurs à exposition
-- Le token permet de modifier/supprimer sa réservation sans compte
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS inscriptions (
    id_inscription INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL COMMENT 'Nom du visiteur',
    prenom VARCHAR(100) NOT NULL COMMENT 'Prénom du visiteur',
    email VARCHAR(255) NOT NULL COMMENT 'Email du visiteur',
    id_categorie INT NOT NULL COMMENT 'Clé étrangère vers la catégorie',
    nb_personnes INT NOT NULL DEFAULT 1 COMMENT 'Nombre de personnes (1-12)',
    buffet_jeudi TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = participe au buffet, 0 = non',
    token VARCHAR(64) NOT NULL UNIQUE COMMENT 'Token unique pour modification/suppression',
    date_inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date et heure de inscription',
    email_referent VARCHAR(255) COMMENT 'Email du référent du projet',
    CONSTRAINT fk_inscription_categorie FOREIGN KEY (id_categorie) 
        REFERENCES categories(id_categorie) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_nb_personnes CHECK (nb_personnes >= 1 AND nb_personnes <= 12)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table : reservations (table pivot/association)
-- Description : Lie les inscriptions aux créneaux choisis
-- ON DELETE CASCADE : si inscription est supprimée, les réservations aussi
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reservations (
    id_reservation INT AUTO_INCREMENT PRIMARY KEY,
    id_inscription INT NOT NULL COMMENT 'Clé étrangère vers inscription',
    id_creneau INT NOT NULL COMMENT 'Clé étrangère vers le créneau',
    CONSTRAINT fk_reservation_inscription FOREIGN KEY (id_inscription) 
        REFERENCES inscriptions(id_inscription) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_reservation_creneau FOREIGN KEY (id_creneau) 
        REFERENCES creneaux(id_creneau) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_inscription_creneau (id_inscription, id_creneau)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table : utilisateurs
-- Description : Comptes administrateurs et référents pour espace admin
-- Le mot de passe est hashé avec password_hash() de PHP
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS utilisateurs (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE COMMENT 'Email de connexion',
    mot_de_passe VARCHAR(255) NOT NULL COMMENT 'Mot de passe hashé (password_hash)',
    nom VARCHAR(100) NOT NULL COMMENT 'Nom de utilisateur',
    prenom VARCHAR(100) NOT NULL COMMENT 'Prénom de utilisateur',
    role ENUM('admin', 'referent') NOT NULL DEFAULT 'referent' COMMENT 'Rôle de utilisateur',
    agence VARCHAR(100) COMMENT 'Agence ou département (optionnel)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- INSERTION DES DONNÉES INITIALES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Insertion des 4 salles de exposition
-- ----------------------------------------------------------------------------
INSERT INTO salles (numero, nom, description, image) VALUES
('002', 'Salle Immersive', 
 'Plongez dans un univers sensoriel unique où la lumière, le son et les projections créent une expérience immersive totale. Cette salle vous transporte dans des mondes imaginaires grâce à des installations audiovisuelles de pointe.', 
 'salle-002.jpg'),
('001', 'Salle Interactive', 
 'Devenez acteur de art dans cet espace où chaque mouvement, chaque geste influence les oeuvres qui vous entourent. Capteurs et écrans réactifs répondent à votre présence pour créer une expérience personnalisée et ludique.', 
 'salle-001.jpg'),
('005', 'Salle Contemplative', 
 'Un havre de paix dédié à la réflexion et à la méditation visuelle. Les oeuvres exposées invitent à la contemplation et questionnent notre rapport au temps, à espace et à la technologie dans notre quotidien.', 
 'salle-005.jpg'),
('021', 'Salle Expérimentale', 
 'Laboratoire créatif où les artistes repoussent les limites de art numérique. Découvrez des prototypes et des oeuvres en cours de développement qui préfigurent art de demain.', 
 'salle-021.jpg');

-- ----------------------------------------------------------------------------
-- Insertion des oeuvres (4 par salle, sauf salle 021 qui en a 2)
-- ----------------------------------------------------------------------------

-- Oeuvres de la Salle 002 (Immersive) - 4 oeuvres
INSERT INTO oeuvres (id_salle, titre, description, artiste, image) VALUES
((SELECT id_salle FROM salles WHERE numero = '002'), 
 'Horizon Infini', 
 'Une projection à 360° qui simule un voyage à travers des paysages oniriques en constante évolution. Les couleurs et les formes se transforment au rythme d une musique ambiante composée spécialement pour oeuvre.',
 'Marie Dubois', 'oeuvre-001.jpg'),
((SELECT id_salle FROM salles WHERE numero = '002'), 
 'Échos Lumineux', 
 'Installation de tubes LED synchronisés créant des vagues de lumière qui traversent espace. Le visiteur est enveloppé dans un ballet lumineux hypnotique.',
 'Thomas Laurent', 'oeuvre-002.jpg'),
((SELECT id_salle FROM salles WHERE numero = '002'), 
 'Membrane Sonore', 
 'Un dôme acoustique où chaque son est spatialisé pour créer illusion d être au coeur d un organisme vivant. Les battements cardiaques et respirations deviennent une symphonie immersive.',
 'Clara Martin', 'oeuvre-003.jpg'),
((SELECT id_salle FROM salles WHERE numero = '002'), 
 'Particules d Éternité', 
 'Des milliers de points lumineux flottent dans obscurité, simulant une galaxie en formation. Le visiteur peut marcher au milieu de cette constellation artificielle.',
 'Lucas Bernard', 'oeuvre-004.jpg');

-- Oeuvres de la Salle 001 (Interactive) - 4 oeuvres
INSERT INTO oeuvres (id_salle, titre, description, artiste, image) VALUES
((SELECT id_salle FROM salles WHERE numero = '001'), 
 'Miroir de Âme', 
 'Un écran géant analyse en temps réel les expressions faciales du visiteur et génère un portrait abstrait reflétant ses émotions perçues par intelligence artificielle.',
 'Sophie Petit', 'oeuvre-005.jpg'),
((SELECT id_salle FROM salles WHERE numero = '001'), 
 'Danse des Ombres', 
 'Votre silhouette projetée sur le mur interagit avec des créatures numériques. Plus vous bougez, plus écosystème virtuel s anime et évolue.',
 'Antoine Moreau', 'oeuvre-006.jpg'),
((SELECT id_salle FROM salles WHERE numero = '001'), 
 'Toile Collective', 
 'Une fresque numérique collaborative où chaque visiteur peut ajouter sa touche de couleur via une tablette. L oeuvre grandit et se transforme au fil des jours.',
 'Emma Leroy', 'oeuvre-007.jpg'),
((SELECT id_salle FROM salles WHERE numero = '001'), 
 'Gravité Zéro', 
 'Des objets virtuels flottent dans espace et réagissent aux mouvements des mains du visiteur. Attrapez, lancez, faites rebondir ces formes géométriques colorées.',
 'Maxime Roux', 'oeuvre-008.jpg');

-- Oeuvres de la Salle 005 (Contemplative) - 4 oeuvres
INSERT INTO oeuvres (id_salle, titre, description, artiste, image) VALUES
((SELECT id_salle FROM salles WHERE numero = '005'), 
 'Respiration du Monde', 
 'Une sphère translucide pulse lentement au rythme des données environnementales mondiales en temps réel : qualité de air, température, activité humaine.',
 'Julie Blanc', 'oeuvre-009.jpg'),
((SELECT id_salle FROM salles WHERE numero = '005'), 
 'Temps Suspendu', 
 'Des horloges déconstruites dont les aiguilles se déplacent selon des algorithmes imprévisibles, questionnant notre perception linéaire du temps.',
 'Pierre Simon', 'oeuvre-010.jpg'),
((SELECT id_salle FROM salles WHERE numero = '005'), 
 'Jardin de Données', 
 'Des plantes numériques poussent et fanent selon les flux de données Internet. Un jardin virtuel qui reflète activité invisible du monde connecté.',
 'Camille Durand', 'oeuvre-011.jpg'),
((SELECT id_salle FROM salles WHERE numero = '005'), 
 'Silence Numérique', 
 'Une pièce où le bruit ambiant est capté et transformé en visualisations apaisantes. Plus le silence est profond, plus oeuvre devient lumineuse.',
 'Léa Fournier', 'oeuvre-012.jpg');

-- Oeuvres de la Salle 021 (Expérimentale) - 2 oeuvres seulement
INSERT INTO oeuvres (id_salle, titre, description, artiste, image) VALUES
((SELECT id_salle FROM salles WHERE numero = '021'), 
 'Prototype Alpha', 
 'Une intelligence artificielle en apprentissage qui tente de créer de art en temps réel. Observez ses tentatives, ses erreurs et ses surprenantes réussites.',
 'Collectif IA-Art', 'oeuvre-013.jpg'),
((SELECT id_salle FROM salles WHERE numero = '021'), 
 'Frontière Quantique', 
 'Expérimentation visuelle basée sur les principes de la physique quantique. Les particules virtuelles existent dans plusieurs états simultanés jusqu à observation.',
 'Dr. Nicolas Fabre', 'oeuvre-014.jpg');

-- ----------------------------------------------------------------------------
-- Insertion des catégories de visiteurs
-- buffet_actif = 1 : peut participer au buffet du jeudi
-- buffet_actif = 0 : ne peut pas participer au buffet
-- ----------------------------------------------------------------------------
INSERT INTO categories (nom, buffet_actif) VALUES
('Enseignant·e', 1),
('Étudiant·e MMI 2 ou 3', 0),
('Personnel USMB', 1),
('Professionnels/partenaires', 1),
('Visiteur·se extérieur', 1);

-- ----------------------------------------------------------------------------
-- Insertion des créneaux horaires
-- Jeudi 18/06/2026 : 10 créneaux (15:00 à 20:00)
-- Vendredi 19/06/2026 : 4 créneaux (9:30 à 11:00)
-- Pour chaque salle (4 salles) = 56 créneaux au total
-- ----------------------------------------------------------------------------

-- Créneaux pour la Salle 002
INSERT INTO creneaux (id_salle, date_creneau, heure, places_total) VALUES
((SELECT id_salle FROM salles WHERE numero = '002'), '2026-06-18', '15:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '002'), '2026-06-18', '15:30:00', 12),
((SELECT id_salle FROM salles WHERE numero = '002'), '2026-06-18', '16:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '002'), '2026-06-18', '16:30:00', 12),
((SELECT id_salle FROM salles WHERE numero = '002'), '2026-06-18', '17:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '002'), '2026-06-18', '17:30:00', 12),
((SELECT id_salle FROM salles WHERE numero = '002'), '2026-06-18', '18:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '002'), '2026-06-18', '19:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '002'), '2026-06-18', '19:30:00', 12),
((SELECT id_salle FROM salles WHERE numero = '002'), '2026-06-18', '20:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '002'), '2026-06-19', '09:30:00', 12),
((SELECT id_salle FROM salles WHERE numero = '002'), '2026-06-19', '10:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '002'), '2026-06-19', '10:30:00', 12),
((SELECT id_salle FROM salles WHERE numero = '002'), '2026-06-19', '11:00:00', 12);

-- Créneaux pour la Salle 001
INSERT INTO creneaux (id_salle, date_creneau, heure, places_total) VALUES
((SELECT id_salle FROM salles WHERE numero = '001'), '2026-06-18', '15:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '001'), '2026-06-18', '15:30:00', 12),
((SELECT id_salle FROM salles WHERE numero = '001'), '2026-06-18', '16:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '001'), '2026-06-18', '16:30:00', 12),
((SELECT id_salle FROM salles WHERE numero = '001'), '2026-06-18', '17:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '001'), '2026-06-18', '17:30:00', 12),
((SELECT id_salle FROM salles WHERE numero = '001'), '2026-06-18', '18:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '001'), '2026-06-18', '19:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '001'), '2026-06-18', '19:30:00', 12),
((SELECT id_salle FROM salles WHERE numero = '001'), '2026-06-18', '20:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '001'), '2026-06-19', '09:30:00', 12),
((SELECT id_salle FROM salles WHERE numero = '001'), '2026-06-19', '10:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '001'), '2026-06-19', '10:30:00', 12),
((SELECT id_salle FROM salles WHERE numero = '001'), '2026-06-19', '11:00:00', 12);

-- Créneaux pour la Salle 005
INSERT INTO creneaux (id_salle, date_creneau, heure, places_total) VALUES
((SELECT id_salle FROM salles WHERE numero = '005'), '2026-06-18', '15:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '005'), '2026-06-18', '15:30:00', 12),
((SELECT id_salle FROM salles WHERE numero = '005'), '2026-06-18', '16:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '005'), '2026-06-18', '16:30:00', 12),
((SELECT id_salle FROM salles WHERE numero = '005'), '2026-06-18', '17:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '005'), '2026-06-18', '17:30:00', 12),
((SELECT id_salle FROM salles WHERE numero = '005'), '2026-06-18', '18:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '005'), '2026-06-18', '19:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '005'), '2026-06-18', '19:30:00', 12),
((SELECT id_salle FROM salles WHERE numero = '005'), '2026-06-18', '20:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '005'), '2026-06-19', '09:30:00', 12),
((SELECT id_salle FROM salles WHERE numero = '005'), '2026-06-19', '10:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '005'), '2026-06-19', '10:30:00', 12),
((SELECT id_salle FROM salles WHERE numero = '005'), '2026-06-19', '11:00:00', 12);

-- Créneaux pour la Salle 021
INSERT INTO creneaux (id_salle, date_creneau, heure, places_total) VALUES
((SELECT id_salle FROM salles WHERE numero = '021'), '2026-06-18', '15:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '021'), '2026-06-18', '15:30:00', 12),
((SELECT id_salle FROM salles WHERE numero = '021'), '2026-06-18', '16:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '021'), '2026-06-18', '16:30:00', 12),
((SELECT id_salle FROM salles WHERE numero = '021'), '2026-06-18', '17:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '021'), '2026-06-18', '17:30:00', 12),
((SELECT id_salle FROM salles WHERE numero = '021'), '2026-06-18', '18:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '021'), '2026-06-18', '19:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '021'), '2026-06-18', '19:30:00', 12),
((SELECT id_salle FROM salles WHERE numero = '021'), '2026-06-18', '20:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '021'), '2026-06-19', '09:30:00', 12),
((SELECT id_salle FROM salles WHERE numero = '021'), '2026-06-19', '10:00:00', 12),
((SELECT id_salle FROM salles WHERE numero = '021'), '2026-06-19', '10:30:00', 12),
((SELECT id_salle FROM salles WHERE numero = '021'), '2026-06-19', '11:00:00', 12);

-- ----------------------------------------------------------------------------
-- Insertion de utilisateur administrateur de test
-- Email : admin@ellusion.fr
-- Mot de passe : admin123 (hashé avec password_hash)
-- 
-- IMPORTANT : Le hash ci-dessous correspond à "admin123"
-- En production, changez ce mot de passe !
-- ----------------------------------------------------------------------------
INSERT INTO utilisateurs (email, mot_de_passe, nom, prenom, role, agence) VALUES
('admin@ellusion.fr', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
 'Administrateur', 
 'E-LLUSION', 
 'admin', 
 'MMI Chambéry');

-- ============================================================================
-- INDEX POUR OPTIMISATION DES PERFORMANCES
-- ============================================================================
CREATE INDEX idx_inscriptions_token ON inscriptions(token);
CREATE INDEX idx_inscriptions_email ON inscriptions(email);
CREATE INDEX idx_creneaux_date ON creneaux(date_creneau);
CREATE INDEX idx_reservations_creneau ON reservations(id_creneau);

-- ============================================================================
-- FIN DU SCRIPT
-- La base de données sae203_ellusion est maintenant prête à emploi !
-- ============================================================================
