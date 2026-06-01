<?php
/**
 * ============================================================================
 * SAE203 E-LLUSION - Déconnexion
 * ============================================================================
 * Déconnecte l'utilisateur en détruisant sa session.
 * Redirige vers la page de connexion avec un message de confirmation.
 * ============================================================================
 */

// Démarrer la session si pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Supprimer toutes les variables de session
$_SESSION = [];

// Supprimer le cookie de session si existant
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Détruire la session
session_destroy();

// Inclusion des constantes pour SITE_URL
require_once __DIR__ . '/../config/config.php';

// Redirection vers la page de connexion
redirect(SITE_URL . '/login.php');
