<?php
require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

$message = "";

// Vérifier la présence de l'ID du produit
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin_produits.php");
    exit;
}
$produit_id = intval($_GET['id']);

// Récupération des données du produit
try {
    $stmt = $connexion->prepare("SELECT * FROM produits WHERE id = :id");
    $stmt->execute([':id' => $produit_id]);
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$produit) {
        header("Location: admin_produits.php");
        exit;
    }
} catch (PDOException $e) {
    $message = "Erreur lors de la récupération du produit : " . $e->getMessage();
}

// Récupération de la liste des marques
try {
    $stmt = $connexion->query("SELECT id, nom FROM marques ORDER BY nom ASC");
    $marques = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $marques = [];
    $message = "Erreur lors de la récupération des marques : " . $e->getMessage();
}

// Traitement de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    // Récupérer et nettoyer les champs
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $marques_id = trim($_POST['marques_id']);
    $prix = trim($_POST['prix']);
    $stock = trim($_POST['stock']);
    $id_status = trim($_POST['id_status']);

    // Préparer la nouvelle image (si upload)
    $lien_image = $produit['lien_image'];
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
    }
    
    // Vérifier que la marque existe
    if (empty($message)) {
        try {
            $stmtMarque = $connexion->prepare("SELECT id FROM marques WHERE id = :id");
            $stmtMarque->execute([':id' => $marques_id]);
            if (!$stmtMarque->fetch(PDO::FETCH_ASSOC)) {
                $message = "La marque sélectionnée n'existe pas.";
            }
        } catch(PDOException $e) {
            $message = "Erreur lors de la vérification de la marque : " . $e->getMessage();
        }
    }
    
    // Si aucune erreur, mettre à jour le produit
    if (empty($message)) {
        try {
            $stmtUpdate = $connexion->prepare("
                UPDATE produits 
                SET nom = :nom, description = :description, lien_image = :lien_image, 
                    marques_id = :marques_id, prix = :prix, stock = :stock, id_status = :id_status 
                WHERE id = :id
            ");
            $stmtUpdate->execute([
                ':nom'         => $nom,
                ':description' => $description,
                ':lien_image'  => $lien_image,
                ':marques_id'  => $marques_id,
                ':prix'        => $prix,
                ':stock'       => $stock,
                ':id_status'   => $id_status,
                ':id'          => $produit_id
            ]);
            $message = "Produit modifié avec succès.";
            // Rafraîchir les données du produit après mise à jour
            $stmt = $connexion->prepare("SELECT * FROM produits WHERE id = :id");
            $stmt->execute([':id' => $produit_id]);
            $produit = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $message = "Erreur lors de la modification du produit : " . $e->getMessage();
        }
    }
}
?>

<style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f8;
            color: #333;
            margin: 20px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-retour {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }

        .btn-retour:hover {
            background-color: #0056b3;
        }

        h2 {
            color: #37474f;
            margin-bottom: 25px;
            border-bottom: 2px solid #eceff1;
            padding-bottom: 10px;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            text-align: center;
        }

        .message.success {
            background-color: #e6ffe6;
            color: #38761d;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #ffe6e6;
            color: #cc0000;
            border: 1px solid #f5c6cb;
        }

        form {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #546e7a;
            font-weight: bold;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="file"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #dce7ec;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group input[type="file"]:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .form-group textarea {
            resize: vertical;
        }

        .form-group select {
            appearance: none;
            background-image: url('data:image/svg+xml;charset=UTF-8,<svg fill="%2364b5f6" viewBox="0 0 4 5"><path d="M2 0L0 2h4L2 0z"/></svg>');
            background-repeat: no-repeat;
            background-position-x: 98%;
            background-position-y: 50%;
            background-size: 6px auto;
            padding-right: 25px;
        }

        button[type="submit"] {
            background-color: #2e7d32;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #1b5e20;
        }
    </style>
<!DOCTYPE html>
<html>
<head>
    <title>Modifier le Produit</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        /* Vos styles spécifiques */
    </style>
</head>
<body>
    <a href="admin_produits.php" class="btn-retour">Retour</a>
    <h2>Modifier le Produit</h2>
    <?php if (!empty($message)): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form method="POST" action="admin_modifier_produit.php?id=<?php echo $produit_id; ?>" enctype="multipart/form-data">
        <div class="form-group">
            <label for="nom">Nom du produit :</label>
            <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($produit['nom']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description :</label>
            <textarea id="description" name="description" required><?php echo htmlspecialchars($produit['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="image">Image (laisser vide pour conserver l'image actuelle) :</label>
            <input type="file" id="image" name="image" accept="image/*">
        </div>
        <!-- Liste déroulante pour la marque -->
        <div class="form-group">
            <label for="marques_id">Marque :</label>
            <select id="marques_id" name="marques_id" required>
                <option value="">-- Sélectionnez une marque --</option>
                <?php foreach ($marques as $marque): ?>
                    <option value="<?php echo htmlspecialchars($marque['id']); ?>"
                        <?php echo ($marque['id'] == $produit['marques_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($marque['nom']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="prix">Prix (Fcfa) :</label>
            <input type="number" step="0.01" id="prix" name="prix" value="<?php echo htmlspecialchars($produit['prix']); ?>" required>
        </div>
        <div class="form-group">
            <label for="stock">Stock :</label>
            <input type="number" id="stock" name="stock" value="<?php echo htmlspecialchars($produit['stock']); ?>" required>
        </div>
        <div class="form-group">
            <label for="id_status">Status :</label>
            <select id="id_status" name="id_status" required>
                <option value="">-- Sélectionnez le status --</option>
                <option value="1" <?php echo ($produit['id_status'] == 1) ? 'selected' : ''; ?>>Visible</option>
                <option value="2" <?php echo ($produit['id_status'] == 2) ? 'selected' : ''; ?>>Masqué</option>
                <option value="3" <?php echo ($produit['id_status'] == 3) ? 'selected' : ''; ?>>En cours de traitement</option>
                <option value="4" <?php echo ($produit['id_status'] == 4) ? 'selected' : ''; ?>>Traité</option>
                <option value="5" <?php echo ($produit['id_status'] == 5) ? 'selected' : ''; ?>>Archivé</option>
            </select>
        </div>
        <button type="submit" name="update_product">Mettre à jour le produit</button>
    </form>
</body>
</html>