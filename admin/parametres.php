<?php
/**
 * ============================================================================
 * SAE203 E-LLUSION - Paramètres du compte admin
 * ============================================================================
 * Page permettant de modifier l'email et le mot de passe de l'administrateur
 * ============================================================================
 */

// Inclusion des fonctions
require_once __DIR__ . '/../includes/functions.php';

// Protection de la page
protegerPageAdmin();

// Configuration de la page
$pageTitle = "Paramètres du compte";
$pageDescription = "Modifier vos informations de connexion.";
$pageActive = "";

// Variables
$erreur = '';
$succes = '';
$pdo = getPDO();

// ============================================================================
// TRAITEMENT DU FORMULAIRE
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Vérification CSRF
    $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verifierCsrf($csrf_token)) {
        $erreur = "Erreur de sécurité. Veuillez actualiser la page et réessayer.";
    } else {
        
        // Récupération des données
        $motDePasseActuel = $_POST['password_actuel'] ?? '';
        $nouvelEmail = nettoyer($_POST['email'] ?? '');
        $nouveauMotDePasse = $_POST['nouveau_password'] ?? '';
        $confirmationMotDePasse = $_POST['confirmation_password'] ?? '';
        
        // Validation
        if (empty($motDePasseActuel)) {
            $erreur = "Veuillez entrer votre mot de passe actuel pour confirmer les modifications.";
        } else {
            
            // Vérifier le mot de passe actuel
            $stmt = $pdo->prepare("
                SELECT mot_de_passe 
                FROM utilisateurs 
                WHERE id_utilisateur = :id
            ");
            $stmt->bindValue(':id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($motDePasseActuel, $user['mot_de_passe'])) {
                $erreur = "Le mot de passe actuel est incorrect.";
            } else {
                
                // Préparer les champs à mettre à jour
                $updates = [];
                $params = [':id' => $_SESSION['user_id']];
                
                // Modification de l'email
                if (!empty($nouvelEmail) && $nouvelEmail !== $_SESSION['user_email']) {
                    if (!validerEmail($nouvelEmail)) {
                        $erreur = "L'adresse email n'est pas valide.";
                    } else {
                        // Vérifier que l'email n'est pas déjà utilisé
                        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM utilisateurs WHERE email = :email AND id_utilisateur != :id");
                        $stmt->execute([':email' => $nouvelEmail, ':id' => $_SESSION['user_id']]);
                        if ($stmt->fetch()['total'] > 0) {
                            $erreur = "Cet email est déjà utilisé par un autre compte.";
                        } else {
                            $updates[] = "email = :email";
                            $params[':email'] = $nouvelEmail;
                        }
                    }
                }
                
                // Modification du mot de passe
                if (empty($erreur) && !empty($nouveauMotDePasse)) {
                    if (strlen($nouveauMotDePasse) < 6) {
                        $erreur = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
                    } elseif ($nouveauMotDePasse !== $confirmationMotDePasse) {
                        $erreur = "Les deux mots de passe ne correspondent pas.";
                    } else {
                        $updates[] = "mot_de_passe = :password";
                        $params[':password'] = password_hash($nouveauMotDePasse, PASSWORD_DEFAULT);
                    }
                }
                
                // Exécuter la mise à jour si pas d'erreur
                if (empty($erreur)) {
                    if (empty($updates)) {
                        $erreur = "Aucune modification à enregistrer.";
                    } else {
                        $sql = "UPDATE utilisateurs SET " . implode(', ', $updates) . " WHERE id_utilisateur = :id";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($params);
                        
                        // Mettre à jour la session si l'email a changé
                        if (isset($params[':email'])) {
                            $_SESSION['user_email'] = $nouvelEmail;
                        }
                        
                        $succes = "Vos informations ont été mises à jour avec succès !";
                        
                        // Régénérer le token CSRF
                        regenererCsrf();
                    }
                }
            }
        }
    }
}

