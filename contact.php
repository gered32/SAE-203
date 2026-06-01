<?php
/**
 * ============================================================================
 * SAE203 E-LLUSION - Page de contact
 * ============================================================================
 * Formulaire de contact permettant aux visiteurs de :
 * - Poser des questions sur l'exposition
 * - Signaler un problème avec leur réservation
 * - Contacter le référent du projet
 * 
 * L'envoi d'email est simulé (écrit dans un fichier log).
 * ============================================================================
 */

// Inclusion des fonctions
require_once __DIR__ . '/includes/functions.php';

// Configuration de la page
$pageTitle = "Contact";
$pageDescription = "Contactez l'équipe E-LLUSION pour toute question concernant l'exposition ou votre réservation.";
$pageActive = "contact";

// ============================================================================
// TRAITEMENT DU FORMULAIRE
// ============================================================================
$erreurs = [];
$succes = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Vérification du token CSRF
    $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verifierCsrf($csrf_token)) {
        $erreurs[] = "Erreur de sécurité. Veuillez réessayer.";
    }
    
    // 2. Récupération et nettoyage des données
    $nom = nettoyer($_POST['nom'] ?? '');
    $email = nettoyer($_POST['email'] ?? '');
    $sujet = nettoyer($_POST['sujet'] ?? '');
    $message = nettoyer($_POST['message'] ?? '');
    
    // 3. Validation des champs
    if (empty($nom)) {
        $erreurs[] = "Le nom est requis.";
    } elseif (strlen($nom) < 2) {
        $erreurs[] = "Le nom doit contenir au moins 2 caractères.";
    }
    
    if (empty($email)) {
        $erreurs[] = "L'email est requis.";
    } elseif (!validerEmail($email)) {
        $erreurs[] = "L'adresse email n'est pas valide.";
    }
    
    if (empty($sujet)) {
        $erreurs[] = "Le sujet est requis.";
    }
    
    if (empty($message)) {
        $erreurs[] = "Le message est requis.";
    } elseif (strlen($message) < 10) {
        $erreurs[] = "Le message doit contenir au moins 10 caractères.";
    } elseif (strlen($message) > 2000) {
        $erreurs[] = "Le message ne doit pas dépasser 2000 caractères.";
    }
    
    // 4. Si pas d'erreur, envoyer le message
    if (empty($erreurs)) {
        // Construction du contenu de l'email
        $contenuEmail = "
NOUVEAU MESSAGE DE CONTACT - E-LLUSION
========================================

De : {$nom} <{$email}>
Sujet : {$sujet}
Date : " . date('d/m/Y à H:i') . "

----------------------------------------
MESSAGE :
----------------------------------------
{$message}
----------------------------------------

-- 
Ce message a été envoyé via le formulaire de contact du site E-LLUSION.
";
        
        // Simulation d'envoi (écriture dans un fichier log)
        $resultat = simulerEnvoiMail(
            EMAIL_REFERENT,
            "[E-LLUSION] Contact : " . $sujet,
            $contenuEmail
        );
        
        if ($resultat) {
            $succes = true;
            // Régénérer le token CSRF après soumission réussie
            regenererCsrf();
            
            // Vider les champs
            $nom = $email = $sujet = $message = '';
        } else {
            $erreurs[] = "Une erreur est survenue lors de l'envoi du message. Veuillez réessayer.";
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
    <h1>Contact</h1>
    <p class="page-subtitle">
        Une question sur l'exposition ou votre réservation ? N'hésitez pas à nous contacter.
    </p>
</section>

<div class="contact-container">
    <!-- ================================================================
         INFORMATIONS DE CONTACT
         ================================================================ -->
    <section class="contact-info">
        <h2>Nous contacter</h2>
        
        <div class="info-block">
            <h3>Référent du projet</h3>
            <p>
                Pour toute question concernant l'exposition     E-LLUSION, vous pouvez contacter 
                le responsable du projet :
            </p>
            <p class="contact-email">
                <strong>Email :</strong> 
                <a href="mailto:<?php echo EMAIL_REFERENT; ?>"><?php echo EMAIL_REFERENT; ?></a>
            </p>
        </div>
        
        <div class="info-block">
            <h3>Adresse</h3>
            <address>
                IUT de Chambéry - Département MMI<br>
                Savoie Technolac<br>
                73370 Le Bourget-du-Lac<br>
                France
            </address>
        </div>
        
        <div class="info-block">
            <h3>Horaires de l'exposition</h3>
            <p>
                <strong>Jeudi 18 juin 2026</strong><br>
                15h00 - 20h30 (buffet à 18h30)
            </p>
            <p>
                <strong>Vendredi 19 juin 2026</strong><br>
                9h30 - 11h30
            </p>
        </div>
    </section>
    
    <!-- ================================================================
         FORMULAIRE DE CONTACT
         ================================================================ -->
    <section class="contact-form-section">
        <h2>Envoyer un message</h2>
        
        <?php if ($succes): ?>
        <div class="flash-message flash-success" role="alert">
            <div class="flash-content">
                <p>Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.</p>
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
            </div>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="contact.php" data-validate>
            <!-- Token CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            
            <!-- Nom -->
            <div class="form-group">
                <label for="nom" class="form-label form-label-required">Votre nom</label>
                <input type="text" 
                       id="nom" 
                       name="nom" 
                       class="form-input" 
                       value="<?php echo sanitize($nom ?? ''); ?>"
                       placeholder="Jean Dupont"
                       required
                       maxlength="100">
            </div>
            
            <!-- Email -->
            <div class="form-group">
                <label for="email" class="form-label form-label-required">Votre email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       class="form-input" 
                       value="<?php echo sanitize($email ?? ''); ?>"
                       placeholder="jean.dupont@email.fr"
                       required
                       maxlength="255">
            </div>
            
            <!-- Sujet -->
            <div class="form-group">
                <label for="sujet" class="form-label form-label-required">Sujet</label>
                <select id="sujet" name="sujet" class="form-select" required>
                    <option value="">-- Choisir un sujet --</option>
                    <option value="Question sur l'exposition" <?php echo ($sujet ?? '') === "Question sur l'exposition" ? 'selected' : ''; ?>>
                        Question sur l'exposition
                    </option>
                    <option value="Problème de réservation" <?php echo ($sujet ?? '') === "Problème de réservation" ? 'selected' : ''; ?>>
                        Problème de réservation
                    </option>
                    <option value="Accessibilité" <?php echo ($sujet ?? '') === "Accessibilité" ? 'selected' : ''; ?>>
                        Accessibilité
                    </option>
                    <option value="Partenariat" <?php echo ($sujet ?? '') === "Partenariat" ? 'selected' : ''; ?>>
                        Partenariat
                    </option>
                    <option value="Autre" <?php echo ($sujet ?? '') === "Autre" ? 'selected' : ''; ?>>
                        Autre
                    </option>
                </select>
            </div>
            
            <!-- Message -->
            <div class="form-group">
                <label for="message" class="form-label form-label-required">Votre message</label>
                <textarea id="message" 
                          name="message" 
                          class="form-textarea" 
                          placeholder="Décrivez votre demande..."
                          required
                          maxlength="2000"><?php echo sanitize($message ?? ''); ?></textarea>
                <span class="form-hint">Maximum 2000 caractères</span>
            </div>
            
            <!-- Bouton -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">
                    Envoyer le message
                </button>
            </div>
        </form>
    </section>
</div>

<?php
// Styles spécifiques à la page
?>
<style>
    .page-header {
        padding: var(--spacing-xl) var(--spacing-md);
        margin-bottom: var(--spacing-xl);
    }
    
    .page-subtitle {
        max-width: 600px;
        margin: 0 auto;
        color: var(--gris-fonce);
    }
    
    .contact-container {
        display: grid;
        grid-template-columns: 1fr;
        gap: var(--spacing-xl);
    }
    
    @media (min-width: 768px) {
        .contact-container {
            grid-template-columns: 1fr 1.5fr;
        }
    }
    
    .contact-info {
        background: var(--cyan-clair);
        padding: var(--spacing-xl);
        border-radius: var(--border-radius-lg);
    }
    
    .contact-info h2 {
        color: var(--noir);
        margin-bottom: var(--spacing-lg);
    }
    
    .info-block {
        margin-bottom: var(--spacing-lg);
        padding-bottom: var(--spacing-lg);
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    .info-block:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
    
    .info-block h3 {
        color: var(--cyan-fonce);
        font-size: var(--font-size-large);
        margin-bottom: var(--spacing-sm);
    }
    
    .contact-email a {
        color: var(--cyan-fonce);
        font-weight: 600;
    }
    
    address {
        font-style: normal;
        line-height: 1.8;
    }
    
    .contact-form-section {
        background: var(--blanc);
        padding: var(--spacing-xl);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow);
    }
    
    .contact-form-section h2 {
        margin-bottom: var(--spacing-lg);
    }
</style>

<?php require_once 'includes/footer.php'; ?>
