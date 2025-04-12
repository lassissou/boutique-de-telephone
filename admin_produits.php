<?php
require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

$message = "";

// Récupération de la liste des marques depuis la table "marques"
try {
    $stmt = $connexion->query("SELECT id, nom FROM marques ORDER BY nom ASC");
    $marques = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $marques = [];
    $message = "Erreur lors de la récupération des marques : " . $e->getMessage();
}

// Gestion de l'ajout d'un nouveau produit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    // Récupération et nettoyage des champs
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    // On récupère directement l'id de la marque sélectionnée
    $marques_id = trim($_POST['marques_id']);
    $prix = trim($_POST['prix']);
    $stock = trim($_POST['stock']);
    $id_status = isset($_POST['id_status']) ? trim($_POST['id_status']) : '';

    // Traitement de l'upload de l'image
    $lien_image = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "images/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
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
    
    // Vérification des champs requis avant insertion
    if (empty($message)) {
        if (!empty($nom) && !empty($description) && !empty($marques_id) && $prix !== '' && $stock !== '' && !empty($id_status)) {
            // Vérification que la marque sélectionnée existe
            try {
                $stmtMarque = $connexion->prepare("SELECT id FROM marques WHERE id = :id");
                $stmtMarque->execute([':id' => $marques_id]);
                $marqueData = $stmtMarque->fetch(PDO::FETCH_ASSOC);
                if (!$marqueData) {
                    $message = "La marque sélectionnée n'existe pas.";
                }
            } catch(PDOException $e) {
                $message = "Erreur lors de la vérification de la marque : " . $e->getMessage();
            }
            
            if (empty($message)) {
                try {
                    // Insertion du produit dans la base, on insère l'id de la marque dans la colonne "marques_id"
                    $requete = $connexion->prepare("
                        INSERT INTO produits (nom, description, lien_image, marques_id, date_ajouter, prix, stock, id_status)
                        VALUES (:nom, :description, :lien_image, :marques_id, NOW(), :prix, :stock, :id_status)
                    ");
                    $requete->execute([
                        ':nom'         => $nom,
                        ':description' => $description,
                        ':lien_image'  => $lien_image,
                        ':marques_id'  => $marques_id,
                        ':prix'        => $prix,
                        ':stock'       => $stock,
                        ':id_status'   => $id_status
                    ]);
                    $message = "Produit ajouté avec succès.";
                } catch (PDOException $e) {
                    $message = "Erreur lors de l'ajout du produit : " . $e->getMessage();
                }
            }
        } else {
            $message = "Veuillez remplir tous les champs requis.";
        }
    }
}

// Récupération de la liste des produits avec le libellé du status
try {
    $req = $connexion->query("
        SELECT p.*, s.status AS status_libelle 
        FROM produits p
        JOIN status s ON p.id_status = s.id
        ORDER BY p.id DESC
    ");
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
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        h2, h3 {
            text-align: center;
            color: #333;
        }
        .message {
            margin: 15px auto;
            max-width: 800px;
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 10px;
        }
        .form-group label { 
            display: block; 
            font-weight: bold; 
            margin-bottom: 5px;
        }
        .form-group input, 
        .form-group textarea, 
        .form-group select { 
            width: 100%; 
            padding: 8px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            font-size: 15px;
        }
        table {
            width: 100%;
            max-width: 1000px;
            margin: 20px auto;
            border-collapse: collapse;
        }
        table, th, td {  
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f1f1f1;
        }
        .image-thumb {
            max-width: 100px;
            height: auto;
        }
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
    <a href="javascript:history.back()" class="btn-retour">Retour</a>

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
        <!-- Champ de sélection pour la marque via la table "marques" -->
        <div class="form-group">
            <label for="marques_id">Marque :</label>
            <select id="marques_id" name="marques_id" required>
                <option value="">-- Sélectionnez une marque --</option>
                <?php foreach ($marques as $marque): ?>
                    <option value="<?php echo htmlspecialchars($marque['id']); ?>">
                        <?php echo htmlspecialchars($marque['nom']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="prix">Prix (Fcfa) :</label>
            <input type="number" step="0.01" id="prix" name="prix" required>
        </div>
        <div class="form-group">
            <label for="stock">Stock :</label>
            <input type="number" id="stock" name="stock" required>
        </div>
        <div class="form-group">
            <label for="id_status">Status :</label>
            <select id="id_status" name="id_status" required>
                <option value="">-- Sélectionnez le status --</option>
                <option value="1">Visible</option>
                <option value="2">Masqué</option>
                <option value="3">En cours de traitement</option>
                <option value="4">Traité</option>
                <option value="5">Archivé</option>
            </select>
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
                    <th>Stock</th>
                    <th>Status</th>
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
                        <!-- Ici, vous pouvez afficher le nom de la marque en fonction de l'id de la marque
                             soit en joignant la table 'marques' dans la requête de sélection, soit en stockant
                             le nom lors de l'insertion si souhaité -->
                        <td><?php echo htmlspecialchars($produit['marque']); ?></td>
                        <td><?php echo htmlspecialchars($produit['date_ajouter']); ?></td>
                        <td><?php echo htmlspecialchars($produit['prix']); ?></td>
                        <td><?php echo htmlspecialchars($produit['stock']); ?></td>
                        <td><?php echo htmlspecialchars($produit['status_libelle']); ?></td>
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
``` 
