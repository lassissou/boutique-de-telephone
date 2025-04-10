<?php
// filepath: c:\wamp64\www\vente téléphone\admin_produits.php

require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

$message = "";

// Gestion de l'ajout d'un nouveau produit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    // Récupération et nettoyage des champs
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $marque = trim($_POST['marque']);
    $prix = trim($_POST['prix']);
    $prix_initial = trim($_POST['prix_initial']);
    $stock = trim($_POST['stock']);
    
    // Traitement de l'upload de l'image
    $lien_image = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "images/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        // Utilisation de basename et ajout d'un timestamp pour éviter les collisions
        $fileName = time() . "_" . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $lien_image = $fileName;
        } else {
            $message = "Erreur lors du téléchargement de l'image.";
        }
    } else {
        $message = "Veuillez sélectionner une image pour le produit.";
    }
    
    if (empty($message)) {
        if (!empty($nom) && !empty($description) && !empty($marque) && $prix !== '' && $prix_initial !== '' && $stock !== '') {
            try {
                // On utilise NOW() pour la date d'ajout
                $requete = $connexion->prepare("
                    INSERT INTO produits (nom, description, lien_image, marque, date_ajouter, prix, prix_initial, stock)
                    VALUES (:nom, :description, :lien_image, :marque, NOW(), :prix, :prix_initial, :stock)
                ");
                $requete->execute([
                    ':nom' => $nom,
                    ':description' => $description,
                    ':lien_image' => $lien_image,
                    ':marque' => $marque,
                    ':prix' => $prix,
                    ':prix_initial' => $prix_initial,
                    ':stock' => $stock
                ]);
                $message = "Produit ajouté avec succès.";
            } catch (PDOException $e) {
                $message = "Erreur lors de l'ajout du produit : " . $e->getMessage();
            }
        } else {
            $message = "Veuillez remplir tous les champs requis.";
        }
    }
}

// Récupération de la liste des produits
try {
    $req = $connexion->query("SELECT * FROM produits ORDER BY id DESC");
    $produits = $req->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Erreur lors de la récupération des produits : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestion des produits - Administration</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        .form-group {
            margin-bottom: 10px;
        }
        .form-group label { 
            display: block; 
            font-weight: bold; 
        }
        .form-group input, 
        .form-group textarea { 
            width: 100%; 
            padding: 5px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
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
        }
        .image-thumb {
            max-width: 100px;
            height: auto;
        }
    </style>
</head>
<body>
    <h2>Gestion des produits</h2>
    <?php if (!empty($message)): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    
    <h3>Ajouter un nouveau produit</h3>
    <form method="POST" action="admin_produits.php" enctype="multipart/form-data">
        <div class="form-group">
            <label for="nom">Nom du produit :</label>
            <input type="text" id="nom" name="nom" required>
        </div>
        <div class="form-group">
            <label for="description">Description :</label>
            <textarea id="description" name="description" required></textarea>
        </div>
        <div class="form-group">
            <label for="image">Image :</label>
            <input type="file" id="image" name="image" accept="image/*" required>
        </div>
        <div class="form-group">
            <label for="marque">Marque :</label>
            <input type="text" id="marque" name="marque" required>
        </div>
        <div class="form-group">
            <label for="prix">Prix (€) :</label>
            <input type="number" step="0.01" id="prix" name="prix" required>
        </div>
        <div class="form-group">
            <label for="prix_initial">Prix initial (€) :</label>
            <input type="number" step="0.01" id="prix_initial" name="prix_initial" required>
        </div>
        <div class="form-group">
            <label for="stock">Stock :</label>
            <input type="number" id="stock" name="stock" required>
        </div>
        <button type="submit" name="add_product">Ajouter le produit</button>
    </form>
    
    <h3>Liste des produits</h3>
    <?php if (!empty($produits)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Marque</th>
                    <th>Date d'ajout</th>
                    <th>Prix (Fcfa)</th>
                    <th>Prix initial (Fcfa)</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produits as $produit): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($produit['id']); ?></td>
                        <td><?php echo htmlspecialchars($produit['nom']); ?></td>
                        <td><?php echo htmlspecialchars($produit['description']); ?></td>
                        <td>
                            <?php if (!empty($produit['lien_image'])): ?>
                                <img src="images/<?php echo htmlspecialchars($produit['lien_image']); ?>" class="image-thumb" alt="Image du produit">
                            <?php else: ?>
                                Pas d'image
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($produit['marque']); ?></td>
                        <td><?php echo htmlspecialchars($produit['date_ajouter']); ?></td>
                        <td><?php echo htmlspecialchars($produit['prix']); ?></td>
                        <td><?php echo htmlspecialchars($produit['prix_initial']); ?></td>
                        <td><?php echo htmlspecialchars($produit['stock']); ?></td>
                        <td>
                            <a href="admin_modifier_produit.php?id=<?php echo $produit['id']; ?>">Modifier</a> |
                            <a href="admin_supprimer_produit.php?id=<?php echo $produit['id']; ?>" onclick="return confirm('Voulez-vous vraiment supprimer ce produit ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucun produit trouvé.</p>
    <?php endif; ?>
</body>
</html>