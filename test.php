<?php
session_start(); // Démarrer la session

// Inclure le fichier gestionnaire.php
require_once 'gestionnaire.php';

// Créer une instance de la classe Gestionnaire
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$produitsParPage = 10;
$offset = ($page - 1) * $produitsParPage;

// Filtrage et tri
$filtreMarque = isset($_GET['marque']) ? $_GET['marque'] : null;
$tri = isset($_GET['tri']) ? $_GET['tri'] : 'nom';

// Construire la requête SQL
$sql = "SELECT * FROM produits";
$conditions = [];
$params = [];

if ($filtreMarque) {
    $conditions[] = "marque = :marque";
    $params[':marque'] = $filtreMarque;
}

if ($conditions) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

$sql .= " ORDER BY $tri LIMIT $offset, $produitsParPage";

try {
    $produits = $gestionnaire->executerRequete($sql, $params)->fetchAll(PDO::FETCH_ASSOC);

    // Compter le nombre total de produits
    $totalProduits = $gestionnaire->executerRequete("SELECT COUNT(*) FROM produits")->fetchColumn();
    $totalPages = ceil($totalProduits / $produitsParPage);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Accueil - Vente de téléphones</title>
    <link rel="stylesheet" href="index.css"> <!-- Assurez-vous d'avoir un fichier CSS -->
</head>
<body>
<header>
    <div class="logo">
        <a href="index.php"><img src="images/phone11.jpg" alt="Logo du site"></a>
    </div>
    <nav>
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li>
                <a href="#">Marques</a>
                <ul>
                    <li><a href="index.php?marque=apple">Apple</a></li>
                    <li><a href="index.php?marque=samsung">Samsung</a></li>
                    <li><a href="index.php?marque=xiaomi">Xiaomi</a></li>
                </ul>
            </li>
            <li><a href="nouveautes.php">Nouveautés</a></li>
            <li><a href="promotions.php">Promotions</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="panier.php">Panier (<?php echo isset($_SESSION['panier']) ? count($_SESSION['panier']) : 0; ?>)</a></li>
            <li>
                <?php if (isset($_SESSION['utilisateur'])) : ?>
                    <a href="compte.php">Mon compte</a>
                <?php else : ?>
                    <a href="connexion.php">Connexion/Inscription</a>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
    <div class="recherche">
        <form action="recherche.php" method="GET">
            <input type="text" name="q" placeholder="Rechercher un téléphone...">
            <button type="submit">Rechercher</button>
        </form>
    </div>
</header>

<!-- Section de la bannière principale -->
<section class="banniere-principale">
    <div class="contenu-banniere">
        <h1>Découvrez les derniers téléphones mobiles</h1>
        <p>Profitez de nos offres exceptionnelles et trouvez le téléphone qui vous convient.</p>
        <a href="nouveautes.php" class="bouton-decouvrir">Découvrez les nouveautés</a>
    </div>
</section>

<!-- Section des nouveautés -->
<section class="nouveautes">
    <h2>Nouveautés</h2>
    <div class="liste-produits">
        <?php
        try {
            $requete = $connexion->query("SELECT * FROM produits ORDER BY date_ajouter DESC LIMIT 4");
            $produits = $requete->fetchAll(PDO::FETCH_ASSOC);

            if ($produits) {
                foreach ($produits as $produit) {
                    echo '<div class="produit">';
                    echo '<img src="'. images/$produit['lien_image'] . '" alt="' . $produit['nom'] . '">';
                    echo '<h3>' . $produit['nom'] . '</h3>';
                    echo '<p>' . $produit['prix'] . ' €</p>';
                    echo '<a href="produit.php?id=' . $produit['id'] . '" class="bouton-voir">Voir le produit</a>';
                    echo '</div>';
                }
            } else {
                echo '<p>Aucun produit trouvé.</p>';
            }
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
        ?>
    </div>
    <a href="nouveautes.php" class="bouton-plus">Voir toutes les nouveautés</a>
</section>

<!-- Section des promotions -->
<section class="promotions">
    <h2>Promotions</h2>
    <div class="liste-promotions">
        <?php
        try {
            $requete = $connexion->query("SELECT * FROM produits WHERE prix < prix_initial");
            $promotions = $requete->fetchAll(PDO::FETCH_ASSOC);

            if ($promotions) {
                foreach ($promotions as $promotion) {
                    echo '<div class="promotion">';
                    echo '<img src="' . $promotion['lien_image'] . '" alt="' . $promotion['nom'] . '">';
                    echo '<h3>' . $promotion['nom'] . '</h3>';
                    echo '<p class="prix-initial">' . $promotion['prix_initial'] . ' €</p>';
                    echo '<p class="prix-promotion">' . $promotion['prix'] . ' €</p>';
                    echo '<a href="produit.php?id=' . $promotion['id'] . '" class="bouton-voir">Voir l\'offre</a>';
                    echo '</div>';
                }
            } else {
                echo '<p>Aucune promotion disponible pour le moment.</p>';
            }
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
        ?>
    </div>
</section>

<!-- Section des marques populaires -->
<section class="marques-populaires">
    <h2>Marques populaires</h2>
    <div class="liste-marques">
        <a href="marque.php?marque=apple" class="marque">
            <img src="images/apple-logo.png" alt="Logo Apple">
        </a>
        <a href="marque.php?marque=samsung" class="marque">
            <img src="images/samsung-logo.png" alt="Logo Samsung">
        </a>
        <a href="marque.php?marque=xiaomi" class="marque">
            <img src="images/xiaomi-logo.png" alt="Logo Xiaomi">
        </a>
    </div>
</section>

<!-- Section des avis clients -->
<section class="avis-clients">
    <h2>Ce que nos clients disent de nous</h2>
    <div class="liste-avis">
        <div class="avis">
            <div class="info-client">
                <img src="images/client1.jpg" alt="Client 1">
                <h3>Jean Dupont</h3>
            </div>
            <p>Excellent service et téléphone de qualité. Je recommande vivement !</p>
            <div class="etoiles">
                <span class="etoile active">★</span>
                <span class="etoile active">★</span>
                <span class="etoile active">★</span>
                <span class="etoile active">★</span>
                <span class="etoile active">★</span>
            </div>
        </div>
        <div class="avis">
            <div class="info-client">
                <img src="images/client2.jpg" alt="Client 2">
                <h3>Marie Dubois</h3>
            </div>
            <p>Livraison rapide et produit conforme à la description. Très satisfaite.</p>
            <div class="etoiles">
                <span class="etoile active">★</span>
                <span class="etoile active">★</span>
                <span class="etoile active">★</span>
                <span class="etoile active">★</span>
                <span class="etoile">★</span>
            </div>
        </div>
        <div class="avis">
            <div class="info-client">
                <img src="images/client3.jpg" alt="Client 3">
                <h3>Pierre Martin</h3>
            </div>
            <p>Service client très réactif et professionnel. Je suis ravi de mon achat.</p>
            <div class="etoiles">
                <span class="etoile active">★</span>
                <span class="etoile active">★</span>
                <span class="etoile active">★</span>
                <span class="etoile active">★</span>
                <span class="etoile active">★</span>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer>
    <div class="contenu-footer">
        <div class="colonnes-footer">
            <div class="colonne">
                <h3>Informations</h3>
                <ul>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="cgv.php">CGV</a></li>
                    <li><a href="politique-confidentialite.php">Politique de confidentialité</a></li>
                </ul>
            </div>
            <div class="colonne">
                <h3>Contact</h3>
                <p>Adresse : 123 Rue des Téléphones, Ville</p>
                <p>Téléphone : 01 23 45 67 89</p>
                <p>Email : contact@votresite.com</p>
            </div>
            <div class="colonne reseaux-sociaux">
                <h3>Suivez-nous</h3>
                <a href="#" class="reseau-social"><img src="images/facebook.png" alt="Facebook"></a>
                <a href="#" class="reseau-social"><img src="images/twitter.png" alt="Twitter"></a>
                <a href="#" class="reseau-social"><img src="images/instagram.png" alt="Instagram"></a>
            </div>
            <div class="colonne paiements">
                <h3>Paiements sécurisés</h3>
                <img src="images/visa.png" alt="Visa">
                <img src="images/mastercard.png" alt="Mastercard">
                <img src="images/paypal.png" alt="PayPal">
            </div>
        </div>
        <div class="copyright">
            <p>&copy; <?php echo date("Y"); ?> Vente de téléphones. Tous droits réservés.</p>
        </div>
    </div>
</footer>

</body>
</html>