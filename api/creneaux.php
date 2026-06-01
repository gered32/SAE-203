<?php
/**
 * ============================================================================
 * SAE203 E-LLUSION - API Créneaux
 * ============================================================================
 * Endpoint JSON retournant les créneaux disponibles pour une salle donnée.
 * 
 * PARAMÈTRES (GET) :
 * - id_salle : identifiant de la salle (obligatoire)
 * 
 * RÉPONSE JSON :
 * [
 *   {
 *     "id_creneau": 1,
 *     "date_creneau": "2026-06-18",
 *     "date_formatee": "Jeudi 18 juin 2026",
 *     "heure": "15:00:00",
 *     "heure_formatee": "15h",
 *     "places_total": 12,
 *     "places_restantes": 10
 *   },
 *   ...
 * ]
 * 
 * SÉCURITÉ :
 * - Validation de l'ID salle (entier positif)
 * - Requête préparée pour éviter les injections SQL
 * ============================================================================
 */

// Désactiver l'affichage des erreurs en production
// En développement, on peut les garder pour debug
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Header JSON obligatoire
header('Content-Type: application/json; charset=utf-8');

// Empêcher la mise en cache
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Inclusion de la configuration et des fonctions
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// ============================================================================
// VALIDATION DES PARAMÈTRES
// ============================================================================

// Récupérer et valider l'ID de la salle
$id_salle = isset($_GET['id_salle']) ? intval($_GET['id_salle']) : 0;

// Vérifier que l'ID est valide (entier positif)
if ($id_salle <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode([
        'error' => true,
        'message' => 'ID de salle invalide. Veuillez fournir un entier positif.'
    ]);
    exit;
}

// ============================================================================
// RÉCUPÉRATION DES CRÉNEAUX AVEC CALCUL DES PLACES RESTANTES
// ============================================================================

try {
    $pdo = getPDO();
    
    /**
     * Requête SQL avec calcul des places restantes.
     * 
     * Explication de la requête :
     * 1. On sélectionne tous les créneaux de la salle demandée
     * 2. On fait une jointure LEFT JOIN avec la table reservations
     * 3. On fait une jointure avec inscriptions pour récupérer nb_personnes
     * 4. On calcule : places_restantes = places_total - SUM(nb_personnes)
     * 5. COALESCE gère le cas où il n'y a pas encore de réservation (retourne 0)
     * 6. GROUP BY pour agréger par créneau
     * 7. ORDER BY pour trier par date puis heure
     */
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
        ORDER BY c.date_creneau ASC, c.heure ASC
    ";
    
    // Préparation de la requête (protection contre injection SQL)
    $stmt = $pdo->prepare($sql);
    
    // Liaison du paramètre (type INT pour plus de sécurité)
    $stmt->bindValue(':id_salle', $id_salle, PDO::PARAM_INT);
    
    // Exécution de la requête
    $stmt->execute();
    
    // Récupération des résultats
    $creneaux = $stmt->fetchAll();
    
    // Vérifier si la salle existe (pas de créneaux = salle inexistante ou sans créneaux)
    if (empty($creneaux)) {
        // Vérifier si la salle existe
        $checkSalle = $pdo->prepare("SELECT id_salle FROM salles WHERE id_salle = :id");
        $checkSalle->bindValue(':id', $id_salle, PDO::PARAM_INT);
        $checkSalle->execute();
        
        if (!$checkSalle->fetch()) {
            http_response_code(404); // Not Found
            echo json_encode([
                'error' => true,
                'message' => 'Cette salle n\'existe pas.'
            ]);
            exit;
        }
    }
    
    // ========================================================================
    // FORMATAGE DES DONNÉES POUR LE FRONTEND
    // ========================================================================
    
    $creneauxFormates = [];
    
    foreach ($creneaux as $creneau) {
        $creneauxFormates[] = [
            'id_creneau' => (int) $creneau['id_creneau'],
            'date_creneau' => $creneau['date_creneau'],
            'date_formatee' => formaterDate($creneau['date_creneau']),
            'heure' => $creneau['heure'],
            'heure_formatee' => formaterHeure($creneau['heure']),
            'places_total' => (int) $creneau['places_total'],
            'places_restantes' => max(0, (int) $creneau['places_restantes']) // Jamais négatif
        ];
    }
    
    // ========================================================================
    // ENVOI DE LA RÉPONSE JSON
    // ========================================================================
    
    // Code 200 OK (par défaut)
    echo json_encode($creneauxFormates, JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    // Erreur de base de données
    http_response_code(500); // Internal Server Error
    
    // En production, ne pas afficher les détails de l'erreur
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        echo json_encode([
            'error' => true,
            'message' => 'Erreur de base de données : ' . $e->getMessage()
        ]);
    } else {
        echo json_encode([
            'error' => true,
            'message' => 'Une erreur est survenue. Veuillez réessayer.'
        ]);
    }
    exit;
    
} catch (Exception $e) {
    // Autre erreur
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Une erreur inattendue est survenue.'
    ]);
    exit;
}
