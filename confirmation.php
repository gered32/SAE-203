<?php
/**
 * ============================================================================
 * SAE203 E-LLUSION - Page de confirmation
 * ============================================================================
 * Affiche le récapitulatif de la réservation après inscription réussie.
 * Accessible via : confirmation.php?token=XXX
 * 
 * Contenu affiché :
 * - Récapitulatif complet de la réservation
 * - Token unique pour modification/suppression
 * - Lien vers la page de gestion de réservation
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
    redirectAvecMessage('index.php', 'Aucune réservation à afficher.', 'error');
}

// Récupérer les détails complets de la réservation
$reservation = getReservationComplete($token);

// Vérifier que la réservation existe
if (!$reservation) {
    redirectAvecMessage('index.php', 'Réservation non trouvée. Le token est peut-être invalide ou expiré.', 'error');
}

// ============================================================================
// CONFIGURATION DE LA PAGE
// ============================================================================
$pageTitle = "Confirmation de réservation";
$pageDescription = "Votre inscription à l'exposition E-LLUSION a bien été enregistrée.";
$pageActive = "";

// Inclusion du header
require_once 'includes/header.php';

// Formatage des données pour l'affichage
$dateFormatee = formaterDate($reservation['date_creneau']);
$heureFormatee = formaterHeure($reservation['heure']);
$buffetTexte = $reservation['buffet_jeudi'] ? 'Oui' : 'Non';
$lienModification = SITE_URL . '/ma-reservation.php?token=' . urlencode($token);
?>

<!-- ================================================================
     CONFIRMATION DE RÉSERVATION
     ================================================================ -->
<div class="confirmation-box">
    
    <!-- Icône de succès -->
    <div class="confirmation-icon" aria-hidden="true">
        &#10003;
    </div>
    
    <h1>Réservation confirmée !</h1>
    
    <p class="confirmation-intro">
        Merci <strong><?php echo sanitize($reservation['prenom']); ?></strong> ! 
        Votre inscription à l'exposition E-LLUSION a bien été enregistrée.
    </p>
    
    <p class="confirmation-email">
        Un email de confirmation a été envoyé à <strong><?php echo sanitize($reservation['email']); ?></strong>
    </p>
    
</div>

<!-- ================================================================
     RÉCAPITULATIF
     ================================================================ -->
<section class="recap-section">
    <h2>Récapitulatif de votre réservation</h2>
    
    <ul class="recap-list">
        <li>
            <span class="recap-label">Nom</span>
            <span class="recap-value"><?php echo sanitize($reservation['nom']); ?></span>
        </li>
        <li>
            <span class="recap-label">Prénom</span>
            <span class="recap-value"><?php echo sanitize($reservation['prenom']); ?></span>
        </li>
        <li>
            <span class="recap-label">Email</span>
            <span class="recap-value"><?php echo sanitize($reservation['email']); ?></span>
        </li>
        <li>
            <span class="recap-label">Catégorie</span>
            <span class="recap-value"><?php echo sanitize($reservation['categorie_nom']); ?></span>
        </li>
        <li>
            <span class="recap-label">Salle</span>
            <span class="recap-value">
                Salle <?php echo sanitize($reservation['salle_numero']); ?> - 
                <?php echo sanitize($reservation['salle_nom']); ?>
            </span>
        </li>
        <li>
            <span class="recap-label">Date</span>
            <span class="recap-value"><?php echo $dateFormatee; ?></span>
        </li>
        <li>
            <span class="recap-label">Heure</span>
            <span class="recap-value"><?php echo $heureFormatee; ?></span>
        </li>
        <li>
            <span class="recap-label">Nombre de personnes</span>
            <span class="recap-value"><?php echo $reservation['nb_personnes']; ?></span>
        </li>
        <li>
            <span class="recap-label">Participation au buffet</span>
            <span class="recap-value"><?php echo $buffetTexte; ?></span>
        </li>
        <li>
            <span class="recap-label">Date d'inscription</span>
            <span class="recap-value">
                <?php echo date('d/m/Y à H:i', strtotime($reservation['date_inscription'])); ?>
            </span>
        </li>
    </ul>
</section>

<!-- ================================================================
     LIEN DE MODIFICATION
     ================================================================ -->
<section class="token-section">
    <h2>Modifier ou annuler votre réservation</h2>
    
    <p>
        Conservez précieusement le lien ci-dessous. Il vous permet de modifier 
        ou d'annuler votre réservation à tout moment.
    </p>
    
    <div class="token-box">
        <p><strong>Votre lien personnel :</strong></p>
        <code><?php echo sanitize($lienModification); ?></code>
    </div>
    
    <div class="btn-group" style="justify-content: center; flex-wrap: wrap;">
        <a href="<?php echo sanitize($lienModification); ?>" class="btn btn-primary">
            Gérer ma réservation
        </a>
        <button type="button" class="btn btn-secondary" onclick="copierLien()">
            Copier le lien
        </button>
    </div>
    
    <p class="form-hint mt-2 text-center">
        <strong style="color: var(--rouge);">Important :</strong> 
        Ce lien est unique et confidentiel. Ne le partagez pas.
    </p>
</section>

<!-- ================================================================
     INFORMATIONS COMPLÉMENTAIRES
     ================================================================ -->
<section class="info-section mt-4">
    <h2>Informations pratiques</h2>
    
    <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: var(--spacing-lg);">
        <div>
            <h3 style="color: var(--cyan-fonce);">Lieu</h3>
            <p>
                IUT de Chambéry<br>
                Département MMI<br>
                Savoie Technolac<br>
                73370 Le Bourget-du-Lac
            </p>
        </div>
        
        <div>
            <h3 style="color: var(--cyan-fonce);">Conseils</h3>
            <ul style="padding-left: 1.2rem;">
                <li>Arrivez quelques minutes avant votre créneau</li>
                <li>Les retardataires ne pourront pas être acceptés</li>
                <li>Présentez-vous à l'accueil avec votre nom</li>
            </ul>
        </div>
        
        <?php if ($reservation['buffet_jeudi']): ?>
        <div>
            <h3 style="color: var(--cyan-fonce);">Buffet</h3>
            <p>
                Vous êtes inscrit au buffet du <strong>jeudi 18 juin à 18h30</strong>.
                Le buffet se tiendra dans l'espace commun après les visites.
            </p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ================================================================
     RETOUR À L'ACCUEIL
     ================================================================ -->
<section class="text-center mt-4 mb-4">
    <a href="index.php" class="btn btn-secondary">Retour à l'accueil</a>
    <a href="salles.php" class="btn btn-secondary">Découvrir les salles</a>
</section>

<!-- Script pour copier le lien -->
<script>
function copierLien() {
    const lien = '<?php echo addslashes($lienModification); ?>';
    
    // Utiliser l'API Clipboard moderne si disponible
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(lien).then(function() {
            alert('Lien copié dans le presse-papiers !');
        }).catch(function() {
            fallbackCopier(lien);
        });
    } else {
        fallbackCopier(lien);
    }
}

function fallbackCopier(texte) {
    // Fallback pour les navigateurs plus anciens
    const textarea = document.createElement('textarea');
    textarea.value = texte;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    
    try {
        document.execCommand('copy');
        alert('Lien copié dans le presse-papiers !');
    } catch (err) {
        prompt('Copiez ce lien manuellement :', texte);
    }
    
    document.body.removeChild(textarea);
}
</script>

<?php
// Styles spécifiques à la page
?>
<style>
    .confirmation-intro {
        font-size: var(--font-size-large);
        margin-bottom: var(--spacing-sm);
    }
    
    .confirmation-email {
        color: var(--gris);
    }
    
    .recap-section {
        background: var(--blanc);
        padding: var(--spacing-xl);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow);
        margin: var(--spacing-xl) 0;
    }
    
    .recap-section h2 {
        text-align: center;
        margin-bottom: var(--spacing-lg);
    }
    
    .token-section {
        background: var(--gris-clair);
        padding: var(--spacing-xl);
        border-radius: var(--border-radius-lg);
        text-align: center;
    }
    
    .token-section h2 {
        margin-bottom: var(--spacing-md);
    }
    
    .info-section {
        padding: var(--spacing-xl) 0;
        border-top: 2px solid var(--cyan-clair);
    }
    
    .info-section h2 {
        text-align: center;
        margin-bottom: var(--spacing-lg);
    }
</style>

<?php require_once 'includes/footer.php'; ?>
