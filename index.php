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
// CARROUSEL PARAMÉTRABLE
// ============================================================================
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

// Inclusion du header
require_once 'includes/header.php';

// Récupérer les salles pour les afficher
$salles = getSalles();
?>

<!-- ================================================================
     SECTION HERO - TITRE PRINCIPAL
     ================================================================ -->
<section class="hero text-center">
    <h1>E-LLUSION</h1>
    <p class="hero-subtitle">Exposition multimédia interactive</p>
    <p class="hero-dates">18 & 19 juin 2026 - IUT de Chambéry</p>
</section>

<!-- ================================================================
     CARROUSEL D'IMAGES
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
     SECTION PRÉSENTATION
     ================================================================ -->
<section class="presentation">
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
</section>

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

<!-- ================================================================
     SECTION INFORMATIONS PRATIQUES
     ================================================================ -->
<section class="presentation mt-4">
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
</section>

<!-- ================================================================
     APPEL À L'ACTION
     ================================================================ -->
<section class="text-center mt-4 mb-4">
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

<?php
// Styles spécifiques à la page d'accueil
?>
<style>
    .hero {
        padding: var(--spacing-xl) var(--spacing-md);
        background: linear-gradient(135deg, var(--cyan-clair) 0%, var(--blanc) 100%);
        border-radius: var(--border-radius-lg);
        margin-bottom: var(--spacing-xl);
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
        font-size: 1.1rem;
        color: var(--gris-fonce);
        font-weight: 600;
    }
    
    .section-salles {
        padding: var(--spacing-xl) 0;
    }
    
    .info-grid h3 {
        margin-bottom: var(--spacing-sm);
    }
</style>

<?php require_once 'includes/footer.php'; ?>
