<?php
// filepath: c:\wamp64\www\vente téléphone\nouveautes.php

require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

$message = "";

// Définir le nombre de produits par page
$produits_par_page = 6;
$page_courante = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page_courante - 1) * $produits_par_page;

// Récupérer les nouveaux produits avec pagination
try {
    $requete = $connexion->prepare("
        SELECT id, nom, description, lien_image, marque, prix, date_ajouter 
        FROM produits 
        ORDER BY date_ajouter DESC 
        LIMIT :offset, :produits_par_page
    ");
    $requete->bindValue(':offset', $offset, PDO::PARAM_INT);
    $requete->bindValue(':produits_par_page', $produits_par_page, PDO::PARAM_INT);
    $requete->execute();
    $produits = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer le nombre total de produits
    $total_produits = $connexion->query("SELECT COUNT(*) FROM produits")->fetchColumn();
    $total_pages = ceil($total_produits / $produits_par_page);
} catch (PDOException $e) {
    $message = "Erreur lors de la récupération des produits : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveautés</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .produits {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .produit {
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .produit:hover {
            transform: scale(1.02);
        }
        .produit img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .produit .details {
            padding: 15px;
        }
        .produit .details h3 {
            margin: 0 0 10px;
            font-size: 18px;
            color: #333;
        }
        .produit .details p {
            margin: 0 0 10px;
            color: #666;
        }
        .produit .details .prix {
            font-size: 16px;
            font-weight: bold;
            color: #007BFF;
        }
        .pagination {
            text-align: center;
            margin-top: 20px;
        }
        .pagination a {
            display: inline-block;
            margin: 0 5px;
            padding: 10px 15px;
            color: #007BFF;
            text-decoration: none;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .pagination a.active {
            background-color: #007BFF;
            color: #fff;
            border-color: #007BFF;
        }
        .pagination a:hover {
            background-color: #0056b3;
            color: #fff;
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
    <div class="container">
        <h1>Nouveautés</h1>

        <?php if (!empty($message)): ?>
            <p class="message error"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <div class="produits">
            <?php if (!empty($produits)): ?>
                <?php foreach ($produits as $produit): ?>
                    <div class="produit">
                        <img src="images/<?php echo htmlspecialchars($produit['lien_image']); ?>" alt="<?php echo htmlspecialchars($produit['nom']); ?>">
                        <div class="details">
                            <h3><?php echo htmlspecialchars($produit['nom']); ?></h3>
                            <p><?php echo htmlspecialchars($produit['description']); ?></p>
                            <p class="prix"><?php echo htmlspecialchars(number_format($produit['prix'], 2)); ?> Fcfa</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun produit trouvé.</p>
            <?php endif; ?>
        </div>

        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="nouveautes.php?page=<?php echo $i; ?>" class="<?php echo ($i === $page_courante) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
</body>
</html>