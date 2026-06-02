<?php
/**
 * ============================================================================
 * SAE203 E-LLUSION - Page d'accueil
 * ============================================================================
 * Page principale du site présentant :
 * - Un carrousel d'images paramétrable
 * - Une présentation de l'exposition
 * - Des liens vers les salles et l'inscription
 * ============================================================================
 */

// ============================================================================
// CONFIGURATION DES IMAGES
// ============================================================================

// IMAGE DU HERO (arrière-plan)
// Changez facilement l'image de fond du hero en modifiant cette ligne
// Format recommandé : 1920x1080 pixels (16:9)
$heroImage = 'assets/images/hero-background.jpg'; // Chemin vers l'image du hero

// CARROUSEL D'IMAGES
// Modifiez facilement ce tableau pour changer les images du carrousel.
// Ajoutez vos propres images dans le dossier assets/images/
// Format recommandé : 1920x1080 pixels (16:9)
$slides = [
    'assets/images/slide1.jpg',
    'assets/images/slide2.jpg',
    'assets/images/slide3.jpg',
    'assets/images/slide4.jpg',
];

// ============================================================================
// CONFIGURATION DE LA PAGE
// ============================================================================
$pageTitle = "Accueil";
$pageDescription = "E-LLUSION : Exposition multimédia interactive. Plongez dans un univers sensoriel unique où art, technologie et interactivité se rencontrent.";
$pageActive = "accueil";
$pageAccueil = true; // Active l'affichage des RS dans le footer

// Ne pas afficher le tag <main> dans le header car on gère la structure nous-mêmes
$noMainTag = true;

// Inclusion du header
require_once __DIR__ . '/includes/header.php';

// Récupérer les salles pour les afficher (APRÈS le header qui charge les fonctions)
$salles = getSalles();
?>

<!-- ================================================================
     SECTION HERO - TITRE PRINCIPAL (PLEINE LARGEUR)
     ================================================================ -->
<section class="hero text-center" style="background-image: url('<?php echo SITE_URL . '/' . sanitize($heroImage); ?>');">
    <div class="hero-overlay">
        <div class="container">
            <h1>E-LLUSION</h1>
            <p class="hero-subtitle">Exposition multimédia interactive</p>
            <p class="hero-dates">18 & 19 juin 2026 - IUT de Chambéry</p>
        </div>
    </div>
</section>

<!-- ================================================================
     CARROUSEL D'IMAGES (PLEINE LARGEUR)
     ================================================================ -->
<section class="carrousel" aria-label="Galerie de l'exposition" tabindex="0">
    <div class="carrousel-container">
        <?php foreach ($slides as $index => $slide): ?>
        <div class="carrousel-slide">
            <img src="<?php echo SITE_URL . '/' . sanitize($slide); ?>" 
                 alt="Image de l'exposition E-LLUSION <?php echo $index + 1; ?>"
                 loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>">
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Boutons de navigation -->
    <button class="carrousel-btn carrousel-btn-prev" aria-label="Image précédente">
        <span aria-hidden="true">&#10094;</span>
    </button>
    <button class="carrousel-btn carrousel-btn-next" aria-label="Image suivante">
        <span aria-hidden="true">&#10095;</span>
    </button>
    
    <!-- Indicateurs (points) -->
    <div class="carrousel-indicators" role="tablist" aria-label="Sélecteur de slides">
        <!-- Les indicateurs sont générés par JavaScript -->
    </div>
</section>

<!-- ================================================================
     SECTION PRÉSENTATION (PLEINE LARGEUR)
     ================================================================ -->
<section class="presentation full-width-section">
    <div class="container">
        <h2>Bienvenue à E-LLUSION</h2>
        <p>
            Plongez dans un univers sensoriel unique où la lumière, le son et les projections 
            créent une expérience immersive totale. L'exposition E-LLUSION vous invite à explorer 
            quatre salles thématiques, chacune offrant une approche différente de l'art numérique 
            et interactif.
        </p>
        <p>
            Devenez acteur de l'art : chaque mouvement, chaque geste influence les œuvres qui vous 
            entourent. Capteurs et écrans réactifs répondent à votre présence pour créer une 
            expérience personnalisée et ludique.
        </p>
    </div>
</section>

<!-- ================================================================
     CONTENU AVEC CONTENEUR
     ================================================================ -->
