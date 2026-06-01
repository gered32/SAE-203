<?php
/**
 * ============================================================================
 * SAE203 E-LLUSION - Gestion de réservation
 * ============================================================================
 * Permet au visiteur de :
 * - Consulter sa réservation via son token unique
 * - Modifier ses informations (nom, prénom, catégorie, nb_personnes, créneau)
 * - Supprimer sa réservation
 * 
 * Accessible via : ma-reservation.php?token=XXX
 * 
 * SÉCURITÉ :
 * - Accès uniquement via token unique
 * - Protection CSRF sur les actions
 * - Transaction pour la modification (gestion jauge)
 * ============================================================================
 */

// Inclusion des fonctions
require_once 'includes/functions.php';

// ============================================================================
// RÉCUPÉRATION ET VALIDATION DU TOKEN
// ============================================================================

$token = isset($_GET['token']) ? trim($_GET['token']) : '';

// Vérifier que le token est fourni
if (empty($token)) {
    redirectAvecMessage('index.php', 'Lien de réservation invalide.', 'error');
}

// Récupérer les détails de la réservation
$reservation = getReservationComplete($token);

// Vérifier que la réservation existe
if (!$reservation) {
    redirectAvecMessage('index.php', 'Réservation non trouvée. Le lien est peut-être invalide ou la réservation a été supprimée.', 'error');
}

// ============================================================================
// RÉCUPÉRATION DES DONNÉES POUR LE FORMULAIRE
// ============================================================================

$pdo = getPDO();
$categories = getCategories();
$salles = getSalles();
$erreurs = [];
$succes = '';

