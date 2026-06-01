<?php
/**
 * ============================================================================
 * SAE203 E-LLUSION - Liste des salles
 * ============================================================================
 * Affiche les 4 salles de l'exposition sous forme de cartes.
 * Chaque carte présente :
 * - Le numéro de la salle
 * - Le nom de la salle
 * - Le nombre d'œuvres
 * - Un bouton pour découvrir la salle
 * ============================================================================
 */

// Configuration de la page
$pageTitle = "Les Salles";
$pageDescription = "Découvrez les 4 salles de l'exposition E-LLUSION : Salle Immersive, Interactive, Contemplative et Expérimentale.";
$pageActive = "salles";

// Inclusion du header (qui inclut functions.php et config.php)
require_once 'includes/header.php';

// Récupération des salles depuis la base de données
$salles = getSalles();
?>

<!-- ================================================================
     EN-TÊTE DE PAGE
     ================================================================ -->
<section class="page-header text-center">
    <h1>Les Salles</h1>
    <p class="page-subtitle">
        Quatre univers, quatre expériences uniques. Choisissez votre parcours et laissez-vous guider 
        par vos sens à travers notre exposition multimédia.
    </p>
</section>

<!-- ================================================================
     GRILLE DES SALLES
     ================================================================ -->
<section class="section-salles">
    <div class="cards-grid">
        <?php foreach ($salles as $salle): 
            // Compter le nombre d'œuvres dans cette salle
            $nbOeuvres = countOeuvresBySalle($salle['id_salle']);
        ?>
        <article class="card">
            <!-- Image de la salle -->
            <img src="<?php echo SITE_URL; ?>/assets/images/<?php echo sanitize($salle['image']); ?>" 
                 alt="Vue de la <?php echo sanitize($salle['nom']); ?>"
                 class="card-image"
                 loading="lazy">
            
            <div class="card-content">
                <!-- Numéro de la salle -->
                <span class="card-numero">Salle <?php echo sanitize($salle['numero']); ?></span>
                
                <!-- Nom de la salle -->
                <h2 class="card-title"><?php echo sanitize($salle['nom']); ?></h2>
                
                <!-- Description courte -->
                <p class="card-description">
                    <?php 
                    // Afficher les 150 premiers caractères de la description
                    $description = $salle['description'];
                    if (strlen($description) > 150) {
                        $description = substr($description, 0, 150) . '...';
                    }
                    echo sanitize($description);
                    ?>
                </p>
                
                <!-- Nombre d'œuvres (avec point rouge) -->
                <p class="card-meta">
                    <?php echo $nbOeuvres; ?> œuvre<?php echo $nbOeuvres > 1 ? 's' : ''; ?> à découvrir
                </p>
                
                <!-- Boutons d'action -->
                <div class="btn-group">
                    <a href="salle-detail.php?id=<?php echo $salle['id_salle']; ?>" class="btn btn-primary">
                        Découvrir
                    </a>
                    <a href="inscription.php?salle=<?php echo $salle['id_salle']; ?>" class="btn btn-secondary">
                        Réserver
                    </a>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</section>

<!-- ================================================================
     INFORMATIONS COMPLÉMENTAIRES
     ================================================================ -->
<section class="presentation mt-4">
    <h2>Comment ça marche ?</h2>
    <p>
        Chaque salle peut accueillir jusqu'à <strong>12 visiteurs par créneau</strong> de 30 minutes.
        Cette jauge limitée garantit une expérience immersive optimale et un accès privilégié aux œuvres.
    </p>
    <p>
        Lors de votre inscription, vous choisissez la salle et le créneau horaire qui vous conviennent.
        Vous pouvez ensuite modifier ou annuler votre réservation à tout moment grâce au lien unique 
        que vous recevrez par email.
    </p>
    <div class="text-center mt-3">
        <a href="inscription.php" class="btn btn-primary">Réserver un créneau</a>
    </div>
</section>

<?php
// Styles spécifiques à la page
?>
<style>
    .page-header {
        padding: var(--spacing-xl) var(--spacing-md);
        margin-bottom: var(--spacing-xl);
    }
    
    .page-header h1 {
        margin-bottom: var(--spacing-md);
    }
    
    .page-subtitle {
        max-width: 700px;
        margin: 0 auto;
        font-size: var(--font-size-large);
        color: var(--gris-fonce);
    }
    
    /* Afficher 2 colonnes sur les cartes pour cette page */
    @media (min-width: 768px) {
        .section-salles .cards-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<?php require_once 'includes/footer.php'; ?>
