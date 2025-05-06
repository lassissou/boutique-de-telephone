<?php
session_start();
require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

$message = "";

// Récupération de la liste de toutes les marques (pour le menu)
try {
    $stmtBrands = $connexion->query("SELECT id, nom FROM marques ORDER BY nom ASC");
    $listeMarques = $stmtBrands->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $listeMarques = [];
}

// Définir le nombre de produits par page
$produits_par_page = 6;
$page_courante = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page_courante - 1) * $produits_par_page;

// Récupérer les nouveaux produits avec pagination
try {
    $requete = $connexion->prepare("
        SELECT p.id, p.nom, p.description, p.lien_image, p.prix, p.date_ajouter, m.nom as marque 
        FROM produits p
        JOIN marques m ON p.marques_id = m.id
        ORDER BY p.date_ajouter DESC 
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
    <title>Nouveautés - Boutique de Téléphones</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #f72585;
            --accent: #4cc9f0;
            --dark: #14213d;
            --light: #f8f9fa;
            --gray: #adb5bd;
            --light-gray: #e9ecef;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: var(--light);
        }

        /* Header */
        header {
            position: sticky;
            top: 0;
            background: rgba(255,255,255,0.98);
            box-shadow: var(--shadow-sm);
            z-index: 1000;
            padding: 1rem 5%;
            transition: var(--transition);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo img {
            height: 50px;
            transition: var(--transition);
        }

        .logo:hover img {
            transform: scale(1.05);
        }

        /* Navigation */
        nav ul {
            display: flex;
            list-style: none;
            gap: 1.5rem;
        }

        nav ul li {
            position: relative;
        }

        nav ul li a {
            text-decoration: none;
            color: var(--dark);
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        nav ul li a:hover {
            color: var(--primary);
            background: rgba(67, 97, 238, 0.1);
        }

        /* Menu déroulant */
        nav ul li ul {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            box-shadow: var(--shadow-lg);
            border-radius: 0.5rem;
            padding: 0.5rem 0;
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: var(--transition);
            z-index: 10;
        }

        nav ul li:hover ul {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        nav ul li ul li a {
            display: block;
            padding: 0.5rem 1rem;
        }

        /* Panier */
        .panier-count {
            background: var(--secondary);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }

        /* Main Content */
        .main-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .page-title {
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 2rem;
            color: var(--dark);
            position: relative;
            padding-bottom: 1rem;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }

        /* Produits Grid */
        .produits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .produit-card {
            background: white;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .produit-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .produit-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: var(--transition);
        }

        .produit-card:hover .produit-image {
            transform: scale(1.05);
        }

        .produit-info {
            padding: 1.5rem;
        }

        .produit-title {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .produit-marque {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .produit-description {
            color: var(--dark);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .produit-prix {
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--primary);
            margin-top: 1rem;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .page-link {
            padding: 0.75rem 1rem;
            background: white;
            color: var(--dark);
            border: 1px solid var(--light-gray);
            border-radius: 0.5rem;
            text-decoration: none;
            transition: var(--transition);
        }

        .page-link:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .page-link.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* Bouton Retour */
        .btn-retour {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--primary);
            color: white;
            border-radius: 0.5rem;
            text-decoration: none;
            margin: 2rem 0;
            transition: var(--transition);
        }

        .btn-retour:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Message */
        .message {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0.5rem;
            text-align: center;
        }

        .message.error {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger);
        }

        /* Responsive */
        @media (max-width: 768px) {
            nav ul {
                flex-direction: column;
                position: fixed;
                top: 80px;
                left: 0;
                width: 100%;
                background: white;
                padding: 1rem;
                box-shadow: var(--shadow-md);
                transform: translateY(-150%);
                opacity: 0;
                transition: var(--transition);
                z-index: 999;
            }

            nav ul.active {
                transform: translateY(0);
                opacity: 1;
            }

            .nav-toggle {
                display: block;
                background: none;
                border: none;
                font-size: 1.5rem;
                color: var(--dark);
                cursor: pointer;
            }

            .produits-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                font-size: 2rem;
            }
        }

        @media (min-width: 769px) {
            .nav-toggle {
                display: none;
            }
        }

        
        /* Footer */
        footer {
            background: var(--dark);
            color: white;
            padding: 3rem 0 0;
            margin-top: 3rem;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-column h3 {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .footer-column h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--primary);
        }

        .footer-links {
            list-style: none;
        }

        .footer-link {
            display: block;
            margin-bottom: 0.75rem;
            color: var(--gray);
            transition: var(--transition);
            text-decoration: none;
        }

        .footer-link:hover {
            color: white;
            transform: translateX(5px);
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            color: white;
            transition: var(--transition);
        }

        .social-link:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }

        .contact-info {
            margin-top: 1rem;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
            color: var(--gray);
        }

        .contact-item i {
            color: var(--primary);
            margin-top: 0.25rem;
        }

        .payment-methods {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .payment-method {
            height: 24px;
            filter: grayscale(100%);
            opacity: 0.7;
            transition: var(--transition);
        }

        .payment-method:hover {
            filter: grayscale(0%);
            opacity: 1;
        }

        .footer-bottom {
            background: rgba(0,0,0,0.2);
            padding: 1.5rem 0;
            text-align: center;
        }

        .copyright {
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            nav ul {
                flex-direction: column;
                position: fixed;
                top: 80px;
                left: 0;
                width: 100%;
                background: white;
                padding: 1rem;
                box-shadow: var(--shadow-md);
                transform: translateY(-150%);
                opacity: 0;
                transition: var(--transition);
                z-index: 999;
            }

            nav ul.active {
                transform: translateY(0);
                opacity: 1;
            }

            .nav-toggle {
                display: block;
                background: none;
                border: none;
                font-size: 1.5rem;
                color: var(--dark);
                cursor: pointer;
            }

            .produits-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                font-size: 2rem;
            }

            .footer-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (min-width: 769px) {
            .nav-toggle {
                display: none;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <a href="index.php"><img src="images/phone11.jpg" alt="Logo de la boutique"></a>
            </div>
            
            <nav>
                <button class="nav-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <ul>
                    <li><a href="index.php"><i class="fas fa-home"></i> Accueil</a></li>
                    <li>
                        <a href="#"><i class="fas fa-tags"></i> Marques</a>
                        <ul>
                            <?php if(!empty($listeMarques)): ?>
                                <?php foreach ($listeMarques as $marque): ?>
                                    <li>
                                        <a href="index.php?marque=<?= urlencode($marque['nom']) ?>">
                                            <?= htmlspecialchars($marque['nom']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><a href="#">Aucune marque disponible</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <li><a href="nouveautes.php" class="active"><i class="fas fa-star"></i> Nouveautés</a></li>
                    <li><a href="promotions.php"><i class="fas fa-percentage"></i> Promotions</a></li>
                    <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
                    <li>
                        <a href="panier.php"><i class="fas fa-shopping-cart"></i> Panier 
                            <?php if(isset($_SESSION['panier']) && count($_SESSION['panier']) > 0): ?>
                                <span class="panier-count"><?= count($_SESSION['panier']) ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <?php if (isset($_SESSION['utilisateur'])): ?>
                            <a href="compte.php"><i class="fas fa-user"></i> Mon compte</a>
                            <a href="deconnexion.php" style="color: #e74c3c;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                        <?php else: ?>
                            <a href="connexion.php"><i class="fas fa-sign-in-alt"></i> Connexion</a>
                        <?php endif; ?>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="main-container">
        <h1 class="page-title">Nos Nouveautés</h1>

        <?php if (!empty($message)): ?>
            <div class="message error"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <a href="javascript:history.back()" class="btn-retour">
            <i class="fas fa-arrow-left"></i> Retour
        </a>

        <div class="produits-grid">
            <?php if (!empty($produits)): ?>
                <?php foreach ($produits as $produit): ?>
                    <div class="produit-card">
                        <img src="images/<?= htmlspecialchars($produit['lien_image']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>" class="produit-image">
                        <div class="produit-info">
                            <h3 class="produit-title"><?= htmlspecialchars($produit['nom']) ?></h3>
                            <p class="produit-marque"><?= htmlspecialchars($produit['marque']) ?></p>
                            <p class="produit-description"><?= htmlspecialchars($produit['description']) ?></p>
                            <p class="produit-prix"><?= number_format($produit['prix'], 2) ?> Fcfa</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun produit trouvé.</p>
            <?php endif; ?>
        </div>

        <div class="pagination">
            <?php if ($total_pages > 1): ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="nouveautes.php?page=<?= $i ?>" class="page-link <?= ($i === $page_courante) ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="footer-container">
            <div class="footer-grid">
                <div class="footer-column">
                    <h3>Informations</h3>
                    <ul class="footer-links">
                        <li><a href="index.php" class="footer-link">Accueil</a></li>
                        <li><a href="nouveautes.php" class="footer-link">Nouveautés</a></li>
                        <li><a href="promotions.php" class="footer-link">Promotions</a></li>
                        <li><a href="contact.php" class="footer-link">Contact</a></li>
                        <li><a href="cgv.php" class="footer-link">Conditions Générales</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Contact</h3>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>123 Rue des Téléphones, Ville</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+33 1 23 45 67 89</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>contact@boutique-phone.com</span>
                        </div>
                    </div>
                    
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h3>Moyens de paiement</h3>
                    <div class="payment-methods">
                        <img src="images/visa.png" alt="Visa" class="payment-method">
                        <img src="images/mastercard.png" alt="Mastercard" class="payment-method">
                        <img src="images/paypal.png" alt="PayPal" class="payment-method">
                        <img src="images/cb.png" alt="CB" class="payment-method">
                        <img src="images/apple-pay.png" alt="Apple Pay" class="payment-method">
                        <img src="images/google-pay.png" alt="Google Pay" class="payment-method">
                    </div>
                    
                    <h3 style="margin-top: 1.5rem;">Newsletter</h3>
                    <form class="newsletter-form">
                        <input type="email" placeholder="Votre email" required>
                        <button type="submit" class="btn-submit">S'abonner</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="footer-container">
                <p class="copyright">&copy; <?= date('Y') ?> Boutique de Téléphones. Tous droits réservés.</p>
            </div>
        </div>
    </footer>


    <script>
        // Menu mobile
        document.querySelector('.nav-toggle').addEventListener('click', function() {
            document.querySelector('nav ul').classList.toggle('active');
            this.innerHTML = document.querySelector('nav ul').classList.contains('active') ? 
                '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
        });

        // Header scroll effect
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                document.querySelector('header').classList.add('header-scrolled');
            } else {
                document.querySelector('header').classList.remove('header-scrolled');
            }
        });
    </script>
</body>
</html>