// ============================================================================
// TRAITEMENT DES ACTIONS (POST)
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Vérification CSRF
    $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verifierCsrf($csrf_token)) {
        $erreurs[] = "Erreur de sécurité. Veuillez actualiser la page et réessayer.";
    } else {
        
        // ====================================================================
        // ACTION : SUPPRESSION
        // ====================================================================
        if (isset($_POST['action']) && $_POST['action'] === 'supprimer') {
            
            try {
                $pdo->beginTransaction();
                
                // Supprimer l'inscription (les réservations seront supprimées en cascade)
                $stmt = $pdo->prepare("DELETE FROM inscriptions WHERE token = :token");
                $stmt->bindValue(':token', $token, PDO::PARAM_STR);
                $stmt->execute();
                
                $pdo->commit();
                
                // Redirection avec message de succès
                redirectAvecMessage('index.php', 'Votre réservation a bien été supprimée.', 'success');
                
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $erreurs[] = "Erreur lors de la suppression. Veuillez réessayer.";
            }
        }
        
        // ====================================================================
        // ACTION : MODIFICATION
        // ====================================================================
        elseif (isset($_POST['action']) && $_POST['action'] === 'modifier') {
            
            // Récupération des données
            $nom = nettoyer($_POST['nom'] ?? '');
            $prenom = nettoyer($_POST['prenom'] ?? '');
            $id_categorie = intval($_POST['id_categorie'] ?? 0);
            $id_salle = intval($_POST['id_salle'] ?? 0);
            $id_creneau = intval($_POST['id_creneau'] ?? 0);
            $nb_personnes = intval($_POST['nb_personnes'] ?? 1);
            $buffet_jeudi = isset($_POST['buffet_jeudi']) ? 1 : 0;
            
            // Validation
            if (empty($nom) || strlen($nom) < 2) {
                $erreurs[] = "Le nom doit contenir au moins 2 caractères.";
            }
            
            if (empty($prenom) || strlen($prenom) < 2) {
                $erreurs[] = "Le prénom doit contenir au moins 2 caractères.";
            }
            
            if ($id_categorie <= 0) {
                $erreurs[] = "Veuillez sélectionner une catégorie.";
            }
            
            if ($id_salle <= 0) {
                $erreurs[] = "Veuillez sélectionner une salle.";
            }
            
            if ($id_creneau <= 0) {
                $erreurs[] = "Veuillez sélectionner un créneau.";
            }
            
            if ($nb_personnes < 1 || $nb_personnes > JAUGE_MAX) {
                $erreurs[] = "Le nombre de personnes doit être entre 1 et " . JAUGE_MAX . ".";
            }
            
            // Vérifier la catégorie pour le buffet
            $categorieData = null;
            if ($id_categorie > 0) {
                $stmt = $pdo->prepare("SELECT * FROM categories WHERE id_categorie = :id");
                $stmt->bindValue(':id', $id_categorie, PDO::PARAM_INT);
                $stmt->execute();
                $categorieData = $stmt->fetch();
                
                if ($buffet_jeudi && $categorieData && $categorieData['buffet_actif'] == 0) {
                    $erreurs[] = "Votre catégorie ne permet pas de participer au buffet.";
                    $buffet_jeudi = 0;
                }
            }
            
            // Si pas d'erreur, procéder à la modification
            if (empty($erreurs)) {
                
                try {
                    $pdo->beginTransaction();
                    
                    // Calculer les places disponibles en tenant compte de la libération des places actuelles
                    $ancienCreneau = $reservation['id_creneau'];
                    $ancienNbPersonnes = $reservation['nb_personnes'];
                    
                    // Si changement de créneau ou de nombre de personnes
                    if ($id_creneau != $ancienCreneau || $nb_personnes != $ancienNbPersonnes) {
                        
                        // Calculer les places restantes du nouveau créneau
                        $sqlPlaces = "
                            SELECT 
                                c.places_total - COALESCE(SUM(i.nb_personnes), 0) AS places_restantes
                            FROM creneaux c
                            LEFT JOIN reservations r ON c.id_creneau = r.id_creneau
                            LEFT JOIN inscriptions i ON r.id_inscription = i.id_inscription
                            WHERE c.id_creneau = :id_creneau
                            GROUP BY c.id_creneau
                        ";
                        
                        $stmtPlaces = $pdo->prepare($sqlPlaces);
                        $stmtPlaces->bindValue(':id_creneau', $id_creneau, PDO::PARAM_INT);
                        $stmtPlaces->execute();
                        $resultatPlaces = $stmtPlaces->fetch();
                        
                        $placesRestantes = $resultatPlaces ? (int)$resultatPlaces['places_restantes'] : JAUGE_MAX;
                        
                        // Si c'est le même créneau, on récupère nos propres places
                        if ($id_creneau == $ancienCreneau) {
                            $placesRestantes += $ancienNbPersonnes;
                        }
                        
                        // Vérifier s'il y a assez de places
                        if ($placesRestantes < $nb_personnes) {
                            $pdo->rollBack();
                            $erreurs[] = "Désolé, il ne reste que {$placesRestantes} place(s) pour ce créneau.";
                        }
                    }
                    
                    if (empty($erreurs)) {
                        // Mise à jour de l'inscription
                        $sqlUpdate = "
                            UPDATE inscriptions SET
                                nom = :nom,
                                prenom = :prenom,
                                id_categorie = :id_categorie,
                                nb_personnes = :nb_personnes,
                                buffet_jeudi = :buffet_jeudi
                            WHERE token = :token
                        ";
                        
                        $stmtUpdate = $pdo->prepare($sqlUpdate);
                        $stmtUpdate->bindValue(':nom', $nom, PDO::PARAM_STR);
                        $stmtUpdate->bindValue(':prenom', $prenom, PDO::PARAM_STR);
                        $stmtUpdate->bindValue(':id_categorie', $id_categorie, PDO::PARAM_INT);
                        $stmtUpdate->bindValue(':nb_personnes', $nb_personnes, PDO::PARAM_INT);
                        $stmtUpdate->bindValue(':buffet_jeudi', $buffet_jeudi, PDO::PARAM_INT);
                        $stmtUpdate->bindValue(':token', $token, PDO::PARAM_STR);
                        $stmtUpdate->execute();
                        
                        // Si changement de créneau, mettre à jour la réservation
                        if ($id_creneau != $ancienCreneau) {
                            $sqlReservation = "
                                UPDATE reservations SET id_creneau = :id_creneau
                                WHERE id_inscription = :id_inscription
                            ";
                            
                            $stmtReservation = $pdo->prepare($sqlReservation);
                            $stmtReservation->bindValue(':id_creneau', $id_creneau, PDO::PARAM_INT);
                            $stmtReservation->bindValue(':id_inscription', $reservation['id_inscription'], PDO::PARAM_INT);
                            $stmtReservation->execute();
                        }
                        
                        $pdo->commit();
                        
                        // Recharger les données
                        $reservation = getReservationComplete($token);
                        $succes = "Votre réservation a bien été modifiée.";
                        
                        // Régénérer le token CSRF
                        regenererCsrf();
                    }
                    
                } catch (PDOException $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    if (DEBUG_MODE) {
                        $erreurs[] = "Erreur : " . $e->getMessage();
                    } else {
                        $erreurs[] = "Erreur lors de la modification. Veuillez réessayer.";
                    }
                }
            }
        }
    }
}

