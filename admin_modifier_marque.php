<?php
// filepath: c:\wamp64\www\vente téléphone\admin_modifier_marque.php

require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

$message = "";

// Vérifier si l'ID de la marque est présent dans l'URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_marque = $_GET['id'];

    // Récupérer les informations de la marque à modifier
    try {
        $requete = $connexion->prepare("SELECT * FROM marques WHERE id = :id");
        $requete->execute([':id' => $id_marque]);
        $marque = $requete->fetch(PDO::FETCH_ASSOC);

        if (!$marque) {
            $message = "Marque non trouvée.";
        }
    } catch (PDOException $e) {
        $message = "Erreur lors de la récupération des informations de la marque : " . $e->getMessage();
    }

    // Gestion de la modification de la marque
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_marque'])) {
        $nouveau_nom = trim($_POST['nom_marque']);
        if (!empty($nouveau_nom)) {
            try {
                $requete = $connexion->prepare("UPDATE marques SET nom = :nom WHERE id = :id");
                $requete->execute([
                    ':nom' => $nouveau_nom,
                    ':id' => $id_marque
                ]);
                $message = "Marque mise à jour avec succès.";
                // Redirection vers la page de gestion des marques après la modification
                header("Location: admin_categories.php");
                exit();
            } catch (PDOException $e) {
                $message = "Erreur lors de la mise à jour de la marque : " . $e->getMessage();
            }
        } else {
            $message = "Veuillez entrer un nouveau nom de marque.";
        }
    }
} else {
    $message = "ID de marque invalide.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier la marque - Administration</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        .form-group {
            margin-bottom: 10px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 15px;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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

    <h2>Modifier la marque</h2>
    <?php if (!empty($message)): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if (isset($marque)): ?>
        <form method="POST" action="admin_modifier_marque.php?id=<?php echo htmlspecialchars($marque['id']); ?>">
            <div class="form-group">
                <label for="nom_marque">Nom de la marque :</label>
                <input type="text" id="nom_marque" name="nom_marque" value="<?php echo htmlspecialchars($marque['nom']); ?>" required>
            </div>
            <button type="submit" name="modifier_marque">Enregistrer les modifications</button>
        </form>
    <?php endif; ?>
</body>
</html>