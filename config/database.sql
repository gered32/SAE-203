-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: sae203_ellusion
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id_categorie` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL COMMENT 'Nom de la catégorie',
  `buffet_actif` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = accès buffet autorisé, 0 = non autorisé',
  PRIMARY KEY (`id_categorie`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` (`id_categorie`, `nom`, `buffet_actif`) VALUES (1,'Enseignant·e',1);
INSERT INTO `categories` (`id_categorie`, `nom`, `buffet_actif`) VALUES (2,'Étudiant·e MMI 2 ou 3',0);
INSERT INTO `categories` (`id_categorie`, `nom`, `buffet_actif`) VALUES (3,'Personnel USMB',1);
INSERT INTO `categories` (`id_categorie`, `nom`, `buffet_actif`) VALUES (4,'Professionnels/partenaires',1);
INSERT INTO `categories` (`id_categorie`, `nom`, `buffet_actif`) VALUES (5,'Visiteur·se extérieur',1);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `creneaux`
--

DROP TABLE IF EXISTS `creneaux`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `creneaux` (
  `id_creneau` int(11) NOT NULL AUTO_INCREMENT,
  `id_salle` int(11) NOT NULL COMMENT 'Clé étrangère vers la salle',
  `date_creneau` date NOT NULL COMMENT 'Date du créneau',
  `heure` time NOT NULL COMMENT 'Heure de début du créneau',
  `places_total` int(11) NOT NULL DEFAULT 12 COMMENT 'Nombre total de places disponibles',
  PRIMARY KEY (`id_creneau`),
  UNIQUE KEY `unique_creneau` (`id_salle`,`date_creneau`,`heure`),
  KEY `idx_creneaux_date` (`date_creneau`),
  CONSTRAINT `fk_creneau_salle` FOREIGN KEY (`id_salle`) REFERENCES `salles` (`id_salle`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `creneaux`
--

LOCK TABLES `creneaux` WRITE;
/*!40000 ALTER TABLE `creneaux` DISABLE KEYS */;
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (1,1,'2026-06-18','15:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (2,1,'2026-06-18','15:30:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (3,1,'2026-06-18','16:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (4,1,'2026-06-18','16:30:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (5,1,'2026-06-18','17:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (6,1,'2026-06-18','17:30:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (7,1,'2026-06-18','18:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (8,1,'2026-06-18','19:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (9,1,'2026-06-18','19:30:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (10,1,'2026-06-18','20:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (11,1,'2026-06-19','09:30:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (12,1,'2026-06-19','10:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (13,1,'2026-06-19','10:30:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (14,1,'2026-06-19','11:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (15,2,'2026-06-18','15:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (16,2,'2026-06-18','15:30:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (17,2,'2026-06-18','16:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (18,2,'2026-06-18','16:30:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (19,2,'2026-06-18','17:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (20,2,'2026-06-18','17:30:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (21,2,'2026-06-18','18:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (22,2,'2026-06-18','19:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (23,2,'2026-06-18','19:30:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (24,2,'2026-06-18','20:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (25,2,'2026-06-19','09:30:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (26,2,'2026-06-19','10:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (27,2,'2026-06-19','10:30:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (28,2,'2026-06-19','11:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (29,3,'2026-06-18','15:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (30,3,'2026-06-18','15:30:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (31,3,'2026-06-18','16:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (32,3,'2026-06-18','16:30:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (33,3,'2026-06-18','17:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (34,3,'2026-06-18','17:30:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (35,3,'2026-06-18','18:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (36,3,'2026-06-18','19:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (37,3,'2026-06-18','19:30:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (38,3,'2026-06-18','20:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (39,3,'2026-06-19','09:30:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (40,3,'2026-06-19','10:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (41,3,'2026-06-19','10:30:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (42,3,'2026-06-19','11:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (43,4,'2026-06-18','15:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (44,4,'2026-06-18','15:30:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (45,4,'2026-06-18','16:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (46,4,'2026-06-18','16:30:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (47,4,'2026-06-18','17:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (48,4,'2026-06-18','17:30:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (49,4,'2026-06-18','18:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (50,4,'2026-06-18','19:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (51,4,'2026-06-18','19:30:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (52,4,'2026-06-18','20:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (53,4,'2026-06-19','09:30:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (54,4,'2026-06-19','10:00:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (55,4,'2026-06-19','10:30:00',12);
INSERT INTO `creneaux` (`id_creneau`, `id_salle`, `date_creneau`, `heure`, `places_total`) VALUES (56,4,'2026-06-19','11:00:00',12);
/*!40000 ALTER TABLE `creneaux` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inscriptions`
--

DROP TABLE IF EXISTS `inscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inscriptions` (
  `id_inscription` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL COMMENT 'Nom du visiteur',
  `prenom` varchar(100) NOT NULL COMMENT 'Prénom du visiteur',
  `email` varchar(191) NOT NULL COMMENT 'Email du visiteur',
  `id_categorie` int(11) NOT NULL COMMENT 'Clé étrangère vers la catégorie',
  `nb_personnes` int(11) NOT NULL DEFAULT 1 COMMENT 'Nombre de personnes (1-12)',
  `buffet_jeudi` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = participe au buffet, 0 = non',
  `token` varchar(64) NOT NULL COMMENT 'Token unique pour modification/suppression',
  `date_inscription` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Date et heure de inscription',
  `email_referent` varchar(191) DEFAULT NULL COMMENT 'Email du référent du projet',
  PRIMARY KEY (`id_inscription`),
  UNIQUE KEY `token` (`token`),
  KEY `fk_inscription_categorie` (`id_categorie`),
  KEY `idx_inscriptions_token` (`token`),
  KEY `idx_inscriptions_email` (`email`),
  CONSTRAINT `fk_inscription_categorie` FOREIGN KEY (`id_categorie`) REFERENCES `categories` (`id_categorie`) ON UPDATE CASCADE,
  CONSTRAINT `chk_nb_personnes` CHECK (`nb_personnes` >= 1 and `nb_personnes` <= 12)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inscriptions`
--

LOCK TABLES `inscriptions` WRITE;
/*!40000 ALTER TABLE `inscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `inscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oeuvres`
--

DROP TABLE IF EXISTS `oeuvres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oeuvres` (
  `id_oeuvre` int(11) NOT NULL AUTO_INCREMENT,
  `id_salle` int(11) NOT NULL COMMENT 'Clé étrangère vers la salle',
  `titre` varchar(150) NOT NULL COMMENT 'Titre de oeuvre',
  `description` text DEFAULT NULL COMMENT 'Description de oeuvre',
  `artiste` varchar(100) NOT NULL COMMENT 'Nom de artiste',
  `image` varchar(255) DEFAULT 'placeholder.jpg' COMMENT 'Chemin vers image de oeuvre',
  PRIMARY KEY (`id_oeuvre`),
  KEY `fk_oeuvre_salle` (`id_salle`),
  CONSTRAINT `fk_oeuvre_salle` FOREIGN KEY (`id_salle`) REFERENCES `salles` (`id_salle`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oeuvres`
--

LOCK TABLES `oeuvres` WRITE;
/*!40000 ALTER TABLE `oeuvres` DISABLE KEYS */;
INSERT INTO `oeuvres` (`id_oeuvre`, `id_salle`, `titre`, `description`, `artiste`, `image`) VALUES (1,1,'TAPIS ROUGE','TAPIS ROUGE est une installation interactive et immersive qui détourne les codes du prestige pour confronter le spectateur aux réalités sociales invisibles de la production industrielle.\r\nUn tapis rouge physique invite le public à s\'avancer sous des projecteurs de scène. Le mouvement du visiteur contrôle directement une vidéo projetée sur le mur frontal : d\'abord une ambiance de luxe et de privilège puis, à mesure qu\'il avance, les coulisses de la consommation s\'imposent : entrepôts, lignes de production, infrastructures froides.\r\nEn bout de course, l\'image devient grave : pénibilité du travail, insalubrité des usines, épuisement des corps. L\'œuvre révèle que chaque pas vers le succès repose sur une réalité humaine sacrifiée.\r\n','TP 2.1','oeuvre-001.jpg');
INSERT INTO `oeuvres` (`id_oeuvre`, `id_salle`, `titre`, `description`, `artiste`, `image`) VALUES (2,1,'EN DIRECT','Placez-vous devant l\'écran : une caméra vous filme en direct, à la manière d\'un live TikTok ou Instagram. Des commentaires apparaissent automatiquement, générés selon votre distance à l\'écran et vos expressions faciales détectées par reconnaissance d\'image. Trop proche, trop loin, souriant ou neutre — quoi que vous fassiez, vous serez jugé. En Direct met en scène le jugement social permanent que produisent les réseaux sociaux, et l\'illusion d\'un « like » qui n\'existe que pour mieux nous critiquer.','TP 2.1','oeuvre-002.jpg');
INSERT INTO `oeuvres` (`id_oeuvre`, `id_salle`, `titre`, `description`, `artiste`, `image`) VALUES (3,1,'AD-HD ( Ad driven human display )','Ad Driven Human Display est une œuvre interactive qui questionne la place de l’humain dans l’économie de l’attention. Face à une interface TikTok, le visiteur fait défiler un flux de contenus promotionnels à l’aide d’un grand rouleau physique. Des pop-up publicitaires apparaissent aléatoirement, l’obligeant à les fermer avec une souris. L’œuvre révèle une illusion contemporaine : nous pensons contrôler ce que nous regardons, alors que nos gestes, notre temps et notre attention sont constamment captés par la publicité.','TP 2.1','oeuvre-003.jpg');
INSERT INTO `oeuvres` (`id_oeuvre`, `id_salle`, `titre`, `description`, `artiste`, `image`) VALUES (5,2,'Bon profil','L\'œuvre explore la vulnérabilité de notre identité numérique en plaçant le visiteur au cœur d\'une mécanique de désinformation instantanée. Le spectateur est d\'abord invité à prendre une simple photo, pensant capturer un souvenir inoffensif. Cependant, cette image est immédiatement détournée par une intelligence artificielle qui génère un deepfake à son insu. En quelques secondes, le visage du visiteur se retrouve propulsé dans des situations absurdes ou compromettantes et publié sur un faux fil d\'actualité de réseaux sociaux. ','TP 1.2','oeuvre-005.jpg');
INSERT INTO `oeuvres` (`id_oeuvre`, `id_salle`, `titre`, `description`, `artiste`, `image`) VALUES (6,2,'Antithèse','Antithèse oppose deux visions de la réalité : une vision déformée, influencée par les réseaux sociaux, les chaînes d’information et les contenus biaisés, et une vision plus objective basée sur des faits, des chiffres et l’esprit critique. Grâce à une interaction basée sur la distance du visiteur, l’installation montre que notre perception peut évoluer selon le point de vue adopté. L’œuvre invite ainsi le spectateur à prendre du recul face aux informations qu’il consomme quotidiennement et à questionner la manière dont les médias influencent sa compréhension du réel. ','TP 1.2','oeuvre-006.jpg');
INSERT INTO `oeuvres` (`id_oeuvre`, `id_salle`, `titre`, `description`, `artiste`, `image`) VALUES (7,2,'Beauté hors du cadre','L\'œuvre \"BEAUTÉ HORS DU CADRE\" plonge le spectateur dans un espace végétal apaisant où un écran géant imite un smartphone. Ce miroir numérique capte le reflet du visiteur et l\'invite à \"swiper\" des vidéos d\'abord familières et agréables. Au fil des défilements, les contenus deviennent angoissants, la lumière s\'assombrit et les sons naturels se déforment pour devenir inquiétants. Le reflet de l\'utilisateur s\'efface alors peu à peu, donnant l\'illusion qu\'il est totalement absorbé par l\'écran. Cette installation interactive offre ainsi une métaphore de la mort numérique. Elle illustre comment notre usage des objets numériques et des réseaux sociaux altère notre perception du réel. s.','TP 1.2','oeuvre-007.jpg');
INSERT INTO `oeuvres` (`id_oeuvre`, `id_salle`, `titre`, `description`, `artiste`, `image`) VALUES (9,3,'LOTUS','Nous créons une œuvre interactive où la chute d’Alice au pays des merveilles\r\ndans le terrier du lapin devient une expérience sensorielle et participative.\r\nEn jouant du synthétiseur,\r\nnous transformons en temps réel ce que le public voit à l’écran : les notes jouées modifient la vitesse, la forme ou les couleurs de la chute,\r\ncomme si Alice réagissait directement aux sons.','TP 2.2','oeuvre-009.jpg');
INSERT INTO `oeuvres` (`id_oeuvre`, `id_salle`, `titre`, `description`, `artiste`, `image`) VALUES (10,3,'E-biscus','E-biscus est une œuvre interactive qui permet de mettre en avant les illusions de la société, plus précisément concernant la beauté. Le but global de l\'œuvre est de plonger le spectateur dans un rêve numérique.','TP 2.2','oeuvre-010.jpg');
INSERT INTO `oeuvres` (`id_oeuvre`, `id_salle`, `titre`, `description`, `artiste`, `image`) VALUES (11,3,'Datura','« Datura » est une installation interactive présentant une forêt de séquoias en 3D, projetée sur un grand écran. Deux zones physiques au sol devant la projection, « Zone Rêve » et « Zone Cauchemar », invitent le spectateur à interagir. Une webcam détecte la répartition des visiteurs entre ces deux zones. Le public, par sa présence physique, vote et influence en temps réel le monde 3.','TP 2.2','oeuvre-011.jpg');
INSERT INTO `oeuvres` (`id_oeuvre`, `id_salle`, `titre`, `description`, `artiste`, `image`) VALUES (12,3,'Silence Numérique','Une pièce où le bruit ambiant est capté et transformé en visualisations apaisantes. Plus le silence est profond, plus oeuvre devient lumineuse.','Léa Fournier','oeuvre-012.jpg');
INSERT INTO `oeuvres` (`id_oeuvre`, `id_salle`, `titre`, `description`, `artiste`, `image`) VALUES (13,4,'Community','Community explore la décontextualisation et montre comment une société numérique, miniature de la nôtre, devient une matière malléable. En isolant un geste, un instant ou une réaction, le système transforme un événement aléatoire en intention interprétée, souvent éloignée de la réalité. Dans Community, cette distorsion est amplifiée par le regard des autres, Ce que la personne au commande de la société choisit de monter influence l’ensemble du groupe mais également les personnages du jeu ! Chacun ajuste son comportement en fonction de ce qu’il pense que les autres perçoivent et de ce qui est montré sous un certain point de vue. L’écran devient alors un miroir déformant. En effet, dans le jeu, une image isolée suffit à déclencher des émotions collectives, à modifier l’ambiance générale et à orienter les comportements du groupe, comme si une simple capture définissait soudain une vérité partagée. ','TP 1.1','oeuvre-013.jpg');
INSERT INTO `oeuvres` (`id_oeuvre`, `id_salle`, `titre`, `description`, `artiste`, `image`) VALUES (14,4,'Distorsion','L’œuvre Distorsion explore l’émancipation de notre identité dans un récit où notre image ne nous appartient plus. Elle montre que, dans l’univers numérique, notre visage devient une matière que les autres peuvent modifier, détourner ou réinventer. Cette transformation imposée crée une version de nous qui échappe à notre contrôle. Dans Distorsion, cette image altérée est ensuite mise en vente, comme un produit parmi d’autres, révélant comment la société de consommation s’approprie jusqu’à notre identité. Le visage devient un objet marchand, façonné par le regard collectif, que chacun peut acheter et  juger. La valeur de cette nouvelle identité dépend alors non plus de nous, mais de la réaction des autres. ','TP 1.1','oeuvre-014.jpg');
/*!40000 ALTER TABLE `oeuvres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservations` (
  `id_reservation` int(11) NOT NULL AUTO_INCREMENT,
  `id_inscription` int(11) NOT NULL COMMENT 'Clé étrangère vers inscription',
  `id_creneau` int(11) NOT NULL COMMENT 'Clé étrangère vers le créneau',
  PRIMARY KEY (`id_reservation`),
  UNIQUE KEY `unique_inscription_creneau` (`id_inscription`,`id_creneau`),
  KEY `idx_reservations_creneau` (`id_creneau`),
  CONSTRAINT `fk_reservation_creneau` FOREIGN KEY (`id_creneau`) REFERENCES `creneaux` (`id_creneau`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_reservation_inscription` FOREIGN KEY (`id_inscription`) REFERENCES `inscriptions` (`id_inscription`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
/*!40000 ALTER TABLE `reservations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salles`
--

DROP TABLE IF EXISTS `salles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salles` (
  `id_salle` int(11) NOT NULL AUTO_INCREMENT,
  `numero` varchar(10) NOT NULL COMMENT 'Numéro de la salle (ex: 002, 001, 005, 021)',
  `nom` varchar(100) NOT NULL COMMENT 'Nom descriptif de la salle',
  `description` text DEFAULT NULL COMMENT 'Description détaillée de la salle',
  `capacite_max` int(11) NOT NULL DEFAULT 12 COMMENT 'Capacité maximale par créneau',
  `image` varchar(255) DEFAULT 'placeholder.jpg' COMMENT 'Chemin vers image de la salle',
  PRIMARY KEY (`id_salle`),
  UNIQUE KEY `numero` (`numero`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salles`
--

LOCK TABLES `salles` WRITE;
/*!40000 ALTER TABLE `salles` DISABLE KEYS */;
INSERT INTO `salles` (`id_salle`, `numero`, `nom`, `description`, `capacite_max`, `image`) VALUES (1,'002','L\'envers du décors ','Comment l\'illusion d\'une société parfaite révèle-t-elle l\'état de la nôtre ? Le thème principal de notre salle est de questionner les façades que la société se construit pour masquer ses contradictions, qu\'il s\'agisse du regard social sur les réseaux, du glamour de la mode ou de la mécanique de la consommation. Les trois œuvres montrent ainsi comment le numérique, en mettant en scène ces illusions, finit par révéler l\'état réel d\'un monde qui se rêve parfait.',12,'salle-002.jpg');
INSERT INTO `salles` (`id_salle`, `numero`, `nom`, `description`, `capacite_max`, `image`) VALUES (2,'001','Horizon','Comment les objets numériques altèrent-ils notre perception du réel ? Le thème principal de notre salle est : À travers la création de trois œuvres interactives, nous cherchons à explorer la manière dont les technologies influencent notre perception du réel, en transformant notre rapport au corps, à l’image et à l’environnement.',12,'salle-001.jpg');
INSERT INTO `salles` (`id_salle`, `numero`, `nom`, `description`, `capacite_max`, `image`) VALUES (3,'005','La pépinière','A l\'image de nos rêves, comment le numérique altère t-il notre perception de la réalité. Notre exposition immersive et interactive vous invite à explorer comment le numérique transforme notre perception du monde. Grâce à des œuvres numériques innovantes, vous serez transporté·e dans des univers oniriques où les écrans deviennent des fenêtres vers l’inconscient, et où chaque interaction révèle une nouvelle facette de notre réalité altérée. Entre illusions optiques, expériences sensorielles et récits visuels, venez questionner votre propre perception et découvrir comment la technologie façonne nos rêves… et nos cauchemars. Une expérience à vivre, pas à observer.',12,'salle-005.jpg');
INSERT INTO `salles` (`id_salle`, `numero`, `nom`, `description`, `capacite_max`, `image`) VALUES (4,'020','Societ-E','Comment le monde numérique modifie‑t‑il et crée‑t‑il une nouvelle réalité ? Le thème principal de notre salle est de questionner l’influence de la société sur nos comportements, mais aussi sur la construction de notre identité et sur la recherche de validation sociale à travers le regard des autres. Les deux œuvres montrent ainsi comment le numérique transforme notre rapport au réel et renforce cette pression.',12,'salle-021.jpg');
/*!40000 ALTER TABLE `salles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `utilisateurs` (
  `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(191) NOT NULL COMMENT 'Email de connexion',
  `mot_de_passe` varchar(255) NOT NULL COMMENT 'Mot de passe hashé (password_hash)',
  `nom` varchar(100) NOT NULL COMMENT 'Nom de utilisateur',
  `prenom` varchar(100) NOT NULL COMMENT 'Prénom de utilisateur',
  `role` enum('admin','referent') NOT NULL DEFAULT 'referent' COMMENT 'Rôle de utilisateur',
  `agence` varchar(100) DEFAULT NULL COMMENT 'Agence ou département (optionnel)',
  PRIMARY KEY (`id_utilisateur`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utilisateurs`
--

LOCK TABLES `utilisateurs` WRITE;
/*!40000 ALTER TABLE `utilisateurs` DISABLE KEYS */;
INSERT INTO `utilisateurs` (`id_utilisateur`, `email`, `mot_de_passe`, `nom`, `prenom`, `role`, `agence`) VALUES (1,'admin@ellusion.fr','$2y$10$Kd3opxzMDPbVFb66IXfoVeVfWJXi/zHNEEpkxnAK9wiY/HRuFcHmi','Administrateur','E-LLUSION','admin','MMI Chambéry');
/*!40000 ALTER TABLE `utilisateurs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'sae203_ellusion'
--

--
-- Dumping routines for database 'sae203_ellusion'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-01 16:07:04
