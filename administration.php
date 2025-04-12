<?php
session_start();

// Vérifier si l'utilisateur est un administrateur
if (!isset($_SESSION['admin']) || empty($_SESSION['admin'])) {
    header('Location: connexion.php'); // Rediriger vers la page de connexion
    exit;
}

// Inclure les fichiers nécessaires
require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

// Déterminer l'action à effectuer
$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Administration - Vente de téléphones</title>
    <link rel="stylesheet" href="administraion.css">
</head>
<body>
<header>
    <h1>Panneau d'administration</h1>
    <nav>
        <ul>
            <li><a href="administration.php?action=dashboard">Tableau de bord</a></li>
            <li><a href="administration.php?action=produits">Produits</a></li>
            <li><a href="administration.php?action=utilisateurs">Utilisateurs</a></li>
            <li><a href="administration.php?action=commandes">Commandes</a></li>
            <li><a href="administration.php?action=categories">Catégories</a></li>
            <li><a href="administration.php?action=avis">Avis</a></li>
            <li><a href="administration.php?action=promotions">Promotions</a></li>
            <li><a href="administration.php?action=rapports">Rapports</a></li>
            <li><a href="administration.php?action=parametres">Paramètres</a></li>
            <li><a href="deconnexion.php">Déconnexion</a></li>
        </ul>
    </nav>
</header>
<main>
    <?php
    // Charger la section demandée
    switch ($action) {
        case 'dashboard':
            include 'admin_dashboard.php';
            break;
        case 'produits':
            include 'admin_produits.php';
            break;
        case 'utilisateurs':
            include 'admin_utilisateurs.php';
            break;
        case 'commandes':
            include 'admin_commandes.php';
            break;
        case 'categories':
            include 'admin_categories.php';
            break;
        case 'avis':
            include 'admin_avis.php';
            break;
        case 'promotions':
            include 'admin_promotions.php';
            break;
        case 'rapports':
            include 'admin_rapports.php';
            break;
        case 'parametres':
            include 'admin_parametres.php';
            break;
        default:
            echo '<p>Page non trouvée.</p>';
    }
    ?>
</main>
<footer>
    

</body>
</html>