<?php
// filepath: c:\wamp64\www\vente téléphone\admin_categories.php

require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

$message = "";

// Gestion de l'ajout d'une nouvelle marque
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_marque'])) {
    $nom_marque = trim($_POST['nom_marque']);
    if (!empty($nom_marque)) {
        try {
            $requete = $connexion->prepare("INSERT INTO marques (nom) VALUES (:nom)");
            $requete->execute([
                ':nom' => $nom_marque
            ]);
            $message = "Marque ajoutée avec succès.";
        } catch (PDOException $e) {
            $message = "Erreur lors de l'ajout de la marque : " . $e->getMessage();
        }
    } else {
        $message = "Veuillez entrer un nom de marque.";
    }
}

// Récupération de la liste des marques
try {
    $req = $connexion->query("SELECT * FROM marques ORDER BY id DESC");
    $marques = $req->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Erreur lors de la récupération des marques : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des marques - Administration</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {  
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
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
/* Ajoutez ce style dans votre fichier CSS ou dans un bloc <style> de la page */
.btn-retour {
    display: inline-block;
    margin: 20px;
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
    <!-- Insérez ce code dans votre page à l'endroit désiré, par exemple juste avant le footer -->
<a href="javascript:history.back()" class="btn-retour">Retour</a>

    <h2>Gestion des marques</h2>
    <?php if (!empty($message)): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <h3>Ajouter une nouvelle marque</h3>
    <form method="POST" action="admin_categories.php">
        <div class="form-group">
            <label for="nom_marque">Nom de la marque :</label>
            <input type="text" id="nom_marque" name="nom_marque" required>
        </div>
        <button type="submit" name="add_marque">Ajouter la marque</button>
    </form>

    <h3>Liste des marques</h3>
    <?php if (!empty($marques)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($marques as $marque): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($marque['id']); ?></td>
                        <td><?php echo htmlspecialchars($marque['nom']); ?></td>
                        <td>
                            <a href="admin_modifier_marque.php?id=<?php echo $marque['id']; ?>">Modifier</a> |
                            <a href="admin_supprimer_marque.php?id=<?php echo $marque['id']; ?>" onclick="return confirm('Voulez-vous vraiment supprimer cette marque ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucune marque trouvée.</p>
    <?php endif; ?>
</body>
</html>