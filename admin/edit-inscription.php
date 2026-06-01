<?php
/**
 * ============================================================================
 * SAE203 E-LLUSION - Édition d'une inscription
 * ============================================================================
 * Permet à l'administrateur de modifier les informations d'une inscription
 * 
 * ACCÈS : Réservé aux utilisateurs authentifiés (admin/referent)
 * ============================================================================
 */

// Inclusion des fonctions
require_once __DIR__ . '/../includes/functions.php';

// Protection de la page
protegerPageAdmin();

// Configuration de la page
$pageTitle = "Modifier une inscription";
$pageDescription = "Modification d'une inscription E-LLUSION.";
$pageActive = "";

// ============================================================================
// RÉCUPÉRATION DE L'INSCRIPTION À MODIFIER
// ============================================================================

$pdo = getPDO();
$errors = [];
$success = '';

// Vérifier que l'ID est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect(SITE_URL . '/admin/dashboard.php');
}

$id_inscription = (int)$_GET['id'];

// Récupérer l'inscription avec toutes les infos
$sqlInscription = "
    SELECT 
        i.*,
        r.id_creneau,
        cr.id_salle,
        cr.date_creneau,
        cr.heure
    FROM inscriptions i
    LEFT JOIN reservations r ON i.id_inscription = r.id_inscription
    LEFT JOIN creneaux cr ON r.id_creneau = cr.id_creneau
    WHERE i.id_inscription = :id
";
$stmt = $pdo->prepare($sqlInscription);
$stmt->execute(['id' => $id_inscription]);
$inscription = $stmt->fetch();

// Si l'inscription n'existe pas, rediriger
if (!$inscription) {
    redirect(SITE_URL . '/admin/dashboard.php');
}

// ============================================================================
// TRAITEMENT DU FORMULAIRE
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $id_categorie = (int)($_POST['id_categorie'] ?? 0);
    $nb_personnes = (int)($_POST['nb_personnes'] ?? 1);
    $buffet_jeudi = isset($_POST['buffet_jeudi']) ? 1 : 0;
    $id_creneau = (int)($_POST['id_creneau'] ?? 0);
    
    // Validation
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire.";
    }
    if (empty($prenom)) {
        $errors[] = "Le prénom est obligatoire.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email est invalide.";
    }
    if ($id_categorie <= 0) {
        $errors[] = "Veuillez sélectionner une catégorie.";
    }
    if ($nb_personnes < 1 || $nb_personnes > 10) {
        $errors[] = "Le nombre de personnes doit être entre 1 et 10.";
    }
    if ($id_creneau <= 0) {
        $errors[] = "Veuillez sélectionner un créneau.";
    }
    
    // Vérifier la disponibilité du créneau si changé
    if (empty($errors) && $id_creneau != $inscription['id_creneau']) {
        $sqlDisponibilite = "
            SELECT 
                c.places_total - COALESCE(SUM(i.nb_personnes), 0) AS places_restantes
            FROM creneaux c
            LEFT JOIN reservations r ON c.id_creneau = r.id_creneau
            LEFT JOIN inscriptions i ON r.id_inscription = i.id_inscription
            WHERE c.id_creneau = :id_creneau
            GROUP BY c.id_creneau
        ";
        $stmtDispo = $pdo->prepare($sqlDisponibilite);
        $stmtDispo->execute(['id_creneau' => $id_creneau]);
        $dispo = $stmtDispo->fetch();
        
        if (!$dispo || $dispo['places_restantes'] < $nb_personnes) {
            $errors[] = "Le créneau sélectionné n'a pas assez de places disponibles.";
        }
    }
    
    // Si pas d'erreurs, mettre à jour
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Mettre à jour l'inscription
            $sqlUpdate = "
                UPDATE inscriptions 
                SET nom = :nom,
                    prenom = :prenom,
                    email = :email,
                    id_categorie = :id_categorie,
                    nb_personnes = :nb_personnes,
                    buffet_jeudi = :buffet_jeudi
                WHERE id_inscription = :id
            ";
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute([
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'id_categorie' => $id_categorie,
                'nb_personnes' => $nb_personnes,
                'buffet_jeudi' => $buffet_jeudi,
                'id' => $id_inscription
            ]);
            
            // Mettre à jour la réservation si le créneau a changé
            if ($id_creneau != $inscription['id_creneau']) {
                $sqlUpdateReservation = "
                    UPDATE reservations 
                    SET id_creneau = :id_creneau
                    WHERE id_inscription = :id_inscription
                ";
                $stmtUpdateReservation = $pdo->prepare($sqlUpdateReservation);
                $stmtUpdateReservation->execute([
                    'id_creneau' => $id_creneau,
                    'id_inscription' => $id_inscription
                ]);
            }
            
            $pdo->commit();
            $success = "L'inscription a été modifiée avec succès.";
            
            // Recharger les données
            $stmt->execute(['id' => $id_inscription]);
            $inscription = $stmt->fetch();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
}

