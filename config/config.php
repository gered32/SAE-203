<?php
/**
 * ============================================================================
 * SAE203 E-LLUSION - Fichier de configuration
 * ============================================================================
 * Ce fichier centralise toute la configuration du site :
 * - Paramètres de connexion à la base de données
 * - Constantes globales du projet
 * - Fonction de connexion PDO sécurisée
 * 
 * IMPORTANT : En production, modifiez les identifiants de connexion !
 * ============================================================================
 */

// ============================================================================
// DÉTECTION DE L'ENVIRONNEMENT (LOCAL ou PRODUCTION)
// ============================================================================
// Le site détecte automatiquement s'il est en local ou sur OVH

// Détection robuste de l'environnement local
$estEnLocal = (
    in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']) ||
    strpos($_SERVER['HTTP_HOST'], '.local') !== false ||
    strpos($_SERVER['HTTP_HOST'], '.test') !== false ||
    strpos($_SERVER['HTTP_HOST'], '192.168.') === 0 ||
    strpos($_SERVER['HTTP_HOST'], '10.') === 0
);

// ============================================================================
// CONFIGURATION DE LA BASE DE DONNÉES
// ============================================================================

if ($estEnLocal) {
    // ====== CONFIGURATION LOCALE (XAMPP) ======
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'sae203_ellusion');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    
    // Détection automatique de l'URL du site (compatible tous environnements)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    
    // Si on est dans un sous-dossier (admin, api, etc.), remonter au dossier parent
    if (basename($scriptPath) === 'admin' || basename($scriptPath) === 'api') {
        $scriptPath = dirname($scriptPath);
    }
    
    $scriptPath = rtrim($scriptPath, '/');
    define('SITE_URL', $protocol . '://' . $_SERVER['HTTP_HOST'] . $scriptPath);
    
    define('DEBUG_MODE', true);
    
} else {
    // ====== CONFIGURATION OVH (PRODUCTION) ======
    // À REMPLIR avec vos identifiants OVH quand vous aurez créé la BDD
    define('DB_HOST', 'VOTRE_SERVEUR_MYSQL.mysql.db');  // Ex: ljtebow.mysql.db
    define('DB_NAME', 'ljtebow_ellusion');               // Nom de votre BDD sur OVH
    define('DB_USER', 'ljtebow');                        // Utilisateur MySQL OVH
    define('DB_PASS', 'VOTRE_MOT_DE_PASSE_MYSQL');       // Mot de passe MySQL OVH
    define('SITE_URL', 'https://VOTRE_DOMAINE.ovh');     // URL de votre site
    define('DEBUG_MODE', false);  // IMPORTANT : false en production !
}

define('DB_CHARSET', 'utf8mb4');

// Nom du site affiché dans les titres de page
define('SITE_NAME', 'E-LLUSION');

// Email du référent du projet (affiché dans le formulaire d'inscription)
define('EMAIL_REFERENT', 'referent@univ-smb.fr');

// Capacité maximale par créneau (jauge)
define('JAUGE_MAX', 12);

// Mode debug : défini automatiquement selon l'environnement (voir plus haut)

// ============================================================================
// CONFIGURATION DES CHEMINS
// ============================================================================

// Chemin absolu vers la racine du projet
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

// Chemin vers le dossier des logs
define('LOGS_PATH', ROOT_PATH . 'logs' . DIRECTORY_SEPARATOR);

// Chemin vers le dossier des images
define('IMAGES_PATH', ROOT_PATH . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR);

// ============================================================================
// FONCTION DE CONNEXION PDO (SINGLETON)
// ============================================================================
/**
 * Retourne une instance unique de connexion PDO à la base de données.
 * Utilise le pattern Singleton pour éviter les connexions multiples.
 * 
 * Configuration de sécurité :
 * - PDO::ATTR_ERRMODE => ERRMODE_EXCEPTION : lance des exceptions en cas d'erreur
 * - PDO::ATTR_DEFAULT_FETCH_MODE => FETCH_ASSOC : retourne des tableaux associatifs
 * - PDO::ATTR_EMULATE_PREPARES => false : désactive l'émulation des requêtes préparées
 * 
 * @return PDO Instance de connexion à la base de données
 */
function getPDO(): PDO {
    // Variable statique : conserve sa valeur entre les appels
    static $pdo = null;
    
    // Si la connexion n'existe pas encore, on la crée
    if ($pdo === null) {
        try {
            // Construction du DSN (Data Source Name)
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            
            // Options de configuration PDO
            $options = [
                // Mode d'erreur : lance des exceptions PDOException
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                
                // Mode de récupération par défaut : tableaux associatifs
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                
                // Désactive l'émulation des requêtes préparées
                // Plus sécurisé car c'est MySQL qui prépare vraiment la requête
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            // Création de la connexion PDO
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            // En mode debug, affiche l'erreur détaillée
            if (DEBUG_MODE) {
                die('Erreur de connexion à la base de données : ' . $e->getMessage());
            } else {
                // En production, message générique (sécurité)
                die('Une erreur est survenue. Veuillez réessayer plus tard.');
            }
        }
    }
    
    return $pdo;
}

// ============================================================================
// CONFIGURATION DE LA SESSION PHP
// ============================================================================
/**
 * Démarre la session PHP de manière sécurisée.
 * À appeler au début de chaque page nécessitant les sessions.
 */
function demarrerSession(): void {
    // Vérifie si la session n'est pas déjà démarrée
    if (session_status() === PHP_SESSION_NONE) {
        // Configuration de la session avant démarrage
        ini_set('session.use_strict_mode', 1);      // Mode strict
        ini_set('session.use_only_cookies', 1);     // Cookies uniquement
        ini_set('session.cookie_httponly', 1);      // Cookie non accessible en JS
        
        // Démarre la session
        session_start();
    }
}

// ============================================================================
// GESTION DES ERREURS EN DÉVELOPPEMENT
// ============================================================================
if (DEBUG_MODE) {
    // Affiche toutes les erreurs en développement
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // Cache les erreurs en production (sécurité)
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ============================================================================
// FUSEAU HORAIRE
// ============================================================================
date_default_timezone_set('Europe/Paris');
