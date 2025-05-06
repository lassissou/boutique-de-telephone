<?php
require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

$message = "";

// Récupérer les promotions en cours avec calcul du prix après réduction
try {
    $requete = $connexion->prepare("
        SELECT 
            p.id AS promotion_id,
            p.nom AS promotion_nom,
            p.type_reduction,
            p.valeur_reduction,
            p.prix_initial,
            p.date_debut,
            p.date_fin,
            pr.id AS produit_id,
            pr.nom AS produit_nom,
            pr.lien_image,
            m.nom AS marque_nom,
            CASE 
                WHEN p.type_reduction = 'pourcentage' THEN p.prix_initial - (p.prix_initial * (p.valeur_reduction / 100))
                WHEN p.type_reduction = 'montant' THEN p.prix_initial - p.valeur_reduction
                ELSE p.prix_initial
            END AS prix_promo
        FROM promotions p
        JOIN produits pr ON p.produit_id = pr.id
        JOIN marques m ON pr.marques_id = m.id
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
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        header {
            background-color: #2c3e50;
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }
        nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        nav ul li {
            margin-left: 20px;
        }
        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        nav ul li a:hover {
            color: #3498db;
        }
        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            flex: 1;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .promotions {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            padding: 15px;
        }
        .promotion {
            border: 1px solid #e1e1e1;
            border-radius: 10px;
            overflow: hidden;
            background-color: #fff;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }
        .promotion:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .promotion-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #e74c3c;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .promotion img {
            width: 100%;
            height: 200px;
            object-fit: contain;
            background: #f8f8f8;
            padding: 20px;
        }
        .promotion .details {
            padding: 20px;
        }
        .promotion .details h3 {
            margin: 0 0 10px;
            font-size: 18px;
            color: #2c3e50;
        }
        .promotion .details .marque {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .promotion .details .price-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 15px 0;
        }
        .promotion .details .original-price {
            text-decoration: line-through;
            color: #95a5a6;
            font-size: 16px;
        }
        .promotion .details .promo-price {
            font-size: 20px;
            font-weight: bold;
            color: #e74c3c;
        }
        .promotion .details .reduction {
            display: inline-block;
            background-color: #fdeaea;
            color: #e74c3c;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
        }
        .promotion .details .dates {
            font-size: 13px;
            color: #7f8c8d;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #eee;
        }
        .message.error {
            text-align: center;
            padding: 15px;
            background-color: #f8d7da;
            color: #721c24;
            margin-bottom: 30px;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        .no-promotions {
            text-align: center;
            grid-column: 1 / -1;
            padding: 40px;
            color: #7f8c8d;
            font-size: 18px;
        }
        .btn-retour {
            display: inline-block;
            margin: 20px;
            padding: 10px 20px;
            background-color: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .btn-retour:hover {
            background-color: #2980b9;
        }
        footer {
            background-color: #2c3e50;
            color: white;
            padding: 30px 0;
            margin-top: 50px;
        }
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            padding: 0 20px;
        }
        .footer-section h3 {
            font-size: 18px;
            margin-bottom: 15px;
            position: relative;
            padding-bottom: 10px;
        }
        .footer-section h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 40px;
            height: 2px;
            background-color: #3498db;
        }
        .footer-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .footer-section ul li {
            margin-bottom: 10px;
        }
        .footer-section ul li a {
            color: #ecf0f1;
            text-decoration: none;
            transition: color 0.3s;
        }
        .footer-section ul li a:hover {
            color: #3498db;
        }
        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            margin-top: 20px;
            border-top: 1px solid #34495e;
            font-size: 14px;
        }
        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }
        .social-icons a {
            color: white;
            font-size: 20px;
            transition: color 0.3s;
        }
        .social-icons a:hover {
            color: #3498db;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <a href="index.php" class="logo">MonSite</a>
            <nav>
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="produits.php">Produits</a></li>
                    <li><a href="promotions.php">Promotions</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <a href="javascript:history.back()" class="btn-retour">Retour</a>
    <div class="container">
        <h1>Promotions en cours</h1>
        <?php if (!empty($message)): ?>
            <p class="message error"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        
        <div class="promotions">
            <?php if (!empty($promotions)): ?>
                <?php foreach ($promotions as $promotion): ?>
                    <div class="promotion">
                        <span class="promotion-badge">PROMO</span>
                        <img src="images/<?php echo htmlspecialchars($promotion['lien_image']); ?>" alt="<?php echo htmlspecialchars($promotion['produit_nom']); ?>">
                        <div class="details">
                            <h3><?php echo htmlspecialchars($promotion['produit_nom']); ?></h3>
                            <p class="marque"><?php echo htmlspecialchars($promotion['marque_nom']); ?></p>
                            
                            <div class="price-container">
                                <span class="original-price"><?php echo number_format($promotion['prix_initial'], 2); ?> €</span>
                                <span class="promo-price"><?php echo number_format($promotion['prix_promo'], 2); ?> €</span>
                            </div>
                            
                            <span class="reduction">
                                <?php if ($promotion['type_reduction'] === 'pourcentage'): ?>
                                    -<?php echo htmlspecialchars($promotion['valeur_reduction']); ?>%
                                <?php else: ?>
                                    -<?php echo number_format($promotion['valeur_reduction'], 2); ?> €
                                <?php endif; ?>
                            </span>
                            
                            <p class="dates">
                                Valable du <?php echo date('d/m/Y', strtotime($promotion['date_debut'])); ?> 
                                au <?php echo date('d/m/Y', strtotime($promotion['date_fin'])); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-promotions">
                    <p>Aucune promotion en cours pour le moment.</p>
                    <p>Revenez plus tard pour découvrir nos offres spéciales!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>A propos</h3>
                <p>Notre entreprise s'engage à vous offrir les meilleurs produits aux meilleurs prix avec des promotions régulières.</p>
            </div>
            <div class="footer-section">
                <h3>Liens utiles</h3>
                <ul>
                    <li><a href="mentions-legales.php">Mentions légales</a></li>
                    <li><a href="cgv.php">Conditions générales de vente</a></li>
                    <li><a href="politique-confidentialite.php">Politique de confidentialité</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <ul>
                    <li>Email: contact@monsite.com</li>
                    <li>Téléphone: 01 23 45 67 89</li>
                    <li>Adresse: 123 Rue Example, Paris</li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Réseaux sociaux</h3>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> MonSite. Tous droits réservés.</p>
        </div>
    </footer>

    <!-- Font Awesome pour les icônes -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>