<?php
session_start();
require_once 'gestionnaire.php';

$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();
$message = "";

// 1. Ajout au panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_au_panier'])) {
    $pid = intval($_POST['produit_id']);
    $qte = intval($_POST['quantite']);
    if ($pid > 0 && $qte > 0) {
        if (!isset($_SESSION['panier'])) {
            $_SESSION['panier'] = array();
        }
        // Si le produit est déjà dans le panier, on ajoute la quantité
        if (isset($_SESSION['panier'][$pid])) {
            $_SESSION['panier'][$pid] += $qte;
        } else {
            // Sinon on l'initialise à la quantité demandée
            $_SESSION['panier'][$pid] = $qte;
        }
    }
}


// 2. Nouveautés
try {
    $req = $connexion->query("
        SELECT id, nom, prix, prix_initial, lien_image 
        FROM produits 
        ORDER BY date_ajouter DESC 
        LIMIT 4
    ");
    $nouveautes = $req->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $nouveautes = [];
}

// 3. Promotions
try {
    $req = $connexion->query("
        SELECT id, nom, prix, prix_initial, lien_image,
               (prix_initial - prix) AS reduction,
               ROUND(((prix_initial - prix)/prix_initial)*100) AS pourcentage
        FROM produits 
        WHERE prix_initial > 0 AND prix < prix_initial
        ORDER BY reduction DESC
        LIMIT 4
    ");
    $promotions = $req->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $promotions = [];
}

// 4. Chargement et groupement des avis
try {
    $req = $connexion->query("
        SELECT a.id, a.commentaire, a.note, a.date_avis, a.produit_id, u.nom AS utilisateur_nom
        FROM avis a
        JOIN utilisateurs u ON a.utilisateur_id = u.id
        ORDER BY a.date_avis DESC
    ");
    $tous_les_avis = $req->fetchAll(PDO::FETCH_ASSOC);
    $avis_par_produit = [];
    foreach ($tous_les_avis as $avis) {
        $avis_par_produit[$avis['produit_id']][] = $avis;
    }
} catch (PDOException $e) {
    $avis_par_produit = [];
}

// 5. Ajout d’un nouvel avis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_avis'])) {
    if (isset($_SESSION['utilisateur'])) {
        $utilisateur_id = $_SESSION['utilisateur']['id'];
        $prod_id = intval($_POST['produit_id']);
        $commentaire = trim($_POST['commentaire']);
        $note = intval($_POST['note']);
        if ($prod_id > 0 && $commentaire !== "" && $note >= 1 && $note <= 5) {
            try {
                $ins = $connexion->prepare("
                    INSERT INTO avis (utilisateur_id, produit_id, commentaire, note, date_avis)
                    VALUES (:uid, :pid, :com, :note, NOW())
                ");
                $ins->execute([
                    ':uid' => $utilisateur_id,
                    ':pid' => $prod_id,
                    ':com' => $commentaire,
                    ':note' => $note
                ]);
                header('Location: index.php');
                exit;
            } catch (PDOException $e) {
                $message = "Erreur ajout avis : " . $e->getMessage();
            }
        } else {
            $message = "Veuillez remplir tous les champs correctement.";
        }
    } else {
        $message = "Vous devez être connecté pour laisser un avis.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Accueil - Vente de téléphones</title>
    <link rel="stylesheet" href="index.css">
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
                <?php if (isset($_SESSION['utilisateur'])): ?>
                    <a href="compte.php">Mon compte</a>
                    <a href="deconnexion.php" style="color:red;">Déconnexion</a>
                <?php else: ?>
                    <a href="connexion.php">Connexion/Inscription</a>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
</header>

<!--image banner-->
<div class="banner">
    <img src="images/banner.jpg" alt="Bannière de promotion">
    <h1>Bienvenue dans notre boutique! </h1>
    <p>Découvrez les dernières nouveautés et promotions sur les téléphones.</p>
    <a href="nouveautes.php" class="bouton-plus">Voir toutes les nouveautés</a>

</div>

<!-- Section Nouveautés -->
<section class="nouveautes">
    <h2>Nouveautés</h2>
    <div class="liste-produits">
        <?php if ($nouveautes): ?>
            <?php foreach ($nouveautes as $produit): ?>
                <div class="produit">
                    <img src="images/<?php echo htmlspecialchars($produit['lien_image']); ?>"
                         alt="<?php echo htmlspecialchars($produit['nom']); ?>">
                    <h3><?php echo htmlspecialchars($produit['nom']); ?></h3>
                    <p><?php echo number_format($produit['prix'], 2); ?> Fcfa</p>
                    <form method="POST" action="index.php">
                        <input type="hidden" name="produit_id" value="<?php echo $produit['id']; ?>">
                        <input type="number" name="quantite" value="1" min="1">
                        <button type="submit" name="ajouter_au_panier">Ajouter au panier</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun produit trouvé.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Section Promotions -->
<section class="promotions">
    <h2>Promotions</h2>
    <div class="liste-promotions">
        <?php if ($promotions): ?>
            <?php foreach ($promotions as $promotion): ?>
                <div class="promotion">
                    <img src="images/<?php echo htmlspecialchars($promotion['lien_image']); ?>"
                         alt="<?php echo htmlspecialchars($promotion['nom']); ?>">
                    <h3><?php echo htmlspecialchars($promotion['nom']); ?></h3>
                    <p class="prix-initial">
                        <s><?php echo number_format($promotion['prix_initial'], 2); ?> Fcfa</s>
                    </p>
                    <p class="prix-promotion">
                        <?php echo number_format($promotion['prix'], 2); ?> Fcfa
                        <span class="reduction">(-<?php echo $promotion['pourcentage']; ?>%)</span>
                    </p>
                    <form method="POST" action="index.php">
                        <input type="hidden" name="produit_id" value="<?php echo $promotion['id']; ?>">
                        <input type="number" name="quantite" value="1" min="1">
                        <button type="submit" name="ajouter_au_panier">Ajouter au panier</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucune promotion disponible pour le moment.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Section dépôt d'avis général -->
<section class="deposer-avis">
    <h2>Laisser un avis</h2>
    <?php if ($message): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['utilisateur'])): ?>
        <?php $pid = isset($_GET['produit_id']) ? intval($_GET['produit_id']) : 0; ?>
        <form method="POST" action="index.php">
            <input type="hidden" name="produit_id" value="<?php echo $pid; ?>">
            <textarea name="commentaire" placeholder="Votre commentaire" required></textarea>
            <select name="note" required>
                <option value="" disabled selected>Note</option>
                <option value="1">1 étoile</option>
                <option value="2">2 étoiles</option>
                <option value="3">3 étoiles</option>
                <option value="4">4 étoiles</option>
                <option value="5">5 étoiles</option>
            </select>
            <button type="submit" name="ajouter_avis">Envoyer</button>
        </form>
    <?php else: ?>
        <p>Vous devez être connecté pour laisser un avis.</p>
    <?php endif; ?>
</section>

<!-- Footer -->
<footer>
    <div class="contenu-footer">
        <div class="colonnes-footer">
            <div class="colonne">
                <h3>Informations</h3>
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="promotions.php">Promotions</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="cgv.php">CGV</a></li>
                    <li><a href="politique-confidentialite.php">Politique de confidentialité</a></li>
                </ul>
            </div>
            <div class="colonne">
                <h3>Contact</h3>
                <p>Adresse : 123 Rue des Téléphones, Ville</p>
                <p>Téléphone : 01 23 45 67 89</p>
                <p>Email : contact@votresite.com</p>
            </div>
            <div class="colonne reseaux-sociaux">
                <h3>Suivez-nous</h3>
                <a href="#"><img src="images/facebook.png" alt="Facebook"></a>
                <a href="#"><img src="images/twitter.png" alt="Twitter"></a>
                <a href="#"><img src="images/instagram.png" alt="Instagram"></a>
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
<script src="index.js"></script>
</body>
</html>