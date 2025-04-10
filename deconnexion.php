<?php
// filepath: c:\wamp64\www\vente téléphone\deconnexion.php

session_start();

// Vérifier si une session est active
if (isset($_SESSION)) {
    // Supprimer toutes les variables de session
    session_unset();

    // Détruire la session
    session_destroy();
}

// Rediriger l'utilisateur vers la page de connexion
header('Location: index.php');
exit;