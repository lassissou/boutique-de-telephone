<?php
// filepath: c:\wamp64\www\vente téléphone\admin_supprimer_marque.php

require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

$message = "";

// Vérifier si l'ID de la marque est présent dans l'URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_marque = $_GET['id'];

    // Tentative de suppression de la marque
    try {
        $requete = $connexion->prepare("DELETE FROM marques WHERE id = :id");
        $requete->execute([':id' => $id_marque]);

        if ($requete->rowCount() > 0) {
            $message = "Marque supprimée avec succès.";
        } else {
            $message = "La marque avec l'ID spécifié n'a pas été trouvée.";
        }
        // Redirection vers la page de gestion des marques après la suppression
        header("Location: admin_categories.php?message=" . urlencode($message));
        exit();
    } catch (PDOException $e) {
        $message = "Erreur lors de la suppression de la marque : " . $e->getMessage();
        // Redirection en cas d'erreur
        header("Location: admin_categories.php?error=" . urlencode($message));
        exit();
    }
} else {
    $message = "ID de marque invalide.";
    // Redirection en cas d'ID invalide
    header("Location: admin_categories.php?error=" . urlencode($message));
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Supprimer la marque - Administration</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        .message {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            text-align: center;
        }
        .btn-retour {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007BFF;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .btn-retour:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <a href="admin_categories.php" class="btn-retour">Retour à la gestion des marques</a>

    <h2>Supprimer la marque</h2>
    <?php if (!empty($message)): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <p>Êtes-vous sûr de vouloir supprimer cette marque ? Cette action est irréversible.</p>
    </body>
</html>