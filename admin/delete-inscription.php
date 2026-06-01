<?php
/**
 * ============================================================================
 * SAE203 E-LLUSION - Suppression d'une inscription
 * ============================================================================
 * Permet à l'administrateur de supprimer une inscription
 * 
 * ACCÈS : Réservé aux utilisateurs authentifiés (admin/referent)
 * ============================================================================
 */

// Inclusion des fonctions
require_once __DIR__ . '/../includes/functions.php';

// Protection de la page
protegerPageAdmin();

// ============================================================================
// TRAITEMENT DE LA SUPPRESSION
// ============================================================================

$pdo = getPDO();

// Vérifier que l'ID est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['flash_error'] = "ID d'inscription invalide.";
    header('Location: dashboard.php');
    exit;
}

$id_inscription = (int)$_GET['id'];

try {
    // Vérifier que l'inscription existe
    $stmtCheck = $pdo->prepare("SELECT nom, prenom FROM inscriptions WHERE id_inscription = :id");
    $stmtCheck->execute(['id' => $id_inscription]);
    $inscription = $stmtCheck->fetch();
    
    if (!$inscription) {
        $_SESSION['flash_error'] = "L'inscription n'existe pas.";
        header('Location: dashboard.php');
        exit;
    }
    
    // Démarrer une transaction
    $pdo->beginTransaction();
    
    // Supprimer d'abord les réservations associées (contrainte de clé étrangère)
    $stmtDeleteReservations = $pdo->prepare("DELETE FROM reservations WHERE id_inscription = :id");
    $stmtDeleteReservations->execute(['id' => $id_inscription]);
    
    // Supprimer l'inscription
    $stmtDelete = $pdo->prepare("DELETE FROM inscriptions WHERE id_inscription = :id");
    $stmtDelete->execute(['id' => $id_inscription]);
    
    // Valider la transaction
    $pdo->commit();
    
    // Message de succès
    $_SESSION['flash_success'] = "L'inscription de " . sanitize($inscription['prenom'] . ' ' . $inscription['nom']) . " a été supprimée avec succès.";
    
} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $_SESSION['flash_error'] = "Erreur lors de la suppression : " . $e->getMessage();
}

// Rediriger vers le dashboard
header('Location: dashboard.php');
exit;
