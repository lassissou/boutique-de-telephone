<?php
// filepath: c:\wamp64\www\vente téléphone\promotions.php

require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

$message = "";

// Récupérer les promotions en cours
try {
    $requete = $connexion->prepare("
        SELECT p.id, p.nom, p.type_reduction, p.valeur_reduction, p.date_debut, p.date_fin, pr.nom AS produit_nom, pr.lien_image, pr.prix
        FROM promotions p
        JOIN produits pr ON p.produit_id = pr.id
        WHERE CURDATE() BETWEEN p.date_debut AND p.date_fin
        ORDER BY p.date_debut ASC
    ");
    $requete->execute();
    $promotions = $requete->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Erreur lors de la récupération des promotions : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promotions</title>
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
        .promotions {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .promotion {
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .promotion img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .promotion .details {
            padding: 15px;
        }
        .promotion .details h3 {
            margin: 0 0 10px;
            font-size: 18px;
            color: #333;
        }
        .promotion .details p {
            margin: 0 0 10px;
            color: #666;
        }
        .promotion .details .prix {
            font-size: 16px;
            font-weight: bold;
            color: #007BFF;
        }
        .promotion .details .reduction {
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Promotions en cours</h1>

        <?php if (!empty($message)): ?>
            <p class="message error"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <div class="promotions">
            <?php if (!empty($promotions)): ?>
                <?php foreach ($promotions as $promotion): ?>
                    <div class="promotion">
                        <img src="images/<?php echo htmlspecialchars($promotion['lien_image']); ?>" alt="<?php echo htmlspecialchars($promotion['produit_nom']); ?>">
                        <div class="details">
                            <h3><?php echo htmlspecialchars($promotion['produit_nom']); ?></h3>
                            <p>Promotion : <?php echo htmlspecialchars($promotion['nom']); ?></p>
                            <p class="reduction">
                                <?php if ($promotion['type_reduction'] === 'pourcentage'): ?>
                                    Réduction : <?php echo htmlspecialchars($promotion['valeur_reduction']); ?>%
                                <?php else: ?>
                                    Réduction : -<?php echo htmlspecialchars(number_format($promotion['valeur_reduction'], 2)); ?> €
                                <?php endif; ?>
                            </p>
                            <p class="prix">Prix : <?php echo htmlspecialchars(number_format($promotion['prix'], 2)); ?> €</p>
                            <p>Valable du <?php echo htmlspecialchars($promotion['date_debut']); ?> au <?php echo htmlspecialchars($promotion['date_fin']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucune promotion en cours.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>