// ============================================================================
// CONFIGURATION DE LA PAGE
// ============================================================================
$pageTitle = "Ma réservation";
$pageDescription = "Gérez votre réservation pour l'exposition E-LLUSION.";
$pageActive = "";

// Inclusion du header
require_once 'includes/header.php';

// Formatage pour l'affichage
$dateFormatee = formaterDate($reservation['date_creneau']);
$heureFormatee = formaterHeure($reservation['heure']);
?>

<!-- ================================================================
     EN-TÊTE DE PAGE
     ================================================================ -->
<section class="page-header text-center">
    <h1>Ma réservation</h1>
    <p class="page-subtitle">
        Consultez, modifiez ou annulez votre réservation pour l'exposition E-LLUSION.
    </p>
</section>

<!-- ================================================================
     MESSAGES
     ================================================================ -->
<?php if (!empty($succes)): ?>
<div class="flash-message flash-success" role="alert">
    <div class="flash-content">
        <p><?php echo sanitize($succes); ?></p>
        <button type="button" class="flash-close" aria-label="Fermer">&times;</button>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($erreurs)): ?>
<div class="flash-message flash-error" role="alert">
    <div class="flash-content">
        <ul style="margin: 0; padding-left: 1rem;">
            <?php foreach ($erreurs as $erreur): ?>
            <li><?php echo sanitize($erreur); ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="flash-close" aria-label="Fermer">&times;</button>
    </div>
</div>
<?php endif; ?>

<!-- ================================================================
     RÉCAPITULATIF ACTUEL
     ================================================================ -->
<section class="recap-actuel">
    <h2>Votre réservation actuelle</h2>
    
    <div class="recap-card">
        <div class="recap-header">
            <span class="recap-salle">Salle <?php echo sanitize($reservation['salle_numero']); ?></span>
            <span class="recap-date"><?php echo $dateFormatee; ?> à <?php echo $heureFormatee; ?></span>
        </div>
        <div class="recap-body">
            <p><strong><?php echo sanitize($reservation['prenom'] . ' ' . $reservation['nom']); ?></strong></p>
            <p><?php echo sanitize($reservation['email']); ?></p>
            <p><?php echo sanitize($reservation['categorie_nom']); ?> - <?php echo $reservation['nb_personnes']; ?> personne(s)</p>
            <?php if ($reservation['buffet_jeudi']): ?>
            <p class="buffet-badge">Participe au buffet</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ================================================================
     FORMULAIRE DE MODIFICATION
     ================================================================ -->
<section class="modification-section">
    <h2>Modifier ma réservation</h2>
    
    <form method="POST" action="ma-reservation.php?token=<?php echo urlencode($token); ?>" data-validate>
        <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
        <input type="hidden" name="action" value="modifier">
        
        <fieldset>
            <legend>Informations personnelles</legend>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="nom" class="form-label form-label-required">Nom</label>
                    <input type="text" id="nom" name="nom" class="form-input" 
                           value="<?php echo sanitize($reservation['nom']); ?>" required maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="prenom" class="form-label form-label-required">Prénom</label>
                    <input type="text" id="prenom" name="prenom" class="form-input" 
                           value="<?php echo sanitize($reservation['prenom']); ?>" required maxlength="100">
                </div>
            </div>
            
            <div class="form-group">
                <label for="id_categorie" class="form-label form-label-required">Catégorie</label>
                <select id="id_categorie" name="id_categorie" class="form-select" required>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id_categorie']; ?>"
                            data-buffet="<?php echo $cat['buffet_actif']; ?>"
                            <?php echo $reservation['id_categorie'] == $cat['id_categorie'] ? 'selected' : ''; ?>>
                        <?php echo sanitize($cat['nom']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </fieldset>
        
        <fieldset>
            <legend>Créneau de visite</legend>
            
            <div class="form-group">
                <label for="id_salle" class="form-label form-label-required">Salle</label>
                <select id="id_salle" name="id_salle" class="form-select" required>
                    <?php foreach ($salles as $salle): ?>
                    <option value="<?php echo $salle['id_salle']; ?>"
                            <?php echo $reservation['id_salle'] == $salle['id_salle'] ? 'selected' : ''; ?>>
                        Salle <?php echo sanitize($salle['numero']); ?> - <?php echo sanitize($salle['nom']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="id_creneau" class="form-label form-label-required">Créneau</label>
                <select id="id_creneau" name="id_creneau" class="form-select" required>
                    <option value="<?php echo $reservation['id_creneau']; ?>" selected>
                        <?php echo $dateFormatee; ?> - <?php echo $heureFormatee; ?> (créneau actuel)
                    </option>
                </select>
                <span class="form-hint">Changez de salle pour voir les autres créneaux disponibles.</span>
            </div>
            
            <div class="form-group">
                <label for="nb_personnes" class="form-label form-label-required">Nombre de personnes</label>
                <input type="number" id="nb_personnes" name="nb_personnes" class="form-input" 
                       value="<?php echo $reservation['nb_personnes']; ?>" 
                       min="1" max="<?php echo JAUGE_MAX; ?>" required>
            </div>
        </fieldset>
        
        <fieldset>
            <legend>Buffet</legend>
            
            <div class="buffet-container">
                <label class="form-checkbox">
                    <input type="checkbox" id="buffet_jeudi" name="buffet_jeudi" value="1"
                           <?php echo $reservation['buffet_jeudi'] ? 'checked' : ''; ?>
                           <?php echo $reservation['buffet_actif'] ? '' : 'disabled'; ?>>
                    <span>Je participe au buffet du jeudi 18 juin à 18h30</span>
                </label>
            </div>
        </fieldset>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block">
                Enregistrer les modifications
            </button>
        </div>
    </form>
</section>

<!-- ================================================================
     SUPPRESSION
     ================================================================ -->
<section class="suppression-section">
    <h2>Annuler ma réservation</h2>
    
    <p>
        Si vous ne pouvez plus venir, vous pouvez annuler votre réservation. 
        Cette action est <strong>irréversible</strong> et libérera vos places pour d'autres visiteurs.
    </p>
    
    <form method="POST" action="ma-reservation.php?token=<?php echo urlencode($token); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
        <input type="hidden" name="action" value="supprimer">
        
        <button type="submit" class="btn btn-danger" 
                data-confirm="Êtes-vous sûr de vouloir supprimer votre réservation ? Cette action est irréversible.">
            Supprimer ma réservation
        </button>
    </form>
</section>

<!-- ================================================================
     RETOUR
     ================================================================ -->
<section class="text-center mt-4 mb-4">
    <a href="index.php" class="btn btn-secondary">Retour à l'accueil</a>
</section>

<style>
    .page-header {
        padding: var(--spacing-xl) var(--spacing-md);
        margin-bottom: var(--spacing-lg);
    }
    
    .page-subtitle {
        max-width: 600px;
        margin: 0 auto;
        color: var(--gris-fonce);
    }
    
    .recap-actuel, .modification-section, .suppression-section {
        max-width: 700px;
        margin: 0 auto var(--spacing-xl);
    }
    
    .recap-card {
        background: var(--cyan-clair);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
    }
    
    .recap-header {
        background: var(--cyan-fonce);
        color: var(--blanc);
        padding: var(--spacing-md);
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: var(--spacing-sm);
    }
    
    .recap-salle {
        font-weight: 700;
    }
    
    .recap-body {
        padding: var(--spacing-lg);
    }
    
    .recap-body p {
        margin-bottom: var(--spacing-xs);
    }
    
    .buffet-badge {
        display: inline-block;
        background: var(--rouge);
        color: var(--blanc);
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius);
        font-size: var(--font-size-small);
        margin-top: var(--spacing-sm);
    }
    
    .modification-section {
        background: var(--blanc);
        padding: var(--spacing-xl);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow);
    }
    
    .suppression-section {
        background: #fff5f5;
        padding: var(--spacing-xl);
        border-radius: var(--border-radius-lg);
        border: 2px solid var(--rouge);
    }
    
    .suppression-section h2 {
        color: var(--rouge);
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--spacing-md);
    }
    
    @media (max-width: 600px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php require_once 'includes/footer.php'; ?>
