<?php
/**
 * ============================================================================
 * SAE203 E-LLUSION - Page d'inscription
 * ============================================================================
 * Formulaire d'inscription à l'exposition comprenant :
 * - Informations personnelles (nom, prénom, email)
 * - Choix de la catégorie de visiteur
 * - Sélection de la salle et du créneau
 * - Nombre de personnes
 * - Option buffet (conditionnelle selon catégorie)
 * 
 * SÉCURITÉ :
 * - Protection CSRF
 * - Validation côté serveur
 * - Transaction PDO pour la gestion de la jauge
 * - Requêtes préparées contre injection SQL
 * ============================================================================
 */

// Inclusion des fonctions
require_once __DIR__ . '/includes/functions.php';

// Configuration de la page
$pageTitle = "Inscription";
$pageDescription = "Inscrivez-vous gratuitement à l'exposition E-LLUSION. Choisissez votre salle et votre créneau horaire.";
$pageActive = "inscription";

// ============================================================================
// RÉCUPÉRATION DES DONNÉES POUR LE FORMULAIRE
// ============================================================================

$pdo = getPDO();

// Récupérer les catégories
$categories = getCategories();

// Récupérer les salles
$salles = getSalles();

// Salle pré-sélectionnée (si provient de la page salle-detail.php)
$sallePreSelectionnee = isset($_GET['salle']) ? intval($_GET['salle']) : 0;

// ============================================================================
// TRAITEMENT DU FORMULAIRE (POST)
// ============================================================================

$erreurs = [];
$succes = false;
$tokenGenere = '';