<main id="contenu-principal" class="site-main">
    <div class="container">
        <!-- ================================================================
             SECTION SALLES (Aperçu)
             ================================================================ -->
        <section class="section-salles">
            <h2 class="text-center">Découvrez nos 4 salles</h2>
            
            <div class="cards-grid">
                <?php foreach ($salles as $salle): ?>
                <article class="card">
                    <img src="<?php echo SITE_URL; ?>/assets/images/<?php echo sanitize($salle['image']); ?>" 
                         alt="Aperçu de la <?php echo sanitize($salle['nom']); ?>"
                         class="card-image"
                         loading="lazy">
                    <div class="card-content">
                        <span class="card-numero">Salle <?php echo sanitize($salle['numero']); ?></span>
                        <h3 class="card-title"><?php echo sanitize($salle['nom']); ?></h3>
                        <p class="card-meta"><?php echo countOeuvresBySalle($salle['id_salle']); ?> œuvres</p>
                        <a href="salle-detail.php?id=<?php echo $salle['id_salle']; ?>" class="btn btn-secondary">
                            Découvrir
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-3">
                <a href="salles.php" class="btn btn-primary">Voir toutes les salles</a>
            </div>
        </section>
    </div>
</main>

<!-- ================================================================
     SECTION INFORMATIONS PRATIQUES (PLEINE LARGEUR)
     ================================================================ -->
<section class="presentation full-width-section">
    <div class="container">
        <h2>Informations pratiques</h2>
        <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; text-align: left; max-width: 800px; margin: 0 auto;">
            <div>
                <h3 style="color: var(--cyan-fonce);">Dates</h3>
                <p>
                    <strong>Jeudi 18 juin 2026</strong><br>
                    15h00 - 20h30<br>
                    <em>Buffet à 18h30</em>
                </p>
                <p>
                    <strong>Vendredi 19 juin 2026</strong><br>
                    9h30 - 11h30
                </p>
            </div>
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
                <h3 style="color: var(--cyan-fonce);">Inscription</h3>
                <p>
                    L'inscription est gratuite mais obligatoire pour garantir une expérience optimale.
                    Les places sont limitées à 12 personnes par créneau.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ================================================================
     CONTENU AVEC CONTENEUR
     ================================================================ -->
<main class="site-main">
    <div class="container">
        <!-- ================================================================
             APPEL À L'ACTION
             ================================================================ -->
        <section class="text-center">
            <h2>Prêt à vivre l'expérience ?</h2>
            <p>Réservez votre créneau dès maintenant et préparez-vous à être émerveillé.</p>
            <div class="btn-group" style="justify-content: center;">
                <a href="inscription.php" class="btn btn-primary" style="font-size: 1.2rem; padding: 1rem 2rem;">
                    S'inscrire gratuitement
                </a>
                <a href="salles.php" class="btn btn-secondary">
                    Explorer les salles
                </a>
            </div>
        </section>
    </div>
</main>

<?php
// Styles spécifiques à la page d'accueil
?>
<style>
    /* Hero avec image de fond paramétrable - PLEINE LARGEUR */
    .hero {
        width: 100vw; /* Pleine largeur de la fenêtre */
        position: relative;
        left: 50%;
        right: 50%;
        margin-left: -50vw;
        margin-right: -50vw;
        min-height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
    
    /* Overlay pour assurer la lisibilité du texte */
    .hero-overlay {
        width: 100%;
        min-height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(197, 249, 242, 0.9) 0%, rgba(255, 255, 255, 0.85) 100%);
        padding: var(--spacing-xl) var(--spacing-md);
    }
    
    .hero h1 {
        font-size: clamp(3rem, 10vw, 6rem);
        margin-bottom: var(--spacing-sm);
        background: linear-gradient(135deg, var(--cyan) 0%, var(--cyan-fonce) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .hero-subtitle {
        font-size: clamp(1.2rem, 3vw, 1.8rem);
        color: var(--noir);
        margin-bottom: var(--spacing-sm);
    }
    
    .hero-dates {
        font-size: clamp(1rem, 2vw, 1.2rem);
        color: var(--gris-fonce);
        font-weight: 600;
    }
    
    /* Carrousel - PLEINE LARGEUR */
    .carrousel {
        width: 100vw; /* Pleine largeur de la fenêtre */
        position: relative;
        left: 50%;
        right: 50%;
        margin-left: -50vw;
        margin-right: -50vw;
    }
    
    /* Section présentation - PLEINE LARGEUR */
    .presentation.full-width-section {
        width: 100vw; /* Pleine largeur de la fenêtre */
        position: relative;
        left: 50%;
        right: 50%;
        margin-left: -50vw;
        margin-right: -50vw;
    }
    
    .section-salles {
        background: var(--blanc);
        padding: var(--spacing-xl) 0;
    }
    
    .info-grid h3 {
        margin-bottom: var(--spacing-sm);
    }
    
    /* Responsive pour le hero */
    @media (min-width: 768px) {
        .hero {
            min-height: 70vh;
        }
        
        .hero-overlay {
            min-height: 70vh;
        }
    }
    
    @media (min-width: 1024px) {
        .hero {
            min-height: 80vh;
        }
        
        .hero-overlay {
            min-height: 80vh;
        }
    }
</style>

<?php require_once 'includes/footer.php'; ?>