// Récupérer les informations actuelles
$stmt = $pdo->prepare("
    SELECT email, nom, prenom, role, agence 
    FROM utilisateurs 
    WHERE id_utilisateur = :id
");
$stmt->bindValue(':id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();
$userInfo = $stmt->fetch();

// Inclusion du header
require_once __DIR__ . '/../includes/header.php';
?>

<!-- ================================================================
     EN-TÊTE ADMIN
     ================================================================ -->
<div class="admin-header">
    <div>
        <h1 class="admin-title">Paramètres du compte</h1>
        <p>Modifier vos informations de connexion</p>
    </div>
    <div class="admin-actions">
        <a href="dashboard.php" class="btn btn-secondary">
            ← Retour au dashboard
        </a>
        <a href="logout.php" class="btn btn-secondary">
            Déconnexion
        </a>
    </div>
</div>

<!-- ================================================================
     MESSAGES
     ================================================================ -->
<?php if (!empty($erreur)): ?>
<div class="alert alert-error">
    <?php echo sanitize($erreur); ?>
</div>
<?php endif; ?>

<?php if (!empty($succes)): ?>
<div class="alert alert-success">
    <?php echo sanitize($succes); ?>
</div>
<?php endif; ?>

<!-- ================================================================
     FORMULAIRE DE MODIFICATION
     ================================================================ -->
<div class="parametres-container">
    
    <!-- Informations actuelles -->
    <section class="info-section">
        <h2>📋 Informations actuelles</h2>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Nom :</span>
                <span class="info-value"><?php echo sanitize($userInfo['nom'] . ' ' . $userInfo['prenom']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Email actuel :</span>
                <span class="info-value"><?php echo sanitize($userInfo['email']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Rôle :</span>
                <span class="info-value"><?php echo sanitize($userInfo['role']); ?></span>
            </div>
            <?php if (!empty($userInfo['agence'])): ?>
            <div class="info-item">
                <span class="info-label">Agence :</span>
                <span class="info-value"><?php echo sanitize($userInfo['agence']); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Formulaire de modification -->
    <section class="form-section">
        <h2>✏️ Modifier mes informations</h2>
        
        <form method="POST" action="parametres.php">
            <!-- Token CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            
            <!-- Mot de passe actuel (obligatoire pour toute modification) -->
            <div class="form-group">
                <label for="password_actuel" class="form-label form-label-required">
                    Mot de passe actuel (requis pour valider les modifications)
                </label>
                <input type="password" 
                       id="password_actuel" 
                       name="password_actuel" 
                       class="form-input" 
                       required
                       autocomplete="current-password">
                <p class="form-hint">Pour des raisons de sécurité, vous devez confirmer votre mot de passe actuel.</p>
            </div>
            
            <hr style="margin: 2rem 0; border: none; border-top: 1px solid #e0e0e0;">
            
            <!-- Nouvel email -->
            <div class="form-group">
                <label for="email" class="form-label">Nouvel email (optionnel)</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       class="form-input" 
                       value="<?php echo sanitize($userInfo['email']); ?>"
                       autocomplete="email">
                <p class="form-hint">Laissez tel quel si vous ne souhaitez pas modifier votre email.</p>
            </div>
            
            <!-- Nouveau mot de passe -->
            <div class="form-group">
                <label for="nouveau_password" class="form-label">Nouveau mot de passe (optionnel)</label>
                <input type="password" 
                       id="nouveau_password" 
                       name="nouveau_password" 
                       class="form-input" 
                       autocomplete="new-password"
                       minlength="6">
                <p class="form-hint">Minimum 6 caractères. Laissez vide si vous ne souhaitez pas changer le mot de passe.</p>
            </div>
            
            <!-- Confirmation du nouveau mot de passe -->
            <div class="form-group">
                <label for="confirmation_password" class="form-label">Confirmer le nouveau mot de passe</label>
                <input type="password" 
                       id="confirmation_password" 
                       name="confirmation_password" 
                       class="form-input" 
                       autocomplete="new-password"
                       minlength="6">
            </div>
            
            <!-- Bouton de soumission -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">
                    💾 Enregistrer les modifications
                </button>
            </div>
        </form>
    </section>
    
    <!-- Avertissement de sécurité -->
    <section class="warning-section">
        <h3>⚠️ Conseils de sécurité</h3>
        <ul>
            <li>Utilisez un mot de passe fort (majuscules, minuscules, chiffres, caractères spéciaux)</li>
            <li>Ne partagez jamais vos identifiants</li>
            <li>Changez régulièrement votre mot de passe</li>
            <li>Déconnectez-vous après chaque session, surtout sur un ordinateur partagé</li>
        </ul>
    </section>
    
</div>

<style>
    .parametres-container {
        max-width: 800px;
        margin: 0 auto;
    }
    
    .info-section,
    .form-section,
    .warning-section {
        background: var(--blanc);
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-xl);
        margin-bottom: var(--spacing-xl);
        box-shadow: var(--shadow);
    }
    
    .info-section h2,
    .form-section h2,
    .warning-section h3 {
        color: var(--cyan-fonce);
        margin-bottom: var(--spacing-lg);
    }
    
    .info-grid {
        display: grid;
        gap: var(--spacing-md);
    }
    
    .info-item {
        display: flex;
        justify-content: space-between;
        padding: var(--spacing-md);
        background: var(--gris-clair);
        border-radius: var(--border-radius);
    }
    
    .info-label {
        font-weight: 600;
        color: var(--gris-fonce);
    }
    
    .info-value {
        color: var(--noir);
    }
    
    .warning-section {
        background: #fff3e0;
        border-left: 4px solid var(--warning);
    }
    
    .warning-section h3 {
        color: var(--warning);
        margin-top: 0;
    }
    
    .warning-section ul {
        margin: 0;
        padding-left: var(--spacing-lg);
    }
    
    .warning-section li {
        margin-bottom: var(--spacing-sm);
        color: var(--gris-fonce);
    }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