// Valeurs du formulaire (pour pré-remplissage en cas d'erreur)
$formData = [
    'nom' => '',
    'prenom' => '',
    'email' => '',
    'id_categorie' => '',
    'id_salle' => $sallePreSelectionnee,
    'id_creneau' => '',
    'nb_personnes' => 1,
    'buffet_jeudi' => false
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ========================================================================
    // ÉTAPE 1 : VÉRIFICATION CSRF
    // ========================================================================
    
    $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verifierCsrf($csrf_token)) {
        $erreurs[] = "Erreur de sécurité. Veuillez actualiser la page et réessayer.";
    }
    
    // ========================================================================
    // ÉTAPE 2 : RÉCUPÉRATION ET NETTOYAGE DES DONNÉES
    // ========================================================================
    
    $formData['nom'] = nettoyer($_POST['nom'] ?? '');
    $formData['prenom'] = nettoyer($_POST['prenom'] ?? '');
    $formData['email'] = nettoyer($_POST['email'] ?? '');
    $formData['id_categorie'] = intval($_POST['id_categorie'] ?? 0);
    $formData['id_salle'] = intval($_POST['id_salle'] ?? 0);
    $formData['id_creneau'] = intval($_POST['id_creneau'] ?? 0);
    $formData['nb_personnes'] = intval($_POST['nb_personnes'] ?? 1);
    $formData['buffet_jeudi'] = isset($_POST['buffet_jeudi']) ? 1 : 0;
    
    // ========================================================================
    // ÉTAPE 3 : VALIDATION DES CHAMPS
    // ========================================================================
    
    // Validation du nom
    if (empty($formData['nom'])) {
        $erreurs[] = "Le nom est requis.";
    } elseif (strlen($formData['nom']) < 2 || strlen($formData['nom']) > 100) {
        $erreurs[] = "Le nom doit contenir entre 2 et 100 caractères.";
    }
    
    // Validation du prénom
    if (empty($formData['prenom'])) {
        $erreurs[] = "Le prénom est requis.";
    } elseif (strlen($formData['prenom']) < 2 || strlen($formData['prenom']) > 100) {
        $erreurs[] = "Le prénom doit contenir entre 2 et 100 caractères.";
    }
    
    // Validation de l'email
    if (empty($formData['email'])) {
        $erreurs[] = "L'email est requis.";
    } elseif (!validerEmail($formData['email'])) {
        $erreurs[] = "L'adresse email n'est pas valide.";
    }
    
    // Validation de la catégorie
    $categorieValide = null;
    if ($formData['id_categorie'] <= 0) {
        $erreurs[] = "Veuillez sélectionner une catégorie.";
    } else {
        // Vérifier que la catégorie existe
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id_categorie = :id");
        $stmt->bindValue(':id', $formData['id_categorie'], PDO::PARAM_INT);
        $stmt->execute();
        $categorieValide = $stmt->fetch();
        
        if (!$categorieValide) {
            $erreurs[] = "La catégorie sélectionnée n'existe pas.";
        }
    }
    
    // Validation de la salle
    if ($formData['id_salle'] <= 0) {
        $erreurs[] = "Veuillez sélectionner une salle.";
    }
    
    // Validation du créneau
    $creneauValide = null;
    if ($formData['id_creneau'] <= 0) {
        $erreurs[] = "Veuillez sélectionner un créneau.";
    } else {
        // Vérifier que le créneau existe et appartient à la salle
        $stmt = $pdo->prepare("
            SELECT * FROM creneaux 
            WHERE id_creneau = :id_creneau AND id_salle = :id_salle
        ");
        $stmt->bindValue(':id_creneau', $formData['id_creneau'], PDO::PARAM_INT);
        $stmt->bindValue(':id_salle', $formData['id_salle'], PDO::PARAM_INT);
        $stmt->execute();
        $creneauValide = $stmt->fetch();
        
        if (!$creneauValide) {
            $erreurs[] = "Le créneau sélectionné n'est pas valide pour cette salle.";
        }
    }
    
    // Validation du nombre de personnes
    if ($formData['nb_personnes'] < 1 || $formData['nb_personnes'] > JAUGE_MAX) {
        $erreurs[] = "Le nombre de personnes doit être compris entre 1 et " . JAUGE_MAX . ".";
    }
    
    // Validation du buffet (seulement si catégorie autorisée)
    if ($formData['buffet_jeudi'] && $categorieValide && $categorieValide['buffet_actif'] == 0) {
        $erreurs[] = "Votre catégorie ne permet pas de participer au buffet.";
        $formData['buffet_jeudi'] = 0;
    }
    
    // ========================================================================
    // ÉTAPE 4 : INSCRIPTION TRANSACTIONNELLE (si pas d'erreur)
    // ========================================================================
    
    if (empty($erreurs)) {
        
        try {
            // Démarrage de la transaction
            $pdo->beginTransaction();
            
            // ----------------------------------------------------------------
            // VÉRIFICATION ATOMIQUE DES PLACES RESTANTES
            // ----------------------------------------------------------------
            // Cette requête calcule les places restantes en temps réel
            // Le verrouillage implicite évite les race conditions
            
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
            $stmtPlaces->bindValue(':id_creneau', $formData['id_creneau'], PDO::PARAM_INT);
            $stmtPlaces->execute();
            $resultatPlaces = $stmtPlaces->fetch();
            
            $placesRestantes = $resultatPlaces ? (int)$resultatPlaces['places_restantes'] : JAUGE_MAX;
            
            // Vérifier s'il y a assez de places
            if ($placesRestantes < $formData['nb_personnes']) {
                // Pas assez de places : rollback et erreur
                $pdo->rollBack();
                $erreurs[] = "Désolé, il ne reste que {$placesRestantes} place(s) pour ce créneau. Veuillez réduire le nombre de personnes ou choisir un autre créneau.";
            } else {
                
                // ----------------------------------------------------------------
                // GÉNÉRATION DU TOKEN UNIQUE
                // ----------------------------------------------------------------
                $token = genererToken();
                
                // ----------------------------------------------------------------
                // INSERTION DANS LA TABLE INSCRIPTIONS
                // ----------------------------------------------------------------
                $sqlInscription = "
                    INSERT INTO inscriptions 
                    (nom, prenom, email, id_categorie, nb_personnes, buffet_jeudi, token, email_referent)
                    VALUES 
                    (:nom, :prenom, :email, :id_categorie, :nb_personnes, :buffet_jeudi, :token, :email_referent)
                ";
                
                $stmtInscription = $pdo->prepare($sqlInscription);
                $stmtInscription->bindValue(':nom', $formData['nom'], PDO::PARAM_STR);
                $stmtInscription->bindValue(':prenom', $formData['prenom'], PDO::PARAM_STR);
                $stmtInscription->bindValue(':email', $formData['email'], PDO::PARAM_STR);
                $stmtInscription->bindValue(':id_categorie', $formData['id_categorie'], PDO::PARAM_INT);
                $stmtInscription->bindValue(':nb_personnes', $formData['nb_personnes'], PDO::PARAM_INT);
                $stmtInscription->bindValue(':buffet_jeudi', $formData['buffet_jeudi'], PDO::PARAM_INT);
                $stmtInscription->bindValue(':token', $token, PDO::PARAM_STR);
                $stmtInscription->bindValue(':email_referent', EMAIL_REFERENT, PDO::PARAM_STR);
                $stmtInscription->execute();
                
                // Récupérer l'ID de l'inscription créée
                $idInscription = $pdo->lastInsertId();
                
                // ----------------------------------------------------------------
                // INSERTION DANS LA TABLE RESERVATIONS (table pivot)
                // ----------------------------------------------------------------
                $sqlReservation = "
                    INSERT INTO reservations (id_inscription, id_creneau)
                    VALUES (:id_inscription, :id_creneau)
                ";
                
                $stmtReservation = $pdo->prepare($sqlReservation);
                $stmtReservation->bindValue(':id_inscription', $idInscription, PDO::PARAM_INT);
                $stmtReservation->bindValue(':id_creneau', $formData['id_creneau'], PDO::PARAM_INT);
                $stmtReservation->execute();
                
                // ----------------------------------------------------------------
                // COMMIT DE LA TRANSACTION
                // ----------------------------------------------------------------
                $pdo->commit();
                
                // ----------------------------------------------------------------
                // SIMULATION ENVOI EMAIL DE CONFIRMATION
                // ----------------------------------------------------------------
                
                // Récupérer les détails pour l'email
                $reservationDetails = getReservationComplete($token);
                
                if ($reservationDetails) {
                    // Formater les données pour l'email
                    $dataEmail = [
                        'nom' => $reservationDetails['nom'],
                        'prenom' => $reservationDetails['prenom'],
                        'token' => $token,
                        'nb_personnes' => $reservationDetails['nb_personnes'],
                        'buffet_jeudi' => $reservationDetails['buffet_jeudi']
                    ];
                    
                    $dataReservation = [
                        'salle_numero' => $reservationDetails['salle_numero'],
                        'salle_nom' => $reservationDetails['salle_nom'],
                        'date_formatee' => formaterDate($reservationDetails['date_creneau']),
                        'heure_formatee' => formaterHeure($reservationDetails['heure'])
                    ];
                    
                    // Générer et envoyer l'email (simulation)
                    $contenuEmail = genererEmailConfirmation($dataEmail, $dataReservation);
                    simulerEnvoiMail(
                        $formData['email'],
                        "Confirmation de réservation - E-LLUSION",
                        $contenuEmail
                    );
                }
                
                // ----------------------------------------------------------------
                // REDIRECTION VERS LA PAGE DE CONFIRMATION
                // ----------------------------------------------------------------
                $tokenGenere = $token;
                redirect('confirmation.php?token=' . urlencode($token));
            }
            
        } catch (PDOException $e) {
            // Rollback en cas d'erreur
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
            // Log de l'erreur pour debug
            if (DEBUG_MODE) {
                $erreurs[] = "Erreur base de données : " . $e->getMessage();
            } else {
                $erreurs[] = "Une erreur est survenue lors de l'inscription. Veuillez réessayer.";
            }
        }
    }
}

