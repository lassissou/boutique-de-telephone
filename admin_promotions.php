<?php
// filepath: c:\wamp64\www\vente téléphone\admin_promotions.php

require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

$message = "";

// Récupération des produits pour le menu déroulant (uniquement ceux ayant un statut "visible")
try {
    $produits = $connexion->query("
        SELECT p.id, p.nom, p.prix 
        FROM produits p
        JOIN status s ON p.id_status = s.id
        WHERE s.status = 'visible'
        ORDER BY p.id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $produits = [];
    $message = "Erreur lors du chargement des produits : " . $e->getMessage();
}

// Gestion de l'ajout d'une nouvelle promotion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_promotion'])) {
    $nom_promotion = trim($_POST['nom_promotion']);
    $type_reduction = trim($_POST['type_reduction']);
    $valeur_reduction = trim($_POST['valeur_reduction']);
    $prix = trim($_POST['prix']);
    $date_debut = trim($_POST['date_debut']);
    $date_fin = trim($_POST['date_fin']);
    $produit_id = isset($_POST['produit_id']) ? $_POST['produit_id'] : null;

    if (!empty($nom_promotion) && !empty($type_reduction) && !empty($valeur_reduction) &&
        !empty($prix) && !empty($date_debut) && !empty($date_fin) && !empty($produit_id)) {
        try {
            $requete = $connexion->prepare("
                INSERT INTO promotions (nom, type_reduction, valeur_reduction, prix_initial, date_debut, date_fin, produit_id)
                VALUES (:nom, :type_reduction, :valeur_reduction, :prix_initial, :date_debut, :date_fin, :produit_id)
            ");
            $requete->execute([
                ':nom' => $nom_promotion,
                ':type_reduction' => $type_reduction,
                ':valeur_reduction' => $valeur_reduction,
                ':prix_initial' => $prix,
                ':date_debut' => $date_debut,
                ':date_fin' => $date_fin,
                ':produit_id' => $produit_id
            ]);
            $message = "Promotion ajoutée avec succès.";
        } catch (PDOException $e) {
            $message = "Erreur lors de l'ajout de la promotion : " . $e->getMessage();
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
    }
}

// Récupération de la liste des promotions
try {
    $req = $connexion->query("SELECT * FROM promotions ORDER BY date_debut DESC");
    $promotions = $req->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Erreur lors de la récupération des promotions : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestion des promotions - Administration</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        /* Style pour le formulaire d'ajout de promotion */
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label { 
            display: block; 
            font-weight: bold; 
            margin-bottom: 5px;
        }
        .form-group input, 
        .form-group select { 
            width: 100%; 
            padding: 8px; 
            border: 1px solid #ccc; 
            border-radius: 4px;
            box-sizing: border-box;
        }
        /* Style pour le bouton */
        button[type="submit"] {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button[type="submit"]:hover {
            background-color: #218838;
        }
        /* Style pour le message */
        .message {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
        }
        /* Style pour le bouton retour */
        .btn-retour {
            display: inline-block;
            margin: 20px 0;
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
        /* Style pour le tableau de la liste des promotions */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <a href="javascript:history.back()" class="btn-retour">Retour</a>

    <h2>Gestion des promotions</h2>
    <?php if (!empty($message)): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <h3>Ajouter une nouvelle promotion</h3>
    <form method="POST" action="admin_promotions.php">
        <div class="form-group">
            <label for="produit_id">Produit concerné :</label>
            <select id="produit_id" name="produit_id" required>
                <option value="">-- Sélectionner un produit --</option>
                <?php foreach ($produits as $produit): ?>
                    <option value="<?php echo $produit['id']; ?>">
                        <?php echo htmlspecialchars($produit['nom']); ?> (Prix : <?php echo number_format($produit['prix'], 2); ?> Fcfa)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="nom_promotion">Nom de la promotion :</label>
            <input type="text" id="nom_promotion" name="nom_promotion" required>
        </div>
        <div class="form-group">
            <label for="type_reduction">Type de réduction :</label>
            <select id="type_reduction" name="type_reduction" required>
                <option value="pourcentage">Pourcentage</option>
                <option value="montant">Montant fixe</option>
            </select>
        </div>
        <div class="form-group">
            <label for="valeur_reduction">Valeur de la réduction :</label>
            <input type="number" step="0.01" id="valeur_reduction" name="valeur_reduction" required>
        </div>
        <div class="form-group">
            <label for="prix">Prix :</label>
            <input type="number" step="0.01" id="prix" name="prix" required>
        </div>
        <div class="form-group">
            <label for="date_debut">Date de début :</label>
            <input type="date" id="date_debut" name="date_debut" required>
        </div>
        <div class="form-group">
            <label for="date_fin">Date de fin :</label>
            <input type="date" id="date_fin" name="date_fin" required>
        </div>
        <button type="submit" name="add_promotion">Ajouter la promotion</button>
    </form>

    <h3>Liste des promotions</h3>
    <?php if (!empty($promotions)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Type</th>
                    <th>Valeur</th>
                    <th>Prix</th>
                    <th>Date de début</th>
                    <th>Date de fin</th>
                    <th>ID Produit</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($promotions as $promotion): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($promotion['id']); ?></td>
                        <td><?php echo htmlspecialchars($promotion['nom']); ?></td>
                        <td><?php echo htmlspecialchars($promotion['type_reduction']); ?></td>
                        <td><?php echo htmlspecialchars($promotion['valeur_reduction']); ?></td>
                        <td><?php echo htmlspecialchars($promotion['prix_initial']); ?></td>
                        <td><?php echo htmlspecialchars($promotion['date_debut']); ?></td>
                        <td><?php echo htmlspecialchars($promotion['date_fin']); ?></td>
                        <td><?php echo htmlspecialchars($promotion['produit_id']); ?></td>
                        <td>
                            <a href="admin_supprimer_promotion.php?id=<?php echo $promotion['id']; ?>" onclick="return confirm('Voulez-vous vraiment supprimer cette promotion ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucune promotion trouvée.</p>
    <?php endif; ?>
</body>
</html>