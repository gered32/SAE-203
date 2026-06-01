<?php
/**
 * ============================================================================
 * SAE203 E-LLUSION - Page de connexion admin
 * ============================================================================
 * Authentification des administrateurs et référents.
 * 
 * SÉCURITÉ :
 * - Vérification du mot de passe avec password_verify()
 * - Régénération de l'ID de session après connexion
 * - Protection CSRF
 * - Limitation des informations d'erreur
 * ============================================================================
 */

// Inclusion des fonctions
require_once __DIR__ . '/includes/functions.php';

// Si déjà connecté, rediriger vers le dashboard
if (estConnecte()) {
    redirect('admin/dashboard.php');
}

// Configuration de la page
$pageTitle = "Connexion";
$pageDescription = "Espace de connexion réservé aux administrateurs de l'exposition E-LLUSION.";
$pageActive = "";

// Variables du formulaire
$erreur = '';
$email = '';

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
        $email = nettoyer($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validation basique
        if (empty($email) || empty($password)) {
            $erreur = "Veuillez remplir tous les champs.";
        } elseif (!validerEmail($email)) {
            $erreur = "L'adresse email n'est pas valide.";
        } else {
            
            // Recherche de l'utilisateur en base de données
            $pdo = getPDO();
            $stmt = $pdo->prepare("
                SELECT id_utilisateur, email, mot_de_passe, nom, prenom, role, agence
                FROM utilisateurs 
                WHERE email = :email
            ");
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $utilisateur = $stmt->fetch();
            
            if ($utilisateur && password_verify($password, $utilisateur['mot_de_passe'])) {
                // ============================================================
                // CONNEXION RÉUSSIE
                // ============================================================
                
                // Régénération de l'ID de session (sécurité contre fixation de session)
                session_regenerate_id(true);
                
                // Stockage des informations en session
                $_SESSION['user_id'] = $utilisateur['id_utilisateur'];
                $_SESSION['user_email'] = $utilisateur['email'];
                $_SESSION['user_nom'] = $utilisateur['nom'];
                $_SESSION['user_prenom'] = $utilisateur['prenom'];
                $_SESSION['role'] = $utilisateur['role'];
                $_SESSION['agence'] = $utilisateur['agence'];
                $_SESSION['login_time'] = time();
                
                // Régénérer le token CSRF
                regenererCsrf();
                
                // Redirection vers le dashboard
                redirect('admin/dashboard.php');
                
            } else {
                // ============================================================
                // ÉCHEC DE CONNEXION
                // ============================================================
                // Message générique pour ne pas révéler si l'email existe
                $erreur = "Email ou mot de passe incorrect.";
                
                // En production, on pourrait ajouter une temporisation ou un compteur de tentatives
            }
        }
    }
}

// Inclusion du header
require_once __DIR__ . '/includes/header.php';
?>

<!-- ================================================================
     PAGE DE CONNEXION
     ================================================================ -->
<div class="login-container">
    <div class="login-box">
        
        <!-- Logo / Titre -->
        <div class="login-logo">
            <h1>E-LLUSION</h1>
            <p>Espace Administration</p>
        </div>
        
        <!-- Message d'erreur -->
        <?php if (!empty($erreur)): ?>
        <div class="flash-message flash-error" role="alert">
            <div class="flash-content">
                <p><?php echo sanitize($erreur); ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Formulaire de connexion -->
        <form method="POST" action="login.php">
            <!-- Token CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            
            <!-- Email -->
            <div class="form-group">
                <label for="email" class="form-label form-label-required">Email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       class="form-input" 
                       value="<?php echo sanitize($email); ?>"
                       placeholder="admin@ellusion.fr"
                       required
                       autocomplete="email"
                       autofocus>
            </div>
            
            <!-- Mot de passe -->
            <div class="form-group">
                <label for="password" class="form-label form-label-required">Mot de passe</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="form-input" 
                       placeholder="Votre mot de passe"
                       required
                       autocomplete="current-password">
            </div>
            
            <!-- Bouton de connexion -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">
                    Se connecter
                </button>
            </div>
        </form>
        
        <!-- Lien retour -->
        <div class="login-footer">
            <a href="index.php">&larr; Retour au site</a>
        </div>
        
    </div>
</div>

<style>
    .login-container {
        max-width: 400px;
        margin: 0 auto;
        padding: var(--spacing-xl) var(--spacing-md);
    }
    
    .login-box {
        background: var(--blanc);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-lg);
        padding: var(--spacing-xl);
    }
    
    .login-logo {
        text-align: center;
        margin-bottom: var(--spacing-xl);
    }
    
    .login-logo h1 {
        font-size: 2.5rem;
        margin-bottom: var(--spacing-xs);
    }
    
    .login-logo p {
        color: var(--gris);
        margin: 0;
    }
    
    .login-footer {
        text-align: center;
        margin-top: var(--spacing-lg);
        padding-top: var(--spacing-lg);
        border-top: 1px solid var(--gris-clair);
    }
    
    .login-footer a {
        color: var(--gris);
        font-size: var(--font-size-small);
    }
    
    .login-footer a:hover {
        color: var(--cyan-fonce);
    }
</style>

<?php require_once 'includes/footer.php'; ?>