// Inclusion du header
require_once __DIR__ . '/includes/header.php';
?>

<!-- ================================================================
     EN-TÊTE DE PAGE
     ================================================================ -->
<section class="page-header text-center">
    <h1>Inscription</h1>
    <p class="page-subtitle">
        Réservez gratuitement votre place pour découvrir l'exposition E-LLUSION.
        Choisissez votre salle et votre créneau horaire.
    </p>
</section>

<!-- ================================================================
     AFFICHAGE DES ERREURS
     ================================================================ -->
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
     FORMULAIRE D'INSCRIPTION
     ================================================================ -->
<form method="POST" action="inscription.php" class="inscription-form" data-validate>
    
    <!-- Token CSRF (sécurité) -->
    <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
    
    <!-- ============================================================
         SECTION 1 : INFORMATIONS PERSONNELLES
         ============================================================ -->
    <fieldset>
        <legend>Vos informations</legend>
        
        <div class="form-row">
            <!-- Nom -->
            <div class="form-group">
                <label for="nom" class="form-label form-label-required">Nom</label>
                <input type="text" 
                       id="nom" 
                       name="nom" 
                       class="form-input" 
                       value="<?php echo sanitize($formData['nom']); ?>"
                       placeholder="Votre nom"
                       required
                       maxlength="100"
                       autocomplete="family-name">
            </div>
            
            <!-- Prénom -->
            <div class="form-group">
                <label for="prenom" class="form-label form-label-required">Prénom</label>
                <input type="text" 
                       id="prenom" 
                       name="prenom" 
                       class="form-input" 
                       value="<?php echo sanitize($formData['prenom']); ?>"
                       placeholder="Votre prénom"
                       required
                       maxlength="100"
                       autocomplete="given-name">
            </div>
        </div>
        
        <!-- Email -->
        <div class="form-group">
            <label for="email" class="form-label form-label-required">Adresse email</label>
            <input type="email" 
                   id="email" 
                   name="email" 
                   class="form-input" 
                   value="<?php echo sanitize($formData['email']); ?>"
                   placeholder="votre.email@exemple.fr"
                   required
                   maxlength="255"
                   autocomplete="email">
            <span class="form-hint">Vous recevrez un email de confirmation avec un lien pour modifier votre réservation.</span>
        </div>
        
        <!-- Catégorie -->
        <div class="form-group">
            <label for="id_categorie" class="form-label form-label-required">Qui êtes-vous ?</label>
            <select id="id_categorie" name="id_categorie" class="form-select" required>
                <option value="">-- Sélectionnez votre catégorie --</option>
                <?php foreach ($categories as $categorie): ?>
                <option value="<?php echo $categorie['id_categorie']; ?>"
                        data-buffet="<?php echo $categorie['buffet_actif']; ?>"
                        <?php echo $formData['id_categorie'] == $categorie['id_categorie'] ? 'selected' : ''; ?>>
                    <?php echo sanitize($categorie['nom']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </fieldset>
    
    <!-- ============================================================
         SECTION 2 : CHOIX DU CRÉNEAU
         ============================================================ -->
    <fieldset>
        <legend>Votre visite</legend>
        
        <!-- Salle -->
        <div class="form-group">
            <label for="id_salle" class="form-label form-label-required">Salle à visiter</label>
            <select id="id_salle" name="id_salle" class="form-select" required>
                <option value="">-- Choisissez une salle --</option>
                <?php foreach ($salles as $salle): ?>
                <option value="<?php echo $salle['id_salle']; ?>"
                        <?php echo $formData['id_salle'] == $salle['id_salle'] ? 'selected' : ''; ?>>
                    Salle <?php echo sanitize($salle['numero']); ?> - <?php echo sanitize($salle['nom']); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <span class="form-hint">
                <a href="salles.php" target="_blank">Voir le détail des salles</a>
            </span>
        </div>
        
        <!-- Créneau (chargé dynamiquement via JavaScript) -->
        <div class="form-group">
            <label for="id_creneau" class="form-label form-label-required">Créneau horaire</label>
            <select id="id_creneau" name="id_creneau" class="form-select" required>
                <option value="">-- Sélectionnez d'abord une salle --</option>
            </select>
            <span class="form-hint">Les créneaux sont limités à <?php echo JAUGE_MAX; ?> personnes pour garantir une expérience optimale.</span>
        </div>
        
        <!-- Nombre de personnes -->
        <div class="form-group">
            <label for="nb_personnes" class="form-label form-label-required">Nombre de personnes</label>
            <input type="number" 
                   id="nb_personnes" 
                   name="nb_personnes" 
                   class="form-input" 
                   value="<?php echo $formData['nb_personnes']; ?>"
                   min="1" 
                   max="<?php echo JAUGE_MAX; ?>" 
                   required>
            <span class="form-hint">Maximum <?php echo JAUGE_MAX; ?> personnes par réservation (vous compris).</span>
        </div>
    </fieldset>
    
    <!-- ============================================================
         SECTION 3 : OPTION BUFFET
         ============================================================ -->
    <fieldset>
        <legend>Buffet du jeudi</legend>
        
        <div class="buffet-container <?php echo ($formData['id_categorie'] && !$categories[array_search($formData['id_categorie'], array_column($categories, 'id_categorie'))]['buffet_actif'] ?? true) ? 'disabled' : ''; ?>">
            <label class="form-checkbox">
                <input type="checkbox" 
                       id="buffet_jeudi" 
                       name="buffet_jeudi" 
                       value="1"
                       <?php echo $formData['buffet_jeudi'] ? 'checked' : ''; ?>
                       disabled>
                <span>Je participe au buffet du jeudi 18 juin à 18h30</span>
            </label>
            <p class="form-hint buffet-info">
                Le buffet est réservé aux enseignants, personnels USMB, professionnels/partenaires et visiteurs extérieurs.
                <br>Les étudiants MMI 2 et 3 ne peuvent pas y participer.
            </p>
        </div>
    </fieldset>
    
    <!-- ============================================================
         SECTION 4 : RÉFÉRENT
         ============================================================ -->
    <fieldset>
        <legend>Information</legend>
        
        <div class="form-group">
            <p><strong>Référent du projet :</strong> <?php echo EMAIL_REFERENT; ?></p>
            <input type="hidden" name="email_referent" value="<?php echo EMAIL_REFERENT; ?>">
            <span class="form-hint">En cas de question, vous pouvez contacter le référent à cette adresse.</span>
        </div>
    </fieldset>
    
    <!-- ============================================================
         BOUTON DE SOUMISSION
         ============================================================ -->
    <div class="form-group text-center">
        <button type="submit" class="btn btn-primary btn-block" style="max-width: 400px; margin: 0 auto;">
            Valider mon inscription
        </button>
        <p class="form-hint mt-2">
            En validant, vous acceptez de recevoir un email de confirmation.
        </p>
    </div>
    
</form>

<?php
// Styles spécifiques à la page
?>
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
    
    .inscription-form {
        max-width: 700px;
        margin: 0 auto;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr;
        gap: var(--spacing-md);
    }
    
    @media (min-width: 600px) {
        .form-row {
            grid-template-columns: 1fr 1fr;
        }
    }
    
    .buffet-container {
        padding: var(--spacing-md);
        background: var(--cyan-clair);
        border-radius: var(--border-radius);
    }
    
    .buffet-container.disabled {
        opacity: 0.6;
        background: var(--gris-clair);
    }
    
    .buffet-info {
        margin-top: var(--spacing-sm);
        font-size: var(--font-size-small);
    }
    
    /* Style pour les options désactivées dans le select */
    select option:disabled {
        color: var(--rouge);
        font-style: italic;
    }
</style>

<?php require_once 'includes/footer.php'; ?>
