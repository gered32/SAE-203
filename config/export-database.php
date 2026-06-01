<?php
/**
 * ============================================================================
 * SAE203 E-LLUSION - Script d'export de la base de données (version PHP)
 * ============================================================================
 * Ce script exporte la base de données MySQL vers le fichier database.sql
 * 
 * UTILISATION :
 * - Via navigateur : http://localhost/SAE203_Niels/SAE-203/config/export-database.php
 * - Via terminal : php export-database.php
 * 
 * IMPORTANT : Supprimez ou protégez ce fichier en production !
 * ============================================================================
 */

// Configuration
define('MYSQL_BIN', 'D:\\Xampp\\mysql\\bin\\');
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sae203_ellusion');
define('OUTPUT_FILE', __DIR__ . '/database.sql');

// Mode CLI ou Web
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    echo '<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Export Base de Données - E-LLUSION</title>
        <style>
            body {
                font-family: "Courier New", monospace;
                background: #1a1a1a;
                color: #3ce8d7;
                padding: 20px;
                line-height: 1.6;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
                background: #2a2a2a;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 0 20px rgba(60, 232, 215, 0.3);
            }
            h1 {
                color: #00bbaa;
                border-bottom: 2px solid #3ce8d7;
                padding-bottom: 10px;
            }
            .success { color: #0f0; }
            .error { color: #f00; }
            .info { color: #ff0; }
            pre {
                background: #1a1a1a;
                padding: 15px;
                border-radius: 5px;
                overflow-x: auto;
            }
            .steps {
                background: #0a3a3a;
                padding: 20px;
                border-radius: 5px;
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <div class="container">';
}

// Fonction d'affichage
function output($message, $type = 'info') {
    global $isCLI;
    
    $colors = [
        'success' => $isCLI ? "\033[32m" : '<span class="success">',
        'error' => $isCLI ? "\033[31m" : '<span class="error">',
        'info' => $isCLI ? "\033[33m" : '<span class="info">',
        'reset' => $isCLI ? "\033[0m" : '</span>'
    ];
    
    $color = $colors[$type] ?? $colors['info'];
    $reset = $colors['reset'];
    
    if ($isCLI) {
        echo $color . $message . $reset . PHP_EOL;
    } else {
        echo "<p>" . $color . nl2br(htmlspecialchars($message)) . $reset . "</p>";
    }
}

// Ligne de séparation
function separator() {
    output(str_repeat('=', 60), 'info');
}

// Début du script
separator();
output("  EXPORT DE LA BASE DE DONNÉES", 'success');
output("  E-LLUSION - SAE203", 'success');
separator();
output("");
output("Démarrage de l'export...", 'info');
output("");

// Vérifier la connexion MySQL
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );
    output("✅ Connexion MySQL réussie", 'success');
} catch (PDOException $e) {
    output("❌ ERREUR : Impossible de se connecter à MySQL !", 'error');
    output("Message : " . $e->getMessage(), 'error');
    output("", 'info');
    output("Vérifiez que :", 'info');
    output(" - XAMPP est démarré", 'info');
    output(" - Le service MySQL est actif", 'info');
    output(" - Les identifiants de connexion sont corrects", 'info');
    if (!$isCLI) echo '</div></body></html>';
    exit(1);
}

// Construire la commande mysqldump
$mysqldumpPath = MYSQL_BIN . 'mysqldump.exe';

if (!file_exists($mysqldumpPath)) {
    output("❌ ERREUR : mysqldump.exe introuvable !", 'error');
    output("Chemin recherché : " . $mysqldumpPath, 'error');
    if (!$isCLI) echo '</div></body></html>';
    exit(1);
}

$command = sprintf(
    '"%s" -u %s --add-drop-table --comments --dump-date --complete-insert --skip-extended-insert --default-character-set=utf8mb4 --set-charset --routines --triggers --events %s > "%s" 2>&1',
    $mysqldumpPath,
    DB_USER,
    DB_NAME,
    OUTPUT_FILE
);

// Exécuter l'export
output("Exécution de mysqldump...", 'info');
exec($command, $outputLines, $returnCode);

if ($returnCode === 0 && file_exists(OUTPUT_FILE) && filesize(OUTPUT_FILE) > 0) {
    output("", 'info');
    output("✅ EXPORT RÉUSSI !", 'success');
    output("", 'info');
    output("Le fichier database.sql a été mis à jour.", 'success');
    output("", 'info');
    
    // Afficher la taille du fichier
    $fileSize = filesize(OUTPUT_FILE);
    output("Taille du fichier : " . number_format($fileSize) . " octets (" . round($fileSize / 1024, 2) . " Ko)", 'info');
    output("", 'info');
    
    // Statistiques de la base
    separator();
    output("  STATISTIQUES DE LA BASE", 'success');
    separator();
    output("", 'info');
    
    // Compter les tables
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");
    $nbTables = $stmt->fetch()['total'];
    output("Nombre de tables exportées : " . $nbTables, 'info');
    
    // Lister les tables avec leur taille
    $stmt = $pdo->query("
        SELECT 
            table_name,
            table_rows,
            ROUND(((data_length + index_length) / 1024), 2) AS size_kb
        FROM information_schema.tables 
        WHERE table_schema = '" . DB_NAME . "'
        ORDER BY table_name
    ");
    
    output("", 'info');
    output("Liste des tables :", 'info');
    foreach ($stmt->fetchAll() as $table) {
        output("  - {$table['table_name']} : {$table['table_rows']} ligne(s), {$table['size_kb']} Ko", 'info');
    }
    
    output("", 'info');
    separator();
    output("  PROCHAINES ÉTAPES", 'success');
    separator();
    
    if (!$isCLI) {
        echo '<div class="steps">';
    }
    
    output("", 'info');
    output("1. Vérifiez le fichier database.sql", 'info');
    output("2. Commitez les modifications avec Git :", 'info');
    output("", 'info');
    output("   cd ..", 'info');
    output("   git add config/database.sql", 'info');
    output('   git commit -m "Mise à jour de la base de données"', 'info');
    output("   git push origin main", 'info');
    output("", 'info');
    
    if (!$isCLI) {
        echo '</div>';
    }
    
    separator();
    
} else {
    output("", 'info');
    output("❌ ERREUR lors de l'export !", 'error');
    output("", 'info');
    
    if (!empty($outputLines)) {
        output("Détails de l'erreur :", 'error');
        foreach ($outputLines as $line) {
            output("  " . $line, 'error');
        }
    }
    
    output("", 'info');
    output("Vérifiez que :", 'info');
    output(" - MySQL est démarré dans XAMPP", 'info');
    output(" - La base de données 'sae203_ellusion' existe", 'info');
    output(" - Vous avez les droits d'accès nécessaires", 'info');
    output(" - Le chemin vers mysqldump est correct", 'info');
}

if (!$isCLI) {
    echo '</div></body></html>';
}
?>
