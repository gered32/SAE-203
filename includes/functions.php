<?php
/**
 * ============================================================================
 * SAE203 E-LLUSION - Fonctions utilitaires
 * ============================================================================
 * Ce fichier contient toutes les fonctions réutilisables du projet :
 * - Sécurité (CSRF, sanitization)
 * - Gestion de la jauge
 * - Redirections
 * - Simulation d'envoi d'emails
 * - Fonctions d'affichage et de débogage
 * ============================================================================
 */

// Inclusion de la configuration
require_once __DIR__ . '/../config/config.php';

// ============================================================================
// FONCTIONS DE SÉCURITÉ - CSRF
// ============================================================================

/**
 * Génère ou récupère un token CSRF stocké en session.
 * Le token CSRF protège contre les attaques Cross-Site Request Forgery.
 * 
 * Fonctionnement :
 * 1. Si un token existe en session, on le retourne
 * 2. Sinon, on génère un nouveau token aléatoire de 32 octets (64 caractères hex)
 * 
 * @return string Token CSRF de 64 caractères hexadécimaux
 */
function csrfToken(): string {
    // Démarre la session si nécessaire
    demarrerSession();
    
    // Si le token n'existe pas, on le génère
    if (empty($_SESSION['csrf_token'])) {
        // bin2hex convertit les octets aléatoires en chaîne hexadécimale
        // random_bytes(32) génère 32 octets = 64 caractères hex
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie la validité d'un token CSRF soumis par le formulaire.
 * Compare le token reçu avec celui stocké en session.
 * 
 * @param string $token Token CSRF à vérifier (provenant de $_POST)
 * @return bool True si le token est valide, False sinon
 */
function verifierCsrf(string $token): bool {
    demarrerSession();
    
    // hash_equals compare les chaînes en temps constant
    // Protège contre les attaques de timing
    if (!empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    
    return false;
}

/**
 * Régénère le token CSRF (à appeler après une action sensible réussie).
 * Invalide l'ancien token pour plus de sécurité.
 */
function regenererCsrf(): void {
    demarrerSession();
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ============================================================================
// FONCTIONS DE SÉCURITÉ - SANITIZATION
// ============================================================================

/**
 * Nettoie une chaîne pour un affichage sécurisé en HTML.
 * Protège contre les attaques XSS (Cross-Site Scripting).
 * 
 * @param string|null $string Chaîne à nettoyer
 * @return string Chaîne sécurisée pour affichage HTML
 */
function sanitize(?string $string): string {
    if ($string === null) {
        return '';
    }
    // htmlspecialchars convertit les caractères spéciaux HTML en entités
    // ENT_QUOTES : convertit les guillemets simples et doubles
    // UTF-8 : encodage des caractères
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Nettoie une chaîne en retirant les espaces superflus.
 * Utile pour les champs de formulaire (nom, prénom, etc.)
 * 
 * @param string|null $string Chaîne à nettoyer
 * @return string Chaîne nettoyée
 */
function nettoyer(?string $string): string {
    if ($string === null) {
        return '';
    }
    return trim($string);
}

/**
 * Valide une adresse email.
 * 
 * @param string $email Email à valider
 * @return bool True si l'email est valide
 */
function validerEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// ============================================================================
// FONCTIONS DE GESTION DE LA JAUGE
// ============================================================================

/**
 * Calcule le nombre de places restantes pour un créneau donné.
 * Formule : places_total - SUM(nb_personnes des réservations existantes)
 * 
 * @param int $id_creneau Identifiant du créneau
 * @return int Nombre de places restantes (minimum 0)
 */
function placesRestantes(int $id_creneau): int {
    $pdo = getPDO();
    
    // Requête avec sous-requête pour calculer les places occupées
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
    
    // Retourne 0 si le créneau n'existe pas ou si plus de places
    return $resultat ? max(0, (int)$resultat['places_restantes']) : 0;
}

/**
 * Vérifie si un créneau a assez de places pour un nombre de personnes donné.
 * 
 * @param int $id_creneau Identifiant du créneau
 * @param int $nb_personnes Nombre de personnes à inscrire
 * @return bool True s'il y a assez de places
 */
function creneauDisponible(int $id_creneau, int $nb_personnes): bool {
    return placesRestantes($id_creneau) >= $nb_personnes;
}

// ============================================================================
// FONCTIONS DE REDIRECTION
// ============================================================================

/**
 * Redirige vers une URL et arrête l'exécution du script.
 * 
 * @param string $url URL de destination (peut être relative)
 */
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

/**
 * Redirige vers une page avec un message flash en session.
 * Le message sera affiché une seule fois sur la page de destination.
 * 
 * @param string $url URL de destination
 * @param string $message Message à afficher
 * @param string $type Type de message : 'success', 'error', 'info', 'warning'
 */
function redirectAvecMessage(string $url, string $message, string $type = 'info'): void {
    demarrerSession();
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
    redirect($url);
}

/**
 * Récupère et supprime le message flash de la session.
 * 
 * @return array|null Message flash ou null si absent
 */
function getFlashMessage(): ?array {
    demarrerSession();
    
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    
    return null;
}

// ============================================================================
// FONCTIONS D'AUTHENTIFICATION
// ============================================================================

/**
 * Vérifie si un utilisateur est connecté à l'espace admin.
 * 
 * @return bool True si l'utilisateur est connecté
 */
function estConnecte(): bool {
    demarrerSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

/**
 * Vérifie si l'utilisateur connecté est un administrateur.
 * 
 * @return bool True si l'utilisateur est admin
 */
function estAdmin(): bool {
    demarrerSession();
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Protège une page admin : redirige vers login si non connecté.
 */
function protegerPageAdmin(): void {
    if (!estConnecte()) {
        redirectAvecMessage('../login.php', 'Veuillez vous connecter pour accéder à cette page.', 'error');
    }
}

// ============================================================================
// FONCTIONS DE GÉNÉRATION DE TOKEN
// ============================================================================

/**
 * Génère un token unique pour les réservations.
 * Ce token permet au visiteur de modifier/supprimer sa réservation sans compte.
 * 
 * @return string Token de 64 caractères hexadécimaux
 */
function genererToken(): string {
    return bin2hex(random_bytes(32));
}

// ============================================================================
// FONCTIONS D'ENVOI D'EMAIL (SIMULATION)
// ============================================================================

/**
 * Simule l'envoi d'un email en écrivant dans un fichier log.
 * En production, remplacer le contenu par un appel à mail() ou une librairie SMTP.
 * 
 * @param string $destinataire Adresse email du destinataire
 * @param string $sujet Sujet de l'email
 * @param string $corps Corps du message (texte ou HTML)
 * @return bool True si l'écriture dans le log a réussi
 */
function simulerEnvoiMail(string $destinataire, string $sujet, string $corps): bool {
    // Chemin vers le fichier de log
    $logFile = LOGS_PATH . 'confirmation.log';
    
    // Création du dossier logs s'il n'existe pas
    if (!is_dir(LOGS_PATH)) {
        mkdir(LOGS_PATH, 0755, true);
    }
    
    // Formatage du message de log
    $dateHeure = date('Y-m-d H:i:s');
    $contenuLog = "
================================================================================
DATE : {$dateHeure}
================================================================================
DESTINATAIRE : {$destinataire}
SUJET : {$sujet}
--------------------------------------------------------------------------------
CORPS DU MESSAGE :
{$corps}
================================================================================

";
    
    // Écriture dans le fichier (mode append)
    $resultat = file_put_contents($logFile, $contenuLog, FILE_APPEND | LOCK_EX);
    
    return $resultat !== false;
    
    /*
     * =========================================================================
     * POUR UTILISER LE VRAI ENVOI D'EMAIL EN PRODUCTION :
     * =========================================================================
     * Décommentez le code ci-dessous et commentez le code ci-dessus.
     * Assurez-vous que votre serveur est configuré pour l'envoi d'emails.
     * 
     * $headers = "MIME-Version: 1.0\r\n";
     * $headers .= "Content-type: text/html; charset=UTF-8\r\n";
     * $headers .= "From: noreply@e-llusion.fr\r\n";
     * 
     * return mail($destinataire, $sujet, $corps, $headers);
     * =========================================================================
     */
}

/**
 * Génère le contenu HTML de l'email de confirmation de réservation.
 * 
 * @param array $inscription Données de l'inscription
 * @param array $reservation Données de la réservation (créneau, salle)
 * @return string Contenu HTML de l'email
 */
function genererEmailConfirmation(array $inscription, array $reservation): string {
    $lienModification = SITE_URL . '/ma-reservation.php?token=' . $inscription['token'];
    
    $buffetTexte = $inscription['buffet_jeudi'] ? 'Oui' : 'Non';
    
    $html = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Confirmation de réservation - E-LLUSION</title>
    </head>
    <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
        <h1 style='color: #00bbaa;'>E-LLUSION</h1>
        <h2>Confirmation de votre réservation</h2>
        
        <p>Bonjour {$inscription['prenom']} {$inscription['nom']},</p>
        
        <p>Votre inscription à l'exposition E-LLUSION a bien été enregistrée.</p>
        
        <h3 style='color: #3ce8d7;'>Récapitulatif :</h3>
        <ul>
            <li><strong>Salle :</strong> {$reservation['salle_numero']} - {$reservation['salle_nom']}</li>
            <li><strong>Date :</strong> {$reservation['date_formatee']}</li>
            <li><strong>Heure :</strong> {$reservation['heure_formatee']}</li>
            <li><strong>Nombre de personnes :</strong> {$inscription['nb_personnes']}</li>
            <li><strong>Participation au buffet :</strong> {$buffetTexte}</li>
        </ul>
        
        <h3 style='color: #3ce8d7;'>Modifier ou annuler votre réservation</h3>
        <p>Pour modifier ou annuler votre réservation, cliquez sur le lien ci-dessous :</p>
        <p><a href='{$lienModification}' style='color: #00bbaa;'>{$lienModification}</a></p>
        
        <p style='color: #af0000;'><strong>Conservez précieusement ce lien !</strong> Il est unique et vous permet de gérer votre réservation.</p>
        
        <hr>
        <p style='font-size: 12px; color: #666;'>
            Cet email a été envoyé automatiquement par le système de réservation E-LLUSION.<br>
            Ne répondez pas à cet email.
        </p>
    </body>
    </html>
    ";
    
    return $html;
}

// ============================================================================
// FONCTIONS DE FORMATAGE
// ============================================================================

/**
 * Formate une date au format français.
 * 
 * @param string $date Date au format Y-m-d
 * @return string Date formatée (ex: "Jeudi 18 juin 2026")
 */
function formaterDate(string $date): string {
    $timestamp = strtotime($date);
    
    $jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    $mois = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 
             'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    
    $jour = $jours[date('w', $timestamp)];
    $numero = date('j', $timestamp);
    $moisNom = $mois[date('n', $timestamp)];
    $annee = date('Y', $timestamp);
    
    return "{$jour} {$numero} {$moisNom} {$annee}";
}

/**
 * Formate une heure au format français.
 * 
 * @param string $heure Heure au format H:i:s
 * @return string Heure formatée (ex: "15h30")
 */
function formaterHeure(string $heure): string {
    $timestamp = strtotime($heure);
    $heures = date('G', $timestamp);
    $minutes = date('i', $timestamp);
    
    if ($minutes === '00') {
        return $heures . 'h';
    }
    
    return $heures . 'h' . $minutes;
}

// ============================================================================
// FONCTIONS DE DÉBOGAGE
// ============================================================================

/**
 * Affiche une variable de manière formatée pour le débogage.
 * Ne fonctionne qu'en mode DEBUG.
 * 
 * @param mixed $variable Variable à afficher
 * @param bool $exit Arrêter l'exécution après l'affichage
 */
function debug($variable, bool $exit = true): void {
    if (DEBUG_MODE) {
        echo '<pre style="background: #1a1a1a; color: #3ce8d7; padding: 15px; border-radius: 5px; overflow: auto;">';
        var_dump($variable);
        echo '</pre>';
        
        if ($exit) {
            exit;
        }
    }
}

// ============================================================================
// FONCTIONS UTILITAIRES DIVERSES
// ============================================================================

/**
 * Récupère toutes les catégories depuis la base de données.
 * 
 * @return array Liste des catégories
 */
function getCategories(): array {
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY id_categorie");
    return $stmt->fetchAll();
}

/**
 * Récupère toutes les salles depuis la base de données.
 * 
 * @return array Liste des salles
 */
function getSalles(): array {
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT * FROM salles ORDER BY numero");
    return $stmt->fetchAll();
}

/**
 * Récupère une salle par son ID.
 * 
 * @param int $id_salle Identifiant de la salle
 * @return array|null Données de la salle ou null si non trouvée
 */
function getSalleById(int $id_salle): ?array {
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT * FROM salles WHERE id_salle = :id");
    $stmt->bindValue(':id', $id_salle, PDO::PARAM_INT);
    $stmt->execute();
    
    $salle = $stmt->fetch();
    return $salle ?: null;
}

/**
 * Récupère les oeuvres d'une salle.
 * 
 * @param int $id_salle Identifiant de la salle
 * @return array Liste des oeuvres
 */
function getOeuvresBySalle(int $id_salle): array {
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT * FROM oeuvres WHERE id_salle = :id ORDER BY id_oeuvre");
    $stmt->bindValue(':id', $id_salle, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

/**
 * Compte le nombre d'oeuvres dans une salle.
 * 
 * @param int $id_salle Identifiant de la salle
 * @return int Nombre d'oeuvres
 */
function countOeuvresBySalle(int $id_salle): int {
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM oeuvres WHERE id_salle = :id");
    $stmt->bindValue(':id', $id_salle, PDO::PARAM_INT);
    $stmt->execute();
    
    $resultat = $stmt->fetch();
    return (int)$resultat['total'];
}

/**
 * Récupère une inscription par son token.
 * 
 * @param string $token Token unique de l'inscription
 * @return array|null Données de l'inscription ou null si non trouvée
 */
function getInscriptionByToken(string $token): ?array {
    $pdo = getPDO();
    $stmt = $pdo->prepare("
        SELECT i.*, c.nom as categorie_nom, c.buffet_actif
        FROM inscriptions i
        JOIN categories c ON i.id_categorie = c.id_categorie
        WHERE i.token = :token
    ");
    $stmt->bindValue(':token', $token, PDO::PARAM_STR);
    $stmt->execute();
    
    $inscription = $stmt->fetch();
    return $inscription ?: null;
}

/**
 * Récupère les détails complets d'une réservation (inscription + créneau + salle).
 * 
 * @param string $token Token de l'inscription
 * @return array|null Données complètes ou null si non trouvée
 */
function getReservationComplete(string $token): ?array {
    $pdo = getPDO();
    $stmt = $pdo->prepare("
        SELECT 
            i.*,
            c.nom as categorie_nom,
            c.buffet_actif,
            cr.id_creneau,
            cr.date_creneau,
            cr.heure,
            s.id_salle,
            s.numero as salle_numero,
            s.nom as salle_nom
        FROM inscriptions i
        JOIN categories c ON i.id_categorie = c.id_categorie
        JOIN reservations r ON i.id_inscription = r.id_inscription
        JOIN creneaux cr ON r.id_creneau = cr.id_creneau
        JOIN salles s ON cr.id_salle = s.id_salle
        WHERE i.token = :token
    ");
    $stmt->bindValue(':token', $token, PDO::PARAM_STR);
    $stmt->execute();
    
    $reservation = $stmt->fetch();
    return $reservation ?: null;
}
