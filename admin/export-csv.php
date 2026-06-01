<?php
/**
 * ============================================================================
 * SAE203 E-LLUSION - Export CSV des inscriptions
 * ============================================================================
 * Génère un fichier CSV téléchargeable contenant toutes les inscriptions.
 * Ce fichier sert de "tableur partagé" pour :
 * - Le tuteur/tutrice
 * - Le client
 * - Le chef de département
 * - Le responsable projet
 * 
 * FORMAT :
 * - Encodage UTF-8 avec BOM (compatible Excel)
 * - Séparateur point-virgule (standard français)
 * - Nom du fichier : inscriptions_ellusion_AAAA-MM-JJ.csv
 * 
 * ACCÈS : Réservé aux utilisateurs authentifiés
 * ============================================================================
 */

// Inclusion des fonctions
require_once __DIR__ . '/../includes/functions.php';

// Protection de la page
protegerPageAdmin();

// ============================================================================
// RÉCUPÉRATION DES DONNÉES
// ============================================================================

$pdo = getPDO();

$sql = "
    SELECT 
        i.date_inscription,
        i.nom,
        i.prenom,
        i.email,
        cat.nom AS categorie,
        s.numero AS salle_numero,
        s.nom AS salle_nom,
        cr.date_creneau,
        cr.heure,
        i.nb_personnes,
        CASE WHEN i.buffet_jeudi = 1 THEN 'Oui' ELSE 'Non' END AS buffet
    FROM inscriptions i
    JOIN categories cat ON i.id_categorie = cat.id_categorie
    JOIN reservations r ON i.id_inscription = r.id_inscription
    JOIN creneaux cr ON r.id_creneau = cr.id_creneau
    JOIN salles s ON cr.id_salle = s.id_salle
    ORDER BY i.date_inscription DESC
";

$inscriptions = $pdo->query($sql)->fetchAll();

// ============================================================================
// GÉNÉRATION DU FICHIER CSV
// ============================================================================

// Nom du fichier avec la date du jour
$nomFichier = 'inscriptions_ellusion_' . date('Y-m-d') . '.csv';

// Headers HTTP pour forcer le téléchargement
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nomFichier . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Ouvrir le flux de sortie
$output = fopen('php://output', 'w');

// ============================================================================
// BOM UTF-8
// ============================================================================
// Le BOM (Byte Order Mark) permet à Excel de reconnaître l'encodage UTF-8
// Sans lui, les caractères accentués seraient mal affichés dans Excel
echo "\xEF\xBB\xBF";

// ============================================================================
// EN-TÊTES DU CSV
// ============================================================================
$entetes = [
    'Date inscription',
    'Nom',
    'Prénom',
    'Email',
    'Catégorie',
    'Salle (numéro)',
    'Salle (nom)',
    'Date créneau',
    'Heure',
    'Nb personnes',
    'Buffet'
];

// Écriture des en-têtes avec point-virgule comme séparateur
fputcsv($output, $entetes, ';');

// ============================================================================
// DONNÉES
// ============================================================================
foreach ($inscriptions as $inscription) {
    $ligne = [
        // Date d'inscription formatée
        date('d/m/Y H:i', strtotime($inscription['date_inscription'])),
        
        // Informations personnelles
        $inscription['nom'],
        $inscription['prenom'],
        $inscription['email'],
        
        // Catégorie
        $inscription['categorie'],
        
        // Salle
        $inscription['salle_numero'],
        $inscription['salle_nom'],
        
        // Créneau
        date('d/m/Y', strtotime($inscription['date_creneau'])),
        date('H:i', strtotime($inscription['heure'])),
        
        // Nombre de personnes
        $inscription['nb_personnes'],
        
        // Buffet
        $inscription['buffet']
    ];
    
    // Écriture de la ligne
    fputcsv($output, $ligne, ';');
}

// Fermeture du flux
fclose($output);

// Arrêt du script (important pour éviter tout contenu supplémentaire)
exit;
