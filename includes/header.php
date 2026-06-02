<?php
/**
 * ============================================================================
 * SAE203 E-LLUSION - Header (en-tête commun)
 * ============================================================================
 * Ce fichier est inclus en haut de chaque page.
 * Il contient :
 * - Le DOCTYPE et les balises <head>
 * - Les métadonnées SEO et accessibilité
 * - Le lien vers la feuille de style
 * - L'en-tête du site avec navigation
 * 
 * Variables attendues (optionnelles) :
 * - $pageTitle : titre de la page (défaut : "E-LLUSION")
 * - $pageDescription : description pour le SEO
 * - $pageActive : nom de la page active pour le menu (ex: 'accueil', 'salles')
 * ============================================================================
 */

// Inclusion des fonctions si pas déjà fait
if (!function_exists('sanitize')) {
    require_once __DIR__ . '/functions.php';
}

// Démarrage de la session pour les messages flash et CSRF
demarrerSession();

// Valeurs par défaut pour les variables de page
$pageTitle = isset($pageTitle) ? sanitize($pageTitle) : 'E-LLUSION';
$pageDescription = isset($pageDescription) ? sanitize($pageDescription) : 'Exposition multimédia interactive E-LLUSION - Inscrivez-vous pour une expérience immersive unique.';
$pageActive = isset($pageActive) ? $pageActive : '';

// Récupération du message flash s'il existe
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Encodage des caractères -->
    <meta charset="UTF-8">
    
    <!-- Viewport pour le responsive (mobile-first) -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Titre de la page -->
    <title><?php echo $pageTitle; ?> | E-LLUSION</title>
    
    <!-- Description pour le SEO -->
    <meta name="description" content="<?php echo $pageDescription; ?>">
    
    <!-- Auteur -->
    <meta name="author" content="BUT MMI - SAE203">
    
    <!-- Thème couleur pour les navigateurs mobiles -->
    <meta name="theme-color" content="#00bbaa">
    
    <link rel="icon" type="image/x-icon" href="<?php echo SITE_URL; ?>/assets/images/favicone.ico">

    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    
    <!-- Variable globale JavaScript pour l'URL du site -->
    <script>window.SITE_URL = '<?php echo SITE_URL; ?>';</script>
    
    <!-- Préconnexion pour les polices (optimisation performance) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    
</head>
<body>
    <!-- Lien d'évitement pour l'accessibilité -->
    <a href="#contenu-principal" class="skip-link">Aller au contenu principal</a>
    
    <!-- ================================================================
         EN-TÊTE DU SITE
         ================================================================ -->
    <header class="site-header">
        <div class="header-container">
            <!-- Logo / Titre du site -->
            <div class="site-logo">
                <a href="<?php echo SITE_URL; ?>/index.php" aria-label="Retour à l'accueil E-LLUSION">
                    <span class="logo-text">E-LLUSION</span>
                </a>
            </div>
            
            <!-- Bouton menu burger (mobile) -->
            <button class="burger-menu" 
                    aria-label="Ouvrir le menu de navigation" 
                    aria-expanded="false"
                    aria-controls="navigation-principale">
                <span class="burger-line"></span>
                <span class="burger-line"></span>
                <span class="burger-line"></span>
            </button>
            
            <!-- Navigation principale -->
            <nav id="navigation-principale" 
                 class="nav-principale" 
                 aria-label="Navigation principale">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/index.php" 
                           class="nav-link <?php echo $pageActive === 'accueil' ? 'active' : ''; ?>"
                           <?php echo $pageActive === 'accueil' ? 'aria-current="page"' : ''; ?>>
                            Accueil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/salles.php" 
                           class="nav-link <?php echo $pageActive === 'salles' ? 'active' : ''; ?>"
                           <?php echo $pageActive === 'salles' ? 'aria-current="page"' : ''; ?>>
                            Les Salles
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/inscription.php" 
                           class="nav-link nav-link-cta <?php echo $pageActive === 'inscription' ? 'active' : ''; ?>"
                           <?php echo $pageActive === 'inscription' ? 'aria-current="page"' : ''; ?>>
                            S'inscrire
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/contact.php" 
                           class="nav-link <?php echo $pageActive === 'contact' ? 'active' : ''; ?>"
                           <?php echo $pageActive === 'contact' ? 'aria-current="page"' : ''; ?>>
                            Contact
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
    
    <!-- ================================================================
         MESSAGE FLASH (notifications)
         ================================================================ -->
    <?php if ($flashMessage): ?>
    <div class="flash-message flash-<?php echo sanitize($flashMessage['type']); ?>" role="alert">
        <div class="flash-content">
            <p><?php echo sanitize($flashMessage['message']); ?></p>
            <button type="button" class="flash-close" aria-label="Fermer le message">&times;</button>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- ================================================================
         CONTENU PRINCIPAL (début)
         ================================================================ -->
    <?php if (!isset($noMainTag) || $noMainTag !== true): ?>
    <main id="contenu-principal" class="site-main">
    <?php endif; ?>
