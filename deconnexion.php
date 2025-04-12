<?php
// filepath: c:\wamp64\www\vente téléphone\deconnexion.php

session_start();

// Si une session est active, on la vide et on la détruit
if (!empty($_SESSION)) {
    session_unset();
    session_destroy();
}

// Redirection vers la page d'accueil ou de connexion
header('Location: index.php');
exit;

// filepath: c:\wamp64\www\vente téléphone\deconnexion.php

session_start();

// Si une session est active, on la vide et on la détruit
if (!empty($_SESSION)) {
    session_unset();
    session_destroy();
}

// Redirection vers la page d'accueil ou de connexion
header('Location: index.php');
exit;