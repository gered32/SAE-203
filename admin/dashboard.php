<?php
/**
 * ============================================================================
 * SAE203 E-LLUSION - Dashboard Administration
 * ============================================================================
 * Tableau de bord affichant :
 * - Statistiques globales
 * - Liste de toutes les inscriptions
 * - État des jauges par créneau
 * - Export CSV
 * 
 * ACCÈS : Réservé aux utilisateurs authentifiés (admin/referent)
 * ============================================================================
 */

// Inclusion des fonctions
require_once __DIR__ . '/../includes/functions.php';

// Protection de la page
protegerPageAdmin();

// Configuration de la page
$pageTitle = "Dashboard Admin";
$pageDescription = "Tableau de bord de gestion des inscriptions E-LLUSION.";
$pageActive = "";

// ============================================================================
// RÉCUPÉRATION DES DONNÉES
// ============================================================================

$pdo = getPDO();

// ----------------------------------------------------------------------------
// STATISTIQUES GLOBALES
// ----------------------------------------------------------------------------

// Nombre total d'inscriptions
$stmtTotalInscriptions = $pdo->query("SELECT COUNT(*) as total FROM inscriptions");
$totalInscriptions = $stmtTotalInscriptions->fetch()['total'];

// Nombre total de personnes inscrites
$stmtTotalPersonnes = $pdo->query("SELECT COALESCE(SUM(nb_personnes), 0) as total FROM inscriptions");
$totalPersonnes = $stmtTotalPersonnes->fetch()['total'];

// Nombre de participants au buffet
$stmtBuffet = $pdo->query("SELECT COALESCE(SUM(nb_personnes), 0) as total FROM inscriptions WHERE buffet_jeudi = 1");
$totalBuffet = $stmtBuffet->fetch()['total'];

// Nombre de créneaux complets (places restantes = 0)
$sqlCreneauxComplets = "
    SELECT COUNT(*) as total
    FROM (
        SELECT c.id_creneau,
               c.places_total - COALESCE(SUM(i.nb_personnes), 0) AS places_restantes
        FROM creneaux c
        LEFT JOIN reservations r ON c.id_creneau = r.id_creneau
        LEFT JOIN inscriptions i ON r.id_inscription = i.id_inscription
        GROUP BY c.id_creneau
        HAVING places_restantes <= 0
    ) AS creneaux_complets
";
$stmtCreneauxComplets = $pdo->query($sqlCreneauxComplets);
$creneauxComplets = $stmtCreneauxComplets->fetch()['total'];

// ----------------------------------------------------------------------------
// LISTE DES INSCRIPTIONS (avec JOIN complet)
// ----------------------------------------------------------------------------

$sqlInscriptions = "
    SELECT 
        i.id_inscription,
        i.nom,
        i.prenom,
        i.email,
        i.nb_personnes,
        i.buffet_jeudi,
        i.date_inscription,
        i.token,
        cat.nom AS categorie,
        s.numero AS salle_numero,
        s.nom AS salle_nom,
        cr.date_creneau,
        cr.heure
    FROM inscriptions i
    JOIN categories cat ON i.id_categorie = cat.id_categorie
    JOIN reservations r ON i.id_inscription = r.id_inscription
    JOIN creneaux cr ON r.id_creneau = cr.id_creneau
    JOIN salles s ON cr.id_salle = s.id_salle
    ORDER BY i.date_inscription DESC
";
$inscriptions = $pdo->query($sqlInscriptions)->fetchAll();

// ----------------------------------------------------------------------------
// ÉTAT DES JAUGES PAR CRÉNEAU
// ----------------------------------------------------------------------------