// ============================================================================
// RÉCUPÉRATION DES DONNÉES POUR LES FORMULAIRES
// ============================================================================

// Catégories
$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();

// Créneaux disponibles groupés par date et salle
$creneaux = $pdo->query("
    SELECT 
        cr.id_creneau,
        cr.date_creneau,
        cr.heure,
        cr.places_total,
        s.numero AS salle_numero,
        s.nom AS salle_nom,
        COALESCE(SUM(i.nb_personnes), 0) AS places_occupees,
        cr.places_total - COALESCE(SUM(i.nb_personnes), 0) AS places_restantes
    FROM creneaux cr
    JOIN salles s ON cr.id_salle = s.id_salle
    LEFT JOIN reservations r ON cr.id_creneau = r.id_creneau
    LEFT JOIN inscriptions i ON r.id_inscription = i.id_inscription
    GROUP BY cr.id_creneau
    ORDER BY cr.date_creneau, cr.heure, s.numero
")->fetchAll();

// Inclusion du header
require_once __DIR__ . '/../includes/header.php';
?>

<!-- ================================================================
     EN-TÊTE
     ================================================================ -->
<div class="admin-header">
    <div>
        <h1 class="admin-title">Modifier une inscription</h1>
        <p>Inscription #<?php echo $id_inscription; ?> - <?php echo sanitize($inscription['nom'] . ' ' . $inscription['prenom']); ?></p>
    </div>
    <div class="admin-actions">
        <a href="dashboard.php" class="btn btn-secondary">
            Retour au dashboard
        </a>
    </div>
</div>

<!-- ================================================================
     MESSAGES
     ================================================================ -->
<?php if (!empty($errors)): ?>
<div class="alert alert-error">
    <strong>Erreur(s) :</strong>
    <ul>
        <?php foreach ($errors as $error): ?>
            <li><?php echo sanitize($error); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success">
    <?php echo sanitize($success); ?>
</div>
<?php endif; ?>

<!-- ================================================================
     FORMULAIRE DE MODIFICATION
     ================================================================ -->
<section class="form-section">
    <form method="POST" class="form-inscription">
        
        <!-- Informations personnelles -->
        <fieldset class="form-fieldset">
            <legend>Informations personnelles</legend>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="nom" class="required">Nom</label>
                    <input type="text" id="nom" name="nom" 
                           value="<?php echo sanitize($inscription['nom']); ?>" 
                           required class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="prenom" class="required">Prénom</label>
                    <input type="text" id="prenom" name="prenom" 
                           value="<?php echo sanitize($inscription['prenom']); ?>" 
                           required class="form-input">
                </div>
            </div>
            
            <div class="form-group">
                <label for="email" class="required">Email</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo sanitize($inscription['email']); ?>" 
                       required class="form-input">
            </div>
            
            <div class="form-group">
                <label for="id_categorie" class="required">Catégorie</label>
                <select id="id_categorie" name="id_categorie" required class="form-select">
                    <option value="">-- Sélectionnez --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id_categorie']; ?>"
                                <?php echo $cat['id_categorie'] == $inscription['id_categorie'] ? 'selected' : ''; ?>>
                            <?php echo sanitize($cat['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </fieldset>
        
        <!-- Réservation -->
        <fieldset class="form-fieldset">
            <legend>Réservation</legend>
            
            <div class="form-group">
                <label for="id_creneau" class="required">Créneau de visite</label>
                <select id="id_creneau" name="id_creneau" required class="form-select">
                    <option value="">-- Sélectionnez un créneau --</option>
                    <?php 
                    $currentDate = '';
                    foreach ($creneaux as $creneau): 
                        // Séparateur de date
                        if ($creneau['date_creneau'] !== $currentDate):
                            if ($currentDate !== '') echo '</optgroup>';
                            $currentDate = $creneau['date_creneau'];
                            echo '<optgroup label="' . formaterDate($creneau['date_creneau']) . '">';
                        endif;
                        
                        $isComplet = $creneau['places_restantes'] <= 0;
                        $isCurrent = $creneau['id_creneau'] == $inscription['id_creneau'];
                        $disabled = $isComplet && !$isCurrent ? 'disabled' : '';
                        $selected = $isCurrent ? 'selected' : '';
                        
                        $label = sprintf(
                            'Salle %s - %s (%s places)',
                            $creneau['salle_numero'],
                            formaterHeure($creneau['heure']),
                            $isComplet ? 'COMPLET' : $creneau['places_restantes'] . ' dispos'
                        );
                    ?>
                        <option value="<?php echo $creneau['id_creneau']; ?>" 
                                <?php echo $disabled . ' ' . $selected; ?>>
                            <?php echo sanitize($label); ?>
                        </option>
                    <?php endforeach; ?>
                    <?php if ($currentDate !== '') echo '</optgroup>'; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="nb_personnes" class="required">Nombre de personnes</label>
                <input type="number" id="nb_personnes" name="nb_personnes" 
                       min="1" max="10" 
                       value="<?php echo $inscription['nb_personnes']; ?>" 
                       required class="form-input">
                <small class="form-help">Entre 1 et 10 personnes</small>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" id="buffet_jeudi" name="buffet_jeudi" 
                           <?php echo $inscription['buffet_jeudi'] ? 'checked' : ''; ?>>
                    Participation au buffet jeudi 12 juin
                </label>
            </div>
        </fieldset>
        
        <!-- Boutons -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                Enregistrer les modifications
            </button>
            <a href="dashboard.php" class="btn btn-secondary">
                Annuler
            </a>
        </div>
        
    </form>
</section>

<style>
    .admin-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: flex-start;
        gap: var(--spacing-md);
        margin-bottom: var(--spacing-xl);
        padding-bottom: var(--spacing-lg);
        border-bottom: 2px solid var(--cyan-clair);
    }
    
    .admin-header p {
        color: var(--gris);
        margin: 0;
    }
    
    .admin-actions {
        display: flex;
        gap: var(--spacing-sm);
        flex-wrap: wrap;
    }
    
    .form-section {
        max-width: 800px;
        margin: 0 auto;
    }
    
    .form-fieldset {
        background: var(--blanc);
        border: 1px solid var(--gris-clair);
        border-radius: var(--border-radius);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-lg);
    }
    
    .form-fieldset legend {
        font-size: var(--font-size-lg);
        font-weight: 700;
        color: var(--cyan-fonce);
        padding: 0 var(--spacing-sm);
    }
    
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-md);
    }
    
    .form-group {
        margin-bottom: var(--spacing-md);
    }
    
    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: var(--spacing-xs);
        color: var(--noir);
    }
    
    .form-group label.required::after {
        content: ' *';
        color: var(--rouge);
    }
    
    .form-input,
    .form-select {
        width: 100%;
        padding: var(--spacing-sm) var(--spacing-md);
        border: 1px solid var(--gris);
        border-radius: var(--border-radius);
        font-size: var(--font-size-base);
        font-family: inherit;
    }
    
    .form-input:focus,
    .form-select:focus {
        outline: none;
        border-color: var(--cyan-fonce);
        box-shadow: 0 0 0 3px rgba(27, 119, 175, 0.1);
    }
    
    .form-help {
        display: block;
        margin-top: var(--spacing-xs);
        color: var(--gris);
        font-size: var(--font-size-small);
    }
    
    .checkbox-label {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        cursor: pointer;
        font-weight: normal;
    }
    
    .checkbox-label input[type="checkbox"] {
        width: auto;
        cursor: pointer;
    }
    
    .form-actions {
        display: flex;
        gap: var(--spacing-md);
        justify-content: flex-start;
        margin-top: var(--spacing-xl);
    }
    
    .alert {
        padding: var(--spacing-md) var(--spacing-lg);
        border-radius: var(--border-radius);
        margin-bottom: var(--spacing-lg);
    }
    
    .alert-error {
        background: #fee;
        border: 1px solid var(--rouge);
        color: #900;
    }
    
    .alert-success {
        background: #efe;
        border: 1px solid #0a0;
        color: #060;
    }
    
    .alert ul {
        margin: var(--spacing-sm) 0 0 var(--spacing-lg);
    }
    
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .form-actions .btn {
            width: 100%;
        }
    }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
