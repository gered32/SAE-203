<?php
/**
 * Script de debug pour tester la connexion admin
 * SUPPRIMEZ CE FICHIER après utilisation !
 */

require_once __DIR__ . '/config/config.php';

echo "<h1>🔍 Debug de la connexion admin</h1>";
echo "<style>body{font-family:Arial;padding:20px;} pre{background:#f5f5f5;padding:15px;border-radius:5px;} .success{color:green;} .error{color:red;}</style>";

try {
    $pdo = getPDO();
    
    // 1. Vérifier si la table utilisateurs existe
    echo "<h2>1️⃣ Vérification de la table utilisateurs</h2>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'utilisateurs'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='success'>✅ Table 'utilisateurs' existe</p>";
    } else {
        echo "<p class='error'>❌ Table 'utilisateurs' n'existe PAS</p>";
        die();
    }
    
    // 2. Lister tous les utilisateurs
    echo "<h2>2️⃣ Liste des utilisateurs dans la base</h2>";
    $stmt = $pdo->query("SELECT id_utilisateur, email, nom, prenom, role, LEFT(mot_de_passe, 20) as mdp_debut, LENGTH(mot_de_passe) as mdp_longueur FROM utilisateurs");
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "<p class='error'>❌ Aucun utilisateur trouvé !</p>";
        die();
    }
    
    echo "<table border='1' cellpadding='10' style='border-collapse:collapse;'>";
    echo "<tr><th>ID</th><th>Email</th><th>Nom</th><th>Rôle</th><th>Hash (début)</th><th>Longueur</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user['id_utilisateur']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['nom']} {$user['prenom']}</td>";
        echo "<td>{$user['role']}</td>";
        echo "<td><code>{$user['mdp_debut']}...</code></td>";
        echo "<td>{$user['mdp_longueur']} caractères</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 3. Tester différents mots de passe
    echo "<h2>3️⃣ Test des mots de passe possibles</h2>";
    
    $stmt = $pdo->prepare("SELECT mot_de_passe FROM utilisateurs WHERE email = :email");
    $stmt->execute([':email' => 'admin@ellusion.fr']);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo "<p class='error'>❌ Utilisateur 'admin@ellusion.fr' non trouvé</p>";
        die();
    }
    
    $hashFromDB = $user['mot_de_passe'];
    echo "<p>Hash complet dans la BDD :<br><code>{$hashFromDB}</code></p>";
    
    $testPasswords = [
        'admin123',
        'usmb.ellusion',
        'password',
        'admin',
        'Admin123'
    ];
    
    echo "<table border='1' cellpadding='10' style='border-collapse:collapse;'>";
    echo "<tr><th>Mot de passe testé</th><th>Résultat</th></tr>";
    
    foreach ($testPasswords as $testPwd) {
        $isValid = password_verify($testPwd, $hashFromDB);
        $result = $isValid ? "<span class='success'>✅ VALIDE</span>" : "<span class='error'>❌ Invalide</span>";
        echo "<tr><td><strong>{$testPwd}</strong></td><td>{$result}</td></tr>";
    }
    echo "</table>";
    
    // 4. Test de connexion complète
    echo "<h2>4️⃣ Simulation de connexion complète</h2>";
    
    $email = 'admin@ellusion.fr';
    $password = 'usmb.ellusion';
    
    echo "<p>Test avec :</p>";
    echo "<ul>";
    echo "<li>Email : <strong>{$email}</strong></li>";
    echo "<li>Mot de passe : <strong>{$password}</strong></li>";
    echo "</ul>";
    
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $utilisateur = $stmt->fetch();
    
    if ($utilisateur) {
        echo "<p class='success'>✅ Utilisateur trouvé dans la BDD</p>";
        
        if (password_verify($password, $utilisateur['mot_de_passe'])) {
            echo "<p class='success'>✅✅✅ CONNEXION RÉUSSIE ! Le mot de passe est correct.</p>";
            echo "<div style='background:#d4edda;padding:20px;border-radius:5px;margin:20px 0;'>";
            echo "<h3>🎉 Identifiants valides :</h3>";
            echo "<ul>";
            echo "<li>Email : <strong>admin@ellusion.fr</strong></li>";
            echo "<li>Mot de passe : <strong>usmb.ellusion</strong></li>";
            echo "</ul>";
            echo "</div>";
        } else {
            echo "<p class='error'>❌ Mot de passe INCORRECT</p>";
            echo "<p>Le hash dans la BDD ne correspond à aucun des mots de passe testés.</p>";
        }
    } else {
        echo "<p class='error'>❌ Utilisateur non trouvé avec cet email</p>";
    }
    
    // 5. Informations de session
    echo "<h2>5️⃣ Informations de session PHP</h2>";
    echo "<ul>";
    echo "<li>Session démarrée : " . (session_status() === PHP_SESSION_ACTIVE ? "✅ Oui" : "❌ Non") . "</li>";
    echo "<li>Version PHP : " . phpversion() . "</li>";
    echo "<li>Extensions chargées : password_hash disponible : " . (function_exists('password_hash') ? "✅ Oui" : "❌ Non") . "</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur : " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>⚠️ IMPORTANT : Supprimez ce fichier après utilisation pour la sécurité !</strong></p>";
?>