$sqlJauges = "
    SELECT 
        cr.id_creneau,
        s.numero AS salle_numero,
        s.nom AS salle_nom,
        cr.date_creneau,
        cr.heure,
        cr.places_total,
        COALESCE(SUM(i.nb_personnes), 0) AS places_occupees,
        cr.places_total - COALESCE(SUM(i.nb_personnes), 0) AS places_restantes
    FROM creneaux cr
    JOIN salles s ON cr.id_salle = s.id_salle
    LEFT JOIN reservations r ON cr.id_creneau = r.id_creneau
    LEFT JOIN inscriptions i ON r.id_inscription = i.id_inscription
    GROUP BY cr.id_creneau
    ORDER BY cr.date_creneau, cr.heure, s.numero
";
$jauges = $pdo->query($sqlJauges)->fetchAll();

// Inclusion du header
require_once __DIR__ . '/../includes/header.php';
?>

<!-- ================================================================
     EN-TÊTE ADMIN
     ================================================================ -->
<div class="admin-header">
    <div>
        <h1 class="admin-title">Dashboard</h1>
        <p>Bienvenue, <?php echo sanitize($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?></p>
    </div>
    <div class="admin-actions">
        <a href="parametres.php" class="btn btn-secondary">
            ⚙️ Paramètres
        </a>
        <a href="export-csv.php" class="btn btn-primary">
            Exporter CSV
        </a>
        <a href="logout.php" class="btn btn-secondary">
            Déconnexion
        </a>
    </div>
</div>

<!-- ================================================================
     MESSAGES FLASH
     ================================================================ -->
<?php if (isset($_SESSION['flash_success'])): ?>
<div class="alert alert-success">
    <?php 
    echo sanitize($_SESSION['flash_success']); 
    unset($_SESSION['flash_success']);
    ?>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
<div class="alert alert-error">
    <?php 
    echo sanitize($_SESSION['flash_error']); 
    unset($_SESSION['flash_error']);
    ?>
</div>
<?php endif; ?>

<!-- ================================================================
     STATISTIQUES
     ================================================================ -->
<section class="admin-stats">
    <div class="stat-card">
        <span class="stat-number"><?php echo $totalInscriptions; ?></span>
        <span class="stat-label">Inscriptions</span>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?php echo $totalPersonnes; ?></span>
        <span class="stat-label">Personnes inscrites</span>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?php echo $totalBuffet; ?></span>
        <span class="stat-label">Participants buffet</span>
    </div>
    <div class="stat-card">
        <span class="stat-number <?php echo $creneauxComplets > 0 ? 'indicateur-complet' : ''; ?>">
            <?php echo $creneauxComplets; ?>
        </span>
        <span class="stat-label">Créneaux complets</span>
    </div>
</section>

<!-- ================================================================
     ÉTAT DES JAUGES
     ================================================================ -->
<section class="section-jauges">
    <h2>État des jauges par créneau</h2>
    
    <div class="jauges-grid">
        <?php 
        $currentDate = '';
        foreach ($jauges as $jauge): 
            // Afficher un séparateur par date
            if ($jauge['date_creneau'] !== $currentDate):
                $currentDate = $jauge['date_creneau'];
        ?>
        <div class="jauge-date-header">
            <strong><?php echo formaterDate($jauge['date_creneau']); ?></strong>
        </div>
        <?php endif; ?>
        
        <div class="jauge-item <?php echo $jauge['places_restantes'] <= 0 ? 'complet' : ''; ?>">
            <div class="jauge-info">
                <span class="jauge-salle">Salle <?php echo sanitize($jauge['salle_numero']); ?></span>
                <span class="jauge-heure"><?php echo formaterHeure($jauge['heure']); ?></span>
            </div>
            <div class="jauge">
                <div class="jauge-bar">
                    <?php 
                    $pourcentage = ($jauge['places_occupees'] / $jauge['places_total']) * 100;
                    ?>
                    <div class="jauge-fill <?php echo $pourcentage >= 100 ? 'complet' : ''; ?>" 
                         style="width: <?php echo min(100, $pourcentage); ?>%"></div>
                </div>
                <span class="jauge-text">
                    <?php if ($jauge['places_restantes'] <= 0): ?>
                        <span class="indicateur-complet">COMPLET</span>
                    <?php else: ?>
                        <?php echo $jauge['places_restantes']; ?>/<?php echo $jauge['places_total']; ?>
                    <?php endif; ?>
                </span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ================================================================
     TABLEAU DES INSCRIPTIONS
     ================================================================ -->
<section class="section-inscriptions">
    <h2>Liste des inscriptions (<?php echo count($inscriptions); ?>)</h2>
    
    <?php if (empty($inscriptions)): ?>
    <p class="text-center">Aucune inscription pour le moment.</p>
    <?php else: ?>
    
    <!-- Barre de recherche et filtres -->
    <div class="filters-container">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Rechercher par nom, prénom ou email..." class="form-input">
        </div>
        <div class="filters-row">
            <select id="filterCategorie" class="form-select">
                <option value="">Toutes les catégories</option>
                <?php 
                $categories = $pdo->query("SELECT nom FROM categories ORDER BY nom")->fetchAll();
                foreach ($categories as $cat): 
                ?>
                    <option value="<?php echo sanitize($cat['nom']); ?>">
                        <?php echo sanitize($cat['nom']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select id="filterSalle" class="form-select">
                <option value="">Toutes les salles</option>
                <?php 
                $salles = $pdo->query("SELECT DISTINCT numero FROM salles ORDER BY numero")->fetchAll();
                foreach ($salles as $salle): 
                ?>
                    <option value="<?php echo sanitize($salle['numero']); ?>">
                        Salle <?php echo sanitize($salle['numero']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select id="filterBuffet" class="form-select">
                <option value="">Buffet (tous)</option>
                <option value="1">Oui</option>
                <option value="0">Non</option>
            </select>
            <button id="resetFilters" class="btn btn-secondary btn-sm">
                Réinitialiser
            </button>
        </div>
        <div class="results-count">
            <span id="resultCount"><?php echo count($inscriptions); ?></span> résultat(s) trouvé(s)
        </div>
    </div>
    
    <div class="table-container">
        <table class="table" id="inscriptionsTable">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Nom</th>
                    <th scope="col">Email</th>
                    <th scope="col">Catégorie</th>
                    <th scope="col">Salle</th>
                    <th scope="col">Date</th>
                    <th scope="col">Heure</th>
                    <th scope="col">Pers.</th>
                    <th scope="col">Buffet</th>
                    <th scope="col">Inscrit le</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inscriptions as $index => $insc): ?>
                <tr data-nom="<?php echo strtolower(sanitize($insc['nom'] . ' ' . $insc['prenom'])); ?>"
                    data-email="<?php echo strtolower(sanitize($insc['email'])); ?>"
                    data-categorie="<?php echo sanitize($insc['categorie']); ?>"
                    data-salle="<?php echo sanitize($insc['salle_numero']); ?>"
                    data-buffet="<?php echo $insc['buffet_jeudi']; ?>">
                    <td><?php echo $index + 1; ?></td>
                    <td>
                        <strong><?php echo sanitize($insc['nom'] . ' ' . $insc['prenom']); ?></strong>
                    </td>
                    <td>
                        <a href="mailto:<?php echo sanitize($insc['email']); ?>">
                            <?php echo sanitize($insc['email']); ?>
                        </a>
                    </td>
                    <td><?php echo sanitize($insc['categorie']); ?></td>
                    <td>
                        <span class="badge-salle">
                            <?php echo sanitize($insc['salle_numero']); ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($insc['date_creneau'])); ?></td>
                    <td><?php echo formaterHeure($insc['heure']); ?></td>
                    <td class="text-center"><?php echo $insc['nb_personnes']; ?></td>
                    <td class="text-center">
                        <?php if ($insc['buffet_jeudi']): ?>
                            <span class="badge-buffet">Oui</span>
                        <?php else: ?>
                            <span class="text-muted">Non</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php echo date('d/m/Y H:i', strtotime($insc['date_inscription'])); ?>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="edit-inscription.php?id=<?php echo $insc['id_inscription']; ?>" 
                               class="btn-action btn-edit" 
                               title="Modifier">
                                Modifier
                            </a>
                            <button onclick="confirmerSuppression(<?php echo $insc['id_inscription']; ?>, '<?php echo sanitize($insc['nom'] . ' ' . $insc['prenom']); ?>')" 
                                    class="btn-action btn-delete" 
                                    title="Supprimer">
                                Supprimer
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php endif; ?>
</section>

<!-- ================================================================
     ACTIONS RAPIDES
     ================================================================ -->
<section class="section-actions mt-4">
    <h2>Actions rapides</h2>
    
    <div class="btn-group">
        <a href="parametres.php" class="btn btn-secondary">
            ⚙️ Paramètres du compte
        </a>
        <a href="export-csv.php" class="btn btn-primary">
            Télécharger le tableur CSV
        </a>
        <a href="../index.php" class="btn btn-secondary" target="_blank">
            Voir le site public
        </a>
        <a href="../inscription.php" class="btn btn-secondary" target="_blank">
            Page d'inscription
        </a>
    </div>
</section>

<style>
    /* Responsive table - styles spécifiques à cette page */
    @media (max-width: 1024px) {
        .table {
            font-size: var(--font-size-small);
        }
        
        .table th,
        .table td {
            padding: var(--spacing-sm);
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .filters-row {
            flex-direction: column;
            align-items: stretch;
        }
        
        .form-select {
            width: 100%;
        }
    }
</style>

<script>
// ============================================================================
// FILTRAGE ET RECHERCHE
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const filterCategorie = document.getElementById('filterCategorie');
    const filterSalle = document.getElementById('filterSalle');
    const filterBuffet = document.getElementById('filterBuffet');
    const resetBtn = document.getElementById('resetFilters');
    const tableRows = document.querySelectorAll('#inscriptionsTable tbody tr');
    const resultCount = document.getElementById('resultCount');
    
    // Fonction de filtrage
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const categorieValue = filterCategorie.value;
        const salleValue = filterSalle.value;
        const buffetValue = filterBuffet.value;
        
        let visibleCount = 0;
        
        tableRows.forEach(row => {
            const nom = row.getAttribute('data-nom') || '';
            const email = row.getAttribute('data-email') || '';
            const categorie = row.getAttribute('data-categorie') || '';
            const salle = row.getAttribute('data-salle') || '';
            const buffet = row.getAttribute('data-buffet') || '';
            
            // Vérifier tous les critères
            const matchSearch = nom.includes(searchTerm) || email.includes(searchTerm);
            const matchCategorie = !categorieValue || categorie === categorieValue;
            const matchSalle = !salleValue || salle === salleValue;
            const matchBuffet = !buffetValue || buffet === buffetValue;
            
            if (matchSearch && matchCategorie && matchSalle && matchBuffet) {
                row.classList.remove('hidden');
                visibleCount++;
            } else {
                row.classList.add('hidden');
            }
        });
        
        // Mettre à jour le compteur
        resultCount.textContent = visibleCount;
    }
    
    // Écouteurs d'événements
    searchInput.addEventListener('input', filterTable);
    filterCategorie.addEventListener('change', filterTable);
    filterSalle.addEventListener('change', filterTable);
    filterBuffet.addEventListener('change', filterTable);
    
    // Réinitialiser les filtres
    resetBtn.addEventListener('click', function() {
        searchInput.value = '';
        filterCategorie.value = '';
        filterSalle.value = '';
        filterBuffet.value = '';
        filterTable();
    });
});

// ============================================================================
// CONFIRMATION DE SUPPRESSION
// ============================================================================

function confirmerSuppression(id, nom) {
    if (confirm('Êtes-vous sûr de vouloir supprimer l\'inscription de ' + nom + ' ?\n\nCette action est irréversible.')) {
        // Rediriger vers le script de suppression
        window.location.href = 'delete-inscription.php?id=' + id;
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
