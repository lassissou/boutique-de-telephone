<?php
// filepath: c:\wamp64\www\vente téléphone\panier.php

session_start();

// Initialiser le panier s'il n'existe pas
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Gestion de l'ajout d'un produit au panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_au_panier'])) {
    $produit_id = intval($_POST['produit_id']);
    $quantite = intval($_POST['quantite']);

    if ($produit_id > 0 && $quantite > 0) {
        if (isset($_SESSION['panier'][$produit_id])) {
            // Si le produit est déjà dans le panier, augmenter la quantité
            $_SESSION['panier'][$produit_id] += $quantite;
        } else {
            // Sinon, ajouter le produit au panier
            $_SESSION['panier'][$produit_id] = $quantite;
        }
    }
}

// Gestion de la suppression d'un produit du panier
if (isset($_GET['supprimer'])) {
    $produit_id = intval($_GET['supprimer']);
    if (isset($_SESSION['panier'][$produit_id])) {
        unset($_SESSION['panier'][$produit_id]);
    }
}

// Gestion de la mise à jour des quantités
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mettre_a_jour'])) {
    foreach ($_POST['quantites'] as $produit_id => $quantite) {
        $produit_id = intval($produit_id);
        $quantite = intval($quantite);

        if ($produit_id > 0 && $quantite > 0) {
            $_SESSION['panier'][$produit_id] = $quantite;
        } elseif ($quantite === 0) {
            unset($_SESSION['panier'][$produit_id]);
        }
    }
}

// Connexion à la base de données pour récupérer les informations des produits
require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

$produits_panier = [];
$total_panier = 0;

if (!empty($_SESSION['panier'])) {
    $ids = implode(',', array_keys($_SESSION['panier']));
    try {
        $requete = $connexion->query("SELECT * FROM produits WHERE id IN ($ids)");
        $produits_panier = $requete->fetchAll(PDO::FETCH_ASSOC);

        foreach ($produits_panier as &$produit) {
            $produit_id = $produit['id'];
            $produit['quantite'] = $_SESSION['panier'][$produit_id];
            $produit['total'] = $produit['prix'] * $produit['quantite'];
            $total_panier += $produit['total'];
        }
    } catch (PDOException $e) {
        die("Erreur lors de la récupération des produits : " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
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
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .total {
            font-weight: bold;
            text-align: right;
        }
        .actions {
            text-align: center;
        }
        .actions a, .actions button {
            display: inline-block;
            margin: 5px;
            padding: 10px 15px;
            color: #fff;
            background-color: #007BFF;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .actions a:hover, .actions button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Votre panier</h1>

        <?php if (!empty($produits_panier)): ?>
            <form method="POST" action="panier.php">
                <table>
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Prix</th>
                            <th>Quantité</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produits_panier as $produit): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($produit['nom']); ?></td>
                                <td><?php echo htmlspecialchars(number_format($produit['prix'], 2)); ?> €</td>
                                <td>
                                    <input type="number" name="quantites[<?php echo $produit['id']; ?>]" value="<?php echo $produit['quantite']; ?>" min="1">
                                </td>
                                <td><?php echo htmlspecialchars(number_format($produit['total'], 2)); ?> €</td>
                                <td class="actions">
                                    <a href="panier.php?supprimer=<?php echo $produit['id']; ?>" onclick="return confirm('Voulez-vous vraiment supprimer ce produit ?');">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="total">
                    Total : <?php echo htmlspecialchars(number_format($total_panier, 2)); ?> €
                </div>
                <div class="actions">
                    <button type="submit" name="mettre_a_jour">Mettre à jour le panier</button>
                    <a href="checkout.php">Passer à la caisse</a>
                </div>
                <div class="actions">
                  <a href="checkout.php" class="bouton-commande">Passer à la caisse</a>
               </div>
            </form>
        <?php else: ?>
            <p>Votre panier est vide.</p>
        <?php endif; ?>
    </div>
</body>
</html>