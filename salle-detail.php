<?php
/**
 * ============================================================================
 * SAE203 E-LLUSION - Détail d'une salle
 * ============================================================================
 * Affiche les informations détaillées d'une salle :
 * - Description complète
 * - Liste des œuvres exposées
 * - Lien pour réserver un créneau
 * ============================================================================
 */

// Inclusion des fonctions
require_once __DIR__ . '/includes/functions.php';

// ============================================================================
// RÉCUPÉRATION ET VALIDATION DE L'ID
// ============================================================================

// Récupérer l'ID depuis l'URL avec validation
$id_salle = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Vérifier que l'ID est valide
if ($id_salle <= 0) {
    redirectAvecMessage('salles.php', 'Salle non trouvée.', 'error');
}

// Récupérer la salle depuis la base de données
$salle = getSalleById($id_salle);

// Vérifier que la salle existe
if (!$salle) {
    redirectAvecMessage('salles.php', 'Cette salle n\'existe pas.', 'error');
}

// Récupérer les œuvres de la salle
$oeuvres = getOeuvresBySalle($id_salle);

// ============================================================================
// CONFIGURATION DE LA PAGE
// ============================================================================
$pageTitle = "Salle " . $salle['numero'] . " - " . $salle['nom'];
$pageDescription = "Découvrez la " . $salle['nom'] . " de l'exposition E-LLUSION et ses " . count($oeuvres) . " œuvres uniques.";
$pageActive = "salles";

// Inclusion du header
require_once __DIR__ . '/includes/header.php';
?>

<!-- ================================================================
     EN-TÊTE DE LA SALLE
     ================================================================ -->
<section class="salle-header">
    <!-- Image principale -->
    <img src="<?php echo SITE_URL; ?>/assets/images/<?php echo sanitize($salle['image']); ?>" 
         alt="Vue de la <?php echo sanitize($salle['nom']); ?>"
         class="salle-image">
    
    <!-- Informations -->
    <div class="salle-info">
        <span class="card-numero">Salle <?php echo sanitize($salle['numero']); ?></span>
        <h1><?php echo sanitize($salle['nom']); ?></h1>
        <p class="salle-description">
            <?php echo sanitize($salle['description']); ?>
        </p>
        
        <!-- Statistiques -->
        <div class="salle-stats">
            <div class="stat">
                <span class="stat-number"><?php echo count($oeuvres); ?></span>
                <span class="stat-label">œuvre<?php echo count($oeuvres) > 1 ? 's' : ''; ?></span>
            </div>
            <div class="stat">
                <span class="stat-number"><?php echo $salle['capacite_max']; ?></span>
                <span class="stat-label">places/créneau</span>
            </div>
        </div>
        
        <!-- Bouton réservation -->
        <div class="btn-group mt-3">
            <a href="inscription.php?salle=<?php echo $salle['id_salle']; ?>" class="btn btn-primary">
                Réserver cette salle
            </a>
            <a href="salles.php" class="btn btn-secondary">
                Retour aux salles
            </a>
        </div>
    </div>
</section>

<!-- ================================================================
     SECTION ŒUVRES
     ================================================================ -->
<section class="section-oeuvres mt-4">
    <h2>Les œuvres exposées</h2>
    
    <?php if (empty($oeuvres)): ?>
    <p class="text-center">Aucune œuvre n'est encore référencée pour cette salle.</p>
    <?php else: ?>
    
    <div class="cards-grid oeuvres-grid">
        <?php foreach ($oeuvres as $oeuvre): ?>
        <article class="card oeuvre-card">
            <!-- Image de l'œuvre -->
            <img src="<?php echo SITE_URL; ?>/assets/images/<?php echo sanitize($oeuvre['image']); ?>" 
                 alt="<?php echo sanitize($oeuvre['titre']); ?> par <?php echo sanitize($oeuvre['artiste']); ?>"
                 class="card-image"
                 loading="lazy">
            
            <div class="card-content">
                <!-- Titre de l'œuvre -->
                <h3 class="card-title"><?php echo sanitize($oeuvre['titre']); ?></h3>
                
                <!-- Artiste -->
                <p class="oeuvre-artiste">
                    <strong>Artiste :</strong> <?php echo sanitize($oeuvre['artiste']); ?>
                </p>
                
                <!-- Description -->
                <p class="card-description">
                    <?php echo sanitize($oeuvre['description']); ?>
                </p>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    
    <?php endif; ?>
</section>

<!-- ================================================================
     NAVIGATION ENTRE SALLES
     ================================================================ -->
<section class="navigation-salles mt-4">
    <h2 class="text-center">Découvrez les autres salles</h2>
    
    <?php
    // Récupérer toutes les salles pour la navigation
    $toutesLesSalles = getSalles();
    ?>
    
    <div class="cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <?php foreach ($toutesLesSalles as $autreSalle): 
            // Ne pas afficher la salle actuelle
            if ($autreSalle['id_salle'] == $id_salle) continue;
        ?>
        <a href="salle-detail.php?id=<?php echo $autreSalle['id_salle']; ?>" class="nav-salle-link">
            <span class="nav-salle-numero">Salle <?php echo sanitize($autreSalle['numero']); ?></span>
            <span class="nav-salle-nom"><?php echo sanitize($autreSalle['nom']); ?></span>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<?php
// Styles spécifiques à la page
?>
<style>
    .salle-description {
        font-size: var(--font-size-large);
        line-height: 1.8;
        color: var(--gris-fonce);
    }
    
    .salle-stats {
        display: flex;
        gap: var(--spacing-xl);
        margin-top: var(--spacing-lg);
        padding: var(--spacing-md);
        background: var(--cyan-clair);
        border-radius: var(--border-radius);
    }
    
    .stat {
        text-align: center;
    }
    
    .stat-number {
        display: block;
        font-size: var(--font-size-h2);
        font-weight: 700;
        color: var(--cyan-fonce);
    }
    
    .stat-label {
        font-size: var(--font-size-small);
        color: var(--noir);
    }
    
    .section-oeuvres {
        padding: var(--spacing-xl) 0;
        border-top: 2px solid var(--cyan-clair);
    }
    
    .oeuvres-grid {
        grid-template-columns: 1fr;
    }
    
    @media (min-width: 768px) {
        .oeuvres-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    .oeuvre-artiste {
        color: var(--cyan-fonce);
        font-size: var(--font-size-small);
        margin-bottom: var(--spacing-sm);
    }
    
    .navigation-salles {
        padding: var(--spacing-xl) 0;
        border-top: 2px solid var(--cyan-clair);
    }
    
    .nav-salle-link {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: var(--spacing-lg);
        background: var(--blanc);
        border: 2px solid var(--cyan-clair);
        border-radius: var(--border-radius);
        text-decoration: none;
        transition: all var(--transition);
    }
    
    .nav-salle-link:hover {
        background: var(--cyan-clair);
        border-color: var(--cyan-fonce);
        transform: translateY(-3px);
    }
    
    .nav-salle-numero {
        font-size: var(--font-size-small);
        color: var(--cyan-fonce);
        font-weight: 700;
    }
    
    .nav-salle-nom {
        color: var(--noir);
        font-weight: 600;
    }
</style>

<?php require_once 'includes/footer.php'; ?